-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 01:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `seminaronline`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `seminar_id` int(11) NOT NULL,
  `peserta_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `topik` varchar(255) DEFAULT NULL,
  `tanggal_feedback` datetime DEFAULT NULL,
  `status_validasi` enum('pending','valid','ditolak') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_seminar`
--

CREATE TABLE `jadwal_seminar` (
  `id` int(11) NOT NULL,
  `seminar_id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `zona_waktu` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klien`
--

CREATE TABLE `klien` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_perusahaan` varchar(150) DEFAULT NULL,
  `bidang_usaha` varchar(150) DEFAULT NULL,
  `status_kerjasama` enum('aktif','tidak_aktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kontrak`
--

CREATE TABLE `kontrak` (
  `kontrak_id` int(11) NOT NULL,
  `judul_kontrak` varchar(100) NOT NULL,
  `judul_seminar` varchar(200) NOT NULL,
  `nomor_kontrak` varchar(50) DEFAULT NULL,
  `eo_id` int(11) NOT NULL,
  `klien_id` int(11) NOT NULL,
  `tanggal_buat` date DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `nilai_kontrak` decimal(15,2) DEFAULT NULL,
  `isi_kontrak` varchar(2000) DEFAULT NULL,
  `status_kontrak` enum('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
  `alasan_penolakan` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kontrak`
--

INSERT INTO `kontrak` (`kontrak_id`, `judul_kontrak`, `judul_seminar`, `nomor_kontrak`, `eo_id`, `klien_id`, `tanggal_buat`, `tanggal_mulai`, `tanggal_selesai`, `nilai_kontrak`, `isi_kontrak`, `status_kontrak`, `alasan_penolakan`) VALUES
(1, 'Kerja Sama Seminar Pengembangan WEB ', 'Pengembangan Kemampuan Front-End dan Back-End Bersama Dicoding\r\n', '213sda', 19, 18, '2004-12-02', '2005-02-12', '2026-02-11', 123.00, 'Perjanjian kerja sama ini dibuat sebagai bentuk kesepakatan antara pihak Event Organizer (EO) dan pihak Klien dalam penyelenggaraan Seminar Pembelajaran Pengembangan Web yang bertujuan untuk memberikan edukasi kepada peserta mengenai dasar hingga penerapan pengembangan web modern. Kegiatan ini akan membahas materi seperti HTML, CSS, JavaScript, pengenalan backend dan database, serta implementasi teknologi web yang relevan dengan kebutuhan industri saat ini. Dalam kerja sama ini, EO bertanggung jawab atas perencanaan, pengelolaan teknis acara, penyediaan narasumber, pengelolaan sistem pendaftaran peserta, serta pelaporan hasil kegiatan kepada Klien.\r\n\r\nKlien bertindak sebagai mitra pendukung kegiatan dengan memberikan dukungan pendanaan sesuai nilai kontrak yang telah disepakati serta menyediakan materi atau identitas promosi yang akan ditampilkan selama kegiatan berlangsung. Sebagai imbal balik, Klien berhak memperoleh laporan pelaksanaan seminar, data jumlah peserta, serta dokumentasi kegiatan sebagai bentuk pertanggungjawaban kerja sama. Kedua belah pihak sepakat untuk menjalankan kerja sama ini secara profesional, transparan, dan saling menguntungkan sesuai dengan ketentuan yang telah disetujui bersama.', 'disetujui', ''),
(2, 'Kerja asasdsadadasdadasdad', 'sasadadasdsadsadad', '22pwa', 8, 18, '2004-12-02', '2005-01-02', '2005-02-20', 100.00, 'wadasdadddsadadadaadsadaawdda', 'disetujui', ''),
(6, 'Kerja Sama Webinar Penguatan Pelatihan Pekerja C++', 'Pelatihan Penguatan Fundamental Pekerja PT A dengan Pembelajaran C++', '#21pdf', 8, 18, '2026-03-28', '2026-03-12', '2026-03-19', 2500000.00, 'sadasdsaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'disetujui', ''),
(7, 'Penguatan ', 'asdasdasdasdasddddddddddddddddddddddddddddddddddddddddd', '91Pc', 8, 18, '2026-05-28', '2026-05-29', '2026-05-29', 2000000.00, 'lorep ipsum', 'menunggu', '');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_akhir`
--

CREATE TABLE `laporan_akhir` (
  `laporan_id` int(11) NOT NULL,
  `kontrak_id` int(11) NOT NULL,
  `seminar_id` int(11) NOT NULL,
  `eo_id` int(11) NOT NULL,
  `klien_id` int(11) NOT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `ringkasan` text DEFAULT NULL,
  `peserta_hadir` int(11) DEFAULT 0,
  `kendala` text DEFAULT NULL,
  `rekomendasi` text DEFAULT NULL,
  `catatan_tambahan` text DEFAULT NULL,
  `tanggal_laporan` date DEFAULT NULL,
  `status_laporan` enum('draft','terkirim') DEFAULT 'terkirim'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporan_akhir`
--

INSERT INTO `laporan_akhir` (`laporan_id`, `kontrak_id`, `seminar_id`, `eo_id`, `klien_id`, `judul_laporan`, `ringkasan`, `peserta_hadir`, `kendala`, `rekomendasi`, `catatan_tambahan`, `tanggal_laporan`, `status_laporan`) VALUES
(1, 6, 10, 8, 18, 'Laporan Akhir Seminar: daasd', 'seminar berjalan bagus dan lancar', 0, 'tidak ada', 'keren', 'anu', '2026-04-24', 'terkirim'),
(2, 2, 12, 8, 18, 'Laporan Akhir: Penguatan', 'berjalan baik', 1, 'kendala internet yang berasal dari keluhan peserta', '', '', '2026-05-28', 'terkirim'),
(3, 2, 12, 8, 18, 'Laporan Akhir: Penguatan', 'asdasd', 1, 'dasd', 'asdas', 'bagus', '2026-05-28', 'terkirim');

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `materi_id` int(11) NOT NULL,
  `seminar_id` int(11) NOT NULL,
  `narasumber_id` int(11) NOT NULL,
  `judul_materi` varchar(200) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_materi` varchar(255) DEFAULT NULL,
  `tipe_file` varchar(50) DEFAULT NULL,
  `ukuran_file` varchar(50) DEFAULT NULL,
  `upload_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materi`
--

INSERT INTO `materi` (`materi_id`, `seminar_id`, `narasumber_id`, `judul_materi`, `deskripsi`, `file_materi`, `tipe_file`, `ukuran_file`, `upload_at`) VALUES
(1, 8, 19, 'Materi biar mewing', 'Ayo Baca', '1776867536_Soal_Tugas_SIG___1_.pdf', 'pdf', '61173', '2026-04-22 14:18:56'),
(2, 11, 19, 'Penguatan Pemikiran Logic', 'Lorep Ipsum', '1779961312_latres.docx', 'docx', '1049304', '2026-05-28 09:41:53'),
(3, 12, 19, 'asd', 'asd', '1779981593_BPMN_SeminarOnline.png', 'png', '3635476', '2026-05-28 15:19:53'),
(4, 13, 19, 'Penguatan Pemikiran Logic', 'zczx', '1779984284_BPMN_SeminarOnline.png', 'png', '3635476', '2026-05-28 16:04:43'),
(5, 14, 19, 'Teknologi AI', 'Lorep Ipsum', '1779987072_latres.docx', 'docx', '1049304', '2026-05-28 16:51:11');

-- --------------------------------------------------------

--
-- Table structure for table `narasumber`
--

CREATE TABLE `narasumber` (
  `narasumber_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gelar` varchar(100) DEFAULT NULL,
  `instansi` varchar(150) DEFAULT NULL,
  `keahlian` varchar(150) DEFAULT NULL,
  `pengalaman_tahun` int(11) DEFAULT NULL,
  `profil_singkat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expired_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `otp_code`, `expired_at`, `created_at`) VALUES
(1, 'isyakamaulana@gmail.com', '78524', '2026-03-09 17:15:19', '2026-03-09 16:10:19'),
(2, 'isyakamaulana@gmail.com', '29315', '2026-03-09 17:15:26', '2026-03-09 16:10:26'),
(7, '', '30148', '2026-03-12 16:12:36', '2026-03-12 15:07:36'),
(18, 'udinnn@gmail.com', '662753', '2026-05-28 23:37:00', '2026-05-28 16:32:00');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL COMMENT 'Format: SEM-{seminar_id}-{user_id}-{timestamp}',
  `seminar_id` int(11) NOT NULL,
  `peserta_id` int(11) NOT NULL,
  `gross_amount` decimal(15,2) NOT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL COMMENT 'gopay, bca_va, bri_va, dsb',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT 'ID transaksi dari Midtrans',
  `status_payment` enum('pending','settlement','expire','cancel','deny') DEFAULT 'pending',
  `raw_response` text DEFAULT NULL COMMENT 'JSON response callback Midtrans',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `order_id`, `seminar_id`, `peserta_id`, `gross_amount`, `snap_token`, `payment_type`, `transaction_id`, `status_payment`, `raw_response`, `created_at`, `updated_at`) VALUES
(1, 'SEM-4-21-1776666694', 4, 21, 12312421.00, NULL, NULL, NULL, 'settlement', NULL, '2026-04-20 06:31:34', '2026-05-28 09:55:37'),
(2, 'SEM-4-21-1776667376', 4, 21, 12312421.00, NULL, NULL, NULL, 'pending', NULL, '2026-04-20 06:42:56', '2026-04-20 06:42:56'),
(3, 'SEM-4-21-1776667505', 4, 21, 12312421.00, NULL, NULL, NULL, 'pending', NULL, '2026-04-20 06:45:05', '2026-04-20 06:45:05'),
(4, 'SEM-4-21-1776667831', 4, 21, 12312421.00, NULL, NULL, NULL, 'pending', NULL, '2026-04-20 06:50:32', '2026-04-20 06:50:32'),
(5, 'SEM-4-21-1776667866', 4, 21, 12312421.00, NULL, NULL, NULL, 'pending', NULL, '2026-04-20 06:51:06', '2026-04-20 06:51:06'),
(6, 'SEM-4-21-1776668286', 4, 21, 12312421.00, NULL, NULL, NULL, 'pending', NULL, '2026-04-20 06:58:06', '2026-04-20 06:58:06'),
(7, 'SEM-4-21-1776668296', 4, 21, 12312421.00, NULL, NULL, NULL, 'pending', NULL, '2026-04-20 06:58:16', '2026-04-20 06:58:16'),
(8, 'SEM-4-21-1776668516', 4, 21, 12312421.00, 'b7d8cdb2-886e-41eb-8584-f7080b452119', NULL, NULL, 'pending', NULL, '2026-04-20 07:01:56', '2026-04-20 07:01:57'),
(9, 'SEM-2-21-1776668575', 2, 21, 12412.00, 'f835cfbf-8b9f-4c02-b1e7-54c872c5ab60', NULL, NULL, 'pending', NULL, '2026-04-20 07:02:55', '2026-04-20 07:02:56'),
(10, 'SEM-2-21-1776668646', 2, 21, 12412.00, '37aa0ff8-46a0-4a7c-a391-abdcfbd1f0fd', NULL, NULL, 'pending', NULL, '2026-04-20 07:04:06', '2026-04-20 07:04:07'),
(11, 'SEM-2-21-1776668705', 2, 21, 12412.00, 'ed2300de-7c28-43d5-a174-5f048aab54d3', NULL, NULL, 'pending', NULL, '2026-04-20 07:05:05', '2026-04-20 07:05:06'),
(12, 'SEM-2-21-1776668732', 2, 21, 12412.00, '42e2c5cf-e655-47f7-8d70-7686e130a7df', NULL, NULL, 'pending', NULL, '2026-04-20 07:05:32', '2026-04-20 07:05:33'),
(13, 'SEM-2-21-1776668750', 2, 21, 12412.00, '21b9240d-8f9c-4051-ada5-790e87e7cea1', NULL, NULL, 'pending', NULL, '2026-04-20 07:05:50', '2026-04-20 07:05:51'),
(14, 'SEM-4-21-1776668965', 4, 21, 12312421.00, '3d4d7ee1-db43-4790-aae5-711d4d362cf8', NULL, NULL, 'pending', NULL, '2026-04-20 07:09:25', '2026-04-20 07:09:25'),
(15, 'SEM-5-21-1776669067', 5, 21, 250000.00, '59acbf30-cd71-4ff9-8bb3-150014dd07bf', NULL, NULL, 'pending', NULL, '2026-04-20 07:11:07', '2026-04-20 07:11:09'),
(16, 'SEM-5-21-1776669080', 5, 21, 250000.00, '7b619b15-5a50-4f7d-86a8-b238a1c743c7', NULL, NULL, 'pending', NULL, '2026-04-20 07:11:20', '2026-04-20 07:11:20'),
(17, 'SEM-5-21-1776669174', 5, 21, 250000.00, 'f18d1d90-8664-4aad-aac4-86d32c544582', NULL, NULL, 'pending', NULL, '2026-04-20 07:12:54', '2026-04-20 07:12:55'),
(18, 'SEM-2-21-1776669242', 2, 21, 12412.00, '1708f641-8f1c-46e5-81d0-3588f5950506', NULL, NULL, 'pending', NULL, '2026-04-20 07:14:02', '2026-04-20 07:14:03'),
(19, 'SEM-2-21-1776669756', 2, 21, 12412.00, '46d4656d-03d0-45af-9c37-af4fbb8e3043', NULL, NULL, 'pending', NULL, '2026-04-20 07:22:36', '2026-04-20 07:22:36'),
(20, 'SEM-5-21-1776769538', 5, 21, 250000.00, '5b022576-6d58-47b0-b89b-c2deaae265a9', NULL, NULL, 'pending', NULL, '2026-04-21 11:05:38', '2026-04-21 11:05:39'),
(21, 'SEM-2-21-1776770184', 2, 21, 12412.00, 'abe94ffb-66ef-4af7-baaf-f38fe2800bf9', NULL, NULL, 'pending', NULL, '2026-04-21 11:16:24', '2026-04-21 11:16:26'),
(22, 'SEM-4-21-1776784349', 4, 21, 12312421.00, '0180eb54-b0fb-470d-b79b-2f86bbb079ef', NULL, NULL, 'pending', NULL, '2026-04-21 15:12:29', '2026-04-21 15:12:31'),
(23, 'SEM-5-21-1776784652', 5, 21, 250000.00, '9464cbe2-2e2f-4adc-97f4-9529272ea471', NULL, NULL, 'pending', NULL, '2026-04-21 15:17:32', '2026-04-21 15:17:33'),
(24, 'SEM-5-21-1776785432', 5, 21, 250000.00, 'e2d814c5-1b77-492e-9585-c08a2fe6bb10', NULL, NULL, 'pending', NULL, '2026-04-21 15:30:32', '2026-04-21 15:30:33'),
(25, 'SEM-2-21-1776785791', 2, 21, 12412.00, '5d8d9014-f282-490e-83a0-c4ab89a35513', NULL, NULL, 'pending', NULL, '2026-04-21 15:36:31', '2026-04-21 15:36:31'),
(26, 'SEM-2-21-1776786751', 2, 21, 12412.00, '30540450-792c-463d-9cf4-3929093c986a', NULL, NULL, 'pending', NULL, '2026-04-21 15:52:31', '2026-04-21 15:52:33'),
(27, 'SEM-4-21-1776786778', 4, 21, 12312421.00, '311f8cf0-c66b-414e-ad8e-ce3962cc0925', NULL, NULL, 'settlement', NULL, '2026-04-21 15:52:58', '2026-04-21 15:53:25'),
(28, 'SEM-5-21-1776786968', 5, 21, 250000.00, '35d68873-ad59-446a-9eb8-1056be5110e8', NULL, NULL, 'settlement', NULL, '2026-04-21 15:56:08', '2026-04-21 15:56:23'),
(29, 'SEM-7-21-1776787214', 7, 21, 100000.00, 'e14950c6-622d-47c8-9e9a-cbc429f69a36', NULL, NULL, 'settlement', NULL, '2026-04-21 16:00:14', '2026-04-21 16:00:37'),
(30, 'SEM-8-21-1776787400', 8, 21, 10000.00, '14e0d03f-1564-4a07-853b-eee5d9151fc4', NULL, NULL, 'pending', NULL, '2026-04-21 16:03:20', '2026-04-21 16:03:23'),
(31, 'SEM-8-21-1776787952', 8, 21, 10000.00, 'a4346e52-51ca-44f3-9778-9be908838a2e', NULL, NULL, 'settlement', NULL, '2026-04-21 16:12:32', '2026-04-21 16:12:46'),
(32, 'SEM-2-21-1777176381', 2, 21, 12412.00, '0d46423b-581f-4cd9-808b-3d1b67133075', NULL, NULL, 'settlement', NULL, '2026-04-26 04:06:21', '2026-04-26 04:06:38'),
(33, 'SEM-11-21-1779961562', 11, 21, 200000.00, 'dedc5343-0838-45f5-8ff3-f9358450344e', NULL, NULL, 'settlement', NULL, '2026-05-28 09:46:02', '2026-05-28 09:58:10'),
(34, 'SEM-11-21-1779961639', 11, 21, 200000.00, '69059fd3-514d-49c4-a746-f44a034c3354', NULL, NULL, 'pending', NULL, '2026-05-28 09:47:20', '2026-05-28 09:47:21'),
(35, 'SEM-11-21-1779961902', 11, 21, 200000.00, 'dc7adc46-5258-4d68-9681-ac0b2b1ea216', NULL, NULL, 'settlement', NULL, '2026-05-28 09:51:42', '2026-05-28 09:57:34'),
(36, 'SEM-11-21-1779962329', 11, 21, 200000.00, '8744883b-3ebb-4e58-84d8-c6f68eb71ed6', NULL, NULL, 'pending', NULL, '2026-05-28 09:58:49', '2026-05-28 09:58:50'),
(37, 'SEM-11-21-1779962965', 11, 21, 200000.00, '0becaa58-cb73-4605-a10f-8292eab4a860', NULL, NULL, 'pending', NULL, '2026-05-28 10:09:26', '2026-05-28 10:09:27'),
(38, 'SEM-10-21-1779963116', 10, 21, 2000000.00, 'e97873b1-6c06-4832-8d2d-665b49be8c3c', NULL, NULL, 'settlement', NULL, '2026-05-28 10:11:57', '2026-05-28 10:13:05'),
(39, 'SEM-10-21-1779963263', 10, 21, 2000000.00, 'cb295d47-e0d7-4eaf-8f39-635d59a07101', NULL, NULL, 'pending', NULL, '2026-05-28 10:14:24', '2026-05-28 10:14:25'),
(40, 'SEM-10-21-1779964727', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 10:38:47', '2026-05-28 10:38:47'),
(41, 'SEM-10-21-1779965122', 10, 21, 2000000.00, '1c92ffed-51af-487f-b31b-cc9ec0c05c0c', NULL, NULL, 'pending', NULL, '2026-05-28 10:45:22', '2026-05-28 10:45:23'),
(42, 'SEM-10-21-1779965140', 10, 21, 2000000.00, '55fce147-20d8-4f49-8273-0564ec14f2d5', NULL, NULL, 'pending', NULL, '2026-05-28 10:45:41', '2026-05-28 10:45:42'),
(43, 'SEM-10-21-1779965507', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 10:51:48', '2026-05-28 10:51:48'),
(44, 'SEM-10-21-1779965602', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 10:53:23', '2026-05-28 10:53:23'),
(45, 'SEM-10-21-1779966050', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:00:51', '2026-05-28 11:00:51'),
(46, 'SEM-10-21-1779966062', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:01:02', '2026-05-28 11:01:02'),
(47, 'SEM-10-21-1779966067', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:01:07', '2026-05-28 11:01:07'),
(48, 'SEM-10-21-1779966399', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:06:39', '2026-05-28 11:06:39'),
(49, 'SEM-10-21-1779966497', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:08:17', '2026-05-28 11:08:17'),
(50, 'SEM-10-21-1779966682', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:11:22', '2026-05-28 11:11:22'),
(51, 'SEM-10-21-1779966807', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:13:27', '2026-05-28 11:13:27'),
(52, 'SEM-10-21-1779966820', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:13:40', '2026-05-28 11:13:40'),
(53, 'SEM-10-21-1779966970', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:16:10', '2026-05-28 11:16:10'),
(54, 'SEM-10-21-1779966979', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:16:19', '2026-05-28 11:16:19'),
(55, 'SEM-10-21-1779967128', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:18:48', '2026-05-28 11:18:48'),
(56, 'SEM-10-21-1779968922', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:48:42', '2026-05-28 11:48:42'),
(57, 'SEM-10-21-1779968937', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:48:57', '2026-05-28 11:48:57'),
(58, 'SEM-10-21-1779969045', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 11:50:45', '2026-05-28 11:50:45'),
(59, 'SEM-10-21-1779969629', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:00:28', '2026-05-28 12:00:28'),
(60, 'SEM-10-21-1779969827', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:03:47', '2026-05-28 12:03:47'),
(61, 'SEM-10-21-1779969837', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:03:57', '2026-05-28 12:03:57'),
(62, 'SEM-10-21-1779970007', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:06:46', '2026-05-28 12:06:46'),
(63, 'SEM-10-21-1779970103', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:08:22', '2026-05-28 12:08:22'),
(64, 'SEM-10-21-1779970224', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:10:23', '2026-05-28 12:10:23'),
(65, 'SEM-10-21-1779970344', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:12:23', '2026-05-28 12:12:23'),
(66, 'SEM-10-21-1779970350', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:12:29', '2026-05-28 12:12:29'),
(67, 'SEM-10-21-1779970354', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:12:33', '2026-05-28 12:12:33'),
(68, 'SEM-10-21-1779970373', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:12:52', '2026-05-28 12:12:52'),
(69, 'SEM-10-21-1779970376', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:12:56', '2026-05-28 12:12:56'),
(70, 'SEM-10-21-1779970779', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:19:38', '2026-05-28 12:19:38'),
(71, 'SEM-10-21-1779970848', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:20:48', '2026-05-28 12:20:48'),
(72, 'SEM-10-21-1779970997', 10, 21, 2000000.00, NULL, NULL, NULL, 'pending', NULL, '2026-05-28 12:23:16', '2026-05-28 12:23:16'),
(73, 'SEM-10-21-1779971040', 10, 21, 2000000.00, '7e3073a1-c7b6-45e6-aa58-c1a1748a1714', NULL, NULL, 'pending', NULL, '2026-05-28 12:23:59', '2026-05-28 12:24:00'),
(74, 'SEM-10-21-1779971059', 10, 21, 2000000.00, 'f22392f3-c6b3-4435-b03d-b3422b1a24d4', NULL, NULL, 'pending', NULL, '2026-05-28 12:24:19', '2026-05-28 12:24:20'),
(75, 'SEM-10-21-1779971222', 10, 21, 2000000.00, '16a23a73-45d9-4f03-95eb-3959fee13184', NULL, NULL, 'pending', NULL, '2026-05-28 12:27:01', '2026-05-28 12:27:02'),
(76, 'SEM-11-21-1779971354', 11, 21, 200000.00, '991459de-ea81-4d07-b3c8-e3ad5d4bbda6', NULL, NULL, 'pending', NULL, '2026-05-28 12:29:13', '2026-05-28 12:29:14'),
(77, 'SEM-11-21-1779971382', 11, 21, 200000.00, 'd350c210-0fab-4c03-8a88-3e57af0428e6', NULL, NULL, 'pending', NULL, '2026-05-28 12:29:41', '2026-05-28 12:29:42'),
(78, 'SEM-10-21-1779971402', 10, 21, 2000000.00, 'd7599078-f4c0-4c11-bafa-00d502bc9c6a', NULL, NULL, 'pending', NULL, '2026-05-28 12:30:01', '2026-05-28 12:30:02'),
(79, 'SEM-11-21-1779972161', 11, 21, 200000.00, 'ea9ece99-6d8d-4bdf-8261-c9b655c26388', NULL, NULL, 'settlement', NULL, '2026-05-28 12:42:41', '2026-05-28 12:44:21'),
(80, 'SEM-10-21-1779972370', 10, 21, 2000000.00, 'cf9a3256-0bc4-4fb7-a68b-17559da7499e', NULL, NULL, 'pending', NULL, '2026-05-28 12:46:09', '2026-05-28 12:46:10'),
(81, 'SEM-10-21-1779972671', 10, 21, 2000000.00, 'a5db31d5-35b2-447b-b111-f16b6ad5abc8', NULL, NULL, 'pending', NULL, '2026-05-28 12:51:10', '2026-05-28 12:51:11'),
(82, 'SEM-10-21-1779975695', 10, 21, 2000000.00, '88f95a12-302c-4a6f-aa87-3a2587510a7f', NULL, NULL, 'pending', NULL, '2026-05-28 13:41:34', '2026-05-28 13:41:35'),
(83, 'SEM-10-21-1779976801', 10, 21, 2000000.00, '875a7d72-68aa-4675-af38-bdccd11c84db', NULL, NULL, 'pending', NULL, '2026-05-28 14:00:01', '2026-05-28 14:00:02'),
(84, 'SEM-10-21-1779976955', 10, 21, 2000000.00, '2872966c-a3b3-4184-b45d-9338bac6a8ef', NULL, NULL, 'pending', NULL, '2026-05-28 14:02:35', '2026-05-28 14:02:36'),
(85, 'SEM-10-21-1779977725', 10, 21, 2000000.00, 'dfa61d15-98f4-4037-b6ab-5346fa8300b0', NULL, NULL, 'pending', NULL, '2026-05-28 14:15:26', '2026-05-28 14:15:27'),
(86, 'SEM-10-21-1779978157', 10, 21, 2000000.00, '9eab6449-9901-4cc6-9eee-5b420133d0a6', NULL, NULL, 'pending', NULL, '2026-05-28 14:22:37', '2026-05-28 14:22:38'),
(87, 'SEM-10-21-1779978352', 10, 21, 2000000.00, '33314eda-0dd9-4fb7-9a1e-d9f6f3dce247', NULL, NULL, 'pending', NULL, '2026-05-28 14:25:52', '2026-05-28 14:25:53'),
(88, 'SEM-10-21-1779978842', 10, 21, 2000000.00, '4a3e8819-d1ed-43b7-8971-b82925ab5115', NULL, NULL, 'settlement', NULL, '2026-05-28 14:34:02', '2026-05-28 14:34:11'),
(89, 'SEM-12-21-1779981443', 12, 21, 100000.00, '9eb29069-df26-40a8-89be-5d2f65342060', NULL, NULL, 'settlement', NULL, '2026-05-28 15:17:23', '2026-05-28 15:17:32'),
(90, 'SEM-13-27-1779984537', 13, 27, 99999.00, 'ab7766a0-c984-41d5-a5a3-b3c249c56d8f', NULL, NULL, 'settlement', NULL, '2026-05-28 16:08:56', '2026-05-28 16:09:19');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL,
  `seminar_id` int(11) NOT NULL,
  `peserta_id` int(11) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `metode_daftar` varchar(50) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','diterima','ditolak') DEFAULT 'menunggu',
  `kehadiran` enum('hadir','tidak') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `seminar_id`, `peserta_id`, `tanggal_daftar`, `metode_daftar`, `order_id`, `bukti_bayar`, `status`, `kehadiran`) VALUES
(3, 3, 21, '2026-04-20 07:09:19', 'gratis', NULL, NULL, 'diterima', NULL),
(4, 4, 21, '2026-04-21 15:53:25', 'midtrans', 'SEM-4-21-1776786778', NULL, 'diterima', NULL),
(5, 5, 21, '2026-04-21 15:56:23', 'midtrans', 'SEM-5-21-1776786968', NULL, 'diterima', NULL),
(6, 7, 21, '2026-04-21 16:00:37', 'midtrans', 'SEM-7-21-1776787214', NULL, 'diterima', NULL),
(7, 8, 21, '2026-04-21 16:12:46', 'midtrans', 'SEM-8-21-1776787952', NULL, 'diterima', NULL),
(8, 2, 21, '2026-04-26 04:06:38', 'midtrans', 'SEM-2-21-1777176381', NULL, 'diterima', NULL),
(9, 10, 21, '2026-05-28 14:34:11', NULL, NULL, NULL, 'diterima', NULL),
(10, 12, 21, '2026-05-28 15:17:32', NULL, NULL, NULL, 'diterima', NULL),
(11, 13, 27, '2026-05-28 16:09:19', NULL, NULL, NULL, 'diterima', NULL),
(12, 14, 21, '2026-05-28 16:53:30', 'gratis', NULL, NULL, 'diterima', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `seminar`
--

CREATE TABLE `seminar` (
  `seminar_id` int(11) NOT NULL,
  `kontrak_id` int(11) DEFAULT NULL,
  `eo_id` int(11) NOT NULL,
  `narasumber_id` int(11) NOT NULL,
  `judul_seminar` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `kuota` int(11) DEFAULT NULL,
  `biaya` decimal(10,2) DEFAULT NULL,
  `metode` enum('online','offline') DEFAULT 'online',
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `platform` varchar(200) NOT NULL,
  `gambar` mediumblob NOT NULL,
  `link_meeting` varchar(255) DEFAULT NULL,
  `status` enum('draft','aktif','selesai') DEFAULT 'draft',
  `undangan_status` enum('menunggu','diterima','ditolak') NOT NULL DEFAULT 'menunggu',
  `alasan_tolak` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seminar`
--

INSERT INTO `seminar` (`seminar_id`, `kontrak_id`, `eo_id`, `narasumber_id`, `judul_seminar`, `deskripsi`, `kategori`, `kuota`, `biaya`, `metode`, `jam_mulai`, `jam_selesai`, `platform`, `gambar`, `link_meeting`, `status`, `undangan_status`, `alasan_tolak`, `created_at`, `tanggal`) VALUES
(2, 1, 8, 19, 'adsasd', 'adssa', 'sada', 121, 12412.00, 'online', '00:00:00', '00:00:00', 'Zoom', 0x53637265656e73686f7420323032362d30332d3132203031333931382e706e67, 'asdas', 'selesai', 'diterima', NULL, '2026-02-25 13:07:43', ''),
(3, NULL, 17, 19, 'asdasdasdasdasddddddddddddddddddddddddddddddddddddddddd', 'dsad\r\n\r\n\r\n\r\n', 'ad', 100, 0.00, 'online', '09:20:00', '10:30:00', 'Zoom', 0x53637265656e73686f7420323032362d30332d3132203031333931382e706e67, 'ZXDASD', 'selesai', 'diterima', NULL, '2026-03-24 18:15:18', '2005-10-20'),
(4, NULL, 17, 19, 'asd', 'asd', 'das', 12411, 12312421.00, 'online', '12:42:00', '23:12:00', 'Google Meet', '', '12sad', 'selesai', 'diterima', NULL, '2026-03-26 17:44:51', '1231-04-21'),
(5, NULL, 8, 19, 'Penguatan BLALA', 'SEMINAR KEREN', 'Pendidikan', 99, 250000.00, 'online', '09:00:00', '10:00:00', 'Google Meet', 0x3132343234303133315f5475676173342e706e67, 'adasdadasfa', 'selesai', 'diterima', NULL, '2026-04-05 06:24:03', '2026-04-15'),
(7, NULL, 8, 19, 'Penguatan Mahasiswa', 'adasdas', 'Pendidikan', 99, 100000.00, 'online', '12:30:00', '15:30:00', 'Zoom', 0x53637265656e73686f7420323032362d30342d3130203136303234382e706e67, 'zoom gaming', 'selesai', 'diterima', NULL, '2026-04-21 15:58:26', '2026-04-22'),
(8, NULL, 8, 19, 'sda', 'dsaddsa', 'dsasad', 9, 10000.00, 'online', '12:30:00', '13:30:00', 'Zoom', '', 'adasd', 'draft', 'diterima', NULL, '2026-04-21 16:02:18', '2026-04-22'),
(10, 6, 8, 19, 'Penguatan', 'adssadas', 'Teknologi', 100, 2000000.00, 'online', '12:30:00', '15:00:00', 'Zoom', 0x3132343234303133315f547567617335202831292e706e67, 'https\' zoom', 'draft', 'diterima', NULL, '2026-04-24 16:23:27', '2026-04-25'),
(11, 6, 8, 19, 'penguatan', 'lorep ipsum', 'Teknologi', 100, 200000.00, 'online', '18:00:00', '21:00:00', 'Zoom', 0x53637265656e73686f7420323032362d30352d3235203230343934382e706e67, 'https\' zoom', 'aktif', 'diterima', NULL, '2026-05-28 09:39:55', '2026-05-28'),
(12, 2, 8, 19, 'cxz', 'cxzc', 'Teknologi', 9, 100000.00, 'online', '22:00:00', '23:00:00', 'Zoom', '', 'adasdvga', 'selesai', 'diterima', NULL, '2026-05-28 15:11:44', '2026-07-01'),
(13, 6, 8, 19, 'Penguatan Bali ', 'ASDASAD', 'Teknologi', 10, 99999.00, 'online', '10:00:00', '12:00:00', 'Zoom', 0x53637265656e73686f7420323032362d30352d3235203230343433342e706e67, 'https dst', 'selesai', 'diterima', NULL, '2026-05-28 16:03:21', '2026-05-29'),
(14, 2, 8, 19, 'Penguatan Teknologi AI', 'Lorep Ipsum', 'Teknologi', 10, 0.00, 'online', '12:00:00', '14:00:00', 'Zoom', '', 'https://', 'aktif', 'diterima', NULL, '2026-05-28 16:48:59', '2026-06-29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','eo','klien','narasumber','peserta') NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `foto_profil` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `no_hp`, `foto_profil`, `created_at`) VALUES
(8, 'JOKO', 'joko@gmail.com', 'sdms1234', 'eo', '08961929122', '', '2026-02-23 13:32:10'),
(15, 'Isyaka Dhafa Maulana', 'udinsss@gmail.com', 'Sdms1234', 'peserta', '08969128391', '', '2026-02-23 14:03:39'),
(16, 'Isyaka Dhafa Maulana', 'Udinnsss@gmail.con', 'Sdms1234', 'peserta', '0896912313', '', '2026-02-23 14:14:12'),
(17, 'udinss', 'udinss@gmail.com', 'sdms1234', 'eo', '08961929122', '', '2026-02-24 12:58:18'),
(18, 'meja', 'meja@gmail.com', 'sdms1234', 'klien', '08961929122', '', '2026-02-24 13:16:30'),
(19, 'kipas', 'kipas@gmail.com', 'sdms1234', 'narasumber', '08829490', '', '2026-02-24 13:19:55'),
(20, 'Isyaka Dhafa Maulana', 'isyakamaulana@gmail.com', 'Sdms1234', 'peserta', '089619293719', '', '2026-03-09 15:32:20'),
(21, 'orang ganteng', 'kkaling389@gmail.com', 'Sdms1234', 'peserta', '089619293718', 'avatar_21_1776787916.jpg', '2026-03-09 16:13:07'),
(22, 'sakol', 'sakol@student.ac.id', 'Sdms1234', 'peserta', '0896124124112', '', '2026-04-07 11:20:01'),
(26, 'Saka', 'Saka@gmail.com', 'Sdms1234', 'peserta', '08912849', '', '2026-05-28 09:44:22'),
(27, 'Bagas', 'bagas@gmail.com', 'Sdms1234', 'peserta', '089892419', 'avatar_27_1779984475.png', '2026-05-28 16:06:32'),
(28, 'udin', 'udinnn@gmail.com', 'sdms1234', 'peserta', '089891248', '', '2026-05-28 16:20:12'),
(29, 'kue', 'kue@gmail.com', 'Sdms1234', 'peserta', '0898980', '', '2026-05-28 16:36:08'),
(30, 'kemangi', 'kemangi@gmail.com', 'Sdms1234', 'peserta', '0895215', '', '2026-05-28 16:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `verifikasi_otp`
--

CREATE TABLE `verifikasi_otp` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `seminar_id` int(11) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verifikasi_otp`
--

INSERT INTO `verifikasi_otp` (`id`, `user_id`, `seminar_id`, `otp`, `expired_at`) VALUES
(1, 21, 4, '870018', '2026-04-07 06:48:37'),
(2, 21, 4, '730428', '2026-04-07 06:49:29'),
(3, 21, 5, '713386', '2026-04-07 22:58:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jadwal_seminar`
--
ALTER TABLE `jadwal_seminar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seminar_id` (`seminar_id`);

--
-- Indexes for table `klien`
--
ALTER TABLE `klien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `kontrak`
--
ALTER TABLE `kontrak`
  ADD PRIMARY KEY (`kontrak_id`),
  ADD KEY `eo_id` (`eo_id`),
  ADD KEY `klien_id` (`klien_id`);

--
-- Indexes for table `laporan_akhir`
--
ALTER TABLE `laporan_akhir`
  ADD PRIMARY KEY (`laporan_id`),
  ADD KEY `kontrak_id` (`kontrak_id`),
  ADD KEY `seminar_id` (`seminar_id`),
  ADD KEY `eo_id` (`eo_id`),
  ADD KEY `klien_id` (`klien_id`);

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`materi_id`),
  ADD KEY `seminar_id` (`seminar_id`),
  ADD KEY `narasumber_id` (`narasumber_id`);

--
-- Indexes for table `narasumber`
--
ALTER TABLE `narasumber`
  ADD PRIMARY KEY (`narasumber_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `seminar_id` (`seminar_id`),
  ADD KEY `peserta_id` (`peserta_id`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pendaftaran` (`seminar_id`,`peserta_id`),
  ADD KEY `seminar_id` (`seminar_id`),
  ADD KEY `peserta_id` (`peserta_id`);

--
-- Indexes for table `seminar`
--
ALTER TABLE `seminar`
  ADD PRIMARY KEY (`seminar_id`),
  ADD KEY `kontrak_id` (`kontrak_id`),
  ADD KEY `eo_id` (`eo_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `verifikasi_otp`
--
ALTER TABLE `verifikasi_otp`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jadwal_seminar`
--
ALTER TABLE `jadwal_seminar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `klien`
--
ALTER TABLE `klien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kontrak`
--
ALTER TABLE `kontrak`
  MODIFY `kontrak_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `laporan_akhir`
--
ALTER TABLE `laporan_akhir`
  MODIFY `laporan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `materi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `narasumber`
--
ALTER TABLE `narasumber`
  MODIFY `narasumber_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
