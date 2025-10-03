-- ============================================================================
-- SMARTSTAY DATABASE STORED PROCEDURES
-- ============================================================================
-- File: 02_procedures.sql
-- Purpose: Business logic implementation for hotel management
-- Run this file after 01_schema.sql
-- ============================================================================

USE `smart_stay`;

DELIMITER $$

-- ============================================================================
-- PROCEDURE: CalculateLoyaltyPoints
-- Description: Calculate and award loyalty points based on booking
-- Parameters: p_booking_id (INT) - Booking ID to calculate points for
-- Called by: Trigger after booking completion
-- ============================================================================
CREATE PROCEDURE `CalculateLoyaltyPoints`(IN p_booking_id INT)
BEGIN
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

-- ============================================================================
-- PROCEDURE: CalculateRoomRevenue
-- Description: Calculate total revenue for a specific hotel in a date range
-- Parameters:  
--   p_hotel_id (INT) - Hotel ID
--   p_start_date (DATE) - Start date for revenue calculation
--   p_end_date (DATE) - End date for revenue calculation
--   p_total_revenue (OUT DECIMAL) - Total revenue amount
-- ============================================================================
CREATE PROCEDURE `CalculateRoomRevenue`(
    IN p_hotel_id INT,
    IN p_start_date DATE,
    IN p_end_date DATE,
    OUT p_total_revenue DECIMAL(10,2)
)
BEGIN
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

-- ============================================================================
-- PROCEDURE: GenerateMonthlyHotelReport
-- Description: Generate comprehensive monthly performance report for a hotel
-- Parameters:
--   p_hotel_id (INT) - Hotel ID
--   p_year (INT) - Year for the report
--   p_month (INT) - Month for the report (1-12)
-- ============================================================================
CREATE PROCEDURE `GenerateMonthlyHotelReport`(
    IN p_hotel_id INT,
    IN p_year INT,
    IN p_month INT
)
BEGIN
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

-- ============================================================================
-- PROCEDURE: GetAvailableRooms
-- Description: Find available rooms for specified dates and room type
-- Parameters:
--   p_hotel_id (INT) - Hotel ID
--   p_check_in (DATE) - Check-in date
--   p_check_out (DATE) - Check-out date
--   p_room_type_id (INT) - Room type ID (NULL for all types)
-- ============================================================================
CREATE PROCEDURE `GetAvailableRooms`(
    IN p_hotel_id INT,
    IN p_check_in DATE,
    IN p_check_out DATE,
    IN p_room_type_id INT
)
BEGIN
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

-- ============================================================================
-- PROCEDURE: ProcessLoyaltyUpgrades
-- Description: Process batch loyalty tier upgrades for all active guests
-- Called by: Admin/system scheduled tasks
-- ============================================================================
CREATE PROCEDURE `ProcessLoyaltyUpgrades`()
BEGIN
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

-- ============================================================================
-- PROCEDURE: UpdateRoomPrices
-- Description: Update room prices with seasonal and manual adjustments
-- Parameters:
--   p_hotel_id (INT) - Hotel ID
--   p_adjustment_percentage (DECIMAL) - Manual adjustment percentage (+ or -)
-- ============================================================================
CREATE PROCEDURE `UpdateRoomPrices`(
    IN p_hotel_id INT,
    IN p_adjustment_percentage DECIMAL(5,2)
)
BEGIN
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

-- ============================================================================
-- PROCEDURE: UpdateRoomPricesBasedOnDemand
-- Description: Dynamic pricing based on current occupancy rates
-- Parameters: p_hotel_id (INT) - Hotel ID
-- ============================================================================
CREATE PROCEDURE `UpdateRoomPricesBasedOnDemand`(IN p_hotel_id INT)
BEGIN
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

-- ============================================================================
-- PROCEDURE: UpdateRoomPricesManual
-- Description: Manual price adjustment for all rooms in a hotel
-- Parameters:
--   p_hotel_id (INT) - Hotel ID
--   p_percentage (DECIMAL) - Percentage adjustment (+ or -)
-- Example: CALL UpdateRoomPricesManual(1, 10.00); -- Increase by 10%
-- ============================================================================
CREATE PROCEDURE `UpdateRoomPricesManual`(
    IN p_hotel_id INT,
    IN p_percentage DECIMAL(5,2)
)
BEGIN
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

DELIMITER ;

-- ============================================================================
-- Success message
-- ============================================================================
SELECT 'Procedures created successfully! Run 03_functions.sql next.' as Status;
