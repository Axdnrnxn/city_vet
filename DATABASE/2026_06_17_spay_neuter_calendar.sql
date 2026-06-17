-- Global Event Calendar & Spay/Neuter Appointment System
-- Run this against city_vet_db after importing the current dump.

CREATE TABLE IF NOT EXISTS `calendar_events` (
  `Event_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(150) NOT NULL,
  `Event_Date` date NOT NULL,
  `Max_Slots` int(11) NOT NULL,
  `Created_By` int(11) DEFAULT NULL,
  `Status` enum('Open','Closed','Cancelled') NOT NULL DEFAULT 'Open',
  `Created_At` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Event_ID`),
  UNIQUE KEY `uniq_calendar_event_date` (`Event_Date`),
  KEY `Created_By` (`Created_By`),
  CONSTRAINT `calendar_events_created_by_fk` FOREIGN KEY (`Created_By`) REFERENCES `users` (`User_ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `appointments`
  ADD COLUMN IF NOT EXISTS `Event_ID` int(11) DEFAULT NULL AFTER `Appointment_ID`,
  ADD COLUMN IF NOT EXISTS `Confirmed_At` datetime DEFAULT NULL AFTER `Status`,
  ADD KEY IF NOT EXISTS `idx_appointments_event_status` (`Event_ID`, `Status`),
  ADD CONSTRAINT `appointments_event_fk` FOREIGN KEY (`Event_ID`) REFERENCES `calendar_events` (`Event_ID`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `appointment_pets` (
  `Appointment_Pet_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Appointment_ID` int(11) NOT NULL,
  `Pet_ID` int(11) NOT NULL,
  PRIMARY KEY (`Appointment_Pet_ID`),
  UNIQUE KEY `uniq_appointment_pet` (`Appointment_ID`, `Pet_ID`),
  KEY `Pet_ID` (`Pet_ID`),
  CONSTRAINT `appointment_pets_appointment_fk` FOREIGN KEY (`Appointment_ID`) REFERENCES `appointments` (`Appointment_ID`) ON DELETE CASCADE,
  CONSTRAINT `appointment_pets_pet_fk` FOREIGN KEY (`Pet_ID`) REFERENCES `pets` (`Pet_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `notifications`
  MODIFY `Type` enum('Appointment','Vaccination','Consultation','System','SpayNeuter') NOT NULL;
