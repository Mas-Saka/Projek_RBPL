<?php
session_start();
include "config.php";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email= '$email'");
    $data = mysqli_fetch_assoc($query);

    if ($data && $password == $data['password']) {
        $_SESSION['id'] = $data['id'];  
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];

    if ($data['role'] == 'eo') {
            header("Location: dashboardeo.php");
        } elseif ($data['role'] == 'peserta') {
        header("Location: dashboardpeserta.php");
        } elseif ($data['role'] == 'narasumber') {
            header("Location: dashboardnarasumber.php");
        } elseif ($data['role'] == 'klien') {
            header("Location: dashboardklien.php");
        }
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistem Seminar Online</title>
    <style>

        body {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
}

.login-box {
    width: 350px;
    margin: 120px auto;
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    text-align: center;
}

.login-box h2 {
    margin-bottom: 20px;
    color: #2c3e50;
}

.login-box input {
    width: 95%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
}

.login-box button {
    width: 80%;
    padding: 10px;
    background: #2a5298;
    
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-weight: bold;
}

.login-box button:hover {
    background: #1e3c72;
}

body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #eef2f7;
}

.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    background: linear-gradient(180deg,#1e3c72,#2a5298);
    color: white;
    padding: 25px;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 40px;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 10px;
    margin: 8px 0;
    border-radius: 8px;
    transition: 0.3s;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.2);
}

.content {
    margin-left: 260px;
    padding: 40px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.forgot-password {
    margin-top: 1px;
    text-align: center;
    margin-bottom: 20px;
}

.forgot-password a {
    font-size: 13px;
    color: #2a5298;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s;
}

.forgot-password a:hover {
    text-decoration: underline;
    color: #1e3c72;
}
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <div class="forgot-password">
            <a href="lupa_password.php">Lupa password?</a>
    </div>
        <button type="submit" name="login">Login</button>
    </form>

    <p>Belum punya akun? <a href="register.php">Daftar sebagai Peserta</a></p>
</div>

</body>
</html>