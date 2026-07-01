<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

$peserta_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'] ?? 'Peserta';
$foto_user = $_SESSION['foto_profil'] ?? null;


// Ambil seminar yang sudah didaftarkan peserta (aktif & selesai)
$seminar_q = mysqli_query(
    $conn,
    "SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.jam_mulai, s.jam_selesai,
            s.status AS status_seminar, s.kategori, s.platform,
            u.nama AS narasumber,
            p.status AS status_daftar
     FROM pendaftaran p
     JOIN seminar s ON p.seminar_id = s.seminar_id
     LEFT JOIN users u ON s.narasumber_id = u.id
     WHERE p.peserta_id = $peserta_id
       AND s.status IN ('aktif', 'selesai')
       AND p.status = 'diterima'
     ORDER BY s.tanggal DESC"
);
$seminar_list = [];
while ($r = mysqli_fetch_assoc($seminar_q)) {
    $seminar_list[] = $r;
}

// Filter seminar
$filter_seminar = isset($_GET['seminar_id']) ? (int) $_GET['seminar_id'] : ($seminar_list[0]['seminar_id'] ?? 0);

// Info seminar yang dipilih
$seminar_info = null;
foreach ($seminar_list as $sl) {
    if ($sl['seminar_id'] == $filter_seminar) {
        $seminar_info = $sl;
        break;
    }
}

