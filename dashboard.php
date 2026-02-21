<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="assets/dashboard.css">
</head>
<body>

<div class="sidebar">
    <h3>Menu</h3>
    <a href="dashboard.php">Dashboard</a>
    <a href="seminar.php">Data Seminar Baru</a>
    <a href="narasumber.php">Narasumber Ganteng</a>
    <a href="peserta.php">Peserta</a>
    <a href="pendaftaran.php">Pendaftaran</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h2>Dashboard Sistem Manajemen Seminar</h2>
    <p>Selamat datang Admin</p>

    <div class="card">
        <h3>Jumlah Seminar 3</h3>
    </div>
    <div class="card">
        <h3>Total Peserta: 25</h3>
    </div>
    <div class="card">
        <h3>Total Narasumber: 5</h3>
    </div>
</div>

</body>
</html>