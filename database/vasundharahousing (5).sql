-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2025 at 11:41 PM
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
-- Database: `vasundharahousing`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_login`
--

CREATE TABLE `admin_login` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login`
--

INSERT INTO `admin_login` (`id`, `name`, `email`, `password`) VALUES
(1, 'Vasundhara Housing', 'vasundharahousing@gmail.com', 'vh@809');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `request_id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `request_date` date NOT NULL,
  `status` enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `number` int(10) DEFAULT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `number`, `message`) VALUES
(1, 'Poptani Parth', 'poptaniparth@gmail.com', 2147483647, 'Nice home i can book the home for 1 year so please tall me the some information about the home.');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_period` date NOT NULL DEFAULT current_timestamp(),
  `remark` varchar(50) DEFAULT NULL,
  `payment_receive_date` date NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) NOT NULL,
  `status` enum('Paid','Due','Overdue') DEFAULT 'Due'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `tenant_id`, `property_id`, `amount`, `payment_period`, `remark`, `payment_receive_date`, `payment_method`, `status`) VALUES
(1, 6, 11, 5000.00, '2025-09-01', 'Rent for September 2025', '2025-09-13', 'upi-qr-simulation', 'Paid'),
(2, 6, 11, 5000.00, '2025-09-01', 'Rent for September 2025 (UPI ID: poptaniparth@sbi)', '2025-09-13', 'upi', 'Paid'),
(3, 6, 11, 5000.00, '2025-10-01', 'Rent for October 2025 (QR Code)', '2025-09-13', 'upi-qr-simulation', 'Paid'),
(4, 7, 12, 8800.00, '2025-09-01', 'Rent for September 2025 (QR Code)', '2025-09-13', 'upi-qr-simulation', 'Paid'),
(5, 6, 11, 5000.00, '2025-11-01', 'Rent for November 2025 (QR Code)', '2025-09-14', 'upi-qr-simulation', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `pro_id` int(11) NOT NULL,
  `pro_nm` varchar(255) NOT NULL,
  `pro_type` varchar(255) NOT NULL,
  `month_rent` int(11) NOT NULL,
  `status` enum('Available','Unavailable','Maintenance') DEFAULT 'Available',
  `pro_dis` varchar(255) NOT NULL,
  `bed` int(11) NOT NULL,
  `bath` int(11) NOT NULL,
  `area_sq` int(11) NOT NULL,
  `houseno` varchar(255) NOT NULL DEFAULT '0',
  `street` varchar(255) DEFAULT NULL,
  `taluka` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `pincode` int(6) NOT NULL,
  `outdoor_img` varchar(255) NOT NULL,
  `hall_img` varchar(255) NOT NULL,
  `bedroom_img` varchar(255) NOT NULL,
  `kitchen_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`pro_id`, `pro_nm`, `pro_type`, `month_rent`, `status`, `pro_dis`, `bed`, `bath`, `area_sq`, `houseno`, `street`, `taluka`, `district`, `state`, `pincode`, `outdoor_img`, `hall_img`, `bedroom_img`, `kitchen_img`) VALUES
(11, 'Radhe Syam villa', 'Villa', 5000, 'Unavailable', 'Nestled in the heart of Rajkot, Radhe Syam Villa offers spacious living with 4 bedrooms and 4 bathrooms spread across 1500 sq. ft. This elegant villa combines comfort and convenience, featuring a serene atmosphere perfect for families. With a monthly rent', 4, 4, 1500, '15B,Radhe shyam vila', 'Sardar Nagar', 'Rajkot', 'Rajkot', 'Gujarat', 360001, 'uploads/property_images/prop_68c32f579bc58.jpg', 'uploads/property_images/prop_68c32f579c402.jpg', 'uploads/property_images/prop_68c32f579c6a8.jpg', 'uploads/property_images/prop_68c32f579c86c.jpg'),
(12, 'Sunshine villa', 'Bungalows', 8000, 'Unavailable', 'Sunshine Villa is a charming 3-bedroom, 3-bathroom bungalow located in Rajkot, offering 850 sq.ft.. of thoughtfully designed living space. With a monthly rent of ₹8,000, it’s perfect for families seeking a peaceful retreat with modern amenities. Whether y', 3, 3, 850, '15B,Sunshine Apartments', 'Narayan Nagar', 'Amreli', 'Amreli', 'Gujarat', 365220, 'uploads/property_images/prop_68c497290c352.webp', 'uploads/property_images/prop_68c497290d39d.jpg', 'uploads/property_images/prop_68c497290e0ac.jpg', 'uploads/property_images/prop_68c497290e759.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `rental_agreements`
--

CREATE TABLE `rental_agreements` (
  `agreement_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `pro_id` int(11) NOT NULL,
  `tenantName` varchar(255) NOT NULL,
  `fatherName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `number` varchar(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `aadharNumber` varchar(12) DEFAULT NULL,
  `month_rent_no` int(11) NOT NULL,
  `month_rent_word` varchar(255) NOT NULL,
  `starting_date` date NOT NULL,
  `ending_date` date NOT NULL,
  `place` varchar(255) NOT NULL,
  `witness1_name` varchar(255) DEFAULT NULL,
  `witness1_aadhar` varchar(255) NOT NULL,
  `witness2_name` varchar(255) DEFAULT NULL,
  `witness2_aadhar` varchar(255) NOT NULL,
  `tenant_photo` varchar(255) NOT NULL,
  `tenant_aadhar` varchar(255) NOT NULL,
  `tenant_sign` varchar(255) NOT NULL,
  `witness1_sign` varchar(255) NOT NULL,
  `witness2_sign` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_agreements`
--

INSERT INTO `rental_agreements` (`agreement_id`, `tenant_id`, `pro_id`, `tenantName`, `fatherName`, `email`, `number`, `address`, `aadharNumber`, `month_rent_no`, `month_rent_word`, `starting_date`, `ending_date`, `place`, `witness1_name`, `witness1_aadhar`, `witness2_name`, `witness2_aadhar`, `tenant_photo`, `tenant_aadhar`, `tenant_sign`, `witness1_sign`, `witness2_sign`) VALUES
(1, 6, 11, 'Poptani Parth', 'Poptani Hasmukhbhai', 'poptaniparth2@gmail.com', '7201918520', 'Near Swaminarayan Temple, Ansodar', '145214521453', 5000, 'Five Thousand ', '2025-09-12', '2025-09-16', 'Ansodar', 'Vishal Makavana', 'uploads/tenant_documents/img_68c41732c6ae0.jpg', 'Tushar Vaghela', 'uploads/tenant_documents/img_68c41732c6cd9.jpg', 'uploads/tenant_documents/img_68c41732c6284.jpg', 'uploads/tenant_documents/img_68c41732c66f2.jpg', 'uploads/tenant_documents/img_68c41732c6901.jpg', 'uploads/tenant_documents/img_68c41732c7000.jpg', 'uploads/tenant_documents/img_68c41732c7326.jpg'),
(2, 7, 12, 'Poptani Vaibhav', 'Poptani Hasmukhbhai', 'vaibhavpoptani@gmail.com', '7418529630', 'Near Swaminarayan Temple, Ansodar', '123456789123', 8000, '', '2025-09-13', '2026-08-13', 'Ansodar', 'Poptani Parth', 'uploads/tenant_documents/img_68c4981a70a56.jpg', 'Poptani Dharmeshbhai', 'uploads/tenant_documents/img_68c4981a70e7d.jpg', 'uploads/tenant_documents/img_68c4981a6d9bb.jpg', 'uploads/tenant_documents/img_68c4981a701e8.jpg', 'uploads/tenant_documents/img_68c4981a7064a.jpg', 'uploads/tenant_documents/img_68c4981a712cd.jpg', 'uploads/tenant_documents/img_68c4981a71682.jpg'),
(5, 6, 11, 'Poptani Parth', 'Poptani Hasmukhbhai', 'poptaniparth2@gmail.com', '7201918520', 'Near Swaminarayan Temple, Ansodar', '145214521453', 5000, 'Five Thousand ', '2025-09-20', '2025-09-16', 'Ansodar', 'Vishal Makavana', 'uploads/tenant_documents/img_68c41732c6ae0.jpg', 'Tushar Vaghela', 'uploads/tenant_documents/img_68c41732c6cd9.jpg', 'uploads/tenant_documents/img_68c41732c6284.jpg', 'uploads/tenant_documents/img_68c41732c66f2.jpg', 'uploads/tenant_documents/img_68c41732c6901.jpg', 'uploads/tenant_documents/img_68c41732c7000.jpg', 'uploads/tenant_documents/img_68c41732c7326.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tenantnotice`
--

CREATE TABLE `tenantnotice` (
  `notice_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenantnotice`
--

INSERT INTO `tenantnotice` (`notice_id`, `tenant_id`, `subject`, `message`, `recipient`, `created_at`, `updated_at`) VALUES
(1, 6, 'Payment Due Reminder for Your Rent', 'Dear Tenant,\n\nThis is a friendly reminder that your rent payment of ₹5,000 for the period of September 2025 is currently due. Please make the payment at your earliest convenience to avoid any late fees.\n\nYou can pay your rent through your tenant dashboard.\n\nThank you,\nVasundhara Housing Management', '', '2025-09-12 21:45:36', '2025-09-12 21:45:36'),
(2, 6, 'Thank for the Booking....', 'Thank for the Booking....', 'Poptani Parth', '2025-09-14 16:09:06', '2025-09-14 16:09:06');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `tenant_id` int(11) NOT NULL,
  `tenant_name` varchar(255) NOT NULL,
  `profile_photo` varchar(255) NOT NULL,
  `contact_number` varchar(10) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `status` enum('Active','Unactive') DEFAULT 'Unactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `profile_photo`, `contact_number`, `email`, `property_id`, `status`, `created_at`, `password`) VALUES
(6, 'Poptani Parth', 'uploads/profile_photos/Poptani_Parth_6.jpg', '7201918520', 'poptaniparth2@gmail.com', 11, 'Active', '2025-09-12 12:35:03', '$2y$10$fFpD/2uGnf2jel5Zzc5SIegL/w8PXdH8kF8vBg6ehZ3Q2Qi0Lo7JC'),
(7, 'Poptani Vaibhav', '', '7418529630', 'vaibhavpoptani@gmail.com', 12, 'Active', '2025-09-12 21:59:57', '$2y$10$2iW/M62zHKhHzhjg7opT3.RCu5NcbX4l9lNDXmfvumGn64WJtM8wS');

-- --------------------------------------------------------

--
-- Table structure for table `termination_requests`
--

CREATE TABLE `termination_requests` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `admin_remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `termination_requests`
--

INSERT INTO `termination_requests` (`id`, `tenant_id`, `property_id`, `request_date`, `reason`, `status`, `admin_remark`) VALUES
(1, 6, 11, '2025-09-14 19:27:22', 'i would not like the home architecture. and neighbors was not batter.\n', 'Rejected', 'lease period not completed, understating dues found');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_login`
--
ALTER TABLE `admin_login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`pro_id`);

--
-- Indexes for table `rental_agreements`
--
ALTER TABLE `rental_agreements`
  ADD PRIMARY KEY (`agreement_id`),
  ADD KEY `fk_rental_tenant` (`tenant_id`),
  ADD KEY `fk_rental_property` (`pro_id`);

--
-- Indexes for table `tenantnotice`
--
ALTER TABLE `tenantnotice`
  ADD PRIMARY KEY (`notice_id`),
  ADD KEY `fk_tenant_notice` (`tenant_id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`tenant_id`),
  ADD KEY `tenants_ibfk_1` (`property_id`);

--
-- Indexes for table `termination_requests`
--
ALTER TABLE `termination_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_termination_tenant` (`tenant_id`),
  ADD KEY `fk_termination_property` (`property_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_login`
--
ALTER TABLE `admin_login`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `pro_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rental_agreements`
--
ALTER TABLE `rental_agreements`
  MODIFY `agreement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tenantnotice`
--
ALTER TABLE `tenantnotice`
  MODIFY `notice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `termination_requests`
--
ALTER TABLE `termination_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`pro_id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`pro_id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_agreements`
--
ALTER TABLE `rental_agreements`
  ADD CONSTRAINT `fk_rental_property` FOREIGN KEY (`pro_id`) REFERENCES `properties` (`pro_id`) ON DELETE CASCADE;

--
-- Constraints for table `tenantnotice`
--
ALTER TABLE `tenantnotice`
  ADD CONSTRAINT `fk_tenant_notice` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`pro_id`);

--
-- Constraints for table `termination_requests`
--
ALTER TABLE `termination_requests`
  ADD CONSTRAINT `fk_termination_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`pro_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_termination_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
