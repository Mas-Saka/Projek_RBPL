<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];
$nama_eo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM users WHERE id=$eo_id"))['nama'];
$foto_user = $_SESSION['foto_profil'] ?? null;

// Hapus seminar
if (isset($_GET['hapus'])) {
    $hapus_id = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM seminar WHERE seminar_id=$hapus_id AND eo_id=$eo_id");
    echo "<script>alert('Seminar berhasil dihapus.'); window.location='seminar.php';</script>";
    exit;
}

// Ambil seminar milik EO ini
$seminar = mysqli_query($conn, "
    SELECT s.*, u.nama AS nama_narasumber, k.judul_kontrak
    FROM seminar s
    LEFT JOIN users u ON s.narasumber_id = u.id
    LEFT JOIN kontrak k ON s.kontrak_id = k.kontrak_id
    WHERE s.eo_id = $eo_id
    ORDER BY s.seminar_id DESC
");

// Ambil narasumber
$narasumber = mysqli_query($conn, "SELECT id, nama FROM users WHERE role='narasumber'");

// Siapkan array kontrak untuk JS search
$kontrak_arr = [];
$kontrak_list_temp = mysqli_query($conn, "
    SELECT kontrak_id, judul_kontrak, nomor_kontrak, judul_seminar
    FROM kontrak
    WHERE eo_id = $eo_id AND status_kontrak = 'disetujui'
    ORDER BY kontrak_id DESC
");
while ($k = mysqli_fetch_assoc($kontrak_list_temp)) {
    $kontrak_arr[] = $k;
}

// Tambah seminar
if (isset($_POST['submit'])) {
    $judul_seminar = mysqli_real_escape_string($conn, $_POST['judul_seminar']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $biaya = (int) $_POST['biaya'];
    $kuota = (int) $_POST['kuota'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $platform = mysqli_real_escape_string($conn, $_POST['platform']);
    $link_meeting = mysqli_real_escape_string($conn, $_POST['link_meeting']);
    $narasumber_id = (int) $_POST['narasumber_id'];
    $kontrak_id = !empty($_POST['kontrak_id']) ? (int) $_POST['kontrak_id'] : 'NULL';
    $status = "draft";
    $undangan_status = "menunggu";

    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    $folder = "upload/";

    if ($gambar != "") {
        move_uploaded_file($tmp, $folder . $gambar);
    }

    $kontrak_val = ($kontrak_id === 'NULL') ? 'NULL' : "'$kontrak_id'";

    mysqli_query($conn, "INSERT INTO seminar 
        (judul_seminar, deskripsi, kategori, gambar, biaya, kuota, tanggal, jam_mulai, jam_selesai, platform, link_meeting, status, undangan_status, eo_id, narasumber_id, kontrak_id)
        VALUES ('$judul_seminar','$deskripsi','$kategori','$gambar','$biaya','$kuota','$tanggal','$jam_mulai','$jam_selesai','$platform','$link_meeting','$status','$undangan_status','$eo_id','$narasumber_id',$kontrak_val)");

    echo "<script>alert('Seminar berhasil dibuat! Menunggu konfirmasi narasumber.'); window.location='seminar.php';</script>";
    exit;
}

$cek_tolak = mysqli_query($conn, "SELECT COUNT(*) as n FROM seminar WHERE eo_id=$eo_id AND undangan_status='ditolak'");
$jml_tolak = mysqli_fetch_assoc($cek_tolak)['n'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Seminar — SeminarOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7f6;
            color: #1a2634;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #2c3e50;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-brand h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .sidebar-brand p {
            color: #7f8c9a;
            font-size: 11px;
            margin-top: 3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
        }

        .nav-label {
            color: #5a6a78;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 12px;
            margin: 12px 0 6px;
            display: block;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #9baab7;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 2px;
            transition: all .2s;
        }

        .sidebar-nav a:hover {
            background: rgba(52, 152, 219, .12);
            color: #3498db;
        }

        .sidebar-nav a.active {
            background: rgba(52, 152, 219, .18);
            color: #3498db;
            font-weight: 600;
        }

        .sidebar-nav a.logout {
            background: rgba(241, 27, 3, 0.26);
            color: #da0d0d;
            margin-top: 8px;
            font-weight: bold;
        }

        .sidebar-nav a.logout:hover {
            background: red;
            color: #e3bcb8;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, .06);
            font-size: 11px;
            color: #5a6a78;
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 90;
            backdrop-filter: blur(1px);
        }

        .overlay.show {
            display: block;
        }

        /* Topbar */
        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 240px;
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 80;
            transition: left .3s;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .burger {
            background: none;
            border: none;
            cursor: pointer;
            display: none;
            flex-direction: column;
            gap: 5px;
            padding: 4px;
        }

        .burger span {
            display: block;
            width: 20px;
            height: 2px;
            background: #64748b;
            border-radius: 2px;
            transition: .2s;
        }

        .topbar-title {
            font-size: 14.5px;
            font-weight: 600;
            color: #1a2634;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 9px;
            background: #f4f7f6;
            border: 1px solid #e8edf2;
            border-radius: 24px;
            padding: 6px 14px 6px 8px;
            font-size: 12.5px;
            font-weight: 500;
            color: #2c3e50;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Main Content */
        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left .3s;
        }

        .content {
            padding: 28px 26px 48px;
        }

        /* Card & Table */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border: 1px solid #e8edf2;
            overflow: hidden;
            margin-bottom: 26px;
        }

        .card-head {
            padding: 18px 22px 14px;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-head h3 {
            font-size: 14.5px;
            font-weight: 700;
            color: #1a2634;
        }

        .card-head p {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 2px;
        }

        .tbl-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #f8fafc;
        }

        th {
            padding: 11px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #7f8c8d;
            letter-spacing: .5px;
            text-transform: uppercase;
            border-bottom: 1px solid #e8edf2;
            white-space: nowrap;
        }

        td {
            padding: 13px 16px;
            font-size: 13px;
            color: #2c3e50;
            border-bottom: 1px solid #f4f7f6;
        }

        tbody tr:hover {
            background: #fafcfe;
        }

        .td-title {
            font-weight: 600;
            color: #1a2634;
            cursor: pointer;
            transition: color .2s;
        }

        .td-title:hover {
            color: #3498db;
        }

        .td-sub {
            font-size: 11.5px;
            color: #95a5a6;
            margin-top: 2px;
        }

        /* Badges */
        .tbl-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-aktif {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-draft {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-selesai {
            background: #e8edf2;
            color: #2c3e50;
        }

        .badge-u-menunggu {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-u-diterima {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-u-ditolak {
            background: #fde8e8;
            color: #c0392b;
        }

        /* Buttons */
        .btn-daftar {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background .18s;
        }

        .btn-daftar:hover {
            background: #2980b9;
        }

        .btn-detail-sm {
            background: #f4f7f6;
            color: #2c3e50;
            border: 1px solid #e8edf2;
            border-radius: 7px;
            padding: 6px 12px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
        }

        .btn-detail-sm:hover {
            background: #e8edf2;
        }

        .btn-edit-sm {
            background: #27ae60;
            color: #fff;
            border: 1px solid #27ae60;
            border-radius: 7px;
            padding: 6px 12px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
        }

        .btn-edit-sm:hover {
            background: #219653;
        }

        .btn-hapus-sm {
            background: #fde8e8;
            color: #c0392b;
            border: 1px solid #f8b4b4;
            border-radius: 7px;
            padding: 6px 12px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
        }

        .btn-hapus-sm:hover {
            background: #f8b4b4;
        }

        /* Alerts & Panels */
        .notif-tolak {
            background: #fde8e8;
            border: 1px solid #f8b4b4;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 22px;
            font-size: 13px;
            color: #c0392b;
        }

        .notif-tolak strong {
            font-weight: 700;
        }

        .alasan-panel {
            background: #fffcfc;
            border: 1px dashed #f8b4b4;
            border-radius: 8px;
            padding: 14px;
            margin-top: 8px;
        }

        .alasan-panel .label-tolak {
            font-size: 11.5px;
            font-weight: 700;
            color: #c0392b;
            margin-bottom: 6px;
        }

        .alasan-panel p {
            font-size: 13px;
            color: #444;
            margin-bottom: 12px;
        }

        .alasan-actions {
            display: flex;
            gap: 8px;
        }

        /* Form Styles */
        .form-content {
            padding: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #1a2634;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #dce3eb;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #333;
            background: #fff;
            transition: border-color .2s;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #3498db;
        }

        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #dce3eb;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #333;
            background: #fff;
            resize: vertical;
            min-height: 80px;
            box-sizing: border-box;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        img.preview {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }

        /* Custom Dropdown Search */
        .kontrak-search-wrap {
            position: relative;
            width: 100%;
        }

        .kontrak-search-input {
            width: 100%;
            padding: 10px 36px 10px 14px;
            border: 1px solid #dce3eb;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            cursor: pointer;
            background: #fff;
            box-sizing: border-box;
        }

        .kontrak-search-input:focus {
            outline: none;
            border-color: #3498db;
        }

        .kontrak-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e8edf2;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            z-index: 500;
            max-height: 260px;
            overflow-y: auto;
            display: none;
        }

        .kontrak-dropdown.show {
            display: block;
        }

        .kontrak-option {
            padding: 12px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f4f7f6;
            transition: background .15s;
        }

        .kontrak-option:last-child {
            border-bottom: none;
        }

        .kontrak-option:hover {
            background: #f8fafc;
        }

        .kontrak-option .opt-judul {
            font-size: 13px;
            font-weight: 600;
            color: #1a2634;
        }

        .kontrak-option .opt-meta {
            font-size: 11.5px;
            color: #95a5a6;
            margin-top: 2px;
        }

        .kontrak-option.no-result {
            color: #95a5a6;
            cursor: default;
            font-style: italic;
        }

        .kontrak-option.no-result:hover {
            background: #fff;
        }

        .selected-tag-wrap {
            margin-top: 8px;
            display: none;
        }

        .kontrak-label-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f0f7ff;
            border: 1px solid #c3ddf7;
            color: #2a5298;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .remove-kontrak {
            cursor: pointer;
            color: #95a5a6;
            font-size: 14px;
            line-height: 1;
            transition: color .15s;
        }

        .remove-kontrak:hover {
            color: #e74c3c;
        }

        @media (max-width:960px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .topbar {
                left: 0;
            }

            .main {
                margin-left: 0;
            }

            .burger {
                display: flex;
            }
        }

        @media (max-width:720px) {
            .content {
                padding: 18px 14px 40px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Portal Event Organizer</p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Menu Utama</span>
            <a href="dashboardeo.php">Dashboard</a>
            <a href="seminar.php" class="active">Kelola Seminar</a>
            <a href="data_peserta.php">Data Peserta</a>
            <a href="feedback_eo.php">Data Feedback</a>
            <a href="buat_laporan.php">Laporan</a>

            <span class="nav-label" style="margin-top:18px">Sistem</span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        <div class="sidebar-footer">&copy; 2026 Sistem Manajemen Seminar</div>
    </nav>

    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="burger" onclick="toggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <span class="topbar-title">Kelola Seminar</span>
        </div>
        <div class="user-pill">
            <div class="user-avatar">
                <?php if ($foto_user): ?>
                    <img src="upload/foto_profil/<?= htmlspecialchars($foto_user) ?>">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($nama_eo, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <?= htmlspecialchars($nama_eo) ?>
        </div>
    </div>

    <div class="main" id="main">
        <div class="content">

            <?php if ($jml_tolak > 0): ?>
                        <div class="notif-tolak">
                            <strong>Perhatian:</strong> Ada <?= $jml_tolak ?> seminar yang undangannya ditolak oleh narasumber. Lihat detail di tabel dan lakukan pengeditan atau hapus seminar tersebut.
                        </div>
            <?php endif; ?>

            <div class="table-card">
                <div class="card-head">
                    <div>
                        <h3>Daftar Seminar</h3>
                        <p>Kelola seluruh seminar yang telah Anda daftarkan</p>
                    </div>
                    <a href="#formTambah" class="btn-daftar">+ Tambah Seminar</a>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th style="width:180px;">Judul Seminar</th>
                                <th>Narasumber</th>
                                <th>Kontrak</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th>Undangan</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($s = mysqli_fetch_assoc($seminar)):
                                $status_class = 'badge-draft';
                                if ($s['status'] == 'aktif')
                                    $status_class = 'badge-aktif';
                                elseif ($s['status'] == 'selesai')
                                    $status_class = 'badge-selesai';

                                $us = $s['undangan_status'] ?? 'menunggu';
                                $us_class = 'badge-u-menunggu';
                                if ($us == 'diterima')
                                    $us_class = 'badge-u-diterima';
                                elseif ($us == 'ditolak')
                                    $us_class = 'badge-u-ditolak';
                                ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <div class="td-title" onclick="toggleDetail(this)" data-full="<?= htmlspecialchars($s['judul_seminar']) ?>" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">
                                                    <?= htmlspecialchars($s['judul_seminar']) ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($s['nama_narasumber'] ?? '-') ?></td>
                                            <td>
                                                <?php if (!empty($s['judul_kontrak'])): ?>
                                                            <div style="font-size:12px; color:#2c3e50;"><?= htmlspecialchars($s['judul_kontrak']) ?></div>
                                                <?php else: ?>
                                                            <div style="font-size:12px; color:#95a5a6; font-style:italic;">Tanpa kontrak</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><?= $s['tanggal'] ? date('d/m/Y', strtotime($s['tanggal'])) : '-' ?></div>
                                                <div class="td-sub"><?= $s['jam_mulai'] ?> - <?= $s['jam_selesai'] ?></div>
                                            </td>
                                            <td><span class="tbl-badge <?= $status_class ?>"><?= ucfirst($s['status']) ?></span></td>
                                            <td><span class="tbl-badge <?= $us_class ?>"><?= ucfirst($us) ?></span></td>
                                            <td style="text-align:center;">
                                                <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                                                    <a href="detail_seminar.php?id=<?= $s['seminar_id'] ?>" class="btn-detail-sm">Detail</a>
                                                    <a href="edit_seminar.php?id=<?= $s['seminar_id'] ?>" class="btn-edit-sm">Edit</a>
                                                    <?php if ($us === 'ditolak'): ?>
                                                                <a href="seminar.php?hapus=<?= $s['seminar_id'] ?>" onclick="return confirm('Hapus seminar ini?')" class="btn-hapus-sm">Hapus</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <?php if ($us === 'ditolak' && $s['alasan_tolak']): ?>
                                                    <tr>
                                                        <td colspan="8" style="background:#fffcfc; border-top:none; padding:10px 16px;">
                                                            <div class="alasan-panel">
                                                                <div class="label-tolak">Alasan Penolakan Narasumber</div>
                                                                <p><?= htmlspecialchars($s['alasan_tolak']) ?></p>
                                                                <div class="alasan-actions">
                                                                    <a href="edit_seminar.php?id=<?= $s['seminar_id'] ?>" class="btn-edit-sm">Edit &amp; Ajukan Ulang</a>
                                                                    <a href="seminar.php?hapus=<?= $s['seminar_id'] ?>" onclick="return confirm('Yakin ingin menghapus seminar ini?')" class="btn-hapus-sm">Hapus Seminar</a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                        <?php endif; ?>
                            <?php endwhile; ?>
                            
                            <?php if (mysqli_num_rows($seminar) == 0): ?>
                                        <tr><td colspan="8" style="text-align:center; padding:30px; color:#95a5a6;">Belum ada seminar yang didaftarkan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card" id="formTambah">
                <div class="card-head">
                    <div>
                        <h3>Tambah Seminar Baru</h3>
                        <p>Isi formulir di bawah ini untuk membuat seminar</p>
                    </div>
                </div>
                <div class="form-content">
                    <form method="POST" enctype="multipart/form-data">
                        
                        <div class="form-group">
                            <label class="form-label">Judul Seminar</label>
                            <input type="text" name="judul_seminar" class="form-input" placeholder="Masukkan judul seminar" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-textarea" placeholder="Deskripsi singkat seminar..."></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Kategori</label>
                                <input type="text" name="kategori" class="form-input" placeholder="Contoh: Teknologi, Kesehatan, Bisnis">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Gambar Banner</label>
                                <input type="file" name="gambar" class="form-input" onchange="previewImage(event)" accept="image/*" style="padding: 7px;">
                                <img id="preview" class="preview">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pilih Kontrak Kerja Sama (Opsional)</label>
                            <input type="hidden" name="kontrak_id" id="kontrak_id_hidden" value="">
                            
                            <?php if (count($kontrak_arr) === 0): ?>
                                        <div style="padding:12px; background:#fff3cd; border:1px solid #ffeeba; border-radius:8px; font-size:12.5px; color:#856404;">
                                            Belum ada kontrak yang disetujui. Seminar ini akan dibuat tanpa terhubung ke kontrak.
                                        </div>
                            <?php else: ?>
                                        <div class="kontrak-search-wrap">
                                            <input type="text" id="kontrakSearchInput" class="kontrak-search-input" placeholder="Cari judul atau nomor kontrak..." autocomplete="off">
                                            <div class="kontrak-dropdown" id="kontrakDropdown">
                                                <div class="kontrak-option" onclick="selectKontrak('', 'Tanpa Kontrak', '')">
                                                    <div class="opt-judul" style="color:#95a5a6;">— Tanpa Kontrak —</div>
                                                    <div class="opt-meta">Seminar tidak terhubung ke kontrak klien manapun</div>
                                                </div>
                                                <?php foreach ($kontrak_arr as $k): ?>
                                                            <div class="kontrak-option" data-search="<?= strtolower(htmlspecialchars($k['judul_kontrak'] . ' ' . $k['nomor_kontrak'] . ' ' . $k['judul_seminar'])) ?>"
                                                                onclick="selectKontrak('<?= $k['kontrak_id'] ?>', '<?= htmlspecialchars($k['judul_kontrak'], ENT_QUOTES) ?>', 'No. <?= htmlspecialchars($k['nomor_kontrak'], ENT_QUOTES) ?>')">
                                                                <div class="opt-judul"><?= htmlspecialchars($k['judul_kontrak']) ?></div>
                                                                <div class="opt-meta">No. <?= htmlspecialchars($k['nomor_kontrak']) ?> &middot; <?= htmlspecialchars($k['judul_seminar']) ?></div>
                                                            </div>
                                                <?php endforeach; ?>
                                                <div class="kontrak-option no-result" id="noResult" style="display:none;">Tidak ada hasil ditemukan</div>
                                            </div>
                                        </div>
                                        <div class="selected-tag-wrap" id="selectedKontrakTag">
                                            <div class="kontrak-label-tag">
                                                <span id="selectedKontrakLabel"></span>
                                                <span class="remove-kontrak" onclick="clearKontrak()" title="Hapus pilihan">&times;</span>
                                            </div>
                                            <div style="font-size:11.5px; color:#95a5a6; margin-top:4px;" id="selectedKontrakMeta"></div>
                                        </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Tanggal Pelaksanaan</label>
                                <input type="date" name="tanggal" class="form-input" required>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Kuota Peserta</label>
                                <input type="number" name="kuota" class="form-input" min="1" step="1" placeholder="Misal: 100" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="form-input" required>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" name="jam_selesai" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Biaya (Rp)</label>
                                <input type="number" name="biaya" class="form-input" min="0" placeholder="0 = Gratis" required>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Pilih Narasumber</label>
                                <select name="narasumber_id" class="form-input" required>
                                    <option value="">-- Pilih Narasumber --</option>
                                    <?php while ($n = mysqli_fetch_assoc($narasumber)): ?>
                                                <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['nama']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Platform</label>
                                <select name="platform" class="form-input">
                                    <option value="Zoom">Zoom</option>
                                    <option value="Google Meet">Google Meet</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Link Meeting</label>
                                <input type="text" name="link_meeting" class="form-input" placeholder="https://...">
                            </div>
                        </div>

                        <p style="font-size:12.5px; color:#95a5a6; margin: 18px 0;">
                            Setelah disimpan, status seminar akan menjadi <strong>Draft</strong>. Sistem akan mengirimkan undangan ke Narasumber terpilih untuk persetujuan.
                        </p>

                        <button class="btn-daftar" name="submit" type="submit" style="width: 100%; padding: 12px; font-size: 14px;">Simpan & Kirim Undangan</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
        }

        function previewImage(event) {
            const output = document.getElementById('preview');
            if(event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    output.src = e.target.result;
                    output.style.display = 'block';
                }
                reader.readAsDataURL(event.target.files[0]);
            } else {
                output.style.display = 'none';
            }
        }

        function toggleDetail(el) {
            const fullText = el.getAttribute('data-full');
            if (el.style.whiteSpace === 'normal') {
                el.style.whiteSpace = 'nowrap';
                el.innerText = fullText; 
            } else {
                el.style.whiteSpace = 'normal';
                el.innerText = fullText;
            }
        }

        /* Logic Pencarian Kontrak */
        const searchInput = document.getElementById('kontrakSearchInput');
        const dropdown = document.getElementById('kontrakDropdown');
        const hiddenInput = document.getElementById('kontrak_id_hidden');
        const tagWrap = document.getElementById('selectedKontrakTag');
        const tagLabel = document.getElementById('selectedKontrakLabel');
        const tagMeta = document.getElementById('selectedKontrakMeta');
        const noResult = document.getElementById('noResult');

        if (searchInput) {
            searchInput.addEventListener('focus', () => dropdown.classList.add('show'));
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                const opts = dropdown.querySelectorAll('.kontrak-option:not(.no-result)');
                let count = 0;
                opts.forEach(opt => {
                    const searchStr = opt.getAttribute('data-search') || '';
                    if (q === '' || searchStr.includes(q)) {
                        opt.style.display = 'block';
                        count++;
                    } else {
                        opt.style.display = 'none';
                    }
                });
                noResult.style.display = count === 0 ? 'block' : 'none';
                dropdown.classList.add('show');
            });
        }

        function selectKontrak(id, label, meta) {
            hiddenInput.value = id;
            if (id === '') {
                tagWrap.style.display = 'none';
                searchInput.value = '';
            } else {
                tagLabel.textContent = label;
                tagMeta.textContent = meta;
                tagWrap.style.display = 'block';
                searchInput.value = label;
            }
            dropdown.classList.remove('show');
        }

        function clearKontrak() {
            selectKontrak('', 'Tanpa Kontrak', '');
        }

        document.addEventListener('click', function(e) {
            if (dropdown && searchInput && !dropdown.contains(e.target) && !searchInput.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>