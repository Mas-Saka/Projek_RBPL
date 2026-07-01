<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

$peserta_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'] ?? 'peserta';
$seminar_id = (int) ($_GET['id'] ?? 0);

if (!$seminar_id) {
    header("Location: dashboardpeserta.php");
    exit;
}

// Pastikan seminar status = selesai dan peserta sudah terdaftar
$cek_seminar = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.kategori,
            s.gambar, u.nama AS narasumber, u.id AS narasumber_id
     FROM seminar s
     LEFT JOIN users u ON s.narasumber_id = u.id
     JOIN pendaftaran p ON p.seminar_id = s.seminar_id
     WHERE s.seminar_id = $seminar_id
       AND s.status = 'selesai'
       AND p.peserta_id = $peserta_id
     LIMIT 1"
));

if (!$cek_seminar) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Feedback hanya bisa diberikan untuk seminar yang sudah selesai dan Anda ikuti.'];
    header("Location: dashboardpeserta.php");
    exit;
}

// Cek apakah sudah pernah feedback
$sudah_feedback = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT id FROM feedback WHERE seminar_id = $seminar_id AND peserta_id = $peserta_id LIMIT 1"
));

// Proses submit
$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$sudah_feedback) {
    $rating = (int) ($_POST['rating'] ?? 0);
    $komentar = trim(mysqli_real_escape_string($conn, $_POST['komentar'] ?? ''));
    $topik = mysqli_real_escape_string($conn, $_POST['topik'] ?? 'seminar');

    if ($rating < 1 || $rating > 5) {
        $pesan_error = 'Silakan pilih rating terlebih dahulu.';
    } elseif (strlen($komentar) < 10) {
        $pesan_error = 'Komentar minimal 10 karakter.';
    } else {
        mysqli_query(
            $conn,
            "INSERT INTO feedback (seminar_id, peserta_id, rating, komentar, topik, status_validasi)
             VALUES ($seminar_id, $peserta_id, $rating, '$komentar', '$topik', 'pending')"
        );
        $sudah_feedback = true;
        $pesan_sukses = 'Terima kasih! Feedback Anda berhasil dikirim.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Feedback — <?= htmlspecialchars($cek_seminar['judul_seminar']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        <style>
        html,
        body,
        h2,
        h3,
        p,
        textarea,
        button,
        label,
        header,
        main,
        div,
        span {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7f6;
            color: #333333;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: #3498db;
        }

        /* ── HEADER ── */
        header {
            background: #ffffff;
            height: 64px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        }

        .header-inner {
            width: 90%;
            max-width: 1100px;
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

        /* ── MAIN ── */
        main {
            width: 90%;
            max-width: 720px;
            margin: 44px auto 80px;
        }

        /* ── SEMINAR INFO CARD ── */
        .seminar-info {
            background: #ffffff;
            border-radius: 14px;
            padding: 28px 32px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, .10);
            display: flex;
            gap: 22px;
            align-items: flex-start;
            margin-bottom: 28px;
            border-left: 4px solid #3498db;
        }

        .seminar-thumb {
            width: 90px;
            height: 70px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            background: #f4f7f6;
            display: block;
        }

        .seminar-meta h2 {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .seminar-meta p {
            font-size: 13px;
            color: #888888;
            margin-bottom: 2px;
        }

        .badge-selesai {
            display: inline-block;
            margin-top: 8px;
            background: #eafaf1;
            color: #27ae60;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid #a9dfbf;
        }

        /* ── ALERT ── */
        .alert {
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 14px;
            margin-bottom: 22px;
            font-weight: 500;
        }

        .alert-success {
            background: #eafaf1;
            color: #1e8449;
            border: 1px solid #a9dfbf;
        }

        .alert-error {
            background: #fdedec;
            color: #922b21;
            border: 1px solid #f1948a;
        }

        /* ── SUDAH FEEDBACK ── */
        .done-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 52px 32px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(44, 62, 80, .10);
        }

        .done-card .check-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #eafaf1;
            border: 2px solid #a9dfbf;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 28px;
        }

        .done-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .done-card p {
            font-size: 14px;
            color: #888888;
        }

        /* ── FORM CARD ── */
        .form-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 36px 40px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, .10);
        }

        .form-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .form-card .subtitle {
            font-size: 13.5px;
            color: #888888;
            margin-bottom: 28px;
        }

        /* Topik tabs */
        .topik-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 28px;
        }

        .topik-tab {
            flex: 1;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            padding: 13px 10px;
            cursor: pointer;
            text-align: center;
            font-size: 13.5px;
            font-weight: 500;
            color: #888888;
            background: #ffffff;
            transition: all .22s;
            user-select: none;
            position: relative;
        }

        .topik-tab.active {
            border-color: #3498db;
            color: #3498db;
            background: #eaf4fc;
        }

        .topik-tab input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .section-label {
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── STAR RATING ── */
        .star-wrap {
            display: flex;
            flex-direction: row-reverse;
            gap: 6px;
            margin-bottom: 28px;
            justify-content: flex-end;
        }

        .star-wrap input {
            display: none;
        }

        .star-wrap label {
            font-size: 38px;
            color: #ddd;
            cursor: pointer;
            transition: color .18s, transform .18s;
            line-height: 1;
        }

        .star-wrap input:checked~label,
        .star-wrap label:hover,
        .star-wrap label:hover~label {
            color: #f39c12;
        }

        .star-wrap label:hover {
            transform: scale(1.15);
        }

        .rating-desc {
            font-size: 13px;
            color: #888888;
            margin-top: -22px;
            margin-bottom: 24px;
            min-height: 18px;
            transition: opacity .2s;
        }

        /* ── TEXTAREA ── */
        .field {
            margin-bottom: 24px;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .field textarea {
            width: 100%;
            min-height: 130px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            padding: 14px 16px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #333333;
            resize: vertical;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            background: #ffffff;
        }

        .field textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, .12);
        }

        .char-count {
            font-size: 12px;
            color: #888888;
            text-align: right;
            margin-top: 5px;
        }

        .char-count.warn {
            color: #e74c3c;
        }

        /* ── SUBMIT ── */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #3498db;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .15s, box-shadow .2s;
            margin-top: 4px;
        }

        .btn-submit:hover {
            background: #2980b9;
            box-shadow: 0 4px 16px rgba(52, 152, 219, .3);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            background: #b0c4d8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 600px) {
            main {
                width: 95%;
                margin-top: 28px;
            }

            .form-card {
                padding: 24px 20px;
            }

            .seminar-info {
                flex-direction: column;
                gap: 14px;
                padding: 20px;
            }

            .seminar-thumb {
                width: 100%;
                height: 140px;
            }

            .star-wrap label {
                font-size: 32px;
            }

            .topik-tabs {
                flex-direction: column;
            }
        }
    s
    </style>
