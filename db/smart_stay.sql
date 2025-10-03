-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2025 at 06:08 PM
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
-- Database: `smart_stay`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CalculateLoyaltyPoints` (IN `p_booking_id` INT)   BEGIN
    DECLARE v_guest_id INT;
    DECLARE v_final_amount DECIMAL(10,2);
    DECLARE v_loyalty_points INT;
    DECLARE v_current_points INT;
    DECLARE v_new_membership VARCHAR(20);
    
    -- Get booking details
    SELECT guest_id, final_amount 
    INTO v_guest_id, v_final_amount
    FROM bookings 
    WHERE booking_id = p_booking_id;
    
    -- Calculate loyalty points (1 point per $10 spent)
    SET v_loyalty_points = FLOOR(v_final_amount / 10);
    
    -- Get current points
    SELECT loyalty_points INTO v_current_points
    FROM guests
    WHERE guest_id = v_guest_id;
    
    -- Add new points
    SET v_current_points = v_current_points + v_loyalty_points;
    
    -- Determine membership level
    IF v_current_points >= 5000 THEN
        SET v_new_membership = 'Platinum';
    ELSEIF v_current_points >= 2000 THEN
        SET v_new_membership = 'Gold';
    ELSEIF v_current_points >= 500 THEN
        SET v_new_membership = 'Silver';
    ELSE
        SET v_new_membership = 'Bronze';
    END IF;
    
    -- Update guest record
    UPDATE guests 
    SET loyalty_points = v_current_points,
        membership_level = v_new_membership
    WHERE guest_id = v_guest_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CalculateRoomRevenue` (IN `p_hotel_id` INT, IN `p_start_date` DATE, IN `p_end_date` DATE, OUT `p_total_revenue` DECIMAL(10,2))   BEGIN
    SELECT COALESCE(SUM(b.final_amount), 0)
    INTO p_total_revenue
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.room_id
    WHERE r.hotel_id = p_hotel_id
    AND b.check_in >= p_start_date
    AND b.check_out <= p_end_date
    AND b.booking_status = 'Completed'
    AND b.payment_status = 'Paid';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GenerateMonthlyHotelReport` (IN `p_hotel_id` INT, IN `p_year` INT, IN `p_month` INT)   BEGIN
    DECLARE v_start_date DATE;
    DECLARE v_end_date DATE;
    
    SET v_start_date = DATE(CONCAT(p_year, '-', LPAD(p_month, 2, '0'), '-01'));
    SET v_end_date = LAST_DAY(v_start_date);
    
    SELECT 
        h.hotel_name,
        COUNT(DISTINCT b.booking_id) as total_bookings,
        COUNT(DISTINCT CASE WHEN b.booking_status = 'Completed' THEN b.booking_id END) as completed_bookings,
        COUNT(DISTINCT CASE WHEN b.booking_status = 'Cancelled' THEN b.booking_id END) as cancelled_bookings,
        COALESCE(SUM(CASE WHEN b.booking_status = 'Completed' THEN b.final_amount ELSE 0 END), 0) as total_revenue,
        COALESCE(AVG(CASE WHEN b.booking_status = 'Completed' THEN b.final_amount END), 0) as avg_booking_value,
        COUNT(DISTINCT b.guest_id) as unique_guests,
        COALESCE(AVG(rv.rating), 0) as avg_rating,
        COUNT(DISTINCT e.event_id) as total_events
    FROM hotels h
    LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
    LEFT JOIN bookings b ON r.room_id = b.room_id 
        AND b.check_in >= v_start_date 
        AND b.check_in <= v_end_date
    LEFT JOIN reviews rv ON h.hotel_id = rv.hotel_id 
        AND rv.created_at >= v_start_date 
        AND rv.created_at <= v_end_date
    LEFT JOIN events e ON h.hotel_id = e.hotel_id 
        AND e.event_date >= v_start_date 
        AND e.event_date <= v_end_date
    WHERE h.hotel_id = p_hotel_id
    GROUP BY h.hotel_id, h.hotel_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAvailableRooms` (IN `p_hotel_id` INT, IN `p_check_in` DATE, IN `p_check_out` DATE, IN `p_room_type_id` INT)   BEGIN
    SELECT 
        r.room_id,
        r.room_number,
        r.floor_number,
        r.price,
        r.area_sqft,
        r.max_occupancy,
        rt.type_name,
        r.amenities,
        r.maintenance_status
    FROM rooms r
    INNER JOIN room_types rt ON r.type_id = rt.type_id
    WHERE r.hotel_id = p_hotel_id
    AND (p_room_type_id IS NULL OR r.type_id = p_room_type_id)
    AND r.is_active = 1
    AND r.maintenance_status = 'Available'
    AND r.room_id NOT IN (
        SELECT b.room_id
        FROM bookings b
        WHERE b.booking_status IN ('Confirmed', 'Completed')
        AND NOT (p_check_out <= b.check_in OR p_check_in >= b.check_out)
    )
    ORDER BY r.price ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessLoyaltyUpgrades` ()   BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_guest_id INT;
    DECLARE v_points INT;
    DECLARE v_new_level VARCHAR(20);
    DECLARE v_old_level VARCHAR(20);
    
    DECLARE guest_cursor CURSOR FOR 
        SELECT guest_id, loyalty_points, membership_level
        FROM guests
        WHERE is_active = 1;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN guest_cursor;
    
    read_loop: LOOP
        FETCH guest_cursor INTO v_guest_id, v_points, v_old_level;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Determine new membership level
        IF v_points >= 5000 THEN
            SET v_new_level = 'Platinum';
        ELSEIF v_points >= 2000 THEN
            SET v_new_level = 'Gold';
        ELSEIF v_points >= 500 THEN
            SET v_new_level = 'Silver';
        ELSE
            SET v_new_level = 'Bronze';
        END IF;
        
        -- Only update if level changed
        IF v_new_level != v_old_level THEN
            UPDATE guests 
            SET membership_level = v_new_level
            WHERE guest_id = v_guest_id;
        END IF;
    END LOOP;
    
    CLOSE guest_cursor;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateRoomPrices` (IN `p_hotel_id` INT, IN `p_adjustment_percentage` DECIMAL(5,2))   BEGIN
    DECLARE v_season VARCHAR(20);
    DECLARE v_multiplier DECIMAL(5,2);
    
    SET v_season = GetSeason(CURDATE());
    
    -- Seasonal multiplier
    CASE v_season
        WHEN 'Peak' THEN SET v_multiplier = 1.20;
        WHEN 'High' THEN SET v_multiplier = 1.10;
        WHEN 'Low' THEN SET v_multiplier = 0.90;
        ELSE SET v_multiplier = 1.00;
    END CASE;
    
    -- Apply adjustment
    UPDATE rooms
    SET price = ROUND(price * (1 + p_adjustment_percentage / 100) * v_multiplier, 2),
        updated_at = CURRENT_TIMESTAMP
    WHERE hotel_id = p_hotel_id
    AND is_active = 1
    AND maintenance_status = 'Available';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateRoomPricesBasedOnDemand` (IN `p_hotel_id` INT)   BEGIN
    DECLARE v_total_rooms INT;
    DECLARE v_booked_rooms INT;
    DECLARE v_occupancy_rate DECIMAL(5,2);
    DECLARE v_price_adjustment DECIMAL(5,2);
    
    -- Count total available rooms
    SELECT COUNT(*) INTO v_total_rooms
    FROM rooms
    WHERE hotel_id = p_hotel_id
    AND is_active = 1
    AND maintenance_status = 'Available';
    
    -- Count currently booked rooms
    SELECT COUNT(DISTINCT b.room_id) INTO v_booked_rooms
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.room_id
    WHERE r.hotel_id = p_hotel_id
    AND b.booking_status = 'Confirmed'
    AND CURDATE() BETWEEN b.check_in AND b.check_out;
    
    -- Calculate occupancy rate
    IF v_total_rooms > 0 THEN
        SET v_occupancy_rate = (v_booked_rooms / v_total_rooms) * 100;
    ELSE
        SET v_occupancy_rate = 0;
    END IF;
    
    -- Determine price adjustment based on occupancy
    IF v_occupancy_rate >= 90 THEN
        SET v_price_adjustment = 15.00;
    ELSEIF v_occupancy_rate >= 70 THEN
        SET v_price_adjustment = 10.00;
    ELSEIF v_occupancy_rate >= 50 THEN
        SET v_price_adjustment = 5.00;
    ELSEIF v_occupancy_rate < 30 THEN
        SET v_price_adjustment = -10.00;
    ELSE
        SET v_price_adjustment = 0.00;
    END IF;
    
    -- Apply price adjustment
    UPDATE rooms r
    SET r.price = ROUND(r.price * (1 + v_price_adjustment / 100), 2),
        r.updated_at = CURRENT_TIMESTAMP
    WHERE r.hotel_id = p_hotel_id
    AND r.is_active = 1
    AND r.maintenance_status = 'Available';
    
    -- Return results
    SELECT v_occupancy_rate as occupancy_rate, 
           v_price_adjustment as price_adjustment,
           v_booked_rooms as booked_rooms,
           v_total_rooms as total_rooms;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateRoomPricesManual` (IN `p_hotel_id` INT, IN `p_percentage` DECIMAL(5,2))   BEGIN
    DECLARE v_affected_rows INT DEFAULT 0;
    DECLARE v_min_price DECIMAL(10,2);
    DECLARE v_max_price DECIMAL(10,2);
    
    -- Update prices
    UPDATE rooms
    SET price = ROUND(price * (1 + p_percentage / 100), 2),
        updated_at = CURRENT_TIMESTAMP
    WHERE hotel_id = p_hotel_id
    AND is_active = 1;
    
    SET v_affected_rows = ROW_COUNT();
    
    -- Get price range after update
    SELECT MIN(price), MAX(price)
    INTO v_min_price, v_max_price
    FROM rooms
    WHERE hotel_id = p_hotel_id
    AND is_active = 1;
    
    -- Return results
    SELECT v_affected_rows as rooms_updated,
           v_min_price as min_price,
           v_max_price as max_price,
           p_percentage as percentage_applied;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateAge` (`p_date_of_birth` DATE) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE v_age INT;
    
    IF p_date_of_birth IS NULL THEN
        RETURN NULL;
    END IF;
    
    SET v_age = TIMESTAMPDIFF(YEAR, p_date_of_birth, CURDATE());
    
    RETURN v_age;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateDynamicPrice` (`p_base_price` DECIMAL(10,2), `p_check_in_date` DATE, `p_room_type` VARCHAR(50)) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE v_season VARCHAR(20);
    DECLARE v_multiplier DECIMAL(5,2) DEFAULT 1.00;
    DECLARE v_days_advance INT;
    DECLARE v_final_price DECIMAL(10,2);
    
    -- Get season
    SET v_season = GetSeason(p_check_in_date);
    
    -- Apply seasonal multiplier
    CASE v_season
        WHEN 'Peak' THEN SET v_multiplier = 1.30;
        WHEN 'High' THEN SET v_multiplier = 1.15;
        WHEN 'Low' THEN SET v_multiplier = 0.85;
        ELSE SET v_multiplier = 1.00;
    END CASE;
    
    -- Calculate days in advance
    SET v_days_advance = DATEDIFF(p_check_in_date, CURDATE());
    
    -- Apply advance booking multiplier
    IF v_days_advance <= 7 THEN
        SET v_multiplier = v_multiplier * 1.15;  -- Last minute booking
    ELSEIF v_days_advance <= 14 THEN
        SET v_multiplier = v_multiplier * 1.10;
    ELSEIF v_days_advance >= 60 THEN
        SET v_multiplier = v_multiplier * 0.90;  -- Early bird discount
    END IF;
    
    -- Apply room type multiplier
    IF p_room_type = 'Suite' THEN
        SET v_multiplier = v_multiplier * 1.20;
    ELSEIF p_room_type = 'Deluxe' THEN
        SET v_multiplier = v_multiplier * 1.10;
    END IF;
    
    SET v_final_price = ROUND(p_base_price * v_multiplier, 2);
    
    RETURN v_final_price;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateGuestSatisfactionScore` (`p_hotel_id` INT) RETURNS DECIMAL(5,2) READS SQL DATA BEGIN
    DECLARE v_avg_rating DECIMAL(3,2);
    DECLARE v_total_reviews INT;
    DECLARE v_response_rate DECIMAL(5,2);
    DECLARE v_satisfaction_score DECIMAL(5,2);
    
    -- Get average rating and review count
    SELECT 
        COALESCE(AVG(rating), 0),
        COUNT(*)
    INTO v_avg_rating, v_total_reviews
    FROM reviews
    WHERE hotel_id = p_hotel_id
    AND is_approved = 1;
    
    -- Calculate response rate
    SELECT 
        CASE 
            WHEN COUNT(*) > 0 THEN 
                (COUNT(CASE WHEN admin_response IS NOT NULL THEN 1 END) / COUNT(*)) * 100
            ELSE 0 
        END
    INTO v_response_rate
    FROM reviews
    WHERE hotel_id = p_hotel_id
    AND is_approved = 1;
    
    -- Calculate satisfaction score
    SET v_satisfaction_score = (
        (v_avg_rating / 5.0) * 70 +
        (LEAST(v_total_reviews, 100) / 100) * 20 +
        (v_response_rate / 100) * 10
    );
    
    RETURN ROUND(v_satisfaction_score, 2);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetSeason` (`p_date` DATE) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC BEGIN
    DECLARE v_month INT;
    DECLARE v_day INT;
    DECLARE v_season VARCHAR(20);
    
    SET v_month = MONTH(p_date);
    SET v_day = DAY(p_date);
    
    -- Peak season: Winter holidays (Dec 15-Feb) and Summer (Jun-Aug)
    IF (v_month = 12 AND v_day >= 15) OR (v_month = 1) OR (v_month = 2) THEN
        SET v_season = 'Peak';
    ELSEIF (v_month = 6 OR v_month = 7 OR v_month = 8) THEN
        SET v_season = 'Peak';
    -- High season: Spring (Mar-May) and Fall (Sep-Nov)
    ELSEIF (v_month = 3 OR v_month = 4 OR v_month = 5) THEN
        SET v_season = 'High';
    ELSEIF (v_month = 9 OR v_month = 10 OR v_month = 11) THEN
        SET v_season = 'High';
    ELSE
        SET v_season = 'Normal';
    END IF;
    
    RETURN v_season;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('Super Admin','Admin','Manager') DEFAULT 'Admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`, `email`, `full_name`, `role`, `last_login`, `created_at`, `is_active`) VALUES
