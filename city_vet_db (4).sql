-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2026 at 09:22 PM
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
(1, 1, 1, 5, NULL, '2026-02-18 09:48:00', 'Confirmed', 'Walk-in Booking');

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
(1, 1, 'Aspin', 'active'),
(2, 1, 'Shih Tzu', 'active'),
(3, 2, 'Puspin', 'active'),
(4, 1, 'Bulldog', 'active'),
(5, 1, 'Husky', 'active'),
(6, 2, 'Siamese', 'active'),
(7, 2, 'Persian', 'active');

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
(1, 1, NULL, NULL, 'vaccination', '2026-02-21 03:15:00', 'need vaccine', 0),
(2, 21, NULL, NULL, 'neuter', '2026-02-21 03:28:29', 'Must revisit 2 days from now', 1);

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
-- Table structure for table `online_consultations`
--

CREATE TABLE `online_consultations` (
  `Consultation_ID` int(11) NOT NULL,
  `Owner_ID` int(11) NOT NULL,
  `Pet_ID` int(11) NOT NULL,
  `Vet_ID` int(11) DEFAULT NULL,
  `Subject` varchar(255) NOT NULL,
  `Concern_Description` text NOT NULL,
  `Status` enum('Pending','Approved','Declined','Completed') DEFAULT 'Pending',
  `Meeting_Link` varchar(255) DEFAULT NULL,
  `Consultation_Date` datetime DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
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
(1, 4, 'admin', 'admin', '56952626566', 'lapasan, Carmen', 'active', '2026-02-14 09:42:01'),
(2, 6, 'John Angelo', 'Manzano', '09867541354', 'phinma coc, Carmen', 'active', '2026-02-14 09:46:56'),
(3, 7, 'asdas', 'asdasd', 'asdas', 'asdsad, Carmen', 'active', '2026-02-14 10:03:11');

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
  `Status` enum('active','deceased','transferred','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`Pet_ID`, `Owner_ID`, `Name`, `Species_ID`, `Breed_ID`, `Gender`, `Birthdate`, `Weight`, `Color_Markings`, `Status`) VALUES
(1, 2, 'Pongpong', 1, 1, 'Male', '2026-02-27', 12.00, '12', 'active'),
(2, 2, 'Hunky', 1, 1, 'Male', '2026-02-10', 23.00, 'Dirty White', 'active'),
(3, 2, 'Lowky', 1, 1, 'Male', '2026-02-04', 5.00, 'Black and white', 'active'),
(13, 1, 'Hunky', 1, 1, 'Male', '2026-03-06', 23.00, '23', 'active'),
(15, 3, 'otot', 1, 1, 'Male', '2026-02-03', 2.00, 'telapia', 'archived'),
(17, 3, 'Kekai', 1, 1, 'Male', '2026-02-03', 2.00, 'telapia', 'archived'),
(21, 3, 'Kekai', 1, 1, 'Male', '0000-00-00', 0.00, '', 'archived'),
(22, 3, 'ds', 1, 1, 'Female', '0000-00-00', 0.00, '', 'archived'),
(23, 3, 'weqw', 1, 2, 'Male', '0000-00-00', 0.00, '', 'archived'),
(27, 3, 'kokok', 2, 3, 'Female', '2026-02-03', 2.00, 'telapia', 'archived'),
(28, 1, 'doggy', 1, 2, 'Female', '2026-02-03', 12.00, 'white', 'active'),
(29, 3, 'Iring', 2, 6, 'Female', '2026-02-01', 12.00, 'orange and grey', 'active');

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
(5, 3, 'Anti-Rabies Shot', 0.00, 'active'),
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
(2, 'Cat', 'active');

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
(1, 5, 'Nick Rysher', 'Datu', 'Front Desk', 'active');

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
(1, 1, 'admin', '$2y$10$fS0I4sU.8M9T5YmR3Z6zOeXpM.R8qE8vE7hG5jK9lW1bC2dE3fG4h', 'admin@cityvet.com', NULL, 'active', '2026-02-14 09:38:29'),
(2, 2, 'veterinarian', '$2y$10$8K9O...hash...', 'vet@cityvet.com', NULL, 'active', '2026-02-14 09:38:29'),
(3, 4, 'receptionist', '$2y$10$8K9O...hash...', 'staff@cityvet.com', NULL, 'active', '2026-02-14 09:38:29'),
(4, 1, 'admin@gmail.com', '$2y$10$NRHk35Q5vvW/3R8U/s0xg.DnW6McDqwyQX8vkx3DLuL/FonhkOIE.', 'admin@gmail.com', '56952626566', 'active', '2026-02-14 09:42:01'),
(5, 4, 'datu', '$2y$10$8lvjzvX78lUTSiS5DdN6JOdWRAjf8V7bRSAr7YP9azejL6ygDjoiC', 'datu@gmail.com', '0986754135', 'active', '2026-02-14 09:46:17'),
(6, 3, 'manzano@gmail.com', '$2y$10$.a5bEBHn7b7Pm4hICFc2C.kDGjB8jyJhV4EYsViqiYzeYHj/Bd2L.', 'manzano@gmail.com', '09867541354', 'active', '2026-02-14 09:46:56'),
(7, 3, 'asd@gmail.com', '$2y$10$bffVLiM9wNPc6GSWARGA4.0Xov9Blj2A/iKk92.3KEj9uD.jtcZLy', 'asd@gmail.com', 'asdas', 'active', '2026-02-14 10:03:11');

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
-- Indexes for table `breeds`
--
ALTER TABLE `breeds`
  ADD PRIMARY KEY (`Breed_ID`),
  ADD KEY `Species_ID` (`Species_ID`);

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
-- Indexes for table `online_consultations`
--
ALTER TABLE `online_consultations`
  ADD PRIMARY KEY (`Consultation_ID`),
  ADD KEY `Owner_ID` (`Owner_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`),
  ADD KEY `Vet_ID` (`Vet_ID`);

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
  MODIFY `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `breeds`
--
ALTER TABLE `breeds`
  MODIFY `Breed_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `Record_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Notification_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `online_consultations`
--
ALTER TABLE `online_consultations`
  MODIFY `Consultation_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `Owner_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `Pet_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
  MODIFY `Species_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `Staff_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Constraints for table `breeds`
--
ALTER TABLE `breeds`
  ADD CONSTRAINT `breeds_ibfk_1` FOREIGN KEY (`Species_ID`) REFERENCES `species` (`Species_ID`);

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
-- Constraints for table `online_consultations`
--
ALTER TABLE `online_consultations`
  ADD CONSTRAINT `online_consultations_ibfk_1` FOREIGN KEY (`Owner_ID`) REFERENCES `owners` (`Owner_ID`),
  ADD CONSTRAINT `online_consultations_ibfk_2` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`),
  ADD CONSTRAINT `online_consultations_ibfk_3` FOREIGN KEY (`Vet_ID`) REFERENCES `users` (`User_ID`);

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
