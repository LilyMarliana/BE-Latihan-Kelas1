-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 03, 2026 at 02:40 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `growwell_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GenerateAlertRisiko` (IN `p_anakID` INT, IN `p_zScore` DECIMAL(4,2), IN `p_kategoriStatus` VARCHAR(50))   BEGIN
    DECLARE v_jenisRisiko ENUM('risiko_stunting','risiko_gizi_buruk','risiko_obesitas','growth_faltering','risiko_kehamilan','nutrisi_tidak_adekuat');
    DECLARE v_tingkatRisiko ENUM('rendah','sedang','tinggi','kritis');
    DECLARE v_deskripsi TEXT;
    
    -- Tentukan jenis dan tingkat risiko berdasarkan z-score
    IF p_zScore < -3 THEN
        SET v_jenisRisiko = 'risiko_stunting';
        SET v_tingkatRisiko = 'kritis';
        SET v_deskripsi = 'Anak mengalami stunting parah (Z-score < -3 SD). Perlu intervensi segera.';
    ELSEIF p_zScore < -2 THEN
        SET v_jenisRisiko = 'risiko_stunting';
        SET v_tingkatRisiko = 'tinggi';
        SET v_deskripsi = 'Anak berisiko stunting (Z-score < -2 SD). Monitoring intensif diperlukan.';
    ELSEIF p_zScore < -1 THEN
        SET v_jenisRisiko = 'risiko_stunting';
        SET v_tingkatRisiko = 'sedang';
        SET v_deskripsi = 'Pertumbuhan anak perlu diperhatikan (Z-score < -1 SD).';
    END IF;
    
    -- Insert alert jika ada risiko
    IF v_jenisRisiko IS NOT NULL THEN
        INSERT INTO AlertRisiko (
            anakID, 
            jenisRisiko, 
            tingkatRisiko, 
            deskripsiRisiko,
            parameterPemicu
        ) VALUES (
            p_anakID,
            v_jenisRisiko,
            v_tingkatRisiko,
            v_deskripsi,
            JSON_OBJECT('z_score', p_zScore, 'kategori', p_kategoriStatus)
        );
        
        -- Kirim notifikasi ke orang tua
        INSERT INTO Notifikasi (userID, jenisNotif, judul, isiNotif, prioritas)
        SELECT 
            u.userID,
            'alert_risiko',
            'Peringatan Risiko Pertumbuhan',
            v_deskripsi,
            CASE v_tingkatRisiko
                WHEN 'kritis' THEN 'urgent'
                WHEN 'tinggi' THEN 'tinggi'
                ELSE 'normal'
            END
        FROM Anak a
        JOIN OrangTua ot ON a.orangtuaID = ot.orangtuaID
        JOIN User u ON ot.userID = u.userID
        WHERE a.anakID = p_anakID;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdatePoinRemaja` (IN `p_remajaID` INT, IN `p_poinTambahan` INT, IN `p_tipeAktivitas` VARCHAR(50), IN `p_deskripsi` VARCHAR(200))   BEGIN
    -- Update total poin remaja
    UPDATE Remaja 
    SET totalPoin = totalPoin + p_poinTambahan
    WHERE remajaID = p_remajaID;
    
    -- Catat riwayat perolehan poin
    INSERT INTO RiwayatPoin (userID, tipeAktivitas, poinDiperoleh, deskripsi)
    SELECT userID, p_tipeAktivitas, p_poinTambahan, p_deskripsi
    FROM Remaja
    WHERE remajaID = p_remajaID;
    
    -- Update level berdasarkan poin (setiap 100 poin naik 1 level)
    UPDATE Remaja
    SET level = FLOOR(totalPoin / 100) + 1
    WHERE remajaID = p_remajaID;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `HitungUsiaBulan` (`tanggal_lahir` DATE, `tanggal_referensi` DATE) RETURNS INT DETERMINISTIC BEGIN
    RETURN TIMESTAMPDIFF(MONTH, tanggal_lahir, tanggal_referensi);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `HitungZScore` (`nilai_aktual` DECIMAL(5,2), `median` DECIMAL(5,2), `std_dev` DECIMAL(5,2)) RETURNS DECIMAL(4,2) DETERMINISTIC BEGIN
    RETURN (nilai_aktual - median) / std_dev;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `alertrisiko`
--

CREATE TABLE `alertrisiko` (
  `alertID` int NOT NULL,
  `anakID` int DEFAULT NULL,
  `remajaID` int DEFAULT NULL,
  `ibuHamilID` int DEFAULT NULL,
  `jenisRisiko` enum('risiko_stunting','risiko_gizi_buruk','risiko_obesitas','growth_faltering','risiko_kehamilan','nutrisi_tidak_adekuat') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkatRisiko` enum('rendah','sedang','tinggi','kritis') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalDeteksi` datetime DEFAULT CURRENT_TIMESTAMP,
  `deskripsiRisiko` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameterPemicu` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: z-score, trend pertumbuhan, dll',
  `statusTindakLanjut` enum('menunggu','ditinjau','intervensi','selesai') COLLATE utf8mb4_unicode_ci DEFAULT 'menunggu',
  `tanggalTindakLanjut` datetime DEFAULT NULL,
  `catatanTindakLanjut` text COLLATE utf8mb4_unicode_ci,
  `tenagaKesehatanID` int DEFAULT NULL COMMENT 'PIC yang menangani',
  `prioritas` int DEFAULT '1' COMMENT '1=highest, 5=lowest'
) ;

--
-- Dumping data for table `alertrisiko`
--

