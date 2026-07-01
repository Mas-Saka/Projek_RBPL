<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'narasumber') {
    header("Location: login.php");
    exit;
}

$narasumber_id = $_SESSION['id'];

// Terima undangan
if (isset($_POST['terima'])) {
    $seminar_id = (int) $_POST['seminar_id'];
    mysqli_query($conn, "UPDATE seminar SET undangan_status='diterima', status='aktif' WHERE seminar_id=$seminar_id AND narasumber_id=$narasumber_id");
    echo "<script>alert('Undangan berhasil diterima. Seminar sekarang bisa dilaksanakan.'); window.location='undangan_seminar.php';</script>";
    exit;
}

// Tolak undangan
if (isset($_POST['tolak'])) {
    $seminar_id = (int) $_POST['seminar_id'];
    $alasan = mysqli_real_escape_string($conn, trim($_POST['alasan_tolak']));
    if ($alasan == '') {
        echo "<script>alert('Mohon isi alasan penolakan.'); history.back();</script>";
        exit;
    }
    mysqli_query($conn, "UPDATE seminar SET undangan_status='ditolak', alasan_tolak='$alasan' WHERE seminar_id=$seminar_id AND narasumber_id=$narasumber_id");
    echo "<script>alert('Undangan ditolak.'); window.location='undangan_seminar.php';</script>";
    exit;
}

