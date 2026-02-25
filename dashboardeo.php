<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];

// Statistik
$total_seminar = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM seminar WHERE eo_id=$eo_id"
))['total'];

$seminar_aktif = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM seminar WHERE eo_id=$eo_id AND status='aktif'"
))['total'];

$total_peserta = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM pendaftaran 
     JOIN seminar ON pendaftaran.seminar_id= seminar.seminar_id
     WHERE seminar.eo_id=$eo_id"
))['total'];

$total_feedback = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM feedback 
     JOIN seminar ON feedback.seminar_id=seminar.seminar_id
     WHERE seminar.eo_id=$eo_id"
))['total'];

$nama_eo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM users WHERE id=$eo_id"))['nama'];
?>


<!DOCTYPE html>
<html>
<head>
    <title>Dashboard EO</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
        }

        .topbar {
            background: #1e3c72;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
        }

        .burger {
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 190px;
            height: 100%;
            background: #2c3e50;
            padding: 20px;
            transition: 0.3s;
        }

        .sidebar.hide {
            left: -230px;
        }

        .sidebar a {
            display: block;
            color: white;
            margin: 15px 0;
            text-decoration: none;
        }

        .main {
            margin-left: 230px;
            padding: 20px;
            transition: 0.3s;
        }

        .main.full {
            margin-left: 0;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stats .card {
            text-align: center;
        }

        h2 {
            color: white;
        }

        button {
            background: #2a5298;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #163d7a;
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <h2 style = "margin-bottom: 12px; ">Selamat Datang</h2>
    <div class ="box"style="background: #611c07; padding: 10px; border-radius: 30px; margin-bottom: 10px;">   
    <h3 style="color:white; font-size: 14px;margin: 2px;"><?= $nama_eo." -- Event Organizer"?></h3>
    </div>
    <a href="#">Dashboard</a>
    <a href="seminar.php">Kelola Seminar</a>
    <a href="buat_kontrak.php">Buat Kontrak</a>
    <a href="#">Undang Narasumber</a>
    <a href="">Data Peserta</a>
    <a href="#">Laporan</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main" id="main">
    <div class="topbar">
        <div class="burger" onclick="toggleMenu()">☰</div>
        <div>Dashboard EO - Sistem Manajemen Seminar</div>
    </div>

    <h2>Statistik Seminar Anda</h2>

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
            <h3><?= $total_peserta ?></h3>
            <p>Total Peserta</p>
        </div>
        <div class="card">
            <h3><?= $total_feedback ?></h3>
            <p>Total Feedback</p>
        </div>
    </div>

    <div class="card">
        <h3>Kelola Seminar</h3>
        <p>Buat, edit, dan atur jadwal seminar sesuai kontrak.</p>
        <a href="seminar.php"><button>Masuk ke Manajemen Seminar</button></a>
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