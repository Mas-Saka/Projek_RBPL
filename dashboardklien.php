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

// Query diperbarui untuk mengambil nama EO agar Klien tahu kontrak dikirim ke siapa
$kontrak = mysqli_query($conn, "
    SELECT k.*, u.nama as nama_eo 
    FROM kontrak k
    JOIN users u ON k.eo_id = u.id
    WHERE k.klien_id = $klien_id
    ORDER BY k.kontrak_id DESC
");

// Hitung statistik
$total_kontrak = mysqli_num_rows($kontrak);

$kontrak_disetujui = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM kontrak 
     WHERE klien_id=$klien_id AND status_kontrak='disetujui'"
))['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Klien — SeminarOnline</title>
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

        /* Welcome Box */
        .welcome {
            background: linear-gradient(130deg, #1e3c72 0%, #2a5298 60%, #3498db 100%);
            border-radius: 12px;
            padding: 26px 28px;
            color: #fff;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .welcome-text h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .welcome-text p {
            font-size: 12.5px;
            color: rgba(255, 255, 255, .72);
        }

        .welcome-badge {
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 10px;
            padding: 10px 18px;
            text-align: center;
        }

        .welcome-badge .wb-label {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .6);
        }

        .welcome-badge .wb-date {
            font-size: 13px;
            font-weight: 600;
            margin-top: 3px;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 24px;
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

        /* Buttons */
        .btn-daftar {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            cursor: pointer;
            transition: background .18s;
        }

        .btn-daftar:hover {
            background: #2980b9;
        }

        .btn-detail-sm {
            background: #cbcecf;
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
            background: #babec2;
        }

        /* Table Card */
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

        .badge-menunggu {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-ditolak {
            background: #fde8e8;
            color: #c0392b;
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
            .stats-row {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 18px 14px 40px;
            }

            .welcome-badge {
                display: none;
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
            <a href="dashboardklien.php" class="active">Dashboard</a>
            <a href="datakontrak.php">Data Kontrak</a>
            <a href="lihat_laporan.php">Laporan Akhir</a>

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
            <span class="topbar-title">Dashboard Klien</span>
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

            <div class="welcome">
                <div class="welcome-text">
                    <h2>Halo, <?= htmlspecialchars(explode(' ', $nama_user)[0]) ?>!</h2>
                    <p>Selamat datang di dashboard klien. Pantau pengajuan kontrak dan laporan seminar Anda di sini.</p>
                </div>
                <div class="welcome-badge">
                    <div class="wb-label">Hari ini</div>
                    <div class="wb-date" id="todayDate">—</div>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-num"><?= $total_kontrak ?></div>
                    <div class="stat-label">Total Pengajuan</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-num"><?= $kontrak_disetujui ?></div>
                    <div class="stat-label">Kontrak Disetujui</div>
                </div>
                <div class="stat-card"
                    style="border-left-color: transparent; box-shadow: none; background: transparent; display: flex; align-items: center; justify-content: flex-end;">
                    <a href="buat_kontrak.php" class="btn-daftar" style="padding: 12px 24px; font-size: 14px;">+ Buat
                        Kontrak Baru</a>
                </div>
            </div>

            <div class="table-card">
                <div class="table-head">
                    <div>
                        <h3>Daftar Pengajuan Kontrak</h3>
                        <p>Status terkini kontrak kerja sama Anda dengan EO</p>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nomor Kontrak</th>
                                <th>Ditujukan Ke (EO)</th>
                                <th>Tanggal Buat</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($kontrak) > 0): ?>
                                <?php while ($k = mysqli_fetch_assoc($kontrak)):
                                    $status_class = 'badge-menunggu';
                                    $status_text = 'Menunggu';

                                    if ($k['status_kontrak'] == 'disetujui') {
                                        $status_class = 'badge-diterima';
                                        $status_text = 'Disetujui';
                                    } elseif ($k['status_kontrak'] == 'ditolak') {
                                        $status_class = 'badge-ditolak';
                                        $status_text = 'Ditolak';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="td-title"><?= htmlspecialchars($k['nomor_kontrak']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($k['nama_eo']) ?></td>
                                        <td><?= date('d M Y', strtotime($k['tanggal_buat'])) ?></td>
                                        <td><span class="tbl-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                                        <td>
                                            <a href="detail_kontrak.php?id=<?= $k['kontrak_id']; ?>" class="btn-detail-sm">Lihat
                                                Detail</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 30px; color: #95a5a6;">Belum ada
                                        pengajuan kontrak.</td>
                                </tr>
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

        (function () {
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            var d = new Date();
            document.getElementById('todayDate').textContent = days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()];
        })();
    </script>
</body>

</html>