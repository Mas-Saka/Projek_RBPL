<?php
include "config.php";

if (isset($_POST['register'])) {

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = ($_POST['password']);
    $no_hp = $_POST['no_hp'];

    mysqli_query($conn, "INSERT INTO users 
    (nama,email,password,role,no_hp) 
    VALUES 
    ('$nama','$email','$password','peserta','$no_hp')");

    header("Location: login.php");
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

    <form method="POST">
        <input type="text" name="nama" placeholder="Nama Lengkap" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="text" name="no_hp" placeholder="No HP" required><br><br>
        <button type="submit" name="register">Daftar</button>
    </form>
</div>

</body>
</html>