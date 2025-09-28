# Smart Stay Hotel Management System - Database Features Documentation

## Overview
This enhanced hotel management system includes comprehensive database features showcasing advanced SQL operations, stored procedures, functions, views, triggers, and real-time analytics.

## Database Structure Enhancement

### Tables Created/Enhanced
1. **guests** - Enhanced with loyalty points, membership levels, demographics
2. **hotels** - Added location, ratings, amenities, operational details
3. **rooms** - Linked to room types, enhanced with amenities and status
4. **room_types** - New table for standardized room categorization
5. **bookings** - Enhanced with pricing breakdown, guest count, status tracking
6. **events** - Expanded with detailed event management
7. **event_bookings** - New table for event registration management
8. **hotel_images** - Enhanced image management with categorization
9. **reviews** - New table for guest feedback and ratings
10. **payments** - New table for payment tracking
11. **services** - New table for additional hotel services
12. **service_bookings** - Service booking management
13. **staff** - Hotel staff management
14. **system_logs** - Activity logging and audit trail

### DDL Features Implemented

#### Constraints Added:
- **Primary Keys**: All tables have proper primary keys
- **Foreign Keys**: Referential integrity across all related tables
- **Check Constraints**: 
  - Rating values (1.0 to 5.0)
  - Positive prices and amounts
  - Valid date ranges (check-out > check-in)
  - Occupancy limits
- **Unique Constraints**: Email uniqueness, hotel-room combinations
- **Default Values**: Timestamps, boolean flags, status enums

#### Indexes Created:
- **Performance Indexes**: On frequently queried columns
- **Composite Indexes**: For complex queries (hotel_id, dates, status)
- **Text Indexes**: For search functionality on names and descriptions

### DML Operations

#### Complex INSERT Operations:
```sql
-- Booking with full pricing calculation
INSERT INTO bookings (guest_id, room_id, check_in, check_out, adults, children, 
    total_amount, discount_amount, tax_amount, final_amount, booking_status, payment_status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', 'Pending');

-- Event creation with participant table
INSERT INTO events (...) VALUES (...);
CREATE TABLE hotel{hotel_id}_event{event_id} (booking_id INT AUTO_INCREMENT PRIMARY KEY, guest_id INT);
```

#### UPDATE Operations:
- Dynamic price updates based on demand
- Loyalty point calculations
- Membership level upgrades
- Room availability status
- Event participant counts

#### DELETE Operations:
- Cascading deletes with proper cleanup
- Archive functionality instead of hard deletes
- Referential integrity maintenance

### Advanced SELECT Queries

#### Aggregate Functions:
```sql
-- Revenue analysis with multiple aggregates
SELECT 
    h.hotel_name,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.final_amount) as total_revenue,
    AVG(b.final_amount) as avg_booking_value,
    MIN(b.final_amount) as min_booking,
    MAX(b.final_amount) as max_booking,
    STDDEV(b.final_amount) as revenue_variance
FROM hotels h
JOIN rooms r ON h.hotel_id = r.hotel_id
JOIN bookings b ON r.room_id = b.room_id
GROUP BY h.hotel_id, h.hotel_name
ORDER BY total_revenue DESC;
```

#### Window Functions:
```sql
-- Revenue ranking and trends
SELECT 
    hotel_name,
    monthly_revenue,
    RANK() OVER (ORDER BY monthly_revenue DESC) as revenue_rank,
    LAG(monthly_revenue) OVER (ORDER BY month) as prev_month_revenue,
    AVG(monthly_revenue) OVER (ORDER BY month ROWS 2 PRECEDING) as rolling_avg
FROM monthly_revenue_data;
```

### Subqueries

#### Correlated Subqueries:
```sql
-- Hotels with above-average ratings
SELECT h.hotel_name 
FROM hotels h 
WHERE (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.hotel_id) > 
      (SELECT AVG(rating) FROM reviews);
```

#### Existence Subqueries:
```sql
-- Guests with bookings but no reviews
SELECT g.name FROM guests g 
WHERE EXISTS (SELECT 1 FROM bookings WHERE guest_id = g.guest_id)
AND NOT EXISTS (SELECT 1 FROM reviews WHERE guest_id = g.guest_id);
```

### Set Operations

#### UNION Operations:
```sql
-- Combined user listing
SELECT 'Guest' as type, name, email FROM guests
UNION
SELECT 'Hotel' as type, hotel_name, email FROM hotels
UNION  
SELECT 'Admin' as type, full_name, email FROM admins;
```

