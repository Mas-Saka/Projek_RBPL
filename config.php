<?php
// Konfigurasi Database
$host = "localhost";
$user = "root";
$pass = "";
$db = "seminar_online";

// Koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set timezone biar tanggal sesuai Indonesia
date_default_timezone_set("Asia/Jakarta");
?>