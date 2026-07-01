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

// Statistik
$total_seminar = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM seminar WHERE eo_id=$eo_id"
))['total'];

$seminar_aktif = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM seminar WHERE eo_id=$eo_id AND status='aktif'"
))['total'];

$total_peserta = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM pendaftaran 
     JOIN seminar ON pendaftaran.seminar_id= seminar.seminar_id
     WHERE seminar.eo_id=$eo_id"
))['total'];

$total_feedback = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as total FROM feedback 
     JOIN seminar ON feedback.seminar_id=seminar.seminar_id
     WHERE seminar.eo_id=$eo_id"
))['total'];

$seminar_list = mysqli_query($conn, "SELECT seminar_id, judul_seminar, tanggal, jam_mulai, kuota, status FROM seminar WHERE eo_id=$eo_id ORDER BY seminar_id DESC");

$kontrak_list = mysqli_query($conn, "SELECT k.*, u.nama as nama_klien 
    FROM kontrak k
    JOIN users u ON k.klien_id = u.id
    WHERE k.eo_id = $eo_id
    ORDER BY k.kontrak_id DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard EO — SeminarOnline</title>
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

        /* Welcome Box */
        .welcome {
            background: linear-gradient(130deg, #1e3c72 0%, #2a5298 60%, #3498db 100%);
            border-radius: 12px;
            padding: 26px 28px;
            color: #fff;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .welcome-text h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .welcome-text p {
            font-size: 12.5px;
            color: rgba(255, 255, 255, .72);
        }

        .welcome-badge {
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 10px;
            padding: 10px 18px;
            text-align: center;
        }

        .welcome-badge .wb-label {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .6);
        }

        .welcome-badge .wb-date {
            font-size: 13px;
            font-weight: 600;
            margin-top: 3px;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border-left: 3px solid #3498db;
        }

        .stat-card.green {
            border-left-color: #27ae60;
        }

        .stat-card.amber {
            border-left-color: #f39c12;
        }

        .stat-card.purple {
            border-left-color: #8e44ad;
        }

        .stat-num {
            font-size: 28px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 5px;
        }

        /* Buttons */
        .btn-daftar {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            cursor: pointer;
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
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
        }

        .btn-detail-sm:hover {
            background: #e8edf2;
        }

        .btn-hapus-sm {
            background: #fde8e8;
            color: #c0392b;
            border: 1px solid #f8b4b4;
            border-radius: 7px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: background .18s;
        }

        .btn-hapus-sm:hover {
            background: #f8b4b4;
        }

        /* Table Card */
        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border: 1px solid #e8edf2;
            overflow: hidden;
            margin-bottom: 26px;
        }

        .table-head {
            padding: 18px 22px 14px;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-head h3 {
            font-size: 14.5px;
            font-weight: 700;
            color: #1a2634;
        }

        .table-head p {
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

        .badge-diterima {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-menunggu {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-ditolak {
            background: #fde8e8;
            color: #c0392b;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(2px);
            justify-content: center;
            align-items: center;
        }

        .modal-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 90%;
            max-width: 320px;
            text-align: center;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-box h3 {
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #1a2634;
        }

        .modal-box p {
            margin-bottom: 20px;
            color: #7f8c8d;
            font-size: 13px;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .close-modal-x {
            position: absolute;
            top: 12px;
            right: 16px;
            font-size: 20px;
            cursor: pointer;
            color: #95a5a6;
        }

        /* Responsive */
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

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width:720px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 18px 14px 40px;
            }

            .welcome-badge {
                display: none;
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
            <a href="dashboardeo.php" class="active">Dashboard</a>
            <a href="seminar.php">Kelola Seminar</a>
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
            <span class="topbar-title">Dashboard EO</span>
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

            <div class="welcome">
                <div class="welcome-text">
                    <h2>Halo, <?= htmlspecialchars(explode(' ', $nama_eo)[0]) ?>!</h2>
                    <p>Selamat datang di dashboard Event Organizer. Kelola seminar dan kontrak Anda dengan mudah.</p>
                </div>
                <div class="welcome-badge">
                    <div class="wb-label">Hari ini</div>
                    <div class="wb-date" id="todayDate">—</div>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-num"><?= $total_seminar ?></div>
                    <div class="stat-label">Total Seminar</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-num"><?= $seminar_aktif ?></div>
                    <div class="stat-label">Seminar Aktif</div>
                </div>
                <div class="stat-card amber">
                    <div class="stat-num"><?= $total_peserta ?></div>
                    <div class="stat-label">Total Peserta</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-num"><?= $total_feedback ?></div>
                    <div class="stat-label">Total Feedback</div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-head">
                    <div>
                        <h3>Kelola Seminar</h3>
                        <p>Daftar seminar terbaru yang telah Anda buat</p>
                    </div>
                    <a href="seminar.php" class="btn-daftar">+ Tambah Seminar</a>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul Seminar</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Kuota</th>
                                <th>Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($seminar_list) > 0): ?>
                                <?php while ($s = mysqli_fetch_assoc($seminar_list)) {
                                    $status_class = $s['status'] == 'aktif' ? 'badge-diterima' : 'badge-menunggu';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="td-title"
                                                style="max-width:250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                title="<?= htmlspecialchars($s['judul_seminar']) ?>">
                                                <?= htmlspecialchars($s['judul_seminar']) ?>
                                            </div>
                                        </td>
                                        <td><?= date('d M Y', strtotime($s['tanggal'])) ?></td>
                                        <td><?= $s['jam_mulai'] ?></td>
                                        <td><?= $s['kuota'] ?></td>
                                        <td><span class="tbl-badge <?= $status_class ?>"><?= ucfirst($s['status']) ?></span>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="detail_seminar.php?id=<?= $s['seminar_id'] ?>"
                                                class="btn-detail-sm">Detail</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 30px; color:#95a5a6;">Belum ada data
                                        seminar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            </div>
 <div class="table-card">
                <div class="table-head">
                    <div>
                        <h3>Data Kontrak Klien</h3>
                        <p>Daftar kontrak kerja sama yang diajukan</p>
                    </div>
                    <a href="buat_kontrak.php" class="btn-daftar">+ Buat Kontrak</a>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul Kontrak</th>
                                <th>Klien</th>
                                <th>Tanggal Buat</th>
                                <th>Nilai Kontrak</th>
                                <th>Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($kontrak_list) > 0) { ?>
                                <?php while ($k = mysqli_fetch_assoc($kontrak_list)) {
                                    $status_class = 'badge-menunggu';
                                    $status_text = 'Menunggu';
                                    if ($k['status_kontrak'] == 'disetujui') {
                                        $status_class = 'badge-diterima';
                                        $status_text = 'Disetujui';
                                    } elseif ($k['status_kontrak'] == 'ditolak') {
                                        $status_class = 'badge-ditolak';
                                        $status_text = 'Ditolak';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="td-title"
                                                style="max-width:200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                title="<?= htmlspecialchars($k['judul_kontrak']) ?>">
                                                <?= htmlspecialchars($k['judul_kontrak']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($k['nama_klien']) ?></td>
                                        <td><?= date('d M Y', strtotime($k['tanggal_buat'])) ?></td>
                                        <td style="font-weight:600; color:#27ae60;">Rp
                                            <?= number_format($k['nilai_kontrak'], 0, ',', '.') ?></td>
                                        <td><span class="tbl-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                                        <td style="text-align: center;">
                                            <div style="display:flex; gap:8px; justify-content:center;">
                                                <a href="detail_kontrak.php?id=<?= $k['kontrak_id'] ?>"
                                                    class="btn-detail-sm">Detail</a>
                                                <button class="btn-hapus-sm"
                                                    onclick="openModal(<?= $k['kontrak_id'] ?>)">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 30px; color:#95a5a6;">Belum ada data
                                        kontrak.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div id="modalHapus" class="modal">
        <div class="modal-box">
            <span class="close-modal-x" onclick="closeModal()">&times;</span>
            <h3>Konfirmasi Hapus</h3>
            <p>Yakin ingin menghapus kontrak ini?</p>
            <div class="modal-actions">
                <button class="btn-hapus-sm" style="padding: 8px 16px; border:none; background:#e74c3c; color:#fff;"
                    onclick="hapusKontrak()">Ya, Hapus</button>
                <button class="btn-detail-sm" style="padding: 8px 16px; border:none;"
                    onclick="closeModal()">Batal</button>
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

        (function () {
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            var d = new Date();
            document.getElementById('todayDate').textContent = days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()];
        })();

        let selectedId = null;
        function openModal(id) {
            selectedId = id;
            document.getElementById("modalHapus").style.display = "flex";
        }
        function closeModal() {
            document.getElementById("modalHapus").style.display = "none";
        }
        function hapusKontrak() {
            if (selectedId) {
                window.location.href = "hapuskontrak.php?id=" + selectedId;
            }
        }
    </script>
</body>

</html> 