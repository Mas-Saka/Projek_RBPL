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

$nama_eo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM users WHERE id=$eo_id"))['nama'];


?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Seminar</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #2a5298);
            margin: 0;
        }

        .topbar {
            background: #1e3c72;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            border-radius: 10px 10px;
        }

        .burger {
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 190px;
            height: 100%;
            background: #2c3e50;
            padding: 20px;
            transition: 0.3s;
        }

        .sidebar.hide {
            left: -230px;
        }

        .sidebar a {
            display: block;
            color: white;
            margin: 15px 0;
            text-decoration: none;
        }


        .main {
            margin-left: 230px;
            padding: 20px;
            transition: 0.3s;
        }

        .main.full {
            margin-left: 0;
        }


        .card {
            margin-top: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
            table-layout: fixed;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            /* GARIS SEMUA SISI */
            text-align: left;
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
            cursor: pointer;
            word-warp: break-word;  
        }

        /* SAAT DIBUKA */
        .judul.open {
            white-space: normal;
            overflow: visible;
            word-break: break-word;
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
        .btn-detail,
        .btn-edit {
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
        input,
        select,
        textarea {
            width: 98%;
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
    <div class="sidebar" id="sidebar">
        <h2 style="margin-bottom: 12px; color : white; font-size: 24px;">Halaman EO</h2>
        <div class="box" style="background: #611c07; padding: 10px; border-radius: 30px; margin-bottom: 10px;">
            <h3 style="color:white; font-size: 14px;margin: 2px;"> <?= $nama_eo . " -- Event Organizer" ?></h3>
        </div>
        <a href="dashboardeo.php">Dashboard</a>
        <a href="seminar.php">Kelola Seminar</a>
        <a href="buat_kontrak.php">Kontrak</a>
        <a href="logout.php" style="width:30%;color:red; text-decoration:none; padding:8px 12px; border-radius:8px;
            transition:0.3s;" onmouseover="this.style.background='red'; this.style.color='white';"
            onmouseout="this.style.background='transparent'; this.style.color='red';"">Logout</a>
    </div>

    <!-- MAIN -->
    <div class=" main" id="main">
            <div class="topbar">
                <div class="burger" onclick="toggleMenu()">☰</div>
                <div>Dashboard EO - Sistem Manajemen Seminar</div>
            </div>


            <!-- LIST SEMINAR -->
            <div class="card">
                <h3>Daftar Seminar</h3>

                <div>

                    <table>
                        <tr>
                            <th>No</th>
                            <th style="width: 250px;">Judul</th>
                            <th>Tanggal</th>
                            <th>Kuota</th>
                            <th>Status</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>

                        <?php $no = 1;
                        while ($s = mysqli_fetch_assoc($seminar)) { ?>
                            <tr class="row-hover">
                                <td><?= $no++; ?></td>
                                <td onclick="toggleDetail(this)" class="judul"
                                    data-full="<?= htmlspecialchars($s['judul_seminar']); ?>">

                                    <?= strlen($s['judul_seminar']) > 30
                                        ? substr($s['judul_seminar'], 0, 30) . "..."
                                        : $s['judul_seminar']; ?>
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
                        <input type="number" name="kuota" min="1" step="1" placeholder="Kuota">
                    </div>

                    <div class="row">
                        <input type="time" name="jam_mulai" required>
                        <input type="time" name="jam_selesai">
                    </div>

                    <input type="number" name="biaya" min="100" placeholder="Biaya">

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

        function toggleMenu() {
            document.getElementById("sidebar").classList.toggle("hide");
            document.getElementById("main").classList.toggle("full");

        }


        function toggleDetail(el) {
            const fullText = el.getAttribute("data-full");

            // cek kalau sudah expanded
            if (el.classList.contains("open")) {
                el.innerText = fullText.substring(0, 30) + "...";
                el.classList.remove("open");
            } else {
                el.innerText = fullText;
                el.classList.add("open");
            }
        }

    </script>

</body>

</html>