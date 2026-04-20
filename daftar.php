<?php
/**
 * daftar.php  (versi baru — integrasi Midtrans Snap)
 * Halaman ini menampilkan detail seminar + tombol bayar.
 * Jika proses_daftar.php sudah memproses & snap_token tersedia di session,
 * Snap popup akan otomatis terbuka.
 */

session_start();
include "config.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

if (!isset($_GET['id'])) {
    die("ID seminar tidak ditemukan");
}

$seminar_id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM seminar WHERE seminar_id = ?");
$stmt->bind_param("i", $seminar_id);
$stmt->execute();
$seminar = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$seminar) {
    die("Seminar tidak ditemukan.");
}

// Cek apakah user sudah terdaftar
$cek = $conn->prepare(
    "SELECT id FROM pendaftaran WHERE peserta_id = ? AND seminar_id = ? LIMIT 1"
);
$cek->bind_param("ii", $user_id, $seminar_id);
$cek->execute();
$cek->store_result();
$sudah_daftar = ($cek->num_rows > 0);
$cek->close();

// Ambil snap_token dari session jika ada (di-set oleh proses_daftar.php)
$snap_token = null;
if (isset($_GET['pay']) && $_GET['pay'] == 1 && isset($_SESSION['snap_token'])) {
    $snap_token = $_SESSION['snap_token'];
}

