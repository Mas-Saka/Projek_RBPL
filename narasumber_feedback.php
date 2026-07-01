<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'narasumber') {
    header("Location: login.php");
    exit;
}

$narasumber_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'] ?? 'Narasumber';

// Filter
$filter_seminar = (int) ($_GET['seminar_id'] ?? 0);
$filter_rating = (int) ($_GET['rating'] ?? 0);

// Seminar milik narasumber ini yang sudah selesai
$seminar_list = [];
$q = mysqli_query($conn, "SELECT seminar_id, judul_seminar FROM seminar WHERE narasumber_id = $narasumber_id AND status = 'selesai' ORDER BY seminar_id DESC");
while ($r = mysqli_fetch_assoc($q))
    $seminar_list[] = $r;

// Buat where
$where = ["s.narasumber_id = $narasumber_id", "s.status = 'selesai'"];
if ($filter_seminar)
    $where[] = "f.seminar_id = $filter_seminar";
if ($filter_rating)
    $where[] = "f.rating = $filter_rating";
$where_sql = implode(' AND ', $where);

$feedback_q = mysqli_query($conn, "
    SELECT f.id, f.rating, f.komentar, f.tanggal_feedback,
           u.nama AS nama_peserta,
           s.judul_seminar, s.seminar_id, s.tanggal AS tgl_seminar
    FROM feedback f
    JOIN users u   ON f.peserta_id = u.id
    JOIN seminar s ON f.seminar_id = s.seminar_id
    WHERE $where_sql
    ORDER BY f.tanggal_feedback DESC
");

$feedbacks = [];
while ($r = mysqli_fetch_assoc($feedback_q))
    $feedbacks[] = $r;

// Statistik personal narasumber
$stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(f.id) AS total,
           AVG(f.rating) AS avg_rating,
           SUM(f.rating = 5) AS bintang5,
           SUM(f.rating = 4) AS bintang4,
           SUM(f.rating = 3) AS bintang3,
           SUM(f.rating = 2) AS bintang2,
           SUM(f.rating = 1) AS bintang1
    FROM feedback f
    JOIN seminar s ON f.seminar_id = s.seminar_id
    WHERE s.narasumber_id = $narasumber_id
      AND s.status = 'selesai'
      AND f.status_validasi = 'valid'
"));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Saya — Narasumber</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue: #3498db;
            --blue-dk: #2980b9;
            --dark: #2c3e50;
            --bg: #f4f7f6;
            --white: #ffffff;
            --text: #333333;
            --muted: #888888;
            --border: #e0e6ed;
            --green: #27ae60;
            --yellow: #f39c12;
            --shadow: 0 4px 24px rgba(44, 62, 80, .10);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: var(--blue);
        }

        header {
            background: var(--white);
            height: 64px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        }

        .header-inner {
            width: 92%;
            max-width: 1200px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--blue);
        }

        .back-link {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color .2s;
        }

        .back-link:hover {
            color: var(--blue);
        }

        .back-link::before {
            content: '←';
            font-size: 16px;
        }

        .wrap {
            width: 92%;
            max-width: 1100px;
            margin: 36px auto 80px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .page-sub {
            font-size: 13.5px;
            color: var(--muted);
            margin-bottom: 28px;
        }

        /* HIGHLIGHT PERSONAL SCORE */
        .personal-score {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            border-radius: 16px;
            padding: 32px 36px;
            color: #fff;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 36px;
            flex-wrap: wrap;
        }

        .score-left .score-num {
            font-size: 64px;
            font-weight: 700;
            line-height: 1;
        }

        .score-left .score-stars {
            font-size: 24px;
            color: var(--yellow);
            margin: 6px 0;
        }

        .score-left p {
            font-size: 13px;
            opacity: .75;
        }

        .score-bars {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 220px;
        }

        .bar-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: rgba(255, 255, 255, .8);
        }

        .bar-label {
            width: 60px;
            flex-shrink: 0;
        }

        .bar-track {
            flex: 1;
            background: rgba(255, 255, 255, .2);
            border-radius: 99px;
            height: 9px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: var(--yellow);
            border-radius: 99px;
            transition: width .5s ease;
        }

        .bar-count {
            width: 26px;
            text-align: right;
            font-weight: 600;
            color: #fff;
        }

        /* FILTER */
        .filter-bar {
            background: var(--white);
            border-radius: 12px;
            padding: 18px 24px;
            box-shadow: var(--shadow);
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 22px;
        }

        .filter-bar label {
            font-size: 12px;
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: .04em;
            display: block;
            margin-bottom: 5px;
        }

        .filter-bar select {
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 8px 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            color: var(--text);
            outline: none;
            transition: border-color .2s;
            background: var(--white);
        }

        .filter-bar select:focus {
            border-color: var(--blue);
        }

        .btn-filter {
            padding: 9px 22px;
            background: var(--blue);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
            align-self: flex-end;
        }

        .btn-filter:hover {
            background: var(--blue-dk);
        }

        

       

        /* CARDS */
        .total-info {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 14px;
        }

        .total-info strong {
            color: var(--dark);
        }

        .feedback-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feedback-card {
            background: var(--white);
            border-radius: 13px;
            padding: 22px 26px;
            box-shadow: var(--shadow);
            border-left: 4px solid transparent;
            transition: transform .18s, box-shadow .18s;
            animation: fadeUp .3s ease both;
        }

        .feedback-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(44, 62, 80, .13);
        }

        .feedback-card[data-rating="5"] {
            border-left-color: #27ae60;
        }

        .feedback-card[data-rating="4"] {
            border-left-color: #2ecc71;
        }

        .feedback-card[data-rating="3"] {
            border-left-color: #f39c12;
        }

        .feedback-card[data-rating="2"] {
            border-left-color: #e67e22;
        }

        .feedback-card[data-rating="1"] {
            border-left-color: #e74c3c;
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .peserta-info h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .peserta-info p {
            font-size: 12.5px;
            color: var(--muted);
        }

        .rating-stars {
            color: var(--yellow);
            font-size: 20px;
            letter-spacing: 1px;
        }

        .seminar-tag {
            display: inline-block;
            background: #eaf4fc;
            color: var(--blue);
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 10px;
        }

        .komentar-text {
            font-size: 14px;
            color: #444;
            line-height: 1.65;
            background: #f9fbfc;
            border-radius: 8px;
            padding: 12px 14px;
            border: 1px solid #eef2f5;
        }

        .card-foot {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .tanggal {
            font-size: 12px;
            color: var(--muted);
        }

        .empty-state {
            background: var(--white);
            border-radius: 14px;
            padding: 60px 24px;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .empty-state h3 {
            font-size: 17px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            color: var(--muted);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 700px) {
            .personal-score {
                flex-direction: column;
                gap: 22px;
                padding: 24px 20px;
            }

            .score-left .score-num {
                font-size: 48px;
            }

            .wrap {
                width: 95%;
                margin-top: 24px;
            }

            .feedback-card {
                padding: 18px 16px;
            }

            .filter-bar {
                flex-direction: column;
                gap: 10px;
            }

            .filter-bar select {
                width: 100%;
            }

            .btn-filter,
             {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="header-inner">
            <a href="index.php" class="logo">SeminarOnline</a>
            <a href="dashboardnarasumber.php" class="back-link">Dashboard Narasumber</a>
        </div>
    </header>

    <div class="wrap">
        <h1 class="page-title">Feedback untuk Saya</h1>
        <p class="page-sub">Komentar dan penilaian peserta terhadap seminar yang Anda pandu.</p>

        <!-- SKOR PERSONAL -->
        <?php if ($stats['total'] > 0): ?>
            <div class="personal-score">
                <div class="score-left">
                    <div class="score-num">
                        <?= number_format($stats['avg_rating'], 1) ?>
                    </div>
                    <div class="score-stars">
                        <?php
                        $avg = round($stats['avg_rating']);
                        echo str_repeat('★', $avg) . str_repeat('☆', 5 - $avg);
                        ?>
                    </div>
                    <p>
                        <?= $stats['total'] ?> feedback diterima
                    </p>
                </div>
                <div class="score-bars">
                    <?php for ($b = 5; $b >= 1; $b--): ?>
                        <?php
                        $jml = (int) $stats["bintang$b"];
                        $pct = $stats['total'] > 0 ? ($jml / $stats['total'] * 100) : 0;
                        ?>
                        <div class="bar-row">
                            <span class="bar-label">
                                <?= $b ?> bintang
                            </span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="bar-count">
                                <?= $jml ?>
                            </span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- FILTER -->
        <form method="GET" class="filter-bar">
            <div>
                <label>Seminar</label>
                <select name="seminar_id">
                    <option value="">Semua Seminar</option>
                    <?php foreach ($seminar_list as $sl): ?>
                        <option value="<?= $sl['seminar_id'] ?>" <?= $filter_seminar == $sl['seminar_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(mb_strimwidth($sl['judul_seminar'], 0, 45, '...')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Rating</label>
                <select name="rating">
                    <option value="">Semua Rating</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= $filter_rating == $i ? 'selected' : '' ?>>
                            <?= $i ?> Bintang
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn-filter">Terapkan</button>
            
        </form>

        <!-- LIST -->
        <p class="total-info">Menampilkan <strong>
                <?= count($feedbacks) ?>
            </strong> feedback</p>

        <?php if (empty($feedbacks)): ?>
            <div class="empty-state">
                <h3>Belum Ada Feedback</h3>
                <p>Belum ada feedback yang masuk untuk seminar Anda, atau belum ada yang divalidasi.</p>
            </div>
        <?php else: ?>
            <div class="feedback-list">
                <?php foreach ($feedbacks as $i => $fb): ?>
                    <div class="feedback-card" data-rating="<?= $fb['rating'] ?>" style="animation-delay: <?= $i * 0.05 ?>s">
                        <div class="card-top">
                            <div class="peserta-info">
                                <h4>
                                    <?= htmlspecialchars($fb['nama_peserta']) ?>
                                </h4>
                                <p>Seminar:
                                    <?= date('d M Y', strtotime($fb['tgl_seminar'])) ?>
                                </p>
                            </div>
                            <div class="rating-stars">
                                <?= str_repeat('★', $fb['rating']) . str_repeat('☆', 5 - $fb['rating']) ?>
                            </div>
                        </div>
                        <span class="seminar-tag">
                            <?= htmlspecialchars($fb['judul_seminar']) ?>
                        </span>
                        <div class="komentar-text">
                            <?= nl2br(htmlspecialchars($fb['komentar'])) ?>
                        </div>
                        <div class="card-foot">
                            <span class="tanggal">
                                <?= date('d M Y, H:i', strtotime($fb['tanggal_feedback'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>