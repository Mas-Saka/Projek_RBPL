<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'narasumber') {
    header("Location: login.php");
    exit;
}

$narasumber_id = $_SESSION['id'];
$flash = null;

// Handle update status selesai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'selesai') {
    $seminar_id = (int) $_POST['seminar_id'];

    // Validasi seminar milik narasumber dan statusnya aktif
    $cek = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT seminar_id, judul_seminar FROM seminar
         WHERE seminar_id=$seminar_id AND narasumber_id=$narasumber_id AND status='aktif'"
    ));

    if (!$cek) {
        $flash = ['type' => 'error', 'msg' => 'Seminar tidak ditemukan atau sudah berstatus selesai.'];
    } else {
        mysqli_query(
            $conn,
            "UPDATE seminar SET status='selesai' WHERE seminar_id=$seminar_id"
        );
        $flash = [
            'type' => 'success',
            'msg' => 'Seminar "' . htmlspecialchars($cek['judul_seminar']) . '" berhasil ditandai sebagai selesai.'
        ];
    }
}

// Ambil seminar aktif milik narasumber
$seminar_aktif_q = mysqli_query(
    $conn,
    "SELECT seminar_id, judul_seminar, tanggal, jam_mulai, jam_selesai,
            platform, kategori, kuota, link_meeting
     FROM seminar
     WHERE narasumber_id=$narasumber_id AND status='aktif' AND undangan_status='diterima'
     ORDER BY tanggal ASC"
);
$seminar_aktif = [];
while ($r = mysqli_fetch_assoc($seminar_aktif_q)) {
    $seminar_aktif[] = $r;
}

