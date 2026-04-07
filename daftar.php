<?php
session_start();
include "config.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
if (!isset($_GET['id'])) {
    die("ID seminar tidak ditemukan");
}

$seminar_id = $_GET['id'];

$seminar = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT * FROM seminar WHERE seminar_id=$seminar_id"
));

if (!isset($_GET['id'])) {
    die("ID seminar tidak ditemukan");
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Seminar</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f7f6;
        }

        .container {
            width: 80%;
            margin: auto;
            margin-top: 80px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        button {
            background: #3498db;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="card">
            <h2><?= $seminar['judul_seminar'] ?></h2>
            <p>Harga: Rp<?= $seminar['biaya'] ?></p>
            <p>Tanggal: <?= $seminar['tanggal'] ?></p>
        </div>

        <div class="card">
            <h3>Pembayaran QRIS</h3>
            <img src="qris.png" width="200"><br><br>

            <form method="POST" action="proses_daftar.php" enctype="multipart/form-data">
                <input type="hidden" name="seminar_id" value="<?= $seminar_id ?>">

                <label>Upload Bukti:</label>
                <input type="file" name="bukti" required>

                <button type="submit">Saya Sudah Bayar</button>
            </form>
        </div>

    </div>

</body>

</html>