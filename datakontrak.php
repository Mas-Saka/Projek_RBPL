<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'klien') {
    header("Location: login.php");
    exit;
}

$klien_id = $_SESSION['id'];

$query = mysqli_query($conn, "SELECT kontrak_id, nomor_kontrak, tanggal_buat, tanggal_mulai, 
           tanggal_selesai, nilai_kontrak, status_kontrak
    FROM kontrak
    WHERE klien_id = $klien_id
    ORDER BY tanggal_buat DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Kontrak</title>
    <style>
       body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
}

/* CONTAINER */
.container {
    max-width: 1100px;
    margin: 50px auto;
    padding: 0 20px;
}

/* HEADER */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    color: white;
}

.header h2 {
    margin: 0;
    font-size: 26px;
}

.subtitle {
    font-size: 13px;
    opacity: 0.8;
    margin-top: 4px;
}

/* BACK BUTTON */
.back-btn {
    background: white;
    color: #1e3c72;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: 0.25s;
}

.back-btn:hover {
    background: #f0f4ff;
    transform: translateY(-2px);
}

/* CARD */
.card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    animation: fadeIn 0.5s ease;
}

/* TABLE WRAPPER */
.table-wrapper {
    overflow-x: auto;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 750px;
}

/* HEADER TABLE */
thead {
    background: linear-gradient(135deg, #eef2ff, #f8faff);
}

th {
    padding: 14px;
    text-align: left;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #444;
}

/* ROW */
td {
    padding: 14px;
    font-size: 14px;
    color: #333;
}

/* BORDER */
tr {
    border-bottom: 1px solid #eee;
}

/* HOVER EFFECT */
tbody tr {
    transition: 0.2s;
}

tbody tr:hover {
    background: #f5f8ff;
    transform: scale(1.002);
}

/* CONTRACT NUMBER */
.contract-number {
    font-weight: 600;
    color: #2a5298;
}

/* DATE RANGE */
.date-range {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
}

.divider {
    color: #aaa;
}

/* PRICE */
.price {
    font-weight: 600;
    color: #16a085;
}

/* BADGE */
.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
    color: white;
    display: inline-block;
    letter-spacing: 0.5px;
}

/* STATUS COLORS */
.menunggu {
    background: linear-gradient(135deg, #f39c12, #f1c40f);
}

.disetujui {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
}

.ditolak {
    background: linear-gradient(135deg, #e74c3c, #ff6b6b);
}

/* BUTTON */
.detail-btn {
    background: linear-gradient(135deg, #2a5298, #1e3c72);
    color: white;
    padding: 7px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: 0.25s;
    display: inline-block;
}

.detail-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

/* ANIMATION */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* SCROLLBAR */
.table-wrapper::-webkit-scrollbar {
    height: 8px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 10px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .back-btn {
        width: 100%;
        text-align: center;
    }

    table {
        font-size: 12px;
    }

    th, td {
        padding: 10px;
    }
}

/* EXTRA POLISH */
tbody tr td:first-chilsd {
    color: #888;
    font-size: 13px;
}

tbody tr td:last-child {
    min-width: 120px;
}
    </style>
</head>

<body>
    <div class="container">

        <div class="header">
            <div>
                <h2>Daftar Kontrak</h2>
                <p class="subtitle">Kelola dan pantau semua kontrak Anda</p>
            </div>
            <a href="dashboardklien.php" class="back-btn">← Dashboard</a>
        </div>

        <div class="card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Kontrak</th>
                            <th>Periode</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1;
                        while ($data = mysqli_fetch_assoc($query)) { ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td class="contract-number">
                                    <?= $data['nomor_kontrak']; ?>
                                </td>

                                <td>
                                    <div class="date-range">
                                        <span><?= $data['tanggal_mulai']; ?></span>
                                        <span class="divider">→</span>
                                        <span><?= $data['tanggal_selesai']; ?></span>
                                    </div>
                                </td>

                                <td class="price">
                                    Rp <?= number_format($data['nilai_kontrak'], 0, ',', '.'); ?>
                                </td>

                                <td>
                                    <span class="badge <?= $data['status_kontrak']; ?>">
                                        <?= strtoupper($data['status_kontrak']); ?>
                                    </span>
                                </td>

                                <td style="text-align:center;">
                                    <a href="detail_kontrak.php?id=<?= $data['kontrak_id']; ?>" class="detail-btn">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>