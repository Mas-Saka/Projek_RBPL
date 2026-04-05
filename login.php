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
            header("Location: index.php");
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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Seminar Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Reset & General */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #2a5298;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Container Login */
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: transform 0.3s ease;
        }

    

        .login-box h2 {
            margin-bottom: 30px;
            color: #1e3c72;
            font-weight: 600;
            font-size: 28px;
            letter-spacing: 1px;
        }

        /* Input Styling */
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .login-box input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ef;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .login-box input:focus {
            border-color: #2a5298;
            box-shadow: 0 0 8px rgba(42, 82, 152, 0.2);
        }

        /* Links */
        .forgot-password {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 25px;
        }

        .forgot-password a {
            font-size: 13px;
            color: #2a5298;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        /* Button */
        .login-box button {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
        }

        .login-box button:hover {
            background: #1e3c72;
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.4);
            transform: scale(1.02);
        }

        /* Footer Text */
        .login-box p {
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .login-box p a {
            color: #2a5298;
            text-decoration: none;
            font-weight: 600;
        }

        .login-box p a:hover {
            text-decoration: underline;
        }

        /* Error Message */
        .error-msg {
            background: #ffe0e0;
            color: #e74c3c;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #e74c3c;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h2>Login</h2>

        <?php if (isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="Alamat Email" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Kata Sandi" required>
            </div>
            
            <div class="forgot-password">
                <a href="lupa_password.php">Lupa password?</a>
            </div>
            
            <button type="submit" name="login">Masuk Sekarang</button>
        </form>

        <p>Belum punya akun? <a href="register.php">Daftar Akun</a></p>
    </div>

</body>

</html>