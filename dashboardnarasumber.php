<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'narasumber') {
    header("Location: login.php");
    exit;
}

$narasumber_id = $_SESSION['id'];

// Data undangan
$undangan = mysqli_query($conn, "SELECT judul_seminar, tanggal, status FROM seminar WHERE narasumber_id = $narasumber_id");

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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Narasumber</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #eef2f7;
        }

        /* TOPBAR */
        .topbar {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 16px 22px;
            display: flex;
            align-items: center;
            font-size: 18px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* BURGER */
        .burger {
            font-size: 20px;
            cursor: pointer;
            margin-right: 15px;
            transition: 0.2s;
        }

        .burger:hover {
            transform: scale(1.1);
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 230px;
            height: 100%;
            background: #2c3e50;
            padding: 25px 20px;
            transition: 0.3s;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.2);
        }

        .sidebar.hide {
            left: -250px;
        }

        .sidebar h3 {
            color: white;
            margin-bottom: 25px;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        /* MENU */
        .sidebar a {
            display: block;
            color: #cbd5e1;
            margin: 10px 0;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            transition: 0.25s;
            font-size: 15px;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        /* MAIN */
        .main {
            margin-left: 270px;
            padding: 30px;
            transition: 0.3s;
        }

        .main.full {
            margin-left: 0;
        }

        /* TITLE */
        h2 {
            margin-bottom: 20px;
            color: #1e293b;
            font-weight: 600;
        }

        /* CARD */
        .card {
            background: white;
            padding: 22px;
            border-radius: 14px;
            margin-bottom: 22px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transition: 0.25s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        /* STATS */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .stats .card {
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stats .card::after {
            content: "";
            position: absolute;
            right: -20px;
            bottom: -20px;
            width: 80px;
            height: 80px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 50%;
        }

        .stats h3 {
            margin: 0;
            font-size: 28px;
            color: #2563eb;
        }

        .stats p {
            margin-top: 6px;
            font-size: 13px;
            color: #64748b;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        /* HEADER */
        th {
            text-align: left;
            padding: 13px;
            font-size: 13px;
            background: #f1f5f9;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        /* DATA */
        td {
            padding: 13px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #334155;
        }

        /* ROW EFFECT */
        tr {
            transition: 0.2s;
        }

        tr:hover {
            background: #f8fafc;
            transform: scale(1.002);
        }

        /* STATUS BADGE */
        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        /* STATUS COLORS */
        .status-aktif {
            background: #dcfce7;
            color: #166534;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        /* SCROLL TABLE */
        .table-wrapper {
            overflow-x: auto;
        }

        /* SCROLLBAR */
        .table-wrapper::-webkit-scrollbar {
            height: 6px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .main {
                margin-left: 0;
            }

            .sidebar {
                left: -250px;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar" id="sidebar">
        <h3>Narasumber Panel</h3>
        <a href="dashboardnarasumber.php">Dashboard</a>
        <a href="#">Undangan Seminar</a>
        <a href="#">Upload Materi</a>
        <a href="#">Lihat Feedback</a>
        <a href="logout.php" style=" width:;color:red;  padding:8px 12px; border-radius:8px;
            transition:0.3s;" onmouseover="this.style.background='red'; this.style.color='white';"
            onmouseout="this.style.background='transparent'; this.style.color='red';" >Logout</a>
    </div>

    <div class="main" id="main">

        <div class="topbar">
            <div class="burger" onclick="toggleMenu()">☰</div>
            Dashboard Narasumber - Sistem Manajemen Seminar
        </div>

        <h2>Statistik Anda</h2>

        <div class="stats">
            <div class="card">
                <h3><?= $total_seminar ?></h3>
                <p>Total Seminar</p>
            </div>

            <div class="card">
                <h3><?= $seminar_aktif ?></h3>
                <p>Seminar Aktif</p>
            </div>

            <div class="card">
                <h3><?= $total_feedback ?></h3>
                <p>Total Feedback</p>
            </div>
        </div>

        <div class="card">
            <h3>Daftar Seminar Anda</h3>
            <p>Seminar yang Anda isi sebagai narasumber.</p>

            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>Judul Seminar</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>

                    <?php while ($u = mysqli_fetch_assoc($undangan)) { ?>
                        <tr>
                            <td><?= $u['judul_seminar']; ?></td>
                            <td><?= $u['tanggal']; ?></td>
                            <td>
                                <?php if ($u['status'] == 'aktif') { ?>
                                    <span class="status status-aktif">Aktif</span>
                                <?php } else { ?>
                                    <span class="status status-draft">Draft</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>

        <script>
            function toggleMenu() {
                document.getElementById("sidebar").classList.toggle("hide");
                document.getElementById("main").classList.toggle("full");
            }
        </script>

</body>

</html>