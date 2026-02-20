-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 06:29 PM
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
  `Owner_ID` int(11) DEFAULT NULL,
  `Veterinarian_ID` int(11) DEFAULT NULL,
  `Appointment_Type_ID` int(11) DEFAULT NULL,
  `Scheduled_Date` date DEFAULT NULL,
  `Scheduled_Time` time DEFAULT NULL,
  `Actual_Start_Time` datetime DEFAULT NULL,
  `Actual_End_Time` datetime DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Pending',
  `Cancellation_Reason` text DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_detail`
--

CREATE TABLE `appointment_detail` (
  `Appointment_detail_ID` int(11) NOT NULL,
  `Appointment_ID` int(11) DEFAULT NULL,
  `Pet_ID` int(11) DEFAULT NULL,
  `Chief_complaint` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_types`
--

CREATE TABLE `appointment_types` (
  `Appointment_Type_ID` int(11) NOT NULL,
  `Type_Name` varchar(50) NOT NULL,
  `Typical_Duration_Minutes` int(11) DEFAULT NULL,
  `Base_Fee` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `breeds`
--

CREATE TABLE `breeds` (
  `Breed_ID` int(11) NOT NULL,
  `Species_ID` int(11) DEFAULT NULL,
  `Breed_Name` varchar(50) DEFAULT NULL,
  `Average_Lifespan_Years` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diagnosis`
--

CREATE TABLE `diagnosis` (
  `Diagnosis_ID` int(11) NOT NULL,
  `Consultation_ID` int(11) DEFAULT NULL,
  `Diagnosis_master_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diagnosis_master`
--

CREATE TABLE `diagnosis_master` (
  `Diagnosis_Master_ID` int(11) NOT NULL,
  `Unique_Code` varchar(20) DEFAULT NULL,
  `Code` varchar(20) DEFAULT NULL,
  `Diagnosis_Name` varchar(100) DEFAULT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laboratory_results`
--

CREATE TABLE `laboratory_results` (
  `Lab_Result_ID` int(11) NOT NULL,
  `Treatment_ID` int(11) DEFAULT NULL,
  `Pet_ID` int(11) DEFAULT NULL,
  `Test_Name` varchar(100) DEFAULT NULL,
  `Test_Date` date DEFAULT NULL,
  `Result_Value` text DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `Attachment_Path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `Medicine_ID` int(11) NOT NULL,
  `Category_ID` int(11) DEFAULT NULL,
  `Medicine_Name` varchar(100) DEFAULT NULL,
  `Generic_Name` varchar(100) DEFAULT NULL,
  `Manufacturer` varchar(100) DEFAULT NULL,
  `Dosage_Form` varchar(50) DEFAULT NULL,
  `Strength` varchar(50) DEFAULT NULL,
  `Unit_Price` decimal(10,2) DEFAULT NULL,
  `Stock_Quantity` int(11) DEFAULT NULL,
  `Reorder_Level` int(11) DEFAULT NULL,
  `Expiry_Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicine_categories`
--

CREATE TABLE `medicine_categories` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(50) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `online_consultations`
--

CREATE TABLE `online_consultations` (
  `Consultation_ID` int(11) NOT NULL,
  `Appointment_details_ID` int(11) DEFAULT NULL,
  `Veterinarian_ID` int(11) DEFAULT NULL,
  `Consultation_Time` time DEFAULT NULL,
  `Consultation_Date` date DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `Chief_Concern` text DEFAULT NULL,
  `Veterinarian_Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `Owner_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `First_name` varchar(50) NOT NULL,
  `Last_name` varchar(50) NOT NULL,
  `Address_Line1` varchar(100) DEFAULT NULL,
  `Address_Line2` varchar(100) DEFAULT NULL,
  `City` varchar(50) DEFAULT NULL,
  `Province` varchar(50) DEFAULT NULL,
  `Contact_Number` varchar(20) DEFAULT NULL,
  `Emergency_Contact_Name` varchar(100) DEFAULT NULL,
  `Emergency_Contact_Number` varchar(20) DEFAULT NULL,
  `Registration_Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`Owner_ID`, `User_ID`, `First_name`, `Last_name`, `Address_Line1`, `Address_Line2`, `City`, `Province`, `Contact_Number`, `Emergency_Contact_Name`, `Emergency_Contact_Number`, `Registration_Date`) VALUES
(1, 2, 'Jezreel Adrian', 'Torion', NULL, NULL, NULL, NULL, '09659258668', NULL, NULL, '2026-01-10');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `Pet_ID` int(11) NOT NULL,
  `Owner_ID` int(11) DEFAULT NULL,
  `Species_ID` int(11) DEFAULT NULL,
  `Breed_ID` int(11) DEFAULT NULL,
  `Pet_Name` varchar(50) NOT NULL,
  `Sex` enum('Male','Female') DEFAULT NULL,
  `Birthdate` date DEFAULT NULL,
  `Weight_KG` decimal(5,2) DEFAULT NULL,
  `Color` varchar(50) DEFAULT NULL,
  `Registration_Date` date DEFAULT NULL,
  `Is_Active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_status_history`
--

CREATE TABLE `pet_status_history` (
  `Status_History_ID` int(11) NOT NULL,
  `Pet_ID` int(11) DEFAULT NULL,
  `Status_Type_ID` int(11) DEFAULT NULL,
  `Status_Date` datetime DEFAULT current_timestamp(),
  `Notes` text DEFAULT NULL,
  `Updated_By_User_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_status_types`
--

CREATE TABLE `pet_status_types` (
  `Status_Type_ID` int(11) NOT NULL,
  `Status_Name` varchar(50) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `Prescription_ID` int(11) NOT NULL,
  `Treatment_ID` int(11) DEFAULT NULL,
  `Medicine_ID` int(11) DEFAULT NULL,
  `Dosage_Amount` varchar(50) DEFAULT NULL,
  `Frequency` varchar(50) DEFAULT NULL,
  `Duration_Days` int(11) DEFAULT NULL,
  `Start_Date` date DEFAULT NULL,
  `End_Date` date DEFAULT NULL,
  `Quantity_Prescribed` int(11) DEFAULT NULL,
  `Total_Cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `Role_ID` int(11) NOT NULL,
  `Role_name` varchar(50) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`Role_ID`, `Role_name`, `Description`) VALUES
(1, 'Administrator', 'Full system access'),
(2, 'Veterinarian/Staff', 'Clinic management access'),
(3, 'Pet Owner', 'Client access for appointments and pets');

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `Specialization_ID` int(11) NOT NULL,
  `Specialization_Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `species`
--

CREATE TABLE `species` (
  `Species_ID` int(11) NOT NULL,
  `Species_Name` varchar(50) NOT NULL,
  `Common_Characteristics` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `Staff_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `First_name` varchar(50) NOT NULL,
  `Last_name` varchar(50) NOT NULL,
  `Job_Title` varchar(50) DEFAULT NULL,
  `Contact_Number` varchar(20) DEFAULT NULL,
  `Is_Active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `treatments`
--

CREATE TABLE `treatments` (
  `Treatment_ID` int(11) NOT NULL,
  `Consultation_ID` int(11) DEFAULT NULL,
  `Veterinarian_ID` int(11) DEFAULT NULL,
  `Treatment_master_ID` int(11) DEFAULT NULL,
  `Diagnosis_Master_ID` int(11) DEFAULT NULL,
  `Treatment_Date` datetime DEFAULT NULL,
  `Treatment_Procedure` text DEFAULT NULL,
  `Treatment_Status` varchar(20) DEFAULT NULL,
  `Treatment_Plan` text DEFAULT NULL,
  `Total_Cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `treatment_master`
--

CREATE TABLE `treatment_master` (
  `Treatment_master_ID` int(11) NOT NULL,
  `Treatment_type` varchar(50) DEFAULT NULL,
  `Desc_Text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_ID` int(11) NOT NULL,
  `Role_ID` int(11) DEFAULT NULL,
  `Username` varchar(50) NOT NULL,
  `Password_Hash` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone_number` varchar(20) DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp(),
  `Last_Login` datetime DEFAULT NULL,
  `Is_Active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_ID`, `Role_ID`, `Username`, `Password_Hash`, `Email`, `Phone_number`, `Created_At`, `Last_Login`, `Is_Active`) VALUES
(2, 3, 'Adrain', '$2y$10$gv4PDscSy4j8pdqocFOoY.Sxak5hr0lR0u.yVKi/MnWX0qyBBCJ/u', 'axdnrnxn@gmail.com', '09659258668', '2026-01-10 01:05:41', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `vaccinations`
--

CREATE TABLE `vaccinations` (
  `Vaccination_ID` int(11) NOT NULL,
  `Pet_ID` int(11) DEFAULT NULL,
  `Vaccine_Type_ID` int(11) DEFAULT NULL,
  `Veterinarian_ID` int(11) DEFAULT NULL,
  `Treatment_ID` int(11) DEFAULT NULL,
  `Vaccination_Date` date DEFAULT NULL,
  `Batch_Number` varchar(50) DEFAULT NULL,
  `Next_Due_Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vaccine_types`
--

CREATE TABLE `vaccine_types` (
  `Vaccine_Type_ID` int(11) NOT NULL,
  `Species_ID` int(11) DEFAULT NULL,
  `Vaccine_Name` varchar(100) DEFAULT NULL,
  `Manufacturer` varchar(100) DEFAULT NULL,
  `Dosage` varchar(50) DEFAULT NULL,
  `Recommended_Age_Weeks` int(11) DEFAULT NULL,
  `Booster_Interval_Months` int(11) DEFAULT NULL,
  `Is_Required_By_Law` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `veterinarians`
--

CREATE TABLE `veterinarians` (
  `Veterinarian_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `License_Number` varchar(50) DEFAULT NULL,
  `Specialization` varchar(100) DEFAULT NULL,
  `Phone_number` varchar(20) DEFAULT NULL,
  `Availability` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vet_availability`
--

CREATE TABLE `vet_availability` (
  `Availability_ID` int(11) NOT NULL,
  `Veterinarian_ID` int(11) DEFAULT NULL,
  `Day_Of_Week` varchar(15) DEFAULT NULL,
  `Start_Time` time DEFAULT NULL,
  `End_Time` time DEFAULT NULL,
  `Is_Available` tinyint(1) DEFAULT 1
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
  ADD KEY `Veterinarian_ID` (`Veterinarian_ID`),
  ADD KEY `Appointment_Type_ID` (`Appointment_Type_ID`);

--
-- Indexes for table `appointment_detail`
--
ALTER TABLE `appointment_detail`
  ADD PRIMARY KEY (`Appointment_detail_ID`),
  ADD KEY `Appointment_ID` (`Appointment_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`);

--
-- Indexes for table `appointment_types`
--
ALTER TABLE `appointment_types`
  ADD PRIMARY KEY (`Appointment_Type_ID`);

--
-- Indexes for table `breeds`
--
ALTER TABLE `breeds`
  ADD PRIMARY KEY (`Breed_ID`),
  ADD KEY `Species_ID` (`Species_ID`);

--
-- Indexes for table `diagnosis`
--
ALTER TABLE `diagnosis`
  ADD PRIMARY KEY (`Diagnosis_ID`),
  ADD KEY `Consultation_ID` (`Consultation_ID`),
  ADD KEY `Diagnosis_master_ID` (`Diagnosis_master_ID`);

--
-- Indexes for table `diagnosis_master`
--
ALTER TABLE `diagnosis_master`
  ADD PRIMARY KEY (`Diagnosis_Master_ID`);

--
-- Indexes for table `laboratory_results`
--
ALTER TABLE `laboratory_results`
  ADD PRIMARY KEY (`Lab_Result_ID`),
  ADD KEY `Treatment_ID` (`Treatment_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`Medicine_ID`),
  ADD KEY `Category_ID` (`Category_ID`);

--
-- Indexes for table `medicine_categories`
--
ALTER TABLE `medicine_categories`
  ADD PRIMARY KEY (`Category_ID`);

--
-- Indexes for table `online_consultations`
--
ALTER TABLE `online_consultations`
  ADD PRIMARY KEY (`Consultation_ID`),
  ADD KEY `Appointment_details_ID` (`Appointment_details_ID`),
  ADD KEY `Veterinarian_ID` (`Veterinarian_ID`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`Owner_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`Pet_ID`),
  ADD KEY `Owner_ID` (`Owner_ID`),
  ADD KEY `Species_ID` (`Species_ID`),
  ADD KEY `Breed_ID` (`Breed_ID`);

--
-- Indexes for table `pet_status_history`
--
ALTER TABLE `pet_status_history`
  ADD PRIMARY KEY (`Status_History_ID`),
  ADD KEY `Pet_ID` (`Pet_ID`),
  ADD KEY `Status_Type_ID` (`Status_Type_ID`);

--
-- Indexes for table `pet_status_types`
--
ALTER TABLE `pet_status_types`
  ADD PRIMARY KEY (`Status_Type_ID`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`Prescription_ID`),
  ADD KEY `Treatment_ID` (`Treatment_ID`),
  ADD KEY `Medicine_ID` (`Medicine_ID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`Role_ID`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`Specialization_ID`);

--
-- Indexes for table `species`
--
ALTER TABLE `species`
  ADD PRIMARY KEY (`Species_ID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`Staff_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`Treatment_ID`),
  ADD KEY `Consultation_ID` (`Consultation_ID`),
  ADD KEY `Veterinarian_ID` (`Veterinarian_ID`),
  ADD KEY `Treatment_master_ID` (`Treatment_master_ID`);

--
-- Indexes for table `treatment_master`
--
ALTER TABLE `treatment_master`
  ADD PRIMARY KEY (`Treatment_master_ID`);

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
  ADD KEY `Pet_ID` (`Pet_ID`),
  ADD KEY `Vaccine_Type_ID` (`Vaccine_Type_ID`),
  ADD KEY `Treatment_ID` (`Treatment_ID`);

--
-- Indexes for table `vaccine_types`
--
ALTER TABLE `vaccine_types`
  ADD PRIMARY KEY (`Vaccine_Type_ID`),
  ADD KEY `Species_ID` (`Species_ID`);

--
-- Indexes for table `veterinarians`
--
ALTER TABLE `veterinarians`
  ADD PRIMARY KEY (`Veterinarian_ID`),
  ADD UNIQUE KEY `License_Number` (`License_Number`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `vet_availability`
--
ALTER TABLE `vet_availability`
  ADD PRIMARY KEY (`Availability_ID`),
  ADD KEY `Veterinarian_ID` (`Veterinarian_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_detail`
--
ALTER TABLE `appointment_detail`
  MODIFY `Appointment_detail_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_types`
--
ALTER TABLE `appointment_types`
  MODIFY `Appointment_Type_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `breeds`
--
ALTER TABLE `breeds`
  MODIFY `Breed_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diagnosis`
--
ALTER TABLE `diagnosis`
  MODIFY `Diagnosis_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diagnosis_master`
--
ALTER TABLE `diagnosis_master`
  MODIFY `Diagnosis_Master_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laboratory_results`
--
ALTER TABLE `laboratory_results`
  MODIFY `Lab_Result_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `Medicine_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicine_categories`
--
ALTER TABLE `medicine_categories`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `online_consultations`
--
ALTER TABLE `online_consultations`
  MODIFY `Consultation_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `Owner_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `Pet_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_status_history`
--
ALTER TABLE `pet_status_history`
  MODIFY `Status_History_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_status_types`
--
ALTER TABLE `pet_status_types`
  MODIFY `Status_Type_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `Prescription_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `Role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `Specialization_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `species`
--
ALTER TABLE `species`
  MODIFY `Species_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `Staff_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `treatments`
--
ALTER TABLE `treatments`
  MODIFY `Treatment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `treatment_master`
--
ALTER TABLE `treatment_master`
  MODIFY `Treatment_master_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vaccinations`
--
ALTER TABLE `vaccinations`
  MODIFY `Vaccination_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vaccine_types`
--
ALTER TABLE `vaccine_types`
  MODIFY `Vaccine_Type_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `veterinarians`
--
ALTER TABLE `veterinarians`
  MODIFY `Veterinarian_ID` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`Veterinarian_ID`) REFERENCES `veterinarians` (`Veterinarian_ID`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`Appointment_Type_ID`) REFERENCES `appointment_types` (`Appointment_Type_ID`);

--
-- Constraints for table `appointment_detail`
--
ALTER TABLE `appointment_detail`
  ADD CONSTRAINT `appointment_detail_ibfk_1` FOREIGN KEY (`Appointment_ID`) REFERENCES `appointments` (`Appointment_ID`),
  ADD CONSTRAINT `appointment_detail_ibfk_2` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`);

--
-- Constraints for table `breeds`
--
ALTER TABLE `breeds`
  ADD CONSTRAINT `breeds_ibfk_1` FOREIGN KEY (`Species_ID`) REFERENCES `species` (`Species_ID`);

--
-- Constraints for table `diagnosis`
--
ALTER TABLE `diagnosis`
  ADD CONSTRAINT `diagnosis_ibfk_1` FOREIGN KEY (`Consultation_ID`) REFERENCES `online_consultations` (`Consultation_ID`),
  ADD CONSTRAINT `diagnosis_ibfk_2` FOREIGN KEY (`Diagnosis_master_ID`) REFERENCES `diagnosis_master` (`Diagnosis_Master_ID`);

--
-- Constraints for table `laboratory_results`
--
ALTER TABLE `laboratory_results`
  ADD CONSTRAINT `laboratory_results_ibfk_1` FOREIGN KEY (`Treatment_ID`) REFERENCES `treatments` (`Treatment_ID`),
  ADD CONSTRAINT `laboratory_results_ibfk_2` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`);

--
-- Constraints for table `medicines`
--
ALTER TABLE `medicines`
  ADD CONSTRAINT `medicines_ibfk_1` FOREIGN KEY (`Category_ID`) REFERENCES `medicine_categories` (`Category_ID`);

--
-- Constraints for table `online_consultations`
--
ALTER TABLE `online_consultations`
  ADD CONSTRAINT `online_consultations_ibfk_1` FOREIGN KEY (`Appointment_details_ID`) REFERENCES `appointment_detail` (`Appointment_detail_ID`),
  ADD CONSTRAINT `online_consultations_ibfk_2` FOREIGN KEY (`Veterinarian_ID`) REFERENCES `veterinarians` (`Veterinarian_ID`);

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
-- Constraints for table `pet_status_history`
--
ALTER TABLE `pet_status_history`
  ADD CONSTRAINT `pet_status_history_ibfk_1` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`),
  ADD CONSTRAINT `pet_status_history_ibfk_2` FOREIGN KEY (`Status_Type_ID`) REFERENCES `pet_status_types` (`Status_Type_ID`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`Treatment_ID`) REFERENCES `treatments` (`Treatment_ID`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`Medicine_ID`) REFERENCES `medicines` (`Medicine_ID`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `treatments`
--
ALTER TABLE `treatments`
  ADD CONSTRAINT `treatments_ibfk_1` FOREIGN KEY (`Consultation_ID`) REFERENCES `online_consultations` (`Consultation_ID`),
  ADD CONSTRAINT `treatments_ibfk_2` FOREIGN KEY (`Veterinarian_ID`) REFERENCES `veterinarians` (`Veterinarian_ID`),
  ADD CONSTRAINT `treatments_ibfk_3` FOREIGN KEY (`Treatment_master_ID`) REFERENCES `treatment_master` (`Treatment_master_ID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `roles` (`Role_ID`);

--
-- Constraints for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD CONSTRAINT `vaccinations_ibfk_1` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`),
  ADD CONSTRAINT `vaccinations_ibfk_2` FOREIGN KEY (`Vaccine_Type_ID`) REFERENCES `vaccine_types` (`Vaccine_Type_ID`),
  ADD CONSTRAINT `vaccinations_ibfk_3` FOREIGN KEY (`Treatment_ID`) REFERENCES `treatments` (`Treatment_ID`);

--
-- Constraints for table `vaccine_types`
--
ALTER TABLE `vaccine_types`
  ADD CONSTRAINT `vaccine_types_ibfk_1` FOREIGN KEY (`Species_ID`) REFERENCES `species` (`Species_ID`);

--
-- Constraints for table `veterinarians`
--
ALTER TABLE `veterinarians`
  ADD CONSTRAINT `veterinarians_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`);

--
-- Constraints for table `vet_availability`
--
ALTER TABLE `vet_availability`
  ADD CONSTRAINT `vet_availability_ibfk_1` FOREIGN KEY (`Veterinarian_ID`) REFERENCES `veterinarians` (`Veterinarian_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
