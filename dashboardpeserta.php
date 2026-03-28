<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

$peserta_id = $_SESSION['id'];

// Ambil seminar aktif
$seminar = mysqli_query($conn, "SELECT * FROM seminar WHERE status='aktif'");

// Ambil pendaftaran peserta
$pendaftaran = mysqli_query($conn, " SELECT seminar.judul_seminar, pendaftaran.status, pendaftaran.kehadiran FROM pendaftaran JOIN seminar ON pendaftaran.seminar_id = seminar.seminar_id WHERE pendaftaran.peserta_id = $peserta_id");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Peserta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header {
            background: #1e3c72;
            color: white;
            padding: 15px;
            font-size: 18px;
        }

        .card-seminar {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn {
            padding: 7px 12px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background: #163d7a;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
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

    <div class="sidebar">
        <h3><?= $_SESSION['nama']; ?></h3>
        <a href="#">Dashboard</a>
        <a href="#seminar">Seminar Tersedia</a>
        <a href="#status">Status Pendaftaran</a>
        <a href="logout.php" style="color:red; text-decoration:none; padding:8px 12px; border-radius:8px;
            transition:0.3s;" onmouseover="this.style.background='red'; this.style.color='white';"
            onmouseout="this.style.background='transparent'; this.style.color='red';"">Logout</a>
    </div>

    <div class="content">

        <div class="header">
            Dashboard Peserta - Sistem Manajemen Seminar Online
        </div>

        <!-- Seminar Aktif -->
        <h2 id="seminar">Seminar Tersedia</h2>

        <?php while ($row = mysqli_fetch_assoc($seminar)): ?>
            <div class="card-seminar">
                <h3><?= $row['judul']; ?></h3>
                <p><?= $row['deskripsi']; ?></p>
                <p><b>Tanggal:</b> <?= $row['created_at']; ?></p>
                <p><b>Metode:</b> <?= strtoupper($row['metode']); ?></p>
                <a href="daftar.php?id=<?= $row['id']; ?>">
                    <button class="btn">Daftar Seminar</button>
                </a>
            </div>
        <?php endwhile; ?>

        <!-- Status Pendaftaran -->
        <h2 id="status">Status Pendaftaran Saya</h2>

        <table>
            <tr>
                <th>Judul Seminar</th>
                <th>Status</th>
                <th>Kehadiran</th>
            </tr>

            <?php while ($p = mysqli_fetch_assoc($pendaftaran)): ?>
                <tr>
                    <td><?= $p['judul']; ?></td>
                    <td><?= $p['status']; ?></td>
                    <td><?= $p['kehadiran'] ? $p['kehadiran'] : '-'; ?></td>
                </tr>
            <?php endwhile; ?>

        </table>

    </div>

</body>

</html>