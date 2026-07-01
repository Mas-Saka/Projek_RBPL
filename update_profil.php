<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profil_peserta.php");
    exit;
}

$peserta_id = (int) $_SESSION['id'];

// Ambil data saat ini
$user_q = mysqli_query($conn, "SELECT * FROM users WHERE id = $peserta_id");
$user = mysqli_fetch_assoc($user_q);

$nama = trim($_POST['nama'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$pw_lama = $_POST['password_lama'] ?? '';
$pw_baru = $_POST['password_baru'] ?? '';

// Validasi nama
if (empty($nama)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Nama tidak boleh kosong.'];
    header("Location: profil_peserta.php");
    exit;
}

// ── Foto Profil ──────────────────────────────────────────────────────────────
$foto_profil = $user['foto_profil'] ?? null;

if (!empty($_FILES['foto_profil']['name'])) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $max_size = 3 * 1024 * 1024; // 3 MB

    $file_type = mime_content_type($_FILES['foto_profil']['tmp_name']);
    $file_size = $_FILES['foto_profil']['size'];

    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Format foto tidak didukung. Gunakan JPG, PNG, atau WebP.'];
        header("Location: profil_peserta.php");
        exit;
    }

    if ($file_size > $max_size) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Ukuran foto maksimal 3 MB.'];
        header("Location: profil_peserta.php");
        exit;
    }

    // Buat folder jika belum ada
    $upload_dir = __DIR__ . '/upload/foto_profil/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Hapus foto lama jika ada
    if (!empty($user['foto_profil']) && file_exists($upload_dir . $user['foto_profil'])) {
        unlink($upload_dir . $user['foto_profil']);
    }

    $ext = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $peserta_id . '_' . time() . '.' . strtolower($ext);
    $dest = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['foto_profil']['tmp_name'], $dest)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Gagal mengunggah foto. Coba lagi.'];
        header("Location: profil_peserta.php");
        exit;
    }

    $foto_profil = $filename;
}

// ── Password (opsional) ──────────────────────────────────────────────────────
$pw_update = '';

if (!empty($pw_lama) || !empty($pw_baru)) {
    if (empty($pw_lama)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Masukkan password lama untuk mengganti password.'];
        header("Location: profil_peserta.php");
        exit;
    }

    if (strlen($pw_baru) < 6) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Password baru minimal 6 karakter.'];
        header("Location: profil_peserta.php");
        exit;
    }

    // Cek password lama (support plain text & hashed)
    $pw_db = $user['password'];
    $match = false;

    if (password_verify($pw_lama, $pw_db)) {
        $match = true;
    } elseif ($pw_lama === $pw_db) {
        // Plain text lama (legacy)
        $match = true;
    }

    if (!$match) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Password lama tidak sesuai.'];
        header("Location: profil_peserta.php");
        exit;
    }

    
    $pw_safe = mysqli_real_escape_string($conn, $pw_baru);
    $pw_update = ", password = '$pw_safe'";
}

// ── Build query ──────────────────────────────────────────────────────────────
$nama_safe = mysqli_real_escape_string($conn, $nama);
$no_hp_safe = mysqli_real_escape_string($conn, $no_hp);
$foto_safe = mysqli_real_escape_string($conn, $foto_profil ?? '');

$foto_col = $foto_profil !== null ? ", foto_profil = '$foto_safe'" : '';

$sql = "UPDATE users
        SET nama = '$nama_safe',
            no_hp = '$no_hp_safe'
            $foto_col
            $pw_update
        WHERE id = $peserta_id";

if (mysqli_query($conn, $sql)) {
    // Perbarui session
    $_SESSION['nama'] = $nama;
    if ($foto_profil) {
        $_SESSION['foto_profil'] = $foto_profil;
    }

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profil berhasil diperbarui!'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Terjadi kesalahan saat menyimpan data. ' . mysqli_error($conn)];
}

header("Location: profil_peserta.php");
exit;