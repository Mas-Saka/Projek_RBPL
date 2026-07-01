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

// Seminar yang sudah didaftarkan peserta ini
$seminar_saya = mysqli_query($conn, "SELECT 
        s.seminar_id,
        s.judul_seminar,
        s.deskripsi,
        s.kategori,
        s.tanggal,
        s.jam_mulai,
        s.jam_selesai,
        s.platform,
        s.link_meeting,
        s.gambar,
        s.biaya,
        s.kuota,
        s.status,
        u.nama AS narasumber,
        p.status AS status_daftar,
        p.tanggal_daftar
    FROM pendaftaran p
    JOIN seminar s ON p.seminar_id = s.seminar_id
    LEFT JOIN users u ON s.narasumber_id = u.id
    WHERE p.peserta_id = $peserta_id
    ORDER BY s.tanggal DESC
");

$total_daftar = mysqli_num_rows($seminar_saya);

// Hitung seminar aktif yang diikuti
$aktif_count = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM pendaftaran p
     JOIN seminar s ON p.seminar_id = s.seminar_id
     WHERE p.peserta_id = $peserta_id AND s.status = 'aktif'"
))['total'];

// Simpan result ke array supaya bisa dipakai 2x
$seminars = [];
while ($row = mysqli_fetch_assoc($seminar_saya)) {
    $seminars[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seminar Saya</title>
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

        /* ========= SIDEBAR ========= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #2c3e50;
            display: flex;
            flex-direction: column;
            padding: 0;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
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
            transition: all 0.2s;
        }

        .sidebar-nav a:hover {
            background: rgba(52, 152, 219, 0.12);
            color: #3498db;
        }

        .sidebar-nav a.active {
            background: rgba(52, 152, 219, 0.18);
            color: #3498db;
            font-weight: 600;
        }

        .sidebar-nav a.logout {
            background: rgba(241, 27, 3, 0.26);
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
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            font-size: 11px;
            color: #5a6a78;
        }

        /* ========= TOPBAR ========= */
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
            transition: 0.2s;
        }

        .topbar-title {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
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

        /* ─────────────────────────────────────────
   MAIN
───────────────────────────────────────── */
        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left .3s cubic-bezier(.4, 0, .2, 1);
        }

        .content {
            padding: 28px 26px 48px;
        }


        /* ========= MAIN ========= */
        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        .content {
            padding: 30px 28px;
        }

        /* ========= PAGE HEADER ========= */
        .page-header {
            margin-bottom: 28px;
        }

        .page-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1a2634;
        }

        .page-header p {
            font-size: 13.5px;
            color: #7f8c8d;
            margin-top: 4px;
        }

        /* ========= STAT STRIP ========= */
        .stat-strip {
            display: flex;
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-item {
            background: #fff;
            border-radius: 10px;
            padding: 18px 22px;
            flex: 1;
            border-left: 3px solid #3498db;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-item.green {
            border-left-color: #27ae60;
        }

        .stat-item.orange {
            border-left-color: #e67e22;
        }

        .stat-item .num {
            font-size: 26px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1;
        }

        .stat-item .label {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 5px;
        }

        /* ========= FILTER BAR ========= */
        .filter-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #fff;
            border: 1.5px solid #e0e7ef;
            border-radius: 20px;
            padding: 7px 18px;
            font-size: 12.5px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            color: #555;
            font-weight: 500;
            transition: all 0.2s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #3498db;
            border-color: #3498db;
            color: #fff;
        }

        /* ========= GRID ========= */
        .seminar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 20px;
        }

        /* ========= CARD ========= */
        .seminar-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            transition: transform 0.25s, box-shadow 0.25s;
            cursor: pointer;
            border: 1px solid #eef0f4;
        }

        .seminar-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
        }

        .card-thumb {
            height: 155px;
            overflow: hidden;
            background: #dfe6e9;
            position: relative;
        }

        .card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .card-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .badge-aktif {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-selesai {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-draft {
            background: #eaecee;
            color: #555;
        }

        .status-daftar {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(52, 152, 219, 0.9);
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        .card-body {
            padding: 18px 18px 14px;
        }

        .card-kategori {
            font-size: 10.5px;
            font-weight: 600;
            color: #3498db;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .card-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.4;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 14px;
        }

        .meta-row {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            color: #7f8c8d;
        }

        .meta-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #bdc3c7;
            flex-shrink: 0;
        }

        .card-footer {
            border-top: 1px solid #f0f3f6;
            padding-top: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-narasumber {
            font-size: 12px;
            color: #95a5a6;
        }

        .card-narasumber strong {
            display: block;
            color: #2c3e50;
            font-size: 12.5px;
        }

        .btn-detail {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 7px 16px;
            font-size: 12px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-detail:hover {
            background: #2980b9;
        }

        /* ========= EMPTY STATE ========= */
        .empty-state {
            text-align: center;
            padding: 70px 20px;
            color: #95a5a6;
        }

        .empty-state .empty-icon {
            width: 72px;
            height: 72px;
            background: #eef2f7;
            border-radius: 50%;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state .empty-icon svg {
            width: 32px;
            height: 32px;
            stroke: #bdc3c7;
            fill: none;
        }

        .empty-state h3 {
            font-size: 16px;
            color: #5d6d7e;
            margin-bottom: 6px;
        }

        .empty-state p {
            font-size: 13px;
        }

        .btn-jelajahi {
            display: inline-block;
            margin-top: 18px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            transition: background 0.2s;
        }

        .btn-jelajahi:hover {
            background: #2980b9;
        }

        /* ========= MODAL ========= */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 25, 40, 0.55);
            z-index: 200;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(2px);
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.25s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-thumb {
            height: 200px;
            background: #dfe6e9;
            border-radius: 16px 16px 0 0;
            overflow: hidden;
        }

        .modal-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-content {
            padding: 24px;
        }

        .modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.4;
            flex: 1;
        }

        .close-modal {
            background: none;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #95a5a6;
            font-size: 20px;
            flex-shrink: 0;
            transition: background 0.2s;
        }

        .close-modal:hover {
            background: #f4f7f6;
            color: #555;
        }

        .modal-section {
            margin-bottom: 18px;
        }

        .section-label {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #95a5a6;
            margin-bottom: 8px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .detail-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px 14px;
        }

        .detail-item .di-label {
            font-size: 10.5px;
            color: #95a5a6;
            margin-bottom: 3px;
        }

        .detail-item .di-value {
            font-size: 13.5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .deskripsi-text {
            font-size: 13px;
            color: #5d6d7e;
            line-height: 1.7;
            background: #f8fafc;
            border-radius: 8px;
            padding: 14px;
        }

        /* Zoom link button */
        .zoom-box {
            border: 1.5px solid #3498db;
            border-radius: 10px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #eaf4fd;
        }

        .zoom-info .zi-label {
            font-size: 10.5px;
            color: #3498db;
            font-weight: 600;
        }

        .zoom-info .zi-platform {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
            margin-top: 2px;
        }

        .btn-join {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 20px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            display: inline-block;
            transition: background 0.2s;
        }

        .btn-join:hover {
            background: #2980b9;
        }

        .no-link {
            font-size: 13px;
            color: #95a5a6;
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px;
            text-align: center;
        }

        .modal-divider {
            height: 1px;
            background: #eef0f4;
            margin: 18px 0;
        }

        /* ========= SIDEBAR OVERLAY ========= */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 80;
        }

        /* ========= RESPONSIVE ========= */
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
            .stat-strip {
                flex-direction: column;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 20px 16px;
            }

            .seminar-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar overlay (mobile) -->
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
            <a href="jelajahi_seminar.php" class="active">Seminar Saya</a>
            <a href="semua_seminar.php">Jelajahi Seminar</a>

            <span class="nav-label" style="margin-top:18px">Akun</span>
            <a href="profil_peserta.php">Profil Saya</a>
            <a href="logout.php" class="logout">Keluar</a>
        </div>
        <div class="sidebar-footer">
            &copy; 2026 Sistem Manajemen Seminar Online
        </div>
    </nav>

    <!-- TOPBAR -->
    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="burger-btn" onclick="toggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <span class="topbar-title">Seminar Saya</span>
        </div>
        <div class="user-pill">
            <div class="user-avatar">
                <?php if ($foto_user): ?>
                    <img src="upload/foto_profil/<?= htmlspecialchars($foto_user) ?>"
                        alt="<?= htmlspecialchars($nama_user) ?>">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($nama_user, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <?= htmlspecialchars($nama_user) ?>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main" id="main">
        <div class="content">

            <!-- Page Header -->
            <div class="page-header">
                <h1>Seminar Saya</h1>
                <p>Daftar seminar yang telah kamu daftarkan.</p>
            </div>

            <!-- Stats -->
            <div class="stat-strip">
                <div class="stat-item">
                    <div class="num"><?= $total_daftar ?></div>
                    <div class="label">Total Terdaftar</div>
                </div>
                <div class="stat-item green">
                    <div class="num"><?= $aktif_count ?></div>
                    <div class="label">Seminar Aktif</div>
                </div>
                <div class="stat-item orange">
                    <div class="num"><?= $total_daftar - $aktif_count ?></div>
                    <div class="label">Seminar Selesai / Lainnya</div>
                </div>
            </div>



            <!-- Filter -->
            <div class="filter-bar">
                <button class="filter-btn active" onclick="filterCards('semua', this)">Semua</button>
                <button class="filter-btn" onclick="filterCards('aktif', this)">Aktif</button>
                <button class="filter-btn" onclick="filterCards('selesai', this)">Selesai</button>
                <button class="filter-btn" onclick="filterCards('draft', this)">Draft</button>
            </div>

            <?php if (count($seminars) === 0): ?>
                <!-- Empty state -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" stroke-width="1.5">
                            <rect x="3" y="4" width="18" height="16" rx="2" />
                            <path d="M8 2v4M16 2v4M3 10h18" />
                        </svg>
                    </div>
                    <h3>Belum ada seminar</h3>
                    <p>Kamu belum mendaftar ke seminar manapun.</p>
                    <a href="semua_seminar.php" class="btn-jelajahi">Jelajahi Seminar</a>
                </div>
            <?php else: ?>

                <!-- Grid Cards -->
                <div class="seminar-grid" id="seminarGrid">
                    <?php foreach ($seminars as $s): ?>
                        <?php
                        $status = strtolower($s['status']);
                        $badge_class = $status === 'aktif' ? 'badge-aktif' : ($status === 'selesai' ? 'badge-selesai' : 'badge-draft');
                        $badge_text = $status === 'aktif' ? 'Aktif' : ($status === 'selesai' ? 'Selesai' : 'Draft');
                        $gambar = !empty($s['gambar']) ? "upload/" . $s['gambar'] : "https://via.placeholder.com/400x200/dfe6e9/ffffff?text=Seminar";
                        $tgl = $s['tanggal'] ? date('d M Y', strtotime($s['tanggal'])) : '-';
                        $jam = ($s['jam_mulai'] && $s['jam_selesai']) ? date('H:i', strtotime($s['jam_mulai'])) . ' – ' . date('H:i', strtotime($s['jam_selesai'])) : '-';
                        $biaya = $s['biaya'] > 0 ? 'Rp ' . number_format($s['biaya'], 0, ',', '.') : 'Gratis';
                        ?>
                        <div class="seminar-card" data-status="<?= $status ?>"
                            onclick="openModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)">
                            <div class="card-thumb">
                                <img src="<?= $gambar ?>" alt="<?= htmlspecialchars($s['judul_seminar']) ?>"
                                    onerror="this.src='https://via.placeholder.com/400x200/dfe6e9/95a5a6?text=Seminar'">
                                <span class="card-badge <?= $badge_class ?>"><?= $badge_text ?></span>
                                <?php if ($s['status_daftar']): ?>
                                    <span class="status-daftar"><?= htmlspecialchars(ucfirst($s['status_daftar'])) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if ($s['kategori']): ?>
                                    <div class="card-kategori"><?= htmlspecialchars($s['kategori']) ?></div>
                                <?php endif; ?>
                                <div class="card-title"><?= htmlspecialchars($s['judul_seminar']) ?></div>
                                <div class="card-meta">
                                    <div class="meta-row">
                                        <span class="meta-dot"></span>
                                        <span><?= $tgl ?></span>
                                    </div>
                                    <div class="meta-row">
                                        <span class="meta-dot"></span>
                                        <span><?= $jam ?> &middot; <?= htmlspecialchars($s['platform'] ?? 'Online') ?></span>
                                    </div>
                                    <div class="meta-row">
                                        <span class="meta-dot"></span>
                                        <span><?= $biaya ?></span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="card-narasumber">
                                        Narasumber
                                        <strong><?= htmlspecialchars($s['narasumber'] ?? '-') ?></strong>
                                    </div>
                                    <button class="btn-detail"
                                        onclick="event.stopPropagation(); openModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)">Detail</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL DETAIL -->
    <div class="modal-overlay" id="modalOverlay" onclick="handleOverlayClick(event)">
        <div class="modal-box" id="modalBox">
            <div class="modal-thumb">
                <img id="modalImg" src="" alt="">
            </div>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="modalTitle"></h2>
                    <button class="close-modal" onclick="closeModal()">&#x2715;</button>
                </div>

                <div class="modal-section">
                    <div class="section-label">Detail Seminar</div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="di-label">Tanggal</div>
                            <div class="di-value" id="mdTanggal"></div>
                        </div>
                        <div class="detail-item">
                            <div class="di-label">Waktu</div>
                            <div class="di-value" id="mdWaktu"></div>
                        </div>
                        <div class="detail-item">
                            <div class="di-label">Platform</div>
                            <div class="di-value" id="mdPlatform"></div>
                        </div>
                        <div class="detail-item">
                            <div class="di-label">Biaya</div>
                            <div class="di-value" id="mdBiaya"></div>
                        </div>
                        <div class="detail-item">
                            <div class="di-label">Narasumber</div>
                            <div class="di-value" id="mdNarasumber"></div>
                        </div>
                        <div class="detail-item">
                            <div class="di-label">Kuota</div>
                            <div class="di-value" id="mdKuota"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-divider"></div>

                <div class="modal-section" id="modalLinkSection">
                    <div class="section-label">Link Bergabung</div>
                    <div class="zoom-box">
                        <div class="zoom-info">
                            <div class="zi-label">Tautan Meeting</div>
                            <div class="zi-platform" id="mdPlatformLabel"></div>
                        </div>
                        <a id="mdZoomLink" href="#" target="_blank" class="btn-join">Bergabung</a>
                    </div>
                </div>

                <div class="modal-section" id="modalNoLinkSection" style="display:none">
                    <div class="section-label">Link Bergabung</div>
                    <div class="no-link">Link meeting belum tersedia. Periksa kembali menjelang hari seminar.</div>
                </div>

                <div class="modal-divider"></div>

                <div class="modal-section" id="modalDeskSection">
                    <div class="section-label">Deskripsi</div>
                    <div class="deskripsi-text" id="mdDeskripsi"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== SIDEBAR TOGGLE =====
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('show');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
        }

        // ===== FILTER CARDS =====
        function filterCards(status, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('.seminar-card').forEach(card => {
                const cardStatus = card.dataset.status;
                card.style.display = (status === 'semua' || cardStatus === status) ? '' : 'none';
            });
        }

        // ===== FORMAT HELPERS =====
        function fmtDate(tgl) {
            if (!tgl) return '-';
            const d = new Date(tgl);
            if (isNaN(d)) return tgl;
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        function fmtTime(t) {
            if (!t) return '-';
            return t.substring(0, 5);
        }

        function fmtBiaya(b) {
            if (!b || b == 0) return 'Gratis';
            return 'Rp ' + parseInt(b).toLocaleString('id-ID');
        }

        // ===== MODAL =====
        function openModal(data) {
            const gambar = data.gambar ? 'upload/' + data.gambar : 'https://via.placeholder.com/600x200/dfe6e9/95a5a6?text=Seminar';

            document.getElementById('modalImg').src = gambar;
            document.getElementById('modalImg').onerror = function () { this.src = 'https://via.placeholder.com/600x200/dfe6e9/95a5a6?text=Seminar'; };
            document.getElementById('modalTitle').textContent = data.judul_seminar;
            document.getElementById('mdTanggal').textContent = fmtDate(data.tanggal);
            document.getElementById('mdWaktu').textContent = fmtTime(data.jam_mulai) + ' – ' + fmtTime(data.jam_selesai) + ' WIB';
            document.getElementById('mdPlatform').textContent = data.platform || 'Online';
            document.getElementById('mdBiaya').textContent = fmtBiaya(data.biaya);
            document.getElementById('mdNarasumber').textContent = data.narasumber || '-';
            document.getElementById('mdKuota').textContent = (data.kuota || '-') + ' orang';
            document.getElementById('mdDeskripsi').textContent = data.deskripsi || 'Tidak ada deskripsi.';
            document.getElementById('mdPlatformLabel').textContent = data.platform || 'Zoom';

            if (data.link_meeting && data.link_meeting.trim() !== '') {
                document.getElementById('mdZoomLink').href = data.link_meeting;
                document.getElementById('modalLinkSection').style.display = '';
                document.getElementById('modalNoLinkSection').style.display = 'none';
            } else {
                document.getElementById('modalLinkSection').style.display = 'none';
                document.getElementById('modalNoLinkSection').style.display = '';
            }

            document.getElementById('modalOverlay').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('show');
            document.body.style.overflow = '';
        }

        function handleOverlayClick(e) {
            if (e.target === document.getElementById('modalOverlay')) {
                closeModal();
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>

</body>

</html>