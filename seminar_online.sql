CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','eo','klien','narasumber','peserta') NOT NULL,
    no_hp VARCHAR(20),
    alamat TEXT,
    status_akun ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE klien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_perusahaan VARCHAR(150),
    bidang_usaha VARCHAR(150),
    status_kerjasama ENUM('aktif','tidak_aktif') DEFAULT 'aktif',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE narasumber (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gelar VARCHAR(100),
    instansi VARCHAR(150),
    keahlian VARCHAR(150),
    pengalaman_tahun INT,
    profil_singkat TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE kontrak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_kontrak VARCHAR(50),
    eo_id INT NOT NULL,
    klien_id INT NOT NULL,
    tanggal_buat DATE,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    nilai_kontrak DECIMAL(15,2),
    ruang_lingkup TEXT,
    isi_kontrak TEXT,
    status_kontrak ENUM('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
    FOREIGN KEY (eo_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (klien_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE seminar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kontrak_id INT,
    eo_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(100),
    kuota INT,
    biaya DECIMAL(10,2),
    metode ENUM('online','offline') DEFAULT 'online',
    lokasi VARCHAR(150),
    link_meeting VARCHAR(255),
    status ENUM('draft','aktif','selesai') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kontrak_id) REFERENCES kontrak(id) ON DELETE SET NULL,
    FOREIGN KEY (eo_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE jadwal_seminar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminar_id INT NOT NULL,
    tanggal DATE,
    hari VARCHAR(20),
    jam_mulai TIME,
    jam_selesai TIME,
    zona_waktu VARCHAR(50),
    FOREIGN KEY (seminar_id) REFERENCES seminar(id) ON DELETE CASCADE
);

CREATE TABLE pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminar_id INT NOT NULL,
    peserta_id INT NOT NULL,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metode_daftar VARCHAR(50),
    status ENUM('menunggu','diterima','ditolak') DEFAULT 'menunggu',
    kehadiran ENUM('hadir','tidak') DEFAULT NULL,
    FOREIGN KEY (seminar_id) REFERENCES seminar(id) ON DELETE CASCADE,
    FOREIGN KEY (peserta_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE materi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminar_id INT NOT NULL,
    narasumber_id INT NOT NULL,
    judul_materi VARCHAR(200),
    deskripsi TEXT,
    file_materi VARCHAR(255),
    tipe_file VARCHAR(50),
    ukuran_file VARCHAR(50),
    upload_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seminar_id) REFERENCES seminar(id) ON DELETE CASCADE,
    FOREIGN KEY (narasumber_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminar_id INT NOT NULL,
    peserta_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    tanggal_feedback TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_validasi ENUM('valid','pending') DEFAULT 'pending',
    FOREIGN KEY (seminar_id) REFERENCES seminar(id) ON DELETE CASCADE,
    FOREIGN KEY (peserta_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE laporan_akhir (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seminar_id INT NOT NULL,
    ringkasan TEXT,
    tujuan_acara TEXT,
    jumlah_peserta INT,
    jumlah_hadir INT,
    tingkat_kehadiran DECIMAL(5,2),
    dokumentasi TEXT,
    evaluasi TEXT,
    kesimpulan TEXT,
    tanggal_laporan DATE,
    FOREIGN KEY (seminar_id) REFERENCES seminar(id) ON DELETE CASCADE
);