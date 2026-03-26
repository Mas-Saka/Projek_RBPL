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

// Update data
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

    // Upload gambar baru (optional)
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
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
        }

        .sidebar {
            position: fixed;
            width: 220px;
            height: 100%;
            background: #1e3c72;
            color: white;
            padding: 20px;
        }

        .sidebar a {
            display: block;
            margin: 12px 0;
            color: white;
            text-decoration: none;
        }

        .main {
            margin-left: 240px;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        img.preview {
            max-width: 150px;
            margin-top: 10px;
            border-radius: 10px;
        }

        .btn {
            background: #1e3c72;
            color: white;
            padding: 10px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;

            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            display: block;
            margin: 5% auto;
            max-width: 70%;
            border-radius: 10px;
            animation: zoomIn 0.3s ease;
        }

        .close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 35px;
            cursor: pointer;
        }

        /* ANIMASI */
        @keyframes zoomIn {
            from {
                transform: scale(0.7);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>EO Panel</h2>
        <a href="dashboardeo.php">Dashboard</a>
        <a href="seminar.php">Kelola Seminar</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main">
        <div class="card">
            <h2>Edit Seminar</h2>

            <form method="POST" enctype="multipart/form-data">

                <input type="text" name="judul_seminar" value="<?= $data['judul_seminar']; ?>">

                <textarea name="deskripsi"><?= $data['deskripsi']; ?></textarea>

                <input type="text" name="kategori" value="<?= $data['kategori']; ?>">

                <label>Gambar Saat Ini:</label><br>
                <img src="upload/<?= $data['gambar']; ?>" class="preview" style="cursor:pointer;"
                    onclick="showImage(this.src)">

                <input type="file" name="gambar">

                <div class="row">
                    <input type="date" name="tanggal" value="<?= $data['tanggal']; ?>">
                    <input type="number" name="kuota" value="<?= $data['kuota']; ?>">
                </div>

                <div class="row">
                    <input type="time" name="jam_mulai" value="<?= $data['jam_mulai']; ?>">
                    <input type="time" name="jam_selesai" value="<?= $data['jam_selesai']; ?>">
                </div>

                <input type="number" name="biaya" value="<?= $data['biaya']; ?>">

                <select name="narasumber_id">
                    <?php while ($n = mysqli_fetch_assoc($narasumber)) { ?>
                        <option value="<?= $n['id']; ?>" <?= $data['narasumber_id'] == $n['id'] ? 'selected' : '' ?>>
                            <?= $n['nama']; ?>
                        </option>
                    <?php } ?>
                </select>

                <select name="platform">
                    <option <?= $data['platform'] == 'Zoom' ? 'selected' : '' ?>>Zoom</option>
                    <option <?= $data['platform'] == 'Google Meet' ? 'selected' : '' ?>>Google Meet</option>
                </select>

                <input type="text" name="link_meeting" value="<?= $data['link_meeting']; ?>">

                <button class="btn" name="update">Update</button>

            </form>
        </div>
    </div>

</body>
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImg">
</div>

<script>
    function showImage(src) {
        document.getElementById("imageModal").style.display = "block";
        document.getElementById("modalImg").src = src;
    }

    function closeModal() {
        document.getElementById("imageModal").style.display = "none";
    }

    // klik luar = close
    window.onclick = function (event) {
        let modal = document.getElementById("imageModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</html>