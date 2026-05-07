-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2026 at 04:58 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_toko_sembako`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_laporan_penjualan_periode` (IN `p_tanggal_mulai` DATE, IN `p_tanggal_selesai` DATE)   BEGIN
    SELECT
        t.tanggal,
        t.kode_transaksi,
        p.nama_pelanggan,
        u.nama_lengkap AS kasir,
        t.total_item,
        t.total_bayar,
        t.metode_bayar
    FROM transaksi_penjualan t
    LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    JOIN users u ON t.id_user = u.id_user
    WHERE t.status = 'selesai'
      AND t.tanggal BETWEEN p_tanggal_mulai AND p_tanggal_selesai
    ORDER BY t.tanggal, t.kode_transaksi;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tambah_transaksi_penjualan` (IN `p_kode_transaksi` VARCHAR(30), IN `p_tanggal` DATE, IN `p_waktu` TIME, IN `p_id_pelanggan` INT, IN `p_id_user` INT, IN `p_total_item` INT, IN `p_subtotal` DECIMAL(15,2), IN `p_diskon` DECIMAL(15,2), IN `p_pajak` DECIMAL(15,2), IN `p_total_bayar` DECIMAL(15,2), IN `p_tunai` DECIMAL(15,2), IN `p_kembalian` DECIMAL(15,2), IN `p_metode_bayar` ENUM('tunai','debit','kredit','transfer','qris'), IN `p_keterangan` TEXT)   BEGIN
    INSERT INTO transaksi_penjualan (
        kode_transaksi, tanggal, waktu,
        id_pelanggan, id_user, total_item,
        subtotal, diskon, pajak,
        total_bayar, tunai, kembalian,
        metode_bayar, status, keterangan
    ) VALUES (
        p_kode_transaksi, p_tanggal, p_waktu,
        p_id_pelanggan, p_id_user, p_total_item,
        p_subtotal, p_diskon, p_pajak,
        p_total_bayar, p_tunai, p_kembalian,
        p_metode_bayar, 'selesai', p_keterangan
    );
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_nilai_stok_produk` (`p_id_produk` INT) RETURNS DECIMAL(15,2) DETERMINISTIC BEGIN
    DECLARE v_nilai DECIMAL(15,2);

    SELECT stok * harga_beli
    INTO v_nilai
    FROM produk
    WHERE id_produk = p_id_produk;

    RETURN IFNULL(v_nilai, 0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_total_omset_harian` (`p_tanggal` DATE) RETURNS DECIMAL(15,2) DETERMINISTIC BEGIN
    DECLARE v_total DECIMAL(15,2);

    SELECT IFNULL(SUM(total_bayar), 0)
    INTO v_total
    FROM transaksi_penjualan
    WHERE tanggal = p_tanggal
      AND status = 'selesai';

    RETURN v_total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_stok_masuk`
--

CREATE TABLE `detail_stok_masuk` (
  `id_detail_masuk` int(11) NOT NULL,
  `id_stok_masuk` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `detail_stok_masuk`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_detail_stok_masuk` AFTER INSERT ON `detail_stok_masuk` FOR EACH ROW BEGIN
    UPDATE produk
    SET stok = stok + NEW.jumlah
    WHERE id_produk = NEW.id_produk;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `diskon` decimal(12,2) DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `nama_produk`, `harga_jual`, `jumlah`, `diskon`, `subtotal`) VALUES
(16, 1, 1, 'Beras Premium Raja Platinum 5Kg', 85000.00, 1, 0.00, 85000.00),
(17, 2, 2, 'Beras Medium IR64 5 Kg', 72000.00, 6, 0.00, 432000.00),
(18, 6, 6, 'Minyak Goreng Sania 1 Liter', 20500.00, 10, 0.00, 205000.00),
(19, 7, 11, 'Tepung Terigu Segitiga Biru 1 Kg', 14000.00, 5, 0.00, 70000.00),
(20, 8, 11, 'Tepung Terigu Segitiga Biru 1 Kg', 14000.00, 5, 0.00, 70000.00),
(21, 9, 3, 'Beras Si Mantul 10 Kg', 155000.00, 4, 0.00, 620000.00),
(22, 10, 1, 'Beras Premium Raja Platinum 5Kg', 85000.00, 1, 0.00, 85000.00),
(23, 11, 1, 'Beras Premium Raja Platinum 5Kg', 85000.00, 3, 0.00, 255000.00);

--
-- Triggers `detail_transaksi`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_detail_transaksi` AFTER INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
    UPDATE produk
    SET stok = stok - NEW.jumlah
    WHERE id_produk = NEW.id_produk;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_barang`
--

CREATE TABLE `kategori_barang` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori_barang`
--

INSERT INTO `kategori_barang` (`id_kategori`, `nama_kategori`, `keterangan`, `created_at`) VALUES
(1, 'Sembako Pokok', 'Beras, minyak, gula, telur, dan kebutuhan pokok lainnya', '2025-12-01 17:07:56'),
(2, 'Minuman', 'Teh, kopi, susu, air mineral, dan minuman ringan', '2025-12-01 17:07:56'),
(3, 'Bumbu dan Rempah', 'Garam, lada, kecap, dan bumbu masak', '2025-12-01 17:07:56'),
(4, 'Sabun & Pembersih', 'Deterjen, sabun mandi, shampo, pembersih lantai', '2025-12-01 17:07:56');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `kode_pelanggan` varchar(20) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tipe` enum('reguler','member') DEFAULT 'reguler',
  `poin` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `kode_pelanggan`, `nama_pelanggan`, `alamat`, `telepon`, `email`, `jenis_kelamin`, `tipe`, `poin`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PEL001', 'Umum', '-', '-', NULL, 'L', 'reguler', 0, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(2, 'PEL002', 'Ibu Dewi Kusuma', 'Jl. Merdeka No. 10, Bogor', '081223344556', NULL, 'P', 'member', 150, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(3, 'PEL003', 'Bapak Andi Saputra', 'Jl. Sudirman No. 25, Bogor', '081334455667', NULL, 'L', 'member', 250, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(4, 'PEL004', 'Ibu Ratna Wati', 'Jl. Pahlawan No. 15, Bogor', '081445566778', NULL, 'P', 'member', 100, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(5, 'PEL005', 'Bapak Joko Widodo', 'Jl. Gatot Subroto, Bogor', '081556677889', NULL, 'L', 'reguler', 0, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56');

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `kategori_pengeluaran` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `kode_produk` varchar(20) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `id_satuan` int(11) NOT NULL,
  `merk` varchar(50) DEFAULT NULL,
  `harga_beli` decimal(12,2) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `diskon` decimal(5,2) DEFAULT 0.00,
  `stok` int(11) DEFAULT 0,
  `stok_minimum` int(11) DEFAULT 5,
  `keterangan` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `kode_produk`, `barcode`, `nama_produk`, `id_kategori`, `id_satuan`, `merk`, `harga_beli`, `harga_jual`, `diskon`, `stok`, `stok_minimum`, `keterangan`, `gambar`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SMK001', '8991002101012', 'Beras Premium Raja Platinum 5Kg', 1, 9, 'Raja Platinum', 70000.00, 85000.00, 0.00, 45, 10, '\r\n', 'beras.jpg', 'aktif', '2025-12-01 17:07:56', '2026-05-06 15:59:27'),
(2, 'SMK002', '8991002101029', 'Beras Medium IR64 5 Kg', 1, 9, 'Topi Koki', 60000.00, 72000.00, 0.00, 54, 10, NULL, 'berasir.jpg', 'aktif', '2025-12-01 17:07:56', '2026-05-06 13:34:40'),
(3, 'SMK003', '8991002101036', 'Beras Si Mantul 10 Kg', 1, 9, 'Si Mantul', 135000.00, 155000.00, 0.00, 26, 5, NULL, '', 'aktif', '2025-12-01 17:07:56', '2025-12-09 19:59:40'),
(4, 'SMK004', '8992771101015', 'Minyak Goreng Bimoli 1 Liter', 1, 10, 'Bimoli', 16000.00, 19500.00, 0.00, 80, 15, NULL, '', 'aktif', '2025-12-01 17:07:56', '2025-12-09 03:04:12'),
(5, 'SMK005', '8992771101022', 'Minyak Goreng Sunco 2 Liter', 1, 10, 'Sunco', 35000.00, 42000.00, 0.00, 60, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(6, 'SMK006', '8992771101039', 'Minyak Goreng Sania 1 Liter', 1, 10, 'Sania', 17000.00, 20500.00, 0.00, 60, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-09 19:09:41'),
(7, 'SMK007', '8992753102017', 'Gula Pasir Gulaku 1 Kg', 1, 1, 'Gulaku', 15000.00, 18500.00, 0.00, 100, 20, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(8, 'SMK008', '8992753102024', 'Gula Pasir Premium SHS 1 Kg', 1, 1, 'SHS', 16000.00, 19500.00, 0.00, 50, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(9, 'SMK009', '8991001010013', 'Telur Ayam Ras 1 Kg', 1, 1, 'Segar', 26000.00, 32000.00, 0.00, 40, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(10, 'SMK010', '8991001010020', 'Telur Ayam Kampung 1 Kg', 1, 1, 'Organik', 40000.00, 50000.00, 0.00, 20, 5, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(11, 'SMK011', '8992753103014', 'Tepung Terigu Segitiga Biru 1 Kg', 1, 1, 'Bogasari', 11000.00, 14000.00, 0.00, 40, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-09 19:12:30'),
(12, 'SMK012', '8992753103021', 'Tepung Terigu Cakra Kembar 1 Kg', 1, 1, 'Bogasari', 12000.00, 15000.00, 0.00, 40, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(13, 'SMK013', '8992753104011', 'Tepung Maizena Maizenaku 100 Gram', 1, 2, 'Maizenaku', 3500.00, 5000.00, 0.00, 60, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(14, 'SMK014', '8992771102012', 'Garam Halus Refina 250 Gram', 1, 2, 'Refina', 2000.00, 3500.00, 0.00, 80, 20, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(15, 'SMK015', '8992771102029', 'Garam Beryodium Dolphin 500 Gram', 1, 2, 'Dolphin', 3500.00, 5500.00, 0.00, 70, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(16, 'MIN001', '8991002201013', 'Air Mineral Aqua 600 ml', 2, 10, 'Aqua', 2500.00, 3500.00, 0.00, 120, 30, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(17, 'MIN002', '8991002201020', 'Air Mineral Aqua 1500 ml', 2, 10, 'Aqua', 4500.00, 6000.00, 0.00, 80, 20, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(18, 'MIN003', '8991002201037', 'Air Mineral Galon Aqua 19 Liter', 2, 5, 'Aqua', 18000.00, 25000.00, 0.00, 40, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(19, 'MIN004', '8991002202014', 'Teh Celup Sariwangi 25 Kantung', 2, 6, 'Sariwangi', 7500.00, 10000.00, 0.00, 50, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(20, 'MIN005', '8991002202021', 'Teh Celup Sosro 25 Kantung', 2, 6, 'Sosro', 8000.00, 11000.00, 0.00, 45, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(21, 'MIN006', '8991002203018', 'Teh Botol Sosro 450 ml', 2, 10, 'Sosro', 4500.00, 6000.00, 0.00, 60, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(22, 'MIN007', '8991002203025', 'Teh Pucuk Harum 350 ml', 2, 10, 'Teh Pucuk', 3500.00, 5000.00, 0.00, 70, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(23, 'MIN008', '8992753201016', 'Kopi Kapal Api Special Mix Isi 10', 2, 8, 'Kapal Api', 12000.00, 15000.00, 0.00, 50, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(24, 'MIN009', '8992753201023', 'Kopi ABC Susu Isi 10', 2, 8, 'ABC', 11000.00, 14000.00, 0.00, 55, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(25, 'MIN010', '8992753201030', 'Kopi Good Day Cappuccino Isi 10', 2, 8, 'Good Day', 13000.00, 16000.00, 0.00, 45, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(26, 'MIN011', '8992771201017', 'Susu Kental Manis Indomilk 370 Gram', 2, 11, 'Indomilk', 11000.00, 14000.00, 0.00, 60, 12, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(27, 'MIN012', '8992771201024', 'Susu Kental Manis Frisian Flag 370 Gram', 2, 11, 'Frisian Flag', 11000.00, 14000.00, 0.00, 55, 12, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(28, 'MIN013', '8992771202014', 'Susu UHT Ultra Milk 1 Liter', 2, 6, 'Ultra Milk', 17000.00, 22000.00, 0.00, 40, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(29, 'MIN014', '8992753202013', 'Susu Dancow Coklat 800 Gram', 2, 6, 'Dancow', 60000.00, 72000.00, 0.00, 30, 8, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(30, 'MIN015', '8992753202020', 'Susu Bendera 400 Gram', 2, 6, 'Bendera', 30000.00, 38000.00, 0.00, 35, 8, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(31, 'BRP001', '8992771301018', 'Garam Dapur Refina 500 Gram', 3, 7, 'Refina', 3000.00, 5000.00, 0.00, 80, 20, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(32, 'BRP002', '8992753301019', 'Lada Bubuk Ladaku 50 Gram', 3, 8, 'Ladaku', 8500.00, 11500.00, 0.00, 50, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(33, 'BRP003', '8992753302016', 'Lada Hitam Koepoe Koepoe 100 Gram', 3, 8, 'Koepoe-Koepoe', 16000.00, 20000.00, 0.00, 40, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(34, 'BRP004', '8992753303013', 'Kecap Manis ABC 600 ml', 3, 10, 'ABC', 13000.00, 17000.00, 0.00, 70, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(35, 'BRP005', '8992753303020', 'Kecap Asin Bango 275 ml', 3, 10, 'Bango', 8500.00, 11500.00, 0.00, 60, 12, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(36, 'BRP006', '8992753304017', 'Saus Sambal ABC 335 ml', 3, 10, 'ABC', 10000.00, 13000.00, 0.00, 55, 12, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(37, 'BRP007', '8992753305014', 'Saus Tiram Panda 275 ml', 3, 10, 'Panda', 14000.00, 18000.00, 0.00, 45, 10, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(38, 'BRP008', '8992753306011', 'Kaldu Bubuk Royco Ayam 1 Kg', 3, 1, 'Royco', 45000.00, 55000.00, 0.00, 30, 8, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(39, 'BRP009', '8992753307018', 'Masako Rasa Ayam 250 Gram', 3, 7, 'Masako', 11000.00, 14000.00, 0.00, 60, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(40, 'BRP010', '8992753308015', 'Bumbu Racik Indofood Rendang', 3, 8, 'Indofood', 2500.00, 4000.00, 0.00, 100, 25, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(41, 'SBP001', '8992772401011', 'Deterjen Rinso Anti Noda 800 Gram', 4, 7, 'Rinso', 16000.00, 20000.00, 0.00, 60, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(42, 'SBP002', '8992772401028', 'Deterjen Daia 900 Gram', 4, 7, 'Daia', 14000.00, 18000.00, 0.00, 65, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(43, 'SBP003', '8992772402018', 'Deterjen Cair Attack 800 ml', 4, 10, 'Attack', 19000.00, 24000.00, 0.00, 50, 12, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(44, 'SBP004', '8992772403015', 'Sabun Mandi Lifebuoy 90 Gram', 4, 5, 'Lifebuoy', 3200.00, 4500.00, 0.00, 100, 25, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(45, 'SBP005', '8992772403022', 'Sabun Mandi Lux 90 Gram', 4, 5, 'Lux', 3500.00, 5000.00, 0.00, 95, 25, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(46, 'SBP006', '8992772404012', 'Sabun Cair Nuvo 450 ml', 4, 10, 'Nuvo', 14000.00, 18000.00, 0.00, 55, 12, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(47, 'SBP007', '8992772405019', 'Shampo Pantene 170 ml', 4, 8, 'Pantene', 17000.00, 22000.00, 0.00, 50, 12, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(48, 'SBP008', '8992772405026', 'Shampo Clear Men 170 ml', 4, 8, 'Clear', 18000.00, 23000.00, 0.00, 45, 12, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(49, 'SBP009', '8992772406016', 'Pembersih Lantai Wipol 800 ml', 4, 10, 'Wipol', 12000.00, 16000.00, 0.00, 60, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(50, 'SBP010', '8992772406023', 'Pembersih Lantai SOS Pembersih Serbaguna 800 ml', 4, 10, 'SOS', 11000.00, 15000.00, 0.00, 65, 15, NULL, NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56');

-- --------------------------------------------------------

--
-- Table structure for table `retur_penjualan`
--

CREATE TABLE `retur_penjualan` (
  `id_retur` int(11) NOT NULL,
  `kode_retur` varchar(30) NOT NULL,
  `tanggal` date NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `alasan` text DEFAULT NULL,
  `total_retur` decimal(15,2) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `retur_penjualan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_retur_penjualan` AFTER INSERT ON `retur_penjualan` FOR EACH ROW BEGIN
    UPDATE produk
    SET stok = stok + NEW.jumlah
    WHERE id_produk = NEW.id_produk;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `satuan_barang`
--

CREATE TABLE `satuan_barang` (
  `id_satuan` int(11) NOT NULL,
  `nama_satuan` varchar(20) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `satuan_barang`
--

INSERT INTO `satuan_barang` (`id_satuan`, `nama_satuan`, `keterangan`) VALUES
(1, 'Kg', 'Kilogram'),
(2, 'Gram', 'Gram'),
(3, 'Liter', 'Liter'),
(4, 'Ml', 'Mililiter'),
(5, 'Pcs', 'Pieces / Buah'),
(6, 'Kotak', 'Box / Kotak'),
(7, 'Bungkus', 'Bungkus / Pack'),
(8, 'Sachet', 'Sachet / Renteng'),
(9, 'Karung', 'Karung / Zak'),
(10, 'Botol', 'Botol'),
(11, 'Kaleng', 'Kaleng'),
(12, 'Lusin', 'Lusin (12 pcs)'),
(13, 'Dus', 'Dus / Karton');

-- --------------------------------------------------------

--
-- Table structure for table `stok_masuk`
--

CREATE TABLE `stok_masuk` (
  `id_stok_masuk` int(11) NOT NULL,
  `kode_transaksi` varchar(30) NOT NULL,
  `tanggal` date NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `total_item` int(11) NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `id_user` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `kode_supplier` varchar(20) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `kontak_person` varchar(100) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `kode_supplier`, `nama_supplier`, `alamat`, `telepon`, `email`, `kontak_person`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SUP001', 'PT Indofood Distributor Jakarta', 'Jl. Sudirman Kav 76-78, Jakarta Selatan', '0215705000', 'info@indofood.co.id', 'Pak Amir Sudrajat', 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(2, 'SUP002', 'CV Berkah Sembako Bogor', 'Pasar Induk Kemang, Bogor', '02518321456', 'berkah@supplier.co.id', 'Ibu Sari Dewi', 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(3, 'SUP003', 'PT Wings Surya Distributor', 'Jl. Raya Bogor KM 28, Cimanggis', '0218771234', 'wings@distributor.co.id', 'Pak Hendra Wijaya', 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(4, 'SUP004', 'Toko Grosir Maju Jaya', 'Pasar Anyar, Bogor', '02518765432', 'majujaya@gmail.com', 'Pak Dedi Hermawan', 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(5, 'SUP005', 'PT Bogasari Distribusi', 'Jl. Raya Jakarta-Bogor, Cibinong', '0218763456', 'distribusi@bogasari.com', 'Ibu Ratna Sari', 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56');

-- --------------------------------------------------------

--
-- Table structure for table `tmp_transaksi`
--

CREATE TABLE `tmp_transaksi` (
  `id_tmp` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `diskon` decimal(12,2) DEFAULT 0.00,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_penjualan`
--

CREATE TABLE `transaksi_penjualan` (
  `id_transaksi` int(11) NOT NULL,
  `kode_transaksi` varchar(30) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `total_item` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `diskon` decimal(15,2) DEFAULT 0.00,
  `pajak` decimal(15,2) DEFAULT 0.00,
  `total_bayar` decimal(15,2) NOT NULL,
  `tunai` decimal(15,2) NOT NULL,
  `kembalian` decimal(15,2) NOT NULL,
  `metode_bayar` enum('tunai','debit','kredit','transfer','qris') DEFAULT 'tunai',
  `status` enum('selesai','pending','batal') DEFAULT 'selesai',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi_penjualan`
--

INSERT INTO `transaksi_penjualan` (`id_transaksi`, `kode_transaksi`, `tanggal`, `waktu`, `id_pelanggan`, `id_user`, `total_item`, `subtotal`, `diskon`, `pajak`, `total_bayar`, `tunai`, `kembalian`, `metode_bayar`, `status`, `keterangan`, `created_at`) VALUES
(1, 'TRX20251209200335', '2025-12-09', '20:03:35', 1, 1, 1, 85000.00, 0.00, 0.00, 85000.00, 85000.00, 0.00, 'tunai', 'selesai', 'Transaksi dari website', '2025-12-09 19:03:35'),
(2, 'TRX20251209200351', '2025-12-09', '20:03:51', 1, 1, 6, 432000.00, 0.00, 0.00, 432000.00, 432000.00, 0.00, 'tunai', 'selesai', 'Transaksi dari website', '2025-12-09 19:03:51'),
(6, 'TRX20251209200941', '2025-12-09', '20:09:41', 1, 1, 10, 205000.00, 0.00, 0.00, 205000.00, 205000.00, 0.00, 'tunai', 'selesai', 'Transaksi dari website', '2025-12-09 19:09:41'),
(7, 'TRX20251209201131', '2025-12-09', '20:11:31', 1, 1, 5, 70000.00, 0.00, 0.00, 70000.00, 70000.00, 0.00, 'tunai', 'selesai', 'Transaksi dari website', '2025-12-09 19:11:31'),
(8, 'TRX20251209201230', '2025-12-09', '20:12:30', 1, 1, 5, 70000.00, 0.00, 0.00, 70000.00, 70000.00, 0.00, 'tunai', 'selesai', 'Transaksi dari website', '2025-12-09 19:12:30'),
(9, 'TRX20251209205940', '2025-12-09', '20:59:40', 1, 1, 4, 620000.00, 0.00, 0.00, 620000.00, 620000.00, 0.00, 'tunai', 'selesai', 'Transaksi dari website', '2025-12-09 19:59:40'),
(10, 'TRX20251210003018', '2025-12-10', '00:30:18', 1, 1, 1, 85000.00, 0.00, 0.00, 85000.00, 85000.00, 0.00, 'tunai', 'selesai', 'Transaksi dari website', '2025-12-09 23:30:18'),
(11, 'TRX20260506175927', '2026-05-06', '17:59:27', 1, 1, 3, 255000.00, 0.00, 0.00, 255000.00, 255000.00, 0.00, 'tunai', 'selesai', 'Transaksi dari website', '2026-05-06 15:59:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `level` enum('admin','kasir','staff','owner') NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `level`, `no_hp`, `email`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator', 'admin', '081234567890', 'admin@tokosembako.com', NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(2, 'kasir01', 'de28f8f7998f23ab4194b51a6029416f', 'Siti Nurhaliza', 'kasir', '085712345678', 'kasir@tokosembako.com', NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(3, 'owner', '5be057accb25758101fa5eadbbd79503', 'Budi Hartono', 'owner', '081298765432', 'owner@tokosembako.com', NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56'),
(4, 'staff01', 'de9bf5643eabf80f4a56fda3bbb84483', 'Ahmad Yani', 'staff', '087823456789', 'staff@tokosembako.com', NULL, 'aktif', '2025-12-01 17:07:56', '2025-12-01 17:07:56');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_laporan_penjualan_harian`
-- (See below for the actual view)
--
CREATE TABLE `v_laporan_penjualan_harian` (
`tanggal` date
,`kode_transaksi` varchar(30)
,`waktu` time
,`nama_pelanggan` varchar(100)
,`kasir` varchar(100)
,`total_item` int(11)
,`total_bayar` decimal(15,2)
,`metode_bayar` enum('tunai','debit','kredit','transfer','qris')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_penjualan_harian_ringkas`
-- (See below for the actual view)
--
CREATE TABLE `v_penjualan_harian_ringkas` (
`tanggal` date
,`jumlah_transaksi` bigint(21)
,`total_omset` decimal(37,2)
,`rata2_per_transaksi` decimal(19,6)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_produk_stok_di_bawah_rerata`
-- (See below for the actual view)
--
CREATE TABLE `v_produk_stok_di_bawah_rerata` (
`id_produk` int(11)
,`kode_produk` varchar(20)
,`barcode` varchar(50)
,`nama_produk` varchar(150)
,`id_kategori` int(11)
,`id_satuan` int(11)
,`merk` varchar(50)
,`harga_beli` decimal(12,2)
,`harga_jual` decimal(12,2)
,`diskon` decimal(5,2)
,`stok` int(11)
,`stok_minimum` int(11)
,`keterangan` text
,`gambar` varchar(255)
,`status` enum('aktif','nonaktif')
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_produk_terlaris`
-- (See below for the actual view)
--
CREATE TABLE `v_produk_terlaris` (
`kode_produk` varchar(20)
,`nama_produk` varchar(150)
,`merk` varchar(50)
,`nama_kategori` varchar(50)
,`total_terjual` decimal(32,0)
,`total_pendapatan` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_stok_menipis`
-- (See below for the actual view)
--
CREATE TABLE `v_stok_menipis` (
`kode_produk` varchar(20)
,`nama_produk` varchar(150)
,`nama_kategori` varchar(50)
,`stok` int(11)
,`stok_minimum` int(11)
,`nama_satuan` varchar(20)
,`harga_jual` decimal(12,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_transaksi_detail_lengkap`
-- (See below for the actual view)
--
CREATE TABLE `v_transaksi_detail_lengkap` (
`id_transaksi` int(11)
,`kode_transaksi` varchar(30)
,`tanggal` date
,`waktu` time
,`nama_pelanggan` varchar(100)
,`kasir` varchar(100)
,`kode_produk` varchar(20)
,`nama_produk` varchar(150)
,`nama_kategori` varchar(50)
,`nama_satuan` varchar(20)
,`harga_jual` decimal(12,2)
,`jumlah` int(11)
,`diskon` decimal(12,2)
,`subtotal` decimal(15,2)
);

-- --------------------------------------------------------

--
-- Structure for view `v_laporan_penjualan_harian`
--
DROP TABLE IF EXISTS `v_laporan_penjualan_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_laporan_penjualan_harian`  AS SELECT `t`.`tanggal` AS `tanggal`, `t`.`kode_transaksi` AS `kode_transaksi`, `t`.`waktu` AS `waktu`, `p`.`nama_pelanggan` AS `nama_pelanggan`, `u`.`nama_lengkap` AS `kasir`, `t`.`total_item` AS `total_item`, `t`.`total_bayar` AS `total_bayar`, `t`.`metode_bayar` AS `metode_bayar` FROM ((`transaksi_penjualan` `t` left join `pelanggan` `p` on(`t`.`id_pelanggan` = `p`.`id_pelanggan`)) join `users` `u` on(`t`.`id_user` = `u`.`id_user`)) WHERE `t`.`status` = 'selesai' ORDER BY `t`.`tanggal` DESC, `t`.`waktu` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_penjualan_harian_ringkas`
--
DROP TABLE IF EXISTS `v_penjualan_harian_ringkas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_penjualan_harian_ringkas`  AS SELECT `t`.`tanggal` AS `tanggal`, count(0) AS `jumlah_transaksi`, sum(`t`.`total_bayar`) AS `total_omset`, (select avg(`t2`.`total_bayar`) from `transaksi_penjualan` `t2` where `t2`.`tanggal` = `t`.`tanggal` and `t2`.`status` = 'selesai') AS `rata2_per_transaksi` FROM `transaksi_penjualan` AS `t` WHERE `t`.`status` = 'selesai' GROUP BY `t`.`tanggal` ORDER BY `t`.`tanggal` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_produk_stok_di_bawah_rerata`
--
DROP TABLE IF EXISTS `v_produk_stok_di_bawah_rerata`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_produk_stok_di_bawah_rerata`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`kode_produk` AS `kode_produk`, `p`.`barcode` AS `barcode`, `p`.`nama_produk` AS `nama_produk`, `p`.`id_kategori` AS `id_kategori`, `p`.`id_satuan` AS `id_satuan`, `p`.`merk` AS `merk`, `p`.`harga_beli` AS `harga_beli`, `p`.`harga_jual` AS `harga_jual`, `p`.`diskon` AS `diskon`, `p`.`stok` AS `stok`, `p`.`stok_minimum` AS `stok_minimum`, `p`.`keterangan` AS `keterangan`, `p`.`gambar` AS `gambar`, `p`.`status` AS `status`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at` FROM `produk` AS `p` WHERE `p`.`stok` < (select avg(`produk`.`stok`) from `produk`) AND `p`.`status` = 'aktif' ;

-- --------------------------------------------------------

--
-- Structure for view `v_produk_terlaris`
--
DROP TABLE IF EXISTS `v_produk_terlaris`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_produk_terlaris`  AS SELECT `p`.`kode_produk` AS `kode_produk`, `p`.`nama_produk` AS `nama_produk`, `p`.`merk` AS `merk`, `k`.`nama_kategori` AS `nama_kategori`, sum(`dt`.`jumlah`) AS `total_terjual`, sum(`dt`.`subtotal`) AS `total_pendapatan` FROM (((`detail_transaksi` `dt` join `produk` `p` on(`dt`.`id_produk` = `p`.`id_produk`)) join `kategori_barang` `k` on(`p`.`id_kategori` = `k`.`id_kategori`)) join `transaksi_penjualan` `t` on(`dt`.`id_transaksi` = `t`.`id_transaksi`)) WHERE `t`.`status` = 'selesai' GROUP BY `p`.`id_produk` ORDER BY sum(`dt`.`jumlah`) DESC LIMIT 0, 20 ;

-- --------------------------------------------------------

--
-- Structure for view `v_stok_menipis`
--
DROP TABLE IF EXISTS `v_stok_menipis`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stok_menipis`  AS SELECT `p`.`kode_produk` AS `kode_produk`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `p`.`stok` AS `stok`, `p`.`stok_minimum` AS `stok_minimum`, `s`.`nama_satuan` AS `nama_satuan`, `p`.`harga_jual` AS `harga_jual` FROM ((`produk` `p` join `kategori_barang` `k` on(`p`.`id_kategori` = `k`.`id_kategori`)) join `satuan_barang` `s` on(`p`.`id_satuan` = `s`.`id_satuan`)) WHERE `p`.`stok` <= `p`.`stok_minimum` AND `p`.`status` = 'aktif' ORDER BY `p`.`stok` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_transaksi_detail_lengkap`
--
DROP TABLE IF EXISTS `v_transaksi_detail_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_transaksi_detail_lengkap`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`kode_transaksi` AS `kode_transaksi`, `t`.`tanggal` AS `tanggal`, `t`.`waktu` AS `waktu`, `p`.`nama_pelanggan` AS `nama_pelanggan`, `u`.`nama_lengkap` AS `kasir`, `pr`.`kode_produk` AS `kode_produk`, `pr`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `s`.`nama_satuan` AS `nama_satuan`, `d`.`harga_jual` AS `harga_jual`, `d`.`jumlah` AS `jumlah`, `d`.`diskon` AS `diskon`, `d`.`subtotal` AS `subtotal` FROM ((((((`transaksi_penjualan` `t` join `detail_transaksi` `d` on(`t`.`id_transaksi` = `d`.`id_transaksi`)) join `produk` `pr` on(`d`.`id_produk` = `pr`.`id_produk`)) join `kategori_barang` `k` on(`pr`.`id_kategori` = `k`.`id_kategori`)) join `satuan_barang` `s` on(`pr`.`id_satuan` = `s`.`id_satuan`)) left join `pelanggan` `p` on(`t`.`id_pelanggan` = `p`.`id_pelanggan`)) join `users` `u` on(`t`.`id_user` = `u`.`id_user`)) WHERE `t`.`status` = 'selesai' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_stok_masuk`
--
ALTER TABLE `detail_stok_masuk`
  ADD PRIMARY KEY (`id_detail_masuk`),
  ADD KEY `id_stok_masuk` (`id_stok_masuk`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `kategori_barang`
--
ALTER TABLE `kategori_barang`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `kode_pelanggan` (`kode_pelanggan`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `id_satuan` (`id_satuan`),
  ADD KEY `idx_produk_kategori` (`id_kategori`),
  ADD KEY `idx_produk_barcode` (`barcode`),
  ADD KEY `idx_produk_status` (`status`);

--
-- Indexes for table `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  ADD PRIMARY KEY (`id_retur`),
  ADD UNIQUE KEY `kode_retur` (`kode_retur`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `satuan_barang`
--
ALTER TABLE `satuan_barang`
  ADD PRIMARY KEY (`id_satuan`);

--
-- Indexes for table `stok_masuk`
--
ALTER TABLE `stok_masuk`
  ADD PRIMARY KEY (`id_stok_masuk`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `id_supplier` (`id_supplier`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_stok_masuk_tanggal` (`tanggal`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`),
  ADD UNIQUE KEY `kode_supplier` (`kode_supplier`);

--
-- Indexes for table `tmp_transaksi`
--
ALTER TABLE `tmp_transaksi`
  ADD PRIMARY KEY (`id_tmp`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `transaksi_penjualan`
--
ALTER TABLE `transaksi_penjualan`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal`),
  ADD KEY `idx_transaksi_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_stok_masuk`
--
ALTER TABLE `detail_stok_masuk`
  MODIFY `id_detail_masuk` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `kategori_barang`
--
ALTER TABLE `kategori_barang`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  MODIFY `id_retur` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `satuan_barang`
--
ALTER TABLE `satuan_barang`
  MODIFY `id_satuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `stok_masuk`
--
ALTER TABLE `stok_masuk`
  MODIFY `id_stok_masuk` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tmp_transaksi`
--
ALTER TABLE `tmp_transaksi`
  MODIFY `id_tmp` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi_penjualan`
--
ALTER TABLE `transaksi_penjualan`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_stok_masuk`
--
ALTER TABLE `detail_stok_masuk`
  ADD CONSTRAINT `detail_stok_masuk_ibfk_1` FOREIGN KEY (`id_stok_masuk`) REFERENCES `stok_masuk` (`id_stok_masuk`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_stok_masuk_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_penjualan` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_barang` (`id_kategori`),
  ADD CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`id_satuan`) REFERENCES `satuan_barang` (`id_satuan`);

--
-- Constraints for table `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  ADD CONSTRAINT `retur_penjualan_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_penjualan` (`id_transaksi`),
  ADD CONSTRAINT `retur_penjualan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`),
  ADD CONSTRAINT `retur_penjualan_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `stok_masuk`
--
ALTER TABLE `stok_masuk`
  ADD CONSTRAINT `stok_masuk_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`),
  ADD CONSTRAINT `stok_masuk_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `tmp_transaksi`
--
ALTER TABLE `tmp_transaksi`
  ADD CONSTRAINT `tmp_transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `tmp_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `transaksi_penjualan`
--
ALTER TABLE `transaksi_penjualan`
  ADD CONSTRAINT `transaksi_penjualan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `transaksi_penjualan_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
