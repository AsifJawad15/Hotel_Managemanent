-- ============================================================================
-- SMARTSTAY ADMIN QUERY REFERENCE
-- ============================================================================
-- File: 08_admin_queries.sql
-- Purpose: Collection of useful queries for admin database interface
-- Usage: Copy and paste these queries into the admin database interface
-- ============================================================================

USE `smart_stay`;

-- ============================================================================
-- SECTION 1: BASIC DATA VIEWING QUERIES
-- ============================================================================

-- Query 1.1: View All Hotels
-- Purpose: Display all registered hotels with basic information
SELECT * FROM hotels 
ORDER BY hotel_name 
LIMIT 10;

-- Query 1.2: View Recent Guests
-- Purpose: Display recently registered guests
SELECT * FROM guests 
ORDER BY created_at DESC 
LIMIT 10;

-- Query 1.3: View Recent Bookings with Details
-- Purpose: Display recent bookings with guest, hotel, and room information
SELECT 
    b.booking_id,
    g.name as guest_name,
    h.hotel_name,
    r.room_number,
    b.check_in,
    b.check_out,
    b.booking_status,
    b.final_amount
FROM bookings b
JOIN guests g ON b.guest_id = g.guest_id
JOIN rooms r ON b.room_id = r.room_id
JOIN hotels h ON r.hotel_id = h.hotel_id
ORDER BY b.created_at DESC
LIMIT 10;

-- ============================================================================
-- SECTION 2: REVENUE ANALYSIS QUERIES
-- ============================================================================

-- Query 2.1: Revenue by Hotel
-- Purpose: Calculate total revenue, bookings, and average booking value per hotel
SELECT 
    h.hotel_name,
    SUM(b.final_amount) as total_revenue,
    COUNT(b.booking_id) as total_bookings,
    AVG(b.final_amount) as avg_booking_value
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id 
    AND b.booking_status = 'Completed'
GROUP BY h.hotel_id, h.hotel_name
ORDER BY total_revenue DESC;

-- Query 2.2: Monthly Revenue Trend
-- Purpose: Show revenue and bookings by month for the last 12 months
SELECT 
    YEAR(b.check_in) as year,
    MONTH(b.check_in) as month,
    DATE_FORMAT(b.check_in, '%Y-%m') as year_month,
    SUM(b.final_amount) as monthly_revenue,
    COUNT(b.booking_id) as monthly_bookings
FROM bookings b
WHERE b.booking_status = 'Completed'
GROUP BY YEAR(b.check_in), MONTH(b.check_in)
ORDER BY year DESC, month DESC
LIMIT 12;

-- Query 2.3: Daily Revenue (Last 30 Days)
-- Purpose: Show daily revenue for performance tracking
SELECT 
    DATE(b.check_in) as booking_date,
    COUNT(b.booking_id) as daily_bookings,
    SUM(b.final_amount) as daily_revenue,
    AVG(b.final_amount) as avg_booking_value
FROM bookings b
WHERE b.booking_status = 'Completed'
    AND b.check_in >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(b.check_in)
ORDER BY booking_date DESC;

-- ============================================================================
-- SECTION 3: ROOM ANALYSIS QUERIES
-- ============================================================================

-- Query 3.1: Rooms by Type and Hotel
-- Purpose: Count rooms by type for each hotel with average pricing
SELECT 
    h.hotel_name,
    rt.type_name,
    COUNT(r.room_id) as total_rooms,
    AVG(r.price) as avg_price,
    MIN(r.price) as min_price,
    MAX(r.price) as max_price
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.is_active = TRUE
GROUP BY h.hotel_id, h.hotel_name, rt.type_id, rt.type_name
ORDER BY h.hotel_name, rt.type_name;

-- Query 3.2: Room Occupancy Rate
-- Purpose: Calculate occupancy rate for each hotel
SELECT 
    h.hotel_name,
    COUNT(DISTINCT r.room_id) as total_rooms,
    COUNT(DISTINCT CASE 
        WHEN b.booking_status IN ('Confirmed', 'CheckedIn') 
        AND CURDATE() BETWEEN b.check_in AND b.check_out 
        THEN r.room_id 
    END) as occupied_rooms,
    ROUND(
        (COUNT(DISTINCT CASE 
            WHEN b.booking_status IN ('Confirmed', 'CheckedIn') 
            AND CURDATE() BETWEEN b.check_in AND b.check_out 
            THEN r.room_id 
        END) * 100.0) / COUNT(DISTINCT r.room_id), 
        2
    ) as occupancy_rate_percent
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id
WHERE r.is_active = TRUE
GROUP BY h.hotel_id, h.hotel_name
ORDER BY occupancy_rate_percent DESC;

