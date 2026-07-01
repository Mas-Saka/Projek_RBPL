<?php

include "config.php";

// !! Samakan dengan Server Key di proses_daftar.php !!
define('MIDTRANS_SERVER_KEY', '');

// ─── 1. Baca body JSON dari Midtrans ─────────────────────────
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
file_put_contents(
    'callback_log.txt',
    date('Y-m-d H:i:s') . "\n" . $raw . "\n\n",
    FILE_APPEND
);

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
$order_id = $data['order_id'];
$transaction_status = $data['transaction_status'];  // settlement, pending, expire, cancel, deny
$fraud_status = $data['fraud_status'] ?? null;
$payment_type = $data['payment_type'] ?? null;
$transaction_id = $data['transaction_id'] ?? null;

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
$raw_response = $raw;
$upd->bind_param("sssss", $status_payment, $payment_type, $transaction_id, $raw_response, $order_id);

if (!$upd->execute()) {
    error_log("[Midtrans] Gagal update tabel pembayaran: " . $upd->error);
}
$upd->close();


// ─── 5. Logika Pendaftaran & Mengubah Status Seminar ─────────
// Kita tangkap status 'settlement' (E-Wallet/Gopay/Transfer) dan 'capture' (Kartu Kredit)
if ($status_payment === 'settlement' || $status_payment === 'capture') {

    error_log("[Midtrans] Pembayaran SUKSES untuk Order ID: " . $order_id);

    // 1. Ambil seminar_id dan peserta_id dari tabel pembayaran
    $get_pay = $conn->prepare("SELECT seminar_id, peserta_id FROM pembayaran WHERE order_id = ? LIMIT 1");
    $get_pay->bind_param("s", $order_id);
    $get_pay->execute();
    $res_pay = $get_pay->get_result()->fetch_assoc();
    $get_pay->close();

    if ($res_pay) {
        $seminar_id = $res_pay['seminar_id'];
        $peserta_id = $res_pay['peserta_id'];
        error_log("[Midtrans] Data ditemukan - Seminar ID: $seminar_id, Peserta ID: $peserta_id");

        // 2. Pastikan belum terdaftar (mencegah duplikat data)
        $cek_daftar = $conn->prepare("SELECT * FROM pendaftaran WHERE seminar_id = ? AND peserta_id = ? LIMIT 1");
        $cek_daftar->bind_param("ii", $seminar_id, $peserta_id);
        $cek_daftar->execute();
        $ada_daftar = $cek_daftar->get_result()->num_rows;
        $cek_daftar->close();

        if ($ada_daftar == 0) {
            // 3. Masukkan ke tabel pendaftaran (Sesuaikan nama kolom dengan databasemu!)
            $ins_daftar = $conn->prepare("INSERT INTO pendaftaran (seminar_id, peserta_id, status) VALUES (?, ?, 'diterima')");
            if ($ins_daftar) {
                $ins_daftar->bind_param("ii", $seminar_id, $peserta_id);
                if ($ins_daftar->execute()) {
                    error_log("[Midtrans] BERHASIL input ke tabel pendaftaran.");
                } else {
                    error_log("[Midtrans] GAGAL input tabel pendaftaran: " . $ins_daftar->error);
                }
                $ins_daftar->close();
            } else {
                error_log("[Midtrans] SQL Prepare Gagal untuk pendaftaran: " . $conn->error);
            }
        } else {
            error_log("[Midtrans] Peserta sudah terdaftar sebelumnya, query insert dilewati.");
        }

        // 4. Ubah status seminar jadi 'draft' agar hilang dari daftar seminar aktif
        $upd_seminar = $conn->prepare("UPDATE seminar SET status = 'draft' WHERE seminar_id = ?");
        if ($upd_seminar) {
            $upd_seminar->bind_param("i", $seminar_id);
            if ($upd_seminar->execute()) {
                error_log("[Midtrans] BERHASIL mengubah status seminar $seminar_id menjadi draft.");
            } else {
                error_log("[Midtrans] GAGAL mengubah status seminar: " . $upd_seminar->error);
            }
            $upd_seminar->close();
        } else {
            error_log("[Midtrans] SQL Prepare Gagal untuk update seminar: " . $conn->error);
        }

    } else {
        error_log("[Midtrans] Order ID $order_id tidak ditemukan di database!");
    }
}

// ─── 6. Balas ke Midtrans ───────────────────────────────────
http_response_code(200);
echo json_encode(['status' => 'ok']);
exit;