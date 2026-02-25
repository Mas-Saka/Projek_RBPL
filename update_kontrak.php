<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'klien') {
    header("Location: login.php");
    exit;
}

$kontrak_id = $_POST['kontrak_id'];

if(isset($_POST['approve'])){
    mysqli_query($conn, "UPDATE kontrak SET status_kontrak='disetujui' WHERE kontrak_id = $kontrak_id");
}

if(isset($_POST['reject'])){ mysqli_query($conn, "UPDATE kontrak  SET status_kontrak='ditolak' WHERE kontrak_id=$kontrak_id");
}

header("Location: datakontrak.php");