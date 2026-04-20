<?php

include "config.php";

// !! Samakan dengan Server Key di proses_daftar.php !!
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-mOSYS3G2ncoppKBixcGQxBr2');

// ─── 1. Baca body JSON dari Midtrans ─────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['order_id'])) {
    http_response_code(400);
    exit("Bad Request");
}

// ─── 2. Verifikasi signature key ─────────────────────────────
// Formula: SHA512(order_id + status_code + gross_amount + server_key)
$signature_string = $data['order_id']
    . $data['status_code']
    . $data['gross_amount']
    . MIDTRANS_SERVER_KEY;

$expected_signature = hash('sha512', $signature_string);

if ($data['signature_key'] !== $expected_signature) {
    http_response_code(403);
    error_log("Midtrans: signature tidak cocok untuk order " . $data['order_id']);
    exit("Forbidden");
}

// ─── 3. Petakan status Midtrans → status lokal ───────────────
$order_id          = $data['order_id'];
$transaction_status = $data['transaction_status'];  // settlement, pending, expire, cancel, deny
$fraud_status      = $data['fraud_status'] ?? null;
$payment_type      = $data['payment_type'] ?? null;
$transaction_id    = $data['transaction_id'] ?? null;

// Tentukan status_payment lokal
$status_payment = 'pending'; // default

if ($transaction_status === 'capture') {
    // Kartu kredit
    $status_payment = ($fraud_status === 'accept') ? 'settlement' : 'deny';
} elseif ($transaction_status === 'settlement') {
    $status_payment = 'settlement';
} elseif (in_array($transaction_status, ['cancel', 'deny', 'expire'])) {
    $status_payment = $transaction_status;
} elseif ($transaction_status === 'pending') {
    $status_payment = 'pending';
}

// ─── 4. Update tabel pembayaran ──────────────────────────────
$upd = $conn->prepare(
    "UPDATE pembayaran
     SET status_payment = ?,
         payment_type   = ?,
         transaction_id = ?,
         raw_response   = ?,
         updated_at     = NOW()
     WHERE order_id = ?"
);
$upd->bind_param("sssss", $status_payment, $payment_type, $transaction_id, $raw, $order_id);
$upd->execute();
$upd->close();

// ─── 5. Jika settlement → daftarkan ke tabel pendaftaran ─────
if ($status_payment === 'settlement') {

    // Ambil data dari tabel pembayaran
    $sel = $conn->prepare(
        "SELECT seminar_id, peserta_id FROM pembayaran WHERE order_id = ?"
    );
    $sel->bind_param("s", $order_id);
    $sel->execute();
    $row = $sel->get_result()->fetch_assoc();
    $sel->close();

    if ($row) {
        $seminar_id = $row['seminar_id'];
        $peserta_id = $row['peserta_id'];

        // Cek apakah sudah terdaftar (hindari duplikat jika callback dikirim 2x)
        $cek = $conn->prepare(
            "SELECT id FROM pendaftaran WHERE peserta_id = ? AND seminar_id = ? LIMIT 1"
        );
        $cek->bind_param("ii", $peserta_id, $seminar_id);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows === 0) {
            // Insert ke pendaftaran dengan status diterima langsung
            $ins = $conn->prepare(
                "INSERT INTO pendaftaran
                    (seminar_id, peserta_id, metode_daftar, order_id, status)
                 VALUES (?, ?, 'midtrans', ?, 'diterima')"
            );
            $ins->bind_param("iis", $seminar_id, $peserta_id, $order_id);
            $ins->execute();
            $ins->close();

            // (Opsional) kurangi kuota seminar
            $kuota = $conn->prepare(
                "UPDATE seminar SET kuota = kuota - 1
                 WHERE seminar_id = ? AND kuota > 0"
            );
            $kuota->bind_param("i", $seminar_id);
            $kuota->execute();
            $kuota->close();
        }

        $cek->close();
    }
}

// ─── 6. Balas 200 OK ke Midtrans ─────────────────────────────
http_response_code(200);
echo json_encode(['status' => 'ok']);
exit;
