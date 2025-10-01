-- =====================================================
-- SMARTSTAY DATABASE SCHEMA
-- Core database structure with tables, relationships, and constraints
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `smart_stay` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smart_stay`;

-- =====================================================
-- CORE TABLES
-- =====================================================

-- Table: admins
CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('Super Admin','Admin','Manager') DEFAULT 'Admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: guests
CREATE TABLE `guests` (
  `guest_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `loyalty_points` int(11) DEFAULT 0,
  `membership_level` enum('Bronze','Silver','Gold','Platinum') DEFAULT 'Bronze',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`guest_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_guest_email` (`email`),
  KEY `idx_membership_level` (`membership_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: hotels
CREATE TABLE `hotels` (
  `hotel_id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `star_rating` decimal(2,1) DEFAULT NULL,
  `total_rooms` int(11) DEFAULT 0,
  `amenities` json DEFAULT NULL,
  `check_in_time` time DEFAULT '14:00:00',
  `check_out_time` time DEFAULT '11:00:00',
  `established_year` int(11) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`hotel_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_hotel_city` (`city`),
  KEY `idx_star_rating` (`star_rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: room_types
CREATE TABLE `room_types` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `max_occupancy` int(11) DEFAULT 2,
  `amenities` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rooms
CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `type_id` int(11) NOT NULL,
  `floor_number` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `area_sqft` int(11) DEFAULT NULL,
  `max_occupancy` int(11) DEFAULT 2,
  `amenities` json DEFAULT NULL,
  `maintenance_status` enum('Available','Under Maintenance','Out of Service') DEFAULT 'Available',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `unique_room` (`hotel_id`,`room_number`),
  KEY `idx_room_hotel` (`hotel_id`),
  KEY `idx_room_type` (`type_id`),
  KEY `idx_room_price` (`price`),
  CONSTRAINT `fk_room_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_room_type` FOREIGN KEY (`type_id`) REFERENCES `room_types` (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: bookings
CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `guest_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `adults` int(11) DEFAULT 1,
  `children` int(11) DEFAULT 0,
  `total_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `booking_status` enum('Confirmed','Cancelled','Completed','No-Show') DEFAULT 'Confirmed',
  `payment_status` enum('Pending','Paid','Partial','Refunded') DEFAULT 'Pending',
  `booking_source` enum('Website','Phone','Walk-in','Third-party') DEFAULT 'Website',
  `special_requests` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`booking_id`),
  KEY `idx_booking_guest` (`guest_id`),
  KEY `idx_booking_room` (`room_id`),
  KEY `idx_booking_dates` (`check_in`,`check_out`),
  KEY `idx_booking_status` (`booking_status`),
  CONSTRAINT `fk_booking_guest` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`guest_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
  CONSTRAINT `check_dates` CHECK (`check_out` > `check_in`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: events
CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `current_participants` int(11) DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00,
  `event_type` enum('Conference','Wedding','Meeting','Party','Workshop','Other') DEFAULT 'Other',
  `event_status` enum('Upcoming','Active','Completed','Cancelled') DEFAULT 'Upcoming',
  `organizer_name` varchar(100) DEFAULT NULL,
  `organizer_contact` varchar(100) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`event_id`),
  KEY `idx_event_hotel` (`hotel_id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_event_status` (`event_status`),
  CONSTRAINT `fk_event_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE,
  CONSTRAINT `check_times` CHECK (`end_time` > `start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: event_bookings
CREATE TABLE `event_bookings` (
  `event_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `participants` int(11) DEFAULT 1,
  `amount_paid` decimal(10,2) NOT NULL,
  `booking_status` enum('Confirmed','Cancelled','Attended','No-Show') DEFAULT 'Confirmed',
  `special_requirements` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`event_booking_id`),
  UNIQUE KEY `unique_event_guest` (`event_id`,`guest_id`),
  KEY `idx_event_booking_guest` (`guest_id`),
  CONSTRAINT `fk_event_booking_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_booking_guest` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`guest_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reviews
CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `service_rating` decimal(2,1) DEFAULT NULL,
  `cleanliness_rating` decimal(2,1) DEFAULT NULL,
  `location_rating` decimal(2,1) DEFAULT NULL,
  `amenities_rating` decimal(2,1) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`review_id`),
  KEY `idx_review_hotel` (`hotel_id`),
  KEY `idx_review_guest` (`guest_id`),
  KEY `idx_review_booking` (`booking_id`),
  CONSTRAINT `fk_review_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_guest` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`guest_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
  CONSTRAINT `check_rating` CHECK (`rating` between 1.0 and 5.0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: services
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `service_type` enum('Spa','Restaurant','Room Service','Transport','Laundry','Other') DEFAULT 'Other',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`service_id`),
  KEY `idx_service_hotel` (`hotel_id`),
  CONSTRAINT `fk_service_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: service_bookings
CREATE TABLE `service_bookings` (
  `service_booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `service_date` date DEFAULT NULL,
  `service_time` time DEFAULT NULL,
  `status` enum('Requested','Confirmed','Completed','Cancelled') DEFAULT 'Requested',
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`service_booking_id`),
  KEY `idx_service_booking_booking` (`booking_id`),
  KEY `idx_service_booking_service` (`service_id`),
  CONSTRAINT `fk_service_booking_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_service_booking_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payments
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `payment_method` enum('Cash','Card','Online','Bank Transfer') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('Success','Failed','Pending','Refunded') DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `gateway_response` text DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payment_booking` (`booking_id`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: staff
CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(50) NOT NULL,
  `department` enum('Front Desk','Housekeeping','Maintenance','Restaurant','Management','Other') NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_staff_hotel` (`hotel_id`),
  CONSTRAINT `fk_staff_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: hotel_images
CREATE TABLE `hotel_images` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `hotel_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` enum('Hotel','Room','Amenity','Event') DEFAULT 'Hotel',
  `caption` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`image_id`),
  KEY `idx_image_hotel` (`hotel_id`),
  KEY `idx_image_room` (`room_id`),
  CONSTRAINT `fk_image_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_image_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: maintenance_schedule
CREATE TABLE `maintenance_schedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `maintenance_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `status` enum('Scheduled','In Progress','Completed','Cancelled') DEFAULT 'Scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  KEY `idx_maintenance_room` (`room_id`),
  KEY `idx_maintenance_staff` (`assigned_to`),
  KEY `idx_maintenance_status` (`status`),
  CONSTRAINT `fk_maintenance_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_maintenance_staff` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: system_logs
CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` enum('Admin','Guest','Hotel','System') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_log_action` (`action`),
  KEY `idx_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;