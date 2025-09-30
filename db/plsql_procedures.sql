-- PL/SQL EQUIVALENT PROCEDURES AND FUNCTIONS FOR MYSQL
-- Advanced database operations with loops, cursors, and conditional logic

DELIMITER //

-- =====================================================
-- PROCEDURES WITH LOOPS AND CURSORS
-- =====================================================

-- 1. Procedure to update room prices based on demand (with loop)
CREATE PROCEDURE UpdateRoomPricesBasedOnDemand()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_room_id INT;
    DECLARE v_current_price DECIMAL(10,2);
    DECLARE v_booking_count INT;
    DECLARE v_new_price DECIMAL(10,2);
    
    -- Cursor to iterate through all active rooms
    DECLARE room_cursor CURSOR FOR 
        SELECT room_id, price FROM rooms WHERE is_active = TRUE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN room_cursor;
    
    read_loop: LOOP
        FETCH room_cursor INTO v_room_id, v_current_price;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Count bookings for this room in the last 3 months
        SELECT COUNT(*) INTO v_booking_count
        FROM bookings 
        WHERE room_id = v_room_id 
        AND check_in >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        AND booking_status = 'Completed';
        
        -- Adjust price based on demand
        CASE 
            WHEN v_booking_count >= 20 THEN 
                SET v_new_price = v_current_price * 1.15; -- High demand, increase by 15%
            WHEN v_booking_count >= 10 THEN 
                SET v_new_price = v_current_price * 1.05; -- Medium demand, increase by 5%
            WHEN v_booking_count <= 2 THEN 
                SET v_new_price = v_current_price * 0.90; -- Low demand, decrease by 10%
            ELSE 
                SET v_new_price = v_current_price; -- Keep current price
        END CASE;
        
        -- Update room price
        UPDATE rooms SET price = v_new_price WHERE room_id = v_room_id;
        
        -- Log the change
        INSERT INTO system_logs (user_type, action, table_name, record_id, old_values, new_values)
        VALUES (
            'System', 
            'PRICE_UPDATE', 
            'rooms', 
            v_room_id,
            JSON_OBJECT('old_price', v_current_price, 'booking_count', v_booking_count),
            JSON_OBJECT('new_price', v_new_price, 'adjustment_reason', 'demand_based')
        );
        
    END LOOP;
    
    CLOSE room_cursor;
END//

