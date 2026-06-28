-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 31, 2025 at 03:09 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `palliative`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `name`, `email`, `role`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, 'System Admin', 'admin@palliative.care', 'super_admin', '2025-03-31 13:43:38', '2025-03-11 16:55:16', '2025-03-31 13:43:38'),
(2, 7, 'Kimi Raikonnen', 'kimibowah@admin.com', 'admin', '2025-03-12 13:36:00', '2025-03-12 13:35:53', '2025-03-12 13:36:00'),
(3, 45, 'Ozymandias', 'smtppalliativecare@gmail.com', 'super_admin', '2025-03-31 13:46:41', '2025-03-31 13:46:26', '2025-03-31 13:46:41');

-- --------------------------------------------------------

--
-- Table structure for table `admin_tokens`
--

DROP TABLE IF EXISTS `admin_tokens`;
CREATE TABLE IF NOT EXISTS `admin_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `admin_level` enum('standard','super') NOT NULL DEFAULT 'standard',
  `generated_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `used_by` int DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `generated_by` (`generated_by`),
  KEY `used_by` (`used_by`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_tokens`
--

INSERT INTO `admin_tokens` (`id`, `token`, `admin_level`, `generated_by`, `created_at`, `expires_at`, `is_used`, `used_by`, `used_at`) VALUES
(1, '067e0b9aead9b07e0019200790d368879d7a1143caceaf768d2fab19a9f9d514', 'standard', 1, '2025-03-12 13:04:26', '2025-03-13 07:34:26', 1, 7, '2025-03-12 13:35:53'),
(2, '70efbe3489c2a6ea2c197ad02497f0f6dcc6b851672c16617e50e061da40d7ea', 'super', 1, '2025-03-12 17:45:19', '2025-03-13 12:15:19', 0, NULL, NULL),
(3, '5f5aeee6d3d78f5ba273bd2d6aefc8e62bdecec74f93c4f762aa0b44c47be05d', 'super', 1, '2025-03-17 17:58:58', '2025-03-18 12:28:58', 0, NULL, NULL),
(4, '41d3355b97dda4b885ed65d47fc0d9a3b2de005c1e7f597026f0c9a77d6e96d1', 'super', 1, '2025-03-31 13:44:16', '2025-04-01 08:14:16', 1, 45, '2025-03-31 13:46:26');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appointment_date` datetime NOT NULL,
  `reason` text,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `reminder_sent` tinyint(1) DEFAULT '0',
  `reminder_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `idx_appointment_date` (`appointment_date`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `reason`, `status`, `notes`, `created_at`, `updated_at`, `payment_status`, `reminder_sent`, `reminder_sent_at`) VALUES
(1, 1, 1, '2025-03-13 10:00:00', 'Sore neck', 'confirmed', NULL, '2025-03-11 17:21:54', '2025-03-11 19:11:05', 'pending', 0, NULL),
(2, 1, 1, '2025-03-31 22:52:00', 'Hamstring Injury', 'confirmed', NULL, '2025-03-11 17:23:16', '2025-03-11 19:11:15', 'pending', 0, NULL),
(3, 1, 1, '2025-03-11 23:25:00', 'leg injury', 'confirmed', NULL, '2025-03-11 17:55:39', '2025-03-11 19:10:56', 'pending', 0, NULL),
(4, 1, 1, '2025-04-30 16:30:00', 'Cancer', 'completed', NULL, '2025-03-11 18:09:56', '2025-03-18 09:37:54', 'pending', 0, NULL),
(5, 1, 1, '2025-03-12 00:50:00', 'tumor', 'completed', NULL, '2025-03-11 19:18:25', '2025-03-11 19:24:52', 'pending', 0, NULL),
(6, 1, 1, '2025-03-12 12:01:00', 'Skin cancer', 'cancelled', NULL, '2025-03-12 05:31:19', '2025-03-12 05:38:42', 'pending', 0, NULL),
(7, 1, 3, '2025-03-28 16:14:00', 'High blood pressure', 'pending', NULL, '2025-03-14 05:43:25', '2025-03-14 05:43:25', 'pending', 0, NULL),
(8, 4, 3, '2025-03-19 12:30:00', 'Allergies', 'pending', NULL, '2025-03-16 13:51:44', '2025-03-16 13:51:44', 'pending', 0, NULL),
(9, 1, 1, '2025-03-17 13:30:00', 'Headache', 'cancelled', NULL, '2025-03-17 07:37:25', '2025-03-17 07:41:00', 'pending', 0, NULL),
(10, 1, 1, '2025-03-17 16:15:00', 'Stomach Ache', 'completed', NULL, '2025-03-17 07:46:14', '2025-03-17 12:39:21', 'pending', 0, NULL),
(11, 1, 18, '2025-03-20 14:02:00', 'Infectious Disease', 'confirmed', NULL, '2025-03-17 17:32:23', '2025-03-17 17:44:51', 'paid', 0, NULL),
(12, 1, 1, '2025-03-19 15:30:00', 'lol', 'cancelled', NULL, '2025-03-18 09:00:48', '2025-03-31 09:15:46', 'paid', 0, NULL),
(13, 1, 1, '2025-03-18 16:45:00', 'Headache', 'confirmed', NULL, '2025-03-18 09:15:30', '2025-03-18 09:15:34', 'paid', 0, NULL),
(14, 1, 1, '2025-03-18 14:00:00', 'Headache', 'cancelled', NULL, '2025-03-18 09:21:02', '2025-03-18 09:22:16', 'paid', 0, NULL),
(15, 1, 1, '2025-03-18 17:55:00', 'Headache', 'cancelled', NULL, '2025-03-18 09:22:34', '2025-03-31 09:15:43', 'paid', 0, NULL),
(16, 1, 1, '2025-03-18 17:55:00', 'Headache', 'confirmed', NULL, '2025-03-18 09:28:39', '2025-03-18 09:28:46', 'paid', 0, NULL),
(17, 1, 1, '2025-03-18 15:00:00', 'Headache', 'cancelled', NULL, '2025-03-18 09:30:57', '2025-03-31 09:15:49', 'paid', 0, NULL),
(18, 1, 1, '2025-03-18 15:00:00', 'Headache', 'confirmed', NULL, '2025-03-18 09:34:01', '2025-03-18 09:34:05', 'paid', 0, NULL),
(19, 1, 1, '2025-03-18 15:10:00', 'Headache', 'cancelled', NULL, '2025-03-18 09:34:51', '2025-03-18 09:36:22', 'paid', 0, NULL),
(20, 1, 1, '2025-03-18 18:09:00', 'Headache', 'confirmed', NULL, '2025-03-18 09:36:43', '2025-03-31 06:20:50', 'paid', 0, NULL),
(21, 5, 1, '2025-03-31 12:30:00', 'Headache', 'completed', NULL, '2025-03-31 06:07:21', '2025-03-31 06:25:20', 'paid', 0, NULL),
(22, 5, 1, '2025-04-01 11:50:00', 'Shoulder dislocation', 'completed', NULL, '2025-03-31 06:18:34', '2025-03-31 09:15:31', 'paid', 1, '2025-03-31 12:01:22'),
(23, 5, 1, '2025-04-01 11:55:00', 'Headache', 'pending', NULL, '2025-03-31 06:24:18', '2025-03-31 06:25:40', 'paid', 0, NULL),
(24, 5, 1, '2025-03-31 15:03:00', 'Pulled Hamstring', 'pending', NULL, '2025-03-31 06:33:27', '2025-03-31 06:33:31', 'paid', 0, NULL),
(25, 5, 7, '2025-03-31 20:51:00', 'Covid', 'pending', NULL, '2025-03-31 09:21:42', '2025-03-31 09:21:46', 'paid', 0, NULL),
(26, 5, 12, '2025-03-31 18:55:00', 'Cough', 'pending', NULL, '2025-03-31 09:26:07', '2025-03-31 09:26:11', 'paid', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cab_bookings`
--

DROP TABLE IF EXISTS `cab_bookings`;
CREATE TABLE IF NOT EXISTS `cab_bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `provider_id` int NOT NULL,
  `pickup_address` text NOT NULL,
  `destination` text NOT NULL,
  `pickup_datetime` datetime NOT NULL,
  `cab_type` enum('standard','wheelchair','stretcher') NOT NULL DEFAULT 'standard',
  `special_requirements` text,
  `status` enum('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `estimated_fare` decimal(10,2) DEFAULT '0.00',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `confirmed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `provider_notes` text,
  `reminder_sent` tinyint(1) DEFAULT '0',
  `reminder_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cab_bookings`
--

INSERT INTO `cab_bookings` (`id`, `patient_id`, `provider_id`, `pickup_address`, `destination`, `pickup_datetime`, `cab_type`, `special_requirements`, `status`, `created_at`, `updated_at`, `estimated_fare`, `payment_status`, `confirmed_at`, `completed_at`, `cancelled_at`, `provider_notes`, `reminder_sent`, `reminder_sent_at`) VALUES
(1, 1, 1, '123 Bleeker Street, New York', '789 Oak Road, Westside', '2025-03-13 12:00:00', 'stretcher', 'Hamstring injury', 'confirmed', '2025-03-12 11:05:22', '2025-03-31 10:29:50', 0.00, 'pending', '2025-03-28 05:27:50', NULL, NULL, 'Pick him up', 0, NULL),
(2, 1, 1, ' 456 Elm Street, Denver, CO, USA', '123 Main Street, City Center', '2025-03-15 15:00:00', 'stretcher', '', 'pending', '2025-03-14 11:14:42', NULL, 0.00, 'pending', NULL, NULL, NULL, NULL, 0, NULL),
(3, 1, 1, ' 456 Elm Street, Denver, CO, USA', '123 Main Street, City Center', '2025-03-15 06:50:00', 'standard', '', 'pending', '2025-03-14 13:08:13', NULL, 0.00, 'pending', NULL, NULL, NULL, NULL, 0, NULL),
(4, 4, 1, 'House Atreides Castle Caladan\r\nPlanet Caladan', '789 Oak Road, Westside', '2025-03-16 19:30:00', 'stretcher', '', 'pending', '2025-03-16 19:20:40', NULL, 0.00, 'pending', NULL, NULL, NULL, NULL, 0, NULL),
(5, 1, 1, ' 456 Elm Street, Denver, CO, USA', '456 Park Avenue, Downtown', '2025-03-28 13:10:00', 'wheelchair', '', 'cancelled', '2025-03-17 13:06:46', NULL, 0.00, 'pending', NULL, NULL, '2025-03-28 05:27:54', NULL, 0, NULL),
(6, 1, 1, ' 456 Elm Street, Denver, CO, USA', '456 Park Avenue, Downtown', '2025-03-27 14:36:00', 'wheelchair', '', 'completed', '2025-03-18 14:33:22', NULL, 250.00, 'paid', NULL, '2025-03-28 05:27:41', NULL, NULL, 0, NULL),
(7, 1, 1, ' 456 Elm Street, Denver, CO, USA', '456 Park Avenue, Downtown', '2025-03-21 14:36:00', 'wheelchair', '', 'confirmed', '2025-03-18 14:33:44', NULL, 250.00, 'paid', NULL, NULL, NULL, NULL, 0, NULL),
(8, 1, 1, ' 456 Elm Street, Denver, CO, USA', '123 Main Street, City Center', '2025-03-29 07:57:00', 'wheelchair', '', 'completed', '2025-03-28 04:57:57', NULL, 250.00, 'paid', NULL, '2025-03-31 19:34:48', NULL, NULL, 0, NULL),
(9, 5, 1, '1234 Example Street\r\nSuite 567\r\nFaketown, FT 98765\r\nUSA', '456 Park Avenue, Downtown', '2025-04-01 14:15:00', 'stretcher', 'Leg surgery', 'completed', '2025-03-31 19:07:40', NULL, 350.00, 'paid', NULL, '2025-03-31 19:35:06', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `appointment_id` int NOT NULL,
  `sender_type` enum('patient','doctor') NOT NULL,
  `sender_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `idx_sender` (`sender_type`,`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diseases`
--

DROP TABLE IF EXISTS `diseases`;
CREATE TABLE IF NOT EXISTS `diseases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `treatment` text,
  `severity_level` enum('low','medium','high') DEFAULT 'medium',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `diseases`
--

INSERT INTO `diseases` (`id`, `name`, `description`, `treatment`, `severity_level`, `created_at`, `updated_at`) VALUES
(1, 'Common Cold', 'A viral infectious disease of the upper respiratory tract that primarily affects the nose', 'Rest, fluids, over-the-counter medications for symptom relief', 'low', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(2, 'Influenza', 'A contagious respiratory illness caused by influenza viruses', 'Antiviral medications, rest, fluids, pain relievers', 'medium', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(3, 'COVID-19', 'A respiratory illness caused by the SARS-CoV-2 virus', 'Supportive care, antiviral medications, rest, isolation', 'high', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(4, 'Strep Throat', 'A bacterial infection that can make your throat feel sore and scratchy', 'Antibiotics, pain relievers, rest, warm liquids', 'medium', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(5, 'Bronchitis', 'Inflammation of the lining of the bronchial tubes', 'Rest, fluids, over-the-counter medications, humidifier', 'medium', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(6, 'Pneumonia', 'Infection that inflames air sacs in one or both lungs', 'Antibiotics, rest, fluids, oxygen therapy if severe', 'high', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(7, 'Sinusitis', 'Inflammation or swelling of the tissue lining the sinuses', 'Nasal decongestants, pain relievers, nasal irrigation', 'low', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(8, 'Gastroenteritis', 'Inflammation of the stomach and intestines', 'Fluid replacement, rest, gradual reintroduction of food', 'medium', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(9, 'Migraine', 'A headache of varying intensity, often accompanied by nausea and sensitivity to light and sound', 'Pain relievers, triptans, preventive medications', 'medium', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(10, 'Hypertension', 'High blood pressure', 'Lifestyle changes, medications to lower blood pressure', 'medium', '2025-03-17 15:16:49', '2025-03-17 15:16:49');

-- --------------------------------------------------------

--
-- Table structure for table `disease_specializations`
--

DROP TABLE IF EXISTS `disease_specializations`;
CREATE TABLE IF NOT EXISTS `disease_specializations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `disease_id` int NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `disease_specialization_unique` (`disease_id`,`specialization`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `disease_specializations`
--

INSERT INTO `disease_specializations` (`id`, `disease_id`, `specialization`, `created_at`) VALUES
(1, 1, 'General Practice', '2025-03-17 15:16:50'),
(2, 1, 'Family Medicine', '2025-03-17 15:16:50'),
(3, 2, 'General Practice', '2025-03-17 15:16:50'),
(4, 2, 'Infectious Disease', '2025-03-17 15:16:50'),
(5, 3, 'Infectious Disease', '2025-03-17 15:16:50'),
(6, 3, 'Pulmonology', '2025-03-17 15:16:50'),
(7, 4, 'Otolaryngology', '2025-03-17 15:16:50'),
(8, 4, 'General Practice', '2025-03-17 15:16:50'),
(9, 5, 'Pulmonology', '2025-03-17 15:16:50'),
(10, 5, 'General Practice', '2025-03-17 15:16:50'),
(11, 6, 'Pulmonology', '2025-03-17 15:16:50'),
(12, 6, 'Infectious Disease', '2025-03-17 15:16:50'),
(13, 7, 'Otolaryngology', '2025-03-17 15:16:50'),
(14, 7, 'General Practice', '2025-03-17 15:16:50'),
(15, 8, 'Gastroenterology', '2025-03-17 15:16:50'),
(16, 8, 'General Practice', '2025-03-17 15:16:50'),
(17, 9, 'Neurology', '2025-03-17 15:16:50'),
(18, 9, 'General Practice', '2025-03-17 15:16:50'),
(19, 10, 'Cardiology', '2025-03-17 15:16:50'),
(20, 10, 'Internal Medicine', '2025-03-17 15:16:50');

-- --------------------------------------------------------

--
-- Table structure for table `disease_symptoms`
--

DROP TABLE IF EXISTS `disease_symptoms`;
CREATE TABLE IF NOT EXISTS `disease_symptoms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `disease_id` int NOT NULL,
  `symptom_id` int NOT NULL,
  `severity` enum('mild','moderate','severe') DEFAULT 'moderate',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `disease_symptom_unique` (`disease_id`,`symptom_id`),
  KEY `symptom_id` (`symptom_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `disease_symptoms`
--

INSERT INTO `disease_symptoms` (`id`, `disease_id`, `symptom_id`, `severity`, `created_at`) VALUES
(1, 1, 3, 'moderate', '2025-03-17 15:16:50'),
(2, 1, 5, 'moderate', '2025-03-17 15:16:50'),
(3, 1, 6, 'severe', '2025-03-17 15:16:50'),
(4, 1, 4, 'mild', '2025-03-17 15:16:50'),
(5, 2, 1, 'severe', '2025-03-17 15:16:50'),
(6, 2, 2, 'moderate', '2025-03-17 15:16:50'),
(7, 2, 3, 'moderate', '2025-03-17 15:16:50'),
(8, 2, 4, 'severe', '2025-03-17 15:16:50'),
(9, 2, 7, 'severe', '2025-03-17 15:16:50'),
(10, 2, 16, 'moderate', '2025-03-17 15:16:50'),
(11, 3, 1, 'moderate', '2025-03-17 15:16:50'),
(12, 3, 3, 'severe', '2025-03-17 15:16:50'),
(13, 3, 4, 'severe', '2025-03-17 15:16:50'),
(14, 3, 8, 'severe', '2025-03-17 15:16:50'),
(15, 3, 17, 'moderate', '2025-03-17 15:16:50'),
(16, 4, 1, 'moderate', '2025-03-17 15:16:50'),
(17, 4, 5, 'severe', '2025-03-17 15:16:50'),
(18, 4, 2, 'mild', '2025-03-17 15:16:50'),
(19, 4, 19, 'moderate', '2025-03-17 15:16:50'),
(20, 5, 3, 'severe', '2025-03-17 15:16:50'),
(21, 5, 8, 'moderate', '2025-03-17 15:16:50'),
(22, 5, 9, 'mild', '2025-03-17 15:16:50'),
(23, 5, 4, 'moderate', '2025-03-17 15:16:50'),
(24, 6, 1, 'severe', '2025-03-17 15:16:50'),
(25, 6, 3, 'severe', '2025-03-17 15:16:50'),
(26, 6, 8, 'severe', '2025-03-17 15:16:50'),
(27, 6, 9, 'moderate', '2025-03-17 15:16:50'),
(28, 6, 4, 'severe', '2025-03-17 15:16:50'),
(29, 6, 16, 'moderate', '2025-03-17 15:16:50'),
(30, 7, 2, 'severe', '2025-03-17 15:16:50'),
(31, 7, 6, 'moderate', '2025-03-17 15:16:50'),
(32, 7, 5, 'mild', '2025-03-17 15:16:50'),
(33, 8, 10, 'severe', '2025-03-17 15:16:50'),
(34, 8, 11, 'moderate', '2025-03-17 15:16:50'),
(35, 8, 12, 'severe', '2025-03-17 15:16:50'),
(36, 8, 18, 'moderate', '2025-03-17 15:16:50'),
(37, 9, 2, 'severe', '2025-03-17 15:16:50'),
(38, 9, 10, 'moderate', '2025-03-17 15:16:50'),
(39, 9, 15, 'moderate', '2025-03-17 15:16:50'),
(40, 10, 2, 'moderate', '2025-03-17 15:16:50'),
(41, 10, 15, 'mild', '2025-03-17 15:16:50'),
(42, 10, 9, 'mild', '2025-03-17 15:16:50');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

DROP TABLE IF EXISTS `doctors`;
CREATE TABLE IF NOT EXISTS `doctors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `experience_years` int DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `availability_status` enum('available','unavailable') DEFAULT 'available',
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `profile_image` varchar(255) DEFAULT NULL,
  `hospital_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `license_number` (`license_number`),
  KEY `hospital_id` (`hospital_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `name`, `email`, `phone`, `specialization`, `qualification`, `experience_years`, `license_number`, `availability_status`, `consultation_fee`, `created_at`, `updated_at`, `profile_image`, `hospital_id`) VALUES
(1, 3, 'Dr. John Smith', 'doctor@test.com', '555-987-6543', 'Internal Medicine', 'MD, PhD', 15, 'MED12345', 'available', 250.00, '2025-03-11 16:55:30', '2025-03-17 16:57:53', 'uploads/profile_images/doctor_1_1742190006.jpg', 1),
(3, 6, 'Dr. James Anderson', 'james.anderson@example.com', NULL, 'Cardiology', NULL, 20, 'CAR123456', 'available', 250.00, '2025-03-12 10:47:04', '2025-03-16 07:00:34', NULL, 2),
(4, 26, 'Dr. Sarah Smith', 'dr.smith@palliative.com', '555-111-2222', 'General Practice', 'MD', 8, 'GP12345', 'available', 150.00, '2025-03-17 15:55:37', '2025-03-17 16:52:42', NULL, 1),
(5, 27, 'Dr. Michael Johnson', 'dr.johnson@palliative.com', '555-222-3333', 'Cardiology', 'MD, PhD', 12, 'CARD6789', 'available', 250.00, '2025-03-17 15:55:37', '2025-03-17 16:57:53', NULL, 2),
(6, 28, 'Dr. Anita Patel', 'dr.patel@palliative.com', '555-333-4444', 'Neurology', 'MD', 10, 'NEUR7890', 'available', 225.00, '2025-03-17 15:55:37', '2025-03-17 16:57:53', NULL, 1),
(7, 29, 'Dr. Robert Wilson', 'dr.wilson@palliative.com', '555-444-5555', 'Pulmonology', 'MD', 15, 'PULM1234', 'available', 200.00, '2025-03-17 15:55:37', '2025-03-17 16:57:53', NULL, 2),
(8, 30, 'Dr. Li Chen', 'dr.chen@palliative.com', '555-555-6666', 'Infectious Disease', 'MD, MPH', 9, 'INFD5678', 'available', 175.00, '2025-03-17 15:55:37', '2025-03-17 15:55:37', NULL, 1),
(9, 31, 'Dr. Maria Garcia', 'dr.garcia@palliative.com', '555-666-7777', 'Otolaryngology', 'MD', 7, 'ENT9012', 'available', 180.00, '2025-03-17 15:55:37', '2025-03-17 16:52:42', NULL, 2),
(10, 32, 'Dr. James Brown', 'dr.brown@palliative.com', '555-777-8888', 'Gastroenterology', 'MD', 11, 'GAST3456', 'available', 210.00, '2025-03-17 15:55:37', '2025-03-17 16:57:53', NULL, 1),
(11, 33, 'Dr. Emily Taylor', 'dr.taylor@palliative.com', '555-888-9999', 'Family Medicine', 'MD', 6, 'FAM7890', 'available', 140.00, '2025-03-17 15:55:37', '2025-03-17 15:55:37', NULL, 1),
(12, 34, 'Dr. Parker Smith', 'parker.smith@example.com', NULL, 'General Practice', NULL, 15, 'MED2394', 'available', 100.00, '2025-03-17 16:08:58', '2025-03-31 13:50:00', NULL, 1),
(13, 35, 'Dr. Sarah Johnson', 'sarah.johnson@example.com', NULL, 'Family Medicine', NULL, 12, NULL, 'available', 90.00, '2025-03-17 16:08:58', '2025-03-17 16:08:58', NULL, 1),
(14, 36, 'Dr. Michael Chen', 'michael.chen@example.com', NULL, 'Cardiology', NULL, 20, NULL, 'available', 200.00, '2025-03-17 16:08:58', '2025-03-17 16:57:53', NULL, 2),
(15, 37, 'Dr. Emily Brown', 'emily.brown@example.com', NULL, 'Neurology', NULL, 18, 'LOL12335', 'available', 180.00, '2025-03-17 16:08:58', '2025-03-17 18:11:16', NULL, 1),
(16, 38, 'Dr. David Wilson', 'david.wilson@example.com', NULL, 'Pulmonology', NULL, 16, 'LOL12333', 'available', 160.00, '2025-03-17 16:08:58', '2025-03-17 18:10:56', NULL, 2),
(17, 39, 'Dr. Lisa Anderson', 'lisa.anderson@example.com', NULL, 'Otolaryngology', NULL, 14, NULL, 'available', 140.00, '2025-03-17 16:08:58', '2025-03-17 16:52:42', NULL, 1),
(18, 40, 'Dr. Robert Taylor', 'robert.taylor@example.com', NULL, 'Infectious Disease', NULL, 17, NULL, 'available', 170.00, '2025-03-17 16:08:58', '2025-03-17 16:08:58', NULL, 1),
(19, 41, 'Dr. Jennifer Lee', 'jennifer.lee@example.com', NULL, 'Dermatology', NULL, 13, NULL, 'available', 130.00, '2025-03-17 16:08:58', '2025-03-17 16:52:42', NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

DROP TABLE IF EXISTS `hospitals`;
CREATE TABLE IF NOT EXISTS `hospitals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `name`, `address`, `phone`, `email`, `website`, `status`, `created_at`, `updated_at`) VALUES
(1, 'City General Hospital', '123 Main Street, City Center', '555-1234', 'info@citygeneral.com', 'www.citygeneral.com', 'active', '2025-03-11 23:12:07', NULL),
(2, 'Memorial Medical Center', '456 Park Avenue, Downtown', '555-5678', 'contact@memorialmed.com', 'www.memorialmed.com', 'active', '2025-03-11 23:12:07', NULL),
(3, 'St. Johns Hospital', '789 Oak Road, Westside', '555-9012', 'info@stjohns.com', 'www.stjohns.com', 'active', '2025-03-11 23:12:07', NULL),
(4, 'City General Hospital', '123 Main Street, City Center', '555-1234', 'info@citygeneral.com', 'www.citygeneral.com', 'active', '2025-03-11 23:24:37', NULL),
(5, 'Memorial Medical Center', '456 Park Avenue, Downtown', '555-5678', 'contact@memorialmed.com', 'www.memorialmed.com', 'active', '2025-03-11 23:24:37', NULL),
(6, 'St. Johns Hospital', '789 Oak Road, Westside', '555-9012', 'info@stjohns.com', 'www.stjohns.com', 'active', '2025-03-11 23:24:37', NULL),
(7, 'City General Hospital', '123 Main Street, City Center', '555-1234', 'info@citygeneral.com', 'www.citygeneral.com', 'active', '2025-03-11 23:24:41', NULL),
(8, 'Memorial Medical Center', '456 Park Avenue, Downtown', '555-5678', 'contact@memorialmed.com', 'www.memorialmed.com', 'active', '2025-03-11 23:24:41', NULL),
(9, 'St. Johns Hospital', '789 Oak Road, Westside', '555-9012', 'info@stjohns.com', 'www.stjohns.com', 'active', '2025-03-11 23:24:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

DROP TABLE IF EXISTS `medical_history`;
CREATE TABLE IF NOT EXISTS `medical_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `condition` varchar(255) NOT NULL,
  `notes` text,
  `recorded_date` date NOT NULL,
  `recorded_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `recorded_by` (`recorded_by`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medical_history`
--

INSERT INTO `medical_history` (`id`, `patient_id`, `condition`, `notes`, `recorded_date`, `recorded_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Initial consultation', 'Patient reported initial symptoms and medical history was recorded', '2025-02-10', 3, '2025-03-11 19:51:19', '2025-03-11 19:51:19'),
(2, 1, 'Follow-up visit', 'Patient showing improvement in overall condition', '2025-02-25', 3, '2025-03-11 19:51:19', '2025-03-11 19:51:19');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

DROP TABLE IF EXISTS `medicines`;
CREATE TABLE IF NOT EXISTS `medicines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pharmacy_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `category` enum('tablets','capsules','syrups','injections','topical','other') NOT NULL,
  `unit` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `reorder_level` int DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `storage_instructions` text,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `requires_prescription` tinyint(1) DEFAULT '0',
  `status` enum('active','out_of_stock','discontinued') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pharmacy_status` (`pharmacy_id`,`status`),
  KEY `idx_medicine_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `pharmacy_id`, `name`, `description`, `category`, `unit`, `price`, `stock_quantity`, `reorder_level`, `manufacturer`, `storage_instructions`, `batch_number`, `expiry_date`, `requires_prescription`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Paracetamol 500mg', 'Analgesic and antipyretic medication used to treat pain and fever', 'tablets', 'tablet', 5.00, 250, 50, 'Acme Pharmaceuticals', 'Store in a cool, dry place away from direct sunlight', 'BATCH001', '2026-06-30', 0, 'active', '2025-03-20 10:00:00', '2025-03-20 10:00:00'),
(2, 1, 'Amoxicillin 250mg', 'Broad-spectrum antibiotic used to treat bacterial infections', 'capsules', 'capsule', 12.50, 140, 30, 'MedLife Labs', 'Store below 25°C in a dry place', 'BATCH002', '2026-04-15', 1, 'active', '2025-03-20 10:01:00', '2025-03-31 14:27:21'),
(3, 1, 'Ibuprofen 400mg', 'Non-steroidal anti-inflammatory drug (NSAID) used to treat pain and inflammation', 'tablets', 'tablet', 7.50, 180, 40, 'HealthPharma Inc.', 'Store at room temperature', 'BATCH003', '2026-05-20', 0, 'active', '2025-03-20 10:02:00', '2025-03-20 10:02:00'),
(4, 1, 'Salbutamol Inhaler', 'Bronchodilator used to treat asthma and COPD symptoms', 'other', 'inhaler', 120.00, 25, 10, 'AeroMed Pharmaceuticals', 'Store at room temperature away from heat and moisture', 'BATCH004', '2025-12-10', 1, 'active', '2025-03-20 10:03:00', '2025-03-20 10:03:00'),
(5, 1, 'Metformin 500mg', 'Oral diabetes medicine that helps control blood sugar levels', 'tablets', 'tablet', 8.75, 200, 50, 'DiaCare Pharma', 'Store at controlled room temperature', 'BATCH005', '2026-08-05', 1, 'active', '2025-03-20 10:04:00', '2025-03-20 10:04:00'),
(6, 1, 'Loratadine 10mg', 'Antihistamine used to relieve allergy symptoms', 'tablets', 'tablet', 9.25, 150, 30, 'AllerCure Ltd.', 'Store at room temperature away from moisture and heat', 'BATCH006', '2026-07-25', 0, 'active', '2025-03-20 10:05:00', '2025-03-20 10:05:00'),
(7, 1, 'Omeprazole 20mg', 'Proton pump inhibitor that decreases stomach acid production', 'capsules', 'capsule', 15.00, 97, 25, 'GastroHealth Inc.', 'Store at room temperature away from light and moisture', 'BATCH007', '2026-03-12', 1, 'active', '2025-03-20 10:06:00', '2025-03-27 21:31:10'),
(8, 1, 'Childrens Cough Syrup', 'Relieves cough and cold symptoms in children', 'syrups', 'bottle', 35.00, 45, 15, 'KidsCare Pharmaceuticals', 'Store in a refrigerator after opening', 'BATCH008', '2025-11-30', 0, 'active', '2025-03-20 10:07:00', '2025-03-20 10:07:00'),
(9, 1, 'Vitamin D3 1000IU', 'Vitamin supplement for bone health', 'tablets', 'tablet', 12.00, 300, 60, 'VitaHealth Corp', 'Store in a cool, dry place', 'BATCH009', '2027-01-15', 0, 'active', '2025-03-20 10:08:00', '2025-03-20 10:08:00'),
(10, 1, 'Insulin Glargine', 'Long-acting insulin for diabetes management', 'injections', 'vial', 250.00, 14, 5, 'DiaBetter Labs', 'Store in refrigerator. Do not freeze', 'BATCH010', '2025-09-22', 1, 'active', '2025-03-20 10:09:00', '2025-03-27 21:31:10'),
(11, 1, 'Cetirizine 10mg', 'Antihistamine for allergy relief', 'tablets', 'tablet', 6.50, 50, 20, 'AllerStop Pharmaceuticals', 'Store at room temperature', 'BATCH011', '2026-02-28', 0, 'active', '2025-03-20 10:10:00', '2025-03-31 14:18:06'),
(12, 1, 'Aspirin 75mg', 'Low-dose aspirin for heart health', 'tablets', 'tablet', 4.25, 394, 100, 'CardioWell Inc.', 'Store in a dry place below 25°C', 'BATCH012', '2026-10-08', 0, 'active', '2025-03-20 10:11:00', '2025-03-27 21:31:10'),
(13, 1, 'Ciprofloxacin 500mg', 'Antibiotic for treating bacterial infections', 'tablets', 'tablet', 18.75, 80, 20, 'InfectionCure Pharma', 'Store at room temperature away from light', 'BATCH013', '2025-12-15', 1, 'active', '2025-03-20 10:12:00', '2025-03-20 10:12:00'),
(14, 1, 'Diclofenac Gel 1%', 'Topical pain reliever for muscle and joint pain', 'topical', 'tube', 45.00, 30, 10, 'PainAway Labs', 'Store at room temperature', 'BATCH014', '2026-05-10', 0, 'active', '2025-03-20 10:13:00', '2025-03-20 10:13:00'),
(15, 1, 'Levothyroxine 50mcg', 'Thyroid hormone replacement', 'tablets', 'tablet', 14.50, 10, 15, 'ThyroHealth Inc.', 'Store in a refrigerator', 'BATCH015', '2025-08-20', 1, 'active', '2025-03-20 10:14:00', '2025-03-31 14:27:28'),
(16, 1, 'Dolo', 'Dolo 500mg', 'tablets', 'tablet', 2.00, 100, 50, 'AllerStop Pharmaceuticals', 'Do not leave it open', 'BATCH011', '2026-03-31', 0, 'active', '2025-03-31 14:31:00', '2025-03-31 14:31:00');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_orders`
--

DROP TABLE IF EXISTS `medicine_orders`;
CREATE TABLE IF NOT EXISTS `medicine_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `pharmacy_id` int NOT NULL,
  `prescription_id` int DEFAULT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `delivery_address` text,
  `notes` text,
  `delivery_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `patient_id` (`patient_id`),
  KEY `prescription_id` (`prescription_id`),
  KEY `idx_pharmacy_order_status` (`pharmacy_id`,`order_status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medicine_orders`
--

INSERT INTO `medicine_orders` (`id`, `patient_id`, `pharmacy_id`, `prescription_id`, `order_number`, `total_amount`, `payment_status`, `order_status`, `delivery_address`, `notes`, `delivery_notes`, `created_at`, `updated_at`) VALUES
(3, 1, 1, 1, 'ORD-1742217087-1', 0.00, 'pending', 'processing', '456 Elm Street, Denver, CO, USA', '', NULL, '2025-03-17 13:11:27', '2025-03-27 21:36:09'),
(4, 1, 1, 2, 'ORD-1742288573-1', 200.00, 'paid', 'shipped', '123 Bleeker Street', '', NULL, '2025-03-18 09:02:53', '2025-03-27 21:31:10'),
(5, 1, 1, 2, 'ORD-1743111580-1', 104.25, 'pending', 'pending', NULL, '', NULL, '2025-03-27 21:39:40', '2025-03-27 21:39:40'),
(6, 1, 1, 2, 'ORD-1743111587-1', 100.00, 'paid', 'processing', NULL, '', NULL, '2025-03-27 21:39:47', '2025-03-27 21:39:59'),
(7, 1, 1, 2, 'ORD-1743115496-1', 0.00, 'pending', 'pending', 'Sao Paulo, Interlagos', '', NULL, '2025-03-27 22:44:56', '2025-03-27 22:44:56'),
(8, 1, 1, 2, 'ORD-1743115634-1', 0.00, 'pending', 'pending', NULL, '', NULL, '2025-03-27 22:47:14', '2025-03-27 22:47:14'),
(9, 1, 1, 1, 'ORD-1743115661-1', 0.00, 'pending', 'pending', NULL, '', NULL, '2025-03-27 22:47:41', '2025-03-27 22:47:41'),
(10, 1, 1, 1, 'ORD-1743115809-1', 14.25, 'paid', 'processing', 'test address', '', NULL, '2025-03-27 22:50:09', '2025-03-27 22:50:15'),
(11, 5, 1, 4, 'ORD-1743412734-5', 110.00, 'paid', 'processing', '33 Street, Interlagos', '', NULL, '2025-03-31 09:18:54', '2025-03-31 09:18:58');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_order_items`
--

DROP TABLE IF EXISTS `medicine_order_items`;
CREATE TABLE IF NOT EXISTS `medicine_order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `medicine_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `medicine_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items` (`order_id`,`medicine_id`),
  KEY `medicine_order_items_ibfk_2` (`medicine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medicine_order_items`
--

INSERT INTO `medicine_order_items` (`id`, `order_id`, `medicine_id`, `quantity`, `unit_price`, `total_price`, `created_at`, `medicine_name`) VALUES
(1, 1, 1, 5, 5.00, 25.00, '2025-03-20 08:15:27', 'Paracetamol 500mg'),
(2, 1, 2, 8, 12.50, 100.00, '2025-03-20 08:15:27', 'Amoxicillin 250mg'),
(3, 2, 3, 6, 7.50, 45.00, '2025-03-20 10:45:33', 'Ibuprofen 400mg'),
(4, 2, 6, 2, 9.25, 18.50, '2025-03-20 10:45:33', 'Loratadine 10mg'),
(5, 2, 9, 3, 12.00, 36.00, '2025-03-20 10:45:33', 'Vitamin D3 1000IU'),
(6, 3, 4, 1, 120.00, 120.00, '2025-03-21 13:11:27', 'Salbutamol Inhaler'),
(7, 3, 8, 2, 35.00, 70.00, '2025-03-21 13:11:27', 'Childrens Cough Syrup'),
(8, 3, 1, 8, 5.00, 40.00, '2025-03-21 13:11:27', 'Paracetamol 500mg'),
(9, 4, 10, 1, 250.00, 250.00, '2025-03-22 09:02:53', 'Insulin Glargine'),
(10, 4, 7, 3, 15.00, 45.00, '2025-03-22 09:02:53', 'Omeprazole 20mg'),
(11, 4, 12, 6, 4.25, 25.50, '2025-03-22 09:02:53', 'Aspirin 75mg'),
(12, 5, 1, 5, 5.00, 25.00, '2025-03-23 15:28:14', 'Paracetamol 500mg'),
(13, 5, 6, 2, 9.25, 18.50, '2025-03-23 15:28:14', 'Loratadine 10mg'),
(14, 5, 12, 1, 4.00, 4.00, '2025-03-23 15:28:14', 'Aspirin 75mg'),
(15, 6, 2, 4, 12.50, 50.00, '2025-03-24 11:34:22', 'Amoxicillin 250mg'),
(16, 6, 13, 5, 18.75, 93.75, '2025-03-24 11:34:22', 'Ciprofloxacin 500mg'),
(17, 6, 14, 1, 45.00, 45.00, '2025-03-24 11:34:22', 'Diclofenac Gel 1%'),
(18, 7, 10, 2, 250.00, 500.00, '2025-03-24 14:25:41', 'Insulin Glargine'),
(19, 7, 5, 3, 8.75, 26.25, '2025-03-24 14:25:41', 'Metformin 500mg'),
(20, 8, 5, 5, 8.75, 43.75, '2025-03-25 08:55:17', 'Metformin 500mg'),
(21, 8, 12, 5, 4.25, 21.25, '2025-03-25 08:55:17', 'Aspirin 75mg'),
(22, 5, NULL, 10, 10.00, 100.00, '2025-03-27 21:39:40', 'Cetirizine 10mg'),
(23, 5, NULL, 1, 4.25, 4.25, '2025-03-27 21:39:40', 'Aspirin 75mg'),
(24, 6, NULL, 10, 10.00, 100.00, '2025-03-27 21:39:47', 'Cetirizine 10mg'),
(25, 10, NULL, 1, 10.00, 10.00, '2025-03-27 22:50:09', 'Cetirizine 10mg'),
(26, 10, NULL, 1, 4.25, 4.25, '2025-03-27 22:50:09', 'Aspirin 75mg'),
(27, 11, NULL, 10, 10.00, 100.00, '2025-03-31 09:18:54', 'cetrizine'),
(28, 11, NULL, 1, 10.00, 10.00, '2025-03-31 09:18:54', 'Paracetamol');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `address` text,
  `medical_history` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `profile_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `name`, `email`, `phone`, `dob`, `gender`, `blood_group`, `emergency_contact`, `address`, `medical_history`, `created_at`, `updated_at`, `profile_image`) VALUES
(1, 2, 'Test Patient', 'patient@test.com', '555-123-4567', '1980-01-01', 'male', 'O+', '+1 555-876-5432 (Mike Johnson)', ' 456 Elm Street, Denver, CO, USA', 'Asthma, allergic to penicillin, previous fracture (left ankle - 2012).', '2025-03-11 16:55:29', '2025-03-12 10:34:39', NULL),
(3, 5, 'Max Verstappen', 'maxverstappen@rbr.com', '3301330133', '1998-12-12', NULL, NULL, '+1 555-987-6543 (Jane Doe)', '123 Main Street, Springfield, IL, USA', 'No known allergies, previous surgery (appendectomy - 2015), mild hypertension.', '2025-03-12 10:33:40', '2025-03-12 12:50:19', NULL),
(4, 25, 'Paul Atreides', 'paulatreides@dune.com', '9874563210', '1999-05-21', 'male', 'O-', '+1 542-987-6543 (Frank Herbert)', 'House Atreides Castle Caladan\r\nPlanet Caladan', 'Survived a near-death experience due to severe dehydration and exposure in the Arrakis desert, and a significant spice overdose granted him prescient abilities. He has mild water sensitivity and allergies to certain desert pollens and dust.', '2025-03-16 13:34:36', '2025-03-16 15:27:12', NULL),
(5, 44, 'Devadathan A R', 'doffy074@gmail.com', '+91 89214 80458', '2004-05-30', 'male', 'O-', '+1 3333-987-6543 (Mark Joe)', '1234 Example Street\r\nSuite 567\r\nFaketown, FT 98765\r\nUSA', 'Chronic Conditions: Hypertension, Type 2 Diabetes\r\n\r\nSurgeries: Appendectomy (2015), Knee Surgery (2020)\r\n\r\nAllergies: Penicillin, Peanuts\r\n\r\nCurrent Medications: Metformin (500mg), Lisinopril (10mg)\r\n\r\nPast Hospitalizations: Admitted for pneumonia in 2018', '2025-03-31 06:06:10', '2025-03-31 09:35:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patient_alerts`
--

DROP TABLE IF EXISTS `patient_alerts`;
CREATE TABLE IF NOT EXISTS `patient_alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `alert_type` enum('appointment','medicine_order','cab_booking','prescription','payment','system','patient_issue') NOT NULL DEFAULT 'system',
  `reference_id` int DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `idx_alert_type_reference` (`alert_type`,`reference_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `patient_alerts`
--

INSERT INTO `patient_alerts` (`id`, `patient_id`, `title`, `message`, `alert_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(1, 5, 'New Prescription Added', 'Dr. Dr. John Smith (Internal Medicine) has added a new prescription for you: Cetirizine. Please check your prescriptions for details.', 'prescription', 4, 0, NULL, '2025-03-31 09:13:46'),
(2, 5, 'Appointment Completed', 'Your appointment with Dr. Dr. John Smith (Internal Medicine) on April 1, 2025 at 11:50 am has been marked as completed.', 'appointment', 22, 1, '2025-03-31 09:17:03', '2025-03-31 09:15:31'),
(3, 1, 'Appointment Cancelled', 'Your appointment with Dr. Dr. John Smith (Internal Medicine) on March 18, 2025 at 5:55 pm has been cancelled.', 'appointment', 15, 0, NULL, '2025-03-31 09:15:43'),
(4, 1, 'Appointment Cancelled', 'Your appointment with Dr. Dr. John Smith (Internal Medicine) on March 19, 2025 at 3:30 pm has been cancelled.', 'appointment', 12, 0, NULL, '2025-03-31 09:15:46'),
(5, 1, 'Appointment Cancelled', 'Your appointment with Dr. Dr. John Smith (Internal Medicine) on March 18, 2025 at 3:00 pm has been cancelled.', 'appointment', 17, 0, NULL, '2025-03-31 09:15:49'),
(6, 5, 'Medicine Order Placed', 'Your medicine order #ORD-1743412734-5 has been placed with Test Pharmacy with delivery to your address. Total amount: $110.00. Please proceed with payment.', 'medicine_order', 11, 0, NULL, '2025-03-31 09:18:54'),
(7, 5, 'New Appointment Booked', 'You have booked an appointment with Dr. Dr. Robert Wilson (Pulmonology) for March 31, 2025, 8:51 pm. Please proceed with payment.', 'appointment', 25, 0, NULL, '2025-03-31 09:21:42'),
(8, 5, 'New Appointment Booked', 'You have booked an appointment with Dr. Dr. John Smith (General Practice) for March 31, 2025, 6:55 pm. Please proceed with payment.', 'appointment', 26, 0, NULL, '2025-03-31 09:26:07'),
(9, 5, 'Response to your issue #1', 'Your reported issue \'cab didn&#39;t arrive\' has been updated. Status: In_progress', 'system', 1, 0, NULL, '2025-03-31 10:04:05'),
(10, 5, 'Response to your issue #2', 'Your reported issue \'wrong medicine received\' has been updated. Status: Pending', 'system', 2, 0, NULL, '2025-03-31 10:10:15'),
(11, 5, 'Cab Booking Created', 'You have booked a stretcher cab with Transport for April 1, 2025, 2:15 pm. Estimated fare: $350.00. Please proceed with payment.', 'cab_booking', 9, 0, NULL, '2025-03-31 13:37:40'),
(12, 5, 'Response to your issue #1', 'Your reported issue \'cab didn&#39;t arrive\' has been updated. Status: Resolved', 'system', 1, 0, NULL, '2025-03-31 13:43:56'),
(13, 1, 'Transport Booking Completed', 'Your transport booking with Transport for March 29, 2025 at 07:57 AM has been marked as completed.', 'cab_booking', 8, 0, NULL, '2025-03-31 14:04:51'),
(14, 5, 'Transport Booking Completed', 'Your transport booking with Transport for April 1, 2025 at 02:15 PM has been marked as completed.', 'cab_booking', 9, 1, '2025-03-31 15:05:38', '2025-03-31 14:05:09');

-- --------------------------------------------------------

--
-- Table structure for table `patient_issues`
--

DROP TABLE IF EXISTS `patient_issues`;
CREATE TABLE IF NOT EXISTS `patient_issues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `issue_type` enum('medicine_delivery','cab_service','appointment','pharmacy','doctor','other') NOT NULL,
  `reference_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
  `admin_response` text,
  `patient_response` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `admin_response_at` datetime DEFAULT NULL,
  `patient_response_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `issue_type_reference_id` (`issue_type`,`reference_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `patient_issues`
--

INSERT INTO `patient_issues` (`id`, `patient_id`, `issue_type`, `reference_id`, `title`, `description`, `status`, `admin_response`, `patient_response`, `created_at`, `updated_at`, `resolved_at`, `admin_response_at`, `patient_response_at`) VALUES
(1, 5, 'cab_service', 0, 'cab didn&#39;t arrive', 'cab didn&#39;t arrive even after a 30 minute wait', 'resolved', 'We are checking', NULL, '2025-03-31 09:59:27', '2025-03-31 13:43:56', '2025-03-31 13:43:56', NULL, NULL),
(2, 5, 'medicine_delivery', 0, 'wrong medicine received', 'wrong medicine received', 'pending', 'Will respond', 'ok', '2025-03-31 10:09:28', '2025-03-31 10:24:18', NULL, NULL, '2025-03-31 15:54:18');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reference_id` int NOT NULL,
  `payment_type` enum('medicine_order','cab_booking','appointment') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payment_reference` (`payment_type`,`reference_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `reference_id`, `payment_type`, `amount`, `payment_method`, `transaction_id`, `status`, `payment_date`, `created_at`, `updated_at`) VALUES
(15, 6, 'medicine_order', 100.00, 'upi', 'TXN67e5c5afae2971743111599', 'completed', '2025-03-27 21:39:59', '2025-03-27 21:39:59', '2025-03-27 21:39:59'),
(16, 10, 'medicine_order', 14.25, 'upi', 'TXN67e5d62710eb41743115815', 'completed', '2025-03-27 22:50:15', '2025-03-27 22:50:15', '2025-03-27 22:50:15'),
(17, 8, 'cab_booking', 250.00, 'upi', 'TXN67e5df03a2ed91743118083', 'completed', '2025-03-27 23:28:03', '2025-03-27 23:28:03', '2025-03-27 23:28:03'),
(18, 21, 'appointment', 250.00, 'upi', 'TXN67ea312026c191743401248', 'completed', '2025-03-31 06:07:28', '2025-03-31 06:07:28', '2025-03-31 06:07:28'),
(19, 22, 'appointment', 250.00, 'upi', 'TXN67ea33be9310d1743401918', 'completed', '2025-03-31 06:18:38', '2025-03-31 06:18:38', '2025-03-31 06:18:38'),
(20, 23, 'appointment', 250.00, 'upi', 'TXN67ea3516a882c1743402262', 'completed', '2025-03-31 06:24:22', '2025-03-31 06:24:22', '2025-03-31 06:24:22'),
(21, 23, 'appointment', 250.00, 'upi', 'TXN67ea35640be321743402340', 'completed', '2025-03-31 06:25:40', '2025-03-31 06:25:40', '2025-03-31 06:25:40'),
(22, 24, 'appointment', 250.00, 'upi', 'TXN67ea373b594541743402811', 'completed', '2025-03-31 06:33:31', '2025-03-31 06:33:31', '2025-03-31 06:33:31'),
(23, 11, 'medicine_order', 110.00, 'upi', 'TXN67ea5e0236c681743412738', 'completed', '2025-03-31 09:18:58', '2025-03-31 09:18:58', '2025-03-31 09:18:58'),
(24, 11, 'medicine_order', 110.00, 'upi', 'TXN67ea5e05b1aec1743412741', 'completed', '2025-03-31 09:19:01', '2025-03-31 09:19:01', '2025-03-31 09:19:01'),
(25, 11, 'medicine_order', 110.00, 'upi', 'TXN67ea5e093637d1743412745', 'completed', '2025-03-31 09:19:05', '2025-03-31 09:19:05', '2025-03-31 09:19:05'),
(26, 11, 'medicine_order', 110.00, 'upi', 'TXN67ea5e1b131611743412763', 'completed', '2025-03-31 09:19:23', '2025-03-31 09:19:23', '2025-03-31 09:19:23'),
(27, 25, 'appointment', 200.00, 'upi', 'TXN67ea5eaae7d711743412906', 'completed', '2025-03-31 09:21:46', '2025-03-31 09:21:46', '2025-03-31 09:21:46'),
(28, 26, 'appointment', 100.00, 'upi', 'TXN67ea5fb3d632c1743413171', 'completed', '2025-03-31 09:26:11', '2025-03-31 09:26:11', '2025-03-31 09:26:11'),
(29, 9, 'cab_booking', 350.00, 'upi', 'TXN67ea9aabf3ca61743428267', 'completed', '2025-03-31 13:37:47', '2025-03-31 13:37:47', '2025-03-31 13:37:47');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacies`
--

DROP TABLE IF EXISTS `pharmacies`;
CREATE TABLE IF NOT EXISTS `pharmacies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `service_provider_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `license_number` varchar(50) DEFAULT NULL,
  `operating_hours` varchar(255) DEFAULT NULL,
  `delivery_available` tinyint(1) DEFAULT '1',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `license_number` (`license_number`),
  KEY `service_provider_id` (`service_provider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pharmacies`
--

INSERT INTO `pharmacies` (`id`, `user_id`, `service_provider_id`, `name`, `email`, `phone`, `address`, `license_number`, `operating_hours`, `delivery_available`, `status`, `created_at`, `updated_at`) VALUES
(1, 20, 2, 'Test Pharmacy', 'pharmacy@test.com', '555-0123', '123 Test Street, Test City, 12345', 'PHR123456', 'Mon-Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM', 1, 'active', '2025-03-16 07:32:31', '2025-03-27 21:04:09'),
(4, 43, 3, 'Pharmacy', 'ftmadmaxlol@gmail.com', '1236547890', 'Pharm 1\r\ntest address', 'LIC123', '24/7 (Available round the clock)', 1, 'active', '2025-03-27 20:46:28', '2025-03-31 14:11:02');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `diagnosis` text,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `patient_id`, `doctor_id`, `diagnosis`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Medication: Mahacef  200\nDosage: 200 mg\nFrequency: Twice Daily\nDuration: 5 Days', 'Eat Up', '2025-03-12 05:22:16', '2025-03-12 05:22:16'),
(2, 1, 1, 'Medication: Mahacef  200\nDosage: 200 mg\nFrequency: Twice Daily\nDuration: 5 Days', 'Eat Up', '2025-03-12 05:22:28', '2025-03-12 05:22:28'),
(3, 4, 1, 'Medication: Melatonin\nDosage: 500 mg\nFrequency: Once Daily at night\nDuration: 5 Days', 'Eat after dinner', '2025-03-17 07:26:39', '2025-03-17 07:26:39'),
(4, 5, 1, 'Medication: Cetirizine\nDosage: 10mg\nFrequency: Daily before bed\nDuration: 7 Days', 'Eat', '2025-03-31 09:13:46', '2025-03-31 09:13:46');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

DROP TABLE IF EXISTS `prescription_items`;
CREATE TABLE IF NOT EXISTS `prescription_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `medicine` varchar(255) NOT NULL,
  `dosage` varchar(255) DEFAULT NULL,
  `frequency` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `instructions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `prescription_id` (`prescription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescription_medicines`
--

DROP TABLE IF EXISTS `prescription_medicines`;
CREATE TABLE IF NOT EXISTS `prescription_medicines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `medicine_id` int NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `instructions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `prescription_id` (`prescription_id`),
  KEY `medicine_id` (`medicine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_providers`
--

DROP TABLE IF EXISTS `service_providers`;
CREATE TABLE IF NOT EXISTS `service_providers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `service_type` enum('pharmacy','transportation','nursing','equipment','rehabilitation','counseling','both') NOT NULL,
  `address` text,
  `operating_hours` varchar(255) DEFAULT NULL,
  `service_area` text,
  `license_number` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_providers`
--

INSERT INTO `service_providers` (`id`, `user_id`, `company_name`, `email`, `phone`, `service_type`, `address`, `operating_hours`, `service_area`, `license_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 14, 'Transport', 'transport@palliative.com', '7418529630', 'transportation', '', '24/7 (Available round the clock)', '', '', 'active', '2025-03-12 18:51:54', '2025-03-31 13:51:54'),
(2, 20, 'Pharmacy', 'pharmacy@test.com', '7896541230', 'pharmacy', '123 Springfeild Road, Springfield, IL, USA', '24/7 (Available round the clock)', 'Greater Springfield Area', 'PHAR-9876543', 'active', '2025-03-15 18:43:54', '2025-03-16 09:48:42'),
(3, 43, 'Pharmacy', 'ftmadmaxlol@gmail.com', '1236547890', 'pharmacy', '', '24x7', '', '', 'active', '2025-03-27 19:01:02', '2025-03-31 14:11:56');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `service_provider_id` int NOT NULL,
  `request_type` varchar(50) NOT NULL DEFAULT 'medicine_delivery',
  `service_type` enum('cab','medicine','equipment') NOT NULL,
  `requested_date` datetime NOT NULL,
  `status` enum('pending','approved','in_progress','completed','cancelled') DEFAULT 'pending',
  `notes` text,
  `provider_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `medicine_order_id` int DEFAULT NULL,
  `delivery_address` text,
  `request_details` text,
  `scheduled_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `provider_id` (`service_provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medicine_id` int NOT NULL,
  `quantity` int NOT NULL,
  `movement_type` enum('in','out') NOT NULL,
  `reference_type` enum('purchase','sale','adjustment','return') NOT NULL,
  `reference_id` int DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_medicine` (`medicine_id`),
  KEY `idx_reference` (`reference_type`,`reference_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `medicine_id`, `quantity`, `movement_type`, `reference_type`, `reference_id`, `notes`, `created_at`, `created_by`) VALUES
(1, 7, 3, 'out', '', 4, 'Order #4 - shipped', '2025-03-27 21:31:10', 20),
(2, 10, 1, 'out', '', 4, 'Order #4 - shipped', '2025-03-27 21:31:10', 20),
(3, 12, 6, 'out', '', 4, 'Order #4 - shipped', '2025-03-27 21:31:10', 20),
(4, 2, 10, 'in', 'purchase', NULL, 'Added 10 units to Amoxicillin 250mg via purchase', '2025-03-31 14:27:16', 20),
(5, 2, 10, 'in', 'purchase', NULL, 'Added 10 units to Amoxicillin 250mg via purchase', '2025-03-31 14:27:21', 20),
(6, 15, 10, 'in', 'purchase', NULL, 'Added 10 units to Levothyroxine 50mcg via purchase', '2025-03-31 14:27:28', 20),
(7, 16, 100, 'in', '', NULL, 'Initial stock for new medicine', '2025-03-31 14:31:00', 20);

-- --------------------------------------------------------

--
-- Table structure for table `symptoms`
--

DROP TABLE IF EXISTS `symptoms`;
CREATE TABLE IF NOT EXISTS `symptoms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `symptoms`
--

INSERT INTO `symptoms` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Fever', 'Elevated body temperature above the normal range', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(2, 'Headache', 'Pain in any region of the head', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(3, 'Cough', 'Sudden expulsion of air from the lungs to clear the air passages', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(4, 'Fatigue', 'Extreme tiredness resulting from mental or physical exertion', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(5, 'Sore throat', 'Pain or irritation in the throat that often worsens when swallowing', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(6, 'Runny nose', 'Excess drainage of mucus from the nose', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(7, 'Muscle ache', 'Pain in muscles throughout the body', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(8, 'Shortness of breath', 'Difficulty breathing or feeling like you cant get enough air', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(9, 'Chest pain', 'Discomfort or pain in the chest area', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(10, 'Nausea', 'Feeling of sickness with an inclination to vomit', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(11, 'Vomiting', 'Forceful expulsion of stomach contents through the mouth', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(12, 'Diarrhea', 'Loose, watery stools occurring more frequently than usual', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(13, 'Rash', 'Area of irritated or swollen skin', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(14, 'Joint pain', 'Discomfort that arises from any joint', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(15, 'Dizziness', 'Feeling faint, lightheaded, or unsteady', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(16, 'Chills', 'Feeling of coldness accompanied by shivering', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(17, 'Loss of appetite', 'Reduced desire to eat', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(18, 'Abdominal pain', 'Pain that occurs between the chest and pelvic regions', '2025-03-17 15:16:49', '2025-03-17 15:16:49'),
(19, 'Swollen glands', 'Enlarged lymph nodes, usually in the neck, armpits, or groin', '2025-03-17 15:16:49', '2025-03-17 15:16:49');

-- --------------------------------------------------------

--
-- Table structure for table `symptom_search_history`
--

DROP TABLE IF EXISTS `symptom_search_history`;
CREATE TABLE IF NOT EXISTS `symptom_search_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `symptoms` text NOT NULL,
  `diseases_found` text,
  `recommended_doctors` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `symptom_search_history`
--

INSERT INTO `symptom_search_history` (`id`, `patient_id`, `symptoms`, `diseases_found`, `recommended_doctors`, `created_at`) VALUES
(1, 1, 'cough,sneezing', '[]', '[]', '2025-03-17 15:12:55'),
(2, 1, 'Headache,Fever', '[\"Influenza\",\"Strep Throat\",\"COVID-19\",\"Pneumonia\",\"Sinusitis\",\"Migraine\",\"Hypertension\"]', '[]', '2025-03-17 15:50:03'),
(3, 1, 'Fever,Fatigue,Headache', '[\"Influenza\",\"COVID-19\",\"Strep Throat\",\"Pneumonia\",\"Common Cold\",\"Bronchitis\",\"Sinusitis\",\"Migraine\",\"Hypertension\"]', '[]', '2025-03-17 15:57:05'),
(4, 1, 'Fever,Fatigue,Headache', '[\"Influenza\",\"COVID-19\",\"Strep Throat\",\"Pneumonia\",\"Common Cold\",\"Bronchitis\",\"Sinusitis\",\"Migraine\",\"Hypertension\"]', '[]', '2025-03-17 16:04:20'),
(5, 1, 'Fever,Cough', '[\"Influenza\",\"COVID-19\",\"Pneumonia\",\"Common Cold\",\"Strep Throat\",\"Bronchitis\"]', '[]', '2025-03-17 16:09:13'),
(6, 1, 'Headache,Sore throat,Chest pain', '[\"Strep Throat\",\"Sinusitis\",\"Hypertension\",\"Common Cold\",\"Influenza\",\"Bronchitis\",\"Pneumonia\",\"Migraine\"]', '[]', '2025-03-17 16:45:44'),
(7, 1, 'Fever', '[\"Influenza\",\"COVID-19\",\"Strep Throat\",\"Pneumonia\"]', '[]', '2025-03-17 16:45:59'),
(8, 1, 'Fever,Cough', '[\"Influenza\",\"COVID-19\",\"Pneumonia\",\"Common Cold\",\"Strep Throat\",\"Bronchitis\"]', '[]', '2025-03-17 16:54:30'),
(9, 1, 'Fever,Cough,Fatigue', '[\"Influenza\",\"COVID-19\",\"Pneumonia\",\"Common Cold\",\"Bronchitis\",\"Strep Throat\"]', '[]', '2025-03-17 16:54:33'),
(10, 1, 'Fever', '[\"Influenza\",\"COVID-19\",\"Strep Throat\",\"Pneumonia\"]', '[]', '2025-03-17 17:05:01'),
(11, 1, 'Fever,Cough', '[\"Influenza\",\"COVID-19\",\"Pneumonia\",\"Common Cold\",\"Strep Throat\",\"Bronchitis\"]', '[]', '2025-03-17 17:12:14'),
(12, 1, 'Fever,Cough', '[\"Influenza\",\"COVID-19\",\"Pneumonia\",\"Common Cold\",\"Strep Throat\",\"Bronchitis\"]', '[]', '2025-03-17 17:13:43'),
(13, 1, 'Fever,Cough', '[\"Influenza\",\"COVID-19\",\"Pneumonia\",\"Common Cold\",\"Strep Throat\",\"Bronchitis\"]', '[]', '2025-03-17 17:25:45'),
(14, 1, 'Fever,Cough', '[\"COVID-19\",\"Pneumonia\",\"Influenza\",\"Strep Throat\",\"Bronchitis\",\"Common Cold\"]', '[\"Dr. Robert Taylor\",\"Dr. David Wilson\",\"Dr. Robert Wilson\",\"Dr. John Smith\",\"Dr. Lisa Anderson\"]', '2025-03-17 17:30:59'),
(15, 1, 'Fever', '[\"COVID-19\",\"Pneumonia\",\"Influenza\",\"Strep Throat\"]', '[\"Dr. Robert Taylor\",\"Dr. David Wilson\",\"Dr. Robert Wilson\",\"Dr. John Smith\",\"Dr. Lisa Anderson\"]', '2025-03-17 17:31:18'),
(16, 1, 'Cough,Fatigue,Fever', '[\"COVID-19\",\"Pneumonia\",\"Influenza\",\"Bronchitis\",\"Common Cold\",\"Strep Throat\"]', '[\"Dr. Robert Taylor\",\"Dr. David Wilson\",\"Dr. Robert Wilson\",\"Dr. John Smith\",\"Dr. Lisa Anderson\"]', '2025-03-17 17:31:45'),
(17, 1, 'Headache,Fever', '[\"Influenza\",\"Strep Throat\",\"COVID-19\",\"Pneumonia\",\"Migraine\",\"Hypertension\",\"Sinusitis\"]', '[\"Dr. James Anderson\",\"Dr. Michael Chen\",\"Dr. Emily Brown\",\"Dr. Robert Taylor\",\"Dr. David Wilson\"]', '2025-03-18 04:21:41'),
(18, 1, 'Fever,Headache', '[\"Influenza\",\"Strep Throat\",\"COVID-19\",\"Pneumonia\",\"Migraine\",\"Hypertension\",\"Sinusitis\"]', '[\"Dr. James Anderson\",\"Dr. Michael Chen\",\"Dr. Emily Brown\",\"Dr. Robert Taylor\",\"Dr. David Wilson\"]', '2025-03-18 04:28:55'),
(19, 1, 'Fever', '[\"COVID-19\",\"Pneumonia\",\"Influenza\",\"Strep Throat\"]', '[\"Dr. Robert Taylor\",\"Dr. David Wilson\",\"Dr. Robert Wilson\",\"Dr. John Smith\",\"Dr. Lisa Anderson\"]', '2025-03-18 08:57:28'),
(20, 1, 'Headache,Fever', '[\"Influenza\",\"Strep Throat\",\"COVID-19\",\"Pneumonia\",\"Migraine\",\"Hypertension\",\"Sinusitis\"]', '[\"Dr. James Anderson\",\"Dr. Michael Chen\",\"Dr. Emily Brown\",\"Dr. Robert Taylor\",\"Dr. David Wilson\"]', '2025-03-31 09:20:26'),
(21, 1, 'Fatigue', '[\"COVID-19\",\"Pneumonia\",\"Influenza\",\"Bronchitis\",\"Common Cold\"]', '[\"Dr. Robert Taylor\",\"Dr. David Wilson\",\"Dr. Robert Wilson\",\"Dr. John Smith\",\"Dr. Sarah Johnson\"]', '2025-03-31 09:20:46'),
(22, 1, 'Fatigue', '[\"COVID-19\",\"Pneumonia\",\"Influenza\",\"Bronchitis\",\"Common Cold\"]', '[\"Dr. Robert Taylor\",\"Dr. David Wilson\",\"Dr. Robert Wilson\",\"Dr. John Smith\",\"Dr. Sarah Johnson\"]', '2025-03-31 09:24:41'),
(23, 1, 'Cough', '[\"COVID-19\",\"Pneumonia\",\"Influenza\",\"Bronchitis\",\"Common Cold\"]', '[\"Dr. Robert Taylor\",\"Dr. David Wilson\",\"Dr. Robert Wilson\",\"Dr. John Smith\",\"Dr. Sarah Johnson\"]', '2025-03-31 09:24:49'),
(24, 1, 'Fatigue,Cough', '[\"COVID-19\",\"Pneumonia\",\"Influenza\",\"Bronchitis\",\"Common Cold\"]', '[\"Dr. Robert Taylor\",\"Dr. David Wilson\",\"Dr. Robert Wilson\",\"Dr. John Smith\",\"Dr. Sarah Johnson\"]', '2025-03-31 09:25:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `user_type` enum('patient','doctor','service','admin') NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `name`, `user_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin@palliative.care', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'admin', 'active', '2025-03-11 16:55:15', '2025-03-12 13:02:13'),
(2, 'patient@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Patient', 'patient', 'active', '2025-03-11 16:55:29', '2025-03-27 21:37:52'),
(3, 'doctor@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. John Smith', 'doctor', 'active', '2025-03-11 16:55:29', '2025-03-31 15:04:38'),
(4, 'test@example.com', '$2y$10$JHpdOGFse0owqsjjkFB01eRaJPPHT7I0AgB7FeMvMI23sxOjmAHEi', '', 'patient', 'active', '2025-03-11 16:56:44', '2025-03-11 16:56:44'),
(5, 'maxvers@rbr.com', '', 'Max Verstappen', 'patient', 'active', '2025-03-12 10:33:40', '2025-03-12 10:33:40'),
(6, 'james.anderson@example.com', '', 'Dr. James Anderson', 'doctor', 'active', '2025-03-12 10:47:04', '2025-03-12 10:47:04'),
(7, 'kimibowah@admin.com', '$2y$10$xM5aYUv6lnh5qW16BIjFFOQrZs/T.Spfp7/RlgizaWne8z355xN1C', 'Kimi Raikonnen', 'admin', 'active', '2025-03-12 13:35:53', '2025-03-12 13:35:53'),
(8, 'doctor1@palliative.com', '$2y$10$Ew3Erhrg5OJqpCpNZuJKs.cbHKZXQHmX1QKb6J1d.IpEkR9DC6uv2', 'Dr. John Smith', 'doctor', 'active', '2025-03-12 18:07:00', '2025-03-12 18:07:00'),
(9, 'doctor2@palliative.com', '$2y$10$Ew3Erhrg5OJqpCpNZuJKs.cbHKZXQHmX1QKb6J1d.IpEkR9DC6uv2', 'Dr. Sarah Wilson', 'doctor', 'active', '2025-03-12 18:07:00', '2025-03-12 18:07:00'),
(12, 'patient1@mail.com', '$2y$10$Ew3Erhrg5OJqpCpNZuJKs.cbHKZXQHmX1QKb6J1d.IpEkR9DC6uv2', 'Robert Johnson', 'patient', 'active', '2025-03-12 18:07:00', '2025-03-12 18:07:00'),
(13, 'patient2@mail.com', '$2y$10$Ew3Erhrg5OJqpCpNZuJKs.cbHKZXQHmX1QKb6J1d.IpEkR9DC6uv2', 'Mary Williams', 'patient', 'active', '2025-03-12 18:07:00', '2025-03-12 18:07:00'),
(14, 'transport@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Transport', 'service', 'active', '2025-03-12 18:51:54', '2025-03-31 14:04:23'),
(20, 'pharmacy@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Pharmacy', 'service', 'active', '2025-03-16 07:32:31', '2025-03-31 14:29:15'),
(25, 'paulatreides@dune.com', '$2y$10$6LCXo6Mreoh2SBVQ1ZE2Xexonh9PY54dd5WA3AJcpF2rFFLFD2Mve', '', 'patient', 'active', '2025-03-16 13:34:36', '2025-03-16 13:34:42'),
(26, 'dr.smith@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sarah Smith', 'doctor', 'active', '2025-03-17 15:55:37', '2025-03-17 15:55:37'),
(27, 'dr.johnson@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Michael Johnson', 'doctor', 'active', '2025-03-17 15:55:37', '2025-03-17 15:55:37'),
(28, 'dr.patel@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Anita Patel', 'doctor', 'active', '2025-03-17 15:55:37', '2025-03-17 15:55:37'),
(29, 'dr.wilson@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Robert Wilson', 'doctor', 'active', '2025-03-17 15:55:37', '2025-03-17 15:55:37'),
(30, 'dr.chen@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Li Chen', 'doctor', 'active', '2025-03-17 15:55:37', '2025-03-17 15:55:37'),
(31, 'dr.garcia@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Maria Garcia', 'doctor', 'active', '2025-03-17 15:55:37', '2025-03-17 15:55:37'),
(32, 'dr.brown@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. James Brown', 'doctor', 'active', '2025-03-17 15:55:37', '2025-03-17 15:55:37'),
(33, 'dr.taylor@palliative.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Emily Taylor', 'doctor', 'active', '2025-03-17 15:55:37', '2025-03-17 15:55:37'),
(34, 'john.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. John Smith', 'doctor', 'active', '2025-03-17 16:08:58', '2025-03-17 16:08:58'),
(35, 'sarah.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sarah Johnson', 'doctor', 'active', '2025-03-17 16:08:58', '2025-03-17 16:08:58'),
(36, 'michael.chen@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Michael Chen', 'doctor', 'active', '2025-03-17 16:08:58', '2025-03-17 16:08:58'),
(37, 'emily.brown@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Emily Brown', 'doctor', 'active', '2025-03-17 16:08:58', '2025-03-17 16:08:58'),
(38, 'david.wilson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. David Wilson', 'doctor', 'active', '2025-03-17 16:08:58', '2025-03-17 16:08:58'),
(39, 'lisa.anderson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Lisa Anderson', 'doctor', 'active', '2025-03-17 16:08:58', '2025-03-17 16:08:58'),
(40, 'robert.taylor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Robert Taylor', 'doctor', 'active', '2025-03-17 16:08:58', '2025-03-17 16:08:58'),
(41, 'jennifer.lee@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Jennifer Lee', 'doctor', 'active', '2025-03-17 16:08:58', '2025-03-17 16:08:58'),
(43, 'ftmadmaxlol@gmail.com', '$2y$10$Fkq.MqbgzZ9j8UpJFUzxSeGAdFmVCre0i8y.PQ/Du.lU0KnEajjLy', 'Pharmacy', 'service', 'active', '2025-03-27 19:01:02', '2025-03-31 14:41:16'),
(44, 'doffy074@gmail.com', '$2y$10$0I/YyRbaiX4r6yMua1pZzOo7uD9VqbYQN32kyCSnS88MyYl9LTyIG', '', 'patient', 'active', '2025-03-31 06:06:10', '2025-03-31 13:33:00'),
(45, 'smtppalliativecare@gmail.com', '$2y$10$CxyCvx3/yYO/c1dvyd1oD.IIusKqxsjsDF04TXOuquStlkfpo7iP2', 'Ozymandias', 'admin', 'active', '2025-03-31 13:46:26', '2025-03-31 13:46:26');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medicines`
--
ALTER TABLE `medicines`
  ADD CONSTRAINT `medicines_ibfk_1` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medicine_orders`
--
ALTER TABLE `medicine_orders`
  ADD CONSTRAINT `medicine_orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medicine_orders_ibfk_2` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medicine_orders_ibfk_3` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_issues`
--
ALTER TABLE `patient_issues`
  ADD CONSTRAINT `patient_issues_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pharmacies`
--
ALTER TABLE `pharmacies`
  ADD CONSTRAINT `pharmacies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `symptom_search_history`
--
ALTER TABLE `symptom_search_history`
  ADD CONSTRAINT `symptom_search_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