// Ambil seminar yang sudah selesai
$seminar_selesai_q = mysqli_query(
    $conn,
    "SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.jam_mulai, s.jam_selesai,
            s.kategori,
            COUNT(m.materi_id) AS jml_materi
     FROM seminar s
     LEFT JOIN materi m ON m.seminar_id = s.seminar_id
     WHERE s.narasumber_id=$narasumber_id AND s.status='selesai'
     GROUP BY s.seminar_id
     ORDER BY s.tanggal DESC
     LIMIT 10"
);
$seminar_selesai = [];
while ($r = mysqli_fetch_assoc($seminar_selesai_q)) {
    $seminar_selesai[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tandai Seminar Selesai — SeminarOnline</title>
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

        /* Section heading */
        .section-heading {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid #eef0f4;
        }

        /* Card */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            margin-bottom: 24px;
        }

        .card-body {
            padding: 24px;
        }

        /* Seminar row */
        .seminar-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #f0f3f7;
        }

        .seminar-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .seminar-row-info {
            flex: 1;
            min-width: 0;
        }

        .seminar-row-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a2634;
        }

        .seminar-row-meta {
            font-size: 12px;
            color: #7f8c9a;
            margin-top: 4px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .seminar-row-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge {
            display: inline-block;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .badge-aktif {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-selesai {
            background: #eaecee;
            color: #5d6d7e;
        }

        /* Btn selesai */
        .btn-selesai {
            padding: 8px 18px;
            border-radius: 7px;
            background: #2980b9;
            color: #fff;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 12.5px;
            font-weight: 600;
            transition: background .2s;
            flex-shrink: 0;
        }

        .btn-selesai:hover {
            background: #2471a3;
        }

        /* Empty */
        .empty-state {
            text-align: center;
            padding: 44px 20px;
            color: #b0bec5;
            font-size: 13px;
        }

        .empty-state p {
            margin-top: 6px;
            font-size: 12.5px;
        }

        /* Info note */
        .info-note {
            background: #eaf6ff;
            border-left: 3px solid #3498db;
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            font-size: 12.5px;
            color: #1a5276;
            margin-bottom: 20px;
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
            max-width: 400px;
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
            margin-bottom: 6px;
        }

        .modal-seminar-name {
            font-size: 14px;
            font-weight: 600;
            color: #2980b9;
            background: #eaf3fb;
            border-radius: 6px;
            padding: 9px 13px;
            margin: 14px 0;
        }

        .modal-box .warn {
            font-size: 12px;
            color: #e67e22;
            margin-bottom: 20px;
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

        .btn-confirm {
            padding: 9px 22px;
            border-radius: 7px;
            border: none;
            background: #2980b9;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-confirm:hover {
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

            .seminar-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <!-- Confirm modal -->
    <div class="modal-bg" id="modalSelesai">
        <div class="modal-box">
            <h4>Konfirmasi Selesai</h4>
            <p>Anda akan menandai seminar berikut sebagai <strong>Selesai</strong>:</p>
            <div class="modal-seminar-name" id="modalSeminarName"></div>
            <p class="warn">Tindakan ini tidak dapat dibatalkan. Status seminar akan berubah menjadi Selesai dan peserta
                akan dapat melihat pembaruan ini.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Batal</button>
                <form method="POST" id="formSelesai" style="display:inline">
                    <input type="hidden" name="aksi" value="selesai">
                    <input type="hidden" name="seminar_id" id="inputSeminarId" value="">
                    <button type="submit" class="btn-confirm">Tandai Selesai</button>
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
            <a href="upload_materi.php">Upload Materi</a>
            <a href="seminar_selesai.php" class="active">Tandai Selesai</a>
            <a href="narasumber_feedback.php">Lihat Feedback</a>
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
            <span class="topbar-title">Tandai Seminar Selesai</span>
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
                    <?= $flash['msg'] ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h2>Tandai Seminar Selesai</h2>
                <p>Setelah sesi Zoom selesai, tandai seminar sebagai selesai agar peserta dapat mengakses materi yang
                    tersedia.</p>
            </div>

            <!-- SEMINAR AKTIF -->
            <div class="card">
                <div class="card-body">
                    <div class="section-heading">Seminar Aktif — Belum Diselesaikan</div>

                    <div class="info-note">
                        Klik tombol <strong>Tandai Selesai</strong> setelah sesi Zoom atau tatap muka seminar selesai
                        dilaksanakan. Peserta akan mendapatkan pembaruan status secara otomatis.
                    </div>

                    <?php if (empty($seminar_aktif)): ?>
                        <div class="empty-state">
                            Tidak ada seminar aktif saat ini.
                            <p>Semua seminar Anda sudah berstatus selesai atau belum ada undangan yang diterima.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($seminar_aktif as $sa):
                            $tgl = $sa['tanggal'] ? date('d M Y', strtotime($sa['tanggal'])) : 'Tanggal TBD';
                            $waktu = date('H:i', strtotime($sa['jam_mulai'])) . ' – ' . date('H:i', strtotime($sa['jam_selesai'])) . ' WIB';
                            ?>
                            <div class="seminar-row">
                                <div class="seminar-row-info">
                                    <div class="seminar-row-title"><?= htmlspecialchars($sa['judul_seminar']) ?></div>
                                    <div class="seminar-row-meta">
                                        <span><?= $tgl ?></span>
                                        <span><?= $waktu ?></span>
                                        <span><?= htmlspecialchars($sa['platform'] ?? 'Online') ?></span>
                                        <?php if ($sa['kategori']): ?>
                                            <span><?= htmlspecialchars($sa['kategori']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="badge badge-aktif">Aktif</span>
                                <button class="btn-selesai"
                                    onclick="konfirmasiSelesai(<?= $sa['seminar_id'] ?>, '<?= addslashes(htmlspecialchars($sa['judul_seminar'])) ?>')">
                                    Tandai Selesai
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SEMINAR SELESAI -->
            <?php if (!empty($seminar_selesai)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="section-heading">Seminar yang Sudah Selesai</div>
                        <?php foreach ($seminar_selesai as $ss):
                            $tgl_ss = $ss['tanggal'] ? date('d M Y', strtotime($ss['tanggal'])) : '-';
                            $jam_ss = date('H:i', strtotime($ss['jam_mulai'])) . ' – ' . date('H:i', strtotime($ss['jam_selesai'])) . ' WIB';
                            ?>
                            <div class="seminar-row">
                                <div class="seminar-row-info">
                                    <div class="seminar-row-title"><?= htmlspecialchars($ss['judul_seminar']) ?></div>
                                    <div class="seminar-row-meta">
                                        <span><?= $tgl_ss ?></span>
                                        <span><?= $jam_ss ?></span>
                                        <?php if ($ss['kategori']): ?>
                                            <span><?= htmlspecialchars($ss['kategori']) ?></span>
                                        <?php endif; ?>
                                        <span><?= $ss['jml_materi'] ?> materi diunggah</span>
                                    </div>
                                </div>
                                <span class="badge badge-selesai">Selesai</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

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

        function konfirmasiSelesai(id, nama) {
            document.getElementById('inputSeminarId').value = id;
            document.getElementById('modalSeminarName').textContent = nama;
            document.getElementById('modalSelesai').classList.add('show');
        }
        function closeModal() {
            document.getElementById('modalSelesai').classList.remove('show');
        }

        (function () {
            var f = document.getElementById('flashMsg');
            if (f) setTimeout(function () {
                f.style.opacity = '0'; f.style.transition = 'opacity .4s';
                setTimeout(function () { f.remove(); }, 400);
            }, 6000);
        })();
    </script>
</body>

</html>