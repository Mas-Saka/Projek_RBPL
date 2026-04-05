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

        a { text-decoration: none; }
        ul { list-style: none; }

        .container {
            width: 85%;
            margin: auto;
            overflow: hidden;
        }

        /* =========================================
           NAVBAR (Sticky di Atas)
           ========================================= */
        header {
            background: #fff;
            height: 80px;
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }

        /* Perubahan Navbar saat Scroll */
        header.active {
            height: 65px;
            background: #2c3e50;
        }
        
        header.active .logo, header.active .nav-menu a {
            color: #fff;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #3498db;
        }

        .nav-content {
            display: flex;
            align-items: center;
        }

        .nav-menu {
            display: flex;
            margin-right: 30px;
        }

        .nav-menu li {
            padding: 0 15px;
        }

        .nav-menu a {
            color: #555;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-menu a:hover {
            color: #3498db;
        }

        /* Tombol Login & Register di Kanan */
        .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-login {
            padding: 8px 20px;
            border: 2px solid #3498db;
            color: #3498db;
            border-radius: 5px;
            font-weight: 600;
        }

        .btn-register {
            padding: 8px 20px;
            background: #3498db;
            color: #fff;
            border-radius: 5px;
            font-weight: 600;
        }

        .btn-register:hover { background: #2980b9; }

        /* Mobile Menu Icon */
        .menu-icon {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* =========================================
           HERO SECTION
           ========================================= */
        #hero {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
            padding-top: 80px;
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
           FEATURES (Card Section)
           ========================================= */
        .section-padding { padding: 80px 0; }
        
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.4s;
        }

        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .feature-box i {
            font-size: 40px;
            color: #3498db;
            margin-bottom: 15px;
        }

        /* =========================================
           WEBINAR PREVIEW
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
            padding: 20px;
        }

        .webinar-content h3 { margin-bottom: 10px; }

        .btn-detail {
            display: block;
            text-align: center;
            padding: 10px;
            background: #f1c40f;
            color: #000;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }

        /* =========================================
           MODAL & BACK TO TOP
           ========================================= */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
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
            top: 10px; right: 15px;
            font-size: 24px;
            cursor: pointer;
        }

        #backToTop {
            position: fixed;
            bottom: 20px; right: 20px;
            background: #3498db;
            color: #fff;
            width: 45px; height: 45px;
            border-radius: 50%;
            display: none;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 999;
        }

        /* =========================================
           ANIMASI REVEAL
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
            padding: 40px 0;
            text-align: center;
        }

       
        @media (max-width: 768px) {
            .nav-content { display: none; }
            .menu-icon { display: block; }
            .hero-text h1 { font-size: 30px; }
        }
    </style>
</head>
<body>

    <header id="navbar">
        <div class="container">
            <nav>
                <div class="logo">Seminar<span>Online</span></div>
                
                <div class="nav-content">
                    <ul class="nav-menu">
                        <li><a href="#hero">Home</a></li>
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#webinar">Webinar</a></li>
                        <li><a href="#kontak">Kontak</a></li>
                    </ul>

                    <div class="auth-buttons">
                        <a href="login.php" class="btn-login">Login</a>
                        <a href="register.php" class="btn-register">Daftar</a>
                    </div>
                </div>

                <div class="menu-icon">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <section id="hero">
        <div class="hero-text">
            <h1>Platform Belajar <span class="typing"></span></h1>
            <p>Tingkatkan skill anda dengan mengikuti seminar berkualitas secara online.</p>
            <a href="#webinar" class="btn-register" style="padding: 15px 30px; font-size: 18px;">Jelajahi Webinar</a>
        </div>
    </section>

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
                    <h3>E-Sertifikat</h3>
                    <p>Sertifikat resmi langsung terbit setelah seminar selesai dilaksanakan.</p>
                </div>
                <div class="feature-box reveal">
                    <i class="fas fa-users"></i>
                    <h3>Networking</h3>
                    <p>Bergabung dengan komunitas peserta lainnya untuk berdiskusi.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="webinar" class="section-padding" style="background: #eef2f3;">
        <div class="container">
            <div class="title-center reveal">
                <h2>Webinar Populer</h2>
                <p>Pilih topik yang sesuai dengan minat karir anda.</p>
            </div>
            <div class="webinar-grid">
                <div class="webinar-card reveal">
                    <div class="webinar-img">
                        <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=400&q=80">
                    </div>
                    <div class="webinar-content">
                        <h3>Web Development 2024</h3>
                        <p><i class="far fa-user"></i> Andre Taulany</p>
                        <p><i class="far fa-calendar-alt"></i> 15 Mei 2024</p>
                        <a href="javascript:void(0)" class="btn-detail" onclick="openModal('Web Development')">Lihat Detail</a>
                    </div>
                </div>
                <div class="webinar-card reveal">
                    <div class="webinar-img">
                        <img src="https://images.unsplash.com/photo-1551288049-bbbda536ad09?auto=format&fit=crop&w=400&q=80">
                    </div>
                    <div class="webinar-content">
                        <h3>Data Science Intro</h3>
                        <p><i class="far fa-user"></i> Prof. Budi</p>
                        <p><i class="far fa-calendar-alt"></i> 20 Mei 2024</p>
                        <a href="javascript:void(0)" class="btn-detail" onclick="openModal('Data Science')">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2024 SeminarOnline - Sistem Manajemen Seminar Online</p>
        </div>
    </footer>

    <div id="myModal" class="modal">
        <div class="modal-box">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Judul Webinar</h2>
            <hr style="margin: 15px 0;">
            <p>Detail informasi webinar akan ditampilkan di sini. Anda bisa melakukan pendaftaran setelah login ke sistem.</p>
            <br>
            <button class="btn-register" style="width: 100%; border:none; cursor:pointer;">Daftar Sekarang</button>
        </div>
    </div>

    <div id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </div>

    <script>
        // 1. NAVBAR SCROLL EFFECT
        window.onscroll = function() {
            let header = document.getElementById("navbar");
            let btnTop = document.getElementById("backToTop");

            if (window.pageYOffset > 100) {
                header.classList.add("active");
                btnTop.style.display = "flex";
            } else {
                header.classList.remove("active");
                btnTop.style.display = "none";
            }
            
            // Trigger Reveal Animation
            reveal();
        };

        // 2. TYPING EFFECT
        const textElement = document.querySelector(".typing");
        const words = ["Terbaik", "Interaktif"];
        let wordIdx = 0;
        let charIdx = 0;

        function typeEffect() {
            if (charIdx < words[wordIdx].length) {
                textElement.textContent += words[wordIdx].charAt(charIdx);
                charIdx++;
                setTimeout(typeEffect, 150);
            } else {
                setTimeout(eraseEffect, 1000);
            }
        }

        function eraseEffect() {
            if (charIdx > 0) {
                textElement.textContent = words[wordIdx].substring(0, charIdx - 1);
                charIdx--;
                setTimeout(eraseEffect, 100);
            } else {
                wordIdx = (wordIdx + 1) % words.length;
                setTimeout(typeEffect, 500);
            }
        }
        typeEffect();

        // 3. SMOOTH SCROLLING
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // 4. REVEAL ANIMATION ON SCROLL
        function reveal() {
            let reveals = document.querySelectorAll(".reveal");
            for (let i = 0; i < reveals.length; i++) {
                let windowHeight = window.innerHeight;
                let elementTop = reveals[i].getBoundingClientRect().top;
                let elementVisible = 150;
                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add("active");
                }
            }
        }

        // 5. MODAL LOGIC
        function openModal(title) {
            document.getElementById("modalTitle").innerText = title;
            document.getElementById("myModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        // 6. BACK TO TOP
        document.getElementById("backToTop").addEventListener("click", function() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    </script>
</body>
</html>