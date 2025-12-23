-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Nov 2025 pada 19.10
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skripsi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `brand`
--

CREATE TABLE `brand` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `brand`
--

INSERT INTO `brand` (`brand_id`, `brand_name`) VALUES
(13, 'ASB'),
(12, 'FAG'),
(11, 'FBJ'),
(7, 'IJK'),
(5, 'KOYO'),
(9, 'NKN'),
(10, 'NSK'),
(4, 'NTN'),
(8, 'OSK'),
(3, 'SKF'),
(6, 'TIMKEN');

-- --------------------------------------------------------

--
-- Struktur dari tabel `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(3, 'Bearing');

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventory`
--

CREATE TABLE `inventory` (
  `location_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `inventory`
--

INSERT INTO `inventory` (`location_id`, `product_id`, `quantity`) VALUES
(7, 3, 44),
(7, 4, 49),
(7, 5, 147),
(7, 6, 119),
(7, 7, 44),
(7, 8, 49),
(7, 9, 55),
(7, 10, 55),
(7, 18, 55),
(7, 19, 58),
(7, 20, 58),
(7, 21, 186),
(7, 22, 33),
(7, 23, 36),
(7, 24, 44),
(7, 25, 42),
(7, 26, 47),
(7, 27, 53),
(7, 28, 44),
(7, 33, 42),
(7, 34, 47),
(7, 35, 31),
(7, 36, 39),
(7, 37, 42),
(7, 40, 53),
(7, 41, 56),
(7, 43, 53),
(7, 44, 36),
(7, 45, 37),
(7, 46, 42),
(7, 47, 45),
(7, 48, 53),
(7, 50, 42),
(7, 51, 3),
(7, 53, 2),
(7, 54, 6),
(7, 55, 0),
(7, 56, 2),
(7, 57, 5),
(7, 58, 3),
(8, 3, 2),
(8, 4, 4),
(8, 5, 21),
(8, 6, 17),
(8, 7, 3),
(8, 8, 5),
(8, 9, 2),
(8, 10, 4),
(8, 18, 3),
(8, 19, 3),
(8, 20, 5),
(8, 21, 22),
(8, 22, 4),
(8, 23, 4),
(8, 24, 3),
(8, 25, 3),
(8, 26, 5),
(8, 27, 2),
(8, 28, 4),
(8, 33, 2),
(8, 34, 4),
(8, 35, 4),
(8, 36, 3),
(8, 37, 3),
(8, 40, 4),
(8, 41, 4),
(8, 43, 3),
(8, 44, 5),
(8, 45, 2),
(8, 46, 4),
(8, 47, 4),
(8, 48, 3),
(8, 50, 5),
(8, 51, 2),
(8, 53, 4),
(8, 54, 3),
(8, 55, 3),
(8, 56, 5),
(8, 57, 2),
(8, 58, 4),
(9, 3, 2),
(9, 4, 2),
(9, 5, 2),
(9, 6, 2),
(9, 7, 2),
(9, 8, 2),
(9, 9, 2),
(9, 10, 2),
(9, 18, 2),
(9, 19, 2),
(9, 20, 2),
(9, 21, 2),
(9, 22, 2),
(9, 23, 2),
(9, 24, 2),
(9, 25, 2),
(9, 26, 2),
(9, 27, 2),
(9, 28, 2),
(9, 33, 2),
(9, 34, 2),
(9, 35, 2),
(9, 36, 2),
(9, 37, 2),
(9, 40, 2),
(9, 41, 2),
(9, 43, 2),
(9, 44, 2),
(9, 45, 2),
(9, 46, 2),
(9, 47, 2),
(9, 48, 2),
(9, 50, 2),
(9, 51, 2),
(9, 53, 2),
(9, 54, 2),
(9, 55, 2),
(9, 56, 2),
(9, 57, 2),
(9, 58, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `location`
--

CREATE TABLE `location` (
  `location_id` int(11) NOT NULL,
  `namalokasi` varchar(100) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `location`
--

INSERT INTO `location` (`location_id`, `namalokasi`, `alamat`, `status`) VALUES
(7, 'Gudang', NULL, 1),
(8, 'Toko 1', NULL, 1),
(9, 'Toko 2', NULL, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `model`
--

CREATE TABLE `model` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `model`
--

INSERT INTO `model` (`model_id`, `model_name`) VALUES
(2, '02474/20'),
(3, '07100-S'),
(4, '07210-X'),
(5, '108'),
(6, '11749/10'),
(7, '11949/10'),
(8, '1200'),
(9, '1201'),
(10, '1202'),
(11, '1203'),
(12, '1204'),
(13, '1205'),
(14, '1206'),
(15, '1207'),
(16, '1208'),
(17, '1209'),
(18, '1210'),
(19, '1211'),
(20, '1212'),
(21, '126'),
(22, '12648/10'),
(23, '12649/10'),
(24, '12749/10'),
(25, '1303'),
(26, '1305'),
(27, '1306');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `desc` varchar(255) DEFAULT NULL,
  `ukuran` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `product`
--

INSERT INTO `product` (`product_id`, `brand_id`, `model_id`, `unit_id`, `category_id`, `name`, `desc`, `ukuran`, `status`) VALUES
(3, 3, 8, 3, 3, '', NULL, NULL, 1),
(4, 3, 11, 3, 3, '', NULL, NULL, 1),
(5, 3, 14, 3, 3, '', NULL, NULL, 1),
(6, 3, 16, 3, 3, '', NULL, NULL, 1),
(7, 3, 17, 3, 3, '', NULL, NULL, 1),
(8, 3, 19, 3, 3, '', NULL, NULL, 1),
(9, 3, 20, 3, 3, '', NULL, NULL, 1),
(10, 3, 27, 3, 3, '', NULL, NULL, 1),
(18, 4, 6, 3, 3, '', NULL, NULL, 1),
(19, 4, 10, 3, 3, '', NULL, NULL, 1),
(20, 4, 12, 3, 3, '', NULL, NULL, 1),
(21, 4, 13, 3, 3, '', NULL, NULL, 1),
(22, 4, 15, 3, 3, '', NULL, NULL, 1),
(23, 4, 16, 3, 3, '', NULL, NULL, 1),
(24, 4, 18, 3, 3, '', NULL, NULL, 1),
(25, 4, 22, 3, 3, '', NULL, NULL, 1),
(26, 4, 23, 3, 3, '', NULL, NULL, 1),
(27, 4, 24, 3, 3, '', NULL, NULL, 1),
(28, 4, 26, 3, 3, '', NULL, NULL, 1),
(33, 6, 3, 3, 3, '', NULL, NULL, 1),
(34, 6, 4, 3, 3, '', NULL, NULL, 1),
(35, 6, 6, 3, 3, '', NULL, NULL, 1),
(36, 6, 7, 3, 3, '', NULL, NULL, 1),
(37, 6, 23, 3, 3, '', NULL, NULL, 1),
(40, 5, 2, 3, 3, '', NULL, NULL, 1),
(41, 5, 25, 3, 3, '', NULL, NULL, 1),
(43, 7, 5, 3, 3, '', NULL, NULL, 1),
(44, 7, 9, 3, 3, '', NULL, NULL, 1),
(45, 7, 10, 3, 3, '', NULL, NULL, 1),
(46, 7, 11, 3, 3, '', NULL, NULL, 1),
(47, 7, 13, 3, 3, '', NULL, NULL, 1),
(48, 7, 21, 3, 3, '', NULL, NULL, 1),
(50, 8, 6, 3, 3, '', NULL, NULL, 1),
(51, 8, 23, 3, 3, '', NULL, NULL, 1),
(53, 9, 7, 3, 3, '', NULL, NULL, 1),
(54, 10, 7, 3, 3, '', NULL, NULL, 1),
(55, 11, 15, 3, 3, '', NULL, NULL, 1),
(56, 12, 18, 3, 3, '', NULL, NULL, 1),
(57, 13, 19, 3, 3, '', NULL, NULL, 1),
(58, 13, 21, 3, 3, '', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `supplier_id` int(11) NOT NULL,
  `namasupplier` varchar(150) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `namasupplier`, `alamat`, `phone`, `email`, `status`) VALUES
(20, 'PT Sumber Jaya Teknik', 'Jl. Daan Mogot No.45, Jakarta Barat', '081234567801', 'sumberjayateknik@gmail.com', 1),
(21, 'PT Mitra Makmur Sejahtera', 'Jl. Ahmad Yani No.12, Bekasi Timur', '082145678902', 'mitramakmur@gmail.com', 1),
(22, 'CV Prima Industri Nusantara', 'Jl. Industri Raya No.8, Karawang', '083156789013', 'primanusantara@gmail.com', 1),
(23, 'PT Global Sentosa Bearing', 'Jl. Raya Serpong No.77, Tangerang Selatan', '081298765404', 'globalsentosabearing@gmail.com', 1),
(24, 'CV Karya Abadi Mandiri', 'Jl. Raya Bogor No.101, Depok', '082245678905', 'karyaabadi@gmail.com', 1),
(25, 'PT Mekar Sejati Teknik', 'Jl. Jend. Sudirman No.55, Jakarta Pusat', '081377889916', 'mekarsejati@gmail.com', 1),
(26, 'CV Sinar Cahaya Motor', 'Jl. Ir. H. Juanda No.23, Bekasi', '081899900927', 'sinarcahaya@gmail.com', 1),
(27, 'PT Nusantara Bearing Supply', 'Jl. Raya Klari No.17, Karawang', '082188800938', 'nusantarabearing@gmail.com', 1),
(28, 'PT Delta Teknik Mandiri', 'Jl. Raya Kalimalang No.9, Jakarta Timur', '083277711949', 'deltateknik@gmail.com', 1),
(29, 'CV Multi Jaya Perkasa', 'Jl. Siliwangi No.3, Bogor', '081233344950', 'multijayaperkasa@gmail.com', 1),
(30, 'PT Berkah Sentosa Abadi', 'Jl. Raya Bekasi No.60, Jakarta Timur', '082122233961', 'berkahsentosa@gmail.com', 1),
(31, 'CV Cahaya Gemilang Teknik', 'Jl. Raya Kosambi No.10, Tangerang', '081288889972', 'cahayagemilang@gmail.com', 1),
(32, 'PT Andalan Motorindo Sejati', 'Jl. Cikarang Baru No.15, Bekasi', '083199900983', 'andalanmotorindo@gmail.com', 1),
(33, 'PT Supra Teknik Mandiri', 'Jl. Raya Tegal Alur No.5, Jakarta Barat', '081255566994', 'suprateknik@gmail.com', 1),
(34, 'CV Bumi Mekanika Lestari', 'Jl. Raya Lemahabang No.9, Karawang', '081277788005', 'bumimekanika@gmail.com', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier_product`
--

CREATE TABLE `supplier_product` (
  `supplier_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier_product`
--

INSERT INTO `supplier_product` (`supplier_id`, `product_id`, `price`) VALUES
(20, 3, 85000.00),
(20, 7, 155000.00),
(20, 18, 95000.00),
(20, 40, 98000.00),
(21, 5, 120000.00),
(21, 8, 189000.00),
(21, 21, 70000.00),
(21, 24, 145000.00),
(21, 43, 35000.00),
(21, 53, 92000.00),
(22, 4, 99000.00),
(22, 6, 135000.00),
(22, 18, 98000.00),
(22, 41, 62000.00),
(22, 50, 88000.00),
(23, 5, 125000.00),
(23, 33, 125000.00),
(27, 6, 139000.00),
(27, 34, 135000.00),
(27, 54, 99000.00),
(27, 56, 175000.00),
(28, 21, 73000.00),
(28, 44, 52000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `note` varchar(255) DEFAULT NULL,
  `type` enum('IN','OUT','ADJUST') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaction`
--

INSERT INTO `transaction` (`transaction_id`, `location_id`, `user_id`, `transaction_date`, `note`, `type`) VALUES
(3, 7, 1, '2025-11-07 14:53:53', 'Pembelian awal', 'IN'),
(4, 7, 1, '2025-11-07 14:54:55', 'Pembelian awal', 'IN'),
(5, 7, 1, '2025-11-07 14:55:49', 'Pembelian awal', 'IN'),
(6, 7, 1, '2025-11-07 14:56:09', 'Pembelian awal', 'IN'),
(7, 8, 1, '2025-11-07 14:56:09', 'Penjualan kasir Toko 1', 'OUT'),
(13, 7, 1, '2025-10-24 00:00:00', 'Seed IN batch-1 (auto)', 'IN'),
(14, 7, 1, '2025-10-31 00:00:00', 'Seed IN batch-2 (auto)', 'IN'),
(15, 7, 1, '2025-11-07 00:00:00', 'Seed IN batch-3 (auto)', 'IN'),
(16, 8, 1, '2025-11-07 00:00:00', 'Seed penjualan Toko 1 (auto)', 'OUT'),
(17, 9, 1, '2025-11-07 00:00:00', 'Seed penjualan Toko 2 (auto)', 'OUT'),
(19, 7, 1, '2025-11-07 14:58:00', 'keluar di olshop', 'OUT');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_details`
--

CREATE TABLE `transaction_details` (
  `transaction_detail_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaction_details`
--

INSERT INTO `transaction_details` (`transaction_detail_id`, `transaction_id`, `product_id`, `location_id`, `quantity`, `quantity_before`, `quantity_after`) VALUES
(5, 3, 5, 7, 30, 0, 30),
(6, 3, 6, 7, 20, 0, 20),
(7, 3, 21, 7, 40, 0, 40),
(8, 4, 5, 7, 30, 30, 60),
(9, 4, 6, 7, 20, 20, 40),
(10, 4, 21, 7, 40, 40, 80),
(11, 5, 5, 7, 30, 60, 90),
(12, 5, 6, 7, 20, 40, 60),
(13, 5, 21, 7, 40, 80, 120),
(14, 6, 5, 7, 30, 80, 110),
(15, 6, 6, 7, 20, 52, 72),
(16, 6, 21, 7, 40, 108, 148),
(17, 7, 5, 8, -3, 20, 17),
(18, 7, 6, 8, -2, 16, 14),
(19, 7, 21, 8, -4, 24, 20),
(209, 13, 3, 7, 26, 0, 26),
(210, 13, 4, 7, 28, 0, 28),
(211, 13, 7, 7, 34, 0, 34),
(212, 13, 8, 7, 36, 0, 36),
(213, 13, 9, 7, 38, 0, 38),
(214, 13, 10, 7, 40, 0, 40),
(215, 13, 18, 7, 34, 0, 34),
(216, 13, 19, 7, 36, 0, 36),
(217, 13, 20, 7, 38, 0, 38),
(218, 13, 22, 7, 20, 0, 20),
(219, 13, 23, 7, 22, 0, 22),
(220, 13, 24, 7, 24, 0, 24),
(221, 13, 25, 7, 26, 0, 26),
(222, 13, 26, 7, 28, 0, 28),
(223, 13, 27, 7, 30, 0, 30),
(224, 13, 28, 7, 32, 0, 32),
(225, 13, 33, 7, 20, 0, 20),
(226, 13, 34, 7, 22, 0, 22),
(227, 13, 35, 7, 24, 0, 24),
(228, 13, 36, 7, 26, 0, 26),
(229, 13, 37, 7, 28, 0, 28),
(230, 13, 40, 7, 34, 0, 34),
(231, 13, 41, 7, 36, 0, 36),
(232, 13, 43, 7, 40, 0, 40),
(233, 13, 44, 7, 20, 0, 20),
(234, 13, 45, 7, 22, 0, 22),
(235, 13, 46, 7, 24, 0, 24),
(236, 13, 47, 7, 26, 0, 26),
(237, 13, 48, 7, 28, 0, 28),
(238, 13, 50, 7, 32, 0, 32),
(239, 13, 5, 7, 30, 100, 130),
(240, 13, 6, 7, 32, 64, 96),
(241, 13, 21, 7, 40, 136, 176),
(242, 13, 51, 7, 34, 0, 34),
(243, 13, 53, 7, 38, 0, 38),
(244, 13, 54, 7, 40, 0, 40),
(245, 13, 55, 7, 20, 0, 20),
(246, 13, 56, 7, 22, 0, 22),
(247, 13, 57, 7, 24, 0, 24),
(248, 13, 58, 7, 26, 0, 26),
(272, 14, 3, 7, 16, 26, 42),
(273, 14, 4, 7, 18, 28, 46),
(274, 14, 7, 7, 10, 34, 44),
(275, 14, 8, 7, 12, 36, 48),
(276, 14, 9, 7, 14, 38, 52),
(277, 14, 10, 7, 16, 40, 56),
(278, 14, 18, 7, 18, 34, 52),
(279, 14, 19, 7, 20, 36, 56),
(280, 14, 20, 7, 22, 38, 60),
(281, 14, 22, 7, 12, 20, 32),
(282, 14, 23, 7, 14, 22, 36),
(283, 14, 24, 7, 16, 24, 40),
(284, 14, 25, 7, 18, 26, 44),
(285, 14, 26, 7, 20, 28, 48),
(286, 14, 27, 7, 22, 30, 52),
(287, 14, 28, 7, 10, 32, 42),
(288, 14, 33, 7, 20, 20, 40),
(289, 14, 34, 7, 22, 22, 44),
(290, 14, 35, 7, 10, 24, 34),
(291, 14, 36, 7, 12, 26, 38),
(292, 14, 37, 7, 14, 28, 42),
(293, 14, 40, 7, 20, 34, 54),
(294, 14, 41, 7, 22, 36, 58),
(295, 14, 43, 7, 12, 40, 52),
(296, 14, 44, 7, 14, 20, 34),
(297, 14, 45, 7, 16, 22, 38),
(298, 14, 46, 7, 18, 24, 42),
(299, 14, 47, 7, 20, 26, 46),
(300, 14, 48, 7, 22, 28, 50),
(301, 14, 50, 7, 12, 32, 44),
(302, 14, 5, 7, 20, 130, 150),
(303, 14, 6, 7, 22, 96, 118),
(304, 14, 21, 7, 10, 176, 186),
(305, 14, 51, 7, 14, 34, 48),
(306, 14, 53, 7, 18, 38, 56),
(307, 14, 54, 7, 20, 40, 60),
(308, 14, 55, 7, 22, 20, 42),
(309, 14, 56, 7, 10, 22, 32),
(310, 14, 57, 7, 12, 24, 36),
(311, 14, 58, 7, 14, 26, 40),
(335, 15, 3, 7, 11, 42, 53),
(336, 15, 4, 7, 12, 46, 58),
(337, 15, 7, 7, 10, 44, 54),
(338, 15, 8, 7, 11, 48, 59),
(339, 15, 9, 7, 12, 52, 64),
(340, 15, 10, 7, 8, 56, 64),
(341, 15, 18, 7, 11, 52, 63),
(342, 15, 19, 7, 12, 56, 68),
(343, 15, 20, 7, 8, 60, 68),
(344, 15, 22, 7, 10, 32, 42),
(345, 15, 23, 7, 11, 36, 47),
(346, 15, 24, 7, 12, 40, 52),
(347, 15, 25, 7, 8, 44, 52),
(348, 15, 26, 7, 9, 48, 57),
(349, 15, 27, 7, 10, 52, 62),
(350, 15, 28, 7, 11, 42, 53),
(351, 15, 33, 7, 11, 40, 51),
(352, 15, 34, 7, 12, 44, 56),
(353, 15, 35, 7, 8, 34, 42),
(354, 15, 36, 7, 9, 38, 47),
(355, 15, 37, 7, 10, 42, 52),
(356, 15, 40, 7, 8, 54, 62),
(357, 15, 41, 7, 9, 58, 67),
(358, 15, 43, 7, 11, 52, 63),
(359, 15, 44, 7, 12, 34, 46),
(360, 15, 45, 7, 8, 38, 46),
(361, 15, 46, 7, 9, 42, 51),
(362, 15, 47, 7, 10, 46, 56),
(363, 15, 48, 7, 11, 50, 61),
(364, 15, 50, 7, 8, 44, 52),
(365, 15, 5, 7, 8, 150, 158),
(366, 15, 6, 7, 9, 118, 127),
(367, 15, 21, 7, 9, 186, 195),
(368, 15, 51, 7, 9, 48, 57),
(369, 15, 53, 7, 11, 56, 67),
(370, 15, 54, 7, 12, 60, 72),
(371, 15, 55, 7, 8, 42, 50),
(372, 15, 56, 7, 9, 32, 41),
(373, 15, 57, 7, 10, 36, 46),
(374, 15, 58, 7, 11, 40, 51),
(398, 16, 3, 8, -3, 5, 2),
(399, 16, 4, 8, -2, 6, 4),
(400, 16, 7, 8, -3, 6, 3),
(401, 16, 8, 8, -2, 7, 5),
(402, 16, 9, 8, -3, 5, 2),
(403, 16, 10, 8, -2, 6, 4),
(404, 16, 18, 8, -2, 5, 3),
(405, 16, 19, 8, -3, 6, 3),
(406, 16, 20, 8, -2, 7, 5),
(407, 16, 22, 8, -2, 6, 4),
(408, 16, 23, 8, -3, 7, 4),
(409, 16, 24, 8, -2, 5, 3),
(410, 16, 25, 8, -3, 6, 3),
(411, 16, 26, 8, -2, 7, 5),
(412, 16, 27, 8, -3, 5, 2),
(413, 16, 28, 8, -2, 6, 4),
(414, 16, 33, 8, -3, 5, 2),
(415, 16, 34, 8, -2, 6, 4),
(416, 16, 35, 8, -3, 7, 4),
(417, 16, 36, 8, -2, 5, 3),
(418, 16, 37, 8, -3, 6, 3),
(419, 16, 40, 8, -2, 6, 4),
(420, 16, 41, 8, -3, 7, 4),
(421, 16, 43, 8, -3, 6, 3),
(422, 16, 44, 8, -2, 7, 5),
(423, 16, 45, 8, -3, 5, 2),
(424, 16, 46, 8, -2, 6, 4),
(425, 16, 47, 8, -3, 7, 4),
(426, 16, 48, 8, -2, 5, 3),
(427, 16, 50, 8, -2, 7, 5),
(428, 16, 5, 8, -3, 24, 21),
(429, 16, 6, 8, -2, 19, 17),
(430, 16, 21, 8, -3, 25, 22),
(431, 16, 51, 8, -3, 5, 2),
(432, 16, 53, 8, -3, 7, 4),
(433, 16, 54, 8, -2, 5, 3),
(434, 16, 55, 8, -3, 6, 3),
(435, 16, 56, 8, -2, 7, 5),
(436, 16, 57, 8, -3, 5, 2),
(437, 16, 58, 8, -2, 6, 4),
(461, 17, 3, 9, -2, 4, 2),
(462, 17, 4, 9, -1, 3, 2),
(463, 17, 7, 9, -2, 4, 2),
(464, 17, 8, 9, -1, 3, 2),
(465, 17, 9, 9, -2, 4, 2),
(466, 17, 10, 9, -1, 3, 2),
(467, 17, 18, 9, -1, 3, 2),
(468, 17, 19, 9, -2, 4, 2),
(469, 17, 20, 9, -1, 3, 2),
(470, 17, 22, 9, -1, 3, 2),
(471, 17, 23, 9, -2, 4, 2),
(472, 17, 24, 9, -1, 3, 2),
(473, 17, 25, 9, -2, 4, 2),
(474, 17, 26, 9, -1, 3, 2),
(475, 17, 27, 9, -2, 4, 2),
(476, 17, 28, 9, -1, 3, 2),
(477, 17, 33, 9, -2, 4, 2),
(478, 17, 34, 9, -1, 3, 2),
(479, 17, 35, 9, -2, 4, 2),
(480, 17, 36, 9, -1, 3, 2),
(481, 17, 37, 9, -2, 4, 2),
(482, 17, 40, 9, -1, 3, 2),
(483, 17, 41, 9, -2, 4, 2),
(484, 17, 43, 9, -2, 4, 2),
(485, 17, 44, 9, -1, 3, 2),
(486, 17, 45, 9, -2, 4, 2),
(487, 17, 46, 9, -1, 3, 2),
(488, 17, 47, 9, -2, 4, 2),
(489, 17, 48, 9, -1, 3, 2),
(490, 17, 50, 9, -1, 3, 2),
(491, 17, 5, 9, -2, 4, 2),
(492, 17, 6, 9, -1, 3, 2),
(493, 17, 21, 9, -2, 4, 2),
(494, 17, 51, 9, -2, 4, 2),
(495, 17, 53, 9, -2, 4, 2),
(496, 17, 54, 9, -1, 3, 2),
(497, 17, 55, 9, -2, 4, 2),
(498, 17, 56, 9, -1, 3, 2),
(499, 17, 57, 9, -2, 4, 2),
(500, 17, 58, 9, -1, 3, 2),
(501, 19, 58, 7, -39, 42, 3),
(502, 19, 57, 7, -32, 37, 5),
(503, 19, 56, 7, -29, 31, 2),
(504, 19, 55, 7, -40, 40, 0),
(505, 19, 54, 7, -58, 64, 6),
(506, 19, 53, 7, -54, 56, 2),
(507, 19, 51, 7, -45, 48, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_stok_in`
--

CREATE TABLE `transaction_stok_in` (
  `transaction_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `invoice` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaction_stok_in`
--

INSERT INTO `transaction_stok_in` (`transaction_id`, `supplier_id`, `invoice`) VALUES
(3, 20, 'INV-20251107-X'),
(4, 20, 'INV-20251107-X'),
(5, 20, 'INV-20251107-X'),
(6, 20, 'INV-20251107-X'),
(13, 20, 'SEED-20251024-B1'),
(14, 20, 'SEED-20251031-B2'),
(15, 20, 'SEED-20251107-B3');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transfer_stok`
--

CREATE TABLE `transfer_stok` (
  `transfer_id` int(11) NOT NULL,
  `source_location_id` int(11) NOT NULL,
  `target_location_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transfer_stok`
--

INSERT INTO `transfer_stok` (`transfer_id`, `source_location_id`, `target_location_id`, `user_id`, `note`, `created_at`) VALUES
(2, 7, 8, 1, 'Mutasi etalase', '2025-11-07 14:53:53'),
(3, 7, 8, 1, 'Mutasi etalase', '2025-11-07 14:54:55'),
(4, 7, 8, 1, 'Mutasi etalase', '2025-11-07 14:55:49'),
(5, 7, 8, 1, 'Mutasi etalase', '2025-11-07 14:56:09'),
(7, 7, 8, 1, 'Seed transfer ke Toko 1 (auto)', '2025-11-06 16:24:50'),
(8, 7, 9, 1, 'Seed transfer ke Toko 2 (auto)', '2025-11-06 16:24:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transfer_stok_detail`
--

CREATE TABLE `transfer_stok_detail` (
  `transfer_detail_id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `source_location_id` int(11) NOT NULL,
  `target_location_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `src_quantitybef` int(11) NOT NULL,
  `src_quantityaft` int(11) NOT NULL,
  `tgt_quantitybef` int(11) NOT NULL,
  `tgt_quantityaft` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transfer_stok_detail`
--

INSERT INTO `transfer_stok_detail` (`transfer_detail_id`, `transfer_id`, `product_id`, `source_location_id`, `target_location_id`, `quantity`, `src_quantitybef`, `src_quantityaft`, `tgt_quantitybef`, `tgt_quantityaft`) VALUES
(1, 3, 5, 7, 8, 10, 60, 50, 0, 10),
(2, 3, 6, 7, 8, 8, 40, 32, 0, 8),
(3, 3, 21, 7, 8, 12, 80, 68, 0, 12),
(4, 4, 5, 7, 8, 10, 90, 80, 0, 10),
(5, 4, 6, 7, 8, 8, 60, 52, 0, 8),
(6, 4, 21, 7, 8, 12, 120, 108, 0, 12),
(7, 5, 5, 7, 8, 10, 110, 100, 10, 20),
(8, 5, 6, 7, 8, 8, 72, 64, 8, 16),
(9, 5, 21, 7, 8, 12, 148, 136, 12, 24),
(10, 7, 3, 7, 8, 5, 53, 48, 0, 5),
(11, 7, 4, 7, 8, 6, 58, 52, 0, 6),
(12, 7, 7, 7, 8, 6, 54, 48, 0, 6),
(13, 7, 8, 7, 8, 7, 59, 52, 0, 7),
(14, 7, 9, 7, 8, 5, 64, 59, 0, 5),
(15, 7, 10, 7, 8, 6, 64, 58, 0, 6),
(16, 7, 18, 7, 8, 5, 63, 58, 0, 5),
(17, 7, 19, 7, 8, 6, 68, 62, 0, 6),
(18, 7, 20, 7, 8, 7, 68, 61, 0, 7),
(19, 7, 22, 7, 8, 6, 42, 36, 0, 6),
(20, 7, 23, 7, 8, 7, 47, 40, 0, 7),
(21, 7, 24, 7, 8, 5, 52, 47, 0, 5),
(22, 7, 25, 7, 8, 6, 52, 46, 0, 6),
(23, 7, 26, 7, 8, 7, 57, 50, 0, 7),
(24, 7, 27, 7, 8, 5, 62, 57, 0, 5),
(25, 7, 28, 7, 8, 6, 53, 47, 0, 6),
(26, 7, 33, 7, 8, 5, 51, 46, 0, 5),
(27, 7, 34, 7, 8, 6, 56, 50, 0, 6),
(28, 7, 35, 7, 8, 7, 42, 35, 0, 7),
(29, 7, 36, 7, 8, 5, 47, 42, 0, 5),
(30, 7, 37, 7, 8, 6, 52, 46, 0, 6),
(31, 7, 40, 7, 8, 6, 62, 56, 0, 6),
(32, 7, 41, 7, 8, 7, 67, 60, 0, 7),
(33, 7, 43, 7, 8, 6, 63, 57, 0, 6),
(34, 7, 44, 7, 8, 7, 46, 39, 0, 7),
(35, 7, 45, 7, 8, 5, 46, 41, 0, 5),
(36, 7, 46, 7, 8, 6, 51, 45, 0, 6),
(37, 7, 47, 7, 8, 7, 56, 49, 0, 7),
(38, 7, 48, 7, 8, 5, 61, 56, 0, 5),
(39, 7, 50, 7, 8, 7, 52, 45, 0, 7),
(40, 7, 5, 7, 8, 7, 158, 151, 17, 24),
(41, 7, 6, 7, 8, 5, 127, 122, 14, 19),
(42, 7, 21, 7, 8, 5, 195, 190, 20, 25),
(43, 7, 51, 7, 8, 5, 57, 52, 0, 5),
(44, 7, 53, 7, 8, 7, 67, 60, 0, 7),
(45, 7, 54, 7, 8, 5, 72, 67, 0, 5),
(46, 7, 55, 7, 8, 6, 50, 44, 0, 6),
(47, 7, 56, 7, 8, 7, 41, 34, 0, 7),
(48, 7, 57, 7, 8, 5, 46, 41, 0, 5),
(49, 7, 58, 7, 8, 6, 51, 45, 0, 6),
(73, 8, 3, 7, 9, 4, 48, 44, 0, 4),
(74, 8, 4, 7, 9, 3, 52, 49, 0, 3),
(75, 8, 7, 7, 9, 4, 48, 44, 0, 4),
(76, 8, 8, 7, 9, 3, 52, 49, 0, 3),
(77, 8, 9, 7, 9, 4, 59, 55, 0, 4),
(78, 8, 10, 7, 9, 3, 58, 55, 0, 3),
(79, 8, 18, 7, 9, 3, 58, 55, 0, 3),
(80, 8, 19, 7, 9, 4, 62, 58, 0, 4),
(81, 8, 20, 7, 9, 3, 61, 58, 0, 3),
(82, 8, 22, 7, 9, 3, 36, 33, 0, 3),
(83, 8, 23, 7, 9, 4, 40, 36, 0, 4),
(84, 8, 24, 7, 9, 3, 47, 44, 0, 3),
(85, 8, 25, 7, 9, 4, 46, 42, 0, 4),
(86, 8, 26, 7, 9, 3, 50, 47, 0, 3),
(87, 8, 27, 7, 9, 4, 57, 53, 0, 4),
(88, 8, 28, 7, 9, 3, 47, 44, 0, 3),
(89, 8, 33, 7, 9, 4, 46, 42, 0, 4),
(90, 8, 34, 7, 9, 3, 50, 47, 0, 3),
(91, 8, 35, 7, 9, 4, 35, 31, 0, 4),
(92, 8, 36, 7, 9, 3, 42, 39, 0, 3),
(93, 8, 37, 7, 9, 4, 46, 42, 0, 4),
(94, 8, 40, 7, 9, 3, 56, 53, 0, 3),
(95, 8, 41, 7, 9, 4, 60, 56, 0, 4),
(96, 8, 43, 7, 9, 4, 57, 53, 0, 4),
(97, 8, 44, 7, 9, 3, 39, 36, 0, 3),
(98, 8, 45, 7, 9, 4, 41, 37, 0, 4),
(99, 8, 46, 7, 9, 3, 45, 42, 0, 3),
(100, 8, 47, 7, 9, 4, 49, 45, 0, 4),
(101, 8, 48, 7, 9, 3, 56, 53, 0, 3),
(102, 8, 50, 7, 9, 3, 45, 42, 0, 3),
(103, 8, 5, 7, 9, 4, 151, 147, 0, 4),
(104, 8, 6, 7, 9, 3, 122, 119, 0, 3),
(105, 8, 21, 7, 9, 4, 190, 186, 0, 4),
(106, 8, 51, 7, 9, 4, 52, 48, 0, 4),
(107, 8, 53, 7, 9, 4, 60, 56, 0, 4),
(108, 8, 54, 7, 9, 3, 67, 64, 0, 3),
(109, 8, 55, 7, 9, 4, 44, 40, 0, 4),
(110, 8, 56, 7, 9, 3, 34, 31, 0, 3),
(111, 8, 57, 7, 9, 4, 41, 37, 0, 4),
(112, 8, 58, 7, 9, 3, 45, 42, 0, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `unit`
--

CREATE TABLE `unit` (
  `unit_id` int(11) NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `satuan_hitung` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `unit`
--

INSERT INTO `unit` (`unit_id`, `unit_name`, `satuan_hitung`) VALUES
(3, 'pcs', 'unit');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_name` (`brand_name`);

--
-- Indeks untuk tabel `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indeks untuk tabel `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`location_id`,`product_id`),
  ADD KEY `ix_inv_prod` (`product_id`);

--
-- Indeks untuk tabel `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`);

--
-- Indeks untuk tabel `model`
--
ALTER TABLE `model`
  ADD PRIMARY KEY (`model_id`),
  ADD UNIQUE KEY `model_name` (`model_name`);

--
-- Indeks untuk tabel `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `uq_product_bmuc` (`brand_id`,`model_id`,`unit_id`,`category_id`),
  ADD KEY `fk_prod_brand` (`brand_id`),
  ADD KEY `fk_prod_model` (`model_id`),
  ADD KEY `fk_prod_unit` (`unit_id`),
  ADD KEY `fk_prod_category` (`category_id`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indeks untuk tabel `supplier_product`
--
ALTER TABLE `supplier_product`
  ADD PRIMARY KEY (`supplier_id`,`product_id`),
  ADD UNIQUE KEY `uq_supplier_product` (`supplier_id`,`product_id`),
  ADD KEY `fk_suppprod_product` (`product_id`);

--
-- Indeks untuk tabel `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_txn_location` (`location_id`),
  ADD KEY `fk_txn_user` (`user_id`);

--
-- Indeks untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`transaction_detail_id`),
  ADD KEY `fk_tdet_inv` (`location_id`,`product_id`),
  ADD KEY `ix_tdet_txn` (`transaction_id`),
  ADD KEY `ix_tdet_prod` (`product_id`);

--
-- Indeks untuk tabel `transaction_stok_in`
--
ALTER TABLE `transaction_stok_in`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_stokin_supplier` (`supplier_id`);

--
-- Indeks untuk tabel `transfer_stok`
--
ALTER TABLE `transfer_stok`
  ADD PRIMARY KEY (`transfer_id`),
  ADD KEY `fk_tr_src_loc` (`source_location_id`),
  ADD KEY `fk_tr_dst_loc` (`target_location_id`),
  ADD KEY `fk_tr_user` (`user_id`);

--
-- Indeks untuk tabel `transfer_stok_detail`
--
ALTER TABLE `transfer_stok_detail`
  ADD PRIMARY KEY (`transfer_detail_id`),
  ADD KEY `fk_trdet_prod` (`product_id`),
  ADD KEY `fk_trdet_inv_src` (`source_location_id`,`product_id`),
  ADD KEY `fk_trdet_inv_dst` (`target_location_id`,`product_id`),
  ADD KEY `ix_trdet_tr` (`transfer_id`);

--
-- Indeks untuk tabel `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `unit_name` (`unit_name`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `brand`
--
ALTER TABLE `brand`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `model`
--
ALTER TABLE `model`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `transaction_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=508;

--
-- AUTO_INCREMENT untuk tabel `transfer_stok`
--
ALTER TABLE `transfer_stok`
  MODIFY `transfer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `transfer_stok_detail`
--
ALTER TABLE `transfer_stok_detail`
  MODIFY `transfer_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT untuk tabel `unit`
--
ALTER TABLE `unit`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inv_loc` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`),
  ADD CONSTRAINT `fk_inv_prod` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Ketidakleluasaan untuk tabel `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_prod_brand` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`brand_id`),
  ADD CONSTRAINT `fk_prod_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
  ADD CONSTRAINT `fk_prod_model` FOREIGN KEY (`model_id`) REFERENCES `model` (`model_id`),
  ADD CONSTRAINT `fk_prod_unit` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`unit_id`);

--
-- Ketidakleluasaan untuk tabel `supplier_product`
--
ALTER TABLE `supplier_product`
  ADD CONSTRAINT `fk_suppprod_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `fk_suppprod_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`);

--
-- Ketidakleluasaan untuk tabel `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `fk_txn_location` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`),
  ADD CONSTRAINT `fk_txn_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `fk_tdet_inv` FOREIGN KEY (`location_id`,`product_id`) REFERENCES `inventory` (`location_id`, `product_id`),
  ADD CONSTRAINT `fk_tdet_prod` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `fk_tdet_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`transaction_id`);

--
-- Ketidakleluasaan untuk tabel `transaction_stok_in`
--
ALTER TABLE `transaction_stok_in`
  ADD CONSTRAINT `fk_stokin_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`),
  ADD CONSTRAINT `fk_stokin_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`transaction_id`);

--
-- Ketidakleluasaan untuk tabel `transfer_stok`
--
ALTER TABLE `transfer_stok`
  ADD CONSTRAINT `fk_tr_dst_loc` FOREIGN KEY (`target_location_id`) REFERENCES `location` (`location_id`),
  ADD CONSTRAINT `fk_tr_src_loc` FOREIGN KEY (`source_location_id`) REFERENCES `location` (`location_id`),
  ADD CONSTRAINT `fk_tr_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `transfer_stok_detail`
--
ALTER TABLE `transfer_stok_detail`
  ADD CONSTRAINT `fk_trdet_inv_dst` FOREIGN KEY (`target_location_id`,`product_id`) REFERENCES `inventory` (`location_id`, `product_id`),
  ADD CONSTRAINT `fk_trdet_inv_src` FOREIGN KEY (`source_location_id`,`product_id`) REFERENCES `inventory` (`location_id`, `product_id`),
  ADD CONSTRAINT `fk_trdet_prod` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `fk_trdet_tr` FOREIGN KEY (`transfer_id`) REFERENCES `transfer_stok` (`transfer_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
