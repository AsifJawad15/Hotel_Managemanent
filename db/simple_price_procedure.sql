-- Simple stored procedure for updating room prices
-- Run this in phpMyAdmin SQL tab

USE smart_stay;

DELIMITER //

DROP PROCEDURE IF EXISTS UpdateRoomPrices//

CREATE PROCEDURE UpdateRoomPrices(
    IN p_hotel_id INT,
    IN p_percentage DECIMAL(5,2)
)
BEGIN
    DECLARE v_multiplier DECIMAL(6,4);
    DECLARE v_affected_rows INT DEFAULT 0;
    
    -- Calculate multiplier (e.g., 10% = 1.10, -5% = 0.95)
    SET v_multiplier = 1 + (p_percentage / 100);
    
    -- Update rooms for specific hotel or all hotels
    IF p_hotel_id IS NOT NULL THEN
        UPDATE rooms 
        SET price = ROUND(price * v_multiplier, 2) 
        WHERE hotel_id = p_hotel_id AND is_active = TRUE;
    ELSE
        UPDATE rooms 
        SET price = ROUND(price * v_multiplier, 2) 
        WHERE is_active = TRUE;
    END IF;
    
    -- Get affected rows
    SET v_affected_rows = ROW_COUNT();
    
    -- Return result
    SELECT v_affected_rows as affected_rows, p_percentage as percentage_applied;
    
END//

DELIMITER ;

-- Test the procedure
-- CALL UpdateRoomPrices(1, 10.00);  -- Increase hotel 1 prices by 10%
-- CALL UpdateRoomPrices(NULL, 5.00); -- Increase all hotel prices by 5%