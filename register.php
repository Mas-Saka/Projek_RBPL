<?php
include "config.php";

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $no_hp = $_POST['no_hp'];

    $panjang = strlen($password);
    $hurufkecil = false;
    $hurufbesar = false;
    $angka = false;

    for ($i = 0; $i < $panjang; $i++) {
        $karakter = $password[$i];
        if ($karakter >= 'a' && $karakter <= 'z') {
            $hurufkecil = true;
        } elseif ($karakter >= 'A' && $karakter <= 'Z') {
            $hurufbesar = true;
        } elseif ($karakter >= '0' && $karakter <= '9') {
            $angka = true;
        }
    }

    if ($panjang < 8) {
        $error = "Password harus minimal 8 karakter!";
    } elseif (!$hurufbesar) {
        $error = "Password harus mengandung huruf besar!";
    } elseif (!$angka) {
        $error = "Password harus mengandung angka!";
    } else {
        // Cek email sudah terdaftar atau belum
        $cek_email = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek_email) > 0) {
            $error = "Email sudah terdaftar, gunakan email lain!";
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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Peserta - Sistem Seminar Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Reset & Dasar */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Container Box */
        .register-box {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .register-box h2 {
            margin-bottom: 10px;
            color: #1e3c72;
            font-weight: 600;
            font-size: 26px;
        }

        .register-box p.subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        /* Form & Input */
        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .register-box input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ef;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .register-box input:focus {
            border-color: #2a5298;
            box-shadow: 0 0 8px rgba(42, 82, 152, 0.15);
        }

        /* Button */
        .register-box button {
            width: 100%;
            padding: 13px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
        }

        .register-box button:hover {
            background: #1e3c72;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.4);
        }

        /* Notifikasi */
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: left;
            border-left: 5px solid;
        }

        .alert-error {
            background: #ffe0e0;
            color: #e74c3c;
            border-color: #e74c3c;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #2e7d32;
        }

        .alert-success a {
            color: #2e7d32;
            font-weight: bold;
            text-decoration: underline;
        }

        /* Link Footer */
        .footer-link {
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .footer-link a {
            color: #2a5298;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="register-box">
        <h2>Buat Akun</h2>
        <p class="subtitle">Daftar sebagai peserta seminar sekarang</p>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <strong>Gagal:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <input type="text" name="nama" placeholder="Nama Lengkap" required 
                       value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
            </div>
            
            <div class="input-group">
                <input type="email" name="email" placeholder="Email Aktif" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="input-group">
                <input type="password" name="password" placeholder="Password (Min. 8 Karakter, A-Z, 0-9)" required>
            </div>
            
            <div class="input-group">
                <input type="text" name="no_hp" placeholder="Nomor WhatsApp (Contoh: 0812...)" required
                       value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>">
            </div>
            
            <button type="submit" name="register">Daftar Sekarang</button>
        </form>

        <div class="footer-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>

</body>

</html>