-- Query 3.3: Available Rooms Right Now
-- Purpose: Find rooms that are currently available
SELECT 
    h.hotel_name,
    r.room_number,
    rt.type_name,
    r.price,
    r.maintenance_status
FROM rooms r
JOIN hotels h ON r.hotel_id = h.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.is_active = TRUE
    AND r.maintenance_status = 'Available'
    AND NOT EXISTS (
        SELECT 1 FROM bookings b
        WHERE b.room_id = r.room_id
            AND b.booking_status IN ('Confirmed', 'CheckedIn')
            AND CURDATE() BETWEEN b.check_in AND b.check_out
    )
ORDER BY h.hotel_name, r.room_number;

-- ============================================================================
-- SECTION 4: GUEST ANALYSIS QUERIES
-- ============================================================================

-- Query 4.1: Top Spending Guests
-- Purpose: Identify most valuable customers
SELECT 
    g.name,
    g.email,
    g.membership_level,
    g.loyalty_points,
    SUM(b.final_amount) as total_spent,
    COUNT(b.booking_id) as total_bookings,
    AVG(b.final_amount) as avg_booking_value,
    MAX(b.created_at) as last_booking_date
FROM guests g
JOIN bookings b ON g.guest_id = b.guest_id
WHERE b.booking_status = 'Completed'
GROUP BY g.guest_id, g.name, g.email, g.membership_level, g.loyalty_points
ORDER BY total_spent DESC
LIMIT 10;

-- Query 4.2: Guest Membership Distribution
-- Purpose: Show distribution of guests across membership levels
SELECT 
    membership_level,
    COUNT(*) as guest_count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM guests), 2) as percentage,
    AVG(loyalty_points) as avg_points,
    MIN(loyalty_points) as min_points,
    MAX(loyalty_points) as max_points
FROM guests
WHERE is_active = TRUE
GROUP BY membership_level
ORDER BY 
    CASE membership_level
        WHEN 'Platinum' THEN 1
        WHEN 'Gold' THEN 2
        WHEN 'Silver' THEN 3
        WHEN 'Bronze' THEN 4
    END;

