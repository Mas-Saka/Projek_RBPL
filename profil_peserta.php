<?php
session_start();
include "config.php";

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'peserta') {
    header("Location: login.php");
    exit;
}

$peserta_id = $_SESSION['id'];

// Ambil data user terbaru dari DB
$user_q = mysqli_query($conn, "SELECT * FROM users WHERE id = $peserta_id");
$user = mysqli_fetch_assoc($user_q);

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — SeminarOnline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f8;
            color: #1a2634;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* ─── SIDEBAR ──────────────────────────────── */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #2c3e50;
            display: flex;
            flex-direction: column;
            z-index: 200;
            transition: transform .3s ease;
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
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #9baab7;
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
        }

        .sidebar-nav a i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255, 255, 255, .06);
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #e74c3c;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            transition: all .2s;
        }

        .sidebar-footer a:hover {
            background: rgba(231, 76, 60, .12);
        }

        .sidebar-footer a i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        /* ─── TOPBAR ───────────────────────────────── */
        .topbar {
            position: fixed;
            top: 0;
            left: 240px;
            width: calc(100% - 240px);
            height: 64px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            box-shadow: 0 1px 0 #e8edf2;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .burger-btn {
            display: none;
            background: none;
            border: none;
            color: #2c3e50;
            font-size: 20px;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
        }

        .topbar-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a2634;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #3498db;
        }

        .topbar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .topbar-name {
            font-size: 13px;
            font-weight: 500;
            color: #445566;
        }

        /* ─── MAIN ─────────────────────────────────── */
        .main {
            margin-left: 240px;
            padding-top: 64px;
            min-height: 100vh;
        }

        .page-inner {
            padding: 36px 32px;
            max-width: 900px;
        }

        /* ─── FLASH ────────────────────────────────── */
        .flash {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash.success {
            background: #e6f9f0;
            color: #1a8a50;
            border-left: 4px solid #27ae60;
        }

        .flash.error {
            background: #fdecea;
            color: #c0392b;
            border-left: 4px solid #e74c3c;
        }

        /* ─── PROFILE CARD ─────────────────────────── */
        .profile-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            border-radius: 16px;
            padding: 36px 32px;
            display: flex;
            align-items: center;
            gap: 28px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .05);
        }

        .profile-header::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: 80px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .04);
        }

        .avatar-wrap {
            position: relative;
            flex-shrink: 0;
            z-index: 1;
        }

        .avatar-wrap img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, .4);
            object-fit: cover;
            display: block;
            transition: opacity .3s;
        }

        .avatar-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(0, 0, 0, .5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            cursor: pointer;
            transition: opacity .25s;
        }

        .avatar-wrap:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-overlay i {
            color: #fff;
            font-size: 22px;
        }

        .avatar-input {
            display: none;
        }

        .profile-info {
            z-index: 1;
        }

        .profile-info h2 {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .profile-info p {
            color: rgba(255, 255, 255, .75);
            font-size: 13px;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            text-transform: capitalize;
        }

        /* ─── FORM CARD ────────────────────────────── */
        .form-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        .form-card-header {
            padding: 22px 28px;
            border-bottom: 1px solid #f0f4f8;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-card-header .icon-wrap {
            width: 38px;
            height: 38px;
            background: rgba(52, 152, 219, .1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3498db;
            font-size: 16px;
        }

        .form-card-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1a2634;
        }

        .form-card-header p {
            font-size: 12px;
            color: #7f8c9a;
            margin-top: 2px;
        }

        .form-body {
            padding: 28px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #556677;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab5c0;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #e0e8ef;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #1a2634;
            background: #f9fbfc;
            transition: border-color .2s, background .2s;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3498db;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, .1);
        }

        .form-group input[readonly] {
            background: #f0f4f8;
            color: #8a9ab0;
            cursor: not-allowed;
        }

        .form-hint {
            font-size: 11px;
            color: #aab5c0;
            margin-top: 2px;
        }

        /* ─── PASSWORD SECTION ─────────────────────── */
        .section-divider {
            border: none;
            border-top: 1px solid #f0f4f8;
            margin: 28px 0;
        }

        .section-sub {
            font-size: 13px;
            font-weight: 600;
            color: #556677;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-sub::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f0f4f8;
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #aab5c0;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
        }

        .toggle-pw:hover {
            color: #3498db;
        }

        /* ─── BUTTONS ──────────────────────────────── */
        .form-actions {
            padding: 20px 28px;
            border-top: 1px solid #f0f4f8;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #3498db;
            color: #fff;
            border: none;
            padding: 11px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .15s;
        }

        .btn-save:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-save:active {
            transform: translateY(0);
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f0f4f8;
            color: #556677;
            border: none;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background: #e0e8ef;
        }

        /* ─── OVERLAY & MOBILE ─────────────────────── */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 150;
        }

        .overlay.show {
            display: block;
        }

        /* ─── AVATAR PREVIEW MODAL ─────────────────── */
        .av-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .7);
            z-index: 500;
            justify-content: center;
            align-items: center;
        }

        .av-modal.show {
            display: flex;
        }

        .av-modal-box {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .av-modal-box h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        #previewImg {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
            margin-bottom: 20px;
        }

        .av-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-confirm {
            background: #27ae60;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-confirm:hover {
            background: #219150;
        }

        .btn-discard {
            background: #f0f4f8;
            color: #556677;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-discard:hover {
            background: #e0e8ef;
        }

        /* ─── RESPONSIVE ───────────────────────────── */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .topbar {
                left: 0;
                width: 100%;
            }

            .main {
                margin-left: 0;
            }

            .burger-btn {
                display: flex;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-inner {
                padding: 24px 16px;
            }
        }

        @media (max-width: 480px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn-save,
            .btn-cancel {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3>SeminarOnline</h3>
            <p>Dashboard Peserta</p>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Menu</div>
            <a href="dashboardpeserta.php">Dashboard</a>
            <a href="semua_seminar.php">Semua Seminar</a>
            <a href="jelajahi_seminar.php">Seminar Saya</a>

            <div class="nav-label">Akun</div>
            <a href="profil_peserta.php" class="active">Profil Saya</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <header class="topbar">
        <div class="topbar-left">
            <button class="burger-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <span class="topbar-title">Profil Saya</span>
        </div>
        <div class="topbar-right">
            <span class="topbar-name"><?= htmlspecialchars($user['nama']) ?></span>
            <div class="topbar-avatar">
                <?php if (!empty($user['foto_profil'])): ?>
                    <img src="upload/foto_profil/<?= htmlspecialchars($user['foto_profil']) ?>" alt="foto">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama']) ?>&background=3498db&color=fff"
                        alt="avatar">
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="page-inner">

            <?php if ($flash): ?>
                <div class="flash <?= $flash['type'] ?>">
                    <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>
            <?php endif; ?>

            <div class="profile-header">
                <div class="avatar-wrap">
                    <?php if (!empty($user['foto_profil'])): ?>
                        <img src="upload/foto_profil/<?= htmlspecialchars($user['foto_profil']) ?>" alt="foto"
                            id="headerAvatar">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama']) ?>&background=3498db&color=fff&size=200"
                            alt="avatar" id="headerAvatar">
                    <?php endif; ?>
                    <div class="avatar-overlay" onclick="document.getElementById('avatarInput').click()">
                        <i class="fas fa-camera"></i>
                    </div>
                    <input type="file" class="avatar-input" id="avatarInput" accept="image/*">
                </div>
                <div class="profile-info">
                    <h2><?= htmlspecialchars($user['nama']) ?></h2>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                    <div class="profile-badge">
                        <i class="fas fa-user-tag"></i>
                        <?= htmlspecialchars($user['role']) ?>
                    </div>
                </div>
            </div>

            <form method="POST" action="update_profil.php" enctype="multipart/form-data" id="profileForm">
                <input type="hidden" name="foto_hidden" id="fotoHidden">

                <div class="form-card">
                    <div class="form-card-header">
                        <div class="icon-wrap"><i class="fas fa-id-card"></i></div>
                        <div>
                            <h3>Informasi Pribadi</h3>
                            <p>Perbarui data diri kamu</p>
                        </div>
                    </div>

                    <div class="form-body">
                        <div class="form-grid">

                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <div class="input-wrap">
                                    <i class="fas fa-user"></i>
                                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>"
                                        required placeholder="Nama lengkap">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <div class="input-wrap">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                </div>
                                <span class="form-hint">Email tidak dapat diubah</span>
                            </div>

                            <div class="form-group">
                                <label>Nomor HP</label>
                                <div class="input-wrap">
                                    <i class="fas fa-phone"></i>
                                    <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp']) ?>"
                                        placeholder="08xxxxxxxxxx">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Role Akun</label>
                                <div class="input-wrap">
                                    <i class="fas fa-shield-halved"></i>
                                    <input type="text" value="<?= ucfirst($user['role']) ?>" readonly>
                                </div>
                            </div>

                            <input type="file" name="foto_profil" id="fotoProfil" accept="image/*" style="display:none">

                        </div>

                        <hr class="section-divider">
                        <div class="section-sub"><i class="fas fa-lock"></i> Ubah Password</div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Password Lama</label>
                                <div class="input-wrap">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="password_lama" id="pw_lama"
                                        placeholder="Kosongkan jika tidak ingin ubah">
                                    <button type="button" class="toggle-pw" onclick="togglePw('pw_lama',this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Password Baru</label>
                                <div class="input-wrap">
                                    <i class="fas fa-lock-open"></i>
                                    <input type="password" name="password_baru" id="pw_baru"
                                        placeholder="Min. 6 karakter">
                                    <button type="button" class="toggle-pw" onclick="togglePw('pw_baru',this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="dashboardpeserta.php" class="btn-cancel">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </main>

    <div class="av-modal" id="avModal">
        <div class="av-modal-box">
            <h3>Preview Foto Profil</h3>
            <img src="" alt="preview" id="previewImg">
            <p style="font-size:12px;color:#aab5c0;margin-bottom:16px;">Foto akan tersimpan saat kamu klik
                <strong>Simpan Perubahan</strong>
            </p>
            <div class="av-modal-actions">
                <button class="btn-confirm" onclick="confirmPhoto()">
                    <i class="fas fa-check"></i> Gunakan Foto Ini
                </button>
                <button class="btn-discard" onclick="discardPhoto()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </div>
    </div>

    <script>
        /* ── SIDEBAR ──────────────────────────────── */
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('show');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('show');
        }

        /* ── PASSWORD TOGGLE ─────────────────────── */
        function togglePw(id, btn) {
            var inp = document.getElementById(id);
            var ico = btn.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                ico.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                inp.type = 'password';
                ico.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        /* ── AVATAR UPLOAD ───────────────────────── */
        var selectedFile = null;

        // Klik dari avatar-overlay di header (ikon kamera)
        document.getElementById('avatarInput').addEventListener('change', function () {
            handleFile(this.files[0]);
        });

        function handleFile(file) {
            if (!file) return;
            selectedFile = file;
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('avModal').classList.add('show');
            };
            reader.readAsDataURL(file);
        }

        function confirmPhoto() {
            if (!selectedFile) return;
            // Tampilkan preview di header
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('headerAvatar').src = e.target.result;
            };
            reader.readAsDataURL(selectedFile);

            // Transfer ke input file tersembunyi di form
            var dt = new DataTransfer();
            dt.items.add(selectedFile);
            document.getElementById('fotoProfil').files = dt.files;

            document.getElementById('avModal').classList.remove('show');
        }

        function discardPhoto() {
            selectedFile = null;
            document.getElementById('avatarInput').value = '';
            document.getElementById('avModal').classList.remove('show');
        }

        /* ── AUTO DISMISS FLASH ──────────────────── */
        (function () {
            var f = document.querySelector('.flash');
            if (f) setTimeout(function () {
                f.style.opacity = '0';
                f.style.transition = 'opacity .4s';
                setTimeout(function () { f.remove(); }, 400);
            }, 5000);
        })();
    </script>
</body>

</html>