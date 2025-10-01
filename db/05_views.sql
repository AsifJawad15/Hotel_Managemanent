-- =====================================================
-- SMARTSTAY DATABASE VIEWS
-- Pre-defined queries for common business reports
-- =====================================================

USE `smart_stay`;

-- =====================================================
-- VIEW: vw_hotel_occupancy
-- Real-time hotel occupancy statistics
-- =====================================================
CREATE OR REPLACE VIEW `vw_hotel_occupancy` AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    h.city,
    h.total_rooms,
    COUNT(DISTINCT CASE 
        WHEN b.booking_status = 'Confirmed' 
        AND CURDATE() BETWEEN b.check_in AND b.check_out 
        THEN b.room_id 
    END) as occupied_rooms,
    ROUND((COUNT(DISTINCT CASE 
        WHEN b.booking_status = 'Confirmed' 
        AND CURDATE() BETWEEN b.check_in AND b.check_out 
        THEN b.room_id 
    END) / h.total_rooms) * 100, 2) as occupancy_rate,
    COUNT(DISTINCT CASE 
        WHEN b.booking_status = 'Confirmed' 
        AND b.check_in = CURDATE() 
        THEN b.booking_id 
    END) as check_ins_today,
    COUNT(DISTINCT CASE 
        WHEN b.booking_status = 'Confirmed' 
        AND b.check_out = CURDATE() 
        THEN b.booking_id 
    END) as check_outs_today
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id
WHERE h.is_active = 1
GROUP BY h.hotel_id, h.hotel_name, h.city, h.total_rooms;

-- =====================================================
-- VIEW: vw_guest_booking_history
-- Complete guest booking history with details
-- =====================================================
CREATE OR REPLACE VIEW `vw_guest_booking_history` AS
SELECT 
    g.guest_id,
    g.name as guest_name,
    g.email,
    g.membership_level,
    g.loyalty_points,
    b.booking_id,
    h.hotel_name,
    h.city as hotel_city,
    r.room_number,
    rt.type_name as room_type,
    b.check_in,
    b.check_out,
    DATEDIFF(b.check_out, b.check_in) as nights,
    b.adults,
    b.children,
    b.final_amount,
    b.booking_status,
    b.payment_status,
    b.created_at as booking_date
FROM guests g
LEFT JOIN bookings b ON g.guest_id = b.guest_id
LEFT JOIN rooms r ON b.room_id = r.room_id
LEFT JOIN hotels h ON r.hotel_id = h.hotel_id
LEFT JOIN room_types rt ON r.type_id = rt.type_id
ORDER BY b.created_at DESC;

-- =====================================================
-- VIEW: vw_hotel_revenue_summary
-- Revenue analytics by hotel
-- =====================================================
CREATE OR REPLACE VIEW `vw_hotel_revenue_summary` AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    h.city,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    COUNT(DISTINCT CASE WHEN b.booking_status = 'Completed' THEN b.booking_id END) as completed_bookings,
    COUNT(DISTINCT CASE WHEN b.booking_status = 'Cancelled' THEN b.booking_id END) as cancelled_bookings,
    COALESCE(SUM(CASE WHEN b.booking_status = 'Completed' THEN b.final_amount ELSE 0 END), 0) as total_revenue,
    COALESCE(AVG(CASE WHEN b.booking_status = 'Completed' THEN b.final_amount END), 0) as avg_booking_value,
    COALESCE(SUM(CASE WHEN b.payment_status = 'Paid' THEN b.final_amount ELSE 0 END), 0) as revenue_received,
    COUNT(DISTINCT b.guest_id) as unique_guests,
    COALESCE(AVG(rv.rating), 0) as avg_rating,
    COUNT(DISTINCT rv.review_id) as total_reviews
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id
LEFT JOIN reviews rv ON h.hotel_id = rv.hotel_id
WHERE h.is_active = 1
GROUP BY h.hotel_id, h.hotel_name, h.city;

