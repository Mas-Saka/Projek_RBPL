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

// Query diubah sedikit untuk mengambil nama Klien dan nama EO agar informasinya lebih lengkap
$query = mysqli_query($conn, "
    SELECT 
        k.*,
        ue.nama AS nama_eo,
        uk.nama AS nama_klien 
    FROM kontrak k
    JOIN users ue ON k.eo_id = ue.id
    JOIN users uk ON k.klien_id = uk.id
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

        .isi {
            background: #f4f6f9;
            padding: 15px;
            border-radius: 10px;
            line-height: 1.6;
            margin-top: 6px;
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
            margin-top: 5px;
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
            display: inline-block;
            margin-top: 5px;
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
                <span class="label">Dibuat Oleh (Klien):</span> <?= $data['nama_klien']; ?>
            </div>

            <div class="section">
                <span class="label">Ditujukan Ke (EO):</span> <?= $data['nama_eo']; ?>
            </div>

            <div class="section">
                <span class="label">Nomor Kontrak:</span> <?= $data['nomor_kontrak']; ?>
            </div>

            <div class="section">
                <span class="label">Periode:</span>
                <?= $data['tanggal_mulai']; ?> s/d <?= $data['tanggal_selesai']; ?>
            </div>

            <div class="section">
                <span class="label">Nilai Kontrak:</span>
                Rp <?= number_format($data['nilai_kontrak'], 0, ',', '.'); ?>
            </div>

            <div class="section">
                <span class="label">Status:</span><br>
                <span class="status <?= $data['status_kontrak']; ?>">
                    <?= strtoupper($data['status_kontrak']); ?>
                </span>
            </div>

            <div class="section">
                <span class="label">Isi Kontrak:</span>
                <div class="isi">
                    <?= nl2br($data['isi_kontrak']); ?>
                </div>
            </div>

            <?php if ($data['status_kontrak'] == 'ditolak' && !empty($data['alasan_penolakan'])) { ?>
                <div class="section">
                    <span class="label">Alasan Penolakan:</span>
                    <div class="isi" style="background:#fee2e2; color:#991b1b;">
                        <?= $data['alasan_penolakan']; ?>
                    </div>
                </div>
            <?php } ?>

            <?php if ($data['status_kontrak'] == 'menunggu' && $_SESSION['role'] == 'eo') { ?>

                <div class="actions">
                    <form method="POST" action="update_kontrak.php" style="display:inline;">
                        <input type="hidden" name="kontrak_id" value="<?= $data['kontrak_id']; ?>">
                        <button type="submit" name="approve" class="btn approve">Setujui Kontrak</button>
                    </form>

                    <button onclick="showRejectForm()" class="btn reject">Tolak Kontrak</button>
                </div>

                <div id="rejectForm" style="display:none; margin-top:15px;">
                    <form method="POST" action="update_kontrak.php">
                        <input type="hidden" name="kontrak_id" value="<?= $data['kontrak_id']; ?>">
                        <textarea name="alasan" placeholder="Masukkan alasan penolakan kontrak..." required></textarea>
                        <br><br>
                        <button type="submit" name="reject" class="btn reject">Kirim Penolakan</button>
                    </form>
                </div>

            <?php } ?>

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