<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'narasumber') {
    header("Location: login.php");
    exit;
}

$narasumber_id = $_SESSION['id'];
$flash = null;

// Handle upload materi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'upload') {
    $seminar_id = (int) $_POST['seminar_id'];
    $judul_materi = trim($_POST['judul_materi']);
    $deskripsi_materi = trim($_POST['deskripsi_materi']);

    // Validasi seminar milik narasumber ini
    $cek = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT seminar_id FROM seminar WHERE seminar_id=$seminar_id AND narasumber_id=$narasumber_id"
    ));

    if (!$cek) {
        $flash = ['type' => 'error', 'msg' => 'Seminar tidak ditemukan atau bukan milik Anda.'];
    } elseif (empty($judul_materi)) {
        $flash = ['type' => 'error', 'msg' => 'Judul materi tidak boleh kosong.'];
    } elseif (!isset($_FILES['file_materi']) || $_FILES['file_materi']['error'] !== UPLOAD_ERR_OK) {
        $flash = ['type' => 'error', 'msg' => 'File materi wajib diunggah.'];
    } else {
        $file = $_FILES['file_materi'];
        $allowed_ext = ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'mp4', 'png', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $max_size = 50 * 1024 * 1024; // 50MB

        if (!in_array($ext, $allowed_ext)) {
            $flash = ['type' => 'error', 'msg' => 'Tipe file tidak diizinkan. Gunakan PDF, PPT, PPTX, DOC, DOCX, XLS, XLSX, ZIP, MP4, PNG, JPG.'];
        } elseif ($file['size'] > $max_size) {
            $flash = ['type' => 'error', 'msg' => 'Ukuran file melebihi batas 50MB.'];
        } else {
            $upload_dir = 'uploads/materi/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_materi = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
            $tujuan = $upload_dir . $file_materi;

            if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                $judul_esc = mysqli_real_escape_string($conn, $judul_materi);
                $desk_esc = mysqli_real_escape_string($conn, $deskripsi_materi);
                $nama_esc = mysqli_real_escape_string($conn, $file_materi);
                $ext_esc = mysqli_real_escape_string($conn, $ext);
                $size = $file['size'];

                mysqli_query(
                    $conn,
                    "INSERT INTO materi (seminar_id, narasumber_id, judul_materi, deskripsi, file_materi, tipe_file, ukuran_file)
                     VALUES ($seminar_id, $narasumber_id, '$judul_esc', '$desk_esc', '$nama_esc', '$ext_esc', $size)"
                );
                $flash = ['type' => 'success', 'msg' => 'Materi berhasil diunggah.'];
            } else {
                $flash = ['type' => 'error', 'msg' => 'Gagal menyimpan file. Periksa izin folder.'];
            }
        }
    }
}

// Handle hapus materi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'hapus') {
    $materi_id = (int) $_POST['materi_id'];
    $row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT m.file_materi FROM materi m
         JOIN seminar s ON m.seminar_id = s.seminar_id
         WHERE m.materi_id=$materi_id AND s.narasumber_id=$narasumber_id"
    ));
    if ($row) {
        @unlink('uploads/materi/' . $row['file_materi']);
        mysqli_query($conn, "DELETE FROM materi WHERE materi_id=$materi_id");
        $flash = ['type' => 'success', 'msg' => 'Materi berhasil dihapus.'];
    } else {
        $flash = ['type' => 'error', 'msg' => 'Materi tidak ditemukan.'];
    }
}

// Ambil seminar milik narasumber
$seminar_list_q = mysqli_query(
    $conn,
    "SELECT seminar_id, judul_seminar, tanggal, status FROM seminar
     WHERE narasumber_id=$narasumber_id AND undangan_status='diterima'
     ORDER BY tanggal DESC"
);
$seminar_list = [];
while ($r = mysqli_fetch_assoc($seminar_list_q)) {
    $seminar_list[] = $r;
}

