<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];
$nama_eo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM users WHERE id=$eo_id"))['nama'];
$foto_user = $_SESSION['foto_profil'] ?? null;

// Ambil kontrak milik EO yang disetujui (untuk pilihan)
$kontrak_list = mysqli_query($conn, "
    SELECT k.kontrak_id, k.judul_kontrak, k.nomor_kontrak, k.judul_seminar, k.klien_id,
           u.nama AS nama_klien
    FROM kontrak k
    JOIN users u ON k.klien_id = u.id
    WHERE k.eo_id = $eo_id AND k.status_kontrak = 'disetujui'
    ORDER BY k.kontrak_id DESC
");

// Jika laporan sudah ada, ambil untuk edit
$laporan_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$laporan_data = null;
if ($laporan_id) {
    $laporan_data = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT * FROM laporan_akhir WHERE laporan_id=$laporan_id AND eo_id=$eo_id"
    ));
}

// Handle AJAX: ambil data seminar & feedback berdasarkan kontrak_id
if (isset($_GET['get_data']) && isset($_GET['kontrak_id'])) {
    $kid = (int) $_GET['kontrak_id'];

    $seminar = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.jam_mulai, s.jam_selesai,
               s.platform, s.metode, s.kategori, s.kuota,
               u.nama AS narasumber,
               COUNT(DISTINCT p.id) AS total_peserta
        FROM seminar s
        LEFT JOIN users u ON s.narasumber_id = u.id
        LEFT JOIN pendaftaran p ON p.seminar_id = s.seminar_id
        WHERE s.kontrak_id = $kid AND s.eo_id = $eo_id
        GROUP BY s.seminar_id
        LIMIT 1
    "));

    $avg_rating = 0;
    $total_feedback = 0;
    $komentar_list = [];

    if ($seminar) {
        $sid = $seminar['seminar_id'];
        $fb_stat = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT COUNT(*) AS total, ROUND(AVG(rating),1) AS avg_rating
            FROM feedback WHERE seminar_id=$sid AND status_validasi='valid'
        "));
        $avg_rating = $fb_stat['avg_rating'] ?? 0;
        $total_feedback = $fb_stat['total'] ?? 0;

        $fb_rows = mysqli_query($conn, "
            SELECT f.komentar, f.rating, f.topik, u.nama
            FROM feedback f
            JOIN users u ON f.peserta_id = u.id
            WHERE f.seminar_id=$sid AND f.status_validasi='valid'
            ORDER BY f.id DESC LIMIT 5
        ");
        while ($r = mysqli_fetch_assoc($fb_rows)) {
            $komentar_list[] = $r;
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'seminar' => $seminar,
        'avg_rating' => $avg_rating,
        'total_feedback' => $total_feedback,
        'komentar_list' => $komentar_list,
    ]);
    exit;
}

// Handle simpan laporan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $kid = (int) $_POST['kontrak_id'];
    $sid = (int) $_POST['seminar_id'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul_laporan']);
    $ringkasan = mysqli_real_escape_string($conn, $_POST['ringkasan']);
    $peserta_hadir = (int) $_POST['peserta_hadir'];
    $kendala = mysqli_real_escape_string($conn, $_POST['kendala']);
    $rekomendasi = mysqli_real_escape_string($conn, $_POST['rekomendasi']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan_tambahan']);
    $tgl_laporan = date('Y-m-d');

    // Ambil klien_id dari kontrak
    $klien_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT klien_id FROM kontrak WHERE kontrak_id=$kid"))['klien_id'];

    if ($laporan_id) {
        mysqli_query($conn, "
            UPDATE laporan_akhir SET
                judul_laporan='$judul', ringkasan='$ringkasan',
                peserta_hadir=$peserta_hadir, kendala='$kendala',
                rekomendasi='$rekomendasi', catatan_tambahan='$catatan'
            WHERE laporan_id=$laporan_id AND eo_id=$eo_id
        ");
        echo "<script>alert('Laporan berhasil diperbarui!'); window.location='buat_laporan.php';</script>";
    } else {
        mysqli_query($conn, "INSERT INTO laporan_akhir (kontrak_id, seminar_id, eo_id, klien_id, judul_laporan, ringkasan,
                 peserta_hadir, kendala, rekomendasi, catatan_tambahan, tanggal_laporan, status_laporan)
            VALUES
                ($kid, $sid, $eo_id, $klien_id, '$judul', '$ringkasan',
                 $peserta_hadir, '$kendala', '$rekomendasi', '$catatan', '$tgl_laporan', 'terkirim')
        ");
        echo "<script>alert('Laporan berhasil dikirim ke klien!'); window.location='buat_laporan.php';</script>";
    }
}

// Daftar laporan yang sudah dibuat
$laporan_list = mysqli_query($conn, "SELECT l.laporan_id, l.judul_laporan, l.tanggal_laporan, l.status_laporan, k.nomor_kontrak, u.nama AS nama_klien
    FROM laporan_akhir l
    JOIN kontrak k ON l.kontrak_id = k.kontrak_id
    JOIN users u ON l.klien_id = u.id
    WHERE l.eo_id = $eo_id
    ORDER BY l.laporan_id DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Seminar — SeminarOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7f6;
            color: #1a2634;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #2c3e50;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-brand h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .sidebar-brand p {
            color: #7f8c9a;
            font-size: 11px;
            margin-top: 3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
        }

        .nav-label {
            color: #5a6a78;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 12px;
            margin: 12px 0 6px;
            display: block;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #9baab7;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 2px;
            transition: all .2s;
        }

        .sidebar-nav a:hover {
            background: rgba(52, 152, 219, .12);
            color: #3498db;
        }

        .sidebar-nav a.active {
            background: rgba(52, 152, 219, .18);
            color: #3498db;
            font-weight: 600;
        }

        .sidebar-nav a.logout {
            background: rgba(241, 27, 3, 0.26);
            color: #da0d0d;
            margin-top: 8px;
            font-weight: bold;
        }

        .sidebar-nav a.logout:hover {
            background: red;
            color: #e3bcb8;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, .06);
            font-size: 11px;
            color: #5a6a78;
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 90;
            backdrop-filter: blur(1px);
        }

        .overlay.show {
            display: block;
        }

        /* Topbar */
        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 240px;
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 80;
            transition: left .3s;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .burger {
            background: none;
            border: none;
            cursor: pointer;
            display: none;
            flex-direction: column;
            gap: 5px;
            padding: 4px;
        }

        .burger span {
            display: block;
            width: 20px;
            height: 2px;
            background: #64748b;
            border-radius: 2px;
            transition: .2s;
        }

        .topbar-title {
            font-size: 14.5px;
            font-weight: 600;
            color: #1a2634;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 9px;
            background: #f4f7f6;
            border: 1px solid #e8edf2;
            border-radius: 24px;
            padding: 6px 14px 6px 8px;
            font-size: 12.5px;
            font-weight: 500;
            color: #2c3e50;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Main Content */
        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left .3s;
        }

        .content {
            padding: 28px 26px 48px;
        }

        /* Cards & Tables */
        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border: 1px solid #e8edf2;
            overflow: hidden;
            margin-bottom: 26px;
        }

        .card-head {
            padding: 18px 22px 14px;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-head h3 {
            font-size: 14.5px;
            font-weight: 700;
            color: #1a2634;
        }

        .card-head p {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 2px;
        }

        .tbl-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #f8fafc;
        }

        th {
            padding: 11px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #7f8c8d;
            letter-spacing: .5px;
            text-transform: uppercase;
            border-bottom: 1px solid #e8edf2;
            white-space: nowrap;
        }

        td {
            padding: 13px 16px;
            font-size: 13px;
            color: #2c3e50;
            border-bottom: 1px solid #f4f7f6;
        }

        tbody tr:hover {
            background: #fafcfe;
        }

        .td-title {
            font-weight: 600;
            color: #1a2634;
        }

        /* Badges */
        .tbl-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-diterima {
            background: #d5f5e3;
            color: #1e8449;
        }

        /* Terkirim */
        .badge-menunggu {
            background: #fdebd0;
            color: #a04000;
        }

        /* Proses / Draft */

        /* Buttons */
        .btn-primary {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background .18s;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-secondary {
            background: #f4f7f6;
            color: #2c3e50;
            border: 1px solid #e8edf2;
            border-radius: 7px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #e8edf2;
        }

        .btn-edit-sm {
            background: #2c3e50;
            color: #fff;
            border: 1px solid #2c3e50;
            border-radius: 7px;
            padding: 6px 12px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
        }

        .btn-edit-sm:hover {
            background: #1a252f;
        }

        /* Form Layout */
        .form-content {
            padding: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #1a2634;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #dce3eb;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #333;
            background: #fff;
            transition: border-color .2s;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #3498db;
        }

        .form-input[readonly] {
            background: #f8fafc;
            color: #95a5a6;
            cursor: not-allowed;
        }

        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #dce3eb;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #333;
            background: #fff;
            resize: vertical;
            min-height: 80px;
            box-sizing: border-box;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        /* Autofill Panel */
        .autofill-panel {
            background: #f0f7ff;
            border: 1px solid #c3ddf7;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 22px;
            display: none;
        }

        .autofill-panel.show {
            display: block;
        }

        .autofill-panel .label-head {
            font-size: 12px;
            color: #2980b9;
            font-weight: 700;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .autofill-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .autofill-item {
            background: #fff;
            border-radius: 8px;
            padding: 12px 14px;
            border: 1px solid #dce8f5;
        }

        .autofill-item .ai-label {
            font-size: 11px;
            color: #7f8c8d;
            font-weight: 600;
            display: block;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .autofill-item .ai-value {
            font-size: 13.5px;
            font-weight: 600;
            color: #1a2634;
        }

        .feedback-preview {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #c3ddf7;
        }

        .feedback-preview .fp-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #2980b9;
            margin-bottom: 10px;
        }

        .fb-item {
            background: #fff;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 8px;
            border-left: 3px solid #3498db;
            font-size: 13px;
            color: #2c3e50;
            line-height: 1.5;
            border-top: 1px solid #e8edf2;
            border-right: 1px solid #e8edf2;
            border-bottom: 1px solid #e8edf2;
        }

        .fb-item .fb-meta {
            font-size: 11.5px;
            color: #7f8c8d;
            margin-top: 6px;
            font-weight: 500;
        }

        .stars {
            color: #f39c12;
            font-size: 14px;
        }

        .loading-text {
            color: #3498db;
            font-size: 13px;
            padding: 10px 0;
            display: none;
            font-weight: 500;
        }

        @media (max-width:960px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .topbar {
                left: 0;
            }

            .main {
                margin-left: 0;
            }

            .burger {
                display: flex;
            }

            .autofill-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width:720px) {
            .content {
                padding: 18px 14px 40px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .autofill-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Portal Event Organizer</p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Menu Utama</span>
            <a href="dashboardeo.php">Dashboard</a>
            <a href="seminar.php">Kelola Seminar</a>
            <a href="data_peserta.php">Data Peserta</a>
            <a href="feedback_eo.php">Data Feedback</a>
            <a href="buat_laporan.php" class="active">Laporan</a>

            <span class="nav-label" style="margin-top:18px">Sistem</span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        <div class="sidebar-footer">&copy; 2026 Sistem Manajemen Seminar</div>
    </nav>

    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="burger" onclick="toggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <span class="topbar-title">Laporan Seminar</span>
        </div>
        <div class="user-pill">
            <div class="user-avatar">
                <?php if ($foto_user): ?>
                    <img src="upload/foto_profil/<?= htmlspecialchars($foto_user) ?>">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($nama_eo, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <?= htmlspecialchars($nama_eo) ?>
        </div>
    </div>

    <div class="main" id="main">
        <div class="content">

            <div class="table-card">
                <div class="card-head">
                    <div>
                        <h3><?= $laporan_id ? 'Edit Laporan Seminar' : 'Buat Laporan Seminar Baru' ?></h3>
                        <p>Kirimkan laporan akhir pelaksanaan seminar kepada Klien</p>
                    </div>
                </div>
                <div class="form-content">
                    <form method="POST" id="formLaporan">
                        <input type="hidden" name="seminar_id" id="hidden_seminar_id" value="">

                        <div class="form-group">
                            <label class="form-label">Pilih Kontrak Kerja Sama</label>
                            <select name="kontrak_id" id="select_kontrak" class="form-input"
                                onchange="loadData(this.value)" required>
                                <option value="">-- Pilih Kontrak Disetujui --</option>
                                <?php
                                mysqli_data_seek($kontrak_list, 0);
                                while ($k = mysqli_fetch_assoc($kontrak_list)) { ?>
                                    <option value="<?= $k['kontrak_id'] ?>" <?= ($laporan_data && $laporan_data['kontrak_id'] == $k['kontrak_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nomor_kontrak']) ?> —
                                        <?= htmlspecialchars($k['judul_kontrak']) ?> (Klien:
                                        <?= htmlspecialchars($k['nama_klien']) ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <p class="loading-text" id="loadingText">Menarik data dari server, mohon tunggu...</p>

                        <div class="autofill-panel" id="autofillPanel">
                            <div class="label-head">Data Pelaksanaan Otomatis</div>
                            <div class="autofill-grid" id="autofillGrid"></div>
                            <div class="feedback-preview" id="feedbackPreview"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Judul Laporan</label>
                            <input type="text" name="judul_laporan" id="judul_laporan" class="form-input"
                                value="<?= htmlspecialchars($laporan_data['judul_laporan'] ?? '') ?>"
                                placeholder="Contoh: Laporan Akhir Pelaksanaan Seminar X" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ringkasan Pelaksanaan</label>
                            <textarea name="ringkasan" id="ringkasan" class="form-textarea"
                                placeholder="Deskripsikan ringkasan jalannya seminar secara komprehensif..."><?= htmlspecialchars($laporan_data['ringkasan'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Jumlah Peserta Hadir (Bisa disesuaikan)</label>
                                <input type="number" name="peserta_hadir" id="peserta_hadir" class="form-input"
                                    value="<?= $laporan_data['peserta_hadir'] ?? '' ?>" placeholder="0" min="0">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Rata-rata Rating Feedback</label>
                                <input type="text" id="field_rating" class="form-input" readonly
                                    placeholder="Akan terisi otomatis">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kendala & Catatan Khusus</label>
                            <textarea name="kendala" class="form-textarea" style="min-height: 70px;"
                                placeholder="Catat jika terdapat kendala operasional atau hal lainnya (kosongkan jika lancar)..."><?= htmlspecialchars($laporan_data['kendala'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Rekomendasi Tindak Lanjut</label>
                            <textarea name="rekomendasi" class="form-textarea" style="min-height: 70px;"
                                placeholder="Saran untuk perbaikan atau kegiatan selanjutnya..."><?= htmlspecialchars($laporan_data['rekomendasi'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Catatan Tambahan (Opsional)</label>
                            <textarea name="catatan_tambahan" class="form-textarea" style="min-height: 60px;"
                                placeholder="Pesan tambahan untuk klien..."><?= htmlspecialchars($laporan_data['catatan_tambahan'] ?? '') ?></textarea>
                        </div>

                        <div style="display:flex; gap:12px; margin-top: 10px;">
                            <button type="submit" name="simpan" class="btn-primary">
                                <?= $laporan_id ? 'Simpan Perubahan Laporan' : 'Kirim Laporan ke Klien' ?>
                            </button>
                            <?php if ($laporan_id): ?>
                                <a href="buat_laporan.php" class="btn-secondary">Batal Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-card">
                <div class="card-head">
                    <div>
                        <h3>Riwayat Laporan Terkirim</h3>
                        <p>Daftar laporan akhir yang telah Anda sampaikan ke Klien</p>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul Laporan</th>
                                <th>Nama Klien</th>
                                <th>Tanggal Lapor</th>
                                <th>Status Laporan</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($laporan_list) == 0): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; color:#95a5a6; padding:30px;">
                                        Belum ada riwayat pengiriman laporan.
                                    </td>
                                </tr>
                            <?php else:
                                while ($l = mysqli_fetch_assoc($laporan_list)):
                                    $status_class = 'badge-menunggu';
                                    if (strtolower($l['status_laporan']) == 'terkirim' || strtolower($l['status_laporan']) == 'selesai') {
                                        $status_class = 'badge-diterima';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <div class="td-title"
                                                style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                                                title="<?= htmlspecialchars($l['judul_laporan']) ?>">
                                                <?= htmlspecialchars($l['judul_laporan']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($l['nama_klien']) ?></td>
                                        <td><?= date('d M Y', strtotime($l['tanggal_laporan'])) ?></td>
                                        <td>
                                            <span class="tbl-badge <?= $status_class ?>">
                                                <?= ucfirst($l['status_laporan']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="buat_laporan.php?edit=<?= $l['laporan_id'] ?>" class="btn-edit-sm">Edit
                                                Data</a>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
        }

        function makeStars(rating) {
            let s = '';
            for (let i = 1; i <= 5; i++) {
                s += i <= rating ? '&#9733;' : '&#9734;';
            }
            return '<span class="stars">' + s + '</span>';
        }

        function loadData(kontrak_id) {
            if (!kontrak_id) {
                document.getElementById('autofillPanel').classList.remove('show');
                return;
            }

            document.getElementById('loadingText').style.display = 'block';
            document.getElementById('autofillPanel').classList.remove('show');

            fetch('buat_laporan.php?get_data=1&kontrak_id=' + kontrak_id)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('loadingText').style.display = 'none';

                    if (!data.seminar) {
                        document.getElementById('autofillGrid').innerHTML =
                            '<div style="color:#c0392b; font-size:13px; font-weight:500;">Tidak ditemukan seminar yang terhubung dengan kontrak ini. Pastikan Anda telah membuat seminar untuk kontrak yang dipilih.</div>';
                        document.getElementById('autofillPanel').classList.add('show');
                        return;
                    }

                    const s = data.seminar;
                    document.getElementById('hidden_seminar_id').value = s.seminar_id;

                    // Hanya set peserta_hadir jika sedang tidak dalam mode edit / field kosong
                    if (!document.getElementById('peserta_hadir').value) {
                        document.getElementById('peserta_hadir').value = s.total_peserta;
                    }

                    document.getElementById('field_rating').value = data.avg_rating ? data.avg_rating + ' / 5.0' : 'Belum ada feedback';

                    if (!document.getElementById('judul_laporan').value) {
                        document.getElementById('judul_laporan').value = 'Laporan Akhir: ' + s.judul_seminar;
                    }

                    const grid = document.getElementById('autofillGrid');
                    grid.innerHTML = `
                        <div class="autofill-item"><span class="ai-label">Judul Seminar</span><span class="ai-value">${s.judul_seminar}</span></div>
                        <div class="autofill-item"><span class="ai-label">Waktu Pelaksanaan</span><span class="ai-value">${s.tanggal || '-'} &middot; ${s.jam_mulai}</span></div>
                        <div class="autofill-item"><span class="ai-label">Narasumber</span><span class="ai-value">${s.narasumber || '-'}</span></div>
                        <div class="autofill-item"><span class="ai-label">Platform / Metode</span><span class="ai-value">${s.platform} &middot; ${s.metode || '-'}</span></div>
                        <div class="autofill-item"><span class="ai-label">Total Pendaftar Valid</span><span class="ai-value">${s.total_peserta} Orang</span></div>
                        <div class="autofill-item"><span class="ai-label">Partisipasi Feedback</span><span class="ai-value">${data.total_feedback} Responden</span></div>
                    `;

                    let fbHtml = '';
                    if (data.komentar_list.length > 0) {
                        fbHtml = '<div class="fp-label">Cuplikan Feedback Peserta Terbaru</div>';
                        data.komentar_list.forEach(fb => {
                            fbHtml += `
                                <div class="fb-item">
                                    "${fb.komentar}"
                                    <div class="fb-meta">&mdash; ${fb.nama} &middot; Topik: ${fb.topik} &middot; ${makeStars(fb.rating)}</div>
                                </div>`;
                        });
                    } else {
                        fbHtml = '<div style="font-size:12.5px; color:#95a5a6; padding-top:8px;">Belum ada feedback valid untuk seminar ini.</div>';
                    }
                    document.getElementById('feedbackPreview').innerHTML = fbHtml;
                    document.getElementById('autofillPanel').classList.add('show');
                })
                .catch(() => {
                    document.getElementById('loadingText').style.display = 'none';
                });
        }

        <?php if ($laporan_data): ?>
            window.onload = function () {
                document.getElementById('select_kontrak').value = '<?= $laporan_data['kontrak_id'] ?>';
                loadData('<?= $laporan_data['kontrak_id'] ?>');
            };
        <?php endif; ?>
    </script>
</body>

</html>