(1, 'admin', '1234', 'admin@smartstay.com', 'System Administrator', 'Super Admin', NULL, '2025-10-03 15:48:43', 1),
(2, 'manager', '1234', 'manager@smartstay.com', 'Hotel Manager', 'Manager', NULL, '2025-10-03 15:48:43', 1),
(3, 'support', '1234', 'support@smartstay.com', 'Support Admin', 'Admin', NULL, '2025-10-03 15:48:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `guest_id`, `room_id`, `check_in`, `check_out`, `adults`, `children`, `total_amount`, `discount_amount`, `tax_amount`, `final_amount`, `booking_status`, `payment_status`, `booking_source`, `special_requests`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(8, 1, 1, '2025-10-15', '2025-10-18', 2, 0, 450.00, 0.00, 81.00, 531.00, 'Confirmed', 'Paid', 'Website', 'Late check-in requested', NULL, '2025-10-10 08:22:00', '2025-10-03 15:48:43'),
(10, 2, 3, '2025-10-20', '2025-10-25', 2, 0, 1750.00, 0.00, 315.00, 2065.00, 'Confirmed', 'Paid', 'Website', 'Extra pillows please', NULL, '2025-10-18 05:15:00', '2025-10-03 15:48:43'),
(13, 5, 2, '2025-11-01', '2025-11-05', 2, 0, 880.00, 0.00, 158.40, 1038.40, 'Confirmed', 'Pending', 'Website', 'Non-smoking room', NULL, '2025-10-28 08:00:00', '2025-10-03 15:48:43'),
(16, 2, 8, '2025-10-05', '2025-10-08', 2, 0, 1260.00, 0.00, 226.80, 1486.80, 'Confirmed', 'Paid', 'Website', NULL, NULL, '2025-10-02 05:00:00', '2025-10-03 15:48:43');

--
-- Triggers `bookings`
--
DELIMITER $$
CREATE TRIGGER `calculate_booking_amounts` BEFORE INSERT ON `bookings` FOR EACH ROW BEGIN
    DECLARE v_tax_rate DECIMAL(5,2) DEFAULT 0.18;  -- 18% tax rate
    
    -- Calculate tax if not provided
    IF NEW.tax_amount = 0 OR NEW.tax_amount IS NULL THEN
        SET NEW.tax_amount = ROUND(NEW.total_amount * v_tax_rate, 2);
    END IF;
    
    -- Calculate final amount if not provided
    IF NEW.final_amount = 0 OR NEW.final_amount IS NULL THEN
        SET NEW.final_amount = ROUND(NEW.total_amount + NEW.tax_amount - NEW.discount_amount, 2);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calculate_loyalty_on_completion` AFTER UPDATE ON `bookings` FOR EACH ROW BEGIN
    IF NEW.booking_status = 'Completed' AND OLD.booking_status != 'Completed' THEN
        CALL CalculateLoyaltyPoints(NEW.booking_id);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_booking_changes` AFTER UPDATE ON `bookings` FOR EACH ROW BEGIN
    INSERT INTO system_logs (
        user_type,
        user_id,
        action,
        table_name,
        record_id,
        old_values,
        new_values
    ) VALUES (
        'System',
        NULL,
        'UPDATE',
        'bookings',
        NEW.booking_id,
        JSON_OBJECT(
            'booking_status', OLD.booking_status,
            'payment_status', OLD.payment_status,
            'total_amount', OLD.total_amount,
            'final_amount', OLD.final_amount
        ),
        JSON_OBJECT(
            'booking_status', NEW.booking_status,
            'payment_status', NEW.payment_status,
            'total_amount', NEW.total_amount,
            'final_amount', NEW.final_amount
        )
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validate_room_availability_insert` BEFORE INSERT ON `bookings` FOR EACH ROW BEGIN
    DECLARE v_conflict_count INT;
    
    SELECT COUNT(*) INTO v_conflict_count
    FROM bookings
    WHERE room_id = NEW.room_id
    AND booking_status IN ('Confirmed', 'Completed')
    AND NOT (NEW.check_out <= check_in OR NEW.check_in >= check_out);
    
    IF v_conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Room is not available for the selected dates';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `validate_room_availability_update` BEFORE UPDATE ON `bookings` FOR EACH ROW BEGIN
    DECLARE v_conflict_count INT;
    
    IF NEW.booking_status IN ('Confirmed', 'Completed') THEN
        SELECT COUNT(*) INTO v_conflict_count
        FROM bookings
        WHERE room_id = NEW.room_id
        AND booking_id != NEW.booking_id
        AND booking_status IN ('Confirmed', 'Completed')
        AND NOT (NEW.check_out <= check_in OR NEW.check_in >= check_out);
        
        IF v_conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Room is not available for the selected dates';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `hotel_id`, `event_name`, `description`, `event_date`, `start_time`, `end_time`, `venue`, `max_participants`, `current_participants`, `price`, `event_type`, `event_status`, `organizer_name`, `organizer_contact`, `requirements`, `created_at`, `updated_at`) VALUES
(1, 1, 'Business Leadership Summit', 'Annual conference for business leaders', '2025-03-15', '09:00:00', '17:00:00', 'Grand Ballroom', 200, 0, 450.00, 'Conference', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(2, 1, 'Summer Jazz Night', 'Live jazz performance with dinner', '2025-06-20', '19:00:00', '23:00:00', 'Rooftop Terrace', 150, 0, 120.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(3, 1, 'Wedding Reception', 'Elegant wedding celebration', '2025-04-10', '18:00:00', '23:30:00', 'Crystal Ballroom', 250, 0, 200.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(4, 1, 'Tech Innovation Workshop', 'Latest trends in technology', '2025-05-05', '10:00:00', '16:00:00', 'Conference Room A', 80, 0, 350.00, 'Workshop', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(5, 1, 'New Year Gala', 'Celebrate new year in style', '2025-12-31', '20:00:00', '02:00:00', 'Grand Ballroom', 300, 0, 180.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(6, 2, 'Beach Yoga Retreat', 'Weekend wellness and yoga', '2025-03-22', '07:00:00', '18:00:00', 'Beach Pavilion', 50, 0, 280.00, 'Workshop', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(7, 2, 'Seafood Festival', 'Culinary experience by the ocean', '2025-07-15', '12:00:00', '22:00:00', 'Ocean View Restaurant', 120, 0, 95.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(8, 2, 'Corporate Team Building', 'Beach activities for teams', '2025-04-18', '09:00:00', '17:00:00', 'Beach Activities Center', 60, 0, 420.00, 'Conference', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(9, 2, 'Sunset Wedding Ceremony', 'Romantic beachside wedding', '2025-05-30', '17:00:00', '22:00:00', 'Beach Garden', 180, 0, 250.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(10, 2, 'Summer Pool Party', 'DJ and poolside entertainment', '2025-08-12', '14:00:00', '20:00:00', 'Resort Pool', 200, 0, 75.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(11, 3, 'Mountain Photography Workshop', 'Capture stunning landscapes', '2025-04-08', '06:00:00', '18:00:00', 'Mountain Trail', 30, 0, 320.00, 'Workshop', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(12, 3, 'Ski Competition', 'Annual skiing championship', '2025-02-20', '08:00:00', '16:00:00', 'Ski Slopes', 100, 0, 180.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(13, 3, 'Winter Wonderland Gala', 'Festive celebration', '2025-12-20', '18:00:00', '23:00:00', 'Mountain View Hall', 150, 0, 160.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(14, 3, 'Hiking Adventure Weekend', 'Guided mountain hikes', '2025-06-01', '07:00:00', '19:00:00', 'Trail Center', 40, 0, 250.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(15, 3, 'Mountain Lodge Wedding', 'Rustic mountain wedding', '2025-09-15', '15:00:00', '22:00:00', 'Lodge Hall', 120, 0, 280.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(16, 4, 'Art Gallery Opening', 'Contemporary art exhibition', '2025-03-10', '18:00:00', '22:00:00', 'Gallery Space', 100, 0, 65.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(17, 4, 'Fashion Show Extravaganza', 'Latest fashion trends', '2025-05-25', '19:00:00', '22:30:00', 'Runway Hall', 200, 0, 150.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(18, 4, 'Marketing Strategy Conference', 'Digital marketing insights', '2025-04-12', '09:00:00', '17:00:00', 'Conference Hall', 150, 0, 380.00, 'Conference', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(19, 4, 'Rooftop Cocktail Mixer', 'Networking event', '2025-06-08', '18:00:00', '21:00:00', 'Rooftop Bar', 80, 0, 95.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(20, 4, 'Boutique Wedding Reception', 'Intimate wedding celebration', '2025-07-22', '17:00:00', '23:00:00', 'Boutique Ballroom', 100, 0, 220.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(21, 5, 'Historical Tea Party', 'Victorian-era themed tea', '2025-04-20', '14:00:00', '17:00:00', 'Tea Room', 60, 0, 85.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(22, 5, 'Book Club Literary Evening', 'Author meet and greet', '2025-05-15', '18:00:00', '21:00:00', 'Library', 40, 0, 55.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(23, 5, 'Classic Music Recital', 'Piano and violin performance', '2025-06-10', '19:00:00', '21:30:00', 'Music Hall', 90, 0, 75.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(24, 5, 'Heritage Wedding', 'Traditional wedding ceremony', '2025-08-05', '16:00:00', '22:00:00', 'Heritage Garden', 150, 0, 240.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(25, 5, 'Historical Architecture Tour', 'Guided building tour', '2025-03-28', '10:00:00', '14:00:00', 'Main Building', 25, 0, 45.00, 'Workshop', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(26, 6, 'Business Breakfast Meeting', 'Networking breakfast', '2025-03-18', '07:30:00', '09:30:00', 'Conference Room', 40, 0, 65.00, 'Meeting', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(27, 6, 'Express Training Workshop', 'Productivity and time management', '2025-04-25', '09:00:00', '13:00:00', 'Training Room', 30, 0, 180.00, 'Workshop', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(28, 6, 'City Business Mixer', 'Professional networking', '2025-05-20', '17:00:00', '20:00:00', 'Coffee Lounge', 50, 0, 45.00, 'Meeting', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(29, 6, 'Small Wedding Ceremony', 'Intimate wedding event', '2025-07-15', '15:00:00', '20:00:00', 'Event Space', 60, 0, 150.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(30, 6, 'Holiday Party', 'End of year celebration', '2025-12-15', '18:00:00', '22:00:00', 'Main Hall', 80, 0, 85.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(31, 7, 'Luxury Wine Tasting', 'Premium wines with harbor views', '2025-04-05', '18:00:00', '21:00:00', 'Penthouse Lounge', 40, 0, 180.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(32, 7, 'Executive Leadership Retreat', 'C-suite strategy session', '2025-05-12', '08:00:00', '18:00:00', 'Executive Suite', 25, 0, 850.00, 'Conference', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(33, 7, 'Harbor View Wedding', 'Luxury waterfront wedding', '2025-06-28', '16:00:00', '23:00:00', 'Harbor Ballroom', 200, 0, 380.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(34, 7, 'Gourmet Culinary Workshop', 'Chef-led cooking class', '2025-07-10', '15:00:00', '19:00:00', 'Gourmet Kitchen', 20, 0, 280.00, 'Workshop', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(35, 7, 'New Year Harbor Celebration', 'Fireworks and champagne', '2025-12-31', '21:00:00', '01:00:00', 'Harbor Deck', 150, 0, 250.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(36, 8, 'Forest Meditation Retreat', 'Mindfulness in nature', '2025-03-25', '08:00:00', '17:00:00', 'Forest Pavilion', 30, 0, 220.00, 'Workshop', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(37, 8, 'Wildlife Photography Tour', 'Capture forest wildlife', '2025-05-18', '06:00:00', '18:00:00', 'Nature Trail', 15, 0, 340.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(38, 8, 'Organic Farm-to-Table Dinner', 'Sustainable dining experience', '2025-06-22', '18:00:00', '22:00:00', 'Forest Restaurant', 50, 0, 160.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(39, 8, 'Nature Wedding Ceremony', 'Eco-friendly forest wedding', '2025-08-20', '15:00:00', '21:00:00', 'Forest Garden', 80, 0, 290.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(40, 8, 'Autumn Harvest Festival', 'Seasonal celebration', '2025-10-15', '12:00:00', '20:00:00', 'Main Lodge', 100, 0, 95.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(41, 9, 'Contemporary Art Exhibition', 'Modern art showcase', '2025-03-30', '17:00:00', '21:00:00', 'Art Gallery', 120, 0, 75.00, 'Other', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(42, 9, 'Creative Industries Summit', 'Design and innovation conference', '2025-04-28', '09:00:00', '18:00:00', 'Conference Center', 180, 0, 420.00, 'Conference', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(43, 9, 'Artistic Wedding Celebration', 'Creative themed wedding', '2025-06-15', '17:00:00', '23:00:00', 'Gallery Ballroom', 150, 0, 320.00, 'Wedding', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(44, 9, 'Jazz and Art Evening', 'Live music and art viewing', '2025-07-25', '19:00:00', '23:00:00', 'Rooftop Gallery', 100, 0, 110.00, 'Party', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(45, 9, 'Photography Workshop', 'Urban photography techniques', '2025-05-08', '10:00:00', '16:00:00', 'Studio Space', 25, 0, 260.00, 'Workshop', 'Upcoming', NULL, NULL, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `event_bookings`
--

CREATE TABLE `event_bookings` (
  `event_booking_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `participants` int(11) DEFAULT 1,
  `amount_paid` decimal(10,2) NOT NULL,
  `booking_status` enum('Confirmed','Cancelled','Attended','No-Show') DEFAULT 'Confirmed',
  `special_requirements` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `event_bookings`
--
DELIMITER $$
CREATE TRIGGER `update_event_participants_delete` AFTER DELETE ON `event_bookings` FOR EACH ROW BEGIN
    UPDATE events
    SET current_participants = GREATEST(0, current_participants - OLD.participants)
    WHERE event_id = OLD.event_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_event_participants_insert` AFTER INSERT ON `event_bookings` FOR EACH ROW BEGIN
    UPDATE events
    SET current_participants = current_participants + NEW.participants
    WHERE event_id = NEW.event_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_event_participants_update` AFTER UPDATE ON `event_bookings` FOR EACH ROW BEGIN
    IF NEW.booking_status = 'Cancelled' AND OLD.booking_status != 'Cancelled' THEN
        UPDATE events
        SET current_participants = GREATEST(0, current_participants - NEW.participants)
        WHERE event_id = NEW.event_id;
    ELSEIF OLD.booking_status = 'Cancelled' AND NEW.booking_status != 'Cancelled' THEN
        UPDATE events
        SET current_participants = current_participants + NEW.participants
        WHERE event_id = NEW.event_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `guest_id` int(11) NOT NULL,
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
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`guest_id`, `name`, `email`, `password`, `phone`, `date_of_birth`, `gender`, `nationality`, `address`, `loyalty_points`, `membership_level`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'John Smith', 'john.smith@email.com', '1234', '+1-555-0101', '1985-03-15', 'Male', 'USA', NULL, 2500, 'Gold', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(2, 'Emma Johnson', 'emma.j@email.com', '1234', '+1-555-0102', '1990-07-22', 'Female', 'USA', NULL, 5200, 'Platinum', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(3, 'Michael Chen', 'mchen@email.com', '1234', '+1-555-0103', '1988-11-10', 'Male', 'China', NULL, 850, 'Silver', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(4, 'Sarah Williams', 'swilliams@email.com', '1234', '+1-555-0104', '1992-05-18', 'Female', 'UK', NULL, 350, 'Bronze', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(5, 'David Martinez', 'dmartinez@email.com', '1234', '+1-555-0105', '1987-09-30', 'Male', 'Spain', NULL, 1200, 'Silver', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(6, 'Lisa Anderson', 'landerson@email.com', '1234', '+1-555-0106', '1995-01-25', 'Female', 'Canada', NULL, 180, 'Bronze', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(7, 'Robert Taylor', 'rtaylor@email.com', '1234', '+1-555-0107', '1983-12-05', 'Male', 'USA', NULL, 6500, 'Platinum', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(8, 'Jennifer Lee', 'jlee@email.com', '1234', '+1-555-0108', '1991-08-14', 'Female', 'South Korea', NULL, 420, 'Bronze', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(9, 'James Brown', 'jbrown@email.com', '1234', '+1-555-0109', '1989-04-20', 'Male', 'Australia', NULL, 3100, 'Gold', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(10, 'Maria Garcia', 'mgarcia@email.com', '1234', '+1-555-0110', '1994-06-08', 'Female', 'Mexico', NULL, 750, 'Silver', '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `hotel_id` int(11) NOT NULL,
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
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `check_in_time` time DEFAULT '14:00:00',
  `check_out_time` time DEFAULT '11:00:00',
  `established_year` int(11) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`hotel_id`, `hotel_name`, `email`, `password`, `description`, `address`, `city`, `state`, `country`, `postal_code`, `phone`, `star_rating`, `total_rooms`, `amenities`, `check_in_time`, `check_out_time`, `established_year`, `license_number`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'Grand Plaza Hotel', 'contact@grandplaza.com', '1234', 'Luxury hotel in downtown', '123 Main St', 'New York', 'NY', 'USA', '10001', '+1-212-555-0100', 5.0, 10, '[\"Pool\", \"Spa\", \"Gym\", \"Restaurant\", \"Bar\", \"Conference Rooms\"]', '14:00:00', '11:00:00', 1990, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(2, 'Seaside Resort', 'info@seasideresort.com', '1234', 'Beautiful beachfront resort', '456 Ocean Blvd', 'Miami', 'FL', 'USA', '33139', '+1-305-555-0200', 4.5, 10, '[\"Beach Access\", \"Pool\", \"Water Sports\", \"Restaurant\", \"Spa\"]', '14:00:00', '11:00:00', 1995, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(3, 'Mountain View Lodge', 'reservations@mountainview.com', '1234', 'Cozy mountain retreat', '789 Summit Rd', 'Denver', 'CO', 'USA', '80202', '+1-303-555-0300', 4.0, 10, '[\"Skiing\", \"Hiking\", \"Restaurant\", \"Fireplace\", \"Spa\"]', '14:00:00', '11:00:00', 2000, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(4, 'Urban Boutique Hotel', 'hello@urbanboutique.com', '1234', 'Modern boutique hotel', '321 Fashion Ave', 'Los Angeles', 'CA', 'USA', '90028', '+1-213-555-0400', 4.5, 10, '[\"Rooftop Bar\", \"Pool\", \"Gym\", \"Restaurant\", \"Art Gallery\"]', '14:00:00', '11:00:00', 2010, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(5, 'Historic Inn', 'bookings@historicinn.com', '1234', 'Charming historic property', '567 Heritage Ln', 'Boston', 'MA', 'USA', '02108', '+1-617-555-0500', 4.0, 10, '[\"Library\", \"Garden\", \"Restaurant\", \"Tea Room\"]', '14:00:00', '11:00:00', 1885, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(6, 'City Center Express', 'info@citycenterexpress.com', '1234', 'Convenient city center location', '100 Business Plaza', 'Chicago', 'IL', 'USA', '60601', '+1-312-555-0600', 3.5, 10, '[\"WiFi\", \"Business Center\", \"Coffee Shop\", \"Parking\"]', '14:00:00', '11:00:00', 2015, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(7, 'Harborfront Luxury Suites', 'stay@harborfront.com', '1234', 'Luxury suites with harbor views', '200 Marina Way', 'Seattle', 'WA', 'USA', '98101', '+1-206-555-0700', 5.0, 10, '[\"Harbor View\", \"Spa\", \"Fine Dining\", \"Concierge\", \"Valet\"]', '14:00:00', '11:00:00', 2018, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(8, 'Forest Retreat Villas', 'welcome@forestretreat.com', '1234', 'Peaceful forest getaway', '300 Woodland Path', 'Portland', 'OR', 'USA', '97201', '+1-503-555-0800', 4.5, 10, '[\"Nature Trails\", \"Yoga\", \"Organic Restaurant\", \"Spa\", \"Wildlife Tours\"]', '14:00:00', '11:00:00', 2020, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1),
(9, 'Metropolitan Art Hotel', 'contact@metroarthotel.com', '1234', 'Contemporary art-themed hotel', '400 Gallery Street', 'San Francisco', 'CA', 'USA', '94102', '+1-415-555-0900', 4.5, 10, '[\"Art Exhibitions\", \"Rooftop Bar\", \"Restaurant\", \"Gym\", \"Library\"]', '14:00:00', '11:00:00', 2017, NULL, '2025-10-03 15:48:43', '2025-10-03 15:48:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_method` enum('Cash','Card','Online','Bank Transfer') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('Success','Failed','Pending','Refunded') DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `gateway_response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `type_id` int(11) NOT NULL,
  `floor_number` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `area_sqft` int(11) DEFAULT NULL,
  `max_occupancy` int(11) DEFAULT 2,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `maintenance_status` enum('Available','Under Maintenance','Out of Service') DEFAULT 'Available',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `hotel_id`, `room_number`, `type_id`, `floor_number`, `price`, `area_sqft`, `max_occupancy`, `amenities`, `maintenance_status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, '101', 1, 1, 150.00, 300, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(2, 1, '102', 2, 1, 220.00, 450, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(3, 1, '201', 3, 2, 350.00, 650, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(4, 1, '202', 1, 2, 150.00, 300, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(5, 1, '301', 2, 3, 220.00, 450, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(6, 1, '302', 3, 3, 350.00, 650, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(7, 1, '401', 4, 4, 280.00, 550, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(8, 1, '402', 5, 4, 420.00, 750, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(9, 1, '501', 3, 5, 380.00, 700, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(10, 1, '502', 5, 5, 450.00, 800, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(11, 2, '101', 1, 1, 180.00, 320, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(12, 2, '102', 2, 1, 250.00, 480, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(13, 2, '201', 3, 2, 400.00, 680, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(14, 2, '202', 1, 2, 180.00, 320, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(15, 2, '301', 2, 3, 250.00, 480, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(16, 2, '302', 3, 3, 400.00, 680, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(17, 2, '401', 4, 4, 320.00, 580, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(18, 2, '402', 3, 4, 420.00, 700, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(19, 2, '501', 3, 5, 450.00, 750, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(20, 2, '502', 5, 5, 520.00, 850, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(21, 3, '101', 1, 1, 140.00, 280, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(22, 3, '102', 2, 1, 210.00, 420, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(23, 3, '201', 3, 2, 320.00, 620, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(24, 3, '202', 1, 2, 140.00, 280, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(25, 3, '301', 2, 3, 210.00, 420, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(26, 3, '302', 3, 3, 320.00, 620, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(27, 3, '401', 4, 4, 270.00, 540, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(28, 3, '402', 2, 4, 220.00, 440, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(29, 3, '501', 3, 5, 350.00, 680, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(30, 3, '502', 5, 5, 410.00, 780, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(31, 4, '101', 1, 1, 190.00, 310, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(32, 4, '102', 2, 1, 270.00, 470, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(33, 4, '201', 3, 2, 410.00, 670, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(34, 4, '202', 1, 2, 190.00, 310, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(35, 4, '301', 2, 3, 270.00, 470, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(36, 4, '302', 3, 3, 410.00, 670, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(37, 4, '401', 4, 4, 340.00, 590, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(38, 4, '402', 5, 4, 480.00, 790, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(39, 4, '501', 3, 5, 440.00, 720, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(40, 4, '502', 5, 5, 510.00, 830, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(41, 5, '101', 1, 1, 130.00, 290, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(42, 5, '102', 2, 1, 200.00, 430, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(43, 5, '201', 3, 2, 310.00, 630, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(44, 5, '202', 1, 2, 130.00, 290, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(45, 5, '301', 2, 3, 200.00, 430, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(46, 5, '302', 3, 3, 310.00, 630, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(47, 5, '401', 4, 4, 260.00, 530, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(48, 5, '402', 2, 4, 210.00, 450, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(49, 5, '501', 3, 5, 340.00, 670, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(50, 5, '502', 5, 5, 400.00, 770, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(51, 6, '101', 1, 1, 120.00, 280, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(52, 6, '102', 1, 1, 120.00, 280, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(53, 6, '201', 2, 2, 180.00, 400, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(54, 6, '202', 2, 2, 180.00, 400, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(55, 6, '301', 3, 3, 280.00, 600, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(56, 6, '302', 1, 3, 120.00, 280, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(57, 6, '401', 2, 4, 180.00, 400, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(58, 6, '402', 3, 4, 280.00, 600, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(59, 6, '501', 4, 5, 240.00, 520, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(60, 6, '502', 2, 5, 180.00, 400, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(61, 7, '101', 2, 1, 280.00, 490, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(62, 7, '102', 3, 1, 420.00, 690, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(63, 7, '201', 3, 2, 450.00, 720, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(64, 7, '202', 5, 2, 530.00, 840, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(65, 7, '301', 3, 3, 450.00, 720, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(66, 7, '302', 5, 3, 530.00, 840, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(67, 7, '401', 3, 4, 480.00, 750, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(68, 7, '402', 5, 4, 560.00, 870, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(69, 7, '501', 3, 5, 520.00, 800, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(70, 7, '502', 5, 5, 600.00, 900, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(71, 8, 'V1', 4, 1, 340.00, 650, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(72, 8, 'V2', 4, 1, 340.00, 650, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(73, 8, 'V3', 3, 1, 390.00, 700, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(74, 8, 'V4', 3, 1, 390.00, 700, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(75, 8, 'V5', 4, 2, 360.00, 680, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(76, 8, 'V6', 4, 2, 360.00, 680, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(77, 8, 'V7', 3, 2, 410.00, 730, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(78, 8, 'V8', 3, 2, 410.00, 730, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(79, 8, 'V9', 5, 3, 490.00, 820, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(80, 8, 'V10', 5, 3, 490.00, 820, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(81, 9, '101', 1, 1, 170.00, 310, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(82, 9, '102', 2, 1, 240.00, 460, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(83, 9, '201', 3, 2, 380.00, 660, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(84, 9, '202', 1, 2, 170.00, 310, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(85, 9, '301', 2, 3, 240.00, 460, 3, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(86, 9, '302', 3, 3, 380.00, 660, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(87, 9, '401', 4, 4, 310.00, 570, 5, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(88, 9, '402', 5, 4, 460.00, 780, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(89, 9, '501', 3, 5, 420.00, 710, 4, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43'),
(90, 9, '502', 5, 5, 500.00, 820, 2, NULL, 'Available', 1, '2025-10-03 15:48:43', '2025-10-03 15:48:43');

--
-- Triggers `rooms`
--
DELIMITER $$
CREATE TRIGGER `update_hotel_total_rooms_delete` AFTER DELETE ON `rooms` FOR EACH ROW BEGIN
    UPDATE hotels
    SET total_rooms = GREATEST(0, total_rooms - 1)
    WHERE hotel_id = OLD.hotel_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_hotel_total_rooms_insert` AFTER INSERT ON `rooms` FOR EACH ROW BEGIN
    UPDATE hotels
    SET total_rooms = total_rooms + 1
    WHERE hotel_id = NEW.hotel_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `room_types`
--

CREATE TABLE `room_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `max_occupancy` int(11) DEFAULT 2,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`type_id`, `type_name`, `description`, `max_occupancy`, `amenities`, `created_at`) VALUES
(1, 'Standard', 'Comfortable room with basic amenities', 2, '[\"TV\", \"WiFi\", \"Air Conditioning\"]', '2025-10-03 15:48:43'),
(2, 'Deluxe', 'Spacious room with premium amenities', 3, '[\"TV\", \"WiFi\", \"Air Conditioning\", \"Mini Bar\", \"City View\"]', '2025-10-03 15:48:43'),
(3, 'Suite', 'Luxurious suite with separate living area', 4, '[\"TV\", \"WiFi\", \"Air Conditioning\", \"Mini Bar\", \"Ocean View\", \"Jacuzzi\"]', '2025-10-03 15:48:43'),
(4, 'Family Room', 'Large room suitable for families', 5, '[\"TV\", \"WiFi\", \"Air Conditioning\", \"Kitchenette\", \"Extra Beds\"]', '2025-10-03 15:48:43'),
(5, 'Executive Suite', 'Premium suite for business travelers', 2, '[\"TV\", \"WiFi\", \"Air Conditioning\", \"Workstation\", \"Meeting Room Access\", \"Lounge Access\"]', '2025-10-03 15:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `service_type` enum('Spa','Restaurant','Room Service','Transport','Laundry','Other') DEFAULT 'Other',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `user_type` enum('Guest','Hotel','Admin','System') DEFAULT 'System',
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_guest_booking_history`
-- (See below for the actual view)
--
CREATE TABLE `vw_guest_booking_history` (
`guest_id` int(11)
,`guest_name` varchar(100)
,`email` varchar(100)
,`membership_level` enum('Bronze','Silver','Gold','Platinum')
,`loyalty_points` int(11)
,`booking_id` int(11)
,`hotel_name` varchar(100)
,`hotel_city` varchar(50)
,`room_number` varchar(20)
,`room_type` varchar(50)
,`check_in` date
,`check_out` date
,`nights` int(7)
,`adults` int(11)
,`children` int(11)
,`final_amount` decimal(10,2)
,`booking_status` enum('Confirmed','Cancelled','Completed','No-Show')
,`payment_status` enum('Pending','Paid','Partial','Refunded')
,`booking_date` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_guest_loyalty_tiers`
-- (See below for the actual view)
--
CREATE TABLE `vw_guest_loyalty_tiers` (
`membership_level` enum('Bronze','Silver','Gold','Platinum')
,`total_guests` bigint(21)
,`avg_loyalty_points` decimal(11,0)
,`total_bookings` bigint(21)
,`total_revenue` decimal(32,2)
,`avg_booking_value` decimal(14,6)
,`avg_rating` decimal(6,5)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_hotel_occupancy`
-- (See below for the actual view)
--
CREATE TABLE `vw_hotel_occupancy` (
`hotel_id` int(11)
,`hotel_name` varchar(100)
,`city` varchar(50)
,`total_rooms` int(11)
,`occupied_rooms` bigint(21)
,`occupancy_rate` decimal(26,2)
,`check_ins_today` bigint(21)
,`check_outs_today` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_hotel_revenue_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_hotel_revenue_summary` (
`hotel_id` int(11)
,`hotel_name` varchar(100)
,`city` varchar(50)
,`total_bookings` bigint(21)
,`completed_bookings` bigint(21)
,`cancelled_bookings` bigint(21)
,`total_revenue` decimal(32,2)
,`avg_booking_value` decimal(14,6)
,`revenue_received` decimal(32,2)
,`unique_guests` bigint(21)
,`avg_rating` decimal(6,5)
,`total_reviews` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_room_availability`
-- (See below for the actual view)
--
CREATE TABLE `vw_room_availability` (
`hotel_id` int(11)
,`hotel_name` varchar(100)
,`room_id` int(11)
,`room_number` varchar(20)
,`room_type` varchar(50)
,`floor_number` int(11)
,`price` decimal(10,2)
,`max_occupancy` int(11)
,`maintenance_status` enum('Available','Under Maintenance','Out of Service')
,`current_status` varchar(11)
,`next_available_date` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_upcoming_events`
-- (See below for the actual view)
--
CREATE TABLE `vw_upcoming_events` (
`event_id` int(11)
,`hotel_name` varchar(100)
,`city` varchar(50)
,`event_name` varchar(100)
,`description` text
,`event_date` date
,`start_time` time
,`end_time` time
,`venue` varchar(100)
,`event_type` enum('Conference','Wedding','Meeting','Party','Workshop','Other')
,`max_participants` int(11)
,`current_participants` int(11)
,`available_spots` bigint(12)
,`fill_rate` decimal(16,2)
,`price` decimal(10,2)
,`event_status` enum('Upcoming','Active','Completed','Cancelled')
,`total_bookings` bigint(21)
,`total_registered_participants` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_guest_booking_history`
--
DROP TABLE IF EXISTS `vw_guest_booking_history`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_guest_booking_history`  AS SELECT `g`.`guest_id` AS `guest_id`, `g`.`name` AS `guest_name`, `g`.`email` AS `email`, `g`.`membership_level` AS `membership_level`, `g`.`loyalty_points` AS `loyalty_points`, `b`.`booking_id` AS `booking_id`, `h`.`hotel_name` AS `hotel_name`, `h`.`city` AS `hotel_city`, `r`.`room_number` AS `room_number`, `rt`.`type_name` AS `room_type`, `b`.`check_in` AS `check_in`, `b`.`check_out` AS `check_out`, to_days(`b`.`check_out`) - to_days(`b`.`check_in`) AS `nights`, `b`.`adults` AS `adults`, `b`.`children` AS `children`, `b`.`final_amount` AS `final_amount`, `b`.`booking_status` AS `booking_status`, `b`.`payment_status` AS `payment_status`, `b`.`created_at` AS `booking_date` FROM ((((`guests` `g` left join `bookings` `b` on(`g`.`guest_id` = `b`.`guest_id`)) left join `rooms` `r` on(`b`.`room_id` = `r`.`room_id`)) left join `hotels` `h` on(`r`.`hotel_id` = `h`.`hotel_id`)) left join `room_types` `rt` on(`r`.`type_id` = `rt`.`type_id`)) ORDER BY `b`.`created_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_guest_loyalty_tiers`
--
DROP TABLE IF EXISTS `vw_guest_loyalty_tiers`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_guest_loyalty_tiers`  AS SELECT `g`.`membership_level` AS `membership_level`, count(distinct `g`.`guest_id`) AS `total_guests`, round(avg(`g`.`loyalty_points`),0) AS `avg_loyalty_points`, count(distinct `b`.`booking_id`) AS `total_bookings`, coalesce(sum(`b`.`final_amount`),0) AS `total_revenue`, coalesce(avg(`b`.`final_amount`),0) AS `avg_booking_value`, coalesce(avg(`rv`.`rating`),0) AS `avg_rating` FROM ((`guests` `g` left join `bookings` `b` on(`g`.`guest_id` = `b`.`guest_id` and `b`.`booking_status` = 'Completed')) left join `reviews` `rv` on(`g`.`guest_id` = `rv`.`guest_id`)) WHERE `g`.`is_active` = 1 GROUP BY `g`.`membership_level` ORDER BY CASE `g`.`membership_level` WHEN 'Platinum' THEN 1 WHEN 'Gold' THEN 2 WHEN 'Silver' THEN 3 WHEN 'Bronze' THEN 4 END ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_hotel_occupancy`
--
DROP TABLE IF EXISTS `vw_hotel_occupancy`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_hotel_occupancy`  AS SELECT `h`.`hotel_id` AS `hotel_id`, `h`.`hotel_name` AS `hotel_name`, `h`.`city` AS `city`, `h`.`total_rooms` AS `total_rooms`, count(distinct case when `b`.`booking_status` = 'Confirmed' and curdate() between `b`.`check_in` and `b`.`check_out` then `b`.`room_id` end) AS `occupied_rooms`, round(count(distinct case when `b`.`booking_status` = 'Confirmed' and curdate() between `b`.`check_in` and `b`.`check_out` then `b`.`room_id` end) / `h`.`total_rooms` * 100,2) AS `occupancy_rate`, count(distinct case when `b`.`booking_status` = 'Confirmed' and `b`.`check_in` = curdate() then `b`.`booking_id` end) AS `check_ins_today`, count(distinct case when `b`.`booking_status` = 'Confirmed' and `b`.`check_out` = curdate() then `b`.`booking_id` end) AS `check_outs_today` FROM ((`hotels` `h` left join `rooms` `r` on(`h`.`hotel_id` = `r`.`hotel_id`)) left join `bookings` `b` on(`r`.`room_id` = `b`.`room_id`)) WHERE `h`.`is_active` = 1 GROUP BY `h`.`hotel_id`, `h`.`hotel_name`, `h`.`city`, `h`.`total_rooms` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_hotel_revenue_summary`
--
DROP TABLE IF EXISTS `vw_hotel_revenue_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_hotel_revenue_summary`  AS SELECT `h`.`hotel_id` AS `hotel_id`, `h`.`hotel_name` AS `hotel_name`, `h`.`city` AS `city`, count(distinct `b`.`booking_id`) AS `total_bookings`, count(distinct case when `b`.`booking_status` = 'Completed' then `b`.`booking_id` end) AS `completed_bookings`, count(distinct case when `b`.`booking_status` = 'Cancelled' then `b`.`booking_id` end) AS `cancelled_bookings`, coalesce(sum(case when `b`.`booking_status` = 'Completed' then `b`.`final_amount` else 0 end),0) AS `total_revenue`, coalesce(avg(case when `b`.`booking_status` = 'Completed' then `b`.`final_amount` end),0) AS `avg_booking_value`, coalesce(sum(case when `b`.`payment_status` = 'Paid' then `b`.`final_amount` else 0 end),0) AS `revenue_received`, count(distinct `b`.`guest_id`) AS `unique_guests`, coalesce(avg(`rv`.`rating`),0) AS `avg_rating`, count(distinct `rv`.`review_id`) AS `total_reviews` FROM (((`hotels` `h` left join `rooms` `r` on(`h`.`hotel_id` = `r`.`hotel_id`)) left join `bookings` `b` on(`r`.`room_id` = `b`.`room_id`)) left join `reviews` `rv` on(`h`.`hotel_id` = `rv`.`hotel_id`)) WHERE `h`.`is_active` = 1 GROUP BY `h`.`hotel_id`, `h`.`hotel_name`, `h`.`city` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_room_availability`
--
DROP TABLE IF EXISTS `vw_room_availability`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_room_availability`  AS SELECT `h`.`hotel_id` AS `hotel_id`, `h`.`hotel_name` AS `hotel_name`, `r`.`room_id` AS `room_id`, `r`.`room_number` AS `room_number`, `rt`.`type_name` AS `room_type`, `r`.`floor_number` AS `floor_number`, `r`.`price` AS `price`, `r`.`max_occupancy` AS `max_occupancy`, `r`.`maintenance_status` AS `maintenance_status`, CASE WHEN exists(select 1 from `bookings` `b` where `b`.`room_id` = `r`.`room_id` AND `b`.`booking_status` = 'Confirmed' AND curdate() between `b`.`check_in` and `b`.`check_out` limit 1) THEN 'Occupied' WHEN `r`.`maintenance_status` <> 'Available' THEN 'Maintenance' ELSE 'Available' END AS `current_status`, (select min(`b`.`check_out`) from `bookings` `b` where `b`.`room_id` = `r`.`room_id` and `b`.`booking_status` = 'Confirmed' and `b`.`check_out` > curdate()) AS `next_available_date` FROM ((`rooms` `r` join `hotels` `h` on(`r`.`hotel_id` = `h`.`hotel_id`)) join `room_types` `rt` on(`r`.`type_id` = `rt`.`type_id`)) WHERE `r`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `vw_upcoming_events`
--
DROP TABLE IF EXISTS `vw_upcoming_events`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_upcoming_events`  AS SELECT `e`.`event_id` AS `event_id`, `h`.`hotel_name` AS `hotel_name`, `h`.`city` AS `city`, `e`.`event_name` AS `event_name`, `e`.`description` AS `description`, `e`.`event_date` AS `event_date`, `e`.`start_time` AS `start_time`, `e`.`end_time` AS `end_time`, `e`.`venue` AS `venue`, `e`.`event_type` AS `event_type`, `e`.`max_participants` AS `max_participants`, `e`.`current_participants` AS `current_participants`, `e`.`max_participants`- `e`.`current_participants` AS `available_spots`, round(`e`.`current_participants` / `e`.`max_participants` * 100,2) AS `fill_rate`, `e`.`price` AS `price`, `e`.`event_status` AS `event_status`, count(`eb`.`event_booking_id`) AS `total_bookings`, sum(`eb`.`participants`) AS `total_registered_participants` FROM ((`events` `e` join `hotels` `h` on(`e`.`hotel_id` = `h`.`hotel_id`)) left join `event_bookings` `eb` on(`e`.`event_id` = `eb`.`event_id` and `eb`.`booking_status` = 'Confirmed')) WHERE `e`.`event_status` = 'Upcoming' AND `e`.`event_date` >= curdate() GROUP BY `e`.`event_id`, `h`.`hotel_name`, `h`.`city`, `e`.`event_name`, `e`.`description`, `e`.`event_date`, `e`.`start_time`, `e`.`end_time`, `e`.`venue`, `e`.`event_type`, `e`.`max_participants`, `e`.`current_participants`, `e`.`price`, `e`.`event_status` ORDER BY `e`.`event_date` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_admin_email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_booking_guest` (`guest_id`),
  ADD KEY `idx_booking_room` (`room_id`),
  ADD KEY `idx_booking_dates` (`check_in`,`check_out`),
  ADD KEY `idx_booking_status` (`booking_status`),
  ADD KEY `idx_booking_date_status` (`check_in`,`check_out`,`booking_status`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_event_hotel` (`hotel_id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_event_status` (`event_status`),
  ADD KEY `idx_event_date_status` (`event_date`,`event_status`,`hotel_id`);
ALTER TABLE `events` ADD FULLTEXT KEY `ft_event_search` (`event_name`,`description`,`venue`);

--
-- Indexes for table `event_bookings`
--
ALTER TABLE `event_bookings`
  ADD PRIMARY KEY (`event_booking_id`),
  ADD UNIQUE KEY `unique_event_guest` (`event_id`,`guest_id`),
  ADD KEY `idx_event_booking_guest` (`guest_id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`guest_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_guest_email` (`email`),
  ADD KEY `idx_membership_level` (`membership_level`),
  ADD KEY `idx_guest_membership_active` (`membership_level`,`is_active`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`hotel_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_hotel_city` (`city`),
  ADD KEY `idx_star_rating` (`star_rating`);
ALTER TABLE `hotels` ADD FULLTEXT KEY `ft_hotel_search` (`hotel_name`,`description`,`city`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payment_booking` (`booking_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_payment_date_status` (`payment_date`,`payment_status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_review_hotel` (`hotel_id`),
  ADD KEY `idx_review_guest` (`guest_id`),
  ADD KEY `idx_review_booking` (`booking_id`),
  ADD KEY `idx_review_hotel_approved` (`hotel_id`,`is_approved`,`created_at`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `unique_room` (`hotel_id`,`room_number`),
  ADD KEY `idx_room_hotel` (`hotel_id`),
  ADD KEY `idx_room_type` (`type_id`),
  ADD KEY `idx_room_price` (`price`),
  ADD KEY `idx_room_hotel_status` (`hotel_id`,`maintenance_status`,`is_active`);

--
-- Indexes for table `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);
ALTER TABLE `room_types` ADD FULLTEXT KEY `ft_room_type_search` (`type_name`,`description`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `idx_service_hotel` (`hotel_id`);
ALTER TABLE `services` ADD FULLTEXT KEY `ft_service_search` (`service_name`,`description`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table` (`table_name`),
  ADD KEY `idx_created` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `event_bookings`
--
ALTER TABLE `event_bookings`
  MODIFY `event_booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `guest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `hotel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `room_types`
--
ALTER TABLE `room_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_guest` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`guest_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_event_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_bookings`
--
ALTER TABLE `event_bookings`
  ADD CONSTRAINT `fk_event_booking_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_event_booking_guest` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`guest_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_review_guest` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`guest_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_room_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_room_type` FOREIGN KEY (`type_id`) REFERENCES `room_types` (`type_id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_service_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