-- =====================================================
-- VIEW: vw_room_availability
-- Current room availability status
-- =====================================================
CREATE OR REPLACE VIEW `vw_room_availability` AS
SELECT 
    h.hotel_id,
    h.hotel_name,
    r.room_id,
    r.room_number,
    rt.type_name as room_type,
    r.floor_number,
    r.price,
    r.max_occupancy,
    r.maintenance_status,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM bookings b 
            WHERE b.room_id = r.room_id 
            AND b.booking_status = 'Confirmed'
            AND CURDATE() BETWEEN b.check_in AND b.check_out
        ) THEN 'Occupied'
        WHEN r.maintenance_status != 'Available' THEN 'Maintenance'
        ELSE 'Available'
    END as current_status,
    (SELECT MIN(b.check_out) 
     FROM bookings b 
     WHERE b.room_id = r.room_id 
     AND b.booking_status = 'Confirmed'
     AND b.check_out > CURDATE()
    ) as next_available_date
FROM rooms r
INNER JOIN hotels h ON r.hotel_id = h.hotel_id
INNER JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.is_active = 1;

-- =====================================================
-- VIEW: vw_upcoming_events
-- Upcoming events with participation details
-- =====================================================
CREATE OR REPLACE VIEW `vw_upcoming_events` AS
SELECT 
    e.event_id,
    h.hotel_name,
    h.city,
    e.event_name,
    e.description,
    e.event_date,
    e.start_time,
    e.end_time,
    e.venue,
    e.event_type,
    e.max_participants,
    e.current_participants,
    e.max_participants - e.current_participants as available_spots,
    ROUND((e.current_participants / e.max_participants) * 100, 2) as fill_rate,
    e.price,
    e.event_status,
    COUNT(eb.event_booking_id) as total_bookings,
    SUM(eb.participants) as total_registered_participants
FROM events e
INNER JOIN hotels h ON e.hotel_id = h.hotel_id
LEFT JOIN event_bookings eb ON e.event_id = eb.event_id 
    AND eb.booking_status = 'Confirmed'
WHERE e.event_status = 'Upcoming'
AND e.event_date >= CURDATE()
GROUP BY e.event_id, h.hotel_name, h.city, e.event_name, e.description,
         e.event_date, e.start_time, e.end_time, e.venue, e.event_type,
         e.max_participants, e.current_participants, e.price, e.event_status
ORDER BY e.event_date ASC;

-- =====================================================
-- VIEW: vw_maintenance_dashboard
-- Maintenance schedule and status overview
-- =====================================================
CREATE OR REPLACE VIEW `vw_maintenance_dashboard` AS
SELECT 
    ms.schedule_id,
    h.hotel_name,
    r.room_number,
    rt.type_name as room_type,
    ms.maintenance_type,
    ms.scheduled_date,
    ms.completed_date,
    DATEDIFF(CURDATE(), ms.scheduled_date) as days_overdue,
    ms.priority,
    ms.status,
    CONCAT(s.first_name, ' ', s.last_name) as assigned_to_name,
    ms.description
FROM maintenance_schedule ms
INNER JOIN rooms r ON ms.room_id = r.room_id
INNER JOIN hotels h ON r.hotel_id = h.hotel_id
INNER JOIN room_types rt ON r.type_id = rt.type_id
LEFT JOIN staff s ON ms.assigned_to = s.staff_id
WHERE ms.status != 'Completed'
ORDER BY 
    CASE ms.priority
        WHEN 'Critical' THEN 1
        WHEN 'High' THEN 2
        WHEN 'Medium' THEN 3
        WHEN 'Low' THEN 4
    END,
    ms.scheduled_date ASC;

-- =====================================================
-- VIEW: vw_guest_loyalty_tiers
-- Guest loyalty statistics by tier
-- =====================================================
CREATE OR REPLACE VIEW `vw_guest_loyalty_tiers` AS
SELECT 
    g.membership_level,
    COUNT(DISTINCT g.guest_id) as total_guests,
    AVG(g.loyalty_points) as avg_loyalty_points,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    COALESCE(SUM(b.final_amount), 0) as total_revenue,
    COALESCE(AVG(b.final_amount), 0) as avg_booking_value,
    COALESCE(AVG(rv.rating), 0) as avg_rating
FROM guests g
LEFT JOIN bookings b ON g.guest_id = b.guest_id 
    AND b.booking_status = 'Completed'
LEFT JOIN reviews rv ON g.guest_id = rv.guest_id
WHERE g.is_active = 1
GROUP BY g.membership_level
ORDER BY 
    CASE g.membership_level
        WHEN 'Platinum' THEN 1
        WHEN 'Gold' THEN 2
        WHEN 'Silver' THEN 3
        WHEN 'Bronze' THEN 4
    END;
