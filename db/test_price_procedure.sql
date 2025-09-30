-- Test script for UpdateRoomPricesManual procedure
-- Run this in phpMyAdmin or MySQL command line

USE smart_stay;

-- First, let's see current room prices
SELECT 
    h.hotel_name,
    r.room_number,
    r.price as current_price,
    r.room_id
FROM rooms r
JOIN hotels h ON r.hotel_id = h.hotel_id
WHERE r.is_active = TRUE
ORDER BY h.hotel_id, r.room_number
LIMIT 10;

-- Test the procedure with hotel 1, 10% increase
-- CALL UpdateRoomPricesManual(1, 10.00);

-- Alternative: Manual price update if procedure doesn't work
-- UPDATE rooms 
-- SET price = ROUND(price * 1.10, 2) 
-- WHERE hotel_id = 1 AND is_active = TRUE;

-- Check updated prices
-- SELECT 
--     h.hotel_name,
--     r.room_number,
--     r.price as updated_price,
--     r.room_id
-- FROM rooms r
-- JOIN hotels h ON r.hotel_id = h.hotel_id
-- WHERE r.hotel_id = 1 AND r.is_active = TRUE
-- ORDER BY r.room_number;