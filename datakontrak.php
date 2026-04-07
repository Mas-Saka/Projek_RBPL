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

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            margin-bottom: 20px;
        }

        .back-btn {
            background: white;
            color: #1e3c72;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        /* SEARCH */
        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .controls input,
        .controls select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .controls input {
            flex: 1;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background: #f0f4ff;
        }

        tr {
            border-bottom: 1px solid #eee;
        }

        tbody tr:hover {
            background: #f7f9ff;
        }

        .contract-number {
            font-weight: bold;
            color: #2a5298;
        }

        .price {
            color: #16a085;
            font-weight: bold;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            color: white;
        }

        .menunggu {
            background: orange;
        }

        .disetujui {
            background: green;
        }

        .ditolak {
            background: red;
        }

        .detail-btn {
            background: #2a5298;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
        }

        /* MOBILE */
        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                background: white;
                margin-bottom: 15px;
                padding: 10px;
                border-radius: 10px;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            }

            td {
                display: flex;
                justify-content: space-between;
                padding: 8px;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                color: #555;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <h2>Daftar Kontrak</h2>
            <a href="dashboardklien.php" class="back-btn">← Dashboard</a>
        </div>

        <div class="card">

            <!-- SEARCH + FILTER -->
            <div class="controls">
                <input type="text" id="searchInput" placeholder="Cari nomor kontrak / status...">

                <select id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>

            <table id="kontrakTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor</th>
                        <th>Periode</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no = 1;
                    while ($data = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td data-label="No"><?= $no++; ?></td>

                            <td data-label="Nomor" class="contract-number nomor">
                                <?= $data['nomor_kontrak']; ?>
                            </td>

                            <td data-label="Periode">
                                <?= $data['tanggal_mulai']; ?> → <?= $data['tanggal_selesai']; ?>
                            </td>

                            <td data-label="Nilai" class="price">
                                Rp <?= number_format($data['nilai_kontrak'], 0, ',', '.'); ?>
                            </td>

                            <td data-label="Status">
                                <span class="badge <?= $data['status_kontrak']; ?> status">
                                    <?= strtoupper($data['status_kontrak']); ?>
                                </span>
                            </td>

                            <td data-label="Aksi">
                                <a href="detail_kontrak.php?id=<?= $data['kontrak_id']; ?>" class="detail-btn">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>

    </div>

    <script>
        const searchInput = document.getElementById("searchInput");
        const filterStatus = document.getElementById("filterStatus");
        const rows = document.querySelectorAll("#kontrakTable tbody tr");

        function filterTable() {
            let keyword = searchInput.value.toLowerCase();
            let selectedStatus = filterStatus.value.toLowerCase();

            rows.forEach(function (row) {
                let nomor = row.querySelector(".nomor").innerText.toLowerCase();
                let status = row.querySelector(".status").innerText.toLowerCase();

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