<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "config.php";


$order_id = $_REQUEST['order_id'] ?? ($_SESSION['snap_order'] ?? '');
$transaction_status = 'pending';
$status_code = $_REQUEST['status_code'] ?? '';


unset($_SESSION['snap_token'], $_SESSION['snap_order'], $_SESSION['snap_seminar']);


$pembayaran = null;
if (!empty($order_id)) {
    $query = "SELECT pb.*, s.judul_seminar, s.tanggal, s.jam_mulai 
              FROM pembayaran pb 
              JOIN seminar s ON pb.seminar_id = s.seminar_id 
              WHERE pb.order_id = ? 
              LIMIT 1";

    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
       if ($result && $result->num_rows > 0) {
    $pembayaran = $result->fetch_assoc();

   
    $transaction_status = $_REQUEST['transaction_status'] ?? 'pending';
}
        $stmt->close();
    } else {
        die("Fatal DB Error: " . $conn->error);
    }
}


$is_success = in_array($transaction_status, ['settlement', 'capture']);
// ─── TAMBAHAN LOGIKA KHUSUS INFINITYFREE (UPDATE JIKA SUKSES) ───
if ($is_success && $pembayaran) {
    $seminar_id = (int)$pembayaran['seminar_id'];
    $peserta_id = (int)$pembayaran['peserta_id'];

    // 1. Paksa update status pembayaran di database lokal menjadi settlement
    $upd_pay = $conn->prepare("UPDATE pembayaran SET status_payment = 'settlement', updated_at = NOW() WHERE order_id = ?");
    $upd_pay->bind_param("s", $order_id);
    $upd_pay->execute();
    $upd_pay->close();

    // 2. Cek apakah sudah masuk ke tabel pendaftaran biar tidak duplikat
    $cek_daftar = $conn->prepare("SELECT id FROM pendaftaran WHERE seminar_id = ? AND peserta_id = ? LIMIT 1");
    $cek_daftar->bind_param("ii", $seminar_id, $peserta_id);
    $cek_daftar->execute();
    $ada_daftar = $cek_daftar->get_result()->num_rows;
    $cek_daftar->close();

    if ($ada_daftar == 0) {
        // 3. Masukkan ke tabel pendaftaran supaya muncul di dashboard "Seminar Saya"
        $ins_daftar = $conn->prepare("INSERT INTO pendaftaran (seminar_id, peserta_id, status) VALUES (?, ?, 'diterima')");
        $ins_daftar->bind_param("ii", $seminar_id, $peserta_id);
        $ins_daftar->execute();
        $ins_daftar->close();
    }

    // 4. Ubah status seminar jadi 'draft' agar hilang dari daftar seminar aktif
    $upd_seminar = $conn->prepare("UPDATE seminar SET status = 'draft' WHERE seminar_id = ?");
    $upd_seminar->bind_param("i", $seminar_id);
    $upd_seminar->execute();
    $upd_seminar->close();
}
// ──────────────────────────────────────────────────────────────────
$is_pending = in_array($transaction_status, ['pending']);
$is_failed = in_array($transaction_status, ['cancel', 'deny', 'expire', '202', '300', '400', '500']);


if ($is_success && $pembayaran) {
    
    if (!isset($_SESSION['id'])) {
        $_SESSION['id'] = $pembayaran['peserta_id'];
        $_SESSION['role'] = 'peserta';
        $usr_q = mysqli_query($conn, "SELECT nama FROM users WHERE id = " . $pembayaran['peserta_id']);
        if ($usr = mysqli_fetch_assoc($usr_q)) {
            $_SESSION['nama'] = $usr['nama'];
        }
    }

    
    // -------------------------------------------------------------------------

    $_SESSION['flash'] = "Pembayaran berhasil! Kamu telah terdaftar di: " . $pembayaran['judul_seminar'];
    header("Location: dashboardpeserta.php?registered=" . $pembayaran['seminar_id']);
    exit;
}

// Jika tidak ada session tapi status pending/gagal, paksa ke login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran — SeminarOnline</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f4f7f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 36px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .09);
            border: 1px solid #e8edf2;
        }

        .status-bar {
            width: 56px;
            height: 4px;
            border-radius: 2px;
            margin: 0 auto 28px;
        }

        .bar-pending {
            background: #e67e22;
        }

        .bar-failed {
            background: #e74c3c;
        }

        .title {
            font-family: 'DM Serif Display', serif;
            font-size: 22px;
            font-weight: 400;
            color: #1a2634;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 13.5px;
            color: #7f8c8d;
            line-height: 1.7;
            margin-bottom: 26px;
        }

        .badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            margin-bottom: 22px;
            letter-spacing: .3px;
        }

        .badge-pending {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-failed {
            background: #fde8e8;
            color: #c0392b;
        }

        .detail-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px 18px;
            text-align: left;
            margin-bottom: 24px;
            font-size: 13px;
            color: #2c3e50;
        }

        .detail-box p {
            margin-bottom: 7px;
            display: flex;
            justify-content: space-between;
        }

        .detail-box p:last-child {
            margin-bottom: 0;
        }

        .detail-box span {
            color: #95a5a6;
        }

        .detail-box strong {
            color: #1a2634;
            font-weight: 600;
        }

        .order-id {
            font-size: 11px;
            color: #bdc3c7;
            margin-bottom: 26px;
            word-break: break-all;
        }

        .btn-row {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 11px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13.5px;
            text-decoration: none;
            transition: background .18s, transform .15s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #3498db;
            color: #fff;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #eef2f7;
            color: #2c3e50;
            border: 1px solid #e8edf2;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="status-bar <?= $is_pending ? 'bar-pending' : 'bar-failed' ?>"></div>

        <span class="badge <?= $is_pending ? 'badge-pending' : 'badge-failed' ?>">
            <?= $is_pending ? 'Menunggu Pembayaran' : 'Pembayaran Tidak Berhasil' ?>
        </span>

        <div class="title">
            <?= $is_pending ? 'Pembayaran Sedang Diproses' : 'Transaksi Tidak Berhasil' ?>
        </div>
        <div class="subtitle">
            <?php if ($is_pending): ?>
                Pembayaran kamu sedang diverifikasi. Pendaftaran akan dikonfirmasi otomatis
                setelah pembayaran berhasil — biasanya dalam beberapa menit.
            <?php else: ?>
                Transaksi dibatalkan atau gagal. Silakan coba lagi atau pilih metode pembayaran lain.
            <?php endif; ?>
        </div>

        <?php if ($pembayaran): ?>
            <div class="detail-box">
                <p><span>Seminar</span><strong><?= htmlspecialchars($pembayaran['judul_seminar']) ?></strong></p>
                <p><span>Tanggal</span><strong><?= htmlspecialchars($pembayaran['tanggal'] ?? '-') ?></strong></p>
                <p><span>Total Bayar</span><strong>Rp<?= number_format($pembayaran['gross_amount'], 0, ',', '.') ?></strong>
                </p>
                <?php if ($pembayaran['payment_type']): ?>
                    <p><span>Metode</span><strong><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $pembayaran['payment_type']))) ?></strong>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="order-id">Order ID: <?= htmlspecialchars($order_id) ?></div>

        <div class="btn-row">
            <a href="dashboardpeserta.php" class="btn btn-primary">Ke Dashboard</a>
            <?php if ($is_failed && isset($pembayaran['seminar_id'])): ?>
                <a href="daftar.php?id=<?= $pembayaran['seminar_id'] ?>" class="btn btn-secondary">Coba Lagi</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>