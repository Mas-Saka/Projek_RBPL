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

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $_SESSION['email'] = $email;
    } else {
        $email = $_SESSION['email'];
    }

    $otp = rand(100000, 999999);
    $expired = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    mysqli_query($conn, "DELETE FROM password_resets WHERE email='$email'");
    mysqli_query($conn, "INSERT INTO password_resets(email,otp_code,expired_at) VALUES('$email','$otp','$expired')");

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'isyakamaulana@gmail.com';
    $mail->Password = 'csfv kgrr wbps lpbp';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('isyakamaulana@gmail.com', 'Reset Password');
    $mail->addAddress($email);

    $mail->Subject = "Kode Reset Password";
    $mail->Body = "Kode verifikasi kamu adalah: $otp";

    $mail->send();

    $_SESSION['step'] = 2;
}


/* =========================
   VERIFIKASI OTP
========================= */
if (isset($_POST['verifikasi'])) {

    $email = $_SESSION['email'];
    $otp = $_POST['otp'];

    $data = mysqli_query($conn, "SELECT * FROM password_resets WHERE email='$email' AND otp_code='$otp' AND expired_at>NOW()");

    if (mysqli_num_rows($data) > 0) {
        $_SESSION['step'] = 3;
    } else {
        echo "<p class='error'>Kode OTP salah atau sudah kadaluarsa</p>";
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

        echo "<p class='success'>Password berhasil diganti</p>";
        echo "<a href='login.php'>Login</a>";

        session_destroy();
        exit;

    } else {
        echo "<p class='error'>Password tidak sama</p>";
    }
}

$step = $_SESSION['step'] ?? 1;
?>

<!DOCTYPE html>
<html>

<head>

    <title>Lupa Password</title>

    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }

        .error {
            color: red;
            text-align: center;
        }

        .success {
            color: green;
            text-align: center;
        }

        .otp {
            width: 25px;
            height: 25px;
            text-align: center;
            font-size: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>

</head>

<body>

    <div class="container">

        <?php if ($step == 1) { ?>

            <h3>Lupa Password</h3>

            <form method="POST">
                <input type="email" name="email" placeholder="Masukkan Email" required>
                <button name="kirim">Kirim Kode</button>
            </form>

        <?php } elseif ($step == 2 && isset($_SESSION['email'])) { ?>

            <h3>Masukkan Kode OTP</h3>

            <form method="POST" id="otpForm">

                <div style="display:flex; justify-content:space-between; gap:5px; margin-bottom:15px;">

                    <input type="text" maxlength="1" class="otp" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" maxlength="1" class="otp" pattern="[0-9]" inputmode="numeric" required>

                </div>

                <input type="hidden" name="otp" id="otpValue">

                <button name="verifikasi">Verifikasi</button>

            </form>

            <p id="timer">Kirim ulang kode dalam 30 detik</p>

            <form method="POST">
                <input type="hidden" name="email" value="<?php echo $_SESSION['email']; ?>">
                <button id="resendBtn" name="kirim" style="display:none;">Kirim Ulang Kode</button>
            </form>

            <script>

                const inputs = document.querySelectorAll(".otp");

                inputs.forEach((input, index) => {

                    input.addEventListener("input", () => {

                        if (input.value.length == 1 && index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }

                        updateOTP();

                    });

                });

                function updateOTP() {

                    let otp = "";

                    inputs.forEach(i => {
                        otp += i.value;
                    });

                    document.getElementById("otpValue").value = otp;

                }

                let waktu = 30;

                let timer = setInterval(function () {

                    waktu--;

                    document.getElementById("timer").innerHTML =
                        "Kirim ulang kode dalam " + waktu + " detik";

                    if (waktu <= 0) {

                        clearInterval(timer);

                        document.getElementById("timer").innerHTML =
                            "Tidak menerima kode?";

                        document.getElementById("resendBtn").style.display = "block";

                    }

                }, 1000);

            </script>

        <?php } elseif ($step == 3 && isset($_SESSION['email'])) { ?>

            <h3>Reset Password</h3>

            <form method="POST">

                <div style="position:relative;">

                    <input type="password" id="pass1" name="password" placeholder="Password Baru" required>

                    <span onclick="togglePass()" style="position:absolute; right:10px; top:12px; cursor:pointer;">
                        👁
                    </span>

                </div>

                <input type="password" id="pass2" name="password2" placeholder="Ulangi Password" required>

                <br><br>

                <button name="reset">Reset Password</button>

            </form>

            <script>

                function togglePass() {

                    var x = document.getElementById("pass1");
                    var y = document.getElementById("pass2");

                    if (x.type === "password") {
                        x.type = "text";
                        y.type = "text";
                    } else {
                        x.type = "password";
                        y.type = "password";
                    }

                }

            </script>

        <?php } ?>

    </div>

</body>

</html>