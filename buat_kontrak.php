<?php
session_start();
include "config.php";

$_SESSION['id'];
$_SESSION['role'];

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $judul_kontrak   = $_POST['judul_kontrak'];
    $judul_seminar   = $_POST['judul_seminar'];
    $nomor_kontrak   = $_POST['nomor_kontrak'];
    $klien_id        = $_POST['klien_id'];
    $tanggal_mulai   = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $nilai_kontrak   = $_POST['nilai_kontrak'];
    $isi_kontrak     = $_POST['isi_kontrak'];


    $status = "menunggu";
    $tanggal_buat = date("Y-m-d");
    
    $eo_id = $_SESSION['id'];
    mysqli_query($conn, "INSERT INTO kontrak 
        (judul_kontrak, judul_seminar, nomor_kontrak, eo_id, klien_id, tanggal_mulai, tanggal_selesai, nilai_kontrak, isi_kontrak ,status_kontrak, tanggal_buat)    
        VALUES 
        ('$judul_kontrak','$judul_seminar','$nomor_kontrak','$eo_id','$klien_id','$tanggal_mulai','$tanggal_selesai','$nilai_kontrak','$isi_kontrak','$status','$tanggal_buat')
    ");

    echo "<script>alert('Kontrak berhasil dibuat!'); window.location='dashboardeo.php';</script>";
}
?>

<?php
$klien = mysqli_query($conn, "SELECT id, nama FROM users WHERE role='klien'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Kontrak</title>
    <style>
        body {
            margin: 0;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c5364;
        }

        label {
            font-weight: 600;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #2c5364;
        }

        .btn {
            background: #2c5364;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn:hover {
            background: #203a43;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

    </style>
</head>
<body>

<div class="container">

    <a href="dashboardeo.php" class="back">← Kembali ke Dashboard</a>

    <div class="card">
        <h2>Buat Kontrak Baru</h2>

        <form method="POST">

            <label>Judul Kontrak</label>
            <input type="text" name="judul_kontrak" required>
            
            <label>Judul Seminar</label>
            <input type="text" name="judul_seminar" required>

            <label>Nomor Kontrak</label>
            <input type="text" name="nomor_kontrak" required>

            <label>Pilih / Input Klien</label>
            <input list="list_klien" name="klien_id" placeholder="Ketik ID klien atau pilih..." required>
            <datalist id="list_klien">
                <?php while($k = mysqli_fetch_assoc($klien)) { ?>
                <option value="<?= $k['id']; ?>">
                    <?= $k['nama']; ?> (ID: <?= $k['id']; ?>)
                </option>
                <?php } ?>
            </datalist>

            <label>Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" required>

            <label>Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" required>

            <label>Nilai Kontrak (Rp)</label>
            <input type="number" name="nilai_kontrak" required>
            
            <label>Isi Perjanjian Kontrak</label>
            <input type="text" name="isi_kontrak" required>

            <button type="submit" name="submit" class="btn">
                Simpan Kontrak
            </button>

        </form>

    </div>

</div>

</body>
</html>