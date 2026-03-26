<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || ($_SESSION['role'] != 'klien' && $_SESSION['role'] != 'eo')) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['id'];
$role = $_SESSION['role'];

$query = mysqli_query($conn, "
    SELECT 
        k.kontrak_id,
        k.judul_kontrak,
        k.judul_seminar,
        k.nomor_kontrak,
        k.tanggal_mulai,
        k.tanggal_selesai,
        k.nilai_kontrak,
        k.isi_kontrak,
        k.status_kontrak,
        k.tanggal_buat,
        u.nama AS nama_eo
    FROM kontrak k
    JOIN users u ON k.eo_id = u.id
    WHERE 
        k.kontrak_id = '$id'
        AND (
            ('$role' = 'klien' AND k.klien_id = '$user_id')
            OR
            ('$role' = 'eo' AND k.eo_id = '$user_id')
        )
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan atau bukan hak akses Anda'); window.location='dashboardeo.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Kontrak</title>
    <style>
        body {
            margin: 0;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        h2 {
            margin-top: 0;
            color: #1e3c72;
        }

        .section {
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
            color: #2a5298;
        }

        .isi {
            background: #f4f6f9;
            padding: 15px;
            border-radius: 10px;
            line-height: 1.7;
        }

        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .menunggu {
            background: #f39c12;
            color: white;
        }

        .disetujui {
            background: #27ae60;
            color: white;
        }

        .ditolak {
            background: #e74c3c;
            color: white;
        }

        .section {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .label {
            font-weight: bold;
            color: #2a5298;
        }

        .section p {
            background: #f4f6f9;
            padding: 12px;
            border-radius: 8px;
            margin-top: 6px;
        }

        .actions {
            margin-top: 15px;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
            transition: 0.3s;
        }

        .approve {
            background: #27ae60;
            color: white;
        }

        .reject {
            background: #e74c3c;
            color: white;
        }

        .approve:hover {
            background: #219150;
        }

        .reject:hover {
            background: #c0392b;
        }
    </style>
</head>

<body>

    <div class="container">

        <a href="datakontrak.php" class="back-btn">← Kembali</a>

        <div class="box">
            <h2><?= $data['judul_kontrak']; ?></h2>
            <div class="section">
                <span class="label">EO:</span> <?= $data['nama_eo']; ?>
            </div>
            <div class="section">
                <span class="label">Nomor Kontrak:</span> 
                <?= $data['nomor_kontrak']; ?>
            </div>
            <div class="section">
                <span class="label">Periode:</span>
                <?= $data['tanggal_mulai']; ?> - <?= $data['tanggal_selesai']; ?>
            </div>
            <div class="section">
                <span class="label">Nilai Kontrak:</span>
                Rp <?= number_format($data['nilai_kontrak'], 0, ',', '.'); ?>
            </div>
            <div class="section">
                <span class="label">Isi Kontrak:</span>
                <div class="isi">
                    <?= nl2br($data['isi_kontrak']); ?>
                </div>
            </div>
            <?php if ($data['status_kontrak'] == 'menunggu') { ?>
                <div class="actions">
                    <form method="POST" action="update_kontrak.php">
                        <input type="hidden" name="kontrak_id" value="<?= $data['kontrak_id']; ?>">
                        <button type="submit" name="approve" class="btn approve">Setujui</button>
                        <button type="submit" name="reject" class="btn reject">Tolak</button>
                    </form>
                </div>
            <?php } ?>
            <?php
            if ($_SESSION['role'] == 'eo') {
                $back = "dashboardeo.php";
            } else {
                $back = "datakontrak.php"; // halaman klien
            }
            ?>

            <a href="<?= $back ?>" class="back-btn">← Kembali</a>
        </div>

    </div>

</body>

</html>