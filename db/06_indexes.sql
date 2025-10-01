-- =====================================================
-- SMARTSTAY DATABASE INDEXES
-- Performance optimization indexes
-- =====================================================

USE `smart_stay`;

-- Indexes already created in schema file with PRIMARY KEY and KEY definitions
-- This file documents additional indexes for optimization

-- =====================================================
-- COMPOSITE INDEXES FOR COMMON QUERIES
-- =====================================================

-- Booking search by date range and status
CREATE INDEX idx_booking_date_status ON bookings(check_in, check_out, booking_status);

-- Guest search with membership filtering
CREATE INDEX idx_guest_membership_active ON guests(membership_level, is_active);

-- Room search by hotel and availability
CREATE INDEX idx_room_hotel_status ON rooms(hotel_id, maintenance_status, is_active);

-- Event search by date and status
CREATE INDEX idx_event_date_status ON events(event_date, event_status, hotel_id);

-- Review analytics
CREATE INDEX idx_review_hotel_approved ON reviews(hotel_id, is_approved, created_at);

-- Payment tracking
CREATE INDEX idx_payment_date_status ON payments(payment_date, payment_status);

-- Service booking tracking
CREATE INDEX idx_service_booking_date ON service_bookings(service_date, status);

-- Staff management
CREATE INDEX idx_staff_department_active ON staff(hotel_id, department, is_active);

-- =====================================================
-- FULL TEXT SEARCH INDEXES
-- =====================================================

-- Hotel search
CREATE FULLTEXT INDEX ft_hotel_search ON hotels(hotel_name, description, city);

-- Room type search
CREATE FULLTEXT INDEX ft_room_type_search ON room_types(type_name, description);

-- Event search
CREATE FULLTEXT INDEX ft_event_search ON events(event_name, description, venue);

-- Service search
CREATE FULLTEXT INDEX ft_service_search ON services(service_name, description);

-- =====================================================
-- NOTES ON INDEX USAGE
-- =====================================================
-- 
-- Primary indexes (already in schema):
-- - All PRIMARY KEY constraints create clustered indexes
-- - UNIQUE constraints create unique non-clustered indexes
-- - Foreign key constraints automatically create indexes
--
-- Query optimization tips:
-- - Use EXPLAIN to analyze query execution plans
-- - Monitor slow query log for optimization opportunities
-- - Consider index maintenance during off-peak hours
-- - Balance between query performance and write overhead
-- 
-- Maintenance commands:
-- - ANALYZE TABLE table_name; -- Update index statistics
-- - OPTIMIZE TABLE table_name; -- Defragment and rebuild indexes
-- - SHOW INDEX FROM table_name; -- View index details
-- 
-- =====================================================
