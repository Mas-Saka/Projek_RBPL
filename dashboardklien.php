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

$kontrak_disetujui = mysqli_fetch_assoc(mysqli_query(
    $conn,
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
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
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
        }

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
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
            margin-bottom: 20px;
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
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            border: 1px solid #8ca9cf;
        }

        /* HEADER */
        th {
            text-align: left;
            padding: 13px;
            font-size: 13px;
            background: #f1f5f9;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            border: 1px solid #8ca9cf;
        }

        /* DATA */
        td {
            padding: 13px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #334155;
            border: 1px solid #8ca9cf;
        }

        /* ROW EFFECT */
        tr {
            transition: 0.2s;
        }

        tr:hover {
            background: #f8fafc;
        }

        /* STATUS */
        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-disetujui {
            background: #dcfce7;
            color: #166534;
        }

        .status-menunggu {
            background: #fef3c7;
            color: #92400e;
        }

        .status-ditolak {
            background: #fee2e2;
            color: #991b1b;
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

            .btn-detail {
                padding: 6px 10px;
                font-size: 13px;
                border: none;

            }
        }

        .btn-detail {
            background: #1e40af;
            color: white;
            padding: 7px 14px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .btn-detail:hover {
            background: #1e40af transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.4);
        }

        .btn-detail:active {
            transform: scale(0.95);
            box-shadow: 0 3px 8px rgba(37, 99, 235, 0.3);
        }
    </style>
</head>

<body>

    <div class="sidebar" id="sidebar">
        <h3>Klien Panel</h3>
        <a href="dashboardklien.php">Dashboard</a>
        <a href="datakontrak.php">Data Kontrak</a>
        <a href="#">Laporan Akhir</a>
        <a href="logout.php" style=" width:;color:red; text-decoration:none; padding:8px 12px; border-radius:8px;
            transition:0.3s;" onmouseover="this.style.background='red'; this.style.color='white';"
            onmouseout="this.style.background='transparent'; this.style.color='red';"">Logout</a>
    </div>

    <div class=" main" id="main">

            <div class="topbar">
                <div class="burger" onclick="toggleMenu()">
                    <span>☰</span>
                </div>
                Dashboard Klien - Sistem Manajemen Seminar
            </div>

            <h2>Ringkasan Kontrak</h2>

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
                        <th>Aksi</th>
                    </tr>

                    <?php while ($k = mysqli_fetch_assoc($kontrak)): ?>
                        <tr>
                            <td><?= $k['nomor_kontrak']; ?></td>
                            <td><?= $k['tanggal_mulai']; ?></td>
                            <td>
                                <?php if ($k['status_kontrak'] == 'disetujui') { ?>
                                    <span class="status status-disetujui">Disetujui</span>
                                <?php } elseif ($k['status_kontrak'] == 'ditolak') { ?>
                                    <span class="status status-ditolak">Ditolak</span>
                                <?php } else { ?>
                                    <span class="status status-menunggu">Menunggu</span>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="detail_kontrak.php?id=<?= $k['kontrak_id']; ?>">
                                    <button class="btn-detail" id="btn-detail">Lihat Detail</button>
                                </a>
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