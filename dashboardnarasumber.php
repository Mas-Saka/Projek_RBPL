<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'narasumber') {
    header("Location: login.php");
    exit;
}

$narasumber_id = $_SESSION['id'];

// Data undangan
$undangan = mysqli_query($conn, "SELECT seminar.judul_seminar, seminar.tanggal, seminar.status FROM seminar WHERE seminar.narasumber_id = $narasumber_id");

// Statistik
$total_seminar = mysqli_num_rows($undangan);

$total_peserta = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM pendaftaran 
     JOIN seminar ON pendaftaran.seminar_id = seminar.seminar_id
     WHERE seminar.narasumber_id=$narasumber_id"
))['total'];

$seminar_aktif = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM seminar 
     WHERE narasumber_id=$narasumber_id AND status='aktif'"
))['total'];

$total_feedback = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM feedback
     JOIN seminar ON feedback.seminar_id = seminar.seminar_id
     WHERE seminar.narasumber_id=$narasumber_id"
))['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Narasumber</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
        }

        .topbar {
            height: 60px;
            background: #1e3c72;
            display: flex;
            align-items: center;
            padding: 0 20px;
            color: white;
        }


        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 180px;
            height: 100%;
            background: #2c3e50;
            padding: 25px;
            transition: 0.3s;
        }

        .sidebar.hide {
            left: -240px;
        }

        .sidebar h3 {
            color: white;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            margin: 15px 0;
            padding: 8px;
            border-radius: 6px;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }

        .main {
            margin-left: 240px;
            padding: 30px;
            transition: 0.3s;
        }

        .main.full {
            margin-left: 0;
        }

        h2 {
            color: white;
            margin-bottom: 25px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            text-align: center;
        }

        .card h3 {
            margin: 0;
            font-size: 28px;
            color: #1e3c72;
        }

        .card p {
            margin-top: 8px;
            color: #555;
        }

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th {
            background: #2a5298;
            color: white;
            padding: 12px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        table tr:hover {
            background: #f2f6ff;
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <h3>Narasumber Panel</h3>
    <a href="#">Dashboard</a>
    <a href="#">Undangan Seminar</a>
    <a href="#">Upload Materi</a>
    <a href="#">Lihat Feedback</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main" id="main">

    <div class="topbar">
       
        Dashboard Narasumber - Sistem Manajemen Seminar
    </div>

    <h2>Ringkasan Aktivitas Anda</h2>

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

    <div class="table-card">
        <h3>Daftar Seminar Anda</h3>

        <table>
            <tr>
                <th>Judul Seminar</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>

            <?php while($u = mysqli_fetch_assoc($undangan)): ?>
            <tr>
                <td><?= $u['judul']; ?></td>
                <td><?= $u['tanggal']; ?></td>
                <td><?= $u['status']; ?></td>
            </tr>
            <?php endwhile; ?>
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