<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];
$seminar_id = $_GET['id'];

// validasi
$cek = mysqli_query($conn, "SELECT * FROM seminar WHERE seminar_id=$seminar_id AND eo_id=$eo_id");
if (mysqli_num_rows($cek) == 0) {
    echo "Akses ditolak";
    exit;
}

$peserta = mysqli_query($conn, "
    SELECT u.nama, u.email, u.no_hp, p.tanggal_daftar
    FROM pendaftaran p
    JOIN users u ON p.peserta_id = u.id
    WHERE p.seminar_id = $seminar_id
    ORDER BY p.tanggal_daftar DESC
");

$total = mysqli_num_rows($peserta);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peserta</title>

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

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        input {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 250px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            background: #2ecc71;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #27ae60;
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

        <div class="top">
            <div>
                <h2>Detail Peserta</h2>
                <p>Total Peserta: <b><?= $total ?></b></p>
            </div>

            <input type="text" id="search" placeholder="Cari peserta...">
        </div>

        <br>

        <a href="data_peserta.php">
            <button class="btn">← Kembali</button>
        </a>

        <table id="table">
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Tanggal Daftar</th>
            </tr>

            <?php while ($p = mysqli_fetch_assoc($peserta)) { ?>
                <tr>
                    <td><?= $p['nama']; ?></td>
                    <td><?= $p['email']; ?></td>
                    <td><?= $p['no_hp']; ?></td>
                    <td><?= $p['tanggal_daftar']; ?></td>
                </tr>
            <?php } ?>
        </table>

    </div>

    <script>
        // search realtime
        document.getElementById("search").addEventListener("keyup", function () {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#table tr");

            rows.forEach((row, index) => {
                if (index === 0) return;
                row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
            });
        });
    </script>

</body>

</html>