<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];

// Ambil seminar
$seminar = mysqli_query($conn, "SELECT * FROM seminar WHERE eo_id=$eo_id ORDER BY seminar_id DESC");

// Ambil narasumber
$narasumber = mysqli_query($conn, "SELECT id, nama FROM users WHERE role='narasumber'");

// Tambah seminar
if (isset($_POST['submit'])) {

    $judul_seminar = $_POST['judul_seminar'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'];
    $biaya = $_POST['biaya'];
    $kuota = $_POST['kuota'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $platform = $_POST['platform'];
    $link_meeting = $_POST['link_meeting'];
    $narasumber_id = $_POST['narasumber_id'];
    $status = "draft";

    // Upload gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $folder = "upload/";

    if ($gambar != "") {
        move_uploaded_file($tmp, $folder . $gambar);
    }

    mysqli_query($conn, "INSERT INTO seminar (judul_seminar, deskripsi, kategori, gambar, biaya, kuota, tanggal, jam_mulai, jam_selesai, platform, link_meeting, status, eo_id, narasumber_id) VALUES ('$judul_seminar','$deskripsi','$kategori','$gambar','$biaya','$kuota','$tanggal','$jam_mulai','$jam_selesai','$platform','$link_meeting','$status','$eo_id','$narasumber_id') ");

    echo "<script>alert('Seminar berhasil dibuat!'); window.location='seminar.php';</script>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Seminar</title>
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

        .topbar {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f0f0f0;
        }

        tr:hover {
            background-color: #f5f7fb;
        }

        /* JUDUL ELLIPSIS */
        .judul {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* BADGE */
        .badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .aktif {
            background: #d4edda;
            color: #155724;
        }

        .draft {
            background: #fff3cd;
            color: #856404;
        }

        /* BUTTON */
        .btn-detail, .btn-edit {
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: 0.2s;
        }

        .btn-detail {
            background: #2a5298;
            color: white;
            margin-right: 5px;
        }

        .btn-detail:hover {
            background: #163d7a;
        }

        .btn-edit {
            background: #27ae60;
            color: white;
        }

        .btn-edit:hover {
            background: #1e8449;
        }

        /* FORM */
        input, select, textarea {
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
            max-width: 100%;
            margin-top: 10px;
            border-radius: 10px;
            display: none;
        }

        .btn {
            background: #1e3c72;
            color: white;
            padding: 10px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>EO Panel</h2>
        <a href="dashboardeo.php">Dashboard</a>
        <a href="seminar.php">Kelola Seminar</a>
        <a href="buat_kontrak.php">Kontrak</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="topbar">
            <h2>Kelola Seminar</h2>
        </div>

        <!-- LIST SEMINAR -->
        <div class="card">
            <h3>Daftar Seminar</h3>

            <table style="table-layout: fixed;">
                <tr>
                    <th style="width: 250px;">Judul</th>
                    <th>Tanggal</th>
                    <th>Kuota</th>
                    <th>Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>

                <?php while ($s = mysqli_fetch_assoc($seminar)) { ?>
                    <tr class="row-hover">

                        <td style="max-width:250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                            title="<?= $s['judul_seminar']; ?>">
                            <?= $s['judul_seminar']; ?>
                        </td>

                        <td><?= $s['tanggal']; ?></td>
                        <td><?= $s['kuota']; ?></td>

                        <td>
                            <?php if ($s['status'] == 'aktif') { ?>
                                <span class="badge aktif">Aktif</span>
                            <?php } else { ?>
                                <span class="badge draft">Draft</span>
                            <?php } ?>
                        </td>

                        <td style="text-align:center;">
                            <a href="detail_seminar.php?id=<?= $s['seminar_id']; ?>">
                                <button class="btn-detail">Detail</button>
                            </a>

                            <a href="edit_seminar.php?id=<?= $s['seminar_id']; ?>">
                                <button class="btn-edit">Edit</button>
                            </a>
                        </td>

                    </tr>
                <?php } ?>
            </table>
        </div>

        <!-- FORM TAMBAH -->
        <div class="card">
            <h3>Tambah Seminar</h3>

            <form method="POST" enctype="multipart/form-data">

                <input type="text" name="judul_seminar" placeholder="Judul Seminar" required>
                <textarea name="deskripsi" placeholder="Deskripsi"></textarea>
                <input type="text" name="kategori" placeholder="Kategori">

                <input type="file" name="gambar" onchange="previewImage(event)">
                <img id="preview" class="preview">

                <div class="row">
                    <input type="date" name="tanggal">
                    <input type="number" name="kuota" placeholder="Kuota">
                </div>

                <div class="row">
                    <input type="time" name="jam_mulai">
                    <input type="time" name="jam_selesai">
                </div>

                <input type="number" name="biaya" placeholder="Biaya">

                <select name="narasumber_id">
                    <option>Pilih Narasumber</option>
                    <?php while ($n = mysqli_fetch_assoc($narasumber)) { ?>
                        <option value="<?= $n['id']; ?>"><?= $n['nama']; ?></option>
                    <?php } ?>
                </select>

                <select name="platform">
                    <option>Zoom</option>
                    <option>Google Meet</option>
                </select>

                <input type="text" name="link_meeting" placeholder="Link Meeting">

                <button class="btn" name="submit">Simpan</button>

            </form>
        </div>

    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById('preview');
                output.src = reader.result;
                output.style.display = "block";
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>

</body>

</html>