// Ambil materi untuk seminar yang dipilih
$materi_list = [];
if ($filter_seminar && $seminar_info) {
    $materi_q = mysqli_query(
        $conn,
        "SELECT m.materi_id, m.judul_materi, m.deskripsi,
                m.file_materi, m.tipe_file, m.ukuran_file, m.upload_at,
                u.nama AS narasumber
         FROM materi m
         JOIN users u ON m.narasumber_id = u.id
         WHERE m.seminar_id = $filter_seminar
         ORDER BY m.upload_at ASC"
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
    <title>Materi Seminar — SeminarOnline</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap"
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
            transition: transform .3s ease;
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
            background : rgba(241, 27, 3, 0.26);
            color: #da0d0d;
            margin-top: 8px;
            text: bold; 
        }

        .sidebar-nav a.logout:hover {
            background: red;
            color: #e3bcb8;
            text: bold;
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

        

        .user-pill {
            display: flex;
            align-items: center;
            gap: 9px;
            background: #f4f7f6;
            border: 1px solid #e8edf2;
            border-radius: 24px;
            padding: 6px 14px 6px 8px;
            font-size: 12.5px;
            font-weights: 500;
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

        /* Page header */
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

        /* Seminar selector tabs */
        .seminar-tabs-wrap {
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            margin-bottom: 24px;
        }

        .seminar-tabs-label {
            font-size: 12px;
            font-weight: 700;
            color: #7f8c9a;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 12px;
        }

        .seminar-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .seminar-tab {
            padding: 8px 16px;
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

        .seminar-tab.aktif {
            border-color: #27ae60;
        }

        .seminar-tab.selesai {
            border-color: #bdc3c7;
        }

        .seminar-tab.active {
            background: #2980b9;
            color: #fff;
            border-color: #2980b9;
        }

        /* Seminar info panel */
        .seminar-info-panel {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-start;
        }

        .sip-main {
            flex: 1;
            min-width: 200px;
        }

        .sip-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a2634;
            margin-bottom: 8px;
        }

        .sip-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .sip-meta-item {
            font-size: 12.5px;
            color: #7f8c9a;
        }

        .sip-meta-item strong {
            color: #1a2634;
            font-weight: 600;
        }

        .sip-right {
            flex-shrink: 0;
        }

        .badge {
            display: inline-block;
            padding: 5px 13px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-aktif {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-selesai {
            background: #eaecee;
            color: #5d6d7e;
        }

        /* Materi grid */
        .materi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .materi-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: box-shadow .2s, transform .2s;
        }

        .materi-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, .1);
            transform: translateY(-2px);
        }

        .materi-card-top {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .materi-ext {
            width: 44px;
            height: 44px;
            border-radius: 9px;
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
            background: #d4efdf;
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

        .materi-card-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1a2634;
            flex: 1;
        }

        .materi-card-desc {
            font-size: 12.5px;
            color: #7f8c9a;
            line-height: 1.6;
            min-height: 18px;
        }

        .materi-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #f0f3f7;
        }

        .materi-card-size {
            font-size: 11.5px;
            color: #b0bec5;
        }

        .materi-card-date {
            font-size: 11px;
            color: #bdc3c7;
        }

        .btn-unduh {
            display: inline-block;
            padding: 8px 18px;
            background: #2980b9;
            color: #fff;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s;
        }

        .btn-unduh:hover {
            background: #2471a3;
        }

        /* Empty & lock state */
        .empty-materi,
        .lock-state {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            text-align: center;
            padding: 54px 30px;
            color: #b0bec5;
        }

        .empty-materi h4,
        .lock-state h4 {
            font-size: 15px;
            font-weight: 600;
            color: #7f8c9a;
            margin-bottom: 8px;
        }

        .empty-materi p,
        .lock-state p {
            font-size: 13px;
        }

        .lock-state {
            border: 2px dashed #dce3ea;
            background: #f8fafc;
        }

        .lock-notice {
            display: inline-block;
            background: #fdebd0;
            color: #a04000;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        /* No seminar */
        .no-seminar {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            text-align: center;
            padding: 60px 30px;
            color: #95a5a6;
        }

        .no-seminar h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #7f8c9a;
        }

        .no-seminar a {
            display: inline-block;
            margin-top: 14px;
            padding: 10px 22px;
            background: #2980b9;
            color: #fff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .no-seminar a:hover {
            background: #2471a3;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            z-index: 80;
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

            .materi-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Portal Peserta</p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Menu</span>
            <a href="dashboardpeserta.php">Dashboard</a>
            <a href="jelajahi_seminar.php">Jelajahi Seminar</a>
            <a href="materi_seminar.php" class="active">Materi Seminar</a>
            <span class="nav-label" style="margin-top:8px"></span>
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
            <span class="topbar-title">Materi Seminar</span>
        </div>
        
        <div class="user-pill">
            <div class="user-avatar">
                <?php if ($foto_user): ?>
                    <img src="upload/foto_profil/<?= htmlspecialchars($foto_user) ?>" alt="<?= htmlspecialchars($nama_user) ?>">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($nama_user, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <?= htmlspecialchars($nama_user) ?>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main" id="main">
        <div class="content">

            <div class="page-header">
                <h2>Materi Seminar Saya</h2>
                <p>Unduh dan pelajari bahan-bahan seminar yang tersedia dari narasumber.</p>
            </div>

            <?php if (empty($seminar_list)): ?>
                <div class="no-seminar">
                    <h4>Belum ada seminar yang kamu ikuti</h4>
                    <p>Daftar ke seminar terlebih dahulu untuk dapat mengakses materi.</p>
                    <a href="jelajahi_seminar.php">Jelajahi Seminar</a>
                </div>
            <?php else: ?>

                <!-- PILIH SEMINAR -->
                <div class="seminar-tabs-wrap">
                    <div class="seminar-tabs-label">Pilih Seminar</div>
                    <div class="seminar-tabs">
                        <?php foreach ($seminar_list as $sl):
                            $tab_status = strtolower($sl['status_seminar']);
                            ?>
                            <a href="materi_seminar.php?seminar_id=<?= $sl['seminar_id'] ?>"
                                class="seminar-tab <?= $tab_status ?> <?= ($sl['seminar_id'] == $filter_seminar) ? 'active' : '' ?>">
                                <?= htmlspecialchars(mb_strimwidth($sl['judul_seminar'], 0, 30, '...')) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($seminar_info): ?>
                    <!-- INFO SEMINAR TERPILIH -->
                    <div class="seminar-info-panel">
                        <div class="sip-main">
                            <div class="sip-title"><?= htmlspecialchars($seminar_info['judul_seminar']) ?></div>
                            <div class="sip-meta">
                                <div class="sip-meta-item">
                                    Tanggal: <strong>
                                        <?= $seminar_info['tanggal'] ? date('d M Y', strtotime($seminar_info['tanggal'])) : 'TBD' ?>
                                    </strong>
                                </div>
                                <div class="sip-meta-item">
                                    Waktu: <strong>
                                        <?= date('H:i', strtotime($seminar_info['jam_mulai'])) ?> –
                                        <?= date('H:i', strtotime($seminar_info['jam_selesai'])) ?> WIB
                                    </strong>
                                </div>
                                <div class="sip-meta-item">
                                    Narasumber: <strong><?= htmlspecialchars($seminar_info['narasumber'] ?? '-') ?></strong>
                                </div>
                                <div class="sip-meta-item">
                                    Platform: <strong><?= htmlspecialchars($seminar_info['platform'] ?? '-') ?></strong>
                                </div>
                                <?php if ($seminar_info['kategori']): ?>
                                    <div class="sip-meta-item">
                                        Kategori: <strong><?= htmlspecialchars($seminar_info['kategori']) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="sip-right">
                            <?php
                            $st = strtolower($seminar_info['status_seminar']);
                            $badge_c = $st === 'aktif' ? 'badge-aktif' : 'badge-selesai';
                            $badge_t = $st === 'aktif' ? 'Aktif' : 'Selesai';
                            ?>
                            <span class="badge <?= $badge_c ?>"><?= $badge_t ?></span>
                        </div>
                    </div>

                    <!-- MATERI SECTION -->
                    <?php if ($seminar_info['status_seminar'] === 'aktif'): ?>
                        <!-- Seminar masih aktif: materi belum bisa diakses -->
                        <div class="lock-state">
                            <div class="lock-notice">Seminar masih berlangsung</div>
                            <h4>Materi belum tersedia untuk diunduh</h4>
                            <p>Materi seminar akan dapat diakses setelah narasumber menandai seminar sebagai
                                <strong>Selesai</strong>.
                            </p>
                        </div>
                    <?php elseif (empty($materi_list)): ?>
                        <div class="empty-materi">
                            <h4>Belum ada materi yang diunggah</h4>
                            <p>Narasumber belum mengunggah materi untuk seminar ini. Silakan cek kembali nanti.</p>
                        </div>
                    <?php else: ?>
                        <div class="materi-grid">
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
                                $tgl_up = date('d M Y', strtotime($m['upload_at']));
                                ?>
                                <div class="materi-card">
                                    <div class="materi-card-top">
                                        <div class="materi-ext <?= $ext_class ?>"><?= strtoupper($ext) ?></div>
                                        <div class="materi-card-title"><?= htmlspecialchars($m['judul_materi']) ?></div>
                                    </div>
                                    <?php if ($m['deskripsi']): ?>
                                        <div class="materi-card-desc"><?= htmlspecialchars($m['deskripsi']) ?></div>
                                    <?php else: ?>
                                        <div class="materi-card-desc" style="color:#dce3ea">—</div>
                                    <?php endif; ?>
                                    <div class="materi-card-footer">
                                        <div>
                                            <div class="materi-card-size"><?= formatBytes($m['ukuran_file']) ?></div>
                                            <div class="materi-card-date">Diunggah <?= $tgl_up ?></div>
                                        </div>
                                        <a href="uploads/materi/<?= urlencode($m['file_materi']) ?>"
                                            download="<?= htmlspecialchars($m['file_materi']) ?>" class="btn-unduh">Unduh</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php endif; // end seminar_info ?>

            <?php endif; // end seminar_list ?>

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
    </script>
</body>

</html>