// Filter seminar yang dipilih
$filter_seminar = isset($_GET['seminar_id']) ? (int) $_GET['seminar_id'] : ($seminar_list[0]['seminar_id'] ?? 0);

// Ambil materi untuk seminar yang dipilih
$materi_list = [];
if ($filter_seminar) {
    $materi_q = mysqli_query(
        $conn,
        "SELECT m.*, s.judul_seminar FROM materi m
         JOIN seminar s ON m.seminar_id = s.seminar_id
         WHERE m.seminar_id=$filter_seminar AND m.narasumber_id=$narasumber_id
         ORDER BY m.upload_at DESC"
    );
    while ($r = mysqli_fetch_assoc($materi_q)) {
        $materi_list[] = $r;
    }
}

function formatBytes($bytes)
{
    if ($bytes >= 1048576)
        return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)
        return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Materi — SeminarOnline</title>
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
            transition: transform .3s ease;
        }

        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
        }

        .sidebar-brand h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .sidebar-brand p {
            color: #7f8c9a;
            font-size: 11px;
            margin-top: 3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-label {
            color: #5a6a78;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 12px;
            margin: 12px 0 6px;
            display: block;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            color: #9baab7;
            text-decoration: none;
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
            color: #e74c3c;
            margin-top: 4px;
        }

        .sidebar-nav a.logout:hover {
            background: rgba(231, 76, 60, .1);
            color: #c0392b;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, .06);
            font-size: 11px;
            color: #5a6a78;
        }

        /* Topbar */
        .topbar {
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            height: 60px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            box-shadow: 0 1px 0 #e8edf2;
            z-index: 90;
            transition: left .3s;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .burger-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: none;
            flex-direction: column;
            gap: 5px;
            padding: 4px;
        }

        .burger-btn span {
            display: block;
            width: 22px;
            height: 2px;
            background: #555;
            border-radius: 2px;
        }

        .topbar-title {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
        }

        .user-chip {
            background: #eef2f7;
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 12.5px;
            color: #2c3e50;
            font-weight: 500;
        }

        /* Main */
        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left .3s;
        }

        .content {
            padding: 30px 28px;
        }

        /* Flash */
        .flash {
            padding: 13px 18px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash.success {
            background: #d5f5e3;
            color: #1e8449;
            border-left: 3px solid #27ae60;
        }

        .flash.error {
            background: #fde8e8;
            color: #b03a2e;
            border-left: 3px solid #e74c3c;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 14px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 26px;
        }

        .page-header h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .page-header p {
            font-size: 13px;
            color: rgba(255, 255, 255, .75);
        }

        /* Two column layout */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 24px;
            align-items: start;
        }

        /* Card */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
        }

        .card-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f0f3f7;
        }

        .card-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1a2634;
        }

        .card-header p {
            font-size: 12.5px;
            color: #7f8c9a;
            margin-top: 3px;
        }

        .card-body {
            padding: 22px 24px;
        }

        /* Form */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 7px;
            letter-spacing: .2px;
        }

        .form-group label span {
            color: #e74c3c;
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: #1a2634;
            background: #fafbfc;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, .1);
            background: #fff;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* File drop zone */
        .drop-zone {
            border: 2px dashed #d1dce8;
            border-radius: 10px;
            padding: 30px 20px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            position: relative;
        }

        .drop-zone:hover,
        .drop-zone.drag-over {
            border-color: #3498db;
            background: #eef6fd;
        }

        .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .drop-zone-label {
            font-size: 13px;
            color: #5a7a96;
            pointer-events: none;
        }

        .drop-zone-label strong {
            color: #2980b9;
            font-weight: 600;
        }

        .drop-zone-hint {
            font-size: 11.5px;
            color: #95a5a6;
            margin-top: 6px;
            pointer-events: none;
        }

        .file-chosen {
            margin-top: 10px;
            font-size: 12.5px;
            color: #27ae60;
            font-weight: 600;
            display: none;
        }

        /* Submit btn */
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #2980b9;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
            letter-spacing: .3px;
        }

        .btn-submit:hover {
            background: #2471a3;
        }

        /* Seminar filter tabs */
        .seminar-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .seminar-tab {
            padding: 7px 16px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 500;
            cursor: pointer;
            border: 1.5px solid #dce3ea;
            color: #5a7a96;
            background: #fff;
            transition: all .2s;
            text-decoration: none;
        }

        .seminar-tab:hover {
            border-color: #3498db;
            color: #2980b9;
        }

        .seminar-tab.active {
            background: #2980b9;
            color: #fff;
            border-color: #2980b9;
        }

        /* Materi list */
        .materi-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid #f0f3f7;
        }

        .materi-item:last-child {
            border-bottom: none;
        }

        .materi-ext {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .5px;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .ext-pdf {
            background: #fde8e8;
            color: #c0392b;
        }

        .ext-ppt,
        .ext-pptx {
            background: #fdebd0;
            color: #a04000;
        }

        .ext-doc,
        .ext-docx {
            background: #d5f5e3;
            color: #1e8449;
        }

        .ext-xls,
        .ext-xlsx {
            background: #d5f5e3;
            color: #1a6e3a;
        }

        .ext-zip {
            background: #eaf0fb;
            color: #2874a6;
        }

        .ext-mp4 {
            background: #f4ecf7;
            color: #76448a;
        }

        .ext-img {
            background: #fef9e7;
            color: #9a7d0a;
        }

        .ext-other {
            background: #eaecee;
            color: #5d6d7e;
        }

        .materi-info {
            flex: 1;
            min-width: 0;
        }

        .materi-info-title {
            font-size: 13.5px;
            font-weight: 600;
            color: #1a2634;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .materi-info-meta {
            font-size: 11.5px;
            color: #95a5a6;
            margin-top: 3px;
        }

        .materi-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-dl {
            padding: 6px 13px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background .15s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-dl-primary {
            background: #eaf3fb;
            color: #2980b9;
        }

        .btn-dl-primary:hover {
            background: #d6ecf9;
        }

        .btn-dl-danger {
            background: #fde8e8;
            color: #c0392b;
        }

        .btn-dl-danger:hover {
            background: #fad2d2;
        }

        .empty-materi {
            text-align: center;
            padding: 44px 20px;
            color: #b0bec5;
            font-size: 13px;
        }

        .empty-materi p {
            margin-top: 8px;
            font-size: 12.5px;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            z-index: 80;
        }

        /* Confirm modal */
        .modal-bg {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }

        .modal-bg.show {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 12px;
            padding: 28px 30px;
            max-width: 380px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .18);
        }

        .modal-box h4 {
            font-size: 16px;
            font-weight: 700;
            color: #1a2634;
            margin-bottom: 10px;
        }

        .modal-box p {
            font-size: 13px;
            color: #5a6a78;
            margin-bottom: 22px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-cancel {
            padding: 9px 20px;
            border-radius: 7px;
            border: 1.5px solid #dce3ea;
            background: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            cursor: pointer;
            color: #5a6a78;
        }

        .btn-cancel:hover {
            background: #f4f7f6;
        }

        .btn-confirm-del {
            padding: 9px 20px;
            border-radius: 7px;
            border: none;
            background: #e74c3c;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-confirm-del:hover {
            background: #c0392b;
        }

        @media (max-width: 960px) {
            .two-col {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .overlay.show {
                display: block;
            }

            .topbar {
                left: 0;
            }

            .main {
                margin-left: 0;
            }

            .burger-btn {
                display: flex;
            }
        }

        @media (max-width: 600px) {
            .content {
                padding: 20px 16px;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <!-- Confirm modal hapus -->
    <div class="modal-bg" id="modalHapus">
        <div class="modal-box">
            <h4>Hapus Materi</h4>
            <p>Materi ini akan dihapus secara permanen dan tidak dapat dipulihkan. Yakin ingin melanjutkan?</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Batal</button>
                <form method="POST" id="formHapus" style="display:inline">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="materi_id" id="inputMateriId" value="">
                    <button type="submit" class="btn-confirm-del">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Portal Narasumber</p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Menu</span>
            <a href="dashboardnarasumber.php">Dashboard</a>
            <a href="undangan_seminar.php">Undangan Seminar</a>
            <a href="upload_materi.php" class="active">Upload Materi</a>
            <a href="seminar_selesai.php">Tandai Selesai</a>
            <a href="#">Lihat Feedback</a>
            <span class="nav-label" style="margin-top:16px"></span>
            <a href="logout.php" class="logout">Keluar</a>
        </div>
        <div class="sidebar-footer">&copy; 2026 Sistem Manajemen Seminar Online</div>
    </nav>

    <!-- TOPBAR -->
    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="burger-btn" onclick="toggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <span class="topbar-title">Upload Materi</span>
        </div>
        <div>
            <span class="user-chip"><?= htmlspecialchars($_SESSION['nama'] ?? 'Narasumber') ?></span>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main" id="main">
        <div class="content">

            <?php if ($flash): ?>
                <div class="flash <?= $flash['type'] ?>" id="flashMsg">
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h2>Upload Materi Seminar</h2>
                <p>Unggah bahan presentasi atau materi pendukung yang akan dibagikan kepada peserta seminar Anda.</p>
            </div>

            <div class="two-col">
                <!-- FORM UPLOAD -->
                <div class="card">
                    <div class="card-header">
                        <h3>Form Upload Materi</h3>
                        <p>Pilih seminar dan unggah file materi (maks. 50MB)</p>
                    </div>
                    <div class="card-body">
                        <?php if (empty($seminar_list)): ?>
                            <p style="font-size:13px; color:#95a5a6; text-align:center; padding:20px 0">
                                Anda belum memiliki seminar yang telah diterima.
                            </p>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data" action="upload_materi.php">
                                <input type="hidden" name="aksi" value="upload">

                                <div class="form-group">
                                    <label>Pilih Seminar <span>*</span></label>
                                    <select name="seminar_id" class="form-control" required>
                                        <?php foreach ($seminar_list as $sl): ?>
                                            <option value="<?= $sl['seminar_id'] ?>" <?= ($sl['seminar_id'] == $filter_seminar) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sl['judul_seminar']) ?>
                                                (<?= $sl['tanggal'] ? date('d M Y', strtotime($sl['tanggal'])) : 'Tanggal TBD' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Judul Materi <span>*</span></label>
                                    <input type="text" name="judul_materi" class="form-control"
                                        placeholder="Contoh: Slide Presentasi Sesi 1" required maxlength="200">
                                </div>

                                <div class="form-group">
                                    <label>Deskripsi Singkat</label>
                                    <textarea name="deskripsi_materi" class="form-control"
                                        placeholder="Opsional — jelaskan isi atau tujuan materi ini..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label>File Materi <span>*</span></label>
                                    <div class="drop-zone" id="dropZone">
                                        <input type="file" name="file_materi" id="fileInput"
                                            accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.zip,.mp4,.png,.jpg,.jpeg"
                                            onchange="onFileChange(this)">
                                        <div class="drop-zone-label">
                                            Seret file ke sini atau <strong>klik untuk memilih</strong>
                                        </div>
                                        <div class="drop-zone-hint">
                                            PDF, PPT, PPTX, DOC, DOCX, XLS, XLSX, ZIP, MP4, PNG, JPG &bull; Maks. 50MB
                                        </div>
                                        <div class="file-chosen" id="fileChosen"></div>
                                    </div>
                                </div>

                                <button type="submit" class="btn-submit">Unggah Materi</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- DAFTAR MATERI -->
                <div class="card">
                    <div class="card-header">
                        <h3>Materi yang Sudah Diunggah</h3>
                        <p>Pilih seminar untuk melihat atau mengelola materi</p>
                    </div>
                    <div class="card-body">
                        <!-- Filter seminar -->
                        <?php if (!empty($seminar_list)): ?>
                            <div class="seminar-tabs">
                                <?php foreach ($seminar_list as $sl): ?>
                                    <a href="upload_materi.php?seminar_id=<?= $sl['seminar_id'] ?>"
                                        class="seminar-tab <?= ($sl['seminar_id'] == $filter_seminar) ? 'active' : '' ?>">
                                        <?= htmlspecialchars(mb_strimwidth($sl['judul_seminar'], 0, 28, '...')) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($materi_list)): ?>
                            <div class="empty-materi">
                                Belum ada materi untuk seminar ini.
                                <p>Gunakan form di sebelah kiri untuk mengunggah materi pertama Anda.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($materi_list as $m):
                                $ext = strtolower($m['tipe_file']);
                                $ext_map = [
                                    'pdf' => 'ext-pdf',
                                    'ppt' => 'ext-ppt',
                                    'pptx' => 'ext-pptx',
                                    'doc' => 'ext-doc',
                                    'docx' => 'ext-docx',
                                    'xls' => 'ext-xls',
                                    'xlsx' => 'ext-xlsx',
                                    'zip' => 'ext-zip',
                                    'mp4' => 'ext-mp4',
                                    'png' => 'ext-img',
                                    'jpg' => 'ext-img',
                                    'jpeg' => 'ext-img'
                                ];
                                $ext_class = $ext_map[$ext] ?? 'ext-other';
                                $tgl_up = date('d M Y, H:i', strtotime($m['upload_at']));
                                ?>
                                <div class="materi-item">
                                    <div class="materi-ext <?= $ext_class ?>"><?= strtoupper($ext) ?></div>
                                    <div class="materi-info">
                                        <div class="materi-info-title"><?= htmlspecialchars($m['judul_materi']) ?></div>
                                        <div class="materi-info-meta">
                                            <?= formatBytes($m['ukuran_file']) ?> &bull; Diunggah <?= $tgl_up ?>
                                            <?php if ($m['deskripsi']): ?>
                                                &bull; <?= htmlspecialchars(mb_strimwidth($m['deskripsi'], 0, 50, '...')) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="materi-actions">
                                        <a href="uploads/materi/<?= urlencode($m['file_materi']) ?>"
                                            download="<?= htmlspecialchars($m['file_materi']) ?>"
                                            class="btn-dl btn-dl-primary">Unduh</a>
                                        <button class="btn-dl btn-dl-danger"
                                            onclick="konfirmasiHapus(<?= $m['materi_id'] ?>)">Hapus</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
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

        // File input label
        function onFileChange(input) {
            var label = document.getElementById('fileChosen');
            if (input.files && input.files[0]) {
                label.textContent = input.files[0].name;
                label.style.display = 'block';
            } else {
                label.style.display = 'none';
            }
        }

        // Drag & drop styling
        var dz = document.getElementById('dropZone');
        if (dz) {
            dz.addEventListener('dragover', function (e) { e.preventDefault(); dz.classList.add('drag-over'); });
            dz.addEventListener('dragleave', function () { dz.classList.remove('drag-over'); });
            dz.addEventListener('drop', function (e) { e.preventDefault(); dz.classList.remove('drag-over'); });
        }

        // Confirm hapus modal
        function konfirmasiHapus(id) {
            document.getElementById('inputMateriId').value = id;
            document.getElementById('modalHapus').classList.add('show');
        }
        function closeModal() {
            document.getElementById('modalHapus').classList.remove('show');
        }

        // Auto dismiss flash
        (function () {
            var f = document.getElementById('flashMsg');
            if (f) setTimeout(function () {
                f.style.opacity = '0'; f.style.transition = 'opacity .4s';
                setTimeout(function () { f.remove(); }, 400);
            }, 5000);
        })();
    </script>

</body>

</html>