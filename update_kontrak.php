<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'klien') {
    header("Location: login.php");
    exit;
}

$kontrak_id = $_POST['kontrak_id'];

if (isset($_POST['approve'])) {

    mysqli_query($conn, "
        UPDATE kontrak 
        SET status_kontrak='disetujui', alasan_penolakan=NULL 
        WHERE kontrak_id='$kontrak_id'
    ");

}

if (isset($_POST['reject'])) {

    // ambil alasan
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan']);

    // validasi
    if (empty($alasan)) {
        echo "<script>alert('Alasan penolakan wajib diisi!'); window.history.back();</script>";
        exit;
    }

    mysqli_query($conn, "
        UPDATE kontrak 
        SET status_kontrak='ditolak', alasan_penolakan='$alasan' 
        WHERE kontrak_id='$kontrak_id'
    ");
}

header("Location: datakontrak.php");