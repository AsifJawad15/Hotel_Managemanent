-- =====================================================
-- SMARTSTAY DATABASE FUNCTIONS
-- Reusable calculation and utility functions
-- =====================================================

USE `smart_stay`;

DELIMITER $$

-- =====================================================
-- FUNCTION: CalculateAge
-- Calculate age from date of birth
-- =====================================================
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

-- =====================================================
-- FUNCTION: CalculateDynamicPrice
-- Calculate dynamic room price based on factors
-- =====================================================
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
    
    SET v_season = GetSeason(p_check_in_date);
    
    CASE v_season
        WHEN 'Peak' THEN SET v_multiplier = 1.30;
        WHEN 'High' THEN SET v_multiplier = 1.15;
        WHEN 'Low' THEN SET v_multiplier = 0.85;
        ELSE SET v_multiplier = 1.00;
    END CASE;
    
    SET v_days_advance = DATEDIFF(p_check_in_date, CURDATE());
    
    IF v_days_advance <= 7 THEN
        SET v_multiplier = v_multiplier * 1.15;
    ELSEIF v_days_advance <= 14 THEN
        SET v_multiplier = v_multiplier * 1.10;
    ELSEIF v_days_advance >= 60 THEN
        SET v_multiplier = v_multiplier * 0.90;
    END IF;
    
    IF p_room_type = 'Suite' THEN
        SET v_multiplier = v_multiplier * 1.20;
    ELSEIF p_room_type = 'Deluxe' THEN
        SET v_multiplier = v_multiplier * 1.10;
    END IF;
    
    SET v_final_price = ROUND(p_base_price * v_multiplier, 2);
    
    RETURN v_final_price;
END$$

-- =====================================================
-- FUNCTION: CalculateGuestSatisfactionScore
-- Calculate satisfaction score for a hotel
-- =====================================================
CREATE FUNCTION `CalculateGuestSatisfactionScore`(p_hotel_id INT) 
RETURNS DECIMAL(5,2)
READS SQL DATA
BEGIN
    DECLARE v_avg_rating DECIMAL(3,2);
    DECLARE v_total_reviews INT;
    DECLARE v_response_rate DECIMAL(5,2);
    DECLARE v_satisfaction_score DECIMAL(5,2);
    
    SELECT 
        COALESCE(AVG(rating), 0),
        COUNT(*)
    INTO v_avg_rating, v_total_reviews
    FROM reviews
    WHERE hotel_id = p_hotel_id
    AND is_approved = 1;
    
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
    
    SET v_satisfaction_score = (
        (v_avg_rating / 5.0) * 70 +
        (LEAST(v_total_reviews, 100) / 100) * 20 +
        (v_response_rate / 100) * 10
    );
    
    RETURN ROUND(v_satisfaction_score, 2);
END$$

-- =====================================================
-- FUNCTION: GetSeason
-- Determine season for dynamic pricing
-- =====================================================
CREATE FUNCTION `GetSeason`(p_date DATE) 
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE v_month INT;
    DECLARE v_day INT;
    DECLARE v_season VARCHAR(20);
    
    SET v_month = MONTH(p_date);
    SET v_day = DAY(p_date);
    
    IF (v_month = 12 AND v_day >= 15) OR (v_month = 1) OR (v_month = 2) THEN
        SET v_season = 'Peak';
    ELSEIF (v_month = 6 OR v_month = 7 OR v_month = 8) THEN
        SET v_season = 'Peak';
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
