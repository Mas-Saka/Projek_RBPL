<?php
session_start();
include "config.php";

// Pastikan yang mengakses adalah Klien
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'klien') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $judul_kontrak = $_POST['judul_kontrak'];
    $judul_seminar = $_POST['judul_seminar'];
    $nomor_kontrak = $_POST['nomor_kontrak'];
    $eo_id = $_POST['eo_id']; // Sekarang mengambil ID EO dari form
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $nilai_kontrak = $_POST['nilai_kontrak'];
    $isi_kontrak = $_POST['isi_kontrak'];

    $status = "menunggu";
    $tanggal_buat = date("Y-m-d");
    $klien_id = $_SESSION['id']; // ID Klien diambil dari sesi yang login

    mysqli_query($conn, "INSERT INTO kontrak 
        (judul_kontrak, judul_seminar, nomor_kontrak, eo_id, klien_id, tanggal_mulai, tanggal_selesai, nilai_kontrak, isi_kontrak ,status_kontrak, tanggal_buat)    
        VALUES 
        ('$judul_kontrak','$judul_seminar','$nomor_kontrak','$eo_id','$klien_id','$tanggal_mulai','$tanggal_selesai','$nilai_kontrak','$isi_kontrak','$status','$tanggal_buat')
    ");

    echo "<script>alert('Kontrak berhasil dibuat! Menunggu persetujuan EO.'); window.location='datakontrak.php';</script>";
}

// Ambil daftar EO untuk dipilih oleh Klien
$eo_list = mysqli_query($conn, "SELECT id, nama FROM users WHERE role='eo'");
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kontrak</title>
    <style>
        /* Gaya CSS sama persis seperti aslinya, saya persingkat agar rapi */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #1e3c72;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 700px;
            margin: 50px auto;
            padding: 0 15px;
        }

        .card {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #334155;
        }

        label {
            font-weight: 600;
            font-size: 14px;
            color: #475569;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 18px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
            background: #ffffff;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #64748b;
        }

        .btn {
            background: #334155;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            width: 100%;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn:hover {
            background: #1e293b;
        }

        .back {
            display: inline-block;
            margin-bottom: 15px;
            padding: 6px 12px;
            background: #475569;
            color: white;
            text-decoration: none;
            font-size: 13px;
            border-radius: 6px;
        }

        .back:hover {
            background: #334155;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <a href="datakontrak.php" class="back">← Kembali ke Data Kontrak</a>
            <h2>Pengajuan Kontrak Baru</h2>

            <form method="POST">
                <label>Judul Kontrak</label>
                <input type="text" name="judul_kontrak" required>

                <label>Judul Seminar</label>
                <input type="text" name="judul_seminar" required>

                <label>Nomor Kontrak</label>
                <input type="text" name="nomor_kontrak" required>

                <label>Pilih Event Organizer (EO)</label>
                <input list="list_eo" name="eo_id" placeholder="Ketik ID EO atau pilih..." required>
                <datalist id="list_eo">
                    <?php while ($eo = mysqli_fetch_assoc($eo_list)) { ?>
                        <option value="<?= $eo['id']; ?>">
                            <?= $eo['nama']; ?> (ID: <?= $eo['id']; ?>)
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
                <textarea name="isi_kontrak" required></textarea>

                <button type="submit" name="submit" class="btn">Kirim Pengajuan Kontrak</button>
            </form>
        </div>
    </div>
</body>

</html>