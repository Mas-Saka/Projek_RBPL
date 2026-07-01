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

$seminar = mysqli_query($conn, "
    SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.status,
           COUNT(p.id) as total_peserta
    FROM seminar s
    LEFT JOIN pendaftaran p ON s.seminar_id = p.seminar_id
    WHERE s.eo_id = $eo_id
    GROUP BY s.seminar_id
    ORDER BY s.seminar_id DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peserta — SeminarOnline</title>
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

        /* Card & Table */
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

        /* Badges & Buttons */
        .tbl-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
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
            background: #e8edf2;
            color: #2c3e50;
        }

        .btn-detail-sm {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
            font-family: 'Poppins', sans-serif;
        }

        .btn-detail-sm:hover {
            background: #2980b9;
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
        }

        @media (max-width:720px) {
            .content {
                padding: 18px 14px 40px;
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
            <a href="data_peserta.php" class="active">Data Peserta</a>
            <a href="feedback_eo.php">Data Feedback</a>
            <a href="buat_laporan.php">Laporan</a>

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
            <span class="topbar-title">Data Peserta</span>
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
                        <h3>Rekap Peserta Seminar</h3>
                        <p>Daftar seluruh seminar beserta jumlah peserta yang terdaftar</p>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Judul Seminar</th>
                                <th>Tanggal Pelaksanaan</th>
                                <th>Status Seminar</th>
                                <th>Jumlah Peserta</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($s = mysqli_fetch_assoc($seminar)):
                                $status_class = 'badge-draft';
                                if ($s['status'] == 'aktif')
                                    $status_class = 'badge-aktif';
                                elseif ($s['status'] == 'selesai')
                                    $status_class = 'badge-selesai';
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <div class="td-title"
                                            style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                                            title="<?= htmlspecialchars($s['judul_seminar']) ?>">
                                            <?= htmlspecialchars($s['judul_seminar']); ?>
                                        </div>
                                    </td>
                                    <td><?= $s['tanggal'] ? date('d M Y', strtotime($s['tanggal'])) : '-'; ?></td>
                                    <td>
                                        <span class="tbl-badge <?= $status_class ?>">
                                            <?= ucfirst($s['status']); ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600; color: #2a5298;">
                                        <?= $s['total_peserta']; ?> Orang
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="detail_peserta.php?id=<?= $s['seminar_id']; ?>" class="btn-detail-sm">Lihat
                                            Detail Peserta</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if (mysqli_num_rows($seminar) == 0): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:30px; color:#95a5a6;">Belum ada data
                                        peserta seminar.</td>
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
    </script>
</body>

</html>