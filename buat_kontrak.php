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
    $judul_kontrak = $_POST['judul_kontrak'];
    $judul_seminar = $_POST['judul_seminar'];
    $nomor_kontrak = $_POST['nomor_kontrak'];
    $klien_id = $_POST['klien_id'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $nilai_kontrak = $_POST['nilai_kontrak'];
    $isi_kontrak = $_POST['isi_kontrak'];


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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
    <title>Buat Kontrak</title>
    <style>
        * {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #1e3c72;
    font-family: 'Segoe UI', sans-serif;
}

/* CONTAINER */
.container {
    max-width: 700px;
    margin: 50px auto;
    padding: 0 15px;
}

/* CARD */
.card {
    background: #ffffff;
    padding: 30px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

/* TITLE */
h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #334155;
}

/* LABEL */
label {
    font-weight: 600;
    font-size: 14px;
    color: #475569;
}

/* INPUT & TEXTAREA */
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

/* TEXTAREA */
textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.5;
}

/* FOCUS */
input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #64748b;
}

/* BUTTON */
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

/* BACK BUTTON */
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

/* RESPONSIVE */
@media (max-width: 768px) {
    .container {
        margin: 30px auto;
    }

    .card {
        padding: 20px;
    }

    h2 {
        font-size: 18px;
    }

    input,
    select,
    textarea {
        font-size: 13px;
    }

    .btn {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .container {
        margin: 20px auto;
    }

    .card {
        padding: 15px;
    }
}
    </style>
</head>

<body>

    <div class="container">
        <div class="card">
            <a href="dashboardeo.php" class="back">← Kembali ke Dashboard</a>
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
                    <?php while ($k = mysqli_fetch_assoc($klien)) { ?>
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
                <textarea rows="5" style="width:100%; padding:15px; border-radius:8px; margin-bottom:20px; margin-top: 10px;" name="isi_kontrak" required></textarea>
                <button type="submit" name="submit" class="btn">
                    Simpan Kontrak
                </button>

            </form>

        </div>

    </div>

</body>

</html>