<?php
session_start();
include "config.php";

$id = $_GET['id'];

// Ambil data seminar + narasumber
$data = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT seminar.*, users.nama AS narasumber
    FROM seminar
    LEFT JOIN users ON seminar.narasumber_id = users.id
    WHERE seminar.seminar_id = $id
"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Seminar</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f4f7f6;
    color: #333;
}

/* NAVBAR */
header {
    background: #fff;
    height: 70px;
    display: flex;
    align-items: center;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.container {
    width: 85%;
    margin: auto;
}

nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-weight: 700;
    font-size: 22px;
    color: #3498db;
}

.nav-menu a {
    margin-left: 20px;
    text-decoration: none;
    color: #555;
    font-weight: 500;
}

.nav-menu a:hover {
    color: #3498db;
}

/* HERO */
.hero {
    margin-top: 70px;
    height: 350px;
    position: relative;
    overflow: hidden;
}

.hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
}

.hero-text {
    position: absolute;
    bottom: 30px;
    left: 8%;
    color: white;
}

.hero-text h1 {
    font-size: 32px;
    font-weight: 700;
}

/* DETAIL CARD */
.detail-card {
    background: white;
    margin: -80px auto 30px;
    width: 85%;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    position: relative;
    z-index: 2;
}

.info {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.info p {
    font-size: 14px;
    color: #555;
}

/* BADGE */
.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.gratis {
    background: #d4edda;
    color: #155724;
}

.berbayar {
    background: #fff3cd;
    color: #856404;
}

/* DESKRIPSI */
.deskripsi {
    margin-top: 20px;
    line-height: 1.7;
    max-width: 800px;
}

/* BUTTON */
.btn-daftar {
    display: inline-block;
    margin-top: 25px;
    padding: 12px 25px;
    background: #3498db;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}

.btn-daftar:hover {
    background: #2980b9;
}

/* RESPONSIVE */
@media(max-width:768px){
    .info {
        grid-template-columns: 1fr;
    }

    .hero-text h1 {
        font-size: 22px;
    }

    .detail-card {
        margin-top: -60px;
        padding: 20px;
    }

    .btn-daftar {
        width: 100%;
        text-align: center;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<header>
    <div class="container">
        <nav>
            <div class="logo">SeminarOnline</div>
            <div class="nav-menu">
                <a href="index.php">Home</a>
                <a href="semua_seminar.php">Webinar</a>
            </div>
        </nav>
    </div>
</header>

<!-- HERO -->
<div class="hero">
    <?php if(!empty($data['gambar'])){ ?>
        <img src="upload/<?= $data['gambar']; ?>">
    <?php } else { ?>
        <img src="https://via.placeholder.com/1200x400">
    <?php } ?>

    <div class="hero-overlay"></div>

    <div class="hero-text">
        <h1><?= $data['judul_seminar']; ?></h1>
    </div>
</div>

<!-- DETAIL -->
<div class="detail-card">

    <h2><?= $data['judul_seminar']; ?></h2>

    <!-- BADGE -->
    <?php if($data['biaya'] == 0){ ?>
        <span class="badge gratis">Gratis</span>
    <?php } else { ?>
        <span class="badge berbayar">Berbayar</span>
    <?php } ?>

    <div class="info">
        <p><b>Narasumber:</b> <?= $data['narasumber']; ?></p>
        <p><b>Tanggal:</b> <?= $data['tanggal']; ?></p>
        <p><b>Jam:</b> <?= $data['jam_mulai']; ?> - <?= $data['jam_selesai']; ?></p>
        <p><b>Kuota:</b> <?= $data['kuota']; ?></p>
        <p><b>Platform:</b> <?= $data['platform']; ?></p>
    </div>

    <div class="deskripsi">
        <?= nl2br($data['deskripsi']); ?>
    </div>

    <!-- CTA -->
    <a href="#" class="btn-daftar">Daftar Sekarang</a>

</div>

</body>
</html>