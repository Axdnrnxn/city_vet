-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 05:41 AM
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
-- Database: `city_vet_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `Appointment_ID` int(11) NOT NULL,
  `Owner_ID` int(11) NOT NULL,
  `Pet_ID` int(11) NOT NULL,
  `Service_ID` int(11) DEFAULT NULL,
  `Vet_ID` int(11) DEFAULT NULL,
  `Appointment_Date` datetime NOT NULL,
  `Status` enum('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`Appointment_ID`, `Owner_ID`, `Pet_ID`, `Service_ID`, `Vet_ID`, `Appointment_Date`, `Status`, `Notes`) VALUES
(2, 7, 2, 2, NULL, '2026-03-30 00:00:00', 'Confirmed', NULL),
(3, 7, 2, 7, NULL, '2026-03-25 16:55:00', 'Confirmed', 'Walk-in Booking'),
(4, 7, 2, 2, NULL, '2026-04-28 00:00:00', 'Pending', NULL),
(5, 7, 6, 2, NULL, '2026-04-29 00:00:00', 'Pending', NULL),
(6, 7, 7, 2, NULL, '2026-04-28 00:00:00', 'Confirmed', NULL),
(7, 7, 2, 2, NULL, '2026-04-28 00:00:00', 'Pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `Log_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Action` varchar(255) DEFAULT NULL,
  `Table_Affected` varchar(50) DEFAULT NULL,
  `Record_ID` int(11) DEFAULT NULL,
  `Timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`Log_ID`, `User_ID`, `Action`, `Table_Affected`, `Record_ID`, `Timestamp`) VALUES
(1, 4, 'Login', 'sessions', 0, '2026-04-28 13:02:25'),
(2, 4, 'Update Profile', 'users', 4, '2026-04-28 13:02:25'),
(3, 4, 'View Reports', 'analytics', 0, '2026-04-28 13:02:25'),
(4, 4, 'System Audit', 'audit_logs', 0, '2026-04-28 13:02:25');

-- --------------------------------------------------------

--
-- Table structure for table `breeds`
--

