<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'klien') {
    header("Location: login.php");
    exit;
}

$klien_id = $_SESSION['id'];

// Ambil kontrak milik klien
$kontrak = mysqli_query($conn, "
    SELECT * FROM kontrak 
    WHERE klien_id = $klien_id
");

// Hitung statistik
$total_kontrak = mysqli_num_rows($kontrak);

$kontrak_disetujui = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM kontrak 
     WHERE klien_id=$klien_id AND status_kontrak='disetujui'"
))['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Klien</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: Arial;
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

        .burger {
            width: 30px;
            height: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            cursor: pointer;
            margin-right: 15px;
        }

        .burger span {
            height: 3px;
            background: white;
            border-radius: 2px;
            transition: 0.3s;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 200px;
            height: 100%;
            background: #2c3e50;
            padding: 20px;
            transition: 0.3s;
        }

        .sidebar.hide {
            left: -240px;
        }

        .sidebar h3 {
            color: white;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            margin: 15px 0;
        }

        .main {
            margin-left: 240px;
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
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        table th {
            background: #2a5298;
            color: white;
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <h3>Klien Panel</h3>
    <a href="#">Dashboard</a>
    <a href="datakontrak.php">Data Kontrak</a>
    <a href="#">Status Seminar</a>
    <a href="#">Laporan Akhir</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main" id="main">

    <div class="topbar">
        <div class="burger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
        Dashboard Klien - Sistem Manajemen Seminar
    </div>

    <h2 style="color:white;">Ringkasan Kontrak</h2>

    <div class="stats">
        <div class="card">
            <h3><?= $total_kontrak ?></h3>
            <p>Total Kontrak</p>
        </div>
        <div class="card">
            <h3><?= $kontrak_disetujui ?></h3>
            <p>Kontrak Disetujui</p>
        </div>
    </div>

    <div class="card">
        <h3>Daftar Kontrak</h3>

        <table>
            <tr>
                <th>Nomor Kontrak</th>
                <th>Tanggal Mulai</th>
                <th>Status</th>
            </tr>

            <?php while($k = mysqli_fetch_assoc($kontrak)): ?>
            <tr>
                <td><?= $k['nomor_kontrak']; ?></td>
                <td><?= $k['tanggal_mulai']; ?></td>
                <td><?= $k['status_kontrak']; ?></td>
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