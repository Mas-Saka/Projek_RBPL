<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];
$id = $_GET['id'];

// Ambil data seminar
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM seminar WHERE seminar_id=$id AND eo_id=$eo_id"));

// Ambil narasumber
$narasumber = mysqli_query($conn, "SELECT id, nama FROM users WHERE role='narasumber'");

// Update
if (isset($_POST['update'])) {

    $judul = $_POST['judul_seminar'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'];
    $biaya = $_POST['biaya'];
    $kuota = $_POST['kuota'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $platform = $_POST['platform'];
    $link = $_POST['link_meeting'];
    $narasumber_id = $_POST['narasumber_id'];

    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $folder = "upload/";

    if ($gambar != "") {
        move_uploaded_file($tmp, $folder . $gambar);

        mysqli_query($conn, "UPDATE seminar SET 
            judul_seminar='$judul',
            deskripsi='$deskripsi',
            kategori='$kategori',
            gambar='$gambar',
            biaya='$biaya',
            kuota='$kuota',
            tanggal='$tanggal',
            jam_mulai='$jam_mulai',
            jam_selesai='$jam_selesai',
            platform='$platform',
            link_meeting='$link',
            narasumber_id='$narasumber_id'
            WHERE seminar_id=$id
        ");
    } else {
        mysqli_query($conn, "UPDATE seminar SET 
            judul_seminar='$judul',
            deskripsi='$deskripsi',
            kategori='$kategori',
            biaya='$biaya',
            kuota='$kuota',
            tanggal='$tanggal',
            jam_mulai='$jam_mulai',
            jam_selesai='$jam_selesai',
            platform='$platform',
            link_meeting='$link',
            narasumber_id='$narasumber_id'
            WHERE seminar_id=$id
        ");
    }

    echo "<script>alert('Seminar berhasil diupdate!'); window.location='seminar.php';</script>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Seminar</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
        }

        /* TOPBAR */
        .topbar {
            background: #1e3c72;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
        }

        .burger {
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100%;
            background: #2c3e50;
            padding: 20px;
            transition: 0.3s;
        }

        .sidebar.hide {
            left: -230px;
        }

        .sidebar h2 {
            color: white;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            margin: 12px 0;
            text-decoration: none;
            padding: 8px;
            border-radius: 6px;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* MAIN */
        .main {
            margin-left: 220px;
            transition: 0.3s;
        }

        .main.full {
            margin-left: 0;
        }

        /* CARD */
        .card {
            background: white;
            margin: 20px;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            width: auto;
            max-width: 900px;
            margin-left: auto;
        }

        /* FORM */
        form label {
            font-weight: 600;
            font-size: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            transition: 0.2s;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #2a5298;
            outline: none;
        }

        /* GRID */
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* BUTTON */
        .btn {
            background: #2a5298;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn:hover {
            background: #163d7a;
        }

        /* IMAGE */
        .preview {
            max-width: 150px;
            border-radius: 10px;
            cursor: pointer;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
        }

        .modal-content {
            display: block;
            margin: 5% auto;
            max-width: 60%;
            border-radius: 10px;
        }

        .close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 30px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="sidebar" id="sidebar">
        <h2>EO Panel</h2>
        <a href="dashboardeo.php">Dashboard</a>
        <a href="seminar.php">Kelola Seminar</a>
        <a href="logout.php" style=" width:;color:red; text-decoration:none; padding:8px 12px; border-radius:8px;
            transition:0.3s;" onmouseover="this.style.background='red'; this.style.color='white';"
            onmouseout="this.style.background='transparent'; this.style.color='red';"">Logout</a>
</div>

<div class=" main" id="main">

            <div class="topbar">
                <div class="burger" onclick="toggleMenu()">☰</div>
                <div>Edit Seminar</div>
            </div>

            <div class="card">
                <h2>Edit Seminar</h2>

                <form method="POST" enctype="multipart/form-data">

                    <label>Judul</label>
                    <input type="text" name="judul_seminar" value="<?= $data['judul_seminar']; ?>">

                    <label>Deskripsi</label>
                    <textarea name="deskripsi"><?= $data['deskripsi']; ?></textarea>

                    <label>Kategori</label>
                    <input type="text" name="kategori" value="<?= $data['kategori']; ?>">

                    <label>Gambar Saat Ini</label><br>
                    <img src="upload/<?= $data['gambar']; ?>" class="preview" onclick="showImage(this.src)">
                    <input type="file" name="gambar">

                    <div class="row">
                        <div>
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" value="<?= $data['tanggal']; ?>">
                        </div>
                        <div>
                            <label>Kuota</label>
                            <input type="number" name="kuota" value="<?= $data['kuota']; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div>
                            <label>Jam Mulai</label>
                            <input type="time" name="jam_mulai" value="<?= $data['jam_mulai']; ?>">
                        </div>
                        <div>
                            <label>Jam Selesai</label>
                            <input type="time" name="jam_selesai" value="<?= $data['jam_selesai']; ?>">
                        </div>
                    </div>

                    <label>Biaya</label>
                    <input type="number" name="biaya" value="<?= $data['biaya']; ?>">

                    <label>Narasumber</label>
                    <select name="narasumber_id">
                        <?php while ($n = mysqli_fetch_assoc($narasumber)) { ?>
                            <option value="<?= $n['id']; ?>" <?= $data['narasumber_id'] == $n['id'] ? 'selected' : '' ?>>
                                <?= $n['nama']; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label>Platform</label>
                    <select name="platform">
                        <option <?= $data['platform'] == 'Zoom' ? 'selected' : '' ?>>Zoom</option>
                        <option <?= $data['platform'] == 'Google Meet' ? 'selected' : '' ?>>Google Meet</option>
                    </select>

                    <label>Link Meeting</label>
                    <input type="text" name="link_meeting" value="<?= $data['link_meeting']; ?>">

                    <button class="btn" name="update">Update</button>

                </form>
            </div>
    </div>

    <!-- MODAL -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImg">
    </div>

    <script>
        function toggleMenu() {
            document.getElementById("sidebar").classList.toggle("hide");
            document.getElementById("main").classList.toggle("full");
        }

        function showImage(src) {
            document.getElementById("imageModal").style.display = "block";
            document.getElementById("modalImg").src = src;
        }

        function closeModal() {
            document.getElementById("imageModal").style.display = "none";
        }

        window.onclick = function (e) {
            let modal = document.getElementById("imageModal");
            if (e.target == modal) modal.style.display = "none";
        }
    </script>

</body>

</html>