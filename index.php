<?php
session_start();
include "config.php";

$acara_seminar = mysqli_query($conn, " SELECT seminar.seminar_id,
        seminar.judul_seminar,
        seminar.tanggal,
        seminar.jam_mulai,
        seminar.gambar,
        seminar.biaya,
        seminar.kuota,
        users.nama AS narasumber FROM seminar
    LEFT JOIN users ON seminar.narasumber_id = users.id
    WHERE seminar.status = 'aktif'
    ORDER BY seminar.seminar_id DESC
    LIMIT 3
");

// Ambil data foto profil terbaru dari DB jika sudah login
if (isset($_SESSION['id'])) {
    $uid = (int) $_SESSION['id'];
    $user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto_profil, nama FROM users WHERE id=$uid"));
    if ($user_data) {
        $_SESSION['foto_profil'] = $user_data['foto_profil'];
        $_SESSION['nama'] = $user_data['nama'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Seminar Online</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f4f7f6;
            color: #333;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        ul {
            list-style: none;
        }

        .container {
            width: 85%;
            margin: auto;
            overflow: visible;
        }

        /* =========================================
           NAVBAR
           ========================================= */
        header {
            background: #fff;
            height: 70px;
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: height .3s, background .3s, box-shadow .3s;
        }

        header.scrolled {
            height: 58px;
            background: #2c3e50;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
        }

        header.scrolled .logo {
            color: #fff;
        }

        header.scrolled .nav-menu a {
            color: rgba(255, 255, 255, .85);
        }

        header.scrolled .nav-menu a:hover {
            color: #3498db;
        }

        header.scrolled .nav-content.active {
            background: #2c3e50;
        }

        header.scrolled .nav-content.active .nav-menu a,
        header.scrolled .nav-content.active .btn-login {
            color: rgba(255, 255, 255, .85);
        }

        header.scrolled .menu-icon {
            color: #fff;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: #3498db;
            transition: color .3s;
            flex-shrink: 0;
        }

        .nav-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-menu {
            display: flex;
            margin-right: 20px;
        }

        .nav-menu li {
            padding: 0 14px;
        }

        .nav-menu a {
            color: #555;
            font-weight: 500;
            font-size: 14.5px;
            transition: color .2s;
        }

        .nav-menu a:hover {
            color: #3498db;
        }

        .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-login {
            padding: 7px 18px;
            border: 2px solid #3498db;
            color: #3498db;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: all .2s;
        }

        .btn-login:hover {
            background: #3498db;
            color: #fff;
        }

        .btn-register {
            padding: 7px 18px;
            background: #3498db;
            color: #fff;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: background .2s;
        }

        .btn-register:hover {
            background: #2980b9;
        }

        /* ── PROFILE & DROPDOWN ── */
        .profile {
            position: relative;
        }

        .profile-img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #3498db;
            transition: border-color .2s;
        }

        .profile-img:hover {
            border-color: #2980b9;
        }

        .profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            min-width: 190px;
            display: none;
            overflow: hidden;
            animation: fadeDropdown .2s ease;
            z-index: 9999;
        }

        .dropdown.show {
            display: block;
        }

        .dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #333;
            font-size: 14px;
            transition: background .15s;
        }

        .dropdown a:hover {
            background: #f4f7f6;
            color: #3498db;
        }

        .dropdown .logout {
            color: #e74c3c;
        }

        .dropdown .logout:hover {
            background: #fdecea;
            color: #e74c3c;
        }

        @keyframes fadeDropdown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .menu-icon {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: #2c3e50;
            cursor: pointer;
            padding: 6px 8px;
            border-radius: 6px;
            transition: color .2s, background .2s;
            order: 99;
        }

        .menu-icon:hover {
            background: rgba(52, 152, 219, .1);
            color: #3498db;
        }

        /* =========================================
           HERO
           ========================================= */
        #hero {
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
            padding-top: 70px;
        }

        .hero-text h1 {
            font-size: 45px;
            margin-bottom: 10px;
        }

        .hero-text p {
            font-size: 20px;
            margin-bottom: 30px;
        }

        .typing {
            color: #3498db;
            font-weight: 700;
        }

        /* =========================================
           FEATURES
           ========================================= */
        .section-padding {
            padding: 80px 0;
        }

        .title-center {
            text-align: center;
            margin-bottom: 50px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .feature-box {
            background: #fff;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: .4s;
        }

        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .feature-box i {
            font-size: 40px;
            color: #3498db;
            margin-bottom: 15px;
        }

        /* =========================================
           KERJASAMA / CTA SECTION
           ========================================= */
        #kerjasama {
            padding: 80px 0;
            background: linear-gradient(135deg, #1a2a4a 0%, #2a5298 60%);
        }

        .kerjasama-header {
            text-align: center;
            margin-bottom: 50px;
            color: #fff;
        }

        .kerjasama-header h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .kerjasama-header p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
        }

        .kerjasama-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .cta-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 40px 32px;
            text-align: center;
            color: #fff;
            transition: all .35s;
            position: relative;
            overflow: hidden;
        }

        .cta-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 60%);
            opacity: 0;
            transition: opacity .4s;
            pointer-events: none;
        }

        .cta-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .cta-card:hover::before {
            opacity: 1;
        }

        .cta-card .cta-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            font-size: 32px;
        }

        .cta-card.klien .cta-icon {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            box-shadow: 0 8px 20px rgba(243, 156, 18, 0.4);
        }

        .cta-card.narasumber .cta-icon {
            background: linear-gradient(135deg, #27ae60, #1e8449);
            box-shadow: 0 8px 20px rgba(39, 174, 96, 0.4);
        }

        .cta-card.eo .cta-icon {
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            box-shadow: 0 8px 20px rgba(142, 68, 173, 0.4);
        }

        .cta-card h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .cta-card p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .cta-card .cta-perks {
            list-style: none;
            text-align: left;
            margin-bottom: 28px;
            padding: 0;
        }

        .cta-card .cta-perks li {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            padding: 5px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cta-card .cta-perks li i {
            font-size: 12px;
            color: #f1c40f;
        }

        .cta-btn {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            transition: all .25s;
            cursor: pointer;
            border: 2px solid #fff;
            color: #fff;
        }

        .cta-card.klien .cta-btn {
            background: rgba(243, 156, 18, 0.25);
        }

        .cta-card.klien .cta-btn:hover {
            background: #f39c12;
            border-color: #f39c12;
            color: #fff;
        }

        .cta-card.narasumber .cta-btn {
            background: rgba(39, 174, 96, 0.25);
        }

        .cta-card.narasumber .cta-btn:hover {
            background: #27ae60;
            border-color: #27ae60;
            color: #fff;
        }

        .cta-card.eo .cta-btn {
            background: rgba(142, 68, 173, 0.25);
        }

        .cta-card.eo .cta-btn:hover {
            background: #8e44ad;
            border-color: #8e44ad;
            color: #fff;
        }

        

        /* =========================================
           WEBINAR
           ========================================= */
        .webinar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .webinar-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .webinar-img {
            height: 180px;
            background: #ddd;
        }

        .webinar-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .webinar-content {
            padding: 15px;
        }

        .webinar-content h3 {
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-detail {
            display: block;
            text-align: center;
            margin-top: 10px;
            padding: 8px;
            background: #f1c40f;
            color: black;
            border-radius: 5px;
            font-weight: 600;
            transition: .3s;
        }

        .btn-detail:hover {
            background: #d4ac0d;
            text-decoration: underline;
        }

        .btn-lihat {
            display: inline-block;
            padding: 12px 25px;
            background: #3498db;
            color: #fff;
            border-radius: 5px;
            font-weight: 600;
            transition: .3s;
        }

        .btn-lihat:hover {
            background: #2980b9;
        }

        /* =========================================
           MODAL
           ========================================= */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }

        .modal-box {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }

        /* =========================================
           REVEAL ANIMATION
           ========================================= */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: 1s all ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* =========================================
           FOOTER
           ========================================= */
        footer {
            background: #2c3e50;
            color: #fff;
            padding: 30px 0;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-brand h3 {
            margin-bottom: 5px;
            font-size: 18px;
        }

        .footer-brand p {
            font-size: 13px;
            color: #ccc;
        }

        .footer-contact {
            display: flex;
            gap: 30px;
        }

        .footer-item span {
            display: block;
            font-size: 12px;
            color: #aaa;
            margin-bottom: 3px;
        }

        .footer-item p {
            font-size: 14px;
            font-weight: 500;
        }

        /* =========================================
           RESPONSIVE
           ========================================= */
        @media (max-width: 768px) {
            .menu-icon {
                display: block;
            }

            .nav-content {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100%;
                background: #fff;
                flex-direction: column;
                align-items: stretch;
                padding: 16px 20px 20px;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
                display: none;
                z-index: 999;
                transition: top .3s;
            }

            .nav-content.active {
                display: flex;
            }

            header.scrolled .nav-content.active {
                background: #2c3e50;
            }

            .nav-menu {
                flex-direction: column;
                margin: 0 0 12px 0;
                gap: 2px;
            }

            .nav-menu li {
                padding: 0;
            }

            .nav-menu a {
                display: block;
                padding: 10px 8px;
                border-radius: 6px;
                font-size: 15px;
            }

            .nav-menu a:hover {
                background: rgba(52, 152, 219, .1);
            }

            .auth-buttons {
                flex-direction: column;
                gap: 8px;
            }

            .btn-login,
            .btn-register {
                text-align: center;
                padding: 10px;
            }

            .profile {
                width: 100%;
            }

            .dropdown {
                position: static;
                display: none;
                box-shadow: none;
                border-radius: 8px;
                background: rgba(52, 152, 219, .06);
                margin-top: 8px;
            }

            .dropdown.show {
                display: block;
            }

            .hero-text h1 {
                font-size: 28px;
            }

            .hero-text p {
                font-size: 16px;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-contact {
                flex-direction: column;
                gap: 10px;
            }

            .kerjasama-header h2 {
                font-size: 24px;
            }

            .kerjasama-grid {
                grid-template-columns: 1fr;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <header id="navbar">
        <div class="container">
            <nav>
                <div class="logo">Seminar<span>Online</span></div>

                <div class="nav-content" id="navContent">
                    <ul class="nav-menu">
                        <li><a href="#hero">Home</a></li>
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#kerjasama">Kerjasama</a></li>
                        <li><a href="#webinar">Webinar</a></li>
                    </ul>

                    <?php if (isset($_SESSION['id'])) { ?>
                        <div class="profile">
                            <div class="profile-img" onclick="toggleDropdown(event)">
                                    <?php if (!empty($_SESSION['foto_profil'])): ?>
                                    <img src="upload/foto_profil/<?= htmlspecialchars($_SESSION['foto_profil']) ?>"
                                        alt="foto profil">
                                    <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=3498db&color=fff"
                                        alt="avatar">
                                    <?php endif; ?>
                            </div>
                            <div class="dropdown" id="dropdownMenu">
                                    <?php if ($_SESSION['role'] == 'peserta'): ?>
                                    <a href="profil_peserta.php"><i class="fas fa-user-circle"></i> Profil Saya</a>
                                    <a href="dashboardpeserta.php"><i class="fas fa-th-large"></i> Seminar Saya</a>
                                    <?php elseif ($_SESSION['role'] == 'eo'): ?>
                                    <a href="dashboardeo.php"><i class="fas fa-th-large"></i> Dashboard EO</a>
                                    <?php elseif ($_SESSION['role'] == 'klien'): ?>
                                    <a href="datakontrak.php"><i class="fas fa-file-contract"></i> Kontrak Saya</a>
                                    <?php elseif ($_SESSION['role'] == 'narasumber'): ?>
                                    <a href="dashboardnarasumber.php"><i class="fas fa-chalkboard-teacher"></i> Dashboard</a>
                                    <?php endif; ?>
                                <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="auth-buttons">
                            <a href="login.php" class="btn-login">Login</a>
                            <a href="register.php" class="btn-register">Daftar</a>
                        </div>
                    <?php } ?>
                </div>

                <button class="menu-icon" id="menuIcon" aria-label="Toggle menu" aria-expanded="false">
                    <i class="fas fa-bars" id="burgerIcon"></i>
                </button>
            </nav>
        </div>
    </header>

    <!-- HERO -->
    <section id="hero">
        <div class="hero-text">
            <h1>Platform Kumpulan Seminar <span class="typing"></span></h1>
            <p>Tingkatkan skill anda dengan mengikuti seminar berkualitas secara online.</p>
            <a href="#webinar" class="btn-register" style="padding:15px 30px;font-size:18px;">Jelajahi Webinar</a>
        </div>
    </section>

    <!-- FITUR -->
    <section id="fitur" class="section-padding">
        <div class="container">
            <div class="title-center reveal">
                <h2>Mengapa Memilih Kami?</h2>
                <p>Fitur terbaik untuk mendukung proses belajar anda.</p>
            </div>
            <div class="features-grid">
                <div class="feature-box reveal">
                    <i class="fas fa-bolt"></i>
                    <h3>Akses Cepat</h3>
                    <p>Daftar dan langsung dapatkan link streaming tanpa menunggu lama.</p>
                </div>
                <div class="feature-box reveal">
                    <i class="fas fa-certificate"></i>
                    <h3>Mudah Digunakan</h3>
                    <p>Antarmuka yang sederhana dan intuitif memudahkan Anda dalam mengakses dan mengikuti seminar.</p>
                </div>
                <div class="feature-box reveal">
                    <i class="fas fa-users"></i>
                    <h3>Networking</h3>
                    <p>Bergabung dengan komunitas peserta lainnya untuk berdiskusi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- KERJASAMA / CTA -->
    <section id="kerjasama">
        <div class="container">
            <div class="kerjasama-header reveal">
                <h2>Mari Berkolaborasi Bersama Kami</h2>
                <p>Apakah Anda ingin menyelenggarakan seminar, berbagi ilmu sebagai pemateri, atau mengelola event
                    profesional?<br>Kami menyediakan ekosistem lengkap untuk semua peran.</p>
            </div>

            <div class="kerjasama-grid">

                <!-- CARD KLIEN -->
                <div class="cta-card klien reveal">
                    
                    <h3>Ingin Punya Seminar Sendiri?</h3>
                    <p>Wujudkan ide seminar Anda bersama Event Organizer profesional kami. Dari konsep hingga
                        pelaksanaan, semua kami tangani!</p>
                    <ul class="cta-perks">
                        <li><i class="fas fa-check-circle"></i> Ajukan kontrak kerja sama resmi</li>
                        <li><i class="fas fa-check-circle"></i> Pilih EO terpercaya dan berpengalaman</li>
                        <li><i class="fas fa-check-circle"></i> Pantau seminar secara real-time</li>
                        <li><i class="fas fa-check-circle"></i> Laporan peserta & dokumentasi lengkap</li>
                    </ul>
                    <a href="register.php?role=klien" class="cta-btn">
                         Mulai Sebagai Klien
                    </a>
                </div>

                <!-- CARD NARASUMBER -->
                <div class="cta-card narasumber reveal">
                    
                    <h3>Punya Keahlian? Jadilah Pemateri!</h3>
                    <p>Bagikan ilmu dan pengalaman Anda kepada ratusan peserta, dan dapatkan honorarium yang layak
                        setiap kali tampil sebagai narasumber.</p>
                    <ul class="cta-perks">
                        <li><i class="fas fa-check-circle"></i> Tampil di seminar yang terorganisir</li>
                        <li><i class="fas fa-check-circle"></i> Dapatkan honorarium profesional</li>
                        <li><i class="fas fa-check-circle"></i> Bangun reputasi dan portofolio</li>
                        <li><i class="fas fa-check-circle"></i> Jadwal fleksibel sesuai kesiapan Anda</li>
                    </ul>
                    <a href="register.php?role=narasumber" class="cta-btn">
                         Daftar Sebagai Pemateri
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- WEBINAR -->
    <section id="webinar" class="section-padding" style="background:#eef2f3;">
        <div class="container">
            <div class="title-center reveal">
                <h2>Webinar Terbaru</h2>
                <p>Pilih topik yang sesuai dengan minat karir anda.</p>
            </div>
            <div class="webinar-grid">
                <?php while ($s = mysqli_fetch_assoc($acara_seminar)) { ?>
                    <div class="webinar-card">
                        <div class="webinar-img">
                                <?php if (!empty($s['gambar'])) { ?>
                                <img src="upload/<?= $s['gambar']; ?>">
                                <?php } else { ?>
                                <img src="https://via.placeholder.com/400x200">
                                <?php } ?>
                        </div>
                        <div class="webinar-content">
                            <h3><?= $s['judul_seminar']; ?></h3>
                            <p>Narasumber: <?= $s['narasumber']; ?></p>
                            <p>Tanggal: <?= $s['tanggal']; ?></p>
                            <p>Jam: <?= $s['jam_mulai']; ?></p>
                            <p>Biaya: Rp<?= number_format($s['biaya'], 0, ',', '.'); ?></p>
                            <p>Kuota: <?= $s['kuota']; ?> orang</p>
                            <a href="detail_seminar_peserta.php?id=<?= $s['seminar_id']; ?>" class="btn-detail">Detail</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div style="text-align:center;margin-top:30px;">
                <a href="semua_seminar.php" class="btn-lihat">Lihat Seminar Lainnya</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>SeminarOnline</h3>
                    <p>&copy; 2026 Sistem Manajemen Seminar Online</p>
                </div>
                <div class="footer-contact">
                    <div class="footer-item">
                        <span>Email</span>
                        <p>support@seminaronline.com</p>
                    </div>
                    <div class="footer-item">
                        <span>Telepon</span>
                        <p>+62 812 3456 7890</p>
                    </div>
                    <div class="footer-item">
                        <span>Alamat</span>
                        <p>Yogyakarta, Indonesia</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <div id="myModal" class="modal">
        <div class="modal-box">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Judul Webinar</h2>
            <hr style="margin:15px 0;">
            <p>Detail informasi webinar akan ditampilkan di sini. Anda bisa melakukan pendaftaran setelah login ke
                sistem.</p>
            <br>
            <button class="btn-register" style="width:100%;border:none;cursor:pointer;">Daftar Sekarang</button>
        </div>
    </div>

    <script>
        const navbar = document.getElementById('navbar');
        const navContent = document.getElementById('navContent');
        const menuIcon = document.getElementById('menuIcon');
        const burgerIcon = document.getElementById('burgerIcon');

        function handleScroll() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
                navContent.style.top = '58px';
            } else {
                navbar.classList.remove('scrolled');
                navContent.style.top = '70px';
            }
            revealElements();
        }

        window.addEventListener('scroll', handleScroll, { passive: true });

        menuIcon.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = navContent.classList.toggle('active');
            menuIcon.setAttribute('aria-expanded', isOpen);
            burgerIcon.className = isOpen ? 'fas fa-times' : 'fas fa-bars';
        });

        document.addEventListener('click', function (e) {
            if (!menuIcon.contains(e.target) && !navContent.contains(e.target)) {
                navContent.classList.remove('active');
                menuIcon.setAttribute('aria-expanded', 'false');
                burgerIcon.className = 'fas fa-bars';
            }
        });

        function toggleDropdown(event) {
            event.stopPropagation();
            document.getElementById('dropdownMenu').classList.toggle('show');
        }

        document.addEventListener('click', function () {
            var dd = document.getElementById('dropdownMenu');
            if (dd) dd.classList.remove('show');
        });

        /* TYPING EFFECT */
        const textElement = document.querySelector('.typing');
        const words = ['Terbaik', 'Informatif', 'Mudah Digunakan'];
        let wordIdx = 0, charIdx = 0;

        function typeEffect() {
            if (charIdx < words[wordIdx].length) {
                textElement.textContent += words[wordIdx].charAt(charIdx++);
                setTimeout(typeEffect, 150);
            } else {
                setTimeout(eraseEffect, 1000);
            }
        }

        function eraseEffect() {
            if (charIdx > 0) {
                textElement.textContent = words[wordIdx].substring(0, --charIdx);
                setTimeout(eraseEffect, 100);
            } else {
                wordIdx = (wordIdx + 1) % words.length;
                setTimeout(typeEffect, 500);
            }
        }

        typeEffect();

        /* SMOOTH SCROLL */
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;
                e.preventDefault();
                navContent.classList.remove('active');
                burgerIcon.className = 'fas fa-bars';
                target.scrollIntoView({ behavior: 'smooth' });
            });
        });

        /* REVEAL ANIMATION */
        function revealElements() {
            document.querySelectorAll('.reveal').forEach(el => {
                const top = el.getBoundingClientRect().top;
                if (top < window.innerHeight - 120) {
                    el.classList.add('active');
                }
            });
        }

        revealElements();

        /* MODAL */
        function openModal(title) {
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('myModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('myModal').style.display = 'none';
        }
    </script>
</body>

</html>