-- Query 4.3: Guest Activity Analysis
-- Purpose: Analyze guest booking patterns
SELECT 
    g.guest_id,
    g.name,
    g.membership_level,
    COUNT(b.booking_id) as total_bookings,
    SUM(CASE WHEN b.booking_status = 'Completed' THEN 1 ELSE 0 END) as completed_bookings,
    SUM(CASE WHEN b.booking_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
    SUM(CASE WHEN b.booking_status = 'Completed' THEN b.final_amount ELSE 0 END) as total_spent,
    DATEDIFF(MAX(b.check_out), MIN(b.check_in)) as total_nights_stayed
FROM guests g
JOIN bookings b ON g.guest_id = b.guest_id
GROUP BY g.guest_id, g.name, g.membership_level
HAVING total_bookings > 0
ORDER BY total_bookings DESC
LIMIT 20;

-- ============================================================================
-- SECTION 5: HOTEL PERFORMANCE QUERIES
-- ============================================================================

-- Query 5.1: Hotel Ratings Summary
-- Purpose: Show average ratings and review counts for each hotel
SELECT 
    h.hotel_name,
    h.city,
    AVG(r.rating) as avg_rating,
    COUNT(r.review_id) as total_reviews,
    COUNT(CASE WHEN r.rating >= 4 THEN 1 END) as positive_reviews,
    COUNT(CASE WHEN r.rating <= 2 THEN 1 END) as negative_reviews
FROM hotels h
LEFT JOIN reviews r ON h.hotel_id = r.hotel_id AND r.is_approved = 1
GROUP BY h.hotel_id, h.hotel_name, h.city
HAVING total_reviews > 0
ORDER BY avg_rating DESC, total_reviews DESC;

-- Query 5.2: Hotel Booking Performance
-- Purpose: Compare booking performance across hotels
SELECT 
    h.hotel_name,
    COUNT(DISTINCT r.room_id) as total_rooms,
    COUNT(b.booking_id) as total_bookings,
    SUM(CASE WHEN b.booking_status = 'Completed' THEN b.final_amount ELSE 0 END) as completed_revenue,
    SUM(CASE WHEN b.booking_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
    ROUND(
        COUNT(b.booking_id) * 1.0 / COUNT(DISTINCT r.room_id),
        2
    ) as bookings_per_room
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id
WHERE h.is_active = TRUE
GROUP BY h.hotel_id, h.hotel_name
ORDER BY completed_revenue DESC;

-- Query 5.3: Hotel Service Offerings
-- Purpose: View services offered by each hotel
SELECT 
    h.hotel_name,
    s.service_name,
    s.service_type,
    s.price,
    CASE 
        WHEN s.price = 0 THEN 'Included'
        ELSE CONCAT('$', s.price)
    END as price_display,
    s.description
FROM hotels h
JOIN services s ON h.hotel_id = s.hotel_id
WHERE s.is_active = TRUE
ORDER BY h.hotel_name, s.service_type, s.service_name;

-- ============================================================================
-- SECTION 6: EVENT ANALYSIS QUERIES
-- ============================================================================

-- Query 6.1: Upcoming Events
-- Purpose: Display upcoming hotel events with details
SELECT 
    e.event_name,
    h.hotel_name,
    e.event_date,
    e.start_time,
    e.end_time,
    e.event_type,
    e.max_participants,
    e.current_participants,
    (e.max_participants - e.current_participants) as available_spots,
    e.price,
    e.event_status
FROM events e
JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.event_status = 'Upcoming'
    AND e.event_date >= CURDATE()
ORDER BY e.event_date ASC, e.start_time ASC
LIMIT 10;

-- Query 6.2: Event Revenue Analysis
-- Purpose: Calculate revenue generated from events
SELECT 
    h.hotel_name,
    e.event_name,
    e.event_date,
    e.current_participants,
    e.price,
    (e.current_participants * e.price) as total_revenue,
    e.event_status
FROM events e
JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.price > 0
ORDER BY total_revenue DESC;

-- Query 6.3: Event Participation Rate
-- Purpose: Analyze event popularity and capacity utilization
SELECT 
    h.hotel_name,
    e.event_name,
    e.event_type,
    e.max_participants,
    e.current_participants,
    ROUND(
        (e.current_participants * 100.0) / e.max_participants,
        2
    ) as capacity_percent,
    e.event_status
FROM events e
JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.max_participants > 0
ORDER BY capacity_percent DESC;

-- ============================================================================
-- SECTION 7: PAYMENT AND BOOKING STATUS QUERIES
-- ============================================================================

-- Query 7.1: Pending Payments
-- Purpose: Track bookings with pending payment status
SELECT 
    b.booking_id,
    g.name as guest_name,
    g.email,
    h.hotel_name,
    r.room_number,
    b.check_in,
    b.check_out,
    b.final_amount,
    b.payment_status,
    b.booking_status,
    DATEDIFF(CURDATE(), b.created_at) as days_since_booking
FROM bookings b
JOIN guests g ON b.guest_id = g.guest_id
JOIN rooms r ON b.room_id = r.room_id
JOIN hotels h ON r.hotel_id = h.hotel_id
WHERE b.payment_status = 'Pending'
ORDER BY b.created_at ASC;

-- Query 7.2: Booking Status Summary
-- Purpose: Overview of all booking statuses
SELECT 
    booking_status,
    COUNT(*) as total_count,
    SUM(final_amount) as total_amount,
    AVG(final_amount) as avg_amount
FROM bookings
GROUP BY booking_status
ORDER BY total_count DESC;

-- Query 7.3: Payment Status by Hotel
-- Purpose: Track payment status across hotels
SELECT 
    h.hotel_name,
    b.payment_status,
    COUNT(b.booking_id) as booking_count,
    SUM(b.final_amount) as total_amount
FROM bookings b
JOIN rooms r ON b.room_id = r.room_id
JOIN hotels h ON r.hotel_id = h.hotel_id
GROUP BY h.hotel_id, h.hotel_name, b.payment_status
ORDER BY h.hotel_name, b.payment_status;

-- ============================================================================
-- SECTION 8: DATE-BASED ANALYSIS QUERIES
-- ============================================================================

-- Query 8.1: Bookings by Day of Week
-- Purpose: Identify popular booking days
SELECT 
    DAYNAME(check_in) as day_of_week,
    COUNT(*) as booking_count,
    AVG(final_amount) as avg_amount,
    SUM(final_amount) as total_revenue
FROM bookings
WHERE booking_status = 'Completed'
GROUP BY DAYNAME(check_in), DAYOFWEEK(check_in)
ORDER BY DAYOFWEEK(check_in);

-- Query 8.2: Seasonal Booking Patterns
-- Purpose: Analyze bookings by season
SELECT 
    CASE 
        WHEN MONTH(check_in) IN (12, 1, 2) THEN 'Winter'
        WHEN MONTH(check_in) IN (3, 4, 5) THEN 'Spring'
        WHEN MONTH(check_in) IN (6, 7, 8) THEN 'Summer'
        ELSE 'Fall'
    END as season,
    COUNT(*) as booking_count,
    AVG(final_amount) as avg_amount,
    SUM(final_amount) as total_revenue
FROM bookings
WHERE booking_status = 'Completed'
GROUP BY season
ORDER BY 
    CASE season
        WHEN 'Spring' THEN 1
        WHEN 'Summer' THEN 2
        WHEN 'Fall' THEN 3
        WHEN 'Winter' THEN 4
    END;

-- Query 8.3: Booking Lead Time Analysis
-- Purpose: Analyze how far in advance guests book
SELECT 
    CASE 
        WHEN DATEDIFF(check_in, created_at) <= 7 THEN '0-7 days'
        WHEN DATEDIFF(check_in, created_at) <= 14 THEN '8-14 days'
        WHEN DATEDIFF(check_in, created_at) <= 30 THEN '15-30 days'
        WHEN DATEDIFF(check_in, created_at) <= 60 THEN '31-60 days'
        ELSE '60+ days'
    END as lead_time,
    COUNT(*) as booking_count,
    AVG(final_amount) as avg_amount
FROM bookings
WHERE booking_status IN ('Confirmed', 'Completed')
    AND check_in >= created_at
GROUP BY lead_time
ORDER BY 
    CASE lead_time
        WHEN '0-7 days' THEN 1
        WHEN '8-14 days' THEN 2
        WHEN '15-30 days' THEN 3
        WHEN '31-60 days' THEN 4
        ELSE 5
    END;

-- ============================================================================
-- SECTION 9: CUSTOM FUNCTION USAGE QUERIES
-- ============================================================================

-- Query 9.1: Season Check for Current and Future Dates
-- Purpose: Test GetSeason function with different dates
SELECT 
    CURDATE() as date_checked,
    GetSeason(CURDATE()) as current_season,
    GetSeason('2025-12-25') as christmas_season,
    GetSeason('2025-07-15') as summer_season,
    GetSeason('2025-10-31') as halloween_season;

-- Query 9.2: Dynamic Pricing Comparison
-- Purpose: Compare base price with dynamic pricing
SELECT 
    r.room_id,
    h.hotel_name,
    r.room_number,
    rt.type_name,
    r.price as base_price,
    CalculateDynamicPrice(r.price, '2025-12-25', rt.type_name) as christmas_price,
    CalculateDynamicPrice(r.price, '2025-07-15', rt.type_name) as summer_price,
    CalculateDynamicPrice(r.price, '2025-03-15', rt.type_name) as spring_price
FROM rooms r
JOIN hotels h ON r.hotel_id = h.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.is_active = TRUE
LIMIT 10;

-- Query 9.3: Guest Age Analysis
-- Purpose: Calculate ages of guests using CalculateAge function
SELECT 
    guest_id,
    name,
    date_of_birth,
    CalculateAge(date_of_birth) as age,
    membership_level,
    loyalty_points
FROM guests
WHERE date_of_birth IS NOT NULL
ORDER BY age DESC
LIMIT 10;

-- ============================================================================
-- SECTION 10: ADVANCED ANALYTICAL QUERIES
-- ============================================================================

-- Query 10.1: Guest Lifetime Value (LTV)
-- Purpose: Calculate total value of each guest over their lifetime
SELECT 
    g.guest_id,
    g.name,
    g.membership_level,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.final_amount) as lifetime_value,
    AVG(b.final_amount) as avg_booking_value,
    MIN(b.created_at) as first_booking,
    MAX(b.created_at) as last_booking,
    DATEDIFF(MAX(b.created_at), MIN(b.created_at)) as customer_lifetime_days
FROM guests g
JOIN bookings b ON g.guest_id = b.guest_id
WHERE b.booking_status = 'Completed'
GROUP BY g.guest_id, g.name, g.membership_level
HAVING total_bookings > 1
ORDER BY lifetime_value DESC
LIMIT 20;

-- Query 10.2: Room Type Performance
-- Purpose: Analyze which room types generate most revenue
SELECT 
    rt.type_name,
    COUNT(DISTINCT r.room_id) as total_rooms,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.final_amount) as total_revenue,
    AVG(b.final_amount) as avg_booking_value,
    ROUND(
        SUM(b.final_amount) / COUNT(DISTINCT r.room_id),
        2
    ) as revenue_per_room