CREATE TABLE `breeds` (
  `Breed_ID` int(11) NOT NULL,
  `Species_ID` int(11) NOT NULL,
  `Breed_Name` varchar(100) NOT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `breeds`
--

INSERT INTO `breeds` (`Breed_ID`, `Species_ID`, `Breed_Name`, `Status`) VALUES
(1, 1, 'Aspin (Asong Pinoy)', 'active'),
(2, 1, 'Shih Tzu', 'active'),
(3, 1, 'German Shepherd', 'active'),
(4, 1, 'Golden Retriever', 'active'),
(5, 1, 'Labrador Retriever', 'active'),
(6, 1, 'Bulldog', 'active'),
(7, 1, 'Husky', 'active'),
(8, 1, 'Beagle', 'active'),
(9, 1, 'Chihuahua', 'active'),
(10, 1, 'Pug', 'active'),
(11, 1, 'Poodle', 'active'),
(12, 1, 'Chow Chow', 'active'),
(13, 1, 'Rottweiler', 'active'),
(14, 1, 'Pomeranian', 'active'),
(15, 1, 'American Bully', 'active'),
(16, 1, 'Belgian Malinois', 'active'),
(17, 1, 'Dachshund', 'active'),
(18, 1, 'Jack Russell Terrier', 'active'),
(19, 1, 'Dalmatian', 'active'),
(20, 1, 'Boxer', 'active'),
(21, 2, 'Puspin (Pusang Pinoy)', 'active'),
(22, 2, 'Siamese', 'active'),
(23, 2, 'Persian', 'active'),
(24, 2, 'Maine Coon', 'active'),
(25, 2, 'Bengal', 'active'),
(26, 2, 'British Shorthair', 'active'),
(27, 2, 'Ragdoll', 'active'),
(28, 2, 'Munchkin', 'active'),
(29, 2, 'Sphynx', 'active'),
(30, 3, 'Native Rabbit', 'active'),
(31, 3, 'Guinea Pig', 'active'),
(32, 3, 'Hamster', 'active'),
(33, 4, 'isda', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `Consultation_ID` int(11) NOT NULL,
  `Owner_ID` int(11) NOT NULL,
  `Pet_ID` int(11) NOT NULL,
  `Vet_ID` int(11) DEFAULT NULL,
  `Subject` varchar(255) DEFAULT NULL,
  `Concern_Description` text DEFAULT NULL,
  `Status` enum('Pending','Ongoing','Completed','Cancelled') DEFAULT 'Pending',
  `Meeting_Link` varchar(255) DEFAULT NULL,
  `Consultation_Date` datetime DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diagnosis_master`
--

CREATE TABLE `diagnosis_master` (
  `Diagnosis_ID` int(11) NOT NULL,
  `Diagnosis_Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diagnosis_master`
--

INSERT INTO `diagnosis_master` (`Diagnosis_ID`, `Diagnosis_Name`, `Description`, `Status`) VALUES
(1, 'Preventive Care', 'Used for Anti-Rabies, Deworming, and other routine shots.', 'active'),
(2, 'Surgical Procedure', 'Used for Spay, Neuter, or minor surgeries.', 'active'),
(3, 'Follow-up/Routine', 'Used for post-op checkups or progress monitoring.', 'active'),
(4, 'Clinical Observation', 'Used when a pet is brought in for general symptoms.', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `first_aid_contents`
--

CREATE TABLE `first_aid_contents` (
  `Content_ID` int(11) NOT NULL,
  `Title` varchar(150) NOT NULL,
  `Description` text NOT NULL,
  `Steps_Html` text NOT NULL,
  `Video_URL` varchar(255) DEFAULT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `Record_ID` int(11) NOT NULL,
  `Pet_ID` int(11) NOT NULL,
  `Vet_ID` int(11) DEFAULT NULL,
  `Diagnosis_ID` int(11) DEFAULT NULL,
  `Treatment` text DEFAULT NULL,
  `Visit_Date` datetime DEFAULT current_timestamp(),
  `Notes` text DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`Record_ID`, `Pet_ID`, `Vet_ID`, `Diagnosis_ID`, `Treatment`, `Visit_Date`, `Notes`, `is_deleted`) VALUES
(1, 2, NULL, NULL, 'None provided', '2026-03-29 16:48:00', '', 0),
(2, 2, NULL, NULL, '5-in-1 vaccine', '2026-03-29 16:49:13', 'vaccine', 0),
(3, 2, NULL, NULL, 'None provided', '2026-04-28 13:06:13', '', 0),
(4, 2, NULL, NULL, 'None provided', '2026-04-28 13:13:40', '', 0),
(5, 2, NULL, NULL, 'None provided', '2026-04-28 13:22:37', '', 0),
(6, 6, NULL, NULL, 'None provided', '2026-04-28 13:42:27', '', 0),
(7, 2, NULL, NULL, 'None provided', '2026-04-28 15:17:57', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `Notification_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Message` text NOT NULL,
  `Type` enum('Appointment','Vaccination','Consultation','System') NOT NULL,
  `Is_Read` tinyint(1) DEFAULT 0,
  `Sent_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `Owner_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `First_name` varchar(100) NOT NULL,
  `Last_name` varchar(100) NOT NULL,
  `Contact_number` varchar(15) NOT NULL,
  `Address` text DEFAULT NULL,
  `Status` enum('active','inactive') DEFAULT 'active',
  `Registration_Date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`Owner_ID`, `User_ID`, `First_name`, `Last_name`, `Contact_number`, `Address`, `Status`, `Registration_Date`) VALUES
(1, 4, 'Jessel Von', 'admin', '56952626566', 'lapasan, Carmen', 'active', '2026-02-14 09:42:01'),
(2, 6, 'manzano', 'Manzano', '09867541354', 'phinma coc, Carmen', 'active', '2026-02-14 09:46:56'),
(7, 11, 'Jez', 'jezreel', '09658254825', 'carmen, Carmen', 'active', '2026-03-29 16:41:39'),
(8, 12, 'sample', 'sample', '09265856565', 'sample, Carmen', 'active', '2026-03-29 16:46:39'),
(10, 14, 'jezreel', 'torion', '09586582512', 'kolambog, Carmen', 'active', '2026-04-28 14:53:12');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `Pet_ID` int(11) NOT NULL,
  `Owner_ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Species_ID` int(11) DEFAULT NULL,
  `Breed_ID` int(11) DEFAULT NULL,
  `Gender` enum('Male','Female','Unknown') DEFAULT 'Unknown',
  `Birthdate` date DEFAULT NULL,
  `Weight` decimal(5,2) DEFAULT NULL,
  `Color_Markings` varchar(100) DEFAULT NULL,
  `Status` enum('active','deceased','transferred','archived') DEFAULT 'active',
  `Profile_Pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`Pet_ID`, `Owner_ID`, `Name`, `Species_ID`, `Breed_ID`, `Gender`, `Birthdate`, `Weight`, `Color_Markings`, `Status`, `Profile_Pic`) VALUES
(2, 7, 'Happy', 1, 17, 'Female', '2026-03-03', 1.00, 'Brown', 'active', '69c8e60ed9169.jpg'),
(6, 7, 'Larry', 2, 25, 'Male', '2026-04-01', 4.00, 'Evil Larry', 'active', '69f03a85f35b8.jpg'),
(7, 7, 'iro', 3, 31, 'Female', '2026-04-01', 5.00, 'black', 'active', NULL),
(8, 1, 'Jes', 1, 8, 'Male', '2026-05-01', 2.00, 'brown with white heart shaped spot in the chest', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pet_notes`
--

CREATE TABLE `pet_notes` (
  `Note_ID` int(11) NOT NULL,
  `Pet_ID` int(11) NOT NULL,
  `Note_Text` text DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `Role_ID` int(11) NOT NULL,
  `Role_name` varchar(50) NOT NULL,
  `Description` text DEFAULT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`Role_ID`, `Role_name`, `Description`, `Status`) VALUES
(1, 'Administrator', 'Full system access', 'active'),
(2, 'Veterinarian', 'Medical record access', 'active'),
(3, 'Pet Owner', 'Client/Pet management', 'active'),
(4, 'Staff', 'Front desk operations', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `Service_ID` int(11) NOT NULL,
  `Category_ID` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`Service_ID`, `Category_ID`, `Service_Name`, `Price`, `Status`) VALUES
(1, 1, 'General Checkup', 0.00, 'active'),
(2, 1, 'Online Consultation', 0.00, 'active'),
(3, 2, 'Spay/Neuter (Cat)', 20.00, 'active'),
(4, 2, 'Spay/Neuter (Dog)', 20.00, 'active'),
(5, 3, 'Anti-Rabies Shot', 300.00, 'inactive'),
(6, 3, 'Deworming', 0.00, 'active'),
(7, 3, '5-in-1 Vaccine', 0.00, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(50) NOT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`Category_ID`, `Category_Name`, `Status`) VALUES
(1, 'Consultation', 'active'),
(2, 'Surgery', 'active'),
(3, 'Vaccination', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `species`
--

CREATE TABLE `species` (
  `Species_ID` int(11) NOT NULL,
  `Species_Name` varchar(50) NOT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `species`
--

INSERT INTO `species` (`Species_ID`, `Species_Name`, `Status`) VALUES
(1, 'Dog', 'active'),
(2, 'Cat', 'active'),
(3, 'Other/Exotic', 'active'),
(4, 'fish', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `Staff_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `First_name` varchar(100) DEFAULT NULL,
  `Last_name` varchar(100) DEFAULT NULL,
  `Position` varchar(50) DEFAULT NULL,
  `Status` enum('active','resigned','on_leave') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`Staff_ID`, `User_ID`, `First_name`, `Last_name`, `Position`, `Status`) VALUES
(1, 5, 'Nick Rysher', 'Datu', 'Front Desk', 'active'),
(2, 13, 'sample', 'vet', 'Assistant Vet', 'active'),
(3, 6, 'John Angelo', 'Manzano', 'Lead Veterinarian', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_ID` int(11) NOT NULL,
  `Role_ID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password_Hash` varchar(255) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Phone_number` varchar(20) DEFAULT NULL,
  `Status` enum('active','inactive','banned') DEFAULT 'active',
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_ID`, `Role_ID`, `Username`, `Password_Hash`, `Email`, `Phone_number`, `Status`, `Created_At`) VALUES
(4, 1, 'Jessel Von', '$2y$10$NRHk35Q5vvW/3R8U/s0xg.DnW6McDqwyQX8vkx3DLuL/FonhkOIE.', 'admin@gmail.com', '56952626566', 'active', '2026-02-14 09:42:01'),
(5, 4, 'Datu', '$2y$10$sjbk.CZ5UYROFmQL9vAPauAnCxZI.44PHqq9qW8sDu/LHRMNmARqi', 'datu@gmail.com', '0986754135', 'active', '2026-02-14 09:46:17'),
(6, 2, 'manzano', '$2y$10$j3E1xqpTBCUEBpV9lv3nGO1WbyZHEkWri98OD8CYZRBftfk7WMmpK', 'manzano@gmail.com', '09867541354', 'active', '2026-02-14 09:46:56'),
(11, 3, 'Jez', '$2y$10$vJiRKSDSdlMBcdS301Vv2OJurtVfgCrZ8PrbvhXcg3V5Fpc.WyyEu', 'jezreel@gmail.com', '09658254825', 'active', '2026-03-29 16:41:39'),
(12, 3, 'sample@gmail.com', '$2y$10$cjJcDI6zw3KrTLmQM9Gk3e0S65b6J3GBL0rlk.JKmQlom9O8yiJ8C', 'sampleuser@gmail.com', '09265856565', 'active', '2026-03-29 16:46:39'),
(13, 2, 'VetSample', '$2y$10$iCVqwBRp0piZBF74xms/TO8anwzvhj4L5oJawHwXoa./Y1PEgFTcS', 'VetSample@gmail.com', '09658546586', 'active', '2026-03-29 16:50:23'),
(14, 3, 'adrian@gmail.com', '$2y$10$FPJQv1l5XpuuDciXLYq/k.8F/yEhdd8vXvlxqNvm8hnRAHssD8X32', 'adrian@gmail.com', '09586582512', 'active', '2026-04-28 14:53:12');

-- --------------------------------------------------------

--
-- Table structure for table `vaccinations`
--

CREATE TABLE `vaccinations` (
  `Vaccination_ID` int(11) NOT NULL,
  `Pet_ID` int(11) NOT NULL,
  `Vaccine_Name` varchar(100) NOT NULL,
  `Date_Administered` date NOT NULL,
  `Next_Due_Date` date DEFAULT NULL,
  `Vet_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vet_availability`
--

CREATE TABLE `vet_availability` (
  `Availability_ID` int(11) NOT NULL,
  `Vet_User_ID` int(11) NOT NULL,
  `Day_Of_Week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`Appointment_ID`),
  ADD KEY `Owner_ID` (`Owner_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`),
  ADD KEY `Service_ID` (`Service_ID`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `breeds`
--
ALTER TABLE `breeds`
  ADD PRIMARY KEY (`Breed_ID`),
  ADD KEY `Species_ID` (`Species_ID`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`Consultation_ID`),
  ADD KEY `Owner_ID` (`Owner_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`),
  ADD KEY `Vet_ID` (`Vet_ID`);

--
-- Indexes for table `diagnosis_master`
--
ALTER TABLE `diagnosis_master`
  ADD PRIMARY KEY (`Diagnosis_ID`),
  ADD UNIQUE KEY `Diagnosis_Name` (`Diagnosis_Name`);

--
-- Indexes for table `first_aid_contents`
--
ALTER TABLE `first_aid_contents`
  ADD PRIMARY KEY (`Content_ID`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`Record_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`),
  ADD KEY `Vet_ID` (`Vet_ID`),
  ADD KEY `fk_medical_diagnosis` (`Diagnosis_ID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Notification_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`Owner_ID`),
  ADD UNIQUE KEY `User_ID` (`User_ID`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`Pet_ID`),
  ADD KEY `Owner_ID` (`Owner_ID`),
  ADD KEY `Species_ID` (`Species_ID`),
  ADD KEY `Breed_ID` (`Breed_ID`);

--
-- Indexes for table `pet_notes`
--
ALTER TABLE `pet_notes`
  ADD PRIMARY KEY (`Note_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`Role_ID`),
  ADD UNIQUE KEY `Role_name` (`Role_name`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`Service_ID`),
  ADD UNIQUE KEY `Service_Name` (`Service_Name`),
  ADD KEY `Category_ID` (`Category_ID`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`Category_ID`),
  ADD UNIQUE KEY `Category_Name` (`Category_Name`);

--
-- Indexes for table `species`
--
ALTER TABLE `species`
  ADD PRIMARY KEY (`Species_ID`),
  ADD UNIQUE KEY `Species_Name` (`Species_Name`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`Staff_ID`),
  ADD UNIQUE KEY `User_ID` (`User_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- Indexes for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD PRIMARY KEY (`Vaccination_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`);

--
-- Indexes for table `vet_availability`
--
ALTER TABLE `vet_availability`
  ADD PRIMARY KEY (`Availability_ID`),
  ADD KEY `Vet_User_ID` (`Vet_User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `breeds`
--
ALTER TABLE `breeds`
  MODIFY `Breed_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `Consultation_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diagnosis_master`
--
ALTER TABLE `diagnosis_master`
  MODIFY `Diagnosis_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `first_aid_contents`
--
ALTER TABLE `first_aid_contents`
  MODIFY `Content_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `Record_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Notification_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `Owner_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `Pet_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pet_notes`
--
ALTER TABLE `pet_notes`
  MODIFY `Note_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `Role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `Service_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `species`
--
ALTER TABLE `species`
  MODIFY `Species_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `Staff_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `vaccinations`
--
ALTER TABLE `vaccinations`
  MODIFY `Vaccination_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vet_availability`
--
ALTER TABLE `vet_availability`
  MODIFY `Availability_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`Owner_ID`) REFERENCES `owners` (`Owner_ID`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`Service_ID`) REFERENCES `services` (`Service_ID`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `breeds`
--
ALTER TABLE `breeds`
  ADD CONSTRAINT `breeds_ibfk_1` FOREIGN KEY (`Species_ID`) REFERENCES `species` (`Species_ID`);

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`Owner_ID`) REFERENCES `owners` (`Owner_ID`),
  ADD CONSTRAINT `consultations_ibfk_2` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`),
  ADD CONSTRAINT `consultations_ibfk_3` FOREIGN KEY (`Vet_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `fk_medical_diagnosis` FOREIGN KEY (`Diagnosis_ID`) REFERENCES `diagnosis_master` (`Diagnosis_ID`),
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`),
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`Vet_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `owners`
--
ALTER TABLE `owners`
  ADD CONSTRAINT `owners_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`Owner_ID`) REFERENCES `owners` (`Owner_ID`),
  ADD CONSTRAINT `pets_ibfk_2` FOREIGN KEY (`Species_ID`) REFERENCES `species` (`Species_ID`),
  ADD CONSTRAINT `pets_ibfk_3` FOREIGN KEY (`Breed_ID`) REFERENCES `breeds` (`Breed_ID`);

--
-- Constraints for table `pet_notes`
--
ALTER TABLE `pet_notes`
  ADD CONSTRAINT `pet_notes_ibfk_1` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `service_categories` (`Category_ID`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `roles` (`Role_ID`);

--
-- Constraints for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD CONSTRAINT `vaccinations_ibfk_1` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`);

--
-- Constraints for table `vet_availability`
--
ALTER TABLE `vet_availability`
  ADD CONSTRAINT `vet_availability_ibfk_1` FOREIGN KEY (`Vet_User_ID`) REFERENCES `users` (`User_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
