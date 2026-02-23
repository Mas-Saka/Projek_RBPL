<?php
include "config.php";

if (isset($_POST['register'])) {

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = ($_POST['password']);
    $no_hp = $_POST['no_hp'];


    $panjang = strlen($password);
    $hurufkecil = false;
    $hurufbesar = false;
    $angka = false;

    for ($i = 0; $i < $panjang; $i++) {
        $karakter  = $password[$i];
        if ($karakter >= 'a' && $karakter <= 'z') {
            $hurufkecil = true;
        } elseif ($karakter >= 'A' && $karakter <= 'Z') {
            $hurufbesar = true;
        } elseif ($karakter >= '0' && $karakter <= '9') {
            $angka = true;
        }
    }
  if($panjang < 8) {
    $error = "Password harus minimal 8 karakter!";
  } elseif (!$hurufbesar) {
    $error = "Password harus mengandung huruf besar!";
  } elseif (!$angka) {
    $error = "Password harus mengandung angka!";
  } else {
    //cek email sudah terdaftar atau belum
    $cek_email = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $error = "Email sudah terdaftar gunakan email lain!";
    } else {
    $insert = mysqli_query($conn, "INSERT INTO users (nama, email, password, no_hp, role) VALUES ('$nama', '$email', '$password', '$no_hp', 'peserta')");
    if ($insert) {
        $success = "Pendaftaran berhasil! Silakan <a href='login.php'>login</a>.";
    } else {
        $error = "Gagal mendaftar. Silakan coba lagi.";
    }
  }
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Peserta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-box">
    <h2>Register Peserta</h2>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="nama" placeholder="Nama Lengkap" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="text" name="no_hp" placeholder="No HP" required><br><br>
        <button type="submit" name="register">Daftar</button>
    </form>
    <?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>

</body>
</html>