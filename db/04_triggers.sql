-- =====================================================
-- SMARTSTAY DATABASE TRIGGERS
-- Automated actions for data integrity and business logic
-- =====================================================

USE `smart_stay`;

DELIMITER $$

-- =====================================================
-- TRIGGER: calculate_loyalty_on_completion
-- Award loyalty points when booking is completed
-- =====================================================
CREATE TRIGGER `calculate_loyalty_on_completion` 
AFTER UPDATE ON `bookings`
FOR EACH ROW 
BEGIN
    IF NEW.booking_status = 'Completed' AND OLD.booking_status != 'Completed' THEN
        CALL CalculateLoyaltyPoints(NEW.booking_id);
    END IF;
END$$

-- =====================================================
-- TRIGGER: log_booking_changes
-- Log all booking modifications for audit trail
-- =====================================================
CREATE TRIGGER `log_booking_changes` 
AFTER UPDATE ON `bookings`
FOR EACH ROW 
BEGIN
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
END$$

-- =====================================================
-- TRIGGER: update_event_participants
-- Update participant count when event booking is made
-- =====================================================
CREATE TRIGGER `update_event_participants_insert` 
AFTER INSERT ON `event_bookings`
FOR EACH ROW 
BEGIN
    UPDATE events
    SET current_participants = current_participants + NEW.participants
    WHERE event_id = NEW.event_id;
END$$

CREATE TRIGGER `update_event_participants_delete` 
AFTER DELETE ON `event_bookings`
FOR EACH ROW 
BEGIN
    UPDATE events
    SET current_participants = GREATEST(0, current_participants - OLD.participants)
    WHERE event_id = OLD.event_id;
END$$

CREATE TRIGGER `update_event_participants_update` 
AFTER UPDATE ON `event_bookings`
FOR EACH ROW 
BEGIN
    IF NEW.booking_status = 'Cancelled' AND OLD.booking_status != 'Cancelled' THEN
        UPDATE events
        SET current_participants = GREATEST(0, current_participants - NEW.participants)
        WHERE event_id = NEW.event_id;
    ELSEIF OLD.booking_status = 'Cancelled' AND NEW.booking_status != 'Cancelled' THEN
        UPDATE events
        SET current_participants = current_participants + NEW.participants
        WHERE event_id = NEW.event_id;
    END IF;
END$$

-- =====================================================
-- TRIGGER: validate_room_availability
-- Prevent double booking of rooms
-- =====================================================
CREATE TRIGGER `validate_room_availability_insert` 
BEFORE INSERT ON `bookings`
FOR EACH ROW 
BEGIN
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
END$$

CREATE TRIGGER `validate_room_availability_update` 
BEFORE UPDATE ON `bookings`
FOR EACH ROW 
BEGIN
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
END$$

-- =====================================================
-- TRIGGER: update_hotel_total_rooms
-- Maintain accurate room count for hotels
-- =====================================================
CREATE TRIGGER `update_hotel_total_rooms_insert` 
AFTER INSERT ON `rooms`
FOR EACH ROW 
BEGIN
    UPDATE hotels
    SET total_rooms = total_rooms + 1
    WHERE hotel_id = NEW.hotel_id;
END$$

CREATE TRIGGER `update_hotel_total_rooms_delete` 
AFTER DELETE ON `rooms`
FOR EACH ROW 
BEGIN
    UPDATE hotels
    SET total_rooms = GREATEST(0, total_rooms - 1)
    WHERE hotel_id = OLD.hotel_id;
END$$

-- =====================================================
-- TRIGGER: calculate_booking_amounts
-- Auto-calculate tax and final amount for bookings
-- =====================================================
CREATE TRIGGER `calculate_booking_amounts` 
BEFORE INSERT ON `bookings`
FOR EACH ROW 
BEGIN
    DECLARE v_tax_rate DECIMAL(5,2) DEFAULT 0.18;
    
    IF NEW.tax_amount = 0 OR NEW.tax_amount IS NULL THEN
        SET NEW.tax_amount = ROUND(NEW.total_amount * v_tax_rate, 2);
    END IF;
    
    IF NEW.final_amount = 0 OR NEW.final_amount IS NULL THEN
        SET NEW.final_amount = ROUND(NEW.total_amount + NEW.tax_amount - NEW.discount_amount, 2);
    END IF;
END$$

DELIMITER ;
