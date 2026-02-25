<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'klien') {
    header("Location: login.php");
    exit;
}

$klien_id = $_SESSION['id'];

$query = mysqli_query($conn, "SELECT kontrak_id, nomor_kontrak, tanggal_buat, tanggal_mulai, 
           tanggal_selesai, nilai_kontrak, status_kontrak
    FROM kontrak
    WHERE klien_id = $klien_id
    ORDER BY tanggal_buat DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Kontrak</title>
    <style>
        body {
            margin: 0;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            margin-bottom: 30px;
        }

        .back-btn {
            background: white;
            color: #1e3c72;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .contract-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .contract-info {
            font-size: 14px;
        }

        .badge {
            padding: 2px 12px;
            margin-top: 5px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            color: white;

        }

        .menunggu { background: orange; }
        .disetujui { background: green; }
        .ditolak { background: red; }

        .detail-btn {
            background: #2a5298;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }

    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h2>Daftar Kontrak</h2>
        <a href="dashboardklien.php" class="back-btn">← Dashboard</a>
    </div>

    <?php while($data = mysqli_fetch_assoc($query)) { ?>
        
        <div class="contract-item">
    <div class="contract-info">
        <strong><?= $data['nomor_kontrak']; ?></strong><br>
        Periode: <?= $data['tanggal_mulai']; ?> - <?= $data['tanggal_selesai']; ?><br>
        Nilai: Rp <?= number_format($data['nilai_kontrak'],0,',','.'); ?>
    </div>

    <div style="text-align:center;">
        <div class="badge <?= $data['status_kontrak']; ?>" style="margin-bottom:8px;">
            <?= strtoupper($data['status_kontrak']); ?>
        </div>

        <a href="detail_kontrak.php?id=<?= $data['kontrak_id']; ?>" class="detail-btn">
            Lihat Detail
        </a>
    </div>
</div>

    <?php } ?>

</div>

</body>
</html>