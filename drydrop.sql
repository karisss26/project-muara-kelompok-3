-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Des 2025 pada 11.19
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `drydrop`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `created_at`) VALUES
(1, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 06:50:59'),
(2, 3, 'Update Status', 'Mengubah status Order #9 menjadi completed', '2025-11-26 06:51:13'),
(3, 3, 'New Order', 'Membuat pesanan baru #10 atas nama jihan', '2025-11-26 06:51:41'),
(4, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 06:51:57'),
(5, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 06:55:49'),
(6, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 06:56:46'),
(7, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 06:59:32'),
(8, 3, 'Update Status', 'Mengubah status Order #10 menjadi completed', '2025-11-26 07:01:09'),
(9, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 07:06:39'),
(10, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 07:15:39'),
(11, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 07:19:38'),
(12, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 07:39:28'),
(13, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:02:04'),
(14, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:06:09'),
(15, 3, 'Update Payment', 'Mengubah status pembayaran Order #10 menjadi paid', '2025-11-26 14:13:14'),
(16, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:29:22'),
(17, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:41:02'),
(18, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:45:19'),
(19, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:46:31'),
(20, 3, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:48:58'),
(21, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:54:37'),
(22, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 14:56:21'),
(23, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 15:03:37'),
(24, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 15:20:24'),
(25, 2, 'Login', 'User berhasil login ke sistem', '2025-11-26 15:30:06'),
(26, 2, 'Login', 'User berhasil login ke sistem', '2025-12-02 01:30:45'),
(27, 3, 'Login', 'User berhasil login ke sistem', '2025-12-02 01:31:43'),
(28, 2, 'Login', 'User berhasil login ke sistem', '2025-12-03 06:13:41'),
(29, 3, 'Login', 'User berhasil login ke sistem', '2025-12-03 06:16:31'),
(30, 2, 'Login', 'User berhasil login ke sistem', '2025-12-03 08:04:36'),
(31, 3, 'Login', 'User berhasil login ke sistem', '2025-12-03 08:08:21'),
(32, 3, 'Update Status', 'Mengubah status Order #6 menjadi processing', '2025-12-03 08:08:51'),
(33, 3, 'New Order', 'Membuat pesanan baru #11 atas nama diva', '2025-12-03 08:10:06'),
(34, 3, 'Login', 'User berhasil login ke sistem', '2025-12-03 08:18:15'),
(35, 3, 'New Order', 'Membuat pesanan baru #12 atas nama diva', '2025-12-03 08:24:10'),
(36, 2, 'Login', 'User berhasil login ke sistem', '2025-12-03 08:25:45'),
(37, 2, 'Login', 'User berhasil login ke sistem', '2025-12-04 13:07:55'),
(38, 2, 'Login', 'User berhasil login ke sistem', '2025-12-08 16:46:23'),
(39, 2, 'Login', 'User berhasil login ke sistem', '2025-12-09 01:15:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `customer_name`, `email`, `rating`, `comment`, `created_at`) VALUES
(1, 'jaemin', 'jaemin123@gmail.com', 4, '[Subject: complain] \nlambat 1 menit', '2025-11-26 22:29:47'),
(2, 'karisma', 'apaweh@gmail.com', 1, '[Subject: complain] \nga wangi jaemin bajunya', '2025-11-26 22:41:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `payment_method` enum('cash','online') DEFAULT 'cash',
  `pickup_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `pickup_address` text NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `total_amount`, `status`, `payment_status`, `payment_method`, `pickup_date`, `pickup_time`, `pickup_address`, `special_instructions`, `package_id`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 120.75, 'completed', 'pending', 'cash', '2025-11-19', '02:33:00', 'jonggol', NULL, NULL, '2025-11-18 19:33:31', '2025-11-19 09:29:40'),
(2, 3, NULL, 25000.00, 'processing', 'pending', 'cash', '2025-11-27', '00:30:00', 'jonggol', NULL, NULL, '2025-11-25 04:22:42', '2025-11-25 04:31:28'),
(3, 3, 'karisma', 35000.00, 'cancelled', 'pending', 'cash', '2025-11-27', '07:00:00', 'subang', NULL, NULL, '2025-11-25 08:57:15', '2025-11-25 09:04:33'),
(4, 3, 'karisma', 90000.00, 'pending', 'pending', 'cash', '2025-11-25', '09:05:00', 'jonggol', NULL, NULL, '2025-11-25 08:59:18', '2025-11-25 08:59:18'),
(5, 3, 'nissa', 90000.00, 'cancelled', 'pending', 'cash', '2025-11-29', '04:27:00', 'jonggol', NULL, NULL, '2025-11-25 09:27:29', '2025-11-25 09:28:31'),
(6, 3, 'nissa', 90000.00, 'processing', 'pending', 'cash', '2025-11-29', '04:27:00', 'jonggol', NULL, NULL, '2025-11-25 09:28:17', '2025-12-03 08:08:51'),
(7, 3, 'jaemin', 36000.00, 'processing', 'pending', 'online', '2025-11-26', '10:20:00', 'barokah laundry', NULL, NULL, '2025-11-25 15:20:52', '2025-11-25 15:21:11'),
(8, 3, 'ima', 14000.00, 'completed', 'paid', 'online', '2025-11-29', '13:22:00', 'subang', NULL, NULL, '2025-11-26 06:20:40', '2025-12-03 08:05:41'),
(9, 3, 'diva', 31000.00, 'completed', 'paid', 'online', '2025-11-29', '08:30:00', 'jonggol', NULL, NULL, '2025-11-26 06:31:11', '2025-11-26 14:18:20'),
(10, 3, 'jihan', 36000.00, 'completed', 'paid', 'online', '2025-11-27', '01:51:00', 'jonggol', NULL, NULL, '2025-11-26 06:51:41', '2025-11-26 14:13:14'),
(11, 3, 'diva', 16000.00, 'pending', 'pending', 'online', '2025-12-05', '03:09:00', 'polsub', NULL, NULL, '2025-12-03 08:10:06', '2025-12-03 08:10:06'),
(12, 3, 'diva', 22000.00, 'pending', 'pending', 'cash', '2025-12-04', '09:23:00', 'padaasih', NULL, NULL, '2025-12-03 08:24:10', '2025-12-03 08:24:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `service_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 1, 1, 15.00, '2025-11-18 19:33:31'),
(2, 1, 2, 2, 25.00, '2025-11-18 19:33:31'),
(3, 1, 3, 1, 10.00, '2025-11-18 19:33:31'),
(4, 1, 4, 1, 5.00, '2025-11-18 19:33:31'),
(5, 1, 5, 1, 35.00, '2025-11-18 19:33:31'),
(6, 2, 2, 1, 25000.00, '2025-11-25 04:22:42'),
(7, 3, 5, 1, 35000.00, '2025-11-25 08:57:15'),
(8, 4, 1, 1, 15000.00, '2025-11-25 08:59:18'),
(9, 4, 2, 1, 25000.00, '2025-11-25 08:59:18'),
(10, 4, 3, 1, 10000.00, '2025-11-25 08:59:18'),
(11, 4, 4, 1, 5000.00, '2025-11-25 08:59:18'),
(12, 4, 5, 1, 35000.00, '2025-11-25 08:59:18'),
(13, 5, 1, 1, 15000.00, '2025-11-25 09:27:29'),
(14, 5, 2, 1, 25000.00, '2025-11-25 09:27:29'),
(15, 5, 3, 1, 10000.00, '2025-11-25 09:27:29'),
(16, 5, 4, 1, 5000.00, '2025-11-25 09:27:29'),
(17, 5, 5, 1, 35000.00, '2025-11-25 09:27:29'),
(18, 6, 1, 1, 15000.00, '2025-11-25 09:28:17'),
(19, 6, 2, 1, 25000.00, '2025-11-25 09:28:17'),
(20, 6, 3, 1, 10000.00, '2025-11-25 09:28:17'),
(21, 6, 4, 1, 5000.00, '2025-11-25 09:28:17'),
(22, 6, 5, 1, 35000.00, '2025-11-25 09:28:17'),
(23, 7, 4, 3, 12000.00, '2025-11-25 15:20:52'),
(24, 8, 1, 2, 7000.00, '2025-11-26 06:20:40'),
(25, 9, 1, 1, 7000.00, '2025-11-26 06:31:11'),
(26, 9, 3, 1, 8000.00, '2025-11-26 06:31:11'),
(27, 9, 5, 1, 10000.00, '2025-11-26 06:31:11'),
(28, 9, 9, 1, 6000.00, '2025-11-26 06:31:11'),
(29, 10, 4, 3, 12000.00, '2025-11-26 06:51:41'),
(30, 11, 3, 2, 8000.00, '2025-12-03 08:10:06'),
(31, 12, 4, 1, 12000.00, '2025-12-03 08:24:10'),
(32, 12, 5, 1, 10000.00, '2025-12-03 08:24:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `unit`, `image`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Cuci Regular 3 hari (Cuci + Setrika)', 'Layanan cuci biasa untuk pakaian', 7000.00, 'kg', 'washing.png', 1, '2025-11-18 06:07:35', '2025-11-25 14:15:20'),
(2, 'Cuci Ekspress 24 Jam (Cuci + Setrika)', 'Dapatkan pakaian bersih anda dalam waktu 24 jam', 10000.00, 'kg', 'dry-cleaning.png', 1, '2025-11-18 06:07:35', '2025-11-25 14:14:17'),
(3, 'Cuci Regular 2 Hari (Cuci + Setrika)', 'Layanan cuci biasa untuk pakaian', 8000.00, 'kg', 'ironing.png', 1, '2025-11-18 06:07:35', '2025-11-25 14:15:40'),
(4, 'Cuci Ekspress 12 Jam (Cuci + Setrika)', 'Dapatkan pakaian bersih anda dalam waktu 12 jam', 12000.00, 'kg', 'folding.png', 1, '2025-11-18 06:07:35', '2025-11-25 14:14:36'),
(5, 'Layanan Satuan Sprey (3 Hari)', 'Layanan untuk cuci sprey', 10000.00, 'kg', 'express.png', 1, '2025-11-18 06:07:35', '2025-11-25 14:22:56'),
(6, 'Cuci Ekspress 24 Jam (Cuci + Lipat)', 'Dapatkan pakaian bersih anda dalam waktu 24 jam', 8000.00, 'kg', '6925bad36f6da.png', 1, '2025-11-25 14:18:59', '2025-11-25 14:18:59'),
(7, 'Cuci Regular 2 Hari (Cuci + Lipat)', 'Layanan cuci biasa untuk pakaian', 7000.00, 'kg', '6925bb1c0aacb.png', 1, '2025-11-25 14:20:12', '2025-11-25 14:20:12'),
(8, 'Cuci Regular 3 Hari (Cuci + Lipat)', 'Layanan cuci biasa untuk pakaian', 5000.00, 'kg', '6925bb400c48f.png', 1, '2025-11-25 14:20:48', '2025-11-25 14:20:48'),
(9, 'Setrika (2 Hari)', 'Layanan setrika pakaian', 6000.00, 'kg', '6925bb93c076b.png', 1, '2025-11-25 14:22:11', '2025-11-25 14:22:11'),
(10, 'Layanan Satuan Selimut Kecil (3 Hari)', 'Layanan untuk cuci selimut ukuran kecil', 10000.00, 'pcs', '6925bc086bd35.png', 1, '2025-11-25 14:24:08', '2025-11-25 15:05:20'),
(11, 'Layanan Satuan Selimut Sedang (3 Hari)', 'Layanan untuk cuci selimut ukuran sedang', 15000.00, 'pcs', '6925bc5613531.png', 1, '2025-11-25 14:25:26', '2025-11-25 15:05:40'),
(12, 'Layanan Satuan Selimut Besar (3 Hari)', 'Layanan untuk cuci selimut ukuran besar', 20000.00, 'pcs', '6925bc8f28574.png', 1, '2025-11-25 14:26:23', '2025-11-25 15:05:57'),
(13, 'Layanan Satuan Bed Cover (3 Hari)', 'Layanan untuk cuci bed cover', 30000.00, 'pcs', '6925bcefcbf5d.png', 1, '2025-11-25 14:27:59', '2025-11-25 15:06:11'),
(14, 'Layanan Satuan Jaket (3 Hari)', 'Layanan untuk cuci jaket', 10000.00, 'pcs', '6925bd29f1c62.png', 1, '2025-11-25 14:28:58', '2025-11-25 15:07:09'),
(15, 'Layanan Satuan Topi (3 Hari)', 'Layanan untuk cuci topi', 5000.00, 'pcs', '6925bd524f934.png', 1, '2025-11-25 14:29:38', '2025-11-25 15:07:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `user_role` enum('cashier','admin') NOT NULL DEFAULT 'cashier',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `user_role`, `created_at`, `updated_at`) VALUES
(2, 'admin', 'kyuuu1308@gmail.com', '$2y$10$FlAcLU64yBfYyGT.wGgO5u/Vy0.NgL8rj14DvVKnMdtns1KsH3/fq', '085603320626', 'subang', 'admin', '2025-11-18 19:06:38', '2025-11-25 09:32:59'),
(3, 'kasir', 'kasir1@gmail.com', '$2y$10$bG95Av0KZJ8tVDuIfZ.bJ.Wn4zwc1t.ArBFrKpJZ7YvEo8b5h7hg2', '1234567890', 'jonggol', 'cashier', '2025-11-18 19:31:43', '2025-11-26 14:44:53'),
(4, 'diva', 'diva@gmail.com', '$2y$10$1j2XEr/jYdGVmyDkwibNc.M5m0LmM/dbQD4.rp5saC96AhZVfjeV6', '1234566', 'padaasih', 'cashier', '2025-12-03 08:27:19', '2025-12-04 13:11:07');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indeks untuk tabel `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
