-- CLEANUP SAMPLE DATA
-- Run this file to remove all sample data and reset to base schema only
-- This will keep the original 3 hotels and 4 guests from enhanced_smart_stay.sql

USE smart_stay;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Delete sample data (keep only original data from enhanced_smart_stay.sql)
DELETE FROM hotel_images WHERE hotel_id > 3 OR hotel_id IS NULL;
DELETE FROM staff WHERE hotel_id > 3;
DELETE FROM service_bookings;  
DELETE FROM services WHERE hotel_id > 3;
DELETE FROM reviews WHERE hotel_id > 3 OR booking_id > 0;
DELETE FROM payments;
DELETE FROM event_bookings;
DELETE FROM events;
DELETE FROM bookings;
DELETE FROM rooms WHERE hotel_id > 3 OR room_id > 8;  -- Keep original 8 rooms from base schema
DELETE FROM guests WHERE guest_id > 4;
DELETE FROM hotels WHERE hotel_id > 3;
DELETE FROM system_logs;

-- Reset auto-increment counters
ALTER TABLE hotels AUTO_INCREMENT = 4;
ALTER TABLE guests AUTO_INCREMENT = 5;
ALTER TABLE rooms AUTO_INCREMENT = 9;
ALTER TABLE bookings AUTO_INCREMENT = 1;
ALTER TABLE events AUTO_INCREMENT = 1;
ALTER TABLE event_bookings AUTO_INCREMENT = 1;
ALTER TABLE services AUTO_INCREMENT = 1;
ALTER TABLE service_bookings AUTO_INCREMENT = 1;
ALTER TABLE reviews AUTO_INCREMENT = 1;
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE staff AUTO_INCREMENT = 1;
ALTER TABLE hotel_images AUTO_INCREMENT = 1;
ALTER TABLE system_logs AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verify cleanup
SELECT 'Hotels' as table_name, COUNT(*) as remaining_records FROM hotels
UNION ALL
SELECT 'Guests' as table_name, COUNT(*) as remaining_records FROM guests
UNION ALL
SELECT 'Rooms' as table_name, COUNT(*) as remaining_records FROM rooms
UNION ALL
SELECT 'Bookings' as table_name, COUNT(*) as remaining_records FROM bookings
UNION ALL
SELECT 'Events' as table_name, COUNT(*) as remaining_records FROM events;

SELECT 'Cleanup completed! You can now run sample_data_inserts.sql safely.' as status;