<?php
session_start();
include "config.php";

use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';

$user_id = $_SESSION['id'];
$seminar_id = $_POST['seminar_id'];

// upload bukti
$nama = $_FILES['bukti']['name'];
$tmp = $_FILES['bukti']['tmp_name'];

$path = "upload/" . time() . "_" . $nama;
move_uploaded_file($tmp, $path);

// cek sudah daftar
$cek = mysqli_query($conn, "SELECT * FROM pendaftaran 
WHERE peserta_id=$user_id AND seminar_id=$seminar_id");

if (mysqli_num_rows($cek) > 0) {
    die("Anda sudah terdaftar");
}

// generate OTP
$otp = rand(100000, 999999);
$expired = date("Y-m-d H:i:s", strtotime("+5 minutes"));

mysqli_query($conn, "INSERT INTO verifikasi_otp
(user_id,seminar_id,otp,expired_at)
VALUES('$user_id','$seminar_id','$otp','$expired')");

// ambil email user
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));

// kirim email
$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'email@gmail.com';
$mail->Password = 'app_password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('email@gmail.com', 'Seminar');
$mail->addAddress($user['email']);

$mail->isHTML(true);
$mail->Subject = "Kode OTP Seminar";
$mail->Body = "
<h2>Kode OTP Anda</h2>
<h1>$otp</h1>
<p>Berlaku 5 menit</p>
";

$mail->send();

// simpan session
$_SESSION['otp_user'] = $user_id;
$_SESSION['otp_seminar'] = $seminar_id;
$_SESSION['bukti'] = $path;

header("Location: verifikasi_pendaftaran.php");