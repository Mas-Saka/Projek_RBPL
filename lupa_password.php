<?php
session_start();

if (!isset($_POST['kirim']) && !isset($_POST['verifikasi']) && !isset($_POST['reset'])) {
    $_SESSION['step'] = 1;
    unset($_SESSION['email']);
}

date_default_timezone_set("Asia/Jakarta");
$conn = mysqli_connect("localhost", "root", "", "seminar_online");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* =========================
   KIRIM OTP
========================= */
if (isset($_POST['kirim'])) {
    $email = $_POST['email'] ?? $_SESSION['email'];
    $_SESSION['email'] = $email;

    $otp = rand(100000, 999999);
    $expired = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    mysqli_query($conn, "DELETE FROM password_resets WHERE email='$email'");
    mysqli_query($conn, "INSERT INTO password_resets(email,otp_code,expired_at) VALUES('$email','$otp','$expired')");

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'isyakamaulana@gmail.com';
        $mail->Password = 'csfv kgrr wbps lpbp'; // Gunakan App Password Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('isyakamaulana@gmail.com', 'Seminar Online');
        $mail->addAddress($email);

        $mail->Subject = "Kode Reset Password";
        $mail->isHTML(true);
        $mail->Body = "<h3>Reset Password</h3><p>Kode verifikasi kamu adalah: <b>$otp</b></p><p>Kode berlaku selama 5 menit.</p>";

        $mail->send();
        $_SESSION['step'] = 2;
    } catch (Exception $e) {
        $error = "Gagal mengirim email: {$mail->ErrorInfo}";
    }
}

/* =========================
   VERIFIKASI OTP
========================= */
if (isset($_POST['verifikasi'])) {
    $email = $_SESSION['email'];
    $otp = $_POST['otp'];

    $data = mysqli_query($conn, "SELECT * FROM password_resets WHERE email='$email' AND otp_code='$otp' AND expired_at > NOW()");

    if (mysqli_num_rows($data) > 0) {
        $_SESSION['step'] = 3;
    } else {
        $error = "Kode OTP salah atau sudah kadaluarsa!";
    }
}

/* =========================
   RESET PASSWORD
========================= */
if (isset($_POST['reset'])) {
    $email = $_SESSION['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if ($password == $password2) {
        mysqli_query($conn, "UPDATE users SET password='$password' WHERE email='$email'");
        mysqli_query($conn, "DELETE FROM password_resets WHERE email='$email'");
        $success_reset = true;
        session_destroy();
    } else {
        $error = "Konfirmasi password tidak cocok!";
    }
}

$step = $_SESSION['step'] ?? 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Seminar Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #2a5298; /* Solid Color sesuai request */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h3 { color: #1e3c72; margin-bottom: 10px; font-weight: 600; }
        p.subtitle { color: #666; font-size: 14px; margin-bottom: 25px; }

        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 2px solid #e1e8ef;
            border-radius: 10px;
            outline: none;
            transition: 0.3s;
        }

        input:focus { border-color: #2a5298; }

        button {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 15px;
            transition: 0.3s;
        }

        button:hover { background: #1e3c72; transform: translateY(-2px); }

        .error { color: #e74c3c; background: #ffe0e0; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; }
        .success-box { color: #2e7d32; background: #e8f5e9; padding: 20px; border-radius: 10px; }

        /* Style Kotak OTP */
        .otp-inputs { display: flex; justify-content: space-between; gap: 8px; margin: 20px 0; }
        .otp-field {
            width: 45px;
            height: 50px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            border: 2px solid #e1e8ef;
            border-radius: 10px;
            background: #f8f9fa;
        }

        #timer { font-size: 13px; color: #888; margin-top: 15px; }
        .resend-link { background: none; color: #2a5298; padding: 0; width: auto; box-shadow: none; font-size: 14px; margin-top: 5px; }
        .resend-link:hover { transform: none; text-decoration: underline; background: none; }
    </style>
</head>
<body>

<div class="container">
    <?php if (isset($success_reset)) { ?>
        <div class="success-box">
            <h3>Berhasil!</h3>
            <p>Password Anda telah diperbarui.</p>
            <a href="login.php"><button>Kembali ke Login</button></a>
        </div>
    <?php } else { ?>

        <?php if ($step == 1) { ?>
            <h3>Lupa Password</h3>
            <p class="subtitle">Masukkan email terdaftar untuk menerima OTP</p>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email Anda" required>
                <button name="kirim">Kirim Kode OTP</button>
            </form>
            <p class="footer-link" style="margin-top:20px; font-size:14px;"><a href="login.php" style="color:#2a5298; text-decoration:none;">Kembali ke Login</a></p>

        <?php } elseif ($step == 2) { ?>
            <h3>Verifikasi OTP</h3>
            <p class="subtitle">Kode telah dikirim ke email Anda</p>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
            
            <form method="POST" id="otpForm">
                <div class="otp-inputs">
                    <input type="text" maxlength="1" class="otp-field" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp-field" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp-field" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp-field" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp-field" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp-field" pattern="[0-9]" inputmode="numeric" required>
                </div>
                <input type="hidden" name="otp" id="otpValue">
                <button name="verifikasi">Verifikasi Kode</button>
            </form>

            <p id="timer">Kirim ulang dalam <span id="countdown">30</span> detik</p>
            <form method="POST">
                <button id="resendBtn" name="kirim" class="resend-link" style="display:none;">Kirim Ulang Kode</button>
            </form>

            <script>
                const inputs = document.querySelectorAll(".otp-field");
                inputs.forEach((input, index) => {
                    input.addEventListener("input", (e) => {
                        if (e.target.value.length === 1 && index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                        updateOTP();
                    });
                    input.addEventListener("keydown", (e) => {
                        if (e.key === "Backspace" && e.target.value === "" && index > 0) {
                            inputs[index - 1].focus();
                        }
                    });
                });

                function updateOTP() {
                    let otp = "";
                    inputs.forEach(i => otp += i.value);
                    document.getElementById("otpValue").value = otp;
                }

                let waktu = 30;
                let timer = setInterval(function () {
                    waktu--;
                    document.getElementById("countdown").innerText = waktu;
                    if (waktu <= 0) {
                        clearInterval(timer);
                        document.getElementById("timer").style.display = "none";
                        document.getElementById("resendBtn").style.display = "inline-block";
                    }
                }, 1000);
            </script>

        <?php } elseif ($step == 3) { ?>
            <h3>Reset Password</h3>
            <p class="subtitle">Buat password baru yang aman</p>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
            
            <form method="POST">
                <div style="position:relative;">
                    <input type="password" id="pass1" name="password" placeholder="Password Baru" required>
                    <span onclick="togglePass()" style="position:absolute; right:15px; top:22px; cursor:pointer; opacity:0.6;">👁</span>
                </div>
                <input type="password" id="pass2" name="password2" placeholder="Konfirmasi Password" required>
                <button name="reset">Simpan Password</button>
            </form>

            <script>
                function togglePass() {
                    var x = document.getElementById("pass1");
                    var y = document.getElementById("pass2");
                    x.type = (x.type === "password") ? "text" : "password";
                    y.type = (y.type === "password") ? "text" : "password";
                }
            </script>
        <?php } ?>

    <?php } ?>
</div>

</body>
</html>