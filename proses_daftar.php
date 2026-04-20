<?php
/**
 * proses_daftar.php
 * ------------------------------------------------------------
 * Menggantikan alur upload bukti + OTP.
 * Alur baru:
 *   1. Validasi session & input
 *   2. Cek duplikat pendaftaran
 *   3. Buat order_id unik
 *   4. Simpan record pembayaran (status=pending) ke tabel pembayaran
 *   5. Hit Midtrans Snap API → dapatkan snap_token
 *   6. Update snap_token di tabel pembayaran
 *   7. Redirect ke daftar.php?id=X dengan snap_token di session
 *    (Snap popup akan muncul dari sana)
 * ------------------------------------------------------------
 */

session_start();
include "config.php";

// ─── 0. Guard session ────────────────────────────────────────
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// ─── 1. Ambil & sanitasi input ───────────────────────────────
$user_id    = (int) $_SESSION['id'];
$seminar_id = isset($_POST['seminar_id']) ? (int) $_POST['seminar_id'] : 0;

if ($seminar_id <= 0) {
    die("ID seminar tidak valid.");
}

// ─── 2. Ambil data seminar ───────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM seminar WHERE seminar_id = ?");
$stmt->bind_param("i", $seminar_id);
$stmt->execute();
$seminar = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$seminar) {
    die("Seminar tidak ditemukan.");
}

// ─── 3. Cek duplikat pendaftaran ─────────────────────────────
$cek = $conn->prepare(
    "SELECT id FROM pendaftaran WHERE peserta_id = ? AND seminar_id = ? LIMIT 1"
);
$cek->bind_param("ii", $user_id, $seminar_id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    $cek->close();
    // Arahkan ke dashboard dengan pesan
    $_SESSION['flash'] = "Anda sudah terdaftar di seminar ini.";
    header("Location: dashboardpeserta.php");
    exit;
}
$cek->close();

// ─── 4. Ambil data user ──────────────────────────────────────
$uStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$uStmt->bind_param("i", $user_id);
$uStmt->execute();
$user = $uStmt->get_result()->fetch_assoc();
$uStmt->close();

// ─── 5. Buat order_id unik ───────────────────────────────────
$order_id    = "SEM-{$seminar_id}-{$user_id}-" . time();
$gross_amount = (float) $seminar['biaya'];

// Jika seminar gratis, langsung daftarkan tanpa pembayaran
if ($gross_amount <= 0) {
    $ins = $conn->prepare(
        "INSERT INTO pendaftaran (seminar_id, peserta_id, metode_daftar, status)
         VALUES (?, ?, 'gratis', 'diterima')"
    );
    $ins->bind_param("ii", $seminar_id, $user_id);
    $ins->execute();
    $ins->close();

    $_SESSION['flash'] = "Pendaftaran seminar gratis berhasil!";
    header("Location: dashboardpeserta.php");
    exit;
}

// ─── 6. Simpan record pembayaran (status pending) ────────────
$insP = $conn->prepare(
    "INSERT INTO pembayaran (order_id, seminar_id, peserta_id, gross_amount, status_payment)
     VALUES (?, ?, ?, ?, 'pending')"
);
$insP->bind_param("siid", $order_id, $seminar_id, $user_id, $gross_amount);
$insP->execute();
$insP->close();

// ─── 7. Konfigurasi Midtrans Sandbox ─────────────────────────
// !! Ganti dengan Server Key Sandbox kamu dari dashboard.sandbox.midtrans.com !!
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-mOSYS3G2ncoppKBixcGQxBr2');
define('MIDTRANS_API_URL',    'https://app.sandbox.midtrans.com/snap/v1/transactions');

// ─── 8. Siapkan payload ke Midtrans ──────────────────────────
$payload = [
    'transaction_details' => [
        'order_id'     => $order_id,
        'gross_amount' => (int) $gross_amount,   // Midtrans pakai integer (rupiah)
    ],
    'customer_details' => [
        'first_name' => $user['nama'],
        'email'      => $user['email'],
        'phone'      => $user['no_hp'] ?? '',
    ],
    'item_details' => [
        [
            'id'       => 'SEM-' . $seminar_id,
            'price'    => (int) $gross_amount,
            'quantity' => 1,
            'name'     => mb_substr($seminar['judul_seminar'], 0, 50), // max 50 char
        ]
    ],
    'callbacks' => [
        // URL redirect setelah user selesai di halaman Midtrans
        'finish' => (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                     . "://{$_SERVER['HTTP_HOST']}"
                     . dirname($_SERVER['PHP_SELF'])
                     . "/payment_finish.php",
    ],
];

// ─── 9. Hit Midtrans Snap API (cURL) ─────────────────────────
$ch = curl_init(MIDTRANS_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 10,
]);

$response     = curl_exec($ch);
$http_code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error   = curl_error($ch);
curl_close($ch);

if ($curl_error || $http_code !== 201) {
    // Log error ke file (jangan tampilkan ke user)
    error_log("Midtrans Error [{$http_code}]: {$curl_error} | Response: {$response}");
    die("Terjadi kesalahan saat menghubungi payment gateway. Silakan coba lagi.");
}

$midtrans_res = json_decode($response, true);
$snap_token   = $midtrans_res['token']   ?? null;
$snap_url     = $midtrans_res['redirect_url'] ?? null;

if (!$snap_token) {
    error_log("Midtrans Snap token kosong. Response: " . $response);
    die("Gagal mendapatkan token pembayaran. Hubungi admin.");
}

// ─── 10. Simpan snap_token ke DB ─────────────────────────────
$upd = $conn->prepare(
    "UPDATE pembayaran SET snap_token = ? WHERE order_id = ?"
);
$upd->bind_param("ss", $snap_token, $order_id);
$upd->execute();
$upd->close();

// ─── 11. Lempar ke daftar.php via session ────────────────────
$_SESSION['snap_token']   = $snap_token;
$_SESSION['snap_order']   = $order_id;
$_SESSION['snap_seminar'] = $seminar_id;

header("Location: daftar.php?id={$seminar_id}&pay=1");
exit;