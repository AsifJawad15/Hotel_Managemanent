-- SUBQUERIES AND SET OPERATIONS EXAMPLES
-- Run these queries in phpMyAdmin after running enhanced_smart_stay.sql

-- =====================================================
-- SUBQUERIES EXAMPLES
-- =====================================================

-- 1. Find hotels with above-average ratings
SELECT h.hotel_name, h.city, AVG(rev.rating) as avg_rating
FROM hotels h
JOIN reviews rev ON h.hotel_id = rev.hotel_id
GROUP BY h.hotel_id, h.hotel_name, h.city
HAVING AVG(rev.rating) > (
    SELECT AVG(rating) FROM reviews
);

-- 2. Find guests who have spent more than the average guest spending
SELECT g.name, g.email, 
       (SELECT SUM(final_amount) FROM bookings WHERE guest_id = g.guest_id) as total_spent
FROM guests g
WHERE (
    SELECT SUM(final_amount) 
    FROM bookings 
    WHERE guest_id = g.guest_id AND booking_status = 'Completed'
) > (
    SELECT AVG(guest_total) FROM (
        SELECT SUM(final_amount) as guest_total
        FROM bookings 
        WHERE booking_status = 'Completed'
        GROUP BY guest_id
    ) as avg_spending
);

-- 3. Find rooms that have never been booked
SELECT h.hotel_name, r.room_number, rt.type_name
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
WHERE NOT EXISTS (
    SELECT 1 FROM bookings b WHERE b.room_id = r.room_id
);

-- 4. Find hotels with the most expensive rooms in each city
SELECT h.city, h.hotel_name, r.room_number, r.price
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
WHERE r.price = (
    SELECT MAX(r2.price)
    FROM hotels h2
    JOIN rooms r2 ON h2.hotel_id = r2.hotel_id
    WHERE h2.city = h.city
);

-- 5. Find events with participation above average
SELECT e.event_name, h.hotel_name, e.current_participants,
       (SELECT AVG(current_participants) FROM events) as avg_participation
FROM events e
JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.current_participants > (
    SELECT AVG(current_participants) FROM events
);

-- 6. Correlated subquery: Guest's latest booking details
SELECT g.name, g.email,
       (SELECT h.hotel_name 
        FROM bookings b2 
        JOIN rooms r2 ON b2.room_id = r2.room_id
        JOIN hotels h ON r2.hotel_id = h.hotel_id
        WHERE b2.guest_id = g.guest_id 
        ORDER BY b2.created_at DESC 
        LIMIT 1) as latest_hotel,
       (SELECT b3.final_amount 
        FROM bookings b3 
        WHERE b3.guest_id = g.guest_id 
        ORDER BY b3.created_at DESC 
        LIMIT 1) as latest_amount
FROM guests g
WHERE EXISTS (
    SELECT 1 FROM bookings b WHERE b.guest_id = g.guest_id
);

-- 7. Find hotels with rooms in all room types (Division operation equivalent)
SELECT h.hotel_name
FROM hotels h
WHERE NOT EXISTS (
    SELECT rt.type_id
    FROM room_types rt
    WHERE NOT EXISTS (
        SELECT 1
        FROM rooms r
        WHERE r.hotel_id = h.hotel_id
        AND r.type_id = rt.type_id
    )
);

-- =====================================================
-- SET OPERATIONS (UNION, INTERSECT, EXCEPT equivalents)
-- =====================================================

-- 1. UNION: Combine guest emails and hotel emails
SELECT 'Guest' as user_type, name as full_name, email
FROM guests
UNION
SELECT 'Hotel' as user_type, hotel_name as full_name, email
FROM hotels
UNION
SELECT 'Admin' as user_type, full_name, email
FROM admins
WHERE email IS NOT NULL
ORDER BY user_type, full_name;

-- 2. UNION ALL: All booking dates (including duplicates)
SELECT check_in as booking_date, 'Check-in' as date_type
FROM bookings
UNION ALL
SELECT check_out as booking_date, 'Check-out' as date_type
FROM bookings
ORDER BY booking_date DESC;

-- 3. Find guests who have both room bookings and event bookings (INTERSECT equivalent)
SELECT g.guest_id, g.name, g.email
FROM guests g
WHERE g.guest_id IN (SELECT guest_id FROM bookings)
AND g.guest_id IN (SELECT guest_id FROM event_bookings)
ORDER BY g.name;

