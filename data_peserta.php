<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];

$seminar = mysqli_query($conn, "
    SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.status,
           COUNT(p.id) as total_peserta
    FROM seminar s
    LEFT JOIN pendaftaran p ON s.seminar_id = p.seminar_id
    WHERE s.eo_id = $eo_id
    GROUP BY s.seminar_id
    ORDER BY s.seminar_id DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peserta</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
        }

        .container {
            max-width: 1100px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        h2 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
        }

        th {
            background: #f1f3f6;
            text-align: left;
        }

        tr {
            border-bottom: 1px solid #eee;
            transition: 0.3s;
        }

        tr:hover {
            background: #f9fbff;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .aktif {
            background: #d4edda;
            color: #155724;
        }

        .draft {
            background: #fff3cd;
            color: #856404;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            background: #3498db;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #2980b9;
            transform: scale(1.05);
        }

        @media(max-width:768px) {
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Data Peserta Seminar</h2>
        <a href="dashboardeo.php">
            <button class="btn">← Kembali</button>
        </a>
        <br>   <br> 
        <table>
            <tr>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Peserta</th>
                <th>Aksi</th>
            </tr>

            <?php while ($s = mysqli_fetch_assoc($seminar)) { ?>
                <tr>
                    <td><?= $s['judul_seminar']; ?></td>
                    <td><?= $s['tanggal']; ?></td>
                    <td>
                        <span class="status <?= $s['status'] == 'aktif' ? 'aktif' : 'draft'; ?>">
                            <?= ucfirst($s['status']); ?>
                        </span>
                    </td>
                    <td><?= $s['total_peserta']; ?></td>
                    <td>
                        <a href="detail_peserta.php?id=<?= $s['seminar_id']; ?>">
                            <button class="btn">Lihat Peserta</button>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

</body>

</html>