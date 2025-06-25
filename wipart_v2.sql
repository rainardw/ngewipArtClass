-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2025 at 11:29 AM
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
-- Database: `wipart_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` varchar(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `email`, `password`, `role`) VALUES
('A001', 'Aditya Atomic', 'admin@wipart.com', 'admin123', 'admin'),
('A002', 'Rania Super', 'super@wipart.com', 'superpass', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `karya_seni`
--

CREATE TABLE `karya_seni` (
  `id_karya` int(11) NOT NULL,
  `id_member` int(11) NOT NULL,
  `judul_karya` varchar(255) NOT NULL,
  `deskripsi_karya` text DEFAULT NULL,
  `path_file_karya` varchar(255) NOT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_kelas_terkait` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `karya_seni`
--

INSERT INTO `karya_seni` (`id_karya`, `id_member`, `judul_karya`, `deskripsi_karya`, `path_file_karya`, `tanggal_upload`, `id_kelas_terkait`) VALUES
(1, 1, 'Kapten Mugiwara', '', 'assets/images/karya_member/1749076969_84eff2db52a41c2b433a89b1598378d7.png', '2025-06-04 22:42:49', 'KS001'),
(2, 3, 'Samurai Dragon Punk', '', 'assets/images/karya_member/1749077695_desktop-394782.jpg', '2025-06-04 22:54:55', NULL),
(4, 5, 'rania habis gambar', '', 'assets/images/karya_member/1749098105_Screenshot (868).png', '2025-06-05 04:35:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kelas_seni`
--

CREATE TABLE `kelas_seni` (
  `id_kelas` varchar(10) NOT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `deskripsi_singkat` text DEFAULT NULL,
  `deskripsi_lengkap` text DEFAULT NULL,
  `path_gambar` varchar(255) DEFAULT 'assets/images/kursus/default.png',
  `harga_online` decimal(10,2) DEFAULT NULL,
  `harga_offline` decimal(10,2) DEFAULT NULL,
  `tipe_kelas_tersedia` set('Online','Offline') DEFAULT NULL,
  `id_mentor` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas_seni`
--

INSERT INTO `kelas_seni` (`id_kelas`, `nama_kelas`, `deskripsi_singkat`, `deskripsi_lengkap`, `path_gambar`, `harga_online`, `harga_offline`, `tipe_kelas_tersedia`, `id_mentor`) VALUES
('33', 'anatomi hewan', 'menggambar hewan', 'hjsgyjdyuyg', 'assets/images/kursus/default.png', 1000000.00, NULL, 'Online', 'M003'),
('42', 'Gambar Kuno', 'merupakan kelas yang mempelajari cara menggambar jaman prasejarah', '', 'assets/images/kursus/default.png', 375000.00, NULL, 'Online', 'M002'),
('KS001', 'Dasar Menggambar Digital', 'Pelajari dasar-dasar menggambar menggunakan tablet dan software digital.', 'Kursus komprehensif untuk pemula yang ingin menguasai alat dan teknik dasar dalam seni digital, termasuk pengenalan software, penggunaan layer, brush, dan pewarnaan dasar.', 'assets/images/kursus/digital_art.jpg', 250000.00, 450000.00, 'Online,Offline', 'M001'),
('KS002', 'Teknik Melukis Cat Air', 'Kuasai teknik melukis cat air dari dasar hingga lanjutan.', 'Dalam kursus ini, Anda akan belajar berbagai teknik cat air, mulai dari wet-on-wet, dry brush, glazing, hingga menciptakan tekstur dan kedalaman pada lukisan Anda.', 'assets/images/kursus/watercolor.jpg', 200000.00, 400000.00, 'Offline', 'M002'),
('KS003', 'Ilustrasi Karakter Anime', 'Buat karakter anime unikmu sendiri dengan panduan langkah demi langkah.', 'Fokus pada anatomi khas anime, ekspresi wajah, desain kostum, dan pose dinamis. Cocok untuk Anda yang ingin menjadi ilustrator karakter atau komikus.', 'assets/images/kursus/anime_character.jpg', 300000.00, NULL, 'Online', 'M003'),
('KS004', 'Seni Sketsa Realistis', 'Pelajari cara membuat sketsa objek dan potret dengan proporsi dan bayangan yang akurat.', 'Kursus ini akan melatih mata dan tangan Anda untuk menangkap detail, bentuk, dan nilai cahaya untuk menghasilkan sketsa yang tampak hidup dan realistis.', 'assets/images/kursus/realistic_sketch.jpg', 220000.00, 380000.00, 'Online,Offline', 'M001');

-- --------------------------------------------------------

--
-- Table structure for table `materi_pelajaran`
--

CREATE TABLE `materi_pelajaran` (
  `id_materi` int(11) NOT NULL,
  `id_kelas` varchar(10) NOT NULL,
  `judul_materi` varchar(255) NOT NULL,
  `deskripsi_materi` text DEFAULT NULL,
  `konten_materi` longtext DEFAULT NULL,
  `tipe_konten` enum('Teks','VideoEmbed','Link','File') NOT NULL DEFAULT 'Teks',
  `urutan` int(11) DEFAULT 0,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materi_pelajaran`
--

INSERT INTO `materi_pelajaran` (`id_materi`, `id_kelas`, `judul_materi`, `deskripsi_materi`, `konten_materi`, `tipe_konten`, `urutan`, `tanggal_dibuat`) VALUES
(1, 'KS001', 'Pengenalan Alat dan Software', 'Mengenal berbagai alat digital dan software populer untuk menggambar.', 'Pada bab ini kita akan membahas:\n1. Jenis-jenis tablet grafis (Pen tablet, Display tablet)\n2. Software populer: Photoshop, Clip Studio Paint, Krita, Procreate\n3. Pengaturan dasar workspace.', 'Teks', 1, '2025-06-04 19:31:05'),
(2, 'KS001', 'Dasar-Dasar Layer', 'Memahami konsep layer dan bagaimana menggunakannya secara efektif.', '<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/dQw4w9WgXcQ\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>', 'VideoEmbed', 2, '2025-06-04 19:31:05'),
(3, 'KS001', 'Penggunaan Brush Dasar', 'Eksplorasi berbagai jenis brush dan pengaturannya.', 'Brush hardness, size, opacity, flow, dan blending mode.', 'Teks', 3, '2025-06-04 19:31:05'),
(4, 'KS001', 'Latihan Garis dan Bentuk', 'Latihan dasar untuk melatih kontrol tangan dan membuat bentuk dasar.', 'assets/materi/KS001_Latihan_Garis.pdf', 'File', 4, '2025-06-04 19:31:05'),
(5, 'KS002', 'Pengenalan Cat Air dan Kuas', 'Memilih cat air dan kuas yang tepat untuk pemula.', 'Jenis-jenis cat air (tube, pan), jenis kuas (round, flat, mop), dan kertas cat air.', 'Teks', 1, '2025-06-04 19:31:05'),
(6, 'KS002', 'Teknik Basah di Atas Basah (Wet-on-Wet)', 'Mempelajari efek cat air yang mengalir dengan teknik wet-on-wet.', 'Konten video atau teks detail...', 'Teks', 2, '2025-06-04 19:31:05'),
(7, 'KS001', 'menggambar peta', '', 'assets/materi/1749098523_Logo Atomic.jpg', 'File', 1, '2025-06-05 04:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `id_member` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`id_member`, `username`, `email`, `password`) VALUES
(1, 'WicakSini', 'wicak@gmail.com', 'memberWicakPass'),
(2, 'Ali Lotim', 'ali@gmail.com', 'memberAliPass'),
(3, 'Tegar Nagabonar', 'tegar@gmail.com', 'memberTegarPass'),
(4, 'Reksa Gondrong', 'rek@gmail.com', 'memberRek'),
(5, 'Rania buah naga', 'naga123@gmail.com', 'ranianaga');

-- --------------------------------------------------------

--
-- Table structure for table `mentor`
--

CREATE TABLE `mentor` (
  `id_mentor` varchar(5) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mentor`
--

INSERT INTO `mentor` (`id_mentor`, `username`, `email`, `password`) VALUES
('M001', 'Dzaka Shifu', 'dzakamentor@wipart.com', 'mentorZakaPass'),
('M002', 'YogaSensei', 'yogamentor@wipart.com', 'mentorYogaPass'),
('M003', 'Rafi Raja Kera', 'rafimentor@wipart.com', 'mentorRafiPass');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` varchar(30) NOT NULL,
  `id_pendaftaran_kursus` int(11) DEFAULT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Lunas','Ditolak','Dibatalkan','Kadaluarsa') NOT NULL DEFAULT 'Pending',
  `jumlah_bayar` decimal(10,2) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `verifikasi_admin_oleh` varchar(10) DEFAULT NULL,
  `tanggal_verifikasi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pendaftaran_kursus`, `tanggal_bayar`, `waktu`, `metode_pembayaran`, `status`, `jumlah_bayar`, `bukti_pembayaran`, `verifikasi_admin_oleh`, `tanggal_verifikasi`) VALUES
('PAY20250601001', 1, '2025-06-01', '10:05:00', 'Transfer Bank', 'Lunas', 250000.00, 'assets/images/bukti/bukti001.jpg', 'A001', '2025-06-01 14:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran_kursus`
--

CREATE TABLE `pendaftaran_kursus` (
  `id_pendaftaran` int(11) NOT NULL,
  `id_member` int(11) NOT NULL,
  `id_kelas` varchar(10) NOT NULL,
  `tipe_yang_dipilih` enum('Online','Offline') NOT NULL,
  `tanggal_pendaftaran` datetime NOT NULL DEFAULT current_timestamp(),
  `status_pendaftaran` enum('Menunggu Pembayaran','Aktif','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Pembayaran',
  `id_pembayaran` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran_kursus`
--

INSERT INTO `pendaftaran_kursus` (`id_pendaftaran`, `id_member`, `id_kelas`, `tipe_yang_dipilih`, `tanggal_pendaftaran`, `status_pendaftaran`, `id_pembayaran`) VALUES
(1, 1, 'KS001', 'Online', '2025-06-01 10:00:00', 'Aktif', NULL),
(2, 2, 'KS003', 'Online', '2025-06-02 11:00:00', 'Menunggu Pembayaran', NULL),
(3, 1, 'KS002', 'Offline', '2025-06-03 09:00:00', 'Menunggu Pembayaran', NULL),
(4, 2, 'KS001', 'Online', '2025-06-05 02:25:28', 'Menunggu Pembayaran', NULL),
(5, 3, 'KS004', 'Offline', '2025-06-05 02:37:26', 'Menunggu Pembayaran', NULL),
(6, 2, 'KS002', 'Offline', '2025-06-05 03:23:04', 'Menunggu Pembayaran', NULL),
(7, 1, 'KS003', 'Online', '2025-06-05 10:49:56', 'Menunggu Pembayaran', NULL),
(8, 5, 'KS004', 'Online', '2025-06-05 12:33:20', 'Menunggu Pembayaran', NULL),
(9, 5, '33', 'Online', '2025-06-05 12:48:16', 'Aktif', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username_admin_unique` (`username`),
  ADD UNIQUE KEY `email_admin_unique` (`email`);

--
-- Indexes for table `karya_seni`
--
ALTER TABLE `karya_seni`
  ADD PRIMARY KEY (`id_karya`),
  ADD KEY `fk_karya_member_idx` (`id_member`),
  ADD KEY `fk_karya_kelas_idx` (`id_kelas_terkait`);

--
-- Indexes for table `kelas_seni`
--
ALTER TABLE `kelas_seni`
  ADD PRIMARY KEY (`id_kelas`),
  ADD KEY `fk_kelas_mentor_idx` (`id_mentor`);

--
-- Indexes for table `materi_pelajaran`
--
ALTER TABLE `materi_pelajaran`
  ADD PRIMARY KEY (`id_materi`),
  ADD KEY `fk_materi_kelas_idx` (`id_kelas`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id_member`),
  ADD UNIQUE KEY `username_member_unique` (`username`),
  ADD UNIQUE KEY `email_member_unique` (`email`);

--
-- Indexes for table `mentor`
--
ALTER TABLE `mentor`
  ADD PRIMARY KEY (`id_mentor`),
  ADD UNIQUE KEY `username_mentor_unique` (`username`),
  ADD UNIQUE KEY `email_mentor_unique` (`email`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `fk_pembayaran_pendaftaran_idx` (`id_pendaftaran_kursus`),
  ADD KEY `fk_pembayaran_admin_verif_idx` (`verifikasi_admin_oleh`);

--
-- Indexes for table `pendaftaran_kursus`
--
ALTER TABLE `pendaftaran_kursus`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD UNIQUE KEY `unique_member_kelas_tipe` (`id_member`,`id_kelas`,`tipe_yang_dipilih`),
  ADD KEY `fk_pendaftaran_member_idx` (`id_member`),
  ADD KEY `fk_pendaftaran_kelas_idx` (`id_kelas`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `karya_seni`
--
ALTER TABLE `karya_seni`
  MODIFY `id_karya` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `materi_pelajaran`
--
ALTER TABLE `materi_pelajaran`
  MODIFY `id_materi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `id_member` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pendaftaran_kursus`
--
ALTER TABLE `pendaftaran_kursus`
  MODIFY `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `karya_seni`
--
ALTER TABLE `karya_seni`
  ADD CONSTRAINT `fk_karya_kelas` FOREIGN KEY (`id_kelas_terkait`) REFERENCES `kelas_seni` (`id_kelas`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_karya_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kelas_seni`
--
ALTER TABLE `kelas_seni`
  ADD CONSTRAINT `fk_kelas_mentor_constraint` FOREIGN KEY (`id_mentor`) REFERENCES `mentor` (`id_mentor`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `materi_pelajaran`
--
ALTER TABLE `materi_pelajaran`
  ADD CONSTRAINT `fk_materi_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas_seni` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_pembayaran_admin_verif_constraint` FOREIGN KEY (`verifikasi_admin_oleh`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pembayaran_pendaftaran_constraint` FOREIGN KEY (`id_pendaftaran_kursus`) REFERENCES `pendaftaran_kursus` (`id_pendaftaran`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pendaftaran_kursus`
--
ALTER TABLE `pendaftaran_kursus`
  ADD CONSTRAINT `fk_pendaftaran_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas_seni` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pendaftaran_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