// Ambil semua undangan
$undangan = mysqli_query($conn, "
    SELECT s.*, u.nama AS nama_eo
    FROM seminar s
    JOIN users u ON s.eo_id = u.id
    WHERE s.narasumber_id = $narasumber_id
    ORDER BY s.seminar_id DESC
");

$rows = [];
while ($r = mysqli_fetch_assoc($undangan)) {
    $rows[] = $r;
}

$total = count($rows);
$menunggu = count(array_filter($rows, fn($r) => $r['undangan_status'] === 'menunggu'));
$diterima = count(array_filter($rows, fn($r) => $r['undangan_status'] === 'diterima'));
$ditolak = count(array_filter($rows, fn($r) => $r['undangan_status'] === 'ditolak'));

$nama_narasumber = htmlspecialchars($_SESSION['nama'] ?? 'Narasumber');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Seminar</title>
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
            color: #333;
            min-height: 100vh;
        }

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
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }

        .sidebar-brand h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
        }

        .sidebar-brand p {
            color: #7f8c9a;
            font-size: 11px;
            margin-top: 3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-label {
            color: #5a6a78;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 12px;
            margin: 12px 0 6px;
            display: block;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            color: #9baab7;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 2px;
            transition: all 0.2s;
        }

        .sidebar-nav a:hover {
            background: rgba(52, 152, 219, 0.12);
            color: #3498db;
        }

        .sidebar-nav a.active {
            background: rgba(52, 152, 219, 0.18);
            color: #3498db;
            font-weight: 600;
        }

        .sidebar-nav a.logout {
            color: #e74c3c;
            margin-top: 4px;
        }

        .sidebar-nav a.logout:hover {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            font-size: 11px;
            color: #5a6a78;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            height: 60px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            box-shadow: 0 1px 0 #e8edf2;
            z-index: 90;
            transition: left 0.3s;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .burger-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: none;
            flex-direction: column;
            gap: 5px;
            padding: 4px;
        }

        .burger-btn span {
            display: block;
            width: 22px;
            height: 2px;
            background: #555;
            border-radius: 2px;
        }

        .topbar-title {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
        }

        .user-chip {
            background: #eef2f7;
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 12.5px;
            color: #2c3e50;
            font-weight: 500;
        }

        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        .content {
            padding: 30px 28px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 14px;
            padding: 26px 30px;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .wb-text h2 {
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .wb-text p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.72);
        }

        .wb-badge {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 10px 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .wb-role {
            font-size: 10px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.65);
        }

        .wb-name {
            font-size: 14px;
            font-weight: 700;
            margin-top: 2px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            border-left: 3px solid #3498db;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-card.kuning {
            border-left-color: #f39c12;
        }

        .stat-card.hijau {
            border-left-color: #27ae60;
        }

        .stat-card.merah {
            border-left-color: #e74c3c;
        }

        .stat-num {
            font-size: 28px;
            font-weight: 700;
            color: #1a2634;
        }

        .stat-label {
            font-size: 12px;
            color: #7f8c9a;
            margin-top: 3px;
            font-weight: 500;
        }

        .section-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f3f7;
        }

        .section-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1a2634;
        }

        .section-header p {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 2px;
        }

        .section-count {
            background: #eef2f7;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            color: #2c3e50;
            font-weight: 600;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th {
            padding: 12px 18px;
            font-size: 11px;
            font-weight: 700;
            color: #95a5a6;
            text-transform: uppercase;
            border-bottom: 1px solid #eef0f4;
            white-space: nowrap;
            text-align: left;
        }

        td {
            padding: 14px 18px;
            font-size: 13.5px;
            color: #2c3e50;
            border-bottom: 1px solid #f4f7f6;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr {
            transition: background 0.15s;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .td-title {
            font-weight: 600;
            color: #1a2634;
        }

        .td-meta {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 2px;
        }

        .td-date {
            font-size: 13px;
            color: #555;
            white-space: nowrap;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-menunggu {
            background: #fff3cd;
            color: #856404;
        }

        .badge-diterima {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-ditolak {
            background: #fce4e4;
            color: #c0392b;
        }

        .btn-detail {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            margin-right: 6px;
        }

        .btn-detail:hover {
            background: #2980b9;
        }

        .btn-terima,
        .btn-tolak {
            border: none;
            padding: 7px 14px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: 0.2s;
        }

        .btn-terima {
            background: #27ae60;
            color: #fff;
            margin-right: 6px;
        }

        .btn-terima:hover {
            background: #1e8449;
        }

        .btn-tolak {
            background: #fce4e4;
            color: #c0392b;
        }

        .btn-tolak:hover {
            background: #e74c3c;
            color: #fff;
        }

        .alasan-box {
            background: #fff8f8;
            border-left: 3px solid #e74c3c;
            border-radius: 0 6px 6px 0;
            padding: 8px 12px;
            font-size: 12px;
            color: #555;
            max-width: 260px;
            margin-top: 8px;
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 300;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 14px;
            padding: 30px 28px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
        }

        .modal-box h4 {
            font-size: 16px;
            font-weight: 700;
            color: #1a2634;
            margin-bottom: 6px;
        }

        .modal-box p {
            font-size: 13px;
            color: #7f8c9a;
            margin-bottom: 18px;
        }

        .modal-box textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d9e1ea;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #333;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 18px;
            transition: border-color 0.2s;
        }

        .modal-box textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-batal {
            background: #eef2f7;
            color: #555;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: 0.2s;
        }

        .btn-batal:hover {
            background: #dce3ec;
        }

        .btn-kirim-tolak {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-kirim-tolak:hover {
            background: #c0392b;
        }

        .empty-row td {
            text-align: center;
            padding: 50px 20px;
            color: #bdc3c7;
            font-size: 13px;
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 80;
        }

        .overlay.show {
            display: block;
        }

        @media (max-width: 900px) {
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

            .burger-btn {
                display: flex;
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .welcome-banner {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 600px) {
            .stats-row {
                grid-template-columns: 1fr 1fr;
            }

            .content {
                padding: 20px 16px;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Portal Narasumber</p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Menu</span>
            <a href="dashboardnarasumber.php">Dashboard</a>
            <a href="undangan_seminar.php" class="active">Undangan Seminar</a>
            <a href="upload_materi.php">Upload Materi</a>
            <a href="narasumber_feedback.php">Lihat Feedback</a>
            <span class="nav-label" style="margin-top:16px"></span>
            <a href="logout.php" class="logout">Keluar</a>
        </div>
        <div class="sidebar-footer">&copy; 2026 Sistem Manajemen Seminar Online</div>
    </nav>

    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="burger-btn" onclick="toggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <span class="topbar-title">Undangan Seminar</span>
        </div>
        <div>
            <span class="user-chip"><?= $nama_narasumber ?></span>
        </div>
    </div>

    <div class="main" id="main">
        <div class="content">

            <div class="welcome-banner">
                <div class="wb-text">
                    <h2>Undangan Seminar Anda</h2>
                    <p>Terima atau tolak undangan dari Event Organizer di bawah ini.</p>
                </div>
                <div class="wb-badge">
                    <div class="wb-role">Narasumber</div>
                    <div class="wb-name"><?= $nama_narasumber ?></div>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-num"><?= $total ?></div>
                    <div class="stat-label">Total Undangan</div>
                </div>
                <div class="stat-card kuning">
                    <div class="stat-num"><?= $menunggu ?></div>
                    <div class="stat-label">Menunggu Respons</div>
                </div>
                <div class="stat-card hijau">
                    <div class="stat-num"><?= $diterima ?></div>
                    <div class="stat-label">Diterima</div>
                </div>
                <div class="stat-card merah">
                    <div class="stat-num"><?= $ditolak ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <div>
                        <h3>Daftar Undangan</h3>
                        <p>Semua undangan yang dikirim Event Organizer kepada Anda</p>
                    </div>
                    <span class="section-count"><?= $total ?> undangan</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul Seminar</th>
                                <th>Event Organizer</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Platform</th>
                                <th>Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rows) === 0): ?>
                                <tr class="empty-row">
                                    <td colspan="7">Belum ada undangan seminar untuk Anda.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rows as $r):
                                    $tgl = $r['tanggal'] ? date('d M Y', strtotime($r['tanggal'])) : '-';
                                    $waktu = '-';
                                    if ($r['jam_mulai'] && $r['jam_selesai']) {
                                        $waktu = date('H:i', strtotime($r['jam_mulai'])) . ' – ' . date('H:i', strtotime($r['jam_selesai'])) . ' WIB';
                                    }
                                    $us = $r['undangan_status'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="td-title"><?= htmlspecialchars($r['judul_seminar']) ?></div>
                                            <?php if ($r['kategori']): ?>
                                                <div class="td-meta"><?= htmlspecialchars($r['kategori']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="td-date"><?= htmlspecialchars($r['nama_eo']) ?></td>
                                        <td class="td-date"><?= $tgl ?></td>
                                        <td class="td-date"><?= $waktu ?></td>
                                        <td class="td-date"><?= htmlspecialchars($r['platform']) ?></td>
                                        <td>
                                            <?php if ($us === 'menunggu'): ?>
                                                <span class="badge badge-menunggu">Menunggu</span>
                                            <?php elseif ($us === 'diterima'): ?>
                                                <span class="badge badge-diterima">Diterima</span>
                                            <?php else: ?>
                                                <span class="badge badge-ditolak">Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:center; white-space:nowrap;">
                                            <a href="detail_seminar.php?id=<?= $r['seminar_id'] ?>" class="btn-detail">Detail</a>

                                            <?php if ($us === 'menunggu'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="seminar_id" value="<?= $r['seminar_id'] ?>">
                                                    <button type="submit" name="terima" class="btn-terima"
                                                        onclick="return confirm('Terima undangan seminar ini?')">Terima</button>
                                                </form>
                                                <button class="btn-tolak"
                                                    onclick="bukaModalTolak(<?= $r['seminar_id'] ?>, '<?= htmlspecialchars(addslashes($r['judul_seminar'])) ?>')">
                                                    Tolak
                                                </button>
                                            <?php elseif ($us === 'ditolak' && $r['alasan_tolak']): ?>
                                                <div class="alasan-box">
                                                    <strong style="font-size:11px;color:#e74c3c;">Alasan penolakan:</strong><br>
                                                    <?= htmlspecialchars($r['alasan_tolak']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="modal-backdrop" id="modalTolak">
        <div class="modal-box">
            <h4>Tolak Undangan</h4>
            <p id="modalSubjudul">Berikan alasan penolakan untuk seminar ini.</p>
            <form method="POST" id="formTolak">
                <input type="hidden" name="seminar_id" id="inputSeminarId">
                <textarea name="alasan_tolak" id="inputAlasan"
                    placeholder="Contoh: Jadwal bentrok dengan acara lain, tidak sesuai bidang keahlian, dsb."
                    required></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn-batal" onclick="tutupModal()">Batal</button>
                    <button type="submit" name="tolak" class="btn-kirim-tolak">Kirim Penolakan</button>
                </div>
            </form>
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
        function bukaModalTolak(seminarId, judul) {
            document.getElementById('inputSeminarId').value = seminarId;
            document.getElementById('inputAlasan').value = '';
            document.getElementById('modalSubjudul').textContent = 'Berikan alasan penolakan untuk: ' + judul;
            document.getElementById('modalTolak').classList.add('show');
        }
        function tutupModal() {
            document.getElementById('modalTolak').classList.remove('show');
        }
        document.getElementById('modalTolak').addEventListener('click', function (e) {
            if (e.target === this) tutupModal();
        });
    </script>

</body>

</html>