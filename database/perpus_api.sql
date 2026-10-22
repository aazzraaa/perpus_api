-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2026 at 06:21 AM
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
-- Database: `perpus_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(2, 'admin', '$2y$10$huhKBbVmKOQAHHA8VwXACuUIetCFRZWunBXhwZc/LedO3MAkvELSK');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `kode_buku` varchar(20) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `penerbit` varchar(100) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `kode_buku`, `judul`, `penulis`, `kategori`, `tahun_terbit`, `penerbit`, `gambar`) VALUES
(1, 'BK001', 'Laskar Pelangi', 'Andrea Hirata', 'Novel', '2005', 'Bentang Pustaka', NULL),
(2, 'BK002', 'Bumi', 'Tere Liye', 'Novel', '2014', 'Gramedia', NULL),
(3, 'BK003', 'Pemrograman PHP', 'Abdul Kadir', 'Teknologi', '2020', 'Andi', NULL),
(4, 'BK004', 'Belajar HTML dan CSS', 'Jubilee Enterprise', 'Teknologi', '2021', 'Elex Media', NULL),
(5, 'BK005', 'Dasar-Dasar JavaScript', 'Wahana Komputer', 'Teknologi', '2022', 'Andi', NULL),
(6, 'BK006', 'Filosofi Teras', 'Henry Manampiring', 'Pengembangan Diri', '2018', 'Kompas', NULL),
(7, 'BK007', 'Atomic Habits', 'James Clear', 'Pengembangan Diri', '2019', 'Gramedia', 'https://down-id.img.susercontent.com/file/id-11134207-7r98w-lo69wqnamz2497'),
(8, 'BK008', 'Sebuah Seni Untuk Bersikap Bodo Amat', 'Mark Manson', 'Pengembangan Diri', '2018', 'Grasindo', 'https://tse1.mm.bing.net/th/id/OIP.SEOsTT6Taf-VySDxyCxKrwHaKz?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'),
(9, 'BK009', 'Clean Code', 'Robert C. Martin', 'Teknologi', '2008', 'Prentice Hall', 'assets/uploads/cover_5df35a134cedb9ee.png'),
(10, 'BK010', 'Hujan', 'Tere Liye', 'Novel', '2016', 'Gramedia', 'assets/uploads/cover_b2a75b4bf81e6aa3.png'),
(11, 'BK-00\\9', 'kegelapan di desa amose', 'Madreka Ambuana', 'Novel', '2017', 'Astriana somoe', 'https://marketplace.canva.com/EAFQkSHF0OM/2/0/501w/canva-coklat-hijau-illustrasi-pemandangan-sampul-buku-kedamaian-QIGAzaSzPCY.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
