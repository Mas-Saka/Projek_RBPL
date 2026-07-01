<?php
session_start();
include "config.php";

$user_id = $_SESSION['otp_user'] ?? null;
$seminar_id = $_SESSION['otp_seminar'] ?? null;
$bukti = $_SESSION['bukti'] ?? null;

$error = "";
$sukses = false;

if (isset($_POST['otp'])) {

    $otp = $_POST['otp'];

    $data = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT * FROM verifikasi_otp 
     WHERE user_id=$user_id ORDER BY id DESC LIMIT 1"
    ));

    if (strtotime($data['expired_at']) < time()) {
        $error = "OTP kadaluarsa";
    } elseif ($otp != $data['otp']) {
        $error = "OTP salah";
    } else {

        mysqli_query($conn, "INSERT INTO pendaftaran
        (seminar_id,peserta_id,bukti_pembayaran,metode_daftar,status)
        VALUES('$seminar_id','$user_id','$bukti','qris','diterima')");

        $sukses = true;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial;
            background: #f4f7f6;
            text-align: center;
        }

        .box {
            width: 350px;
            margin: auto;
            margin-top: 100px;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        input {
            padding: 10px;
            width: 100%;
            margin: 10px 0;
        }

        button {
            background: #3498db;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
            font-size: 20px;
        }
    </style>
</head>

<body>

    <div class="box">

        <?php if (!$sukses) { ?>

            <h3>Verifikasi OTP</h3>

            <?php if ($error)
                echo "<p class='error'>$error</p>"; ?>

            <form method="POST">
                <input type="text" name="otp" placeholder="Masukkan OTP" required>
                <button>Verifikasi</button>
            </form>

        <?php } else { ?>

            <h2 class="success">Pendaftaran Berhasil</h2>
            <p>Anda sudah terdaftar di seminar</p>

            <br>
            <a href="dashboardpeserta.php">
                <button>Lihat Seminar Saya</button>
            </a>

        <?php } ?>

    </div>

</body>

</html>