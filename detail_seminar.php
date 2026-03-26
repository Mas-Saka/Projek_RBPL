<?php
session_start();
include "config.php";

if (!isset($_GET['id'])) {
    header("Location: seminar.php");
    exit;
}

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn, " SELECT seminar.*, users.nama as nama_narasumber FROM seminar LEFT JOIN users ON seminar.narasumber_id = users.id WHERE seminar.seminar_id = $id
"));
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Seminar</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        /* HEADER IMAGE */
        .image-wrapper {
            position: relative;
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

        /* CONTENT */
        .content {
            padding: 30px;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1e3c72;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .aktif {
            background: #d4edda;
            color: #155724;
        }

        .draft {
            background: #fff3cd;
            color: #856404;
        }

        /* GRID INFO */
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-top: 20px;
        }

        .box {
            background: #f8f9fb;
            padding: 18px;
            border-radius: 12px;
        }

        .label {
            font-size: 13px;
            color: #777;
            margin-bottom: 5px;
        }

        .value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        /* DESKRIPSI */
        .desc {
            margin-top: 25px;
            line-height: 1.7;
            color: #444;
            background: #f8f9fb;
            padding: 20px;
            border-radius: 12px;
        }

        /* BACK BUTTON */
        .back {
            margin-top: 25px;
        }

        .btn-back {
            display: inline-block;
            padding: 10px 18px;
            background: #1e3c72;
            color: white;
            border-radius: 8px;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #163d7a;
        }

        /* MODAL */
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
        }

        .modal-content {
            display: block;
            margin: 5% auto;
            max-width: 70%;
            border-radius: 12px;
            animation: zoomIn 0.3s ease;
        }

        .close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 35px;
            cursor: pointer;
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.7);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .image-wrapper img {
                height: 220px;
            }
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="card">

            <!-- GAMBAR -->
                <?php if ($data['gambar'] != "") { ?>
                <div class="image-wrapper">
                    <img src="upload/<?= $data['gambar']; ?>" onclick="showImage(this.src)">
                </div>
                <?php } ?>

            <div class="content">

                <div class="title"><?= $data['judul_seminar']; ?></div>

                <!-- STATUS -->
                    <?php if ($data['status'] == 'aktif') { ?>
                    <span class="badge aktif">Aktif</span>
                    <?php } else { ?>
                    <span class="badge draft">Draft</span>
                    <?php } ?>

                <!-- GRID DATA -->
                <div class="grid">

                    <div class="box">
                        <div class="label">Kategori</div>
                        <div class="value"><?= $data['kategori']; ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Narasumber</div>
                        <div class="value"><?= $data['nama_narasumber']; ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Tanggal</div>
                        <div class="value"><?= $data['tanggal']; ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Waktu</div>
                        <div class="value"><?= $data['jam_mulai']; ?> - <?= $data['jam_selesai']; ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Kuota Peserta</div>
                        <div class="value"><?= $data['kuota']; ?> orang</div>
                    </div>

                    <div class="box">
                        <div class="label">Harga</div>
                        <div class="value">Rp <?= number_format($data['biaya']); ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Platform</div>
                        <div class="value"><?= $data['platform']; ?></div>
                    </div>

                    <div class="box">
                        <div class="label">Link Meeting</div>
                        <div class="value"><?= $data['link_meeting']; ?></div>
                    </div>

                </div>

                <!-- DESKRIPSI -->
                <div class="desc">
                    <b>Deskripsi Seminar</b><br><br>
                        <?= ($data['deskripsi']); ?>
                </div>

                <!-- BACK -->
                <div class="back">
                    <a href="seminar.php" class="btn-back">Kembali</a>
                </div>

            </div>

        </div>

    </div>

    <!-- MODAL GAMBAR -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImg">
    </div>

    <script>
        function showImage(src) {
            document.getElementById("imageModal").style.display = "block";
            document.getElementById("modalImg").src = src;
        }

        function closeModal() {
            document.getElementById("imageModal").style.display = "none";
        }

        window.onclick = function (event) {
            let modal = document.getElementById("imageModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>

</html>