INSERT INTO `alertrisiko` (`alertID`, `anakID`, `remajaID`, `ibuHamilID`, `jenisRisiko`, `tingkatRisiko`, `tanggalDeteksi`, `deskripsiRisiko`, `parameterPemicu`, `statusTindakLanjut`, `tanggalTindakLanjut`, `catatanTindakLanjut`, `tenagaKesehatanID`, `prioritas`) VALUES
(1, 5, NULL, NULL, 'risiko_stunting', 'kritis', '2026-01-20 10:30:00', 'Anak mengalami stunting parah (Z-score < -3 SD). Perlu intervensi segera.', '{\"z_score\":-2.80,\"trend\":\"turun\",\"kategori\":\"gizi_buruk\"}', 'intervensi', '2026-01-21 09:00:00', 'Sudah dihubungi orang tua. Jadwalkan konsultasi gizi.', 1, 1),
(2, 3, NULL, NULL, 'risiko_stunting', 'tinggi', '2026-01-18 14:00:00', 'Anak berisiko stunting (Z-score < -2 SD). Monitoring intensif diperlukan.', '{\"z_score\":-1.42,\"trend\":\"datar\",\"kategori\":\"gizi_kurang\"}', 'ditinjau', '2026-01-19 11:00:00', 'Disarankan meningkatkan asupan protein dan kalori harian.', 2, 2),
(3, NULL, 3, NULL, 'risiko_obesitas', 'sedang', '2026-01-28 16:00:00', 'IMT-for-Age tinggi. Risiko gizi lebih pada remaja.', '{\"z_score_imt\":1.90,\"trend\":\"naik\",\"kategori\":\"berisiko_gizi_lebih\"}', 'menunggu', NULL, NULL, 3, 3),
(4, NULL, NULL, 2, 'risiko_kehamilan', 'tinggi', '2026-01-28 17:30:00', 'Tekanan darah ibu sedikit tinggi di trimester 3. Pantau rutin setiap minggu.', '{\"tekanan_darah\":\"130/85\",\"trimester\":3,\"riwayat\":\"anemia\"}', 'menunggu', NULL, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `anak`
--

CREATE TABLE `anak` (
  `anakID` int NOT NULL,
  `orangtuaID` int NOT NULL,
  `namaLengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenisKelamin` enum('L','P') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalLahir` date NOT NULL,
  `tempatLahir` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beratBadanLahir` decimal(5,2) DEFAULT NULL,
  `tinggiLahir` decimal(5,2) DEFAULT NULL,
  `golonganDarah` enum('A','B','AB','O') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `riwayatKelahiran` text COLLATE utf8mb4_unicode_ci COMMENT 'normal, caesar, dll',
  `fotoProfile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statusAktif` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anak`
--

INSERT INTO `anak` (`anakID`, `orangtuaID`, `namaLengkap`, `jenisKelamin`, `tanggalLahir`, `tempatLahir`, `beratBadanLahir`, `tinggiLahir`, `golonganDarah`, `riwayatKelahiran`, `fotoProfile`, `statusAktif`) VALUES
(1, 1, 'Muhammad Adi Pratama', 'L', '2022-05-18', 'RSUD Palangka Raya', 3.40, 49.50, 'A', 'Lahir normal, spontan', 'foto_anak_adi.jpg', 1),
(2, 1, 'Putri Salma Nadia', 'P', '2024-01-30', 'RSUD Palangka Raya', 3.20, 48.00, 'A', 'Lahir normal, spontan', 'foto_anak_salma.jpg', 1),
(3, 2, 'Dafa Rizal Santoso', 'L', '2021-08-12', 'RS Semarang', 3.60, 50.00, 'B', 'Lahir caesar karena posisi sungsang', 'foto_anak_dafa.jpg', 1),
(4, 2, 'Nadia Aurelia', 'P', '2023-11-03', 'RS Semarang', 3.10, 48.50, 'B', 'Lahir normal, spontan', 'foto_anak_nadia.jpg', 1),
(5, 3, 'Elang Prasetyo', 'L', '2023-03-27', 'Puskesmas Gubeng', 2.90, 47.00, 'O', 'Lahir normal, berat lahir rendah', 'foto_anak_elang.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `asupanmakanan`
--

CREATE TABLE `asupanmakanan` (
  `asupanID` int NOT NULL,
  `nutrisiID` int NOT NULL,
  `waktuMakan` enum('sarapan','makan_siang','makan_malam','snack') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jamMakan` time DEFAULT NULL,
  `namaMakanan` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porsi` decimal(6,2) NOT NULL,
  `satuanPorsi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'gram, porsi, gelas, dll',
  `kalori` decimal(7,2) DEFAULT NULL,
  `protein` decimal(6,2) DEFAULT NULL,
  `karbohidrat` decimal(6,2) DEFAULT NULL,
  `lemak` decimal(6,2) DEFAULT NULL,
  `serat` decimal(6,2) DEFAULT NULL,
  `kategoriMakanan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'lokal, tradisional, modern, dll',
  `fotoMakanan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asupanmakanan`
--

INSERT INTO `asupanmakanan` (`asupanID`, `nutrisiID`, `waktuMakan`, `jamMakan`, `namaMakanan`, `porsi`, `satuanPorsi`, `kalori`, `protein`, `karbohidrat`, `lemak`, `serat`, `kategoriMakanan`, `fotoMakanan`) VALUES
(1, 1, 'sarapan', '07:00:00', 'Bubur Ayam', 150.00, 'gram', 180.00, 8.00, 25.00, 5.00, 1.50, 'tradisional', NULL),
(2, 1, 'makan_siang', '12:30:00', 'Nasi Putih', 100.00, 'gram', 130.00, 2.70, 28.00, 0.30, 0.40, 'modern', NULL),
(3, 1, 'makan_siang', '12:30:00', 'Tempe Goreng', 50.00, 'gram', 100.00, 10.00, 8.00, 4.00, 1.50, 'lokal', NULL),
(4, 1, 'makan_siang', '12:30:00', 'Sayur Bayam', 100.00, 'gram', 23.00, 2.90, 3.60, 0.40, 2.20, 'lokal', NULL),
(5, 1, 'makan_malam', '18:45:00', 'Nasi Putih', 100.00, 'gram', 130.00, 2.70, 28.00, 0.30, 0.40, 'modern', NULL),
(6, 1, 'makan_malam', '18:45:00', 'Telur Ayam Rebus', 50.00, 'gram', 78.00, 6.30, 0.60, 5.30, 0.00, 'modern', NULL),
(7, 1, 'snack', '15:30:00', 'Pisang Ambon', 80.00, 'gram', 71.00, 0.90, 18.20, 0.20, 2.10, 'lokal', NULL),
(8, 3, 'sarapan', '07:15:00', 'Kentang Rebus', 100.00, 'gram', 77.00, 2.00, 17.00, 0.10, 2.20, 'tradisional', NULL),
(9, 3, 'sarapan', '07:15:00', 'Telur Ayam Rebus', 50.00, 'gram', 78.00, 6.30, 0.60, 5.30, 0.00, 'modern', NULL),
(10, 3, 'makan_siang', '12:00:00', 'Nasi Putih', 100.00, 'gram', 130.00, 2.70, 28.00, 0.30, 0.40, 'modern', NULL),
(11, 3, 'makan_siang', '12:00:00', 'Ikan Lele Goreng', 80.00, 'gram', 160.00, 18.50, 0.00, 8.20, 0.00, 'lokal', NULL),
(12, 4, 'sarapan', '06:30:00', 'Nasi Putih', 150.00, 'gram', 195.00, 4.10, 42.00, 0.50, 0.60, 'modern', NULL),
(13, 4, 'sarapan', '06:30:00', 'Daging Ayam Rebus', 100.00, 'gram', 175.00, 30.00, 0.00, 6.30, 0.00, 'modern', NULL),
(14, 4, 'makan_siang', '12:30:00', 'Nasi Putih', 150.00, 'gram', 195.00, 4.10, 42.00, 0.50, 0.60, 'modern', NULL),
(15, 4, 'makan_siang', '12:30:00', 'Tempe Goreng', 80.00, 'gram', 160.00, 16.00, 12.80, 6.40, 2.40, 'lokal', NULL),
(16, 4, 'makan_siang', '12:30:00', 'Sayur Bayam', 100.00, 'gram', 23.00, 2.90, 3.60, 0.40, 2.20, 'lokal', NULL),
(17, 4, 'makan_malam', '18:00:00', 'Nasi Putih', 150.00, 'gram', 195.00, 4.10, 42.00, 0.50, 0.60, 'modern', NULL),
(18, 4, 'makan_malam', '18:00:00', 'Ikan Lele Goreng', 100.00, 'gram', 200.00, 23.10, 0.00, 10.30, 0.00, 'lokal', NULL),
(19, 4, 'snack', '15:00:00', 'Jeruk Mandarin', 100.00, 'gram', 49.00, 0.90, 11.20, 0.10, 1.60, 'lokal', NULL);

--
-- Triggers `asupanmakanan`
--
DELIMITER $$
CREATE TRIGGER `trg_update_total_nutrisi` AFTER INSERT ON `asupanmakanan` FOR EACH ROW BEGIN
    UPDATE DataNutrisi
    SET
        totalKalori      = totalKalori      + COALESCE(NEW.kalori, 0),
        totalProtein     = totalProtein     + COALESCE(NEW.protein, 0),
        totalKarbohidrat = totalKarbohidrat + COALESCE(NEW.karbohidrat, 0),
        totalLemak       = totalLemak       + COALESCE(NEW.lemak, 0),
        totalSerat       = totalSerat       + COALESCE(NEW.serat, 0)
    WHERE nutrisiID = NEW.nutrisiID;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `badge`
--

CREATE TABLE `badge` (
  `badgeID` int NOT NULL,
  `namaBadge` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `iconBadge` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategori` enum('konsistensi','pencapaian','edukasi','kolaborasi','spesial') COLLATE utf8mb4_unicode_ci NOT NULL,
  `syaratPerolehan` text COLLATE utf8mb4_unicode_ci COMMENT 'deskripsi syarat mendapatkan badge',
  `poinReward` int DEFAULT '0',
  `tingkatKesulitan` enum('mudah','sedang','sulit','expert') COLLATE utf8mb4_unicode_ci DEFAULT 'mudah'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badge`
--

INSERT INTO `badge` (`badgeID`, `namaBadge`, `deskripsi`, `iconBadge`, `kategori`, `syaratPerolehan`, `poinReward`, `tingkatKesulitan`) VALUES
(1, 'Pemula Sehat', 'Menyelesaikan profil kesehatan pertama kali', 'badge_pemula.png', 'konsistensi', NULL, 10, 'mudah'),
(2, 'Food Logger Konsisten', 'Mencatat makanan 7 hari berturut-turut', 'badge_logger.png', 'konsistensi', NULL, 50, 'sedang'),
(3, 'Pejuang Gizi', 'Menyelesaikan 5 tantangan nutrisi', 'badge_gizi.png', 'pencapaian', NULL, 100, 'sulit'),
(4, 'Pembelajar Aktif', 'Membaca 10 artikel edukasi', 'badge_belajar.png', 'edukasi', NULL, 30, 'mudah'),
(5, 'Konsistensi Emas', 'Pemantauan rutin selama 3 bulan', 'badge_emas.png', 'konsistensi', NULL, 200, 'expert'),
(6, 'Konsultan Setia', 'Melakukan konsultasi sebanyak 5 kali', 'badge_konsultan.png', 'kolaborasi', 'Selesaikan 5 sesi konsultasi', 75, 'sedang'),
(7, 'Superhero Nutrisi', 'Memenuhi target nutrisi harian selama 14 hari berturut', 'badge_superhero.png', 'pencapaian', 'Target nutrisi harian 14 hari', 150, 'sulit'),
(8, 'Explorer Edukasi', 'Menyelesaikan semua kategori konten edukasi', 'badge_explorer.png', 'edukasi', 'Baca semua kategori konten', 60, 'sedang');

-- --------------------------------------------------------

--
-- Table structure for table `dataantropometri`
--

CREATE TABLE `dataantropometri` (
  `dataID` int NOT NULL,
  `anakID` int DEFAULT NULL,
  `remajaID` int DEFAULT NULL,
  `tanggalPengukuran` date NOT NULL,
  `usiaSaatUkur` int DEFAULT NULL COMMENT 'dalam bulan',
  `beratBadan` decimal(5,2) NOT NULL COMMENT 'dalam kg',
  `tinggiBadan` decimal(5,2) NOT NULL COMMENT 'dalam cm',
  `lingkarKepala` decimal(5,2) DEFAULT NULL COMMENT 'dalam cm, untuk balita',
  `lingkarLengan` decimal(5,2) DEFAULT NULL COMMENT 'dalam cm, LILA',
  `zScoreBeratTinggi` decimal(4,2) DEFAULT NULL COMMENT 'Weight-for-Height',
  `zScoreTinggiUsia` decimal(4,2) DEFAULT NULL COMMENT 'Height-for-Age',
  `zScoreBeratUsia` decimal(4,2) DEFAULT NULL COMMENT 'Weight-for-Age',
  `zScoreIMT` decimal(4,2) DEFAULT NULL COMMENT 'BMI-for-Age',
  `kategoriStatusGizi` enum('gizi_buruk','gizi_kurang','gizi_baik','berisiko_gizi_lebih','gizi_lebih','obesitas') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategoriStunting` enum('sangat_pendek','pendek','normal','tinggi') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `catatanTambahan` text COLLATE utf8mb4_unicode_ci,
  `petugasID` int DEFAULT NULL COMMENT 'ID tenaga kesehatan yang melakukan pengukuran'
) ;

--
-- Dumping data for table `dataantropometri`
--

INSERT INTO `dataantropometri` (`dataID`, `anakID`, `remajaID`, `tanggalPengukuran`, `usiaSaatUkur`, `beratBadan`, `tinggiBadan`, `lingkarKepala`, `lingkarLengan`, `zScoreBeratTinggi`, `zScoreTinggiUsia`, `zScoreBeratUsia`, `zScoreIMT`, `kategoriStatusGizi`, `kategoriStunting`, `catatanTambahan`, `petugasID`) VALUES
(1, 1, NULL, '2025-11-10', 42, 13.20, 95.00, 49.50, 13.80, 0.12, -0.45, 0.08, 0.18, 'gizi_baik', 'normal', 'Pertumbuhan normal, lanjutkan pola makan seimbang', 2),
(2, 1, NULL, '2026-01-15', 44, 13.80, 97.20, 49.80, 14.10, 0.10, -0.40, 0.05, 0.15, 'gizi_baik', 'normal', 'Tetap konsistensi', 2),
(3, 3, NULL, '2025-10-20', 50, 14.50, 98.50, 50.20, 13.50, -0.20, -1.50, -0.80, -0.30, 'gizi_kurang', 'pendek', 'Monitoring ketat, tingkatkan nutrisi', 1),
(4, 3, NULL, '2026-01-18', 53, 15.10, 99.80, 50.50, 13.90, -0.15, -1.42, -0.70, -0.22, 'gizi_kurang', 'pendek', 'Ada peningkatan sedikit, teruskan intervensi', 1),
(5, 5, NULL, '2026-01-20', 34, 11.50, 85.00, 48.00, 12.20, -0.80, -2.80, -2.10, -1.50, 'gizi_buruk', 'sangat_pendek', 'PRIO: z-score < -2 SD. Intervensi segera diperlukan.', 1),
(6, NULL, 1, '2026-01-25', 186, 58.00, 172.50, NULL, NULL, 0.30, 0.80, 0.50, 0.40, 'gizi_baik', 'normal', 'Aktif berolahraga, pola makan baik', 3),
(7, NULL, 3, '2026-01-28', 194, 78.50, 175.00, NULL, NULL, 1.50, 0.20, 1.80, 1.90, 'berisiko_gizi_lebih', 'normal', 'IMT sedikit tinggi, sarankan kurangi snack', 3);

--
-- Triggers `dataantropometri`
--
DELIMITER $$
CREATE TRIGGER `trg_check_alert_antropometri` AFTER INSERT ON `dataantropometri` FOR EACH ROW BEGIN
    IF NEW.anakID IS NOT NULL AND NEW.zScoreTinggiUsia IS NOT NULL THEN
        CALL GenerateAlertRisiko(NEW.anakID, NEW.zScoreTinggiUsia, NEW.kategoriStatusGizi);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `datanutrisi`
--

CREATE TABLE `datanutrisi` (
  `nutrisiID` int NOT NULL,
  `userID` int DEFAULT NULL,
  `anakID` int DEFAULT NULL,
  `tanggalCatat` date NOT NULL,
  `totalKalori` decimal(7,2) DEFAULT '0.00',
  `totalProtein` decimal(6,2) DEFAULT '0.00',
  `totalKarbohidrat` decimal(6,2) DEFAULT '0.00',
  `totalLemak` decimal(6,2) DEFAULT '0.00',
  `totalSerat` decimal(6,2) DEFAULT '0.00',
  `catatanHarian` text COLLATE utf8mb4_unicode_ci
) ;

--
-- Dumping data for table `datanutrisi`
--

INSERT INTO `datanutrisi` (`nutrisiID`, `userID`, `anakID`, `tanggalCatat`, `totalKalori`, `totalProtein`, `totalKarbohidrat`, `totalLemak`, `totalSerat`, `catatanHarian`) VALUES
(1, 1, 1, '2026-01-15', 920.00, 38.50, 120.00, 28.00, 8.50, 'Hari yang baik, makan 3x + 1 snack'),
(2, 1, 1, '2026-01-16', 850.00, 35.20, 108.00, 25.50, 7.20, 'Kurang snack siang'),
(3, 2, 3, '2026-01-18', 780.00, 32.10, 98.00, 22.00, 6.80, 'Tambahkan sayuran lebih banyak'),
(4, 4, NULL, '2026-01-20', 1850.00, 62.00, 210.00, 55.00, 12.00, 'Remaja aktif, porsi lebih besar'),
(5, 3, 5, '2026-01-20', 650.00, 28.00, 82.00, 18.00, 5.50, 'Masih kurang, coba tambah protein');

-- --------------------------------------------------------

--
-- Table structure for table `ibuhamil`
--

CREATE TABLE `ibuhamil` (
  `ibuHamilID` int NOT NULL,
  `orangtuaID` int NOT NULL,
  `usiaKehamilan` int DEFAULT NULL COMMENT 'dalam minggu',
  `tanggalHPL` date DEFAULT NULL COMMENT 'Hari Perkiraan Lahir',
  `trimester` enum('1','2','3') COLLATE utf8mb4_unicode_ci NOT NULL,
  `beratBadanSebelumHamil` decimal(5,2) DEFAULT NULL,
  `tinggiBadan` decimal(5,2) DEFAULT NULL,
  `golonganDarah` enum('A','B','AB','O') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `riwayatPenyakit` text COLLATE utf8mb4_unicode_ci,
  `riwayatKehamilanSebelumnya` text COLLATE utf8mb4_unicode_ci,
  `risikoKehamilan` enum('rendah','sedang','tinggi') COLLATE utf8mb4_unicode_ci DEFAULT 'rendah',
  `statusImunisasi` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ibuhamil`
--

INSERT INTO `ibuhamil` (`ibuHamilID`, `orangtuaID`, `usiaKehamilan`, `tanggalHPL`, `trimester`, `beratBadanSebelumHamil`, `tinggiBadan`, `golonganDarah`, `riwayatPenyakit`, `riwayatKehamilanSebelumnya`, `risikoKehamilan`, `statusImunisasi`) VALUES
(1, 1, 22, '2026-06-10', '2', 58.50, 162.00, 'A', 'Tidak ada riwayat penyakit kronik', 'Kehamilan pertama', 'rendah', 'TT1 sudah, TT2 belum'),
(2, 2, 34, '2026-03-01', '3', 62.00, 158.00, 'B', 'Riwayat anemia ringan', 'Anak pertama lahir normal, anak kedua caesar', 'sedang', 'TT1 dan TT2 sudah');

-- --------------------------------------------------------

--
-- Table structure for table `interaksikonten`
--

CREATE TABLE `interaksikonten` (
  `interaksiID` int NOT NULL,
  `kontenID` int NOT NULL,
  `userID` int NOT NULL,
  `tipeInteraksi` enum('view','like','share','bookmark','complete') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalInteraksi` datetime DEFAULT CURRENT_TIMESTAMP,
  `durasiView` int DEFAULT NULL COMMENT 'dalam detik, untuk tracking engagement'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `interaksikonten`
--

INSERT INTO `interaksikonten` (`interaksiID`, `kontenID`, `userID`, `tipeInteraksi`, `tanggalInteraksi`, `durasiView`) VALUES
(1, 1, 1, 'view', '2026-01-05 10:15:00', 180),
(2, 1, 1, 'like', '2026-01-05 10:18:00', NULL),
(3, 2, 1, 'view', '2026-01-06 11:00:00', 240),
(4, 2, 1, 'bookmark', '2026-01-06 11:04:00', NULL),
(5, 3, 2, 'view', '2026-01-08 09:30:00', 300),
(6, 4, 4, 'view', '2026-01-10 16:00:00', 150),
(7, 4, 4, 'like', '2026-01-10 16:02:00', NULL),
(8, 6, 4, 'complete', '2026-01-15 19:00:00', 600);

--
-- Triggers `interaksikonten`
--
DELIMITER $$
CREATE TRIGGER `trg_update_view_count` AFTER INSERT ON `interaksikonten` FOR EACH ROW BEGIN
    IF NEW.tipeInteraksi = 'view' THEN
        UPDATE KontenEdukasi SET viewCount  = viewCount  + 1 WHERE kontenID = NEW.kontenID;
    ELSEIF NEW.tipeInteraksi = 'like' THEN
        UPDATE KontenEdukasi SET likeCount  = likeCount  + 1 WHERE kontenID = NEW.kontenID;
    ELSEIF NEW.tipeInteraksi = 'share' THEN
        UPDATE KontenEdukasi SET shareCount = shareCount + 1 WHERE kontenID = NEW.kontenID;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi`
--

CREATE TABLE `konsultasi` (
  `konsultasiID` int NOT NULL,
  `userID` int NOT NULL COMMENT 'user yang berkonsultasi',
  `tenagaKesehatanID` int NOT NULL,
  `tipeKonsultasi` enum('chat','video_call','phone_call') COLLATE utf8mb4_unicode_ci NOT NULL,
  `statusKonsultasi` enum('dijadwalkan','berlangsung','selesai','dibatalkan') COLLATE utf8mb4_unicode_ci DEFAULT 'dijadwalkan',
  `tanggalJadwal` datetime DEFAULT NULL,
  `tanggalMulai` datetime DEFAULT NULL,
  `tanggalSelesai` datetime DEFAULT NULL,
  `durasi` int DEFAULT NULL COMMENT 'dalam menit',
  `topikKonsultasi` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keluhanUtama` text COLLATE utf8mb4_unicode_ci,
  `catatanDokter` text COLLATE utf8mb4_unicode_ci,
  `diagnosaSementara` text COLLATE utf8mb4_unicode_ci,
  `rekomendasiTindakan` text COLLATE utf8mb4_unicode_ci,
  `urlRekaman` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'untuk video call recording',
  `rating` int DEFAULT NULL COMMENT '1-5',
  `feedback` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `konsultasi`
--

INSERT INTO `konsultasi` (`konsultasiID`, `userID`, `tenagaKesehatanID`, `tipeKonsultasi`, `statusKonsultasi`, `tanggalJadwal`, `tanggalMulai`, `tanggalSelesai`, `durasi`, `topikKonsultasi`, `keluhanUtama`, `catatanDokter`, `diagnosaSementara`, `rekomendasiTindakan`, `urlRekaman`, `rating`, `feedback`) VALUES
(1, 1, 1, 'chat', 'selesai', '2026-01-10 10:00:00', '2026-01-10 10:02:00', '2026-01-10 10:25:00', 23, 'Pola makan anak Adi', 'Anak tidak mau makan sayuran dan buah.', 'Coba teknik pengenalan makanan baru secara bertahap, mulai dari tekstur yang mudah.', 'Picky eating pada balita', 'Variasi menu, ajak anak memasak bersama', NULL, 5, 'Sangat membantu, terima kasih Dr. Kartika!'),
(2, 2, 2, 'video_call', 'selesai', '2026-01-12 14:00:00', '2026-01-12 14:05:00', '2026-01-12 14:40:00', 35, 'Rencana nutrisi Dafa', 'Anak terasa kurang berenergi dan pertumbuhan tampak lambat.', 'Perlu peningkatan asupan kalori dan protein. Tambahkan makanan lokal kaya protein seperti tempe dan ikan.', 'Kurang gizi sedang', 'Tambahkan suplemen dan tingkatkan porsi makan', NULL, 4, 'Penjelasan ahli gizi sangat detail.'),
(3, 3, 1, 'chat', 'berlangsung', '2026-01-30 09:00:00', '2026-01-30 09:01:00', NULL, NULL, 'Pertumbuhan Elang sangat khawatir', 'Anak terlihat sangat kecil dibanding teman sebaya.', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, 3, 'phone_call', 'dijadwalkan', '2026-02-05 11:00:00', NULL, NULL, NULL, 'Follow-up pertumbuhan Adi', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kontenedukasi`
--

CREATE TABLE `kontenedukasi` (
  `kontenID` int NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('stunting','gizi_balita','gizi_remaja','kehamilan','parenting','resep_sehat') COLLATE utf8mb4_unicode_ci NOT NULL,
  `targetSegmen` enum('ibu_hamil','orangtua','remaja','umum') COLLATE utf8mb4_unicode_ci NOT NULL,
  `formatKonten` enum('artikel','video','infografis','quiz','podcast') COLLATE utf8mb4_unicode_ci NOT NULL,
  `isiKonten` longtext COLLATE utf8mb4_unicode_ci,
  `excerptSingkat` text COLLATE utf8mb4_unicode_ci,
  `gambarUtama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urlVideo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urlFile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorID` int DEFAULT NULL COMMENT 'tenaga kesehatan pembuat konten',
  `sumberReferensi` text COLLATE utf8mb4_unicode_ci,
  `tanggalPublish` datetime DEFAULT CURRENT_TIMESTAMP,
  `viewCount` int DEFAULT '0',
  `likeCount` int DEFAULT '0',
  `shareCount` int DEFAULT '0',
  `tags` text COLLATE utf8mb4_unicode_ci COMMENT 'comma separated',
  `statusPublish` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kontenedukasi`
--

INSERT INTO `kontenedukasi` (`kontenID`, `judul`, `slug`, `kategori`, `targetSegmen`, `formatKonten`, `isiKonten`, `excerptSingkat`, `gambarUtama`, `urlVideo`, `urlFile`, `authorID`, `sumberReferensi`, `tanggalPublish`, `viewCount`, `likeCount`, `shareCount`, `tags`, `statusPublish`) VALUES
(1, 'Mengenal Stunting dan Cara Pencegahannya', 'mengenal-stunting-dan-cara-pencegahannya', 'stunting', 'orangtua', 'artikel', 'Stunting adalah kondisi gagal tumbuh pada anak balita akibat kekurangan gizi kronis. Stunting ditandai dengan tinggi badan anak yang jauh di bawah rata-rata usia. Pencegahan dimulai dari 1000 hari pertama kehidupan, yaitu sejak masa kehamilan hingga usia 2 tahun. Pastikan ibu mendapat nutrisi optimal selama hamil dan anak mendapat ASI eksklusif selama 6 bulan pertama.', 'Stunting adalah gagal tumbuh akibat kekurangan gizi kronis. Ketahui cara pencegahannya.', 'img_stunting_pencegahan.jpg', NULL, NULL, 2, 'WHO, Kemenkes RI, UNICEF', '2025-10-01 08:00:00', 1520, 340, 85, 'stunting,gizi,pencegahan,balita', 'published'),
(2, 'Resep Makanan Bergizi untuk Balita Usia 1-3 Tahun', 'resep-makanan-bergizi-balita-1-3-tahun', 'resep_sehat', 'orangtua', 'artikel', 'Berikut beberapa resep makanan bergizi yang mudah dibuat untuk balita usia 1-3 tahun. Sup Ayam Sayuran: Rebus ayam dengan wortel, jagung, dan kentang. Bubur Oatmeal Buah: Campurkan oatmeal dengan pisang dan madu sedikit. Nugget Ikan Homemade: Haluskan ikan, campur dengan tepung roti dan bumbu, lalu goreng.', 'Koleksi resep praktis dan bergizi untuk si kecil di usia 1-3 tahun.', 'img_resep_balita.jpg', NULL, NULL, 2, 'Ahli Gizi Kemenkes', '2025-10-10 09:00:00', 2100, 510, 120, 'resep,balita,nutrisi,mudah', 'published'),
(3, 'Video: Teknik Pemberian Makanan pada Bayi dan Balita', 'video-teknik-pemberian-makanan-bayi-balita', 'gizi_balita', 'orangtua', 'video', 'Video panduan lengkap mengenai teknik pemberian makanan pada bayi dan balita, mulai dari MPASI hingga makanan keluarga.', 'Panduan video cara memberikan makanan yang tepat untuk bayi dan balita.', 'img_video_mpasi.jpg', 'https://youtu.be/example_mpasi', NULL, 1, 'Kemenkes RI', '2025-10-15 10:00:00', 3200, 780, 200, 'video,MPASI,balita,panduan', 'published'),
(4, 'Nutrisi Penting untuk Remaja Aktif', 'nutrisi-penting-untuk-remaja-aktif', 'gizi_remaja', 'remaja', 'artikel', 'Remaja aktif membutuhkan asupan kalori dan nutrisi yang lebih tinggi dibandingkan anak-anak. Protein sangat penting untuk pertumbuhan otot dan jaringan. Kalsium dibutuhkan untuk tulang yang kuat. Zat besi penting terutama untuk remaja putri yang sedang mengalami haid.', 'Remaja aktif butuh nutrisi lebih. Ketahui kebutuhan gizi spesifik untuk tubuh yang berkembang.', 'img_nutrisi_remaja.jpg', NULL, NULL, 3, 'Journal of Adolescent Health', '2025-11-01 11:00:00', 980, 220, 55, 'remaja,nutrisi,protein,aktif', 'published'),
(5, 'Infografis: 1000 Hari Pertama Kehidupan', 'infografis-1000-hari-pertama-kehidupan', 'kehamilan', 'ibu_hamil', 'infografis', 'Infografis interaktif yang menjelaskan pentingnya nutrisi pada 1000 hari pertama kehidupan — dari kehamilan hingga usia 2 tahun.', '1000 hari pertama menentukan tumbuh kembang anak seumur hidup. Lihat infografis lengkapnya.', 'img_infografis_1000hari.jpg', NULL, NULL, 1, 'UNICEF, WHO', '2025-11-15 12:00:00', 1750, 400, 95, '1000hari,kehamilan,infografis,nutrisi', 'published'),
(6, 'Quiz: Seberapa Tahukah Anda Tentang Gizi Anak?', 'quiz-pengetahuan-gizi-anak', 'gizi_balita', 'umum', 'quiz', 'Uji pengetahuan Anda tentang nutrisi dan gizi anak dengan 10 pertanyaan pilihan ganda. Dapatkan badge dan poin untuk setiap jawaban benar!', 'Ikuti quiz interaktif tentang gizi anak dan dapatkan badge eksklusif.', 'img_quiz_gizi.jpg', NULL, NULL, 2, 'Kemenkes RI', '2025-12-01 14:00:00', 1100, 290, 60, 'quiz,gizi,anak,edukasi', 'published');

-- --------------------------------------------------------

--
-- Table structure for table `laporankesehatan`
--

CREATE TABLE `laporankesehatan` (
  `laporanID` int NOT NULL,
  `tenagaKesehatanID` int NOT NULL,
  `wilayahKerjaID` int DEFAULT NULL,
  `periodeLaporan` enum('mingguan','bulanan','triwulan','tahunan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalMulai` date NOT NULL,
  `tanggalSelesai` date NOT NULL,
  `jumlahPasienDipantau` int DEFAULT '0',
  `jumlahKasusStunting` int DEFAULT '0',
  `jumlahKasusGiziBuruk` int DEFAULT '0',
  `jumlahKasusObesitas` int DEFAULT '0',
  `jumlahAlertAktif` int DEFAULT '0',
  `jumlahIntervensiDilakukan` int DEFAULT '0',
  `jumlahKonsultasi` int DEFAULT '0',
  `trendPertumbuhan` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: data trend',
  `dataStatistik` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: data lengkap untuk chart',
  `catatanLaporan` text COLLATE utf8mb4_unicode_ci,
  `statusLaporan` enum('draft','final','terkirim') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `tanggalDibuat` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggalUpdate` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laporankesehatan`
--

INSERT INTO `laporankesehatan` (`laporanID`, `tenagaKesehatanID`, `wilayahKerjaID`, `periodeLaporan`, `tanggalMulai`, `tanggalSelesai`, `jumlahPasienDipantau`, `jumlahKasusStunting`, `jumlahKasusGiziBuruk`, `jumlahKasusObesitas`, `jumlahAlertAktif`, `jumlahIntervensiDilakukan`, `jumlahKonsultasi`, `trendPertumbuhan`, `dataStatistik`, `catatanLaporan`, `statusLaporan`, `tanggalDibuat`, `tanggalUpdate`) VALUES
(1, 1, 4, 'bulanan', '2026-01-01', '2026-01-31', 12, 2, 1, 0, 3, 2, 4, '{\"trend\":\"stabil\",\"catatan\":\"2 kasus stunting sedang diintervensi\"}', '{\"rata_rata_berat\":14.2,\"rata_rata_tinggi\":95.5,\"persentase_gizi_baik\":75}', 'Laporan bulan Januari 2026. Dua kasus stunting sedang dalam penanganan aktif.', 'final', '2026-02-03 22:39:40', '2026-02-03 22:39:40'),
(2, 2, 1, 'mingguan', '2026-01-13', '2026-01-19', 8, 1, 0, 1, 2, 1, 2, '{\"trend\":\"naik_ringan\",\"catatan\":\"1 kasus gizi lebih pada remaja\"}', '{\"rata_rata_berat\":16.8,\"rata_rata_tinggi\":100.2,\"persentase_gizi_baik\":80}', 'Laporan mingguan. Satu kasus berisiko gizi lebih pada remaja, sedang ditindaklanjuti.', 'final', '2026-02-03 22:39:40', '2026-02-03 22:39:40'),
(3, 3, 2, 'bulanan', '2025-12-01', '2025-12-31', 15, 1, 1, 0, 2, 1, 5, '{\"trend\":\"stabil\",\"catatan\":\"Secara keseluruhan tumbuh kembang baik\"}', '{\"rata_rata_berat\":15.5,\"rata_rata_tinggi\":97.0,\"persentase_gizi_baik\":82}', 'Laporan Desember 2025. Kondisi pasien secara umum baik.', 'terkirim', '2026-02-03 22:39:40', '2026-02-03 22:39:40');

-- --------------------------------------------------------

--
-- Table structure for table `logaktivitas`
--

CREATE TABLE `logaktivitas` (
  `logID` int NOT NULL,
  `userID` int NOT NULL,
  `aktivitas` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `targetTabel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `targetID` int DEFAULT NULL,
  `ipAddress` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` text COLLATE utf8mb4_unicode_ci,
  `device` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggalAktivitas` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logaktivitas`
--

INSERT INTO `logaktivitas` (`logID`, `userID`, `aktivitas`, `deskripsi`, `targetTabel`, `targetID`, `ipAddress`, `userAgent`, `device`, `tanggalAktivitas`) VALUES
(1, 1, 'LOGIN', 'User login berhasil', NULL, NULL, '192.168.1.10', 'Mozilla/5.0 Chrome/120', 'mobile', '2026-01-15 08:00:00'),
(2, 1, 'INSERT_NUTRISI', 'Tambahkan catatan asupan makanan untuk anak Adi', 'AsupanMakanan', 1, '192.168.1.10', 'Mozilla/5.0 Chrome/120', 'mobile', '2026-01-15 12:35:00'),
(3, 2, 'LOGIN', 'User login berhasil', NULL, NULL, '10.0.0.5', 'Mozilla/5.0 Safari/17', 'tablet', '2026-01-18 09:45:00'),
(4, 4, 'COMPLETE_QUIZ', 'Menyelesaikan quiz: Seberapa Tahukah Anda Tentang Gizi Anak', 'InteraksiKonten', 8, '172.16.0.20', 'Mozilla/5.0 Chrome/121', 'mobile', '2026-01-15 19:05:00'),
(5, 7, 'UPDATE_ALERT', 'Update status alert risiko anakID 5 menjadi intervensi', 'AlertRisiko', 1, '192.168.2.1', 'Mozilla/5.0 Firefox/121', 'desktop', '2026-01-21 09:00:15'),
(6, 3, 'OPEN_KONSULTASI', 'Membuka sesi konsultasi baru dengan Dr. Kartika', 'Konsultasi', 3, '10.0.1.12', 'Mozilla/5.0 Chrome/120', 'mobile', '2026-01-30 09:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `notifID` int NOT NULL,
  `userID` int NOT NULL,
  `jenisNotif` enum('reminder_pengukuran','reminder_vitamin','alert_risiko','konsultasi_dijadwalkan','rekomendasi_baru','tantangan_baru','badge_diperoleh','pesan_masuk','konten_baru') COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isiNotif` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `prioritas` enum('rendah','normal','tinggi','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `tanggalKirim` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggalBaca` datetime DEFAULT NULL,
  `statusBaca` tinyint(1) DEFAULT '0',
  `actionURL` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'deep link ke fitur terkait',
  `actionLabel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'label tombol action',
  `referensiID` int DEFAULT NULL COMMENT 'ID dari entitas terkait (alert, konsultasi, dll)',
  `referensiTipe` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'nama tabel referensi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`notifID`, `userID`, `jenisNotif`, `judul`, `isiNotif`, `prioritas`, `tanggalKirim`, `tanggalBaca`, `statusBaca`, `actionURL`, `actionLabel`, `referensiID`, `referensiTipe`) VALUES
(1, 3, 'alert_risiko', 'Peringatan Risiko Pertumbuhan – Elang', 'Hasil pemantauan menunjukkan z-score anak Elang di bawah standar. Silakan konsultasikan segera.', 'urgent', '2026-01-20 10:35:00', '2026-01-20 11:00:00', 1, '/alerts/1', 'Lihat Detail', 1, 'AlertRisiko'),
(2, 2, 'alert_risiko', 'Peringatan Risiko Pertumbuhan – Dafa', 'Pertumbuhan Dafa perlu diperhatikan lebih lanjut. Silakan ikuti rekomendasi dari ahli gizi.', 'tinggi', '2026-01-18 14:05:00', '2026-01-18 15:00:00', 1, '/alerts/2', 'Lihat Detail', 2, 'AlertRisiko'),
(3, 4, 'badge_diperoleh', 'Congrats! Anda mendapat Badge Baru', 'Anda berhasil mendapatkan badge \"Food Logger Konsisten\". Terus pertahankan!', 'normal', '2025-12-15 20:00:01', '2025-12-15 20:30:00', 1, '/badges', 'Lihat Badge', 2, 'Badge'),
(4, 1, 'konsultasi_dijadwalkan', 'Konsultasi Dijadwalkan', 'Konsultasi phone call dengan Dr. Samsul Hadi dijadwalkan pada 5 Feb 2026 pukul 11:00.', 'normal', '2026-01-28 09:00:00', NULL, 0, '/konsultasi/4', 'Lihat Jadwal', 4, 'Konsultasi'),
(5, 4, 'tantangan_baru', 'Tantangan Baru Tersedia!', 'Tantangan \"Aktivitas Fisik Mingguan\" sudah dimulai. Yuk ikut dan raih poin!', 'normal', '2026-01-20 07:00:00', '2026-01-20 08:00:00', 1, '/tantangan/3', 'Ikuti Sekarang', 3, 'Tantangan'),
(6, 1, 'rekomendasi_baru', 'Rekomendasi Baru untuk Adi', 'Sistem telah menghasilkan rekomendasi menu makanan sehat untuk anak Adi.', 'normal', '2026-01-16 10:01:00', '2026-01-16 14:00:00', 1, '/rekomendasi/1', 'Baca Rekomendasi', 1, 'RekomendasiPersonal');

-- --------------------------------------------------------

--
-- Table structure for table `orangtua`
--

CREATE TABLE `orangtua` (
  `orangtuaID` int NOT NULL,
  `userID` int NOT NULL,
  `namaLengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenisKelamin` enum('L','P') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalLahir` date NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `provinsi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kota` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kecamatan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikanTerakhir` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statusPernikahan` enum('menikah','single','cerai') COLLATE utf8mb4_unicode_ci DEFAULT 'menikah'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orangtua`
--

INSERT INTO `orangtua` (`orangtuaID`, `userID`, `namaLengkap`, `jenisKelamin`, `tanggalLahir`, `alamat`, `provinsi`, `kota`, `kecamatan`, `pendidikanTerakhir`, `pekerjaan`, `statusPernikahan`) VALUES
(1, 1, 'Siti Rahayu', 'P', '1990-03-15', 'Jl. Sudirman No. 12, RT 03/RW 02', 'Kalimantan Tengah', 'Palangka Raya', 'Palangka', 'S1', 'Guru SD', 'menikah'),
(2, 2, 'Budi Santoso', 'L', '1988-07-22', 'Jl. Kemerdekaan No. 7, RT 01/RW 05', 'Jawa Tengah', 'Semarang', 'Semarang Tengah', 'D3', 'Karyawan Swasta', 'menikah'),
(3, 3, 'Dewi Lestari', 'P', '1992-11-08', 'Jl. Ahmad Yani No. 25, RT 04/RW 01', 'Jawa Timur', 'Surabaya', 'Gubeng', 'S1', 'Ibu Rumah Tangga', 'menikah');

-- --------------------------------------------------------

--
-- Table structure for table `pemantauanjanin`
--

CREATE TABLE `pemantauanjanin` (
  `pemantauanID` int NOT NULL,
  `ibuHamilID` int NOT NULL,
  `tanggalPemeriksaan` date NOT NULL,
  `usiaKehamilanSaatIni` int DEFAULT NULL COMMENT 'dalam minggu',
  `beratBadanIbu` decimal(5,2) DEFAULT NULL,
  `tekananDarahSistolik` int DEFAULT NULL,
  `tekananDarahDiastolik` int DEFAULT NULL,
  `tinggiPundusFundus` decimal(5,2) DEFAULT NULL COMMENT 'dalam cm',
  `denyutJantungJanin` int DEFAULT NULL,
  `gerakanJanin` enum('aktif','normal','kurang') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `posisiJanin` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimasiBeratJanin` decimal(5,2) DEFAULT NULL,
  `catatanDokter` text COLLATE utf8mb4_unicode_ci,
  `petugasID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pemantauanjanin`
--

INSERT INTO `pemantauanjanin` (`pemantauanID`, `ibuHamilID`, `tanggalPemeriksaan`, `usiaKehamilanSaatIni`, `beratBadanIbu`, `tekananDarahSistolik`, `tekananDarahDiastolik`, `tinggiPundusFundus`, `denyutJantungJanin`, `gerakanJanin`, `posisiJanin`, `estimasiBeratJanin`, `catatanDokter`, `petugasID`) VALUES
(1, 1, '2025-12-20', 18, 60.50, 115, 75, 16.50, 142, 'aktif', 'Kepala belum turun', NULL, 'Pemeriksaan rutin trimester 2, semua normal', 1),
(2, 1, '2026-01-22', 21, 62.00, 118, 78, 19.80, 140, 'aktif', 'Kepala belum turun', 1.85, 'Janin berkembang baik, berat sesuai usia kehamilan', 1),
(3, 2, '2026-01-28', 33, 72.00, 130, 85, 32.00, 138, 'normal', 'Kepala sudah turun (engaged)', 2.90, 'Tekanan darah sedikit tinggi, pantau rutin', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `pesanID` int NOT NULL,
  `konsultasiID` int NOT NULL,
  `pengirimID` int NOT NULL COMMENT 'userID atau tenagaKesehatanID',
  `tipePengirim` enum('user','tenaga_kesehatan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `isiPesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileLampiran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipeFile` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggalKirim` datetime DEFAULT CURRENT_TIMESTAMP,
  `statusBaca` tinyint(1) DEFAULT '0',
  `tanggalBaca` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesan`
--

INSERT INTO `pesan` (`pesanID`, `konsultasiID`, `pengirimID`, `tipePengirim`, `isiPesan`, `fileLampiran`, `tipeFile`, `tanggalKirim`, `statusBaca`, `tanggalBaca`) VALUES
(1, 1, 1, 'user', 'Halo Dr. Kartika, saya Siti. Anak saya Adi susah makan sayuran dan buah. Bagaimana caranya?', NULL, NULL, '2026-01-10 10:02:15', 1, '2026-01-10 10:03:00'),
(2, 1, 7, 'tenaga_kesehatan', 'Selamat pagi Ibu Siti. Hal ini cukup umum pada balita. Coba perkenalkan sayuran dalam bentuk sup atau campuran makanan favorit Adi.', NULL, NULL, '2026-01-10 10:05:30', 1, '2026-01-10 10:06:00'),
(3, 1, 1, 'user', 'Terima kasih Dr. Kartika. Saya akan mencoba. Boleh saya kirim foto makanan Adi untuk dievaluasi?', NULL, NULL, '2026-01-10 10:08:00', 1, '2026-01-10 10:09:00'),
(4, 3, 3, 'user', 'Selamat pagi Dr. Kartika, saya Dewi. Anak saya Elang tampak sangat kecil. Saya khawatir.', NULL, NULL, '2026-01-30 09:01:30', 1, '2026-01-30 09:02:00'),
(5, 3, 7, 'tenaga_kesehatan', 'Selamat pagi Ibu Dewi. Terima kasih sudah menghubungi. Bisa ceritakan lebih lanjut mengenai pola makan dan aktivitas Elang?', NULL, NULL, '2026-01-30 09:03:00', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `referensimakanan`
--

CREATE TABLE `referensimakanan` (
  `makananID` int NOT NULL,
  `namaMakanan` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenisLokal` tinyint(1) DEFAULT '0' COMMENT 'makanan khas daerah',
  `porsiStandar` decimal(6,2) DEFAULT '100.00',
  `satuanStandar` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'gram',
  `kalori` decimal(7,2) DEFAULT NULL,
  `protein` decimal(6,2) DEFAULT NULL,
  `karbohidrat` decimal(6,2) DEFAULT NULL,
  `lemak` decimal(6,2) DEFAULT NULL,
  `serat` decimal(6,2) DEFAULT NULL,
  `vitamin` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: {A, B1, B2, C, D, E}',
  `mineral` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: {kalsium, zat_besi, zinc}',
  `sumberData` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'DKBM, USDA, dll',
  `fotoMakanan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referensimakanan`
--

INSERT INTO `referensimakanan` (`makananID`, `namaMakanan`, `kategori`, `jenisLokal`, `porsiStandar`, `satuanStandar`, `kalori`, `protein`, `karbohidrat`, `lemak`, `serat`, `vitamin`, `mineral`, `sumberData`, `fotoMakanan`) VALUES
(1, 'Nasi Putih', 'karbohidrat', 0, 100.00, 'gram', 130.00, 2.70, 28.00, 0.30, 0.40, NULL, NULL, NULL, NULL),
(2, 'Telur Ayam Rebus', 'protein', 0, 50.00, 'gram', 78.00, 6.30, 0.60, 5.30, 0.00, NULL, NULL, NULL, NULL),
(3, 'Tempe Goreng', 'protein', 1, 50.00, 'gram', 100.00, 10.00, 8.00, 4.00, 1.50, NULL, NULL, NULL, NULL),
(4, 'Sayur Bayam', 'sayuran', 0, 100.00, 'gram', 23.00, 2.90, 3.60, 0.40, 2.20, NULL, NULL, NULL, NULL),
(5, 'Pisang Ambon', 'buah', 0, 100.00, 'gram', 89.00, 1.10, 22.80, 0.30, 2.60, NULL, NULL, NULL, NULL),
(6, 'Ikan Lele Goreng', 'protein', 1, 80.00, 'gram', 160.00, 18.50, 0.00, 8.20, 0.00, '{\"A\":120,\"B1\":0.1,\"C\":2,\"D\":5,\"E\":1.2}', '{\"kalsium\":30,\"zat_besi\":1.8,\"zinc\":1.5}', 'DKBM', NULL),
(7, 'Kentang Rebus', 'karbohidrat', 0, 100.00, 'gram', 77.00, 2.00, 17.00, 0.10, 2.20, '{\"A\":0,\"B1\":0.1,\"C\":12,\"D\":0,\"E\":0.01}', '{\"kalsium\":10,\"zat_besi\":0.5,\"zinc\":0.3}', 'DKBM', NULL),
(8, 'Daging Ayam Rebus', 'protein', 0, 80.00, 'gram', 140.00, 24.00, 0.00, 5.00, 0.00, '{\"A\":60,\"B1\":0.08,\"C\":0,\"D\":0.5,\"E\":0.7}', '{\"kalsium\":15,\"zat_besi\":0.9,\"zinc\":1.2}', 'USDA', NULL),
(9, 'Wortel Mentah', 'sayuran', 0, 100.00, 'gram', 41.00, 0.90, 9.60, 0.20, 2.80, '{\"A\":16000,\"B1\":0.07,\"C\":9,\"D\":0,\"E\":0.7}', '{\"kalsium\":41,\"zat_besi\":0.4,\"zinc\":0.3}', 'DKBM', NULL),
(10, 'Jeruk Mandarin', 'buah', 0, 80.00, 'gram', 39.00, 0.70, 9.00, 0.10, 1.30, '{\"A\":150,\"B1\":0.06,\"C\":24,\"D\":0,\"E\":0.2}', '{\"kalsium\":28,\"zat_besi\":0.1,\"zinc\":0.1}', 'USDA', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rekomendasipersonal`
--

CREATE TABLE `rekomendasipersonal` (
  `rekomendasiID` int NOT NULL,
  `userID` int NOT NULL,
  `anakID` int DEFAULT NULL,
  `jenisRekomendasi` enum('menu_makanan','aktivitas_fisik','suplemen','pola_tidur','pemeriksaan_kesehatan','edukasi') COLLATE utf8mb4_unicode_ci NOT NULL,
  `judulRekomendasi` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `prioritas` enum('rendah','sedang','tinggi') COLLATE utf8mb4_unicode_ci DEFAULT 'sedang',
  `targetNutrisi` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: kalori, protein target, dll',
  `contohMenu` text COLLATE utf8mb4_unicode_ci,
  `tipsImplementasi` text COLLATE utf8mb4_unicode_ci,
  `tanggalDibuat` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggalKadaluarsa` date DEFAULT NULL,
  `statusDibaca` tinyint(1) DEFAULT '0',
  `tanggalDibaca` datetime DEFAULT NULL,
  `sumberRekomendasi` enum('sistem_otomatis','tenaga_kesehatan') COLLATE utf8mb4_unicode_ci DEFAULT 'sistem_otomatis',
  `pembuatID` int DEFAULT NULL COMMENT 'ID tenaga kesehatan jika manual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rekomendasipersonal`
--

INSERT INTO `rekomendasipersonal` (`rekomendasiID`, `userID`, `anakID`, `jenisRekomendasi`, `judulRekomendasi`, `deskripsi`, `prioritas`, `targetNutrisi`, `contohMenu`, `tipsImplementasi`, `tanggalDibuat`, `tanggalKadaluarsa`, `statusDibaca`, `tanggalDibaca`, `sumberRekomendasi`, `pembuatID`) VALUES
(1, 1, 1, 'menu_makanan', 'Menu Sehat Balita Usia 3-4 Tahun', 'Anak Adi membutuhkan menu dengan kandungan protein dan zat besi yang cukup untuk tumbuh kembang optimal.', 'sedang', '{\"kalori\":900,\"protein\":40,\"karbohidrat\":120,\"lemak\":30}', 'Sarapan: Bubur + telur. Makan siang: Nasi + ikan + sayur. Snack: Pisang.', 'Pastikan variasi lauk setiap hari untuk nutrisi lengkap.', '2026-01-16 10:00:00', '2026-02-16', 1, '2026-01-16 14:30:00', 'sistem_otomatis', NULL),
(2, 2, 3, 'suplemen', 'Suplementasi Zat Besi dan Vitamin A untuk Dafa', 'Hasil pemantauan menunjukkan Dafa memiliki z-score di bawah rata-rata. Disarankan suplementasi.', 'tinggi', '{\"zat_besi\":\"10mg/hari\",\"vitamin_A\":\"100.000 IU/minggu\"}', NULL, 'Berikan suplemen setelah makan untuk menghindari iritasi lambung.', '2026-01-19 09:00:00', '2026-02-19', 0, NULL, 'tenaga_kesehatan', 2),
(3, 3, 5, 'pemeriksaan_kesehatan', 'Jadwalkan Pemeriksaan Lengkap untuk Elang', 'Z-score sangat rendah. Perlu pemeriksaan laboratorium untuk mendeteksi penyakit kronis atau malabsorpsi.', 'tinggi', NULL, NULL, 'Kunjungi puskesmas dalam 1 minggu untuk pemeriksaan lab lengkap.', '2026-01-21 08:00:00', '2026-02-01', 0, NULL, 'tenaga_kesehatan', 1),
(4, 4, NULL, 'aktivitas_fisik', 'Program Latihan Aerobik untuk Remaja', 'Rizky memiliki aktivitas tinggi. Sarankan program latihan terstruktur untuk menjaga berat ideal.', 'rendah', '{\"kalori_bakar\":\"300-400 kcal/sesi\",\"frekuensi\":\"3x/minggu\"}', NULL, 'Lakukan jogging 30 menit di pagi hari atau sore hari.', '2026-01-22 11:00:00', '2026-03-22', 1, '2026-01-22 18:00:00', 'sistem_otomatis', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `remaja`
--

CREATE TABLE `remaja` (
  `remajaID` int NOT NULL,
  `userID` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalLahir` date NOT NULL,
  `jenisKelamin` enum('L','P') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sekolah` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tingkatAktivitas` enum('rendah','sedang','tinggi') COLLATE utf8mb4_unicode_ci DEFAULT 'sedang',
  `targetKesehatan` text COLLATE utf8mb4_unicode_ci,
  `totalPoin` int DEFAULT '0',
  `level` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remaja`
--

INSERT INTO `remaja` (`remajaID`, `userID`, `nama`, `tanggalLahir`, `jenisKelamin`, `sekolah`, `kelas`, `tingkatAktivitas`, `targetKesehatan`, `totalPoin`, `level`) VALUES
(1, 4, 'Rizky Pratama', '2010-06-14', 'L', 'SMA Negeri 1 Palangka Raya', 'X-A', 'tinggi', 'Maintain berat ideal dan aktivitas rutin', 350, 4),
(2, 5, 'Rina Sari', '2011-09-22', 'P', 'SMA Negeri 2 Semarang', 'IX-B', 'sedang', 'Tingkatkan asupan protein dan sayuran', 180, 2),
(3, 6, 'Kevin Nugroho', '2009-12-01', 'L', 'SMA Negeri 1 Surabaya', 'XI-C', 'tinggi', 'Turunkan berat badan dan jaga pola makan', 520, 6);

-- --------------------------------------------------------

--
-- Table structure for table `remajatantangan`
--

CREATE TABLE `remajatantangan` (
  `partisipasiID` int NOT NULL,
  `remajaID` int NOT NULL,
  `tantanganID` int NOT NULL,
  `tanggalMulai` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggalSelesai` datetime DEFAULT NULL,
  `progressSaatIni` decimal(10,2) DEFAULT '0.00',
  `targetValue` decimal(10,2) DEFAULT NULL,
  `persentaseSelesai` decimal(5,2) DEFAULT '0.00',
  `statusSelesai` tinyint(1) DEFAULT '0',
  `poinDiperoleh` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remajatantangan`
--

INSERT INTO `remajatantangan` (`partisipasiID`, `remajaID`, `tantanganID`, `tanggalMulai`, `tanggalSelesai`, `progressSaatIni`, `targetValue`, `persentaseSelesai`, `statusSelesai`, `poinDiperoleh`) VALUES
(1, 1, 1, '2026-01-13 07:00:00', '2026-01-19 23:59:00', 7.00, 7.00, 100.00, 1, 50),
(2, 1, 2, '2026-01-05 10:00:00', NULL, 3.00, 5.00, 60.00, 0, 0),
(3, 1, 4, '2026-01-05 08:00:00', NULL, 10.00, 14.00, 71.43, 0, 0),
(4, 2, 2, '2026-01-08 14:00:00', NULL, 2.00, 5.00, 40.00, 0, 0),
(5, 3, 3, '2026-01-20 06:30:00', '2026-01-26 23:59:00', 3.00, 3.00, 100.00, 1, 40);

-- --------------------------------------------------------

--
-- Table structure for table `riwayatpoin`
--

CREATE TABLE `riwayatpoin` (
  `riwayatID` int NOT NULL,
  `userID` int NOT NULL,
  `tipeAktivitas` enum('food_logging','pemantauan_antropometri','selesai_tantangan','konsultasi','baca_edukasi','share_konten','konsistensi_mingguan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `poinDiperoleh` int NOT NULL,
  `deskripsi` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referensiID` int DEFAULT NULL COMMENT 'ID dari aktivitas terkait',
  `tanggalPeroleh` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riwayatpoin`
--

INSERT INTO `riwayatpoin` (`riwayatID`, `userID`, `tipeAktivitas`, `poinDiperoleh`, `deskripsi`, `referensiID`, `tanggalPeroleh`) VALUES
(1, 4, 'food_logging', 10, 'Catat makanan harian – 20 Jan', 4, '2026-01-20 19:00:00'),
(2, 4, 'selesai_tantangan', 50, 'Selesai: Tantangan Sarapan Sehat 7 Hari', 1, '2026-01-19 23:59:00'),
(3, 4, 'baca_edukasi', 5, 'Baca artikel: Nutrisi Penting Remaja', 4, '2026-01-10 16:30:00'),
(4, 6, 'food_logging', 10, 'Catat makanan harian – 25 Jan', NULL, '2026-01-25 18:30:00'),
(5, 6, 'selesai_tantangan', 40, 'Selesai: Tantangan Aktivitas Fisik', 3, '2026-01-26 23:59:00'),
(6, 5, 'baca_edukasi', 5, 'Baca artikel: Resep Bergizi Balita', 2, '2026-01-08 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tantangan`
--

CREATE TABLE `tantangan` (
  `tantanganID` int NOT NULL,
  `namaTantangan` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipeTarget` enum('nutrisi','aktivitas','pemantauan','edukasi','sosial') COLLATE utf8mb4_unicode_ci NOT NULL,
  `targetValue` decimal(10,2) DEFAULT NULL COMMENT 'nilai target yang harus dicapai',
  `satuanTarget` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periodeAktif` enum('harian','mingguan','bulanan','custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalMulai` date NOT NULL,
  `tanggalSelesai` date NOT NULL,
  `poinReward` int DEFAULT '10',
  `badgeRewardID` int DEFAULT NULL COMMENT 'badge yang didapat jika selesai',
  `targetPeserta` enum('remaja','orangtua','semua') COLLATE utf8mb4_unicode_ci DEFAULT 'remaja'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tantangan`
--

INSERT INTO `tantangan` (`tantanganID`, `namaTantangan`, `deskripsi`, `tipeTarget`, `targetValue`, `satuanTarget`, `periodeAktif`, `tanggalMulai`, `tanggalSelesai`, `poinReward`, `badgeRewardID`, `targetPeserta`) VALUES
(1, 'Tantangan Sarapan Sehat 7 Hari', 'Catat sarapan bergizi selama 7 hari berturut-turut untuk mendapatkan badge Food Logger.', 'nutrisi', 7.00, 'hari', 'mingguan', '2026-01-13', '2026-01-19', 50, 2, 'remaja'),
(2, 'Tantangan Baca Artikel Edukasi', 'Baca dan selesaikan 5 artikel edukasi dalam satu bulan.', 'edukasi', 5.00, 'artikel', 'bulanan', '2026-01-01', '2026-01-31', 30, 4, 'semua'),
(3, 'Tantangan Aktivitas Fisik Mingguan', 'Lakukan aktivitas fisik minimal 30 menit sebanyak 3 kali dalam seminggu.', 'aktivitas', 3.00, 'sesi', 'mingguan', '2026-01-20', '2026-01-26', 40, NULL, 'remaja'),
(4, 'Tantangan Nutrisi Seimbang Bulanan', 'Penuhi target nutrisi harian selama 14 hari dalam satu bulan.', 'nutrisi', 14.00, 'hari', 'bulanan', '2026-01-01', '2026-01-31', 150, 7, 'remaja');

-- --------------------------------------------------------

--
-- Table structure for table `tenagakesehatan`
--

CREATE TABLE `tenagakesehatan` (
  `tenagaID` int NOT NULL,
  `userID` int NOT NULL,
  `namaLengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenisKelamin` enum('L','P') COLLATE utf8mb4_unicode_ci NOT NULL,
  `spesialisasi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'bidan, dokter, kader, ahli gizi',
  `nomorSTR` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Surat Tanda Registrasi',
  `instansi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'puskesmas, posyandu, klinik',
  `alamatInstansi` text COLLATE utf8mb4_unicode_ci,
  `wilayahKerjaID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenagakesehatan`
--

INSERT INTO `tenagakesehatan` (`tenagaID`, `userID`, `namaLengkap`, `jenisKelamin`, `spesialisasi`, `nomorSTR`, `instansi`, `alamatInstansi`, `wilayahKerjaID`) VALUES
(1, 7, 'Dr. Kartika Dewi', 'P', 'bidan', 'STR/BIDAN/2019/0012', 'Puskesmas Palangka', 'Jl. Diponegoro No. 5, Palangka Raya', 4),
(2, 8, 'Nanda Prasetya', 'L', 'ahli gizi', 'STR/GIZI/2020/0045', 'Posyandu Melati', 'Jl. Pandanaran No. 8, Semarang', 1),
(3, 9, 'Dr. Samsul Hadi', 'L', 'dokter', 'STR/DOKTER/2018/0078', 'Puskesmas Banyumanik', 'Jl. Banyumanik No. 3, Semarang', 2);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passwordHash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('orangtua','ibu_hamil','remaja','tenaga_kesehatan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggalRegistrasi` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggalUpdate` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `statusAktif` tinyint(1) DEFAULT '1',
  `nomorTelepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fotoProfile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `email`, `passwordHash`, `role`, `tanggalRegistrasi`, `tanggalUpdate`, `statusAktif`, `nomorTelepon`, `fotoProfile`) VALUES
(1, 'siti.rahayu@email.com', '$2b$12$hash_siti_rahayu_orangtua', 'orangtua', '2025-10-15 08:30:00', '2026-02-03 22:39:40', 1, '081234567890', 'foto_siti.jpg'),
(2, 'budi.santoso@email.com', '$2b$12$hash_budi_santoso_orangtua', 'orangtua', '2025-10-20 10:00:00', '2026-02-03 22:39:40', 1, '082345678901', 'foto_budi.jpg'),
(3, 'dewi.lestari@email.com', '$2b$12$hash_dewi_lestari_orangtua', 'orangtua', '2025-11-01 09:15:00', '2026-02-03 22:39:40', 1, '083456789012', 'foto_dewi.jpg'),
(4, 'rizky.pratama@email.com', '$2b$12$hash_rizky_pratama_remaja', 'remaja', '2025-11-05 14:00:00', '2026-02-03 22:39:40', 1, '084567890123', 'foto_rizky.jpg'),
(5, 'rina.sari@email.com', '$2b$12$hash_rina_sari_remaja', 'remaja', '2025-11-10 11:30:00', '2026-02-03 22:39:40', 1, '085678901234', 'foto_rina.jpg'),
(6, 'kevin..@email.com', '$2b$12$hash_kevin_nugroho_remaja', 'remaja', '2025-11-12 16:45:00', '2026-02-03 22:39:40', 1, '086789012345', 'foto_kevin.jpg'),
(7, 'dr.kartika.bidan@email.com', '$2b$12$hash_dr_kartika_tenaga', 'tenaga_kesehatan', '2025-09-01 07:00:00', '2026-02-03 22:39:40', 1, '087890123456', 'foto_kartika.jpg'),
(8, 'nanda.ahligizi@email.com', '$2b$12$hash_nanda_ahligizi_tenaga', 'tenaga_kesehatan', '2025-09-05 08:00:00', '2026-02-03 22:39:40', 1, '088901234567', 'foto_nanda.jpg'),
(9, 'samsul.dokter@email.com', '$2b$12$hash_samsul_dokter_tenaga', 'tenaga_kesehatan', '2025-09-10 09:00:00', '2026-02-03 22:39:40', 1, '089012345678', 'foto_samsul.jpg');

--
-- Triggers `user`
--
DELIMITER $$
CREATE TRIGGER `trg_create_user_preferensi` AFTER INSERT ON `user` FOR EACH ROW BEGIN
    INSERT INTO UserPreferensi (userID) VALUES (NEW.userID);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `userbadge`
--

CREATE TABLE `userbadge` (
  `userBadgeID` int NOT NULL,
  `userID` int NOT NULL,
  `badgeID` int NOT NULL,
  `tanggalPeroleh` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `userbadge`
--

INSERT INTO `userbadge` (`userBadgeID`, `userID`, `badgeID`, `tanggalPeroleh`) VALUES
(1, 1, 1, '2025-10-16 12:00:00'),
(2, 4, 1, '2025-11-06 08:00:00'),
(3, 4, 2, '2025-12-15 20:00:00'),
(4, 6, 1, '2025-11-13 10:00:00'),
(5, 6, 4, '2026-01-15 18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `userpreferensi`
--

CREATE TABLE `userpreferensi` (
  `preferensiID` int NOT NULL,
  `userID` int NOT NULL,
  `notifPemantauan` tinyint(1) DEFAULT '1',
  `notifKonsultasi` tinyint(1) DEFAULT '1',
  `notifEdukasi` tinyint(1) DEFAULT '1',
  `notifGamifikasi` tinyint(1) DEFAULT '1',
  `bahasaPreferensi` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'id',
  `temaPilihan` enum('light','dark','auto') COLLATE utf8mb4_unicode_ci DEFAULT 'light',
  `profilPublik` tinyint(1) DEFAULT '0',
  `bagikanDataAgregat` tinyint(1) DEFAULT '1',
  `settingJSON` text COLLATE utf8mb4_unicode_ci COMMENT 'setting tambahan dalam JSON'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `userpreferensi`
--

INSERT INTO `userpreferensi` (`preferensiID`, `userID`, `notifPemantauan`, `notifKonsultasi`, `notifEdukasi`, `notifGamifikasi`, `bahasaPreferensi`, `temaPilihan`, `profilPublik`, `bagikanDataAgregat`, `settingJSON`) VALUES
(1, 1, 1, 1, 1, 0, 'id', 'light', 0, 1, '{\"notif_sound\":\"default\",\"font_size\":\"medium\"}'),
(2, 2, 1, 1, 1, 1, 'id', 'dark', 0, 1, '{\"notif_sound\":\"ringtone_1\",\"font_size\":\"large\"}'),
(3, 3, 1, 1, 0, 1, 'id', 'light', 1, 0, '{\"notif_sound\":\"default\",\"font_size\":\"medium\"}'),
(4, 4, 1, 0, 1, 1, 'id', 'dark', 1, 1, '{\"notif_sound\":\"ringtone_2\",\"font_size\":\"small\"}'),
(5, 5, 1, 1, 1, 1, 'id', 'light', 0, 1, '{\"notif_sound\":\"default\",\"font_size\":\"medium\"}'),
(6, 6, 0, 0, 1, 1, 'id', 'auto', 1, 1, '{\"notif_sound\":\"silent\",\"font_size\":\"large\"}'),
(7, 7, 1, 1, 1, 0, 'id', 'light', 0, 0, '{\"notif_sound\":\"default\",\"font_size\":\"medium\"}'),
(8, 8, 1, 1, 1, 1, 'id', 'dark', 0, 1, '{\"notif_sound\":\"ringtone_1\",\"font_size\":\"medium\"}'),
(9, 9, 1, 1, 0, 0, 'id', 'light', 0, 1, '{\"notif_sound\":\"default\",\"font_size\":\"medium\"}');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_dashboard_anak`
-- (See below for the actual view)
--
CREATE TABLE `vw_dashboard_anak` (
`anakID` int
,`namaLengkap` varchar(100)
,`jenisKelamin` enum('L','P')
,`tanggalLahir` date
,`usiaBulan` bigint
,`usiaTahun` bigint
,`beratBadan` decimal(5,2)
,`tinggiBadan` decimal(5,2)
,`zScoreTinggiUsia` decimal(4,2)
,`zScoreBeratUsia` decimal(4,2)
,`kategoriStatusGizi` enum('gizi_buruk','gizi_kurang','gizi_baik','berisiko_gizi_lebih','gizi_lebih','obesitas')
,`kategoriStunting` enum('sangat_pendek','pendek','normal','tinggi')
,`tanggalPengukuranTerakhir` date
,`namaOrangTua` varchar(100)
,`emailOrangTua` varchar(100)
,`teleponOrangTua` varchar(20)
,`jumlahAlertAktif` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_dashboard_tenaga_kesehatan`
-- (See below for the actual view)
--
CREATE TABLE `vw_dashboard_tenaga_kesehatan` (
`tenagaID` int
,`namaTenagaKesehatan` varchar(100)
,`spesialisasi` varchar(100)
,`namaWilayah` varchar(100)
,`totalPasien` bigint
,`alertKritis` bigint
,`konsultasiHariIni` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_leaderboard_remaja`
-- (See below for the actual view)
--
CREATE TABLE `vw_leaderboard_remaja` (
`remajaID` int
,`nama` varchar(100)
,`totalPoin` int
,`level` int
,`jumlahBadge` bigint
,`tantanganSelesai` bigint
,`ranking` bigint unsigned
);

-- --------------------------------------------------------

--
-- Table structure for table `wilayahkerja`
--

CREATE TABLE `wilayahkerja` (
  `wilayahID` int NOT NULL,
  `namaWilayah` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenisWilayah` enum('posyandu','puskesmas','kelurahan','kecamatan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provinsi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kecamatan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelurahan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `koordinatLat` decimal(10,8) DEFAULT NULL,
  `koordinatLong` decimal(11,8) DEFAULT NULL,
  `jumlahPenduduk` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wilayahkerja`
--

INSERT INTO `wilayahkerja` (`wilayahID`, `namaWilayah`, `jenisWilayah`, `provinsi`, `kota`, `kecamatan`, `kelurahan`, `koordinatLat`, `koordinatLong`, `jumlahPenduduk`) VALUES
(1, 'Posyandu Melati', 'posyandu', 'Jawa Tengah', 'Semarang', 'Semarang Tengah', 'Pandanaran', NULL, NULL, NULL),
(2, 'Puskesmas Banyumanik', 'puskesmas', 'Jawa Tengah', 'Semarang', 'Banyumanik', 'Banyumanik', NULL, NULL, NULL),
(3, 'Posyandu Kenanga', 'posyandu', 'Jawa Timur', 'Surabaya', 'Gubeng', 'Airlangga', NULL, NULL, NULL),
(4, 'Puskesmas Palangka', 'puskesmas', 'Kalimantan Tengah', 'Palangka Raya', 'Palangka', 'Langka', -1.53000000, 114.09000000, 45000),
(5, 'Posyandu Anggrek', 'posyandu', 'Kalimantan Tengah', 'Palangka Raya', 'Sebangau', 'Anggrek', -1.55000000, 114.10000000, 12000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alertrisiko`
--
ALTER TABLE `alertrisiko`
  ADD PRIMARY KEY (`alertID`),
  ADD KEY `tenagaKesehatanID` (`tenagaKesehatanID`),
  ADD KEY `idx_anak` (`anakID`),
  ADD KEY `idx_remaja` (`remajaID`),
  ADD KEY `idx_ibu_hamil` (`ibuHamilID`),
  ADD KEY `idx_jenis` (`jenisRisiko`),
  ADD KEY `idx_tingkat` (`tingkatRisiko`),
  ADD KEY `idx_status` (`statusTindakLanjut`),
  ADD KEY `idx_alert_status_tingkat` (`statusTindakLanjut`,`tingkatRisiko`);

--
-- Indexes for table `anak`
--
ALTER TABLE `anak`
  ADD PRIMARY KEY (`anakID`),
  ADD KEY `idx_orangtua` (`orangtuaID`),
  ADD KEY `idx_tanggal_lahir` (`tanggalLahir`),
  ADD KEY `idx_anak_orangtua_aktif` (`orangtuaID`,`statusAktif`);

--
-- Indexes for table `asupanmakanan`
--
ALTER TABLE `asupanmakanan`
  ADD PRIMARY KEY (`asupanID`),
  ADD KEY `idx_nutrisi` (`nutrisiID`),
  ADD KEY `idx_waktu` (`waktuMakan`);

--
-- Indexes for table `badge`
--
ALTER TABLE `badge`
  ADD PRIMARY KEY (`badgeID`),
  ADD KEY `idx_kategori` (`kategori`);

--
-- Indexes for table `dataantropometri`
--
ALTER TABLE `dataantropometri`
  ADD PRIMARY KEY (`dataID`),
  ADD KEY `petugasID` (`petugasID`),
  ADD KEY `idx_anak` (`anakID`),
  ADD KEY `idx_remaja` (`remajaID`),
  ADD KEY `idx_tanggal` (`tanggalPengukuran`),
  ADD KEY `idx_kategori` (`kategoriStatusGizi`),
  ADD KEY `idx_antropometri_anak_tanggal` (`anakID`,`tanggalPengukuran` DESC);

--
-- Indexes for table `datanutrisi`
--
ALTER TABLE `datanutrisi`
  ADD PRIMARY KEY (`nutrisiID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_anak` (`anakID`),
  ADD KEY `idx_tanggal` (`tanggalCatat`),
  ADD KEY `idx_nutrisi_user_tanggal` (`userID`,`tanggalCatat` DESC);

--
-- Indexes for table `ibuhamil`
--
ALTER TABLE `ibuhamil`
  ADD PRIMARY KEY (`ibuHamilID`),
  ADD KEY `idx_orangtua` (`orangtuaID`),
  ADD KEY `idx_hpl` (`tanggalHPL`);

--
-- Indexes for table `interaksikonten`
--
ALTER TABLE `interaksikonten`
  ADD PRIMARY KEY (`interaksiID`),
  ADD UNIQUE KEY `unique_user_konten_tipe` (`userID`,`kontenID`,`tipeInteraksi`),
  ADD KEY `idx_konten` (`kontenID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_tipe` (`tipeInteraksi`);

--
-- Indexes for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`konsultasiID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_tenaga` (`tenagaKesehatanID`),
  ADD KEY `idx_status` (`statusKonsultasi`),
  ADD KEY `idx_jadwal` (`tanggalJadwal`),
  ADD KEY `idx_konsultasi_jadwal_status` (`tanggalJadwal`,`statusKonsultasi`);

--
-- Indexes for table `kontenedukasi`
--
ALTER TABLE `kontenedukasi`
  ADD PRIMARY KEY (`kontenID`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `authorID` (`authorID`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_target` (`targetSegmen`),
  ADD KEY `idx_status` (`statusPublish`),
  ADD KEY `idx_publish` (`tanggalPublish`);
ALTER TABLE `kontenedukasi` ADD FULLTEXT KEY `ft_konten_search` (`judul`,`excerptSingkat`,`isiKonten`);

--
-- Indexes for table `laporankesehatan`
--
ALTER TABLE `laporankesehatan`
  ADD PRIMARY KEY (`laporanID`),
  ADD KEY `idx_tenaga` (`tenagaKesehatanID`),
  ADD KEY `idx_wilayah` (`wilayahKerjaID`),
  ADD KEY `idx_periode` (`tanggalMulai`,`tanggalSelesai`);

--
-- Indexes for table `logaktivitas`
--
ALTER TABLE `logaktivitas`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_tanggal` (`tanggalAktivitas`),
  ADD KEY `idx_aktivitas` (`aktivitas`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`notifID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_status` (`statusBaca`),
  ADD KEY `idx_prioritas` (`prioritas`),
  ADD KEY `idx_tanggal` (`tanggalKirim`);

--
-- Indexes for table `orangtua`
--
ALTER TABLE `orangtua`
  ADD PRIMARY KEY (`orangtuaID`),
  ADD KEY `idx_user` (`userID`);

--
-- Indexes for table `pemantauanjanin`
--
ALTER TABLE `pemantauanjanin`
  ADD PRIMARY KEY (`pemantauanID`),
  ADD KEY `petugasID` (`petugasID`),
  ADD KEY `idx_ibu_hamil` (`ibuHamilID`),
  ADD KEY `idx_tanggal` (`tanggalPemeriksaan`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`pesanID`),
  ADD KEY `idx_konsultasi` (`konsultasiID`),
  ADD KEY `idx_tanggal` (`tanggalKirim`);

--
-- Indexes for table `referensimakanan`
--
ALTER TABLE `referensimakanan`
  ADD PRIMARY KEY (`makananID`),
  ADD KEY `idx_nama` (`namaMakanan`),
  ADD KEY `idx_kategori` (`kategori`);
ALTER TABLE `referensimakanan` ADD FULLTEXT KEY `ft_makanan_search` (`namaMakanan`,`kategori`);

--
-- Indexes for table `rekomendasipersonal`
--
ALTER TABLE `rekomendasipersonal`
  ADD PRIMARY KEY (`rekomendasiID`),
  ADD KEY `pembuatID` (`pembuatID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_anak` (`anakID`),
  ADD KEY `idx_jenis` (`jenisRekomendasi`),
  ADD KEY `idx_prioritas` (`prioritas`);

--
-- Indexes for table `remaja`
--
ALTER TABLE `remaja`
  ADD PRIMARY KEY (`remajaID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_poin` (`totalPoin`);

--
-- Indexes for table `remajatantangan`
--
ALTER TABLE `remajatantangan`
  ADD PRIMARY KEY (`partisipasiID`),
  ADD UNIQUE KEY `unique_remaja_tantangan` (`remajaID`,`tantanganID`),
  ADD KEY `idx_remaja` (`remajaID`),
  ADD KEY `idx_tantangan` (`tantanganID`),
  ADD KEY `idx_status` (`statusSelesai`);

--
-- Indexes for table `riwayatpoin`
--
ALTER TABLE `riwayatpoin`
  ADD PRIMARY KEY (`riwayatID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_tanggal` (`tanggalPeroleh`),
  ADD KEY `idx_tipe` (`tipeAktivitas`);

--
-- Indexes for table `tantangan`
--
ALTER TABLE `tantangan`
  ADD PRIMARY KEY (`tantanganID`),
  ADD KEY `badgeRewardID` (`badgeRewardID`),
  ADD KEY `idx_periode` (`periodeAktif`),
  ADD KEY `idx_tanggal` (`tanggalMulai`,`tanggalSelesai`),
  ADD KEY `idx_target` (`targetPeserta`);

--
-- Indexes for table `tenagakesehatan`
--
ALTER TABLE `tenagakesehatan`
  ADD PRIMARY KEY (`tenagaID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `wilayahKerjaID` (`wilayahKerjaID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`statusAktif`);

--
-- Indexes for table `userbadge`
--
ALTER TABLE `userbadge`
  ADD PRIMARY KEY (`userBadgeID`),
  ADD UNIQUE KEY `unique_user_badge` (`userID`,`badgeID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_badge` (`badgeID`);

--
-- Indexes for table `userpreferensi`
--
ALTER TABLE `userpreferensi`
  ADD PRIMARY KEY (`preferensiID`),
  ADD UNIQUE KEY `userID` (`userID`);

--
-- Indexes for table `wilayahkerja`
--
ALTER TABLE `wilayahkerja`
  ADD PRIMARY KEY (`wilayahID`),
  ADD KEY `idx_wilayah` (`provinsi`,`kota`,`kecamatan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alertrisiko`
--
ALTER TABLE `alertrisiko`
  MODIFY `alertID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `anak`
--
ALTER TABLE `anak`
  MODIFY `anakID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `asupanmakanan`
--
ALTER TABLE `asupanmakanan`
  MODIFY `asupanID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `badge`
--
ALTER TABLE `badge`
  MODIFY `badgeID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `dataantropometri`
--
ALTER TABLE `dataantropometri`
  MODIFY `dataID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `datanutrisi`
--
ALTER TABLE `datanutrisi`
  MODIFY `nutrisiID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ibuhamil`
--
ALTER TABLE `ibuhamil`
  MODIFY `ibuHamilID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `interaksikonten`
--
ALTER TABLE `interaksikonten`
  MODIFY `interaksiID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `konsultasiID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kontenedukasi`
--
ALTER TABLE `kontenedukasi`
  MODIFY `kontenID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `laporankesehatan`
--
ALTER TABLE `laporankesehatan`
  MODIFY `laporanID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logaktivitas`
--
ALTER TABLE `logaktivitas`
  MODIFY `logID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `notifID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orangtua`
--
ALTER TABLE `orangtua`
  MODIFY `orangtuaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pemantauanjanin`
--
ALTER TABLE `pemantauanjanin`
  MODIFY `pemantauanID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `pesanID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `referensimakanan`
--
ALTER TABLE `referensimakanan`
  MODIFY `makananID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rekomendasipersonal`
--
ALTER TABLE `rekomendasipersonal`
  MODIFY `rekomendasiID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `remaja`
--
ALTER TABLE `remaja`
  MODIFY `remajaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `remajatantangan`
--
ALTER TABLE `remajatantangan`
  MODIFY `partisipasiID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `riwayatpoin`
--
ALTER TABLE `riwayatpoin`
  MODIFY `riwayatID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tantangan`
--
ALTER TABLE `tantangan`
  MODIFY `tantanganID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tenagakesehatan`
--
ALTER TABLE `tenagakesehatan`
  MODIFY `tenagaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `userbadge`
--
ALTER TABLE `userbadge`
  MODIFY `userBadgeID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `userpreferensi`
--
ALTER TABLE `userpreferensi`
  MODIFY `preferensiID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wilayahkerja`
--
ALTER TABLE `wilayahkerja`
  MODIFY `wilayahID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

--
-- Structure for view `vw_dashboard_anak`
--
DROP TABLE IF EXISTS `vw_dashboard_anak`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_dashboard_anak`  AS SELECT `a`.`anakID` AS `anakID`, `a`.`namaLengkap` AS `namaLengkap`, `a`.`jenisKelamin` AS `jenisKelamin`, `a`.`tanggalLahir` AS `tanggalLahir`, timestampdiff(MONTH,`a`.`tanggalLahir`,curdate()) AS `usiaBulan`, timestampdiff(YEAR,`a`.`tanggalLahir`,curdate()) AS `usiaTahun`, `da`.`beratBadan` AS `beratBadan`, `da`.`tinggiBadan` AS `tinggiBadan`, `da`.`zScoreTinggiUsia` AS `zScoreTinggiUsia`, `da`.`zScoreBeratUsia` AS `zScoreBeratUsia`, `da`.`kategoriStatusGizi` AS `kategoriStatusGizi`, `da`.`kategoriStunting` AS `kategoriStunting`, `da`.`tanggalPengukuran` AS `tanggalPengukuranTerakhir`, `ot`.`namaLengkap` AS `namaOrangTua`, `u`.`email` AS `emailOrangTua`, `u`.`nomorTelepon` AS `teleponOrangTua`, (select count(0) from `alertrisiko` `ar` where ((`ar`.`anakID` = `a`.`anakID`) and (`ar`.`statusTindakLanjut` <> 'selesai'))) AS `jumlahAlertAktif` FROM (((`anak` `a` left join `orangtua` `ot` on((`a`.`orangtuaID` = `ot`.`orangtuaID`))) left join `user` `u` on((`ot`.`userID` = `u`.`userID`))) left join (select `da1`.`anakID` AS `anakID`,`da1`.`beratBadan` AS `beratBadan`,`da1`.`tinggiBadan` AS `tinggiBadan`,`da1`.`zScoreTinggiUsia` AS `zScoreTinggiUsia`,`da1`.`zScoreBeratUsia` AS `zScoreBeratUsia`,`da1`.`kategoriStatusGizi` AS `kategoriStatusGizi`,`da1`.`kategoriStunting` AS `kategoriStunting`,`da1`.`tanggalPengukuran` AS `tanggalPengukuran` from `dataantropometri` `da1` where (`da1`.`tanggalPengukuran` = (select max(`da2`.`tanggalPengukuran`) from `dataantropometri` `da2` where (`da2`.`anakID` = `da1`.`anakID`)))) `da` on((`a`.`anakID` = `da`.`anakID`))) WHERE (`a`.`statusAktif` = true) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_dashboard_tenaga_kesehatan`
--
DROP TABLE IF EXISTS `vw_dashboard_tenaga_kesehatan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_dashboard_tenaga_kesehatan`  AS SELECT `tk`.`tenagaID` AS `tenagaID`, `tk`.`namaLengkap` AS `namaTenagaKesehatan`, `tk`.`spesialisasi` AS `spesialisasi`, `wk`.`namaWilayah` AS `namaWilayah`, (select count(distinct `a`.`anakID`) from ((`anak` `a` join `orangtua` `ot` on((`a`.`orangtuaID` = `ot`.`orangtuaID`))) join `user` `u` on((`ot`.`userID` = `u`.`userID`))) where (`a`.`statusAktif` = true)) AS `totalPasien`, (select count(0) from `alertrisiko` `ar` where ((`ar`.`statusTindakLanjut` in ('menunggu','ditinjau')) and (`ar`.`tingkatRisiko` in ('tinggi','kritis')))) AS `alertKritis`, (select count(0) from `konsultasi` `k` where ((`k`.`tenagaKesehatanID` = `tk`.`tenagaID`) and (cast(`k`.`tanggalJadwal` as date) = curdate()) and (`k`.`statusKonsultasi` in ('dijadwalkan','berlangsung')))) AS `konsultasiHariIni` FROM (`tenagakesehatan` `tk` left join `wilayahkerja` `wk` on((`tk`.`wilayahKerjaID` = `wk`.`wilayahID`))) ;

-- --------------------------------------------------------

--
-- Structure for view `vw_leaderboard_remaja`
--
DROP TABLE IF EXISTS `vw_leaderboard_remaja`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_leaderboard_remaja`  AS SELECT `leaderboard_sub`.`remajaID` AS `remajaID`, `leaderboard_sub`.`nama` AS `nama`, `leaderboard_sub`.`totalPoin` AS `totalPoin`, `leaderboard_sub`.`level` AS `level`, `leaderboard_sub`.`jumlahBadge` AS `jumlahBadge`, `leaderboard_sub`.`tantanganSelesai` AS `tantanganSelesai`, `leaderboard_sub`.`ranking` AS `ranking` FROM (select `r`.`remajaID` AS `remajaID`,`r`.`nama` AS `nama`,`r`.`totalPoin` AS `totalPoin`,`r`.`level` AS `level`,(select count(0) from (`userbadge` `ub` join `user` `u` on((`ub`.`userID` = `u`.`userID`))) where (`u`.`userID` = (select `remaja`.`userID` from `remaja` where (`remaja`.`remajaID` = `r`.`remajaID`)))) AS `jumlahBadge`,(select count(0) from `remajatantangan` `rt` where ((`rt`.`remajaID` = `r`.`remajaID`) and (`rt`.`statusSelesai` = true))) AS `tantanganSelesai`,rank() OVER (ORDER BY `r`.`totalPoin` desc )  AS `ranking` from `remaja` `r` order by `r`.`totalPoin` desc limit 100) AS `leaderboard_sub` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alertrisiko`
--
ALTER TABLE `alertrisiko`
  ADD CONSTRAINT `alertrisiko_ibfk_1` FOREIGN KEY (`anakID`) REFERENCES `anak` (`anakID`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertrisiko_ibfk_2` FOREIGN KEY (`remajaID`) REFERENCES `remaja` (`remajaID`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertrisiko_ibfk_3` FOREIGN KEY (`ibuHamilID`) REFERENCES `ibuhamil` (`ibuHamilID`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertrisiko_ibfk_4` FOREIGN KEY (`tenagaKesehatanID`) REFERENCES `tenagakesehatan` (`tenagaID`) ON DELETE SET NULL;

--
-- Constraints for table `anak`
--
ALTER TABLE `anak`
  ADD CONSTRAINT `anak_ibfk_1` FOREIGN KEY (`orangtuaID`) REFERENCES `orangtua` (`orangtuaID`) ON DELETE CASCADE;

--
-- Constraints for table `asupanmakanan`
--
ALTER TABLE `asupanmakanan`
  ADD CONSTRAINT `asupanmakanan_ibfk_1` FOREIGN KEY (`nutrisiID`) REFERENCES `datanutrisi` (`nutrisiID`) ON DELETE CASCADE;

--
-- Constraints for table `dataantropometri`
--
ALTER TABLE `dataantropometri`
  ADD CONSTRAINT `dataantropometri_ibfk_1` FOREIGN KEY (`anakID`) REFERENCES `anak` (`anakID`) ON DELETE CASCADE,
  ADD CONSTRAINT `dataantropometri_ibfk_2` FOREIGN KEY (`remajaID`) REFERENCES `remaja` (`remajaID`) ON DELETE CASCADE,
  ADD CONSTRAINT `dataantropometri_ibfk_3` FOREIGN KEY (`petugasID`) REFERENCES `tenagakesehatan` (`tenagaID`) ON DELETE SET NULL;

--
-- Constraints for table `datanutrisi`
--
ALTER TABLE `datanutrisi`
  ADD CONSTRAINT `datanutrisi_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `datanutrisi_ibfk_2` FOREIGN KEY (`anakID`) REFERENCES `anak` (`anakID`) ON DELETE CASCADE;

--
-- Constraints for table `ibuhamil`
--
ALTER TABLE `ibuhamil`
  ADD CONSTRAINT `ibuhamil_ibfk_1` FOREIGN KEY (`orangtuaID`) REFERENCES `orangtua` (`orangtuaID`) ON DELETE CASCADE;

--
-- Constraints for table `interaksikonten`
--
ALTER TABLE `interaksikonten`
  ADD CONSTRAINT `interaksikonten_ibfk_1` FOREIGN KEY (`kontenID`) REFERENCES `kontenedukasi` (`kontenID`) ON DELETE CASCADE,
  ADD CONSTRAINT `interaksikonten_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD CONSTRAINT `konsultasi_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `konsultasi_ibfk_2` FOREIGN KEY (`tenagaKesehatanID`) REFERENCES `tenagakesehatan` (`tenagaID`) ON DELETE CASCADE;

--
-- Constraints for table `kontenedukasi`
--
ALTER TABLE `kontenedukasi`
  ADD CONSTRAINT `kontenedukasi_ibfk_1` FOREIGN KEY (`authorID`) REFERENCES `tenagakesehatan` (`tenagaID`) ON DELETE SET NULL;

--
-- Constraints for table `laporankesehatan`
--
ALTER TABLE `laporankesehatan`
  ADD CONSTRAINT `laporankesehatan_ibfk_1` FOREIGN KEY (`tenagaKesehatanID`) REFERENCES `tenagakesehatan` (`tenagaID`) ON DELETE CASCADE,
  ADD CONSTRAINT `laporankesehatan_ibfk_2` FOREIGN KEY (`wilayahKerjaID`) REFERENCES `wilayahkerja` (`wilayahID`) ON DELETE SET NULL;

--
-- Constraints for table `logaktivitas`
--
ALTER TABLE `logaktivitas`
  ADD CONSTRAINT `logaktivitas_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `orangtua`
--
ALTER TABLE `orangtua`
  ADD CONSTRAINT `orangtua_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `pemantauanjanin`
--
ALTER TABLE `pemantauanjanin`
  ADD CONSTRAINT `pemantauanjanin_ibfk_1` FOREIGN KEY (`ibuHamilID`) REFERENCES `ibuhamil` (`ibuHamilID`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemantauanjanin_ibfk_2` FOREIGN KEY (`petugasID`) REFERENCES `tenagakesehatan` (`tenagaID`) ON DELETE SET NULL;

--
-- Constraints for table `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `pesan_ibfk_1` FOREIGN KEY (`konsultasiID`) REFERENCES `konsultasi` (`konsultasiID`) ON DELETE CASCADE;

--
-- Constraints for table `rekomendasipersonal`
--
ALTER TABLE `rekomendasipersonal`
  ADD CONSTRAINT `rekomendasipersonal_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `rekomendasipersonal_ibfk_2` FOREIGN KEY (`anakID`) REFERENCES `anak` (`anakID`) ON DELETE CASCADE,
  ADD CONSTRAINT `rekomendasipersonal_ibfk_3` FOREIGN KEY (`pembuatID`) REFERENCES `tenagakesehatan` (`tenagaID`) ON DELETE SET NULL;

--
-- Constraints for table `remaja`
--
ALTER TABLE `remaja`
  ADD CONSTRAINT `remaja_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `remajatantangan`
--
ALTER TABLE `remajatantangan`
  ADD CONSTRAINT `remajatantangan_ibfk_1` FOREIGN KEY (`remajaID`) REFERENCES `remaja` (`remajaID`) ON DELETE CASCADE,
  ADD CONSTRAINT `remajatantangan_ibfk_2` FOREIGN KEY (`tantanganID`) REFERENCES `tantangan` (`tantanganID`) ON DELETE CASCADE;

--
-- Constraints for table `riwayatpoin`
--
ALTER TABLE `riwayatpoin`
  ADD CONSTRAINT `riwayatpoin_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `tantangan`
--
ALTER TABLE `tantangan`
  ADD CONSTRAINT `tantangan_ibfk_1` FOREIGN KEY (`badgeRewardID`) REFERENCES `badge` (`badgeID`) ON DELETE SET NULL;

--
-- Constraints for table `tenagakesehatan`
--
ALTER TABLE `tenagakesehatan`
  ADD CONSTRAINT `tenagakesehatan_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenagakesehatan_ibfk_2` FOREIGN KEY (`wilayahKerjaID`) REFERENCES `wilayahkerja` (`wilayahID`) ON DELETE SET NULL;

--
-- Constraints for table `userbadge`
--
ALTER TABLE `userbadge`
  ADD CONSTRAINT `userbadge_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `userbadge_ibfk_2` FOREIGN KEY (`badgeID`) REFERENCES `badge` (`badgeID`) ON DELETE CASCADE;

--
-- Constraints for table `userpreferensi`
--
ALTER TABLE `userpreferensi`
  ADD CONSTRAINT `userpreferensi_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
