<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];

if (!isset($_GET['id'])) {
    header("Location: dashboardeo.php");
    exit;
}

$id = intval($_GET['id']);

$cek = mysqli_query($conn, "
    SELECT * FROM kontrak 
    WHERE kontrak_id = $id AND eo_id = $eo_id
");

if (mysqli_num_rows($cek) == 0) {
    header("Location: dashboardeo.php");
    exit;
}

$hapus = mysqli_query($conn, "
    DELETE FROM kontrak 
    WHERE kontrak_id = $id
");

if ($hapus) {
    header("Location: dashboardeo.php?msg=hapus_sukses");
} else {
    header("Location: dashboardeo.php?msg=hapus_gagal");
}
exit;
?>