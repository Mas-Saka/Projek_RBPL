<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'klien') {
    header("Location: login.php");
    exit;
}

$klien_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'] ?? 'Klien';
$foto_user = $_SESSION['foto_profil'] ?? null;

// Detail laporan jika dipilih
$laporan_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$detail = null;

if ($laporan_id) {
    $detail = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT l.*,
               k.nomor_kontrak, k.judul_kontrak, k.nilai_kontrak,
               s.judul_seminar, s.tanggal, s.jam_mulai, s.jam_selesai,
               s.platform, s.metode, s.kategori, s.narasumber_id,
               un.nama AS narasumber,
               ue.nama AS nama_eo
        FROM laporan_akhir l
        JOIN kontrak k ON l.kontrak_id = k.kontrak_id
        LEFT JOIN seminar s ON l.seminar_id = s.seminar_id
        LEFT JOIN users un ON s.narasumber_id = un.id
        JOIN users ue ON l.eo_id = ue.id
        WHERE l.laporan_id = $laporan_id AND l.klien_id = $klien_id
        LIMIT 1
    "));

    // feedback stats for this seminar
    if ($detail) {
        $sid = $detail['seminar_id'];
        $fb_stat = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT COUNT(*) AS total, ROUND(AVG(rating),1) AS avg_rating
            FROM feedback WHERE seminar_id=$sid AND status_validasi='valid'
        "));

        $fb_topik = mysqli_query($conn, "
            SELECT topik, COUNT(*) AS jml FROM feedback
            WHERE seminar_id=$sid AND status_validasi='valid'
            GROUP BY topik
        ");

        $fb_komentar = mysqli_query($conn, "
            SELECT f.komentar, f.rating, f.topik, u.nama
            FROM feedback f
            JOIN users u ON f.peserta_id = u.id
            WHERE f.seminar_id=$sid AND f.status_validasi='valid'
            ORDER BY f.id DESC
        ");
    }
}

// Daftar laporan
$laporan_list = mysqli_query($conn, "
    SELECT l.laporan_id, l.judul_laporan, l.tanggal_laporan, l.status_laporan,
           k.nomor_kontrak, ue.nama AS nama_eo
    FROM laporan_akhir l
    JOIN kontrak k ON l.kontrak_id = k.kontrak_id
    JOIN users ue ON l.eo_id = ue.id
    WHERE l.klien_id = $klien_id
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

        /* General Layout Elements */
        .welcome {
            background: linear-gradient(130deg, #1e3c72 0%, #2a5298 60%, #3498db 100%);
            border-radius: 12px;
            padding: 26px 28px;
            color: #fff;
            margin-bottom: 22px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .welcome h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .welcome p {
            font-size: 12.5px;
            color: rgba(255, 255, 255, .72);
        }

        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border: 1px solid #e8edf2;
            overflow: hidden;
            margin-bottom: 26px;
        }

        .table-head {
            padding: 18px 22px 14px;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-head h3 {
            font-size: 14.5px;
            font-weight: 700;
            color: #1a2634;
        }

        .table-head p {
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

        .td-sub {
            font-size: 11.5px;
            color: #95a5a6;
            margin-top: 2px;
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

        /* Disetujui/Terkirim */
        .badge-menunggu {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-ditolak {
            background: #fde8e8;
            color: #c0392b;
        }

        .btn-detail-sm {
            background: #f4f7f6;
            color: #2c3e50;
            border: 1px solid #e8edf2;
            border-radius: 7px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
        }

        .btn-detail-sm:hover {
            background: #e8edf2;
        }

        /* Detail View Specifics */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            padding: 22px;
        }

        .info-item {
            background: #f8fafc;
            border: 1px solid #e8edf2;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .info-item .i-label {
            font-size: 10px;
            color: #7f8c8d;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-item .i-value {
            font-size: 13.5px;
            font-weight: 600;
            color: #1a2634;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            padding: 0 22px 22px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border-left: 3px solid #3498db;
        }

        .stat-card.green {
            border-left-color: #27ae60;
        }

        .stat-card.amber {
            border-left-color: #e67e22;
        }

        .stat-num {
            font-size: 28px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 5px;
        }

        .content-box {
            padding: 22px;
            border-bottom: 1px solid #e8edf2;
        }

        .content-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1a2634;
            margin-bottom: 8px;
        }

        .content-text {
            font-size: 13px;
            color: #2c3e50;
            line-height: 1.6;
            background: #f8fafc;
            padding: 14px;
            border-radius: 8px;
            border: 1px solid #e8edf2;
        }

        .fb-item {
            padding: 14px;
            border-left: 3px solid #3498db;
            background: #f8fafc;
            border-radius: 0 8px 8px 0;
            margin-bottom: 12px;
            font-size: 13px;
            line-height: 1.5;
            color: #2c3e50;
            border-top: 1px solid #e8edf2;
            border-right: 1px solid #e8edf2;
            border-bottom: 1px solid #e8edf2;
        }

        .fb-meta {
            font-size: 11.5px;
            color: #7f8c8d;
            margin-top: 6px;
            font-weight: 500;
        }

        .stars {
            color: #f39c12;
            font-size: 14px;
        }

        /* Responsive */
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
        }

        @media (max-width:720px) {
            .content {
                padding: 18px 14px 40px;
            }

            .stats-row {
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
            <p>Portal Klien</p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Menu Utama</span>
            <a href="dashboardklien.php">Dashboard</a>
            <a href="datakontrak.php">Data Kontrak</a>
            <a href="lihat_laporan.php" class="active">Laporan Akhir</a>

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
                    <?= mb_strtoupper(mb_substr($nama_user, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <?= htmlspecialchars($nama_user) ?>
        </div>
    </div>

    <div class="main" id="main">
        <div class="content">

            <?php if ($detail): ?>
                <a href="lihat_laporan.php" class="btn-detail-sm" style="margin-bottom:18px;">&larr; Kembali ke Daftar</a>

                <div class="welcome">
                    <h2><?= htmlspecialchars($detail['judul_laporan']) ?></h2>
                    <p>Dilaporkan oleh <?= htmlspecialchars($detail['nama_eo']) ?> &middot; Dikirim pada
                        <?= date('d M Y', strtotime($detail['tanggal_laporan'])) ?> &middot; Nomor Kontrak:
                        <?= htmlspecialchars($detail['nomor_kontrak']) ?></p>
                </div>

                <div class="table-card">
                    <div class="table-head">
                        <div>
                            <h3>Informasi Seminar</h3>
                            <p>Detail pelaksanaan seminar berdasarkan kontrak kerja sama</p>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="i-label">Judul Seminar</div>
                            <div class="i-value"><?= htmlspecialchars($detail['judul_seminar'] ?? '-') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="i-label">Waktu Pelaksanaan</div>
                            <div class="i-value"><?= date('d/m/Y', strtotime($detail['tanggal'])) ?? '-' ?>
                                (<?= $detail['jam_mulai'] ?? '-' ?> - <?= $detail['jam_selesai'] ?? '-' ?>)</div>
                        </div>
                        <div class="info-item">
                            <div class="i-label">Narasumber</div>
                            <div class="i-value"><?= htmlspecialchars($detail['narasumber'] ?? '-') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="i-label">Platform & Metode</div>
                            <div class="i-value"><?= htmlspecialchars($detail['platform'] ?? '-') ?> &middot;
                                <?= htmlspecialchars($detail['metode'] ?? '-') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="i-label">Kategori</div>
                            <div class="i-value"><?= htmlspecialchars($detail['kategori'] ?? '-') ?></div>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-head" style="border-bottom: none; padding-bottom: 10px;">
                        <div>
                            <h3>Statistik Pelaksanaan</h3>
                            <p>Rangkuman partisipasi peserta dan kualitas acara</p>
                        </div>
                    </div>
                    <div class="stats-row">
                        <div class="stat-card">
                            <div class="stat-num"><?= $detail['peserta_hadir'] ?? '-' ?></div>
                            <div class="stat-label">Total Peserta Hadir</div>
                        </div>
                        <div class="stat-card green">
                            <div class="stat-num"><?= $fb_stat['total'] ?? 0 ?></div>
                            <div class="stat-label">Feedback Diterima</div>
                        </div>
                        <div class="stat-card amber">
                            <div class="stat-num">
                                <?php if ($fb_stat['avg_rating']): ?>
                                    <?= $fb_stat['avg_rating'] ?> <span class="stars">&#9733;</span>
                                <?php else:
                                    echo '-'; endif; ?>
                            </div>
                            <div class="stat-label">Rata-rata Rating</div>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-head">
                        <div>
                            <h3>Isi Laporan & Evaluasi</h3>
                        </div>
                    </div>

                    <div class="content-box">
                        <div class="content-title">Ringkasan Pelaksanaan</div>
                        <div class="content-text"><?= nl2br(htmlspecialchars($detail['ringkasan'] ?? '-')) ?></div>
                    </div>

                    <?php if ($detail['kendala']): ?>
                        <div class="content-box">
                            <div class="content-title">Kendala & Catatan</div>
                            <div class="content-text"><?= nl2br(htmlspecialchars($detail['kendala'])) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($detail['rekomendasi']): ?>
                        <div class="content-box">
                            <div class="content-title">Rekomendasi Tindak Lanjut</div>
                            <div class="content-text"><?= nl2br(htmlspecialchars($detail['rekomendasi'])) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($detail['catatan_tambahan']): ?>
                        <div class="content-box" style="border-bottom:none;">
                            <div class="content-title">Catatan Tambahan</div>
                            <div class="content-text"><?= nl2br(htmlspecialchars($detail['catatan_tambahan'])) ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="table-card">
                    <div class="table-head">
                        <div>
                            <h3>Feedback Valid dari Peserta</h3>
                        </div>
                    </div>
                    <div style="padding: 22px;">
                        <?php
                        $fb_list = [];
                        while ($fb = mysqli_fetch_assoc($fb_komentar)) {
                            $fb_list[] = $fb;
                        }
                        if (count($fb_list) == 0): ?>
                            <p style="color:#95a5a6; font-size:13px; text-align:center;">Belum ada feedback valid untuk seminar
                                ini.</p>
                        <?php else: ?>
                            <?php foreach ($fb_list as $fb): ?>
                                <div class="fb-item">
                                    "<?= htmlspecialchars($fb['komentar']) ?>"
                                    <div class="fb-meta">
                                        &mdash; <?= htmlspecialchars($fb['nama']) ?> &middot; Topik:
                                        <?= htmlspecialchars($fb['topik']) ?> &middot;
                                        <span class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?= $i <= $fb['rating'] ? '&#9733;' : '&#9734;' ?>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="table-card">
                    <div class="table-head">
                        <div>
                            <h3>Laporan Seminar Anda</h3>
                            <p>Daftar laporan akhir yang dikirimkan oleh Event Organizer</p>
                        </div>
                    </div>
                    <div class="tbl-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Laporan</th>
                                    <th>Kontrak</th>
                                    <th>Event Organizer</th>
                                    <th>Tanggal Lapor</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($laporan_list) == 0): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; color:#95a5a6; padding:30px;">
                                            Belum ada laporan yang dikirim untuk Anda.
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
                                                <div class="td-title"><?= htmlspecialchars($l['judul_laporan']) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($l['nomor_kontrak']) ?></td>
                                            <td><?= htmlspecialchars($l['nama_eo']) ?></td>
                                            <td><?= date('d M Y', strtotime($l['tanggal_laporan'])) ?></td>
                                            <td>
                                                <span class="tbl-badge <?= $status_class ?>">
                                                    <?= ucfirst($l['status_laporan']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="lihat_laporan.php?id=<?= $l['laporan_id'] ?>" class="btn-detail-sm">Lihat
                                                    Detail</a>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

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
    </script>
</body>

</html>