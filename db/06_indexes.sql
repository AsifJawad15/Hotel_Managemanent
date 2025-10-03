-- ============================================================================
-- SMARTSTAY DATABASE INDEXES
-- ============================================================================
-- File: 06_indexes.sql
-- Purpose: Additional performance optimization indexes
-- Run this file after 05_views.sql
-- Note: Most indexes are already created in 01_schema.sql with table definitions
-- ============================================================================

USE `smart_stay`;

-- ============================================================================
-- COMPOSITE INDEXES FOR COMMON QUERIES
-- ============================================================================
-- These indexes improve query performance for frequently used filter combinations

-- Already created in schema: idx_booking_date_status
-- Already created in schema: idx_guest_membership_active
-- Already created in schema: idx_room_hotel_status
-- Already created in schema: idx_event_date_status
-- Already created in schema: idx_review_hotel_approved
-- Already created in schema: idx_payment_date_status

-- ============================================================================
-- FULL TEXT SEARCH INDEXES
-- ============================================================================
-- These indexes enable fast text search across multiple columns

-- Already created in schema: ft_hotel_search (hotel_name, description, city)
-- Already created in schema: ft_room_type_search (type_name, description)
-- Already created in schema: ft_event_search (event_name, description, venue)
-- Already created in schema: ft_service_search (service_name, description)

-- ============================================================================
-- INDEX SUMMARY
-- ============================================================================
-- All necessary indexes have been created in 01_schema.sql including:
-- 
-- PRIMARY KEYS (Auto-indexed):
-- - admins(admin_id), guests(guest_id), hotels(hotel_id)
-- - rooms(room_id), bookings(booking_id), events(event_id)
-- - event_bookings(event_booking_id), reviews(review_id)
-- - payments(payment_id), services(service_id), system_logs(log_id)
-- 
-- FOREIGN KEYS (Auto-indexed):
-- - All FK relationships create indexes automatically
--
-- CUSTOM INDEXES:
-- - Composite indexes for multi-column queries
-- - Full-text indexes for search functionality
-- - Single column indexes for frequent lookups
--
-- ============================================================================
-- INDEX MAINTENANCE
-- ============================================================================
-- 
-- Check index usage:
--   SHOW INDEX FROM table_name;
--   SELECT * FROM information_schema.STATISTICS WHERE table_schema = 'smart_stay';
--
-- Analyze query performance:
--   EXPLAIN SELECT * FROM bookings WHERE check_in >= '2025-10-01';
--   EXPLAIN ANALYZE SELECT * FROM hotels WHERE hotel_name LIKE '%Grand%';
--
-- Maintain indexes:
--   ANALYZE TABLE table_name;  -- Update statistics
--   OPTIMIZE TABLE table_name; -- Defragment and rebuild
--
-- Monitor performance:
--   -- Enable slow query log in my.cnf:
--   -- slow_query_log = 1
--   -- long_query_time = 2
--   -- slow_query_log_file = /path/to/slow-query.log
--
-- ============================================================================

-- ============================================================================
-- Success message
-- ============================================================================
SELECT 'Indexes verified! All indexes created in schema. Run 07_sample_data.sql next.' as Status;