-- 4. Find guests who have room bookings but no event bookings (EXCEPT equivalent)
SELECT g.guest_id, g.name, g.email
FROM guests g
WHERE g.guest_id IN (SELECT guest_id FROM bookings)
AND g.guest_id NOT IN (SELECT guest_id FROM event_bookings)
ORDER BY g.name;

-- 5. Hotels that offer both rooms and events vs only rooms
SELECT 'Hotels with Rooms and Events' as category, COUNT(*) as count
FROM hotels h
WHERE h.hotel_id IN (SELECT DISTINCT hotel_id FROM rooms)
AND h.hotel_id IN (SELECT DISTINCT hotel_id FROM events)

UNION

SELECT 'Hotels with Only Rooms' as category, COUNT(*) as count
FROM hotels h
WHERE h.hotel_id IN (SELECT DISTINCT hotel_id FROM rooms)
AND h.hotel_id NOT IN (SELECT DISTINCT hotel_id FROM events)

UNION

SELECT 'Hotels with Only Events' as category, COUNT(*) as count
FROM hotels h
WHERE h.hotel_id NOT IN (SELECT DISTINCT hotel_id FROM rooms)
AND h.hotel_id IN (SELECT DISTINCT hotel_id FROM events);

-- 6. Comprehensive booking analysis using UNION
SELECT 
    'Room Bookings' as booking_type,
    COUNT(*) as total_count,
    SUM(final_amount) as total_revenue,
    AVG(final_amount) as average_amount
FROM bookings
WHERE booking_status = 'Completed'

UNION

SELECT 
    'Event Bookings' as booking_type,
    COUNT(*) as total_count,
    SUM(amount_paid) as total_revenue,
    AVG(amount_paid) as average_amount
FROM event_bookings
WHERE booking_status = 'Confirmed';

-- =====================================================
-- ADVANCED AGGREGATE FUNCTIONS
-- =====================================================

-- 1. Revenue analysis with window functions
SELECT 
    h.hotel_name,
    YEAR(b.check_in) as year,
    MONTH(b.check_in) as month,
    SUM(b.final_amount) as monthly_revenue,
    AVG(SUM(b.final_amount)) OVER (
        PARTITION BY h.hotel_id 
        ORDER BY YEAR(b.check_in), MONTH(b.check_in)
        ROWS BETWEEN 2 PRECEDING AND CURRENT ROW
    ) as rolling_3month_avg,
    LAG(SUM(b.final_amount), 1) OVER (
        PARTITION BY h.hotel_id 
        ORDER BY YEAR(b.check_in), MONTH(b.check_in)
    ) as prev_month_revenue,
    RANK() OVER (
        PARTITION BY YEAR(b.check_in), MONTH(b.check_in)
        ORDER BY SUM(b.final_amount) DESC
    ) as monthly_rank
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
JOIN bookings b ON r.room_id = b.room_id
WHERE b.booking_status = 'Completed'
GROUP BY h.hotel_id, h.hotel_name, YEAR(b.check_in), MONTH(b.check_in)
ORDER BY h.hotel_name, year DESC, month DESC;

-- 2. Customer segmentation analysis
SELECT 
    CASE 
        WHEN total_spent >= 2000 THEN 'VIP'
        WHEN total_spent >= 1000 THEN 'Premium'
        WHEN total_spent >= 500 THEN 'Regular'
        ELSE 'Basic'
    END as customer_segment,
    COUNT(*) as customer_count,
    AVG(total_spent) as avg_spending,
    MIN(total_spent) as min_spending,
    MAX(total_spent) as max_spending,
    SUM(total_spent) as segment_total_revenue,
    AVG(total_bookings) as avg_bookings_per_customer
FROM (
    SELECT 
        g.guest_id,
        g.name,
        SUM(b.final_amount) as total_spent,
        COUNT(b.booking_id) as total_bookings
    FROM guests g
    JOIN bookings b ON g.guest_id = b.guest_id
    WHERE b.booking_status = 'Completed'
    GROUP BY g.guest_id, g.name
) customer_stats
GROUP BY customer_segment
ORDER BY avg_spending DESC;