-- 1.1. Manual room price update procedure with custom percentage
CREATE PROCEDURE UpdateRoomPricesManual(
    IN hotel_id_param INT,
    IN percentage_change DECIMAL(5,2)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_room_id INT;
    DECLARE v_current_price DECIMAL(10,2);
    DECLARE v_new_price DECIMAL(10,2);
    DECLARE v_room_number VARCHAR(50);
    DECLARE rooms_updated INT DEFAULT 0;
    DECLARE multiplier DECIMAL(5,4);
    
    -- Calculate multiplier from percentage (e.g., 10% = 1.10, -5% = 0.95)
    SET multiplier = 1 + (percentage_change / 100);
    
    -- Cursor to iterate through hotel rooms or all rooms
    DECLARE room_cursor CURSOR FOR 
        SELECT room_id, room_number, price 
        FROM rooms 
        WHERE is_active = TRUE 
        AND (hotel_id_param IS NULL OR hotel_id = hotel_id_param);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Create temp table to store update results
    CREATE TEMPORARY TABLE IF NOT EXISTS temp_price_updates (
        room_id INT,
        room_number VARCHAR(50),
        old_price DECIMAL(10,2),
        new_price DECIMAL(10,2),
        price_change DECIMAL(10,2),
        percentage_applied DECIMAL(5,2)
    );
    
    OPEN room_cursor;
    
    update_loop: LOOP
        FETCH room_cursor INTO v_room_id, v_room_number, v_current_price;
        
        IF done THEN
            LEAVE update_loop;
        END IF;
        
        -- Calculate new price
        SET v_new_price = ROUND(v_current_price * multiplier, 2);
        
        -- Ensure minimum price of $10
        IF v_new_price < 10.00 THEN
            SET v_new_price = 10.00;
        END IF;
        
        -- Update room price
        UPDATE rooms SET price = v_new_price WHERE room_id = v_room_id;
        
        -- Store update info
        INSERT INTO temp_price_updates VALUES (
            v_room_id, v_room_number, v_current_price, v_new_price,
            (v_new_price - v_current_price), percentage_change
        );
        
        -- Log the change
        INSERT INTO system_logs (user_type, action, table_name, record_id, old_values, new_values)
        VALUES (
            'Admin', 
            'MANUAL_PRICE_UPDATE', 
            'rooms', 
            v_room_id,
            JSON_OBJECT('old_price', v_current_price),
            JSON_OBJECT('new_price', v_new_price, 'percentage_change', percentage_change)
        );
        
        SET rooms_updated = rooms_updated + 1;
        
    END LOOP;
    
    CLOSE room_cursor;
    
    -- Return summary of updates
    SELECT 
        rooms_updated as total_rooms_updated,
        percentage_change as percentage_applied,
        SUM(price_change) as total_price_change,
        AVG(old_price) as avg_old_price,
        AVG(new_price) as avg_new_price
    FROM temp_price_updates;
    
    -- Return detailed updates
    SELECT * FROM temp_price_updates ORDER BY room_id;
    
END//

-- 2. Procedure to generate monthly performance report for all hotels
CREATE PROCEDURE GenerateMonthlyHotelReport(
    IN target_year INT,
    IN target_month INT
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_hotel_id INT;
    DECLARE v_hotel_name VARCHAR(100);
    DECLARE v_total_bookings INT DEFAULT 0;
    DECLARE v_total_revenue DECIMAL(12,2) DEFAULT 0.00;
    DECLARE v_occupancy_rate DECIMAL(5,2) DEFAULT 0.00;
    DECLARE v_avg_rating DECIMAL(3,2) DEFAULT 0.00;
    
    -- Cursor for all active hotels
    DECLARE hotel_cursor CURSOR FOR 
        SELECT hotel_id, hotel_name FROM hotels WHERE is_active = TRUE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Create temporary table for report
    CREATE TEMPORARY TABLE IF NOT EXISTS temp_hotel_report (
        hotel_id INT,
        hotel_name VARCHAR(100),
        report_year INT,
        report_month INT,
        total_bookings INT,
        total_revenue DECIMAL(12,2),
        occupancy_rate DECIMAL(5,2),
        average_rating DECIMAL(3,2),
        performance_grade VARCHAR(10),
        generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    OPEN hotel_cursor;
    
    report_loop: LOOP
        FETCH hotel_cursor INTO v_hotel_id, v_hotel_name;
        
        IF done THEN
            LEAVE report_loop;
        END IF;
        
        -- Calculate total bookings
        SELECT COUNT(*) INTO v_total_bookings
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE r.hotel_id = v_hotel_id
        AND YEAR(b.check_in) = target_year
        AND MONTH(b.check_in) = target_month
        AND b.booking_status IN ('Confirmed', 'Completed');
        
        -- Calculate total revenue
        SELECT COALESCE(SUM(b.final_amount), 0) INTO v_total_revenue
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE r.hotel_id = v_hotel_id
        AND YEAR(b.check_in) = target_year
        AND MONTH(b.check_in) = target_month
        AND b.booking_status = 'Completed';
        
        -- Calculate occupancy rate
        SELECT COALESCE(
            (COUNT(DISTINCT b.room_id) / COUNT(DISTINCT r.room_id)) * 100, 0
        ) INTO v_occupancy_rate
        FROM rooms r
        LEFT JOIN bookings b ON r.room_id = b.room_id 
            AND YEAR(b.check_in) = target_year 
            AND MONTH(b.check_in) = target_month
            AND b.booking_status = 'Completed'
        WHERE r.hotel_id = v_hotel_id AND r.is_active = TRUE;
        
        -- Calculate average rating
        SELECT COALESCE(AVG(rating), 0) INTO v_avg_rating
        FROM reviews
        WHERE hotel_id = v_hotel_id
        AND YEAR(review_date) = target_year
        AND MONTH(review_date) = target_month;
        
        -- Insert into report table
        INSERT INTO temp_hotel_report (
            hotel_id, hotel_name, report_year, report_month,
            total_bookings, total_revenue, occupancy_rate, average_rating,
            performance_grade
        ) VALUES (
            v_hotel_id, v_hotel_name, target_year, target_month,
            v_total_bookings, v_total_revenue, v_occupancy_rate, v_avg_rating,
            CASE 
                WHEN v_occupancy_rate >= 80 AND v_avg_rating >= 4.5 THEN 'A+'
                WHEN v_occupancy_rate >= 70 AND v_avg_rating >= 4.0 THEN 'A'
                WHEN v_occupancy_rate >= 60 AND v_avg_rating >= 3.5 THEN 'B'
                WHEN v_occupancy_rate >= 50 AND v_avg_rating >= 3.0 THEN 'C'
                ELSE 'D'
            END
        );
        
    END LOOP;
    
    CLOSE hotel_cursor;
    
    -- Return the report
    SELECT * FROM temp_hotel_report ORDER BY performance_grade, total_revenue DESC;
    
END//

-- 3. Procedure with complex conditional logic for loyalty program management
CREATE PROCEDURE ProcessLoyaltyUpgrades()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_guest_id INT;
    DECLARE v_current_points INT;
    DECLARE v_current_level VARCHAR(20);
    DECLARE v_total_spent DECIMAL(10,2);
    DECLARE v_years_member INT;
    DECLARE v_new_level VARCHAR(20);
    DECLARE v_bonus_points INT DEFAULT 0;
    
    -- Cursor for all active guests
    DECLARE guest_cursor CURSOR FOR 
        SELECT guest_id, loyalty_points, membership_level FROM guests WHERE is_active = TRUE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN guest_cursor;
    
    loyalty_loop: LOOP
        FETCH guest_cursor INTO v_guest_id, v_current_points, v_current_level;
        
        IF done THEN
            LEAVE loyalty_loop;
        END IF;
        
        -- Calculate total spent by guest
        SELECT COALESCE(SUM(final_amount), 0) INTO v_total_spent
        FROM bookings 
        WHERE guest_id = v_guest_id AND booking_status = 'Completed';
        
        -- Calculate years as member
        SELECT TIMESTAMPDIFF(YEAR, created_at, NOW()) INTO v_years_member
        FROM guests WHERE guest_id = v_guest_id;
        
        -- Determine new level based on complex criteria
        IF v_current_points >= 5000 AND v_total_spent >= 10000 THEN
            SET v_new_level = 'Platinum';
            IF v_current_level != 'Platinum' THEN
                SET v_bonus_points = 500; -- Upgrade bonus
            END IF;
        ELSEIF v_current_points >= 2000 AND (v_total_spent >= 5000 OR v_years_member >= 3) THEN
            SET v_new_level = 'Gold';
            IF v_current_level NOT IN ('Gold', 'Platinum') THEN
                SET v_bonus_points = 200;
            END IF;
        ELSEIF v_current_points >= 500 OR v_total_spent >= 1000 THEN
            SET v_new_level = 'Silver';
            IF v_current_level = 'Bronze' THEN
                SET v_bonus_points = 100;
            END IF;
        ELSE
            SET v_new_level = 'Bronze';
        END IF;
        
        -- Update guest if level changed
        IF v_new_level != v_current_level OR v_bonus_points > 0 THEN
            UPDATE guests 
            SET membership_level = v_new_level,
                loyalty_points = loyalty_points + v_bonus_points
            WHERE guest_id = v_guest_id;
            
            -- Log the upgrade
            INSERT INTO system_logs (user_type, action, table_name, record_id, old_values, new_values)
            VALUES (
                'System', 
                'LOYALTY_UPGRADE', 
                'guests', 
                v_guest_id,
                JSON_OBJECT('old_level', v_current_level, 'old_points', v_current_points),
                JSON_OBJECT('new_level', v_new_level, 'bonus_points', v_bonus_points, 'total_spent', v_total_spent)
            );
        END IF;
        
        SET v_bonus_points = 0; -- Reset for next iteration
        
    END LOOP;
    
    CLOSE guest_cursor;
END//

-- 4. Procedure for automated room maintenance scheduling
CREATE PROCEDURE ScheduleRoomMaintenance()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_room_id INT;
    DECLARE v_hotel_id INT;
    DECLARE v_room_number VARCHAR(50);
    DECLARE v_last_cleaned TIMESTAMP;
    DECLARE v_booking_count INT;
    DECLARE v_days_since_clean INT;
    DECLARE v_maintenance_needed BOOLEAN DEFAULT FALSE;
    DECLARE v_reason TEXT;
    
    DECLARE room_cursor CURSOR FOR 
        SELECT room_id, hotel_id, room_number, last_cleaned 
        FROM rooms 
        WHERE is_active = TRUE AND maintenance_status = 'Available';
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Create temporary maintenance schedule table
    CREATE TEMPORARY TABLE IF NOT EXISTS temp_maintenance_schedule (
        room_id INT,
        hotel_id INT,
        room_number VARCHAR(50),
        maintenance_type VARCHAR(50),
        priority ENUM('Low', 'Medium', 'High', 'Critical'),
        reason TEXT,
        scheduled_date DATE,
        estimated_duration_hours INT
    );
    
    OPEN room_cursor;
    
    maintenance_loop: LOOP
        FETCH room_cursor INTO v_room_id, v_hotel_id, v_room_number, v_last_cleaned;
        
        IF done THEN
            LEAVE maintenance_loop;
        END IF;
        
        SET v_maintenance_needed = FALSE;
        SET v_reason = '';
        
        -- Calculate days since last cleaning
        IF v_last_cleaned IS NOT NULL THEN
            SET v_days_since_clean = DATEDIFF(NOW(), v_last_cleaned);
        ELSE
            SET v_days_since_clean = 999; -- Force maintenance if never cleaned
        END IF;
        
        -- Count recent bookings (last 30 days)
        SELECT COUNT(*) INTO v_booking_count
        FROM bookings 
        WHERE room_id = v_room_id 
        AND check_out >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND booking_status = 'Completed';
        
        -- Determine maintenance needs based on multiple criteria
        CASE
            WHEN v_days_since_clean >= 7 OR v_booking_count >= 10 THEN
                -- Regular cleaning needed
                INSERT INTO temp_maintenance_schedule VALUES (
                    v_room_id, v_hotel_id, v_room_number,
                    'Deep Cleaning', 
                    CASE WHEN v_days_since_clean >= 14 THEN 'High' ELSE 'Medium' END,
                    CONCAT('Last cleaned: ', COALESCE(v_last_cleaned, 'Never'), ', Recent bookings: ', v_booking_count),
                    CURDATE() + INTERVAL 1 DAY,
                    4
                );
                
            WHEN v_booking_count >= 20 THEN
                -- Heavy usage maintenance
                INSERT INTO temp_maintenance_schedule VALUES (
                    v_room_id, v_hotel_id, v_room_number,
                    'Heavy Usage Inspection',
                    'High',
                    CONCAT('High usage: ', v_booking_count, ' bookings in 30 days'),
                    CURDATE() + INTERVAL 2 DAY,
                    8
                );
                
            WHEN v_days_since_clean >= 30 THEN
                -- Monthly maintenance
                INSERT INTO temp_maintenance_schedule VALUES (
                    v_room_id, v_hotel_id, v_room_number,
                    'Monthly Maintenance',
                    'Critical',
                    CONCAT('Overdue maintenance: ', v_days_since_clean, ' days since last clean'),
                    CURDATE(),
                    12
                );
        END CASE;
        
    END LOOP;
    
    CLOSE room_cursor;
    
    -- Return the maintenance schedule
    SELECT 
        h.hotel_name,
        ms.*
    FROM temp_maintenance_schedule ms
    JOIN hotels h ON ms.hotel_id = h.hotel_id
    ORDER BY 
        CASE ms.priority
            WHEN 'Critical' THEN 1
            WHEN 'High' THEN 2
            WHEN 'Medium' THEN 3
            WHEN 'Low' THEN 4
        END,
        ms.scheduled_date;
        
END//

-- =====================================================
-- FUNCTIONS WITH COMPLEX LOGIC
-- =====================================================

-- 1. Function to calculate dynamic room pricing based on multiple factors
CREATE FUNCTION CalculateDynamicPrice(
    room_id_param INT,
    check_in_date DATE,
    nights INT
) 
RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE base_price DECIMAL(10,2);
    DECLARE final_price DECIMAL(10,2);
    DECLARE season_multiplier DECIMAL(3,2) DEFAULT 1.00;
    DECLARE demand_multiplier DECIMAL(3,2) DEFAULT 1.00;
    DECLARE weekday_multiplier DECIMAL(3,2) DEFAULT 1.00;
    DECLARE booking_count INT;
    DECLARE advance_days INT;
    
    -- Get base price
    SELECT price INTO base_price FROM rooms WHERE room_id = room_id_param;
    
    -- Calculate advance booking days
    SET advance_days = DATEDIFF(check_in_date, CURDATE());
    
    -- Season adjustment
    CASE GetSeason(check_in_date)
        WHEN 'Summer' THEN SET season_multiplier = 1.20;
        WHEN 'Winter' THEN SET season_multiplier = 0.85;
        WHEN 'Spring' THEN SET season_multiplier = 1.10;
        WHEN 'Fall' THEN SET season_multiplier = 0.95;
    END CASE;
    
    -- Weekend adjustment
    IF DAYOFWEEK(check_in_date) IN (1, 7) THEN -- Sunday or Saturday
        SET weekday_multiplier = 1.15;
    END IF;
    
    -- Demand adjustment based on existing bookings
    SELECT COUNT(*) INTO booking_count
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE r.hotel_id = (SELECT hotel_id FROM rooms WHERE room_id = room_id_param)
    AND check_in_date BETWEEN b.check_in AND b.check_out
    AND b.booking_status = 'Confirmed';
    
    CASE 
        WHEN booking_count >= 10 THEN SET demand_multiplier = 1.25;
        WHEN booking_count >= 5 THEN SET demand_multiplier = 1.10;
        WHEN booking_count <= 1 THEN SET demand_multiplier = 0.90;
        ELSE SET demand_multiplier = 1.00;
    END CASE;
    
    -- Early bird discount
    IF advance_days >= 30 THEN
        SET demand_multiplier = demand_multiplier * 0.90;
    ELSEIF advance_days <= 3 THEN
        SET demand_multiplier = demand_multiplier * 1.05; -- Last minute premium
    END IF;
    
    -- Calculate final price
    SET final_price = base_price * season_multiplier * demand_multiplier * weekday_multiplier;
    
    -- Apply long stay discount
    IF nights >= 7 THEN
        SET final_price = final_price * 0.95;
    ELSEIF nights >= 14 THEN
        SET final_price = final_price * 0.90;
    END IF;
    
    RETURN ROUND(final_price, 2);
END//

-- 2. Function to calculate guest satisfaction score
CREATE FUNCTION CalculateGuestSatisfactionScore(guest_id_param INT)
RETURNS DECIMAL(4,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE satisfaction_score DECIMAL(4,2) DEFAULT 0.00;
    DECLARE total_reviews INT DEFAULT 0;
    DECLARE avg_rating DECIMAL(3,2) DEFAULT 0.00;
    DECLARE booking_count INT DEFAULT 0;
    DECLARE complaint_count INT DEFAULT 0;
    DECLARE loyalty_bonus DECIMAL(3,2) DEFAULT 0.00;
    DECLARE membership_level_param VARCHAR(20);
    
    -- Get guest membership level
    SELECT membership_level INTO membership_level_param 
    FROM guests WHERE guest_id = guest_id_param;
    
    -- Calculate average rating given by guest
    SELECT COUNT(*), COALESCE(AVG(rating), 0) 
    INTO total_reviews, avg_rating
    FROM reviews WHERE guest_id = guest_id_param;
    
    -- Count total bookings
    SELECT COUNT(*) INTO booking_count
    FROM bookings WHERE guest_id = guest_id_param;
    
    -- Base score from reviews
    IF total_reviews > 0 THEN
        SET satisfaction_score = avg_rating * 20; -- Convert 5-point scale to 100-point scale
    ELSE
        SET satisfaction_score = 50.00; -- Neutral score for guests without reviews
    END IF;
    
    -- Adjustment for booking history
    CASE 
        WHEN booking_count >= 20 THEN SET satisfaction_score = satisfaction_score + 10;
        WHEN booking_count >= 10 THEN SET satisfaction_score = satisfaction_score + 5;
        WHEN booking_count >= 5 THEN SET satisfaction_score = satisfaction_score + 2;
    END CASE;
    
    -- Membership loyalty bonus
    CASE membership_level_param
        WHEN 'Platinum' THEN SET loyalty_bonus = 15.00;
        WHEN 'Gold' THEN SET loyalty_bonus = 10.00;
        WHEN 'Silver' THEN SET loyalty_bonus = 5.00;
        ELSE SET loyalty_bonus = 0.00;
    END CASE;
    
    SET satisfaction_score = satisfaction_score + loyalty_bonus;
    
    -- Cap the score at 100
    IF satisfaction_score > 100 THEN
        SET satisfaction_score = 100.00;
    END IF;
    
    RETURN satisfaction_score;
END//

-- 3. Function to determine optimal room assignment
CREATE FUNCTION GetOptimalRoomAssignment(
    hotel_id_param INT,
    guest_id_param INT,
    check_in_date DATE,
    check_out_date DATE,
    room_type_preference INT
)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE optimal_room_id INT DEFAULT 0;
    DECLARE guest_membership VARCHAR(20);
    DECLARE guest_satisfaction DECIMAL(4,2);
    
    -- Get guest details
    SELECT membership_level INTO guest_membership
    FROM guests WHERE guest_id = guest_id_param;
    
    SET guest_satisfaction = CalculateGuestSatisfactionScore(guest_id_param);
    
    -- Find optimal room based on guest profile and preferences
    SELECT room_id INTO optimal_room_id
    FROM (
        SELECT 
            r.room_id,
            r.price,
            r.floor_number,
            rt.type_name,
            CASE 
                -- Premium guests get priority for better rooms
                WHEN guest_membership = 'Platinum' AND r.floor_number >= 10 THEN 100
                WHEN guest_membership = 'Gold' AND r.floor_number >= 5 THEN 90
                WHEN guest_membership IN ('Silver', 'Bronze') THEN 70
                ELSE 50
            END +
            CASE 
                -- Satisfaction score bonus
                WHEN guest_satisfaction >= 90 THEN 20
                WHEN guest_satisfaction >= 70 THEN 10
                ELSE 0
            END +
            CASE 
                -- Type preference match
                WHEN room_type_preference IS NULL OR r.type_id = room_type_preference THEN 30
                ELSE 0
            END as assignment_score
        FROM rooms r
        JOIN room_types rt ON r.type_id = rt.type_id
        WHERE r.hotel_id = hotel_id_param
        AND r.is_active = TRUE
        AND r.maintenance_status = 'Available'
        AND r.room_id NOT IN (
            SELECT b.room_id 
            FROM bookings b 
            WHERE b.booking_status = 'Confirmed'
            AND NOT (check_out_date <= b.check_in OR check_in_date >= b.check_out)
        )
        ORDER BY assignment_score DESC, r.price ASC
        LIMIT 1
    ) AS optimal_selection;
    
    RETURN COALESCE(optimal_room_id, 0);
END//

DELIMITER ;

-- =====================================================
-- EXAMPLE USAGE OF PROCEDURES AND FUNCTIONS
-- =====================================================

-- Call the procedures
-- CALL UpdateRoomPricesBasedOnDemand();
-- CALL GenerateMonthlyHotelReport(2024, 11);
-- CALL ProcessLoyaltyUpgrades();
-- CALL ScheduleRoomMaintenance();

-- Use the functions in queries
-- SELECT CalculateDynamicPrice(1, '2024-12-25', 3) as dynamic_price;
-- SELECT guest_id, name, CalculateGuestSatisfactionScore(guest_id) as satisfaction_score FROM guests LIMIT 5;
-- SELECT GetOptimalRoomAssignment(1, 1, '2024-12-15', '2024-12-18', 3) as recommended_room;