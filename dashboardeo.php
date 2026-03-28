<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];

// Statistik
$total_seminar = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM seminar WHERE eo_id=$eo_id"
))['total'];

$seminar_aktif = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM seminar WHERE eo_id=$eo_id AND status='aktif'"
))['total'];

$total_peserta = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM pendaftaran 
     JOIN seminar ON pendaftaran.seminar_id= seminar.seminar_id
     WHERE seminar.eo_id=$eo_id"
))['total'];

$total_feedback = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM feedback 
     JOIN seminar ON feedback.seminar_id=seminar.seminar_id
     WHERE seminar.eo_id=$eo_id"
))['total'];

$seminar_list = mysqli_query($conn, "SELECT seminar_id, judul_seminar, tanggal, jam_mulai, kuota, status FROM seminar WHERE eo_id=$eo_id ORDER BY seminar_id DESC");

$kontrak_list = mysqli_query($conn, "SELECT k.*, u.nama as nama_klien 
    FROM kontrak k
    JOIN users u ON k.klien_id = u.id
    WHERE k.eo_id = $eo_id
    ORDER BY k.kontrak_id DESC
");
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
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
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

        .btn-detail {
            background: #2a5298;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-detail:hover {
            background: #163d7a;
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        tr:hover {
            background-color: #f5f7fb;
        }

        @media (max-width: 1024px) {
    .main {
        margin-left: 0;
        padding: 15px;
    }

    .sidebar {
        left: -250px;
    }

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stats {
        grid-template-columns: 1fr;
    }

    table {
        font-size: 12px;
        min-width: unset;
    }

    th,
    td {
        padding: 8px;
    }

    button {
        padding: 6px 10px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .topbar {
        font-size: 14px;
    }

    .burger {
        font-size: 18px;
    }

    .card {
        padding: 15px;
    }

    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
    </style>
</head>

<body>

    <div class="sidebar" id="sidebar">
        <h2 style="margin-bottom: 12px; ">Selamat Datang</h2>
        <div class="box" style="background: #611c07; padding: 10px; border-radius: 30px; margin-bottom: 10px;">
            <h3 style="color:white; font-size: 14px;margin: 2px;"><?= $nama_eo . " -- Event Organizer" ?></h3>
        </div>
        <a href="dashboardeo.php">Dashboard</a>
        <a href="seminar.php">Kelola Seminar</a>
        <a href="buat_kontrak.php">Buat Kontrak</a>
        <a href="">Data Peserta</a>
        <a href="#">Laporan</a>
        <a href="logout.php" style=" width:;color:red; text-decoration:none; padding:8px 12px; border-radius:8px;
            transition:0.3s;" onmouseover="this.style.background='red'; this.style.color='white';"
            onmouseout="this.style.background='transparent'; this.style.color='red';"">Logout</a>
    </div>

    <div class=" main" id="main">
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
                <p>Daftar seminar yang telah Anda buat.</p>

                <table class="table-container">
                    <tr style="background:#f0f0f0;">
                        <th style="padding:10px;">Judul</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Kuota</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>

                    <?php while ($s = mysqli_fetch_assoc($seminar_list)) { ?>
                        <tr style="border-bottom:1px solid #ddd;">
                            <td style="max-width:250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                title="<?= $s['judul_seminar']; ?>">
                                <?= $s['judul_seminar']; ?>
                            <td><?= $s['tanggal']; ?></td>
                            <td><?= $s['jam_mulai']; ?></td>
                            <td><?= $s['kuota']; ?></td>

                            <td>
                                <?php if ($s['status'] == 'aktif') { ?>
                                    <span style="color:green; font-weight:bold;">Aktif</span>
                                <?php } else { ?>
                                    <span style="color:orange; font-weight:bold;">Draft</span>
                                <?php } ?>
                            </td>
                            <td>
                                <div style="display:flex; justify-content:center;">
                                    <a href="detail_seminar.php?id=<?= $s['seminar_id']; ?>">
                                        <button class="btn-detail">Detail</button>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <br>
                <a href="seminar.php">
                    <button>+ Tambah Seminar</button>
                </a>
            </div>
            <div class="card">
                <h3>Data Kontrak Klien</h3>
                <p>Daftar kontrak yang telah Anda buat.</p>

                <table>
                    <tr style="background:#f0f0f0;">
                        <th>Judul Kontrak</th>
                        <th>Klien</th>
                        <th>Tanggal</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>

                    <?php if (mysqli_num_rows($kontrak_list) > 0) { ?>

                        <?php while ($k = mysqli_fetch_assoc($kontrak_list)) { ?>
                            <tr>
                                <td style="max-width:200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                    title="<?= $k['judul_kontrak']; ?>">
                                    <?= $k['judul_kontrak']; ?>
                                </td>

                                <td><?= $k['nama_klien']; ?></td>
                                <td><?= $k['tanggal_buat']; ?></td>
                                <td>Rp <?= number_format($k['nilai_kontrak']); ?></td>

                                <td>
                                    <?php if ($k['status_kontrak'] == 'disetujui') { ?>
                                        <span style="color:green; font-weight:bold;">Disetujui</span>
                                    <?php } elseif ($k['status_kontrak'] == 'ditolak') { ?>
                                        <span style="color:red; font-weight:bold;">Ditolak</span>
                                    <?php } else { ?>
                                        <span style="color:orange; font-weight:bold;">Menunggu</span>
                                    <?php } ?>
                                </td>

                                <td style="text-align: center;">
                                    <a href="detail_kontrak.php?id=<?= $k['kontrak_id']; ?>">
                                        <button class="btn-detail">Detail</button>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>

                    <?php } else { ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:15px;">
                                Belum ada data kontrak
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <br><br>
                <a href="buat_kontrak.php">
                    <button>+ Buat Kontrak Baru</button>
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