</head>

<body>

    <header>
        <div class="header-inner">
            <a href="index.php" class="logo">SeminarOnline</a>
            <a href="dashboardpeserta.php" class="back-link">Kembali ke Dashboard</a>
        </div>
    </header>

    <main>

        <!-- Info seminar -->
        <div class="seminar-info">
            <?php if (!empty($cek_seminar['gambar'])): ?>
                <img class="seminar-thumb" src="upload/<?= htmlspecialchars($cek_seminar['gambar']) ?>" alt="">
            <?php else: ?>
                <img class="seminar-thumb" src="https://via.placeholder.com/90x70?text=Seminar" alt="">
            <?php endif; ?>
            <div class="seminar-meta">
                <h2><?= htmlspecialchars($cek_seminar['judul_seminar']) ?></h2>
                <p>Narasumber: <?= htmlspecialchars($cek_seminar['narasumber'] ?? '-') ?></p>
                <p>Tanggal: <?= date('d F Y', strtotime($cek_seminar['tanggal'])) ?></p>
                <?php if ($cek_seminar['kategori']): ?>
                    <p>Kategori: <?= htmlspecialchars($cek_seminar['kategori']) ?></p>
                <?php endif; ?>
                <span class="badge-selesai">Selesai</span>
            </div>
        </div>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success"><?= $pesan_sukses ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="alert alert-error"><?= $pesan_error ?></div>
        <?php endif; ?>

        <?php if ($sudah_feedback && !$pesan_sukses): ?>
            <!-- Sudah pernah feedback -->
            <div class="done-card">
                <div class="check-circle">✓</div>
                <h3>Feedback Sudah Dikirim</h3>
                <p>Anda sudah memberikan feedback untuk seminar ini.<br>Terima kasih atas partisipasi Anda.</p>
            </div>

        <?php elseif ($pesan_sukses): ?>
            <div class="done-card">
                <div class="check-circle">✓</div>
                <h3>Feedback Terkirim!</h3>
                <p>Masukan Anda sangat berarti untuk meningkatkan kualitas seminar ke depannya.</p>
            </div>

        <?php else: ?>
            <!-- Form feedback -->
            <div class="form-card">
                <h3>Berikan Feedback</h3>
                <p class="subtitle">Ceritakan pengalaman Anda mengikuti seminar ini. Komentar Anda akan dibaca oleh EO dan
                    narasumber.</p>

                <form method="POST" id="feedbackForm">

                    <!-- Topik -->
                    <p class="section-label">Feedback untuk</p>
                    <div class="topik-tabs" role="group" aria-label="Pilih topik feedback">
                        <label class="topik-tab active" id="tab-seminar">
                            <input type="radio" name="topik" value="seminar" checked onchange="setTab(this)">
                            Seminar Secara Umum
                        </label>
                        <label class="topik-tab" id="tab-narasumber">
                            <input type="radio" name="topik" value="narasumber" onchange="setTab(this)">
                            Narasumber &amp; Materi
                        </label>
                        <label class="topik-tab" id="tab-keduanya">
                            <input type="radio" name="topik" value="keduanya" onchange="setTab(this)">
                            Keduanya
                        </label>
                    </div>

                    <!-- Rating bintang -->
                    <p class="section-label">Rating</p>
                    <div class="star-wrap" role="radiogroup" aria-label="Rating bintang">
                        <input type="radio" name="rating" id="s5" value="5"><label for="s5" title="Luar biasa">★</label>
                        <input type="radio" name="rating" id="s4" value="4"><label for="s4" title="Bagus">★</label>
                        <input type="radio" name="rating" id="s3" value="3"><label for="s3" title="Cukup">★</label>
                        <input type="radio" name="rating" id="s2" value="2"><label for="s2" title="Kurang">★</label>
                        <input type="radio" name="rating" id="s1" value="1"><label for="s1" title="Mengecewakan">★</label>
                    </div>
                    <p class="rating-desc" id="ratingDesc">Klik bintang untuk memberi penilaian</p>

                    <!-- Komentar -->
                    <div class="field">
                        <label for="komentar">Komentar</label>
                        <textarea id="komentar" name="komentar"
                            placeholder="Tulis pengalaman, saran, atau masukan Anda di sini..." maxlength="1000"
                            oninput="updateChar()"></textarea>
                        <p class="char-count" id="charCount">0 / 1000 karakter</p>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn" disabled>Kirim Feedback</button>
                </form>
            </div>
        <?php endif; ?>

    </main>

    <script>
        const ratingLabels = {
            1: 'Mengecewakan — jauh dari harapan',
            2: 'Kurang — masih banyak yang perlu diperbaiki',
            3: 'Cukup — lumayan, ada beberapa hal yang bisa ditingkatkan',
            4: 'Bagus — secara keseluruhan memuaskan',
            5: 'Luar biasa — melebihi ekspektasi!'
        };

        document.querySelectorAll('.star-wrap input').forEach(input => {
            input.addEventListener('change', function () {
                document.getElementById('ratingDesc').textContent = ratingLabels[this.value];
                checkForm();
            });
        });

        function updateChar() {
            const txt = document.getElementById('komentar');
            const cc = document.getElementById('charCount');
            const len = txt.value.length;
            cc.textContent = len + ' / 1000 karakter';
            cc.classList.toggle('warn', len > 900);
            checkForm();
        }

        function checkForm() {
            const rated = document.querySelector('.star-wrap input:checked');
            const komentar = document.getElementById('komentar').value.trim();
            const btn = document.getElementById('submitBtn');
            btn.disabled = !(rated && komentar.length >= 10);
        }

        function setTab(input) {
            document.querySelectorAll('.topik-tab').forEach(t => t.classList.remove('active'));
            input.closest('.topik-tab').classList.add('active');
        }

        document.getElementById('feedbackForm')?.addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Mengirim...';
        });
    </script>
</body>

</html>