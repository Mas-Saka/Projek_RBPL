<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'narasumber') {
    header("Location: login.php");
    exit;
}

$narasumber_id = $_SESSION['id'];

// Data undangan
$undangan = mysqli_query($conn, "
    SELECT judul_seminar, tanggal, jam_mulai, jam_selesai, status, kategori
    FROM seminar 
    WHERE narasumber_id = $narasumber_id
    ORDER BY tanggal DESC
");

// Statistik
$total_seminar = mysqli_num_rows($undangan);

$seminar_aktif = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM seminar 
     WHERE narasumber_id=$narasumber_id AND status='aktif'"
))['total'];

$total_feedback = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM feedback
     JOIN seminar ON feedback.seminar_id = seminar.seminar_id
     WHERE seminar.narasumber_id=$narasumber_id"
))['total'];

// Simpan rows ke array
$rows = [];
while ($u = mysqli_fetch_assoc($undangan)) {
    $rows[] = $u;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Narasumber</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        /* ============ SIDEBAR ============ */
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
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        .sidebar-brand h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.4px;
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
            background: rgba(231,76,60,0.1);
            color: #c0392b;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,0.06);
            font-size: 11px;
            color: #5a6a78;
        }

        /* ============ TOPBAR ============ */
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

        /* ============ MAIN ============ */
        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        .content {
            padding: 30px 28px;
        }

        /* ============ WELCOME BANNER ============ */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 14px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .wb-text h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .wb-text p {
            font-size: 13px;
            color: rgba(255,255,255,0.75);
        }

        .wb-badge {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 10px 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .wb-badge .wb-role {
            font-size: 10px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.65);
        }

        .wb-badge .wb-name {
            font-size: 14px;
            font-weight: 700;
            margin-top: 2px;
        }

        /* ============ STATS ============ */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            border-left: 3px solid #3498db;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.green { border-left-color: #27ae60; }
        .stat-card.orange { border-left-color: #e67e22; }

        .stat-num {
            font-size: 30px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 6px;
        }

        /* ============ SECTION CARD ============ */
        .section-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .section-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f0f3f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1a2634;
        }

        .section-header p {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 3px;
        }

        .section-count {
            background: #eef2f7;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #2c3e50;
        }

        /* ============ TABLE ============ */
        .table-wrapper {
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
            text-align: left;
            padding: 13px 18px;
            font-size: 11.5px;
            font-weight: 700;
            color: #7f8c8d;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            border-bottom: 1px solid #eef0f4;
            white-space: nowrap;
        }

        td {
            padding: 14px 18px;
            font-size: 13.5px;
            color: #2c3e50;
            border-bottom: 1px solid #f4f7f6;
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

        /* ============ BADGE ============ */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-aktif {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-draft {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-selesai {
            background: #eaecee;
            color: #555;
        }

        /* ============ EMPTY ============ */
        .empty-row td {
            text-align: center;
            padding: 50px 20px;
            color: #bdc3c7;
            font-size: 13px;
        }

        /* ============ OVERLAY ============ */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 80;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .overlay.show {
                display: block;
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
                grid-template-columns: repeat(3, 1fr);
            }

            .welcome-banner {
                flex-direction: column;
                align-items: flex-start;
            }

            .wb-badge {
                align-self: flex-start;
            }
        }

        @media (max-width: 600px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 20px 16px;
            }

            th, td {
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h3>SeminarOnline</h3>
        <p>Portal Narasumber</p>
    </div>
    <div class="sidebar-nav">
        <span class="nav-label">Menu</span>
        <a href="dashboardnarasumber.php" class="active">Dashboard</a>
        <a href="undangan_seminar.php">Undangan Seminar</a>
        <a href="upload_materi.php">Upload Materi</a>
        <a href="seminar_selesai.php">Tandai     Selesai</a>
        <a href="narasumber_feedback.php">Lihat Feedback</a>

        <span class="nav-label" style="margin-top:16px"></span>
        <a href="logout.php" class="logout">Keluar</a>
    </div>
    <div class="sidebar-footer">
        &copy; 2026 Sistem Manajemen Seminar Online
    </div>
</nav>

<!-- TOPBAR -->
<div class="topbar" id="topbar">
    <div class="topbar-left">
        <button class="burger-btn" onclick="toggleSidebar()">
            <span></span><span></span><span></span>
        </button>
        <span class="topbar-title">Dashboard Narasumber</span>
    </div>
    <div>
        <span class="user-chip"><?= htmlspecialchars($_SESSION['nama'] ?? 'Narasumber') ?></span>
    </div>
</div>

<!-- MAIN -->
<div class="main" id="main">
    <div class="content">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="wb-text">
                <h2>Selamat datang kembali!</h2>
                <p>Berikut ringkasan aktivitas seminar Anda sebagai narasumber.</p>
            </div>
            <div class="wb-badge">
                <div class="wb-role">Narasumber</div>
                <div class="wb-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Narasumber') ?></div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-num"><?= $total_seminar ?></div>
                <div class="stat-label">Total Seminar</div>
            </div>
            <div class="stat-card green">
                <div class="stat-num"><?= $seminar_aktif ?></div>
                <div class="stat-label">Seminar Aktif</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-num"><?= $total_feedback ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="section-card">
            <div class="section-header">
                <div>
                    <h3>Daftar Seminar Anda</h3>
                    <p>Seminar yang Anda isi sebagai narasumber</p>
                </div>
                <span class="section-count"><?= $total_seminar ?> seminar</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Seminar</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) === 0): ?>
                        <tr class="empty-row">
                            <td colspan="4">Belum ada seminar yang ditugaskan kepada Anda.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rows as $u):
                            $tgl = $u['tanggal'] ? date('d M Y', strtotime($u['tanggal'])) : '-';
                            $waktu = '-';
                            if ($u['jam_mulai'] && $u['jam_selesai']) {
                                $waktu = date('H:i', strtotime($u['jam_mulai'])) . ' – ' . date('H:i', strtotime($u['jam_selesai'])) . ' WIB';
                            }
                            $status = strtolower($u['status']);
                            $badge_class = $status === 'aktif' ? 'badge-aktif' : ($status === 'selesai' ? 'badge-selesai' : 'badge-draft');
                            $badge_text = $status === 'aktif' ? 'Aktif' : ($status === 'selesai' ? 'Selesai' : 'Draft');
                        ?>
                        <tr>
                            <td>
                                <div class="td-title"><?= htmlspecialchars($u['judul_seminar']) ?></div>
                                <?php if ($u['kategori']): ?>
                                <div class="td-meta"><?= htmlspecialchars($u['kategori']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="td-date"><?= $tgl ?></td>
                            <td class="td-date"><?= $waktu ?></td>
                            <td><span class="badge <?= $badge_class ?>"><?= $badge_text ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
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
</script>

</body>
</html>