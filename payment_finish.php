<?php

session_start();
include "config.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Midtrans redirect dengan query string:
// ?order_id=...&status_code=...&transaction_status=...
$order_id           = $_GET['order_id']           ?? ($_SESSION['snap_order'] ?? '');
$transaction_status = $_GET['transaction_status'] ?? 'pending';
$status_code        = $_GET['status_code']        ?? '';

// Bersihkan session snap
unset($_SESSION['snap_token'], $_SESSION['snap_order'], $_SESSION['snap_seminar']);

// Ambil detail pembayaran dari DB
$pembayaran = null;
if ($order_id) {
    $stmt = $conn->prepare(
        "SELECT pb.*, s.judul_seminar, s.tanggal, s.jam_mulai
         FROM pembayaran pb
         JOIN seminar s ON pb.seminar_id = s.seminar_id
         WHERE pb.order_id = ?
         LIMIT 1"
    );
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $pembayaran = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Tentukan tampilan berdasarkan status
$is_success = in_array($transaction_status, ['settlement', 'capture', '200']);
$is_pending = in_array($transaction_status, ['pending', '201']);
$is_failed  = in_array($transaction_status, ['cancel', 'deny', 'expire', '202', '300', '400', '500']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran - SeminarOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            max-width: 480px;
            width: 90%;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        .icon { font-size: 64px; margin-bottom: 16px; }
        .title { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .subtitle { font-size: 14px; color: #64748b; margin-bottom: 24px; line-height: 1.6; }
        .detail-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px;
            text-align: left;
            margin-bottom: 24px;
            font-size: 13px;
            color: #334155;
        }
        .detail-box p { margin-bottom: 6px; }
        .detail-box strong { color: #1e293b; }
        .order-id {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 24px;
            word-break: break-all;
        }
        .btn {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: 0.2s;
        }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #e2e8f0; color: #334155; margin-left: 8px; }
        .btn-secondary:hover { background: #cbd5e1; }

        /* Status colors */
        .success .icon::before { content: '✅'; }
        .pending .icon::before { content: '⏳'; }
        .failed  .icon::before { content: '❌'; }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-pending { background: #fef3c7; color: #d97706; }
        .badge-failed  { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

<div class="card <?php echo $is_success ? 'success' : ($is_pending ? 'pending' : 'failed'); ?>">

    <div class="icon"></div>

    <?php if ($is_success): ?>
        <span class="badge badge-success">Pembayaran Berhasil</span>
        <div class="title">Selamat! Pendaftaran Dikonfirmasi</div>
        <div class="subtitle">
            Pembayaran kamu telah diterima. Kamu sudah resmi terdaftar di seminar ini.
            Cek email untuk informasi lebih lanjut.
        </div>

    <?php elseif ($is_pending): ?>
        <span class="badge badge-pending">Menunggu Pembayaran</span>
        <div class="title">Pembayaran Sedang Diproses</div>
        <div class="subtitle">
            Pembayaran kamu sedang dalam proses verifikasi. Pendaftaran akan dikonfirmasi
            otomatis setelah pembayaran berhasil (biasanya dalam beberapa menit).
        </div>

    <?php else: ?>
        <span class="badge badge-failed">Pembayaran Gagal</span>
        <div class="title">Pembayaran Tidak Berhasil</div>
        <div class="subtitle">
            Transaksi dibatalkan atau gagal. Silakan coba lagi atau pilih metode pembayaran lain.
        </div>
    <?php endif; ?>

    <?php if ($pembayaran): ?>
    <div class="detail-box">
        <p><strong>Seminar:</strong> <?= htmlspecialchars($pembayaran['judul_seminar']) ?></p>
        <p><strong>Tanggal:</strong> <?= htmlspecialchars($pembayaran['tanggal'] ?? '-') ?></p>
        <p><strong>Jam:</strong> <?= htmlspecialchars($pembayaran['jam_mulai'] ?? '-') ?></p>
        <p><strong>Total Bayar:</strong> Rp<?= number_format($pembayaran['gross_amount'], 0, ',', '.') ?></p>
        <?php if ($pembayaran['payment_type']): ?>
        <p><strong>Metode:</strong> <?= htmlspecialchars(strtoupper(str_replace('_', ' ', $pembayaran['payment_type']))) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="order-id">Order ID: <?= htmlspecialchars($order_id) ?></div>

    <div>
        <a href="dashboardpeserta.php" class="btn btn-primary">Ke Dashboard</a>
        <?php if ($is_failed): ?>
        <a href="daftar.php?id=<?= $pembayaran['seminar_id'] ?? '' ?>" class="btn btn-secondary">Coba Lagi</a>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
