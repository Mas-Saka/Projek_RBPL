<?php
session_start();
include "config.php";

if (!isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$id = (int) $_GET['id'];
$role = $_SESSION['role'];
$nama_user = htmlspecialchars($_SESSION['nama'] ?? 'Pengguna');

$data = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT seminar.*, users.nama as nama_narasumber 
    FROM seminar 
    LEFT JOIN users ON seminar.narasumber_id = users.id 
    WHERE seminar.seminar_id = $id
"));

if (!$data) {
    echo "Seminar tidak ditemukan.";
    exit;
}

// LOGIKA KEMBALI DINAMIS BERDASARKAN ROLE
$link_kembali = "index.php"; // Fallback default
switch ($role) {
    case 'admin':
        $link_kembali = "dashboardadmin.php";
        break;
    case 'eo':
        $link_kembali = "seminar.php"; // Halaman kelola seminar EO
        break;
    case 'narasumber':
        $link_kembali = "undangan_seminar.php"; // Halaman undangan narasumber
        break;
    case 'klien':
        $link_kembali = "dashboardklien.php";
        break;
    case 'peserta':
        $link_kembali = "dashboardpeserta.php";
        break;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Seminar</title>
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
            color: #333;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }

        .sidebar-brand h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
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
            transition: all 0.2s;
        }

        .sidebar-nav a:hover {
            background: rgba(52, 152, 219, 0.12);
            color: #3498db;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            font-size: 11px;
            color: #5a6a78;
        }

        /* --- TOPBAR --- */
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
            transition: left 0.3s;
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

        /* --- MAIN CONTENT --- */
        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        .content {
            padding: 30px 28px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 14px;
            padding: 26px 30px;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .wb-text h2 {
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .wb-text p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.72);
        }

        .section-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            padding: 28px;
        }

        .image-wrapper {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .image-wrapper img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            cursor: pointer;
            transition: 0.3s;
        }

        .image-wrapper img:hover {
            filter: brightness(80%);
        }

        .detail-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a2634;
            margin-bottom: 12px;
        }

        .badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            text-transform: uppercase;
        }

        .badge-aktif {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-draft {
            background: #fff3cd;
            color: #856404;
        }

        .badge-selesai {
            background: #eef2f7;
            color: #5a6a78;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 24px;
        }

        .box {
            background: #f8fafc;
            border: 1px solid #eef2f7;
            padding: 18px 20px;
            border-radius: 10px;
        }

        .label {
            font-size: 11px;
            color: #95a5a6;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .value {
            font-size: 14.5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .desc-box {
            margin-top: 24px;
            line-height: 1.7;
            color: #444;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            padding: 24px;
            border-radius: 10px;
            font-size: 14px;
        }

        .btn-kembali {
            display: inline-block;
            padding: 10px 24px;
            background: #3498db;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            margin-top: 26px;
            transition: 0.2s;
        }

        .btn-kembali:hover {
            background: #2980b9;
        }

        /* MODAL GAMBAR */
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
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            max-width: 80%;
            max-height: 80vh;
            border-radius: 12px;
            animation: zoomIn 0.3s ease;
        }

        .close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
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

            .burger-btn {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .image-wrapper img {
                height: 220px;
            }

            .content {
                padding: 20px 16px;
            }
        }
    </style>
</head>

<body>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Portal <?= ucfirst($role) ?></p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Navigasi</span>
            <a href="<?= $link_kembali ?>">Kembali ke Menu</a>
        </div>
        <div class="sidebar-footer">&copy; 2026 Sistem Manajemen Seminar Online</div>
    </nav>

    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="burger-btn" onclick="toggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <span class="topbar-title">Informasi Seminar</span>
        </div>
        <div>
            <span class="user-chip"><?= $nama_user ?></span>
        </div>
    </div>

    <div class="main" id="main">
        <div class="content">

            <div class="welcome-banner">
                <div class="wb-text">
                    <h2>Detail Seminar</h2>
                    <p>Informasi lengkap mengenai seminar yang dipilih.</p>
                </div>
            </div>

            <div class="section-card">

                <?php if ($data['gambar'] != "") { ?>
                    <div class="image-wrapper">
                        <img src="upload/<?= htmlspecialchars($data['gambar']); ?>" onclick="showImage(this.src)"
                            alt="Banner Seminar">
                    </div>
                <?php } ?>

                <div class="detail-title"><?= htmlspecialchars($data['judul_seminar']); ?></div>

                <?php if ($data['status'] == 'aktif') { ?>
                    <span class="badge badge-aktif">Aktif</span>
                <?php } elseif ($data['status'] == 'selesai') { ?>
                    <span class="badge badge-selesai">Selesai</span>
                <?php } else { ?>
                    <span class="badge badge-draft">Draft</span>
                <?php } ?>

                <div class="grid">
                    <div class="box">
                        <div class="label">Kategori</div>
                        <div class="value"><?= htmlspecialchars($data['kategori']); ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Narasumber</div>
                        <div class="value"><?= htmlspecialchars($data['nama_narasumber'] ?? '-'); ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Tanggal</div>
                        <div class="value"><?= $data['tanggal'] ? date('d M Y', strtotime($data['tanggal'])) : '-'; ?>
                        </div>
                    </div>

                    <div class="box">
                        <div class="label">Waktu (WIB)</div>
                        <div class="value"><?= date('H:i', strtotime($data['jam_mulai'])); ?> -
                            <?= date('H:i', strtotime($data['jam_selesai'])); ?>
                        </div>
                    </div>

                    <div class="box">
                        <div class="label">Kuota Peserta</div>
                        <div class="value"><?= $data['kuota']; ?> orang</div>
                    </div>

                    <div class="box">
                        <div class="label">Harga / Biaya</div>
                        <div class="value">Rp <?= number_format($data['biaya'], 0, ',', '.'); ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Platform</div>
                        <div class="value"><?= htmlspecialchars($data['platform']); ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Link Meeting</div>
                        <div class="value" style="word-break: break-all;">
                            <?php if ($data['link_meeting']): ?>
                                <?= htmlspecialchars($data['link_meeting']); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                    <div class="desc-box">
                        <strong style="color:#1a2634; font-size:15px; display:block; margin-bottom:10px;">Deskripsi
                            Seminar</strong>
                        <?= nl2br(htmlspecialchars($data['deskripsi'])); ?>
                    </div>

                    <a href="<?= $link_kembali ?>" class="btn-kembali">← Kembali ke Halaman Anda</a>

                </div>

            </div>
        </div>

        <div id="imageModal" class="modal">
            <span class="close" onclick="closeModal()">&times;</span>
            <img class="modal-content" id="modalImg">
        </div>

        <script>
            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('open');
            }

            function showImage(src) {
                document.getElementById("imageModal").classList.add("show");
                document.getElementById("modalImg").src = src;
            }

            function closeModal() {
                document.getElementById("imageModal").classList.remove("show");
            }

            window.onclick = function (event) {
                let modal = document.getElementById("imageModal");
                if (event.target == modal) {
                    closeModal();
                }
            }
        </script>

</body>

</html>