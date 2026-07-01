<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'klien') {
    header("Location: login.php");
    exit;
}

$klien_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'] ?? 'Klien';
$foto_user = $_SESSION['foto_profil'] ?? null;

$query = mysqli_query($conn, "SELECT kontrak_id, nomor_kontrak, tanggal_buat, tanggal_mulai, 
           tanggal_selesai, nilai_kontrak, status_kontrak
    FROM kontrak
    WHERE klien_id = $klien_id
    ORDER BY tanggal_buat DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kontrak — SeminarOnline</title>
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
            flex-wrap: wrap;
            gap: 15px;
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

        /* Filters */
        .controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .controls input,
        .controls select {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #e8edf2;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #2c3e50;
            outline: none;
            transition: border-color .2s;
            background: #f8fafc;
        }

        .controls input:focus,
        .controls select:focus {
            border-color: #3498db;
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

        .td-price {
            font-weight: 600;
            color: #27ae60;
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
        }

        @media (max-width:720px) {
            .content {
                padding: 18px 14px 40px;
            }

            .controls {
                flex-direction: column;
                width: 100%;
                align-items: stretch;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Portal Klien</p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Menu Utama</span>
            <a href="dashboardklien.php">Dashboard</a>
            <a href="datakontrak.php" class="active">Data Kontrak</a>
            <a href="lihat_laporan.php">Laporan Akhir</a>

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
            <span class="topbar-title">Data Kontrak</span>
        </div>
        <div class="user-pill">
            <div class="user-avatar">
                <?php if ($foto_user): ?>
                    <img src="upload/foto_profil/<?= htmlspecialchars($foto_user) ?>">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($nama_user, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <?= htmlspecialchars($nama_user) ?>
        </div>
    </div>

    <div class="main" id="main">
        <div class="content">

            <div class="table-card">
                <div class="table-head">
                    <div>
                        <h3>Seluruh Data Kontrak</h3>
                        <p>Kelola dan pantau semua pengajuan kontrak Anda</p>
                    </div>
                    <div class="controls">
                        <input type="text" id="searchInput" placeholder="Cari nomor kontrak / status...">
                        <select id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                </div>

                <div class="tbl-wrap">
                    <table id="kontrakTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Kontrak</th>
                                <th>Periode</th>
                                <th>Nilai Kontrak</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($query) > 0):
                                while ($data = mysqli_fetch_assoc($query)):
                                    $status_class = 'badge-menunggu';
                                    if ($data['status_kontrak'] == 'disetujui')
                                        $status_class = 'badge-diterima';
                                    elseif ($data['status_kontrak'] == 'ditolak')
                                        $status_class = 'badge-ditolak';
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td>
                                            <div class="td-title nomor"><?= htmlspecialchars($data['nomor_kontrak']); ?></div>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($data['tanggal_mulai'])) ?> –
                                            <?= date('d/m/Y', strtotime($data['tanggal_selesai'])) ?></td>
                                        <td class="td-price">Rp <?= number_format($data['nilai_kontrak'], 0, ',', '.'); ?></td>
                                        <td><span
                                                class="tbl-badge <?= $status_class ?> status_lbl"><?= ucfirst($data['status_kontrak']) ?></span>
                                        </td>
                                        <td>
                                            <a href="detail_kontrak.php?id=<?= $data['kontrak_id']; ?>"
                                                class="btn-detail-sm">Detail</a>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 30px; color: #95a5a6;">Belum ada
                                        kontrak yang terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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

        /* Filter Logic */
        const searchInput = document.getElementById("searchInput");
        const filterStatus = document.getElementById("filterStatus");
        const rows = document.querySelectorAll("#kontrakTable tbody tr");

        function filterTable() {
            let keyword = searchInput.value.toLowerCase();
            let selectedStatus = filterStatus.value.toLowerCase();

            rows.forEach(function (row) {
                let colNomor = row.querySelector(".nomor");
                let colStatus = row.querySelector(".status_lbl");

                if (!colNomor || !colStatus) return;

                let nomor = colNomor.innerText.toLowerCase();
                let status = colStatus.innerText.toLowerCase();

                let cocokSearch = nomor.includes(keyword) || status.includes(keyword);
                let cocokFilter = selectedStatus === "" || status.includes(selectedStatus);

                if (cocokSearch && cocokFilter) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        searchInput.addEventListener("keyup", filterTable);
        filterStatus.addEventListener("change", filterTable);
    </script>

</body>

</html>