#### INTERSECT Equivalent:
```sql
-- Guests with both room and event bookings
SELECT guest_id FROM guests
WHERE guest_id IN (SELECT guest_id FROM bookings)
AND guest_id IN (SELECT guest_id FROM event_bookings);
```

### Views Created

#### 1. hotel_performance
```sql
CREATE VIEW hotel_performance AS
SELECT 
    h.hotel_id, h.hotel_name, h.city, h.star_rating,
    COUNT(DISTINCT r.room_id) as total_rooms,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    AVG(rev.rating) as average_rating,
    SUM(b.final_amount) as total_revenue
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id
LEFT JOIN reviews rev ON h.hotel_id = rev.hotel_id
GROUP BY h.hotel_id;
```

#### 2. guest_booking_history
Comprehensive guest booking analysis with room details and financial information.

#### 3. room_occupancy  
Real-time room availability and occupancy status.

#### 4. monthly_revenue_report
Time-series revenue analysis with year-over-year comparisons.

#### 5. event_participation
Event booking analysis with capacity utilization.

### Stored Procedures

#### 1. CalculateLoyaltyPoints(guest_id, amount)
```sql
-- Automatic loyalty calculation with level upgrades
DELIMITER //
CREATE PROCEDURE CalculateLoyaltyPoints(IN guest_id_param INT, IN booking_amount DECIMAL(10,2))
BEGIN
    DECLARE current_points INT;
    DECLARE new_points INT;
    DECLARE new_level VARCHAR(20);
    
    SELECT loyalty_points INTO current_points FROM guests WHERE guest_id = guest_id_param;
    SET new_points = current_points + FLOOR(booking_amount);
    
    CASE 
        WHEN new_points >= 5000 THEN SET new_level = 'Platinum';
        WHEN new_points >= 2000 THEN SET new_level = 'Gold';
        WHEN new_points >= 500 THEN SET new_level = 'Silver';
        ELSE SET new_level = 'Bronze';
    END CASE;
    
    UPDATE guests SET loyalty_points = new_points, membership_level = new_level 
    WHERE guest_id = guest_id_param;
END//
```

#### 2. GetAvailableRooms(hotel_id, check_in, check_out, room_type)
Smart room availability search with filtering and optimization.

#### 3. GenerateOccupancyReport(hotel_id, start_date, end_date)
Detailed occupancy analysis with daily breakdown and percentage calculations.

#### 4. UpdateRoomPricesBasedOnDemand()
Automated pricing algorithm using cursor to iterate through all rooms and adjust prices based on booking history.

#### 5. ProcessLoyaltyUpgrades()
Batch processing of membership upgrades with complex business logic.

#### 6. ScheduleRoomMaintenance()
Automated maintenance scheduling based on usage patterns and cleanliness requirements.

### Custom Functions

#### 1. CalculateDynamicPrice(room_id, check_in_date, nights)
```sql
-- Dynamic pricing based on multiple factors
CREATE FUNCTION CalculateDynamicPrice(room_id_param INT, check_in_date DATE, nights INT) 
RETURNS DECIMAL(10,2)
BEGIN
    DECLARE base_price DECIMAL(10,2);
    DECLARE season_multiplier DECIMAL(3,2) DEFAULT 1.00;
    DECLARE demand_multiplier DECIMAL(3,2) DEFAULT 1.00;
    DECLARE weekday_multiplier DECIMAL(3,2) DEFAULT 1.00;
    
    SELECT price INTO base_price FROM rooms WHERE room_id = room_id_param;
    
    -- Season, demand, and day-of-week adjustments
    -- Complex business logic implementation
    
    RETURN ROUND(base_price * season_multiplier * demand_multiplier * weekday_multiplier, 2);
END
```

#### 2. CalculateGuestSatisfactionScore(guest_id)
Multi-factor guest satisfaction calculation based on reviews, booking history, and loyalty.

#### 3. GetOptimalRoomAssignment(hotel_id, guest_id, check_in, check_out, room_type)
AI-like room recommendation based on guest profile and preferences.

### Triggers

#### 1. Hotel Room Count Management
```sql
CREATE TRIGGER update_hotel_room_count 
AFTER INSERT ON rooms FOR EACH ROW
BEGIN
    UPDATE hotels SET total_rooms = (
        SELECT COUNT(*) FROM rooms 
        WHERE hotel_id = NEW.hotel_id AND is_active = TRUE
    ) WHERE hotel_id = NEW.hotel_id;
END
```

#### 2. Booking Change Logging
Automatic audit trail for all booking modifications.

#### 3. Event Participant Count Updates
Real-time participant count maintenance for events.