FROM room_types rt
JOIN rooms r ON rt.type_id = r.type_id
LEFT JOIN bookings b ON r.room_id = b.room_id 
    AND b.booking_status = 'Completed'
GROUP BY rt.type_id, rt.type_name
ORDER BY total_revenue DESC;

-- Query 10.3: Cancellation Rate Analysis
-- Purpose: Track cancellation rates by hotel
SELECT 
    h.hotel_name,
    COUNT(b.booking_id) as total_bookings,
    SUM(CASE WHEN b.booking_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
    SUM(CASE WHEN b.booking_status = 'Completed' THEN 1 ELSE 0 END) as completed_bookings,
    ROUND(
        (SUM(CASE WHEN b.booking_status = 'Cancelled' THEN 1 ELSE 0 END) * 100.0) / 
        COUNT(b.booking_id),
        2
    ) as cancellation_rate_percent
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
JOIN bookings b ON r.room_id = b.room_id
GROUP BY h.hotel_id, h.hotel_name
HAVING total_bookings > 0
ORDER BY cancellation_rate_percent DESC;

-- ============================================================================
-- SECTION 11: MAINTENANCE AND SYSTEM QUERIES
-- ============================================================================

-- Query 11.1: Rooms Needing Maintenance
-- Purpose: Identify rooms with maintenance issues
SELECT 
    h.hotel_name,
    r.room_number,
    rt.type_name,
    r.maintenance_status,
    r.updated_at as last_updated
FROM rooms r
JOIN hotels h ON r.hotel_id = h.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.maintenance_status != 'Available'
ORDER BY h.hotel_name, r.room_number;

-- Query 11.2: Inactive Records Count
-- Purpose: Count inactive records across tables
SELECT 
    'Hotels' as table_name,
    COUNT(*) as inactive_count
FROM hotels 
WHERE is_active = FALSE
UNION ALL
SELECT 
    'Guests' as table_name,
    COUNT(*) as inactive_count
FROM guests 
WHERE is_active = FALSE
UNION ALL
SELECT 
    'Rooms' as table_name,
    COUNT(*) as inactive_count
FROM rooms 
WHERE is_active = FALSE
UNION ALL
SELECT 
    'Services' as table_name,
    COUNT(*) as inactive_count
FROM services 
WHERE is_active = FALSE;

-- Query 11.3: Database Statistics Summary
-- Purpose: Get overview of database size and record counts
SELECT 
    'Hotels' as entity,
    COUNT(*) as total_count,
    SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END) as active_count
FROM hotels
UNION ALL
SELECT 
    'Guests',
    COUNT(*),
    SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END)
FROM guests
UNION ALL
SELECT 
    'Rooms',
    COUNT(*),
    SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END)
FROM rooms
UNION ALL
SELECT 
    'Bookings',
    COUNT(*),
    SUM(CASE WHEN booking_status != 'Cancelled' THEN 1 ELSE 0 END)
FROM bookings
UNION ALL
SELECT 
    'Events',
    COUNT(*),
    SUM(CASE WHEN event_status != 'Cancelled' THEN 1 ELSE 0 END)
FROM events;

-- ============================================================================
-- END OF ADMIN QUERIES REFERENCE
-- ============================================================================
-- Note: These queries are optimized for analysis and reporting.
-- Always test queries on a development database before using in production.
-- For large datasets, consider adding appropriate LIMIT clauses.
-- ============================================================================
