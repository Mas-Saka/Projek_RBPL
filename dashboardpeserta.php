<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

$peserta_id = (int) $_SESSION['id'];
$nama_user = $_SESSION['nama'] ?? 'Peserta';
$foto_user = $_SESSION['foto_profil'] ?? null;

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Statistik ──────────────────────────────────────────────────
$total_daftar = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM pendaftaran WHERE peserta_id = $peserta_id"
))['total'];

$seminar_aktif = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM pendaftaran p
     JOIN seminar s ON p.seminar_id = s.seminar_id
     WHERE p.peserta_id = $peserta_id AND s.status = 'aktif'"
))['total'];

$total_feedback = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM feedback WHERE peserta_id = $peserta_id"
))['total'];

// ── Seminar paling dekat (berdasarkan tanggal + jam_mulai) ─────
$seminar_dekat_q = mysqli_query(
    $conn,
    "SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.jam_mulai, s.jam_selesai,
            s.platform, s.link_meeting, s.gambar, s.kategori, s.biaya,
            u.nama AS narasumber,
            p.status AS status_daftar
     FROM pendaftaran p
     JOIN seminar s ON p.seminar_id = s.seminar_id
     LEFT JOIN users u ON s.narasumber_id = u.id
     WHERE p.peserta_id = $peserta_id
       AND s.status = 'aktif'
       AND s.tanggal >= CURDATE()
     ORDER BY STR_TO_DATE(s.tanggal, '%Y-%m-%d') ASC,
              s.jam_mulai ASC
     LIMIT 1"
);
$seminar_dekat = mysqli_fetch_assoc($seminar_dekat_q);

// ── Seminar tersedia (belum didaftar peserta ini, status aktif) ─
$seminar_tersedia_q = mysqli_query(
    $conn,
    "SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.jam_mulai, s.jam_selesai,
            s.platform, s.gambar, s.kategori, s.biaya, s.kuota,
            u.nama AS narasumber
     FROM seminar s
     LEFT JOIN users u ON s.narasumber_id = u.id
     WHERE s.status = 'aktif'
       AND s.seminar_id NOT IN (
           SELECT seminar_id FROM pendaftaran WHERE peserta_id = $peserta_id
       )
     ORDER BY s.tanggal ASC
     LIMIT 6"
);
$seminar_tersedia = [];
while ($r = mysqli_fetch_assoc($seminar_tersedia_q)) {
    $seminar_tersedia[] = $r;
}