#### 4. Loyalty Points Auto-Calculation
Automatic loyalty point calculation when bookings are completed.

### JOIN Operations Examples

#### Complex Multi-Table JOINs:
```sql
-- Comprehensive booking analysis
SELECT 
    g.name as guest_name,
    h.hotel_name,
    r.room_number,
    rt.type_name,
    b.check_in,
    b.final_amount,
    rev.rating
FROM guests g
JOIN bookings b ON g.guest_id = b.guest_id
JOIN rooms r ON b.room_id = r.room_id
JOIN hotels h ON r.hotel_id = h.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
LEFT JOIN reviews rev ON b.booking_id = rev.booking_id
WHERE b.booking_status = 'Completed';
```

#### Self-JOIN Examples:
```sql
-- Find guest pairs who stayed at same hotel
SELECT DISTINCT g1.name, g2.name, h.hotel_name
FROM bookings b1
JOIN bookings b2 ON b1.booking_id < b2.booking_id
JOIN rooms r1 ON b1.room_id = r1.room_id
JOIN rooms r2 ON b2.room_id = r2.room_id
JOIN hotels h ON r1.hotel_id = h.hotel_id AND r2.hotel_id = h.hotel_id
JOIN guests g1 ON b1.guest_id = g1.guest_id  
JOIN guests g2 ON b2.guest_id = g2.guest_id;
```

## Frontend Integration

### Admin Interface Features:
1. **Database Query Interface** (`admin_database.php`)
   - Execute custom SQL queries
   - Run stored procedures
   - Call custom functions
   - View system performance

2. **Analytics Dashboard** (`admin_reports.php`)
   - Real-time statistics
   - Performance metrics
   - Revenue analysis
   - Occupancy reports

### Enhanced Booking System:
1. **Dynamic Pricing** - Real-time price calculation
2. **Membership Discounts** - Automatic discount application
3. **Availability Checking** - Conflict prevention
4. **Guest Preferences** - Room assignment optimization

## Files Created/Modified

### Database Files:
1. `db/enhanced_smart_stay.sql` - Complete database schema
2. `db/advanced_queries.sql` - Complex query examples
3. `db/plsql_procedures.sql` - Stored procedures and functions

### Admin Interface:
1. `pages/admin/admin_database.php` - Query interface
2. `pages/admin/admin_reports.php` - Analytics dashboard  
3. `pages/admin/admin_home.php` - Enhanced dashboard

### Guest Interface:
1. `pages/guest/guest_book_room_dates.php` - Enhanced booking

## Usage Instructions

### Database Setup:
1. Run `db/enhanced_smart_stay.sql` in phpMyAdmin SQL tab
2. Optionally run `db/advanced_queries.sql` for testing
3. Execute `db/plsql_procedures.sql` for procedures/functions

### Admin Operations:
1. Login as admin (username: admin, password: 12345678)
2. Access Database Interface for custom queries
3. Use Reports section for analytics
4. Run procedures from Quick Operations

### Testing Database Features:
```sql
-- Test dynamic pricing
SELECT room_id, CalculateDynamicPrice(room_id, '2024-12-25', 3) FROM rooms LIMIT 5;

-- Test guest satisfaction
SELECT guest_id, name, CalculateGuestSatisfactionScore(guest_id) FROM guests LIMIT 5;

-- Run maintenance scheduling
CALL ScheduleRoomMaintenance();

-- Generate performance report
SELECT * FROM hotel_performance ORDER BY total_revenue DESC;
```

## Key Database Concepts Demonstrated

✅ **DDL**: CREATE, ALTER, DROP with constraints and indexes  
✅ **DML**: INSERT, UPDATE, DELETE with complex logic  
✅ **Constraints**: PRIMARY KEY, FOREIGN KEY, CHECK, UNIQUE  
✅ **SELECT**: Complex queries with multiple JOINs  
✅ **Aggregate Functions**: SUM, AVG, COUNT, MIN, MAX, STDDEV  
✅ **Subqueries**: Correlated and non-correlated subqueries  
✅ **Set Operations**: UNION, INTERSECT, EXCEPT equivalents  
✅ **Views**: Multiple complex views for reporting  
✅ **Stored Procedures**: With cursors, loops, and complex logic  
✅ **Functions**: Custom calculation functions  
✅ **Triggers**: Automated data maintenance  
✅ **Window Functions**: RANK, LAG, LEAD, ROW_NUMBER  

This system provides a comprehensive demonstration of advanced database concepts in a practical hotel management context, with real-time pricing, loyalty management, occupancy optimization, and detailed analytics capabilities.