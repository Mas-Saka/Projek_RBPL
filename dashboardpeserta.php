<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

$peserta_id = $_SESSION['id'];

// Ambil seminar aktif
$seminar = mysqli_query($conn, "SELECT * FROM seminar WHERE status='aktif' LIMIT 3");
$total_seminar = mysqli_num_rows($seminar);

// Ambil pendaftaran peserta
$pendaftaran = mysqli_query($conn, "SELECT s.judul_seminar, p.status, p.kehadiran FROM pendaftaran p JOIN seminar s ON p.seminar_id = s.seminar_id WHERE p.peserta_id = $peserta_id");
$total_diikuti = mysqli_num_rows($pendaftaran);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peserta - SeminarOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        /* ================= RESET ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        /* ================= BODY ================= */
        body {
            background: #f1f5f9;
            color: #334155;
            display: flex;
            min-height: 100vh;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 240px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            padding: 30px 15px;
            position: fixed;
            height: 100%;
            transition: 0.3s;
        }

        .sidebar-brand {
            color: #2563eb;
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 30px;
            text-align: center;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            color: #64748b;
            border-radius: 8px;
            margin-bottom: 6px;
            font-size: 14px;
            transition: 0.25s;
        }

        .sidebar-menu a:hover {
            background: #eff6ff;
            color: #2563eb;
        }

        .sidebar-menu a.active {
            background: #2563eb;
            color: #fff;
        }

        .logout-link {
            margin-top: 30px;
            color: #ef4444 !important;
        }

        /* ================= MAIN CONTENT ================= */
        .main-content {
            margin-left: 240px;
            width: calc(100% - 240px);
            padding: 40px;
        }

        /* ================= TOP BAR ================= */
        .top-bar {
            margin-bottom: 30px;
        }

        .user-greet h2 {
            font-size: 22px;
            color: #1e293b;
        }

        .user-greet p {
            font-size: 14px;
            color: #94a3b8;
        }

        /* ================= STATS ================= */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .stat-card h3 {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .value {
            font-size: 26px;
            font-weight: 700;
            color: #1e293b;
        }

        /* ================= SECTION TITLE ================= */
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: #2563eb;
            border-radius: 10px;
        }

        /* ================= CARD ================= */
        .seminar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.07);
        }

        .card-body {
            padding: 20px;
        }

        .card-body h4 {
            font-size: 15px;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .card-body p {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 15px;
            height: 40px;
            overflow: hidden;
        }

        /* ================= BUTTON ================= */
        .btn-daftar {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-daftar:hover {
            opacity: 0.9;
        }

        /* Ripple */
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* ================= TABLE ================= */
        .table-box {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f8fafc;
            padding: 15px;
            font-size: 13px;
            color: #64748b;
            text-align: left;
        }

        table td {
            padding: 15px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        table tr:hover {
            background: #f8fafc;
        }

        /* Badge */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-pending {
            background: #fef3c7;
            color: #d97706;
        }

        /* ================= ANIMATION ================= */
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            transition: 0.6s ease;
        }

        .fade-up.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* ================= DETAIL PAGE ================= */
        .btn-lihat {
            display: inline-block;
            padding: 12px 25px;
            background: #3498db;
            color: #fff;
            border-radius: 5px;
            font-weight: 100;
            transition: 0.3s;
            margin-bottom: 30px;
        }

        .btn-lihat:hover {
            background: #2980b9;
        }
        /* ================= MOBILE ================= */
        .menu-btn {
            display: none;
        }

        @media (max-width:768px) {

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .menu-btn {
                display: block;
                font-size: 22px;
                margin-bottom: 20px;
                cursor: pointer;
            }

        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-brand">SeminarOnline</div>
        <div class="sidebar-menu">
            <a href="#" class="active">Dashboard</a>
            <a href="#seminar">Jelajahi Seminar</a>
            <a href="#status">Status Saya</a>
            <a href="index.php">Kembali ke Home</a>
            <a href="logout.php" class="logout-link">Keluar Akun</a>
        </div>
    </div>

    <div class="main-content">

        <div class="top-bar">
            <div class="user-greet">
                <h2>Halo, <?php echo $_SESSION['nama']; ?>!</h2>
                <p>Senang melihatmu kembali. Mau belajar apa hari ini?</p>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Seminar Tersedia</h3>
                <div class="value"><?php echo $total_seminar; ?></div>
            </div>
            <div class="stat-card">
                <h3>Seminar Diikuti</h3>
                <div class="value"><?php echo $total_diikuti; ?></div>
            </div>
        </div>

        <h3 class="section-title" id="seminar">Seminar Untuk Kamu</h3>
        <div class="seminar-grid">
            <?php while ($row = mysqli_fetch_assoc($seminar)): ?>
                <div class="card">
                    <div class="card-body">
                        <h4><?php echo $row['judul_seminar']; ?></h4>
                        <p><?php echo $row['deskripsi']; ?></p>
                        <div style="font-size: 12px; color: #94a3b8; margin-bottom: 15px;">
                            Tanggal: <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </div>
                        <a href="daftar.php?id=<?php echo $row['seminar_id']; ?>" class="btn-daftar">Ikuti Seminar</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
         <div style="text-align:center; margin-top:30px;">
                <a href="semua_seminar.php" class="btn-lihat">Lihat Seminar Lainnya</a>
        </div>


        <h3 class="section-title" id="status">Status Pendaftaran Saya</h3>
        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>Judul Seminar</th>
                        <th>Status</th>
                        <th>Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($pendaftaran)): ?>
                        <tr>
                            <td style="font-weight: 500;"><?php echo $p['judul']; ?></td>
                            <td>
                                <span
                                    class="badge <?php echo ($p['status'] == 'aktif') ? 'badge-success' : 'badge-pending'; ?>">
                                    <?php echo $p['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $p['kehadiran'] ? $p['kehadiran'] : '<span style="color:#cbd5e1">-</span>'; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>