// ── Seminar yang sudah didaftar (untuk list section) ───────────
$seminar_saya_q = mysqli_query(
    $conn,
    "SELECT s.seminar_id, s.judul_seminar, s.tanggal, s.jam_mulai, s.jam_selesai,
            s.platform, s.gambar, s.kategori, s.biaya, s.status AS status_seminar,
            s.link_meeting, u.nama AS narasumber,
            p.status AS status_daftar, p.tanggal_daftar
     FROM pendaftaran p
     JOIN seminar s ON p.seminar_id = s.seminar_id
     LEFT JOIN users u ON s.narasumber_id = u.id
     WHERE p.peserta_id = $peserta_id
     ORDER BY p.tanggal_daftar DESC
     LIMIT 8"
);
$seminar_saya = [];
while ($r = mysqli_fetch_assoc($seminar_saya_q)) {
    $seminar_saya[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peserta — SeminarOnline</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7f6;
            color: #1a2634;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Sidebar & Layout */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #2c3e50;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-brand h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .sidebar-brand p {
            color: #7f8c9a;
            font-size: 11px;
            margin-top: 3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
        }

        .nav-label {
            color: #5a6a78;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 12px;
            margin: 12px 0 6px;
            display: block;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #9baab7;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 2px;
            transition: all .2s;
        }

        .sidebar-nav a:hover {
            background: rgba(52, 152, 219, .12);
            color: #3498db;
        }

        .sidebar-nav a.active {
            background: rgba(52, 152, 219, .18);
            color: #3498db;
            font-weight: 600;
        }

        .sidebar-nav a.logout {
            background: rgba(241, 27, 3, 0.26);
            color: #da0d0d;
            margin-top: 8px;
            font-weight: bold;
        }

        .sidebar-nav a.logout:hover {
            background: red;
            color: #e3bcb8;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, .06);
            font-size: 11px;
            color: #5a6a78;
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 90;
            backdrop-filter: blur(1px);
        }

        .overlay.show {
            display: block;
        }

        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 240px;
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 80;
            transition: left .3s;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .burger {
            background: none;
            border: none;
            cursor: pointer;
            display: none;
            flex-direction: column;
            gap: 5px;
            padding: 4px;
        }

        .burger span {
            display: block;
            width: 20px;
            height: 2px;
            background: #64748b;
            border-radius: 2px;
            transition: .2s;
        }

        .topbar-title {
            font-size: 14.5px;
            font-weight: 600;
            color: #1a2634;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 9px;
            background: #f4f7f6;
            border: 1px solid #e8edf2;
            border-radius: 24px;
            padding: 6px 14px 6px 8px;
            font-size: 12.5px;
            font-weight: 500;
            color: #2c3e50;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .main {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
            transition: margin-left .3s;
        }

        .content {
            padding: 28px 26px 48px;
        }

        .flash {
            background: #d5f5e3;
            color: #1e6b3a;
            border: 1px solid #a9dfc0;
            border-left: 4px solid #27ae60;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: flashIn .35s ease;
        }

        @keyframes flashIn {
            from {
                opacity: 0;
                transform: translateY(-6px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .flash-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #1e6b3a;
            font-size: 16px;
            padding: 0 4px;
        }

        .welcome {
            background: linear-gradient(130deg, #1e3c72 0%, #2a5298 60%, #3498db 100%);
            border-radius: 12px;
            padding: 26px 28px;
            color: #fff;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }

        .welcome-text h2 {
            font-family: 'times new roman', serif;
            font-size: 20px;
            font-weight: 400;
            margin-bottom: 5px;
        }

        .welcome-text p {
            font-size: 12.5px;
            color: rgba(255, 255, 255, .72);
        }

        .welcome-badge {
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 10px;
            padding: 10px 18px;
            text-align: center;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .welcome-badge .wb-label {
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .6);
        }

        .welcome-badge .wb-date {
            font-size: 13px;
            font-weight: 600;
            margin-top: 3px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border-left: 3px solid #3498db;
            transition: transform .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.green {
            border-left-color: #27ae60;
        }

        .stat-card.amber {
            border-left-color: #e67e22;
        }

        .stat-num {
            font-size: 28px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 5px;
        }

        .section-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a2634;
        }

        .section-link {
            font-size: 12.5px;
            color: #3498db;
            font-weight: 600;
        }

        .section-link:hover {
            color: #2980b9;
        }

        .nearest-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            margin-bottom: 26px;
            overflow: hidden;
            display: flex;
            border: 1px solid #e8edf2;
        }

        .nearest-thumb {
            width: 220px;
            flex-shrink: 0;
            background: #dfe6e9;
            position: relative;
            overflow: hidden;
        }

        .nearest-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .nearest-ribbon {
            position: absolute;
            top: 12px;
            left: 0;
            background: #3498db;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            padding: 5px 12px 5px 10px;
            border-radius: 0 4px 4px 0;
        }

        .nearest-body {
            padding: 22px 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .nearest-kategori {
            font-size: 10.5px;
            font-weight: 700;
            color: #3498db;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .nearest-title {
            font-size: 17px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.35;
            margin-bottom: 12px;
        }

        .nearest-text {
            font-size: 13px;
            color: #2c3e50;
            margin-bottom: 18px;
            list-style: none;
        }

        .nearest-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 20px;
            margin-bottom: 18px;
        }

        .nearest-meta-item {
            font-size: 12.5px;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nearest-meta-item strong {
            color: #2c3e50;
            font-weight: 600;
        }

        .countdown-row {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        .count-box {
            background: #f4f7f6;
            border: 1px solid #e8edf2;
            border-radius: 8px;
            padding: 8px 12px;
            text-align: center;
            min-width: 54px;
        }

        .count-num {
            font-size: 20px;
            font-weight: 700;
            color: #3498db;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .count-label {
            font-size: 9.5px;
            color: #95a5a6;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .nearest-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .zoom-reveal-wrap {
            margin-top: 2px;
        }

        .btn-zoom-toggle {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background .2s, transform .15s;
        }

        .btn-zoom-toggle:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-zoom-toggle.open {
            background: #2ecc71;
        }

        .zoom-link-box {
            display: none;
            margin-top: 10px;
            background: #eaf4fd;
            border: 1.5px solid #93c5fd;
            border-radius: 10px;
            padding: 12px 14px;
            animation: slideDown .25s ease;
        }

        .zoom-link-box.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-6px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .zlb-label {
            font-size: 10.5px;
            color: #3498db;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .zlb-url {
            font-size: 12px;
            color: #1d4ed8;
            word-break: break-all;
            line-height: 1.5;
        }

        .zlb-copy {
            display: inline-block;
            margin-top: 7px;
            padding: 4px 12px;
            background: #fff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            font-size: 11px;
            color: #3498db;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: background .15s;
        }

        .zlb-copy:hover {
            background: #eff6ff;
        }

        .zlb-no-link {
            font-size: 12px;
            color: #64748b;
        }

        .nearest-empty {
            background: #fff;
            border-radius: 12px;
            padding: 32px 24px;
            text-align: center;
            color: #95a5a6;
            font-size: 13px;
            margin-bottom: 26px;
            border: 1px dashed #e8edf2;
        }

        .nearest-empty strong {
            display: block;
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .seminar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 16px;
            margin-bottom: 26px;
        }

        .scard {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border: 1px solid #e8edf2;
            transition: transform .22s, box-shadow .22s;
            display: flex;
            flex-direction: column;
        }

        .scard:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, .1);
        }

        .scard.removing {
            animation: cardOut .4s forwards;
        }

        @keyframes cardOut {
            to {
                opacity: 0;
                transform: scale(.92)
            }
        }

        .scard-thumb {
            height: 148px;
            background: #dfe6e9;
            position: relative;
            overflow: hidden;
        }

        .scard-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .scard-badge {
            position: absolute;
            top: 9px;
            left: 9px;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-aktif {
            background: #d5f5e3;
            color: #1e8449;
        }

        .scard-body {
            padding: 15px 16px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .scard-kategori {
            font-size: 10px;
            font-weight: 700;
            color: #3498db;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .scard-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.4;
            margin-bottom: 9px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .scard-meta {
            font-size: 12px;
            color: #7f8c8d;
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 12px;
            flex: 1;
        }

        .scard-meta-row {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .scard-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #e8edf2;
            flex-shrink: 0;
        }

        .scard-footer {
            border-top: 1px solid #f0f3f6;
            padding-top: 11px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .scard-price {
            font-size: 13px;
            font-weight: 700;
            color: #2c3e50;
        }

        .scard-price.free {
            color: #27ae60;
        }

        .btn-daftar {
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 7px 16px;
            font-size: 12px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background .18s;
            white-space: nowrap;
        }

        .btn-daftar:hover {
            background: #2980b9;
        }

        .btn-detail-sm {
            background: #f4f7f6;
            color: #2c3e50;
            border: 1px solid #e8edf2;
            border-radius: 7px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background .18s;
            white-space: nowrap;
        }

        .btn-detail-sm:hover {
            background: #e8edf2;
        }

        /* ── CSS TAMBAHAN UNTUK TOMBOL FEEDBACK ── */
        .btn-feedback {
            background: #27ae60;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background .15s;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }

        .btn-feedback:hover {
            background: #219653;
            color: #fff;
        }

        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            border: 1px solid #e8edf2;
            overflow: hidden;
            margin-bottom: 26px;
        }

        .table-head {
            padding: 18px 22px 14px;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-head h3 {
            font-size: 14.5px;
            font-weight: 700;
            color: #1a2634;
        }

        .table-head p {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 2px;
        }

        .table-chip {
            background: #f4f7f6;
            border-radius: 20px;
            padding: 4px 13px;
            font-size: 12px;
            font-weight: 600;
            color: #2c3e50;
        }

        .tbl-wrap {
            overflow-x: auto;
        }

        .tbl-wrap::-webkit-scrollbar {
            height: 4px;
        }

        .tbl-wrap::-webkit-scrollbar-thumb {
            background: #e8edf2;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #f8fafc;
        }

        th {
            padding: 11px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #7f8c8d;
            letter-spacing: .5px;
            text-transform: uppercase;
            border-bottom: 1px solid #e8edf2;
            white-space: nowrap;
        }

        td {
            padding: 13px 16px;
            font-size: 13px;
            color: #2c3e50;
            border-bottom: 1px solid #f4f7f6;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fafcfe;
        }

        .td-title {
            font-weight: 600;
            color: #1a2634;
        }

        .td-sub {
            font-size: 11.5px;
            color: #95a5a6;
            margin-top: 2px;
        }

        .tbl-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-diterima {
            background: #d5f5e3;
            color: #1e8449;
        }

        .badge-menunggu {
            background: #fdebd0;
            color: #a04000;
        }

        .badge-ditolak {
            background: #fde8e8;
            color: #c0392b;
        }

        .badge-selesai {
            background: #e8edf2;
            color: #2c3e50;
        }

        .empty-tbl {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-size: 13px;
        }

        .zoom-td-btn {
            background: none;
            border: 1px solid #93c5fd;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #3498db;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all .15s;
            white-space: nowrap;
        }

        .zoom-td-btn:hover {
            background: #eff6ff;
        }

        .zoom-td-reveal {
            display: none;
            margin-top: 6px;
            font-size: 11.5px;
            color: #1d4ed8;
            word-break: break-all;
            line-height: 1.5;
            background: #eff6ff;
            border-radius: 6px;
            padding: 6px 8px;
            animation: slideDown .2s ease;
        }

        .zoom-td-reveal.show {
            display: block;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1a2634;
            color: #fff;
            border-radius: 10px;
            padding: 13px 20px;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
            z-index: 9999;
            transform: translateY(80px);
            opacity: 0;
            transition: transform .35s, opacity .35s;
            max-width: 320px;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        @media (max-width:960px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .topbar {
                left: 0;
            }

            .main {
                margin-left: 0;
            }

            .burger {
                display: flex;
            }

            .nearest-card {
                flex-direction: column;
            }

            .nearest-thumb {
                width: 100%;
                height: 180px;
            }
        }

        @media (max-width:720px) {
            .stats-row {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }

            .stat-card {
                padding: 14px;
            }

            .stat-num {
                font-size: 22px;
            }

            .seminar-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 18px 14px 40px;
            }

            .welcome {
                padding: 20px 18px;
            }

            .welcome-badge {
                display: none;
            }
        }

        @media (max-width:480px) {
            .stats-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .countdown-row {
                gap: 7px;
            }

            .count-box {
                min-width: 44px;
                padding: 7px 8px;
            }

            .count-num {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Portal Peserta</p>
        </div>
        <div class="sidebar-nav">
            <span class="nav-label">Menu</span>
            <a href="dashboardpeserta.php" class="active">Dashboard</a>
            <a href="jelajahi_seminar.php">Seminar Saya</a>
            <a href="semua_seminar.php">Jelajahi Seminar</a>
            <a href="materi_seminar.php">Materi Seminar</a>
            <a href="index.php">Halaman Utama</a>
            <span class="nav-label" style="margin-top:18px">Akun</span>
            <a href="profil_peserta.php">Profil Saya</a>
            <a href="logout.php" class="logout">Keluar</a>
        </div>
        <div class="sidebar-footer">&copy; 2026 Sistem Manajemen Seminar Online</div>
    </nav>

    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="burger" onclick="toggleSidebar()" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            <span class="topbar-title">Dashboard</span>
        </div>
        <div class="user-pill">
            <div class="user-avatar">
                <?php if ($foto_user): ?>
                    <img src="upload/foto_profil/<?= htmlspecialchars($foto_user) ?>"
                        alt="<?= htmlspecialchars($nama_user) ?>">
                <?php else: ?>
                    <?= mb_strtoupper(mb_substr($nama_user, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <?= htmlspecialchars($nama_user) ?>
        </div>
    </div>

    <div class="main" id="main">
        <div class="content">

            <?php if ($flash): ?>
                <div class="flash" id="flashMsg">
                    <span><?= htmlspecialchars($flash) ?></span>
                    <button class="flash-close" onclick="document.getElementById('flashMsg').remove()">&#x2715;</button>
                </div>
            <?php endif; ?>

            <div class="welcome">
                <div class="welcome-text">
                    <h2>Halo, <?= htmlspecialchars(explode(' ', $nama_user)[0]) ?>!</h2>
                    <p>Selamat datang di dashboard kamu. Pantau seminar dan aktivitas di sini.</p>
                </div>
                <div class="welcome-badge">
                    <div class="wb-label">Hari ini</div>
                    <div class="wb-date" id="todayDate">—</div>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-num"><?= $total_daftar ?></div>
                    <div class="stat-label">Total Terdaftar</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-num"><?= $seminar_aktif ?></div>
                    <div class="stat-label">Seminar Aktif</div>
                </div>
                <div class="stat-card amber">
                    <div class="stat-num"><?= $total_feedback ?></div>
                    <div class="stat-label">Feedback Diberikan</div>
                </div>
            </div>

            <div class="section-row">
                <span class="section-title">Seminar Terdekat</span>
            </div>

            <?php if ($seminar_dekat): ?>
                <?php
                $sd = $seminar_dekat;
                $tgl_fmt = $sd['tanggal'] ? date('d M Y', strtotime($sd['tanggal'])) : '-';
                $jam_fmt = ($sd['jam_mulai'] && $sd['jam_selesai'])
                    ? date('H:i', strtotime($sd['jam_mulai'])) . ' – ' . date('H:i', strtotime($sd['jam_selesai'])) . ' WIB'
                    : '-';
                $gambar_sd = !empty($sd['gambar']) ? "upload/" . $sd['gambar'] : 'https://via.placeholder.com/440x220/dfe6e9/95a5a6?text=Seminar';
                $target_ts = 0;
                if ($sd['tanggal'] && $sd['jam_mulai']) {
                    $target_ts = strtotime($sd['tanggal'] . ' ' . $sd['jam_mulai']);
                }
                $link_sd = trim($sd['link_meeting'] ?? '');
                ?>
                <div class="nearest-card">
                    <div class="nearest-thumb">
                        <img src="<?= $gambar_sd ?>" alt="<?= htmlspecialchars($sd['judul_seminar']) ?>"
                            onerror="this.src='https://via.placeholder.com/440x220/dfe6e9/95a5a6?text=Seminar'">
                        <div class="nearest-ribbon">Terdekat</div>
                    </div>
                    <div class="nearest-body">
                        <?php if ($sd['kategori']): ?>
                            <div class="nearest-kategori"><?= htmlspecialchars($sd['kategori']) ?></div>
                        <?php endif; ?>
                        <div class="nearest-title"><?= htmlspecialchars($sd['judul_seminar']) ?></div>
                        <div class="nearest-text">Hai <?= htmlspecialchars(explode(' ', $nama_user)[0]) ?>, seminar ini akan
                            dimulai setelah waktu hitung mundur selesai.
                            Pastikan kamu sudah siap untuk bergabung</div>
                        <div class="nearest-meta">
                            <div class="nearest-meta-item"><strong><?= $tgl_fmt ?></strong></div>
                            <div class="nearest-meta-item"><strong><?= $jam_fmt ?></strong></div>
                            <div class="nearest-meta-item">Platform:
                                <strong><?= htmlspecialchars($sd['platform'] ?? 'Online') ?></strong>
                            </div>
                            <div class="nearest-meta-item">Narasumber:
                                <strong><?= htmlspecialchars($sd['narasumber'] ?? '-') ?></strong>
                            </div>
                        </div>

                        <div class="countdown-row" id="cdRow">
                            <div class="count-box">
                                <div class="count-num" id="cdD">--</div>
                                <div class="count-label">Hari</div>
                            </div>
                            <div class="count-box">
                                <div class="count-num" id="cdH">--</div>
                                <div class="count-label">Jam</div>
                            </div>
                            <div class="count-box">
                                <div class="count-num" id="cdM">--</div>
                                <div class="count-label">Menit</div>
                            </div>
                            <div class="count-box">
                                <div class="count-num" id="cdS">--</div>
                                <div class="count-label">Detik</div>
                            </div>
                        </div>

                        <div class="nearest-actions">
                            <div class="zoom-reveal-wrap">
                                <button class="btn-zoom-toggle" id="nearestZoomBtn" onclick="toggleNearestZoom()">
                                    Bergabung Sekarang
                                </button>
                                <div class="zoom-link-box" id="nearestZoomBox">
                                    <?php if (!empty($link_sd)): ?>
                                        <div class="zlb-label">Link Meeting —
                                            <?= htmlspecialchars($sd['platform'] ?? 'Online') ?>
                                        </div>
                                        <div class="zlb-url" id="nearestZoomUrl"><?= htmlspecialchars($link_sd) ?></div>
                                        <button class="zlb-copy" onclick="copyText('nearestZoomUrl')">Salin Link</button>
                                    <?php else: ?>
                                        <div class="zlb-no-link">Link belum tersedia. Cek lagi menjelang hari seminar.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="jelajahi_seminar.php" class="btn-detail-sm">Lihat Detail</a>
                        </div>
                    </div>
                </div>

                <script>
                    (function () {
                        var target = <?= $target_ts ? ($target_ts * 1000) : 0 ?>;
                        function pad(n) { return String(n).padStart(2, '0'); }
                        function tick() {
                            var diff = target - Date.now();
                            if (diff <= 0) {
                                document.getElementById('cdRow').innerHTML =
                                    '<div style="font-size:13px;color:#27ae60;font-weight:700;padding:6px 0">Seminar sedang berlangsung!</div>';
                                return;
                            }
                            var d = Math.floor(diff / 86400000);
                            var h = Math.floor((diff % 86400000) / 3600000);
                            var m = Math.floor((diff % 3600000) / 60000);
                            var s = Math.floor((diff % 60000) / 1000);
                            document.getElementById('cdD').textContent = pad(d);
                            document.getElementById('cdH').textContent = pad(h);
                            document.getElementById('cdM').textContent = pad(m);
                            document.getElementById('cdS').textContent = pad(s);
                        }
                        if (target > 0) { tick(); setInterval(tick, 1000); }
                        else { document.getElementById('cdRow').style.display = 'none'; }
                    })();
                </script>

            <?php else: ?>
                <div class="nearest-empty">
                    <strong>Tidak ada seminar terdekat</strong>
                    Kamu belum mendaftar ke seminar aktif yang akan datang.
                    <br><br>
                    <a href="semua_seminar.php" style="color:#3498db;font-weight:600;">Jelajahi seminar tersedia</a>
                </div>
            <?php endif; ?>

            <?php if (count($seminar_tersedia) > 0): ?>
                <div class="section-row">
                    <span class="section-title">Seminar Tersedia</span>
                    <a href="semua_seminar.php" class="section-link">Lihat semua</a>
                </div>
                <div class="seminar-grid" id="gridTersedia">
                    <?php foreach ($seminar_tersedia as $st):
                        $tgl_st = $st['tanggal'] ? date('d M Y', strtotime($st['tanggal'])) : '-';
                        $jam_st = $st['jam_mulai'] ? date('H:i', strtotime($st['jam_mulai'])) : '-';
                        $biaya_st = $st['biaya'] > 0 ? 'Rp ' . number_format($st['biaya'], 0, ',', '.') : 'Gratis';
                        $gambar_st = !empty($st['gambar']) ? "upload/" . $st['gambar'] : 'https://via.placeholder.com/400x180/dfe6e9/95a5a6?text=Seminar';
                        ?>
                        <div class="scard" id="card-tersedia-<?= $st['seminar_id'] ?>">
                            <div class="scard-thumb">
                                <img src="<?= $gambar_st ?>" alt="<?= htmlspecialchars($st['judul_seminar']) ?>"
                                    onerror="this.src='https://via.placeholder.com/400x180/dfe6e9/95a5a6?text=Seminar'">
                                <span class="scard-badge badge-aktif">Aktif</span>
                            </div>
                            <div class="scard-body">
                                <?php if ($st['kategori']): ?>
                                    <div class="scard-kategori"><?= htmlspecialchars($st['kategori']) ?></div>
                                <?php endif; ?>
                                <div class="scard-title"><?= htmlspecialchars($st['judul_seminar']) ?></div>
                                <div class="scard-meta">
                                    <div class="scard-meta-row"><span class="scard-dot"></span><?= $tgl_st ?></div>
                                    <div class="scard-meta-row"><span class="scard-dot"></span><?= $jam_st ?> &middot;
                                        <?= htmlspecialchars($st['platform'] ?? 'Online') ?>
                                    </div>
                                    <div class="scard-meta-row"><span class="scard-dot"></span>Sisa <?= $st['kuota'] ?> tempat
                                    </div>
                                </div>
                                <div class="scard-footer">
                                    <span class="scard-price <?= $st['biaya'] <= 0 ? 'free' : '' ?>"><?= $biaya_st ?></span>
                                    <a href="daftar.php?id=<?= $st['seminar_id'] ?>" class="btn-daftar">Daftar</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="section-row">
                <span class="section-title">Seminar Saya</span>
                <a href="jelajahi_seminar.php" class="section-link">Lihat semua</a>
            </div>

            <?php if (count($seminar_saya) === 0): ?>
                <div class="table-card">
                    <div class="empty-tbl">Kamu belum mendaftar ke seminar apapun.</div>
                </div>
            <?php else: ?>
                <div class="table-card">
                    <div class="table-head">
                        <div>
                            <h3>Daftar Seminar</h3>
                            <p>Seminar yang telah kamu daftarkan</p>
                        </div>
                        <span class="table-chip"><?= count($seminar_saya) ?> seminar</span>
                    </div>
                    <div class="tbl-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Seminar</th>
                                    <th>Tanggal</th>
                                    <th>Platform</th>
                                    <th>Biaya</th>
                                    <th>Status</th>
                                    <th>Aksi / Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seminar_saya as $idx => $sm):
                                    $tgl_sm = $sm['tanggal'] ? date('d M Y', strtotime($sm['tanggal'])) : '-';
                                    $biaya_sm = $sm['biaya'] > 0 ? 'Rp ' . number_format($sm['biaya'], 0, ',', '.') : 'Gratis';
                                    $sd_class = 'badge-menunggu';
                                    $sd_text = 'Menunggu';
                                    if ($sm['status_daftar'] === 'diterima') {
                                        $sd_class = 'badge-diterima';
                                        $sd_text = 'Diterima';
                                    }
                                    if ($sm['status_daftar'] === 'ditolak') {
                                        $sd_class = 'badge-ditolak';
                                        $sd_text = 'Ditolak';
                                    }
                                    // Override jika seminar sudah selesai
                                    if ($sm['status_seminar'] === 'selesai') {
                                        $sd_class = 'badge-selesai';
                                        $sd_text = 'Selesai';
                                    }

                                    $link_sm = trim($sm['link_meeting'] ?? '');
                                    $row_id = "zoom-tbl-" . $idx;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="td-title"><?= htmlspecialchars($sm['judul_seminar']) ?></div>
                                            <?php if ($sm['kategori']): ?>
                                                <div class="td-sub"><?= htmlspecialchars($sm['kategori']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space:nowrap"><?= $tgl_sm ?></td>
                                        <td><?= htmlspecialchars($sm['platform'] ?? '-') ?></td>
                                        <td style="white-space:nowrap"><?= $biaya_sm ?></td>
                                        <td><span class="tbl-badge <?= $sd_class ?>"><?= $sd_text ?></span></td>

                                        <td>
                                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                <?php
                                                // Tampilkan tombol feedback HANYA jika seminar selesai & pendaftaran diterima
                                                if ($sm['status_seminar'] === 'selesai' && $sm['status_daftar'] === 'diterima'):
                                                    ?>
                                                    <a href="feedback.php?id=<?= $sm['seminar_id'] ?>" class="btn-feedback">Beri
                                                        Feedback</a>
                                                <?php endif; ?>

                                                <?php if (!empty($link_sm) && $sm['status_seminar'] !== 'selesai'): ?>
                                                    <div>
                                                        <button class="zoom-td-btn"
                                                            onclick="toggleTblZoom('<?= $row_id ?>', this)">Lihat Link</button>
                                                        <div class="zoom-td-reveal" id="<?= $row_id ?>">
                                                            <?= htmlspecialchars($link_sm) ?>
                                                            <br>
                                                            <button class="zlb-copy" style="margin-top:5px"
                                                                onclick="copyTextDirect('<?= addslashes($link_sm) ?>')">Salin</button>
                                                        </div>
                                                    </div>
                                                <?php elseif ($sm['status_seminar'] !== 'selesai'): ?>
                                                    <span style="font-size:11.5px; color:#95a5a6; margin-top: 4px;">Belum
                                                        tersedia</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        /* Sidebar */
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
        }

        /* Today Date */
        (function () {
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            var d = new Date();
            document.getElementById('todayDate').textContent =
                days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()];
        })();

        /* Toast */
        function showToast(msg, dur) {
            dur = dur || 3000;
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(function () { t.classList.remove('show'); }, dur);
        }

        /* Nearest Zoom Toggle */
        function toggleNearestZoom() {
            var box = document.getElementById('nearestZoomBox');
            var btn = document.getElementById('nearestZoomBtn');
            if (!box) return;
            var open = box.classList.toggle('show');
            btn.textContent = open ? 'Tutup Link' : 'Bergabung Sekarang';
            btn.classList.toggle('open', open);
        }

        /* Table Zoom Toggle */
        function toggleTblZoom(id, btn) {
            var el = document.getElementById(id);
            if (!el) return;
            var open = el.classList.toggle('show');
            btn.textContent = open ? 'Tutup' : 'Lihat Link';
        }

        /* Copy Helpers */
        function copyText(elId) {
            var el = document.getElementById(elId);
            if (!el) return;
            navigator.clipboard.writeText(el.textContent.trim()).then(function () {
                showToast('Sukses! Link berhasil disalin.');
            });
        }
        function copyTextDirect(text) {
            navigator.clipboard.writeText(text).then(function () {
                showToast('Sukses! Link berhasil disalin.');
            });
        }

        /* Auto-remove card after register */
        (function () {
            var params = new URLSearchParams(window.location.search);
            var regId = params.get('registered');
            if (regId) {
                removeCard(regId);
                window.history.replaceState({}, '', 'dashboardpeserta.php');
            }
        })();

        function removeCard(seminarId) {
            var card = document.getElementById('card-tersedia-' + seminarId);
            if (!card) return;
            card.classList.add('removing');
            setTimeout(function () {
                card.remove();
                var grid = document.getElementById('gridTersedia');
                if (grid && grid.children.length === 0) {
                    var hdr = grid.previousElementSibling;
                    if (hdr) hdr.style.display = 'none';
                    grid.style.display = 'none';
                }
            }, 420);
        }

        /* Auto-dismiss flash */
        (function () {
            var f = document.getElementById('flashMsg');
            if (f) setTimeout(function () {
                f.style.opacity = '0'; f.style.transition = 'opacity .4s';
                setTimeout(function () { f.remove(); }, 400);
            }, 5000);
        })();
    </script>
</body>

</html>