// Client Key Sandbox Midtrans (aman untuk ditaruh di frontend)
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-TDAs_kVvjy_snqMU');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Seminar - SeminarOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Midtrans Snap.js Sandbox -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            color: #334155;
        }
        .container {
            max-width: 680px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            padding: 28px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        }
        .card h2 { font-size: 20px; color: #1e293b; margin-bottom: 10px; }
        .card p  { font-size: 14px; color: #64748b; margin-bottom: 6px; }
        .price-badge {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 15px;
            font-weight: 600;
            margin: 8px 0 16px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 14px;
        }
        .info-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
        }
        .info-item span { display: block; color: #94a3b8; font-size: 11px; margin-bottom: 2px; }

        /* Tombol bayar */
        .btn-pay {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 10px;
        }
        .btn-pay:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-pay:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }

        .btn-back {
            display: inline-block;
            margin-top: 14px;
            font-size: 13px;
            color: #64748b;
            text-decoration: none;
        }
        .btn-back:hover { color: #2563eb; }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-warning { background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; }
        .alert-success { background: #dcfce7; color: #14532d; border-left: 4px solid #22c55e; }

        /* Loading overlay */
        #loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        #loading-overlay.active { display: flex; }
        .spinner {
            width: 48px;
            height: 48px;
            border: 5px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media(max-width:480px) {
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Loading overlay -->
<div id="loading-overlay">
    <div class="spinner"></div>
</div>

<div class="container">

    <!-- Breadcrumb -->
    <div style="margin-bottom:20px; font-size:13px; color:#94a3b8;">
        <a href="dashboardpeserta.php" style="color:#2563eb; text-decoration:none;">Dashboard</a>
        &rsaquo; Daftar Seminar
    </div>

    <!-- Info Seminar -->
    <div class="card">
        <h2><?= htmlspecialchars($seminar['judul_seminar']) ?></h2>
        <p style="margin-bottom:10px;"><?= htmlspecialchars($seminar['deskripsi']) ?></p>

        <div class="price-badge">
            <?= ($seminar['biaya'] > 0)
                ? 'Rp' . number_format($seminar['biaya'], 0, ',', '.')
                : 'GRATIS' ?>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span>Tanggal</span>
                <?= htmlspecialchars($seminar['tanggal']) ?>
            </div>
            <div class="info-item">
                <span>Jam</span>
                <?= htmlspecialchars($seminar['jam_mulai']) ?> - <?= htmlspecialchars($seminar['jam_selesai']) ?>
            </div>
            <div class="info-item">
                <span>Platform</span>
                <?= htmlspecialchars($seminar['platform']) ?>
            </div>
            <div class="info-item">
                <span>Kuota Tersisa</span>
                <?= htmlspecialchars($seminar['kuota']) ?> orang
            </div>
        </div>
    </div>

    <!-- Aksi -->
    <div class="card">

        <?php if ($sudah_daftar): ?>
            <div class="alert alert-success">
                ✅ Kamu sudah terdaftar di seminar ini.
            </div>
            <a href="dashboardpeserta.php" class="btn-pay" style="text-align:center; text-decoration:none; display:block;">
                Ke Dashboard
            </a>

        <?php elseif ($snap_token): ?>
            <div class="alert alert-warning">
                ⏳ Pembayaran kamu sedang disiapkan. Klik tombol di bawah untuk melanjutkan.
            </div>
            <button class="btn-pay" id="pay-button" onclick="openSnap()">
                💳 Bayar Sekarang — Rp<?= number_format($seminar['biaya'], 0, ',', '.') ?>
            </button>
            <div style="font-size:12px; color:#94a3b8; text-align:center; margin-top:10px;">
                Didukung oleh Midtrans &bull; GoPay, OVO, VA Bank, QRIS &amp; lebih
            </div>

        <?php else: ?>
            <h3 style="margin-bottom:14px; font-size:16px;">Konfirmasi Pendaftaran</h3>
            <p style="font-size:13px; color:#64748b; margin-bottom:18px;">
                Klik tombol di bawah untuk melanjutkan ke halaman pembayaran.
                Sistem akan membuat pesanan dan membuka gateway pembayaran Midtrans.
            </p>

            <form method="POST" action="proses_daftar.php" id="formDaftar">
                <input type="hidden" name="seminar_id" value="<?= $seminar_id ?>">
                <button type="submit" class="btn-pay" id="submit-btn"
                        onclick="document.getElementById('loading-overlay').classList.add('active'); this.disabled=true; this.textContent='Memproses...';">
                    <?= ($seminar['biaya'] > 0)
                        ? '💳 Lanjut ke Pembayaran'
                        : '✅ Daftar Gratis Sekarang' ?>
                </button>
            </form>
            <div style="font-size:12px; color:#94a3b8; text-align:center; margin-top:10px;">
                Pembayaran aman &bull; Didukung Midtrans
            </div>
        <?php endif; ?>

        <div style="text-align:center;">
            <a href="javascript:history.back()" class="btn-back">&larr; Kembali</a>
        </div>
    </div>

</div>

<?php if ($snap_token): ?>
<script>
    // Auto-buka Snap popup jika snap_token tersedia
    function openSnap() {
        window.snap.pay('<?= $snap_token ?>', {
            onSuccess: function(result) {
                // Redirect ke finish page dengan detail transaksi
                window.location.href = 'payment_finish.php'
                    + '?order_id='            + result.order_id
                    + '&status_code='         + result.status_code
                    + '&transaction_status='  + result.transaction_status;
            },
            onPending: function(result) {
                window.location.href = 'payment_finish.php'
                    + '?order_id='            + result.order_id
                    + '&status_code='         + result.status_code
                    + '&transaction_status=pending';
            },
            onError: function(result) {
                window.location.href = 'payment_finish.php'
                    + '?order_id='            + (result.order_id || '')
                    + '&status_code=500'
                    + '&transaction_status=cancel';
            },
            onClose: function() {
                // User menutup popup tanpa selesai bayar — tetap di halaman ini
                console.log('Snap popup ditutup');
            }
        });
    }

    // Auto-open kalau baru redirect dari proses_daftar
    window.addEventListener('load', function() {
        <?php if (isset($_GET['pay']) && $_GET['pay'] == 1): ?>
        openSnap();
        <?php
        // Hapus token dari session setelah di-render
        unset($_SESSION['snap_token']);
        ?>
        <?php endif; ?>
    });
</script>
<?php endif; ?>

</body>
</html>