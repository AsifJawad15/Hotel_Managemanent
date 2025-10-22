-- ============================================================================
-- SMARTSTAY DATABASE FUNCTIONS
-- ============================================================================
-- File: 03_functions.sql
-- Purpose: Reusable calculation and utility functions
-- Run this file after 02_procedures.sql
-- ============================================================================

USE `smart_stay`;

DELIMITER $$

-- ============================================================================
-- FUNCTION: CalculateAge
-- Description: Calculate age from date of birth
-- Parameters: p_date_of_birth (DATE) - Date of birth
-- Returns: INT - Age in years
-- Example: SELECT CalculateAge('1990-05-15');
-- ============================================================================
CREATE FUNCTION `CalculateAge`(p_date_of_birth DATE) 
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE v_age INT;
    
    IF p_date_of_birth IS NULL THEN
        RETURN NULL;
    END IF;
    
    SET v_age = TIMESTAMPDIFF(YEAR, p_date_of_birth, CURDATE());
    
    RETURN v_age;
END$$

-- ============================================================================
-- FUNCTION: CalculateDynamicPrice
-- Description: Calculate dynamic room price based on season, advance booking, and room type
-- Parameters:
--   p_base_price (DECIMAL) - Base room price
--   p_check_in_date (DATE) - Check-in date
--   p_room_type (VARCHAR) - Room type name
-- Returns: DECIMAL - Final calculated price
-- Example: SELECT CalculateDynamicPrice(100.00, '2025-12-25', 'Suite');
-- ============================================================================
CREATE FUNCTION `CalculateDynamicPrice`(
    p_base_price DECIMAL(10,2),
    p_check_in_date DATE,
    p_room_type VARCHAR(50)
) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
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

-- ============================================================================
-- FUNCTION: GetSeason
-- Description: Determine season category for dynamic pricing
-- Parameters: p_date (DATE) - Date to check
-- Returns: VARCHAR - Season name (Peak, High, Normal, Low)
-- Example: SELECT GetSeason(CURDATE());
-- ============================================================================
CREATE FUNCTION `GetSeason`(p_date DATE) 
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
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

-- ============================================================================
-- Success message
-- ============================================================================
SELECT 'Functions created successfully! Run 04_triggers.sql next.' as Status;
