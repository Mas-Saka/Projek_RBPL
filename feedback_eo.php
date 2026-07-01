<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'eo') {
    header("Location: login.php");
    exit;
}

$eo_id = $_SESSION['id'];

// Filter
$filter_seminar = (int) ($_GET['seminar_id'] ?? 0);
$filter_rating = (int) ($_GET['rating'] ?? 0);
$filter_topik = mysqli_real_escape_string($conn, $_GET['topik'] ?? '');

// Ambil seminar milik EO
$seminar_list = [];
$q = mysqli_query($conn, "SELECT seminar_id, judul_seminar FROM seminar WHERE eo_id = $eo_id AND status = 'selesai' ORDER BY seminar_id DESC");
while ($r = mysqli_fetch_assoc($q))
    $seminar_list[] = $r;

// Query feedback
$where = ["s.eo_id = $eo_id", "s.status = 'selesai'"];
if ($filter_seminar)
    $where[] = "f.seminar_id = $filter_seminar";
if ($filter_rating)
    $where[] = "f.rating = $filter_rating";
if ($filter_topik)
    $where[] = "f.komentar LIKE '%$filter_topik%'";

$where_sql = implode(' AND ', $where);

$feedback_q = mysqli_query($conn, "
    SELECT f.id, f.rating, f.komentar, f.tanggal_feedback, f.status_validasi,
           u.nama AS nama_peserta,
           s.judul_seminar, s.seminar_id,
           nr.nama AS nama_narasumber
    FROM feedback f
    JOIN users u  ON f.peserta_id = u.id
    JOIN seminar s ON f.seminar_id = s.seminar_id
    LEFT JOIN users nr ON s.narasumber_id = nr.id
    WHERE $where_sql
    ORDER BY f.tanggal_feedback DESC
");

$feedbacks = [];
while ($r = mysqli_fetch_assoc($feedback_q))
    $feedbacks[] = $r;

// Statistik ringkasan
$stats_q = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(f.id) AS total,
           AVG(f.rating) AS avg_rating,
           SUM(f.rating = 5) AS bintang5,
           SUM(f.rating = 4) AS bintang4,
           SUM(f.rating = 3) AS bintang3,
           SUM(f.rating = 2) AS bintang2,
           SUM(f.rating = 1) AS bintang1
    FROM feedback f
    JOIN seminar s ON f.seminar_id = s.seminar_id
    WHERE s.eo_id = $eo_id AND s.status = 'selesai'
"));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Peserta — EO Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        /* Reset manual satu per satu */
        html {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            background: #f4f7f6;
            color: #333333;
            min-height: 100vh;
        }

        header {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background: #ffffff;
            height: 64px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        }

        div {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        h1 {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        h3 {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        h4 {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        p {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        span {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        form {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        label {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        select {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        input {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        button {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        a {
            text-decoration: none;
            color: #3498db;
        }

        /* ── HEADER ── */
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
            color: #3498db;
        }

        .back-link {
            font-size: 13.5px;
            font-weight: 500;
            color: #888888;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color .2s;
        }

        .back-link:hover {
            color: #3498db;
        }

        .back-link::before {
            content: '←';
            font-size: 16px;
        }

        /* ── WRAP ── */
        .wrap {
            width: 92%;
            max-width: 1100px;
            margin: 36px auto 80px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .page-sub {
            font-size: 13.5px;
            color: #888888;
            margin-bottom: 28px;
        }

        /* ── STATS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-avg {
            background: #ffffff;
            border-radius: 14px;
            padding: 28px 24px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, .10);
            text-align: center;
            border-top: 4px solid #3498db;
        }

        .stat-avg .big-num {
            font-size: 52px;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
        }

        .stat-avg .stars-disp {
            color: #f39c12;
            font-size: 22px;
            margin: 6px 0;
        }

        .stat-avg p {
            font-size: 13px;
            color: #888888;
        }

        .stat-bars {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px 28px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, .10);
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: center;
        }

        .bar-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }

        .bar-label {
            width: 60px;
            color: #888888;
            flex-shrink: 0;
        }

        .bar-track {
            flex: 1;
            background: #eee;
            border-radius: 99px;
            height: 10px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: #f39c12;
            border-radius: 99px;
            transition: width .5s ease;
        }

        .bar-count {
            width: 28px;
            text-align: right;
            font-weight: 600;
            color: #2c3e50;
        }

        /* ── FILTER ── */
        .filter-bar {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px 24px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, .10);
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 22px;
        }

        .filter-bar label {
            font-size: 12px;
            font-weight: 600;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: .04em;
            display: block;
            margin-bottom: 5px;
        }

        .filter-bar select,
        .filter-bar input {
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            padding: 8px 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            color: #333333;
            outline: none;
            transition: border-color .2s;
            background: #ffffff;
        }

        .filter-bar select:focus,
        .filter-bar input:focus {
            border-color: #3498db;
        }

        .btn-filter {
            padding: 9px 22px;
            background: #3498db;
            color: #ffffff;
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
            background: #2980b9;
        }

        .btn-reset {
            padding: 9px 16px;
            background: transparent;
            color: #888888;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s;
            align-self: flex-end;
        }

        .btn-reset:hover {
            border-color: #3498db;
            color: #3498db;
        }

        /* ── FEEDBACK LIST ── */
        .feedback-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feedback-card {
            background: #ffffff;
            border-radius: 13px;
            padding: 22px 26px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, .10);
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
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .peserta-info p {
            font-size: 12.5px;
            color: #888888;
        }

        .rating-stars {
            color: #f39c12;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .seminar-tag {
            display: inline-block;
            background: #eaf4fc;
            color: #3498db;
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
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tanggal {
            font-size: 12px;
            color: #888888;
        }

        .badge-status {
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .badge-pending {
            background: #fef9e7;
            color: #d4ac0d;
            border: 1px solid #f7dc6f;
        }

        .badge-valid {
            background: #eafaf1;
            color: #1e8449;
            border: 1px solid #a9dfbf;
        }

        /* ── KOSONG ── */
        .empty-state {
            background: #ffffff;
            border-radius: 14px;
            padding: 60px 24px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(44, 62, 80, .10);
        }

        .empty-state h3 {
            font-size: 17px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 14px;
            color: #888888;
        }

        /* ── TOTAL ── */
        .total-info {
            font-size: 13px;
            color: #888888;
            margin-bottom: 14px;
        }

        .total-info strong {
            color: #2c3e50;
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
            .stats-grid {
                grid-template-columns: 1fr;
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

            .filter-bar select,
            .filter-bar input {
                width: 100%;
            }

            .btn-filter,
            .btn-reset {
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
            <a href="dashboardeo.php" class="back-link">Dashboard EO</a>
        </div>
    </header>

    <div class="wrap">
        <h1 class="page-title">Feedback Peserta</h1>
        <p class="page-sub">Ringkasan dan detail penilaian dari peserta untuk seminar yang sudah selesai.</p>

        <!-- STATISTIK -->
        <?php if ($stats_q['total'] > 0): ?>
            <div class="stats-grid">
                <div class="stat-avg">
                    <div class="big-num"><?= number_format($stats_q['avg_rating'], 1) ?></div>
                    <div class="stars-disp">
                        <?php
                        $avg = round($stats_q['avg_rating']);
                        echo str_repeat('★', $avg) . str_repeat('☆', 5 - $avg);
                        ?>
                    </div>
                    <p><?= $stats_q['total'] ?> feedback total</p>
                </div>
                <div class="stat-bars">
                    <?php
                    for ($b = 5; $b >= 1; $b--) {
                        $jumlah = (int) $stats_q["bintang$b"];
                        $pct = $stats_q['total'] > 0 ? ($jumlah / $stats_q['total'] * 100) : 0;
                        echo "<div class='bar-row'>
                    <span class='bar-label'>$b bintang</span>
                    <div class='bar-track'><div class='bar-fill' style='width:{$pct}%'></div></div>
                    <span class='bar-count'>$jumlah</span>
                </div>";
                    }
                    ?>
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
                        <option value="<?= $i ?>" <?= $filter_rating == $i ? 'selected' : '' ?>><?= $i ?> Bintang</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label>Cari Komentar</label>
                <input type="text" name="topik" placeholder="Kata kunci..."
                    value="<?= htmlspecialchars($filter_topik) ?>">
            </div>
            <button type="submit" class="btn-filter">Terapkan</button>
            <a href="feedback_eo.php"><button type="button" class="btn-reset">Reset</button></a>
        </form>

        <!-- LIST -->
        <p class="total-info">Menampilkan <strong><?= count($feedbacks) ?></strong> feedback</p>

        <?php if (empty($feedbacks)): ?>
            <div class="empty-state">
                <h3>Belum Ada Feedback</h3>
                <p>Belum ada feedback yang masuk untuk seminar Anda, atau filter tidak menemukan hasil.</p>
            </div>
        <?php else: ?>
            <div class="feedback-list">
                <?php foreach ($feedbacks as $i => $fb): ?>
                    <div class="feedback-card" data-rating="<?= $fb['rating'] ?>" style="animation-delay: <?= $i * 0.05 ?>s">
                        <div class="card-top">
                            <div class="peserta-info">
                                <h4><?= htmlspecialchars($fb['nama_peserta']) ?></h4>
                                <p>Narasumber: <?= htmlspecialchars($fb['nama_narasumber'] ?? '-') ?></p>
                            </div>
                            <div class="rating-stars">
                                <?= str_repeat('★', $fb['rating']) . str_repeat('☆', 5 - $fb['rating']) ?>
                            </div>
                        </div>
                        <span class="seminar-tag"><?= htmlspecialchars($fb['judul_seminar']) ?></span>
                        <div class="komentar-text"><?= nl2br(htmlspecialchars($fb['komentar'])) ?></div>
                        <div class="card-foot">
                            <span class="tanggal"><?= date('d M Y, H:i', strtotime($fb['tanggal_feedback'])) ?></span>
                            <span
                                class="badge-status <?= $fb['status_validasi'] == 'valid' ? 'badge-valid' : 'badge-pending' ?>">
                                <?= ucfirst($fb['status_validasi']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>