-- 3. Occupancy analysis with statistical functions
SELECT 
    h.hotel_name,
    COUNT(DISTINCT DATE(b.check_in)) as occupied_days,
    COUNT(DISTINCT r.room_id) as total_rooms,
    AVG(daily_occupancy.occupied_rooms) as avg_daily_occupancy,
    MAX(daily_occupancy.occupied_rooms) as max_daily_occupancy,
    MIN(daily_occupancy.occupied_rooms) as min_daily_occupancy,
    STDDEV(daily_occupancy.occupied_rooms) as occupancy_variance,
    ROUND(
        AVG(daily_occupancy.occupied_rooms) / COUNT(DISTINCT r.room_id) * 100, 2
    ) as average_occupancy_rate
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN (
    SELECT 
        r2.hotel_id,
        DATE(b2.check_in) as occupancy_date,
        COUNT(DISTINCT b2.room_id) as occupied_rooms
    FROM rooms r2
    JOIN bookings b2 ON r2.room_id = b2.room_id
    WHERE b2.booking_status = 'Completed'
    GROUP BY r2.hotel_id, DATE(b2.check_in)
) daily_occupancy ON h.hotel_id = daily_occupancy.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed'
GROUP BY h.hotel_id, h.hotel_name
ORDER BY average_occupancy_rate DESC;

-- 4. Seasonal demand analysis with percentiles
SELECT 
    GetSeason(b.check_in) as season,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.final_amount) as total_revenue,
    AVG(b.final_amount) as avg_booking_value,
    PERCENTILE_CONT(0.25) WITHIN GROUP (ORDER BY b.final_amount) as q1_amount,
    PERCENTILE_CONT(0.50) WITHIN GROUP (ORDER BY b.final_amount) as median_amount,
    PERCENTILE_CONT(0.75) WITHIN GROUP (ORDER BY b.final_amount) as q3_amount,
    PERCENTILE_CONT(0.95) WITHIN GROUP (ORDER BY b.final_amount) as p95_amount
FROM bookings b
WHERE b.booking_status = 'Completed'
AND b.check_in >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
GROUP BY GetSeason(b.check_in)
ORDER BY total_revenue DESC;

-- =====================================================
-- COMPLEX JOIN OPERATIONS
-- =====================================================

-- 1. Full outer join equivalent (LEFT JOIN + RIGHT JOIN + UNION)
SELECT 
    COALESCE(g.name, 'No Guest') as guest_name,
    COALESCE(h.hotel_name, 'No Hotel') as hotel_name,
    b.booking_id,
    b.final_amount
FROM guests g
LEFT JOIN bookings b ON g.guest_id = b.guest_id
LEFT JOIN rooms r ON b.room_id = r.room_id
LEFT JOIN hotels h ON r.hotel_id = h.hotel_id

UNION

SELECT 
    COALESCE(g.name, 'No Guest') as guest_name,
    COALESCE(h.hotel_name, 'No Hotel') as hotel_name,
    b.booking_id,
    b.final_amount
FROM hotels h
RIGHT JOIN rooms r ON h.hotel_id = r.hotel_id
RIGHT JOIN bookings b ON r.room_id = b.room_id
RIGHT JOIN guests g ON b.guest_id = g.guest_id
WHERE h.hotel_id IS NULL OR g.guest_id IS NULL;

-- 2. Self-join: Find guest pairs who stayed at the same hotel
SELECT DISTINCT
    g1.name as guest1,
    g2.name as guest2,
    h.hotel_name,
    b1.check_in as guest1_checkin,
    b2.check_in as guest2_checkin
FROM bookings b1
JOIN bookings b2 ON b1.booking_id < b2.booking_id
JOIN rooms r1 ON b1.room_id = r1.room_id
JOIN rooms r2 ON b2.room_id = r2.room_id
JOIN hotels h ON r1.hotel_id = h.hotel_id AND r2.hotel_id = h.hotel_id
JOIN guests g1 ON b1.guest_id = g1.guest_id
JOIN guests g2 ON b2.guest_id = g2.guest_id
WHERE b1.booking_status = 'Completed'
AND b2.booking_status = 'Completed'
ORDER BY h.hotel_name, b1.check_in;

-- 3. Cross join for generating all possible room-guest combinations (for analysis)
SELECT 
    g.name as guest_name,
    g.membership_level,
    h.hotel_name,
    r.room_number,
    rt.type_name,
    r.price,
    CASE g.membership_level
        WHEN 'Platinum' THEN r.price * 0.85
        WHEN 'Gold' THEN r.price * 0.90
        WHEN 'Silver' THEN r.price * 0.95
        ELSE r.price
    END as discounted_price
FROM guests g
CROSS JOIN hotels h
CROSS JOIN rooms r ON h.hotel_id = r.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
WHERE g.membership_level IN ('Gold', 'Platinum')
AND h.city IN ('New York', 'Miami')
AND r.is_active = TRUE
ORDER BY g.name, h.hotel_name, r.price;