<?php
session_start();
include "config.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'] ?? 'Peserta';

if (!isset($_GET['id'])) {
    die("ID seminar tidak ditemukan");
}

$seminar_id = (int) $_GET['id'];

// Ambil data seminar
$stmt = $conn->prepare("SELECT * FROM seminar WHERE seminar_id = ?");
$stmt->bind_param("i", $seminar_id);
$stmt->execute();
$seminar = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$seminar) {
    die("Seminar tidak ditemukan.");
}

// ─── Cek apakah user sudah terdaftar (dari tabel pendaftaran) ─
$cek = $conn->prepare("
    SELECT id FROM pendaftaran 
    WHERE peserta_id = ? AND seminar_id = ? LIMIT 1
");
$cek->bind_param("ii", $user_id, $seminar_id);
$cek->execute();
$hasil_cek = $cek->get_result()->fetch_assoc();
$cek->close();

$cek_bayar = $conn->prepare("
    SELECT status_payment 
    FROM pembayaran 
    WHERE peserta_id = ? AND seminar_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$cek_bayar->bind_param("ii", $user_id, $seminar_id);
$cek_bayar->execute();
$row_bayar = $cek_bayar->get_result()->fetch_assoc();
$cek_bayar->close();

$sudah_daftar =
    ($hasil_cek !== null) ||
    ($row_bayar && $row_bayar['status_payment'] === 'settlement');
$status_daftar = $hasil_cek['status'] ?? null;
$tgl_daftar = $hasil_cek['tanggal_daftar'] ?? null;

// ─── Cek juga di tabel pembayaran (pending belum jadi pendaftaran) ─
$cek_bayar = $conn->prepare(
    "SELECT status_payment FROM pembayaran
     WHERE peserta_id = ? AND seminar_id = ?
     ORDER BY created_at DESC LIMIT 1"
);
$cek_bayar->bind_param("ii", $user_id, $seminar_id);
$cek_bayar->execute();
$row_bayar = $cek_bayar->get_result()->fetch_assoc();
$cek_bayar->close();

$ada_pending = ($row_bayar && $row_bayar['status_payment'] === 'pending');

// Ambil snap_token dari session jika ada
$snap_token = null;
if (isset($_GET['pay']) && $_GET['pay'] == 1 && isset($_SESSION['snap_token'])) {
    $snap_token = $_SESSION['snap_token'];
}

define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-TDAs_kVvjy_snqMU');

// Format data seminar
$biaya_fmt = ($seminar['biaya'] > 0)
    ? 'Rp' . number_format($seminar['biaya'], 0, ',', '.')
    : 'GRATIS';
$tgl_fmt = $seminar['tanggal'] ? date('d M Y', strtotime($seminar['tanggal'])) : '-';
$jam_fmt = date('H:i', strtotime($seminar['jam_mulai'])) . ' – ' . date('H:i', strtotime($seminar['jam_selesai'])) . ' WIB';
$link_zoom = $seminar['link_meeting'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Seminar — SeminarOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script 
src="https://app.sandbox.midtrans.com/snap/snap.js"
data-client-key="<?= htmlspecialchars(MIDTRANS_CLIENT_KEY) ?>">
</script>
    <style>
        /* Menggunakan reset spesifik sebagai pengganti universal selector (*) */
        html,
        body,
        div,
        span,
        h4,
        p,
        a,
        button,
        form {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            color: #334155;
            min-height: 100vh;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 20px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-brand {
            font-weight: 700;
            font-size: 15px;
            color: #2563eb;
        }

        .topbar-user {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 28px 16px 60px;
        }

        .breadcrumb {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .breadcrumb a {
            color: #2563eb;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
        }

        .seminar-header {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }

        .seminar-thumb {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe, #eff6ff);
            flex-shrink: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .seminar-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .seminar-thumb-icon {
            font-size: 14px;
            font-weight: 600;
            color: #60a5fa;
        }

        .seminar-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 5px;
        }

        .seminar-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        .price-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .price-badge {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
        }

        .price-badge.free {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 6px;
        }

        .info-item {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 10px 14px;
        }

        .info-item .lbl {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .info-item .val {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .zoom-section {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1.5px solid #93c5fd;
            border-radius: 12px;
            padding: 16px;
            margin-top: 14px;
        }

        .zoom-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }

        .zoom-section-title {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .zoom-toggle-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background .2s, transform .15s;
        }

        .zoom-toggle-btn:hover {
            background: #3b82f6;
            transform: translateY(-1px);
        }

        .zoom-link-reveal {
            display: none;
            margin-top: 12px;
            background: #fff;
            border-radius: 8px;
            padding: 12px 14px;
            border: 1px solid #bfdbfe;
            animation: slideDown .25s ease;
        }

        .zoom-link-reveal.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .zoom-link-label {
            font-size: 10px;
            color: #60a5fa;
            font-weight: 600;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .zoom-link-url {
            font-size: 12.5px;
            color: #2563eb;
            word-break: break-all;
            font-weight: 500;
            line-height: 1.5;
        }

        .zoom-copy-btn {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 12px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 11px;
            color: #64748b;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background .15s;
        }

        .zoom-copy-btn:hover {
            background: #e2e8f0;
        }

        .zoom-no-link {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 4px;
        }

        .registered-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #dcfce7, #f0fdf4);
            border: 1.5px solid #86efac;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 16px;
            animation: fadeIn .4s ease;
        }

        .registered-icon {
            width: 44px;
            height: 44px;
            background: #22c55e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            flex-shrink: 0;
        }

        .registered-text h4 {
            font-size: 14px;
            font-weight: 700;
            color: #14532d;
            margin-bottom: 2px;
        }

        .registered-text p {
            font-size: 12px;
            color: #166534;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .pending-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fef9c3;
            border: 1.5px solid #fde047;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        .pending-banner p {
            font-size: 13px;
            color: #713f12;
        }

        .btn-pay {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: .2s;
            text-align: center;
            text-decoration: none;
        }

        .btn-pay:hover {
            opacity: .9;
            transform: translateY(-1px);
        }

        .btn-back {
            display: block;
            width: 100%;
            padding: 13px;
            background: #f1f5f9;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: .2s;
        }

        .btn-back:hover {
            background: #e2e8f0;
        }

        .btn-dashboard {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: .2s;
        }

        .btn-dashboard:hover {
            opacity: .9;
            transform: translateY(-1px);
        }

        .action-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .payment-info {
            text-align: center;
            font-size: 11.5px;
            color: #64748b;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .payment-info strong {
            color: #2563eb;
        }

        #loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .55);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 14px;
        }

        #loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid rgba(255, 255, 255, .25);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .75s linear infinite;
        }

        .loading-text {
            color: #fff;
            font-size: 13px;
            font-weight: 500;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(70px);
            background: #0f172a;
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            opacity: 0;
            transition: all .3s;
            z-index: 9998;
            white-space: nowrap;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .seminar-header {
                flex-direction: column;
                gap: 10px;
            }

            .seminar-thumb {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>

<body>

    <div id="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Menyiapkan pembayaran…</div>
    </div>

    <div class="toast" id="toast"></div>

    <div class="topbar">
        <span class="topbar-brand">SeminarOnline</span>
        <div class="topbar-user">
            <div class="topbar-avatar"><?= mb_strtoupper(mb_substr($nama_user, 0, 1)) ?></div>
            <span><?= htmlspecialchars($nama_user) ?></span>
        </div>
    </div>

    <div class="container">

        <div class="breadcrumb">
            <a href="dashboardpeserta.php">Dashboard</a>
            <span>›</span>
            <a href="jelajahi_seminar.php">Seminar Saya</a>
            <span>›</span>
            <span>Detail Seminar</span>
        </div>

        <?php if ($sudah_daftar): ?>
            <div class="registered-banner">
                <div class="registered-icon">OK</div>
                <div class="registered-text">
                    <h4>Kamu Sudah Terdaftar!</h4>
                    <p>
                        Status: <strong><?= ucfirst($status_daftar ?? 'menunggu') ?></strong>
                        <?php if ($tgl_daftar): ?>
                            &bull; Didaftarkan <?= date('d M Y', strtotime($tgl_daftar)) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="seminar-header">
                <div class="seminar-thumb">
                    <?php if (!empty($seminar['gambar'])): ?>
                        <img src="upload/<?= htmlspecialchars($seminar['gambar']) ?>"
                            alt="<?= htmlspecialchars($seminar['judul_seminar']) ?>"
                            onerror="this.parentElement.innerHTML='<div class=\'seminar-thumb-icon\'>IMG</div>'">
                    <?php else: ?>
                        <div class="seminar-thumb-icon">IMG</div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="seminar-title"><?= htmlspecialchars($seminar['judul_seminar']) ?></div>
                    <div class="seminar-desc"><?= htmlspecialchars($seminar['deskripsi'] ?? '') ?></div>
                </div>
            </div>

            <div class="price-row">
                <span class="price-badge <?= $seminar['biaya'] <= 0 ? 'free' : '' ?>">
                    <?= $biaya_fmt ?>
                </span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="lbl">Tanggal</div>
                    <div class="val"><?= $tgl_fmt ?></div>
                </div>
                <div class="info-item">
                    <div class="lbl">Waktu</div>
                    <div class="val"><?= $jam_fmt ?></div>
                </div>
                <div class="info-item">
                    <div class="lbl">Platform</div>
                    <div class="val"><?= htmlspecialchars($seminar['platform'] ?? 'Online') ?></div>
                </div>
                <div class="info-item">
                    <div class="lbl">Kuota Tersisa</div>
                    <div class="val"><?= (int) $seminar['kuota'] ?> orang</div>
                </div>
                <?php if (!empty($seminar['kategori'])): ?>
                    <div class="info-item">
                        <div class="lbl">Kategori</div>
                        <div class="val"><?= htmlspecialchars($seminar['kategori']) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($sudah_daftar): ?>
                <div class="zoom-section">
                    <div class="zoom-section-header" onclick="toggleZoom()">
                        <div class="zoom-section-title">
                            Link Bergabung — <?= htmlspecialchars($seminar['platform'] ?? 'Online') ?>
                        </div>
                        <button class="zoom-toggle-btn" id="zoomBtn">Tampilkan Link</button>
                    </div>
                    <div class="zoom-link-reveal" id="zoomReveal">
                        <?php if (!empty($link_zoom)): ?>
                            <div class="zoom-link-label">URL Meeting</div>
                            <div class="zoom-link-url" id="zoomUrl"><?= htmlspecialchars($link_zoom) ?></div>
                            <button class="zoom-copy-btn" onclick="copyZoom()">Salin Link</button>
                        <?php else: ?>
                            <div class="zoom-no-link">Link meeting belum tersedia. Periksa kembali menjelang hari H.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <?php if ($sudah_daftar): ?>
                <div style="text-align:center; margin-bottom:16px;">
                    <div style="font-size:15px; font-weight:700; color:#14532d; margin-bottom:4px;">
                        Pendaftaran Dikonfirmasi
                    </div>
                    <div style="font-size:12.5px; color:#166534;">
                        Kamu tidak perlu mendaftar lagi. Gunakan link bergabung di atas saat hari seminar tiba.
                    </div>
                </div>
                <div class="action-stack">
                    <a href="dashboardpeserta.php" class="btn-dashboard">Ke Dashboard Saya</a>
                    <a href="jelajahi_seminar.php" class="btn-back">Lihat Seminar Saya</a>
                </div>

            <?php elseif ($snap_token): ?>
                <?php if ($ada_pending): ?>
                    <div class="pending-banner">
                        <p>Pembayaran kamu sedang menunggu konfirmasi. Klik tombol di bawah untuk melanjutkan.</p>
                    </div>
                <?php endif; ?>
                <button class="btn-pay" id="pay-button" onclick="openSnap()">
                    Bayar Sekarang — <?= $biaya_fmt ?>
                </button>
                <div class="payment-info">
                    <span>Aman via</span>
                    <strong>Midtrans</strong> &bull; GoPay · OVO · VA Bank · QRIS
                </div>

            <?php else: ?>
                <div style="margin-bottom:18px;">
                    <div style="font-size:15px; font-weight:700; color:#0f172a; margin-bottom:6px;">
                        Konfirmasi Pendaftaran
                    </div>
                    <div style="font-size:13px; color:#64748b; line-height:1.6;">
                        Klik tombol di bawah untuk melanjutkan ke pembayaran. Pendaftaran kamu akan tercatat otomatis
                        setelah pembayaran berhasil.
                    </div>
                </div>
                <form method="POST" action="proses_daftar.php" id="formDaftar">
                    <input type="hidden" name="seminar_id" value="<?= $seminar_id ?>">
                    <div class="action-stack">
                        <button type="submit" class="btn-pay" onclick="showLoading()">
                            Lanjut ke Pembayaran
                        </button>
                        <a href="javascript:history.back()" class="btn-back">Kembali</a>
                    </div>
                </form>
                <div class="payment-info">
                    <span>Aman via</span>
                    <strong>Midtrans</strong> &bull; GoPay · OVO · VA Bank · QRIS
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php if ($snap_token): ?>
        <script>
            function openSnap() {
                document.getElementById('loading-overlay').classList.remove('active');
                window.snap.pay('<?= $snap_token ?>', {
                    onSuccess: function (result) {
                        window.location.href = 'payment_finish.php'
                            + '?order_id=' + result.order_id
                            + '&status_code=' + result.status_code
                            + '&transaction_status=' + result.transaction_status;
                    },
                    onPending: function (result) {
                        window.location.href = 'payment_finish.php'
                            + '?order_id=' + result.order_id
                            + '&status_code=' + result.status_code
                            + '&transaction_status=pending';
                    },
                    onError: function (result) {
                        window.location.href = 'payment_finish.php'
                            + '?order_id=' + (result.order_id || '')
                            + '&status_code=500'
                            + '&transaction_status=cancel';
                    },
                    onClose: function () {
                        console.log('Snap popup ditutup');
                    }
                });
            }
            window.addEventListener('load', function () {
                <?php if (isset($_GET['pay']) && $_GET['pay'] == 1 && $snap_token): ?>
                    openSnap();
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>

    <script>
        function showLoading() {
            document.getElementById('loading-overlay').classList.add('active');
        }

        function toggleZoom() {
            var reveal = document.getElementById('zoomReveal');
            var btn = document.getElementById('zoomBtn');
            if (!reveal) return;
            var isOpen = reveal.classList.contains('show');
            reveal.classList.toggle('show', !isOpen);
            btn.textContent = isOpen ? 'Tampilkan Link' : 'Sembunyikan Link';
        }

        function copyZoom() {
            var url = document.getElementById('zoomUrl');
            if (!url) return;
            navigator.clipboard.writeText(url.textContent.trim()).then(function () {
                showToast('Sukses! Link berhasil disalin.');
            });
        }

        function showToast(msg) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(function () { t.classList.remove('show'); }, 2500);
        }
    </script>

</body>

</html>