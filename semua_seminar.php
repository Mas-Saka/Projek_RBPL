<?php
session_start();
include "config.php";

$keyword = isset($_GET['search']) ? $_GET['search'] : "";

$query = "SELECT seminar.seminar_id,
            seminar.judul_seminar,
            seminar.tanggal,
            seminar.jam_mulai,
            seminar.gambar,
            users.nama AS narasumber
        FROM seminar
        LEFT JOIN users ON seminar.narasumber_id = users.id
        WHERE seminar.status='aktif'";

if (!empty($keyword)) {
    $query .= " AND seminar.judul_seminar LIKE '%$keyword%'";
}

$query .= " ORDER BY seminar.seminar_id DESC";

$data = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Semua Seminar</title>

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
    height: 80px;
    display: flex;
    align-items: center;
    position: fixed;
    top: 0;
    width: 100%;
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
    font-size: 24px;
    font-weight: 700;
    color: #3498db;
}

.nav-menu {
    display: flex;
    gap: 20px;
}

.nav-menu a {
    color: #555;
    text-decoration: none;
    font-weight: 500;
}

.nav-menu a:hover {
    color: #3498db;
}

/* MAIN */
.main {
    padding-top: 110px;
    padding-bottom: 50px;
}

.title {
    text-align: center;
    margin-bottom: 30px;
}

.title h2 {
    font-size: 28px;
    margin-bottom: 10px;
}

/* SEARCH */
.search-box {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
    gap: 10px;
}

.search-box input {
    padding: 10px 15px;
    width: 280px;
    border: 1px solid #ccc;
    border-radius: 6px;
    outline: none;
    transition: 0.3s;
}

.search-box input:focus {
    border-color: #3498db;
    box-shadow: 0 0 5px rgba(52,152,219,0.4);
}

.search-box button {
    padding: 10px 20px;
    background: #3498db;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s;
}

.search-box button:hover {
    background: #2980b9;
}

/* GRID */
.grid {
    display: grid;
    gap: 25px;
}

@media(min-width:1024px){
    .grid { grid-template-columns: repeat(4,1fr); }
}
@media(min-width:768px) and (max-width:1023px){
    .grid { grid-template-columns: repeat(2,1fr); }
}
@media(max-width:767px){
    .grid { grid-template-columns: 1fr; }
}

/* CARD */
.card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-8px);
}

.card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.card-content {
    padding: 15px;
}

.card-content h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
}

.card-content p {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

/* BUTTON */
.btn-detail {
    display: block;
    text-align: center;
    margin-top: 10px;
    padding: 8px;
    background: #f1c40f;
    color: black;
    border-radius: 5px;
    font-weight: 600;
    transition: 0.3s;
}

.btn-detail:hover {
    background: #d4ac0d;
}

/* BACK BUTTON */
.back {
    display: block;
    width: fit-content;
    margin: 40px auto 0;
    padding: 12px 25px;
    background: #2c3e50;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    transition: 0.3s;
}

.back:hover {
    background: #1a252f;
}

/* EMPTY */
.empty {
    text-align: center;
    color: #777;
    margin-top: 30px;
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
                <a href="index.php#fitur">Fitur</a>
                <a href="index.php#webinar">Webinar</a>
            </div>
        </nav>
    </div>
</header>

<div class="container main">

<div class="title">
    <h2>Semua Seminar</h2>
    <p>Temukan seminar terbaik untuk meningkatkan skill anda</p>
</div>

<!-- SEARCH -->
<form method="GET" class="search-box">
    <input type="text" name="search" placeholder="Cari seminar..." value="<?= $keyword ?>">
    <button type="submit">Cari</button>
</form>

<!-- DATA -->
<div class="grid">
<?php if(mysqli_num_rows($data) > 0){ ?>
    <?php while($d = mysqli_fetch_assoc($data)){ ?>
        <div class="card">
            <?php if(!empty($d['gambar'])){ ?>
                <img src="upload/<?= $d['gambar']; ?>">
            <?php } else { ?>
                <img src="https://via.placeholder.com/400x200">
            <?php } ?>

            <div class="card-content">
                <h3><?= $d['judul_seminar']; ?></h3>
                <p>Narasumber: <?= $d['narasumber']; ?></p>
                <p><?= $d['tanggal']; ?> | <?= $d['jam_mulai']; ?></p>

                <a href="detail_seminar_peserta.php?id=<?= $d['seminar_id']; ?>" class="btn-detail">
                    Detail
                </a>
            </div>
        </div>
    <?php } ?>
<?php } else { ?>
    <p class="empty">Belum ada seminar tersedia</p>
<?php } ?>
</div>

<a href="index.php" class="back">Kembali ke Beranda</a>

</div>

</body>
</html>