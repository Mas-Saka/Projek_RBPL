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
        k.alasan_penolakan,
        k.tanggal_buat,
        u.nama AS nama_eo FROM kontrak k
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
            background: #1e3c72;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
            color: #2a5298;
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

        .isi {
            background: #f4f6f9;
            padding: 15px;
            border-radius: 10px;
            line-height: 1.6;
        }

        .actions {
            margin-top: 20px;
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

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            resize: none;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 14px;
            background: #2a5298;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .back-btn:hover {
            background: #1e3c72;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .menunggu {
            background: orange;
        }

        .disetujui {
            background: green;
        }

        .ditolak {
            background: red;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="box">

            <h2><?= $data['judul_kontrak']; ?></h2>

            <div class="section">
                <span class="label">EO:</span> <?= $data['nama_eo']; ?>
            </div>

            <div class="section">
                <span class="label">Nomor Kontrak:</span> <?= $data['nomor_kontrak']; ?>
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
                <span class="label">Status:</span>
                <span class="status <?= $data['status_kontrak']; ?>">
                    <?= $data['status_kontrak']; ?>
                </span>
            </div>

            <div class="section">
                <span class="label">Isi Kontrak:</span>
                <div class="isi">
                    <?= nl2br($data['isi_kontrak']); ?>
                </div>
            </div>

            <!-- ALASAN PENOLAKAN -->
            <?php if ($data['status_kontrak'] == 'ditolak') { ?>
                <div class="section">
                    <span class="label">Alasan Penolakan:</span>
                    <div class="isi">
                        <?= $data['alasan_penolakan']; ?>
                    </div>
                </div>
            <?php } ?>

            <!-- ACTION KHUSUS KLIEN -->
            <?php if ($data['status_kontrak'] == 'menunggu' && $_SESSION['role'] == 'klien') { ?>

                <div class="actions">
                    <!-- APPROVE -->
                    <form method="POST" action="update_kontrak.php" style="display:inline;">
                        <input type="hidden" name="kontrak_id" value="<?= $data['kontrak_id']; ?>">
                        <button type="submit" name="approve" class="btn approve">Setujui</button>
                    </form>

                    <!-- TOMBOL TOLAK -->
                    <button onclick="showRejectForm()" class="btn reject">Tolak</button>
                </div>

                <!-- FORM ALASAN -->
                <div id="rejectForm" style="display:none; margin-top:15px;">
                    <form method="POST" action="update_kontrak.php">
                        <input type="hidden" name="kontrak_id" value="<?= $data['kontrak_id']; ?>">

                        <textarea name="alasan" placeholder="Masukkan alasan penolakan..." required></textarea>
                        <br><br>
                        <button type="submit" name="reject" class="btn reject">Kirim Penolakan</button>
                    </form>
                </div>

            <?php } ?>

            <!-- BACK -->
            <?php
            if ($_SESSION['role'] == 'eo') {
                $back = "dashboardeo.php";
            } else {
                $back = "datakontrak.php";
            }
            ?>

            <a href="<?= $back ?>" class="back-btn">← Kembali</a>

        </div>
    </div>

    <script>
        function showRejectForm() {
            document.getElementById('rejectForm').style.display = 'block';
        }
    </script>

</body>

</html>