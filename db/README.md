# SmartStay Database Documentation

## Overview
SmartStay is a comprehensive hotel management system database designed to handle all aspects of hotel operations including room bookings, event management, guest loyalty programs, staff management, and more.

## Database Structure

### Modular SQL Files
The database is organized into modular files for better understanding and maintenance:

1. **01_schema.sql** - Core database structure
   - Table definitions with relationships
   - Primary keys and foreign keys
   - Constraints and indexes
   - JSON columns for flexible data storage

2. **02_procedures.sql** - Business logic stored procedures
   - Loyalty point calculations
   - Revenue analytics
   - Room availability queries
   - Price management
   - Maintenance scheduling

3. **03_functions.sql** - Reusable calculation functions
   - Age calculations
   - Dynamic pricing algorithms
   - Guest satisfaction scoring
   - Season determination

4. **04_triggers.sql** - Automated business rules
   - Loyalty point automation
   - Audit logging
   - Room availability validation
   - Booking amount calculations
   - Event participant tracking

5. **05_views.sql** - Pre-defined business reports
   - Hotel occupancy statistics
   - Revenue summaries
   - Guest booking history
   - Room availability dashboard
   - Maintenance tracking

6. **06_indexes.sql** - Performance optimization
   - Composite indexes for common queries
   - Full-text search indexes
   - Query optimization guidelines

7. **07_sample_data.sql** - Test and demonstration data
   - 9 hotels with complete profiles
   - 10 rooms per hotel (90 total rooms)
   - 10 registered guests
   - 45 events across all hotels
   - Staff, services, and reviews

## Installation Guide

### Prerequisites
- MySQL 5.7+ or MariaDB 10.4+
- PHP 8.0+
- phpMyAdmin (optional, for GUI management)

### Step 1: Create Database
```sql
CREATE DATABASE smart_stay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_stay;
```

### Step 2: Execute SQL Files in Order
Execute the SQL files in the following order:

```bash
# 1. Create tables and structure
mysql -u root -p smart_stay < 01_schema.sql

# 2. Add stored procedures
mysql -u root -p smart_stay < 02_procedures.sql

# 3. Add functions
mysql -u root -p smart_stay < 03_functions.sql

# 4. Add triggers
mysql -u root -p smart_stay < 04_triggers.sql

# 5. Create views
mysql -u root -p smart_stay < 05_views.sql

# 6. Add performance indexes
mysql -u root -p smart_stay < 06_indexes.sql

# 7. Load sample data (optional)
mysql -u root -p smart_stay < 07_sample_data.sql
```

Or using phpMyAdmin:
1. Select the `smart_stay` database
2. Go to "Import" tab
3. Import each file in the order listed above
4. Ensure "Enable foreign key checks" is selected

## Core Tables

### 1. Hotels
Stores hotel information and credentials.
- **Primary Key:** `hotel_id`
- **Key Fields:** `hotel_name`, `email` (login), `city`, `star_rating`
- **Special Features:** JSON amenities field, geo-location fields

### 2. Rooms
Room inventory with pricing and availability.
- **Primary Key:** `room_id`
- **Foreign Keys:** `hotel_id`, `type_id`
- **Key Fields:** `room_number`, `price`, `maintenance_status`
- **Constraints:** Unique combination of hotel_id + room_number

### 3. Guests
Guest profiles with loyalty program integration.
- **Primary Key:** `guest_id`
- **Key Fields:** `email` (login), `loyalty_points`, `membership_level`
- **Membership Tiers:** Bronze (0-499), Silver (500-1999), Gold (2000-4999), Platinum (5000+)

### 4. Bookings
Room reservations with full financial tracking.
- **Primary Key:** `booking_id`
- **Foreign Keys:** `guest_id`, `room_id`
- **Triggers:** Automatic loyalty point calculation on completion
- **Validation:** Date overlap prevention for same room

### 5. Events
Hotel events and functions.
- **Primary Key:** `event_id`
- **Foreign Key:** `hotel_id`
- **Participant Tracking:** Automatic count updates via triggers
- **Event Types:** Conference, Wedding, Meeting, Party, Workshop, Other

### 6. Event_Bookings
Guest registrations for events.
- **Primary Key:** `event_booking_id`
- **Foreign Keys:** `event_id`, `guest_id`
- **Constraint:** One booking per guest per event

### 7. Reviews
Guest feedback and ratings.
- **Primary Key:** `review_id`
- **Foreign Keys:** `hotel_id`, `guest_id`, `booking_id`
- **Rating Categories:** Overall, Service, Cleanliness, Location, Amenities
- **Moderation:** `is_approved` flag for admin review

### 8. Staff
Hotel employee management.
- **Primary Key:** `staff_id`
- **Foreign Key:** `hotel_id`
- **Departments:** Front Desk, Housekeeping, Maintenance, Restaurant, Management, Other

### 9. Services
Additional services offered by hotels.
- **Primary Key:** `service_id`
- **Foreign Key:** `hotel_id`
- **Service Types:** Spa, Restaurant, Room Service, Transport, Laundry, Other

### 10. Maintenance_Schedule
Room maintenance tracking.
- **Primary Key:** `schedule_id`
- **Foreign Keys:** `room_id`, `assigned_to` (staff_id)
- **Priority Levels:** Low, Medium, High, Critical
- **Status:** Scheduled, In Progress, Completed, Cancelled

## Stored Procedures

### CalculateLoyaltyPoints(booking_id)
Automatically calculates and awards loyalty points based on booking amount.
- **Rule:** 1 point per $10 spent
- **Auto-upgrades membership tier based on total points**

### CalculateRoomRevenue(hotel_id, start_date, end_date, OUT total_revenue)
Calculates total revenue for a hotel within a date range.
- **Only counts:** Completed bookings with Paid status

### GenerateMonthlyHotelReport(hotel_id, year, month)
Comprehensive monthly performance report including:
- Total/completed/cancelled bookings
- Revenue metrics
- Average booking value
- Unique guests
- Average rating
- Total events

### GetAvailableRooms(hotel_id, check_in, check_out, room_type_id)
Finds available rooms for specified dates and criteria.
- **Excludes:** Booked rooms and rooms under maintenance
- **Returns:** Room details with pricing

### ProcessLoyaltyUpgrades()
Batch process to upgrade all guest membership levels based on current points.
- **Run periodically:** Recommended monthly

### ScheduleRoomMaintenance(hotel_id)
Automatically schedules maintenance for high-use rooms.
- **Triggers when:** 20+ bookings in 90 days OR 180+ days since last maintenance
- **Priority:** Based on usage and time since last maintenance

### UpdateRoomPrices(hotel_id, adjustment_percentage)
Manual bulk price adjustment with seasonal multipliers.
- **Seasonal Multipliers:** Peak (1.20x), High (1.10x), Low (0.90x), Normal (1.00x)

### UpdateRoomPricesBasedOnDemand(hotel_id)
Dynamic pricing based on current occupancy rates.
- **90%+ occupancy:** +15% price increase
- **70-89% occupancy:** +10% increase
- **50-69% occupancy:** +5% increase
- **<30% occupancy:** -10% decrease

### UpdateRoomPricesManual(hotel_id, percentage)
Simple percentage-based price update.
- **Returns:** Number of rooms updated, new min/max prices

## Functions

### CalculateAge(date_of_birth) → INT
Returns age in years from date of birth.

### CalculateDynamicPrice(base_price, check_in_date, room_type) → DECIMAL
Calculates dynamic room price based on:
- **Season:** Peak/High/Low/Normal
- **Advance Booking:** Early bird discounts, last-minute premiums
- **Room Type:** Suite/Deluxe premium multipliers

### CalculateGuestSatisfactionScore(hotel_id) → DECIMAL
Composite satisfaction score (0-100) based on:
- Average rating (70% weight)
- Number of reviews (20% weight)
- Admin response rate (10% weight)

### GetSeason(date) → VARCHAR
Determines season for date:
- **Peak:** December 15-February, June-August
- **High:** March-May, September-November
- **Normal:** Other periods

## Triggers

### calculate_loyalty_on_completion (AFTER UPDATE on bookings)
Automatically awards loyalty points when booking status changes to 'Completed'.

### log_booking_changes (AFTER UPDATE on bookings)
Logs all booking modifications to system_logs for audit trail.

### update_event_participants (INSERT/UPDATE/DELETE on event_bookings)
Maintains accurate participant counts in events table.

### validate_room_availability (BEFORE INSERT/UPDATE on bookings)
Prevents double-booking by checking for date conflicts.

### update_hotel_total_rooms (AFTER INSERT/DELETE on rooms)
Maintains accurate room count in hotels table.

### calculate_booking_amounts (BEFORE INSERT on bookings)
Automatically calculates tax (18%) and final amount if not provided.

## Views

### vw_hotel_occupancy
Real-time occupancy statistics per hotel including:
- Occupied/total rooms
- Occupancy rate percentage
- Check-ins/check-outs today

### vw_guest_booking_history
Complete booking history with guest and hotel details.

### vw_hotel_revenue_summary
Revenue analytics by hotel including:
- Booking counts by status
- Total and average revenue
- Unique guest count
- Average ratings

### vw_room_availability
Current availability status for all rooms with next available dates.

### vw_upcoming_events
Events scheduled for the future with participation details and fill rates.

### vw_maintenance_dashboard
Pending maintenance tasks sorted by priority.

### vw_guest_loyalty_tiers
Statistics grouped by membership tier.

## Security Features

### Password Storage
- All passwords are hashed using PHP `password_hash()` with `PASSWORD_DEFAULT`
- Sample data uses: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` (password: admin123, hotel123, guest123)

### SQL Injection Prevention
- Use prepared statements in PHP code
- All procedures use parameterized inputs
- Triggers validate data before insertion

### Audit Trail
- `system_logs` table tracks all booking modifications
- Trigger-based logging for important changes
- Stores old and new values in JSON format

## Performance Optimization

### Indexes
- Primary keys on all tables (clustered indexes)
- Foreign key indexes (automatic)
- Composite indexes for common queries
- Full-text search indexes for text fields

### Query Optimization Tips
1. Use views for complex reporting queries
2. Leverage stored procedures for business logic
3. Run `ANALYZE TABLE` periodically to update statistics
4. Monitor slow query log
5. Use `EXPLAIN` to analyze query execution plans

### Maintenance Commands
```sql
-- Update table statistics
ANALYZE TABLE bookings;

-- Defragment and rebuild indexes
OPTIMIZE TABLE bookings;

-- View index information
SHOW INDEX FROM bookings;

-- Check table integrity
CHECK TABLE bookings;
```

## Common Queries

### Find Available Rooms
```sql
CALL GetAvailableRooms(1, '2025-03-01', '2025-03-05', NULL);
```

### Calculate Hotel Revenue
```sql
CALL CalculateRoomRevenue(1, '2025-01-01', '2025-01-31', @revenue);
SELECT @revenue;
```

### Generate Monthly Report
```sql
CALL GenerateMonthlyHotelReport(1, 2025, 3);
```

### Update Prices Based on Demand
```sql
CALL UpdateRoomPricesBasedOnDemand(1);
```

### Manual Price Adjustment
```sql
CALL UpdateRoomPricesManual(1, 10); -- 10% increase
```

### Check Occupancy
```sql
SELECT * FROM vw_hotel_occupancy WHERE hotel_id = 1;
```

### Guest Satisfaction Score
```sql
SELECT hotel_name, CalculateGuestSatisfactionScore(hotel_id) as satisfaction_score
FROM hotels
WHERE is_active = 1;
```

## Business Rules

### Loyalty Program
- **Bronze:** 0-499 points (default)
- **Silver:** 500-1,999 points
- **Gold:** 2,000-4,999 points
- **Platinum:** 5,000+ points
- **Earning Rate:** 1 point per $10 spent on completed bookings

### Pricing Rules
- Base prices set per room
- Dynamic pricing adjusts based on:
  - Season (Peak/High/Low/Normal)
  - Advance booking period
  - Current occupancy rate
  - Room type

### Booking Rules
- Check-out must be after check-in
- No overlapping bookings for same room
- Minimum 18% tax automatically applied
- Discounts applied before tax calculation

### Maintenance Scheduling
- Automatic scheduling triggers:
  - 20+ bookings in 90-day period
  - 180+ days since last maintenance
- Priority calculation:
  - High: 270+ days since maintenance OR 30+ bookings
  - Medium: Standard thresholds
  - Low: Manual scheduling

## API Integration Points

### Authentication Tables
- `admins` - Admin panel authentication
- `hotels` - Hotel dashboard authentication
- `guests` - Customer portal authentication

### Booking Flow
1. Search available rooms: `GetAvailableRooms()`
2. Calculate dynamic price: `CalculateDynamicPrice()`
3. Create booking: INSERT into `bookings`
4. Process payment: INSERT into `payments`
5. Complete booking: UPDATE `bookings` status to 'Completed'
6. Award loyalty points: Automatic via trigger

### Event Booking Flow
1. View events: SELECT from `events` or `vw_upcoming_events`
2. Create booking: INSERT into `event_bookings`
3. Update participant count: Automatic via trigger

## Backup and Restore

### Backup Database
```bash
mysqldump -u root -p smart_stay > backup_smartstay_$(date +%Y%m%d).sql
```

### Backup with Compression
```bash
mysqldump -u root -p smart_stay | gzip > backup_smartstay_$(date +%Y%m%d).sql.gz
```

### Restore Database
```bash
mysql -u root -p smart_stay < backup_smartstay_20250101.sql
```

### Backup Individual Tables
```bash
mysqldump -u root -p smart_stay bookings payments > bookings_backup.sql
```

## Troubleshooting

### Issue: Trigger not firing
**Solution:** Check trigger status:
```sql
SHOW TRIGGERS WHERE `Table` = 'bookings';
```

### Issue: Foreign key constraint fails
**Solution:** Temporarily disable checks:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Your INSERT/UPDATE/DELETE statements
SET FOREIGN_KEY_CHECKS = 1;
```

### Issue: Slow queries
**Solution:** Analyze and optimize:
```sql
EXPLAIN SELECT * FROM bookings WHERE guest_id = 1;
ANALYZE TABLE bookings;
```

### Issue: Event participant count mismatch
**Solution:** Recalculate from bookings:
```sql
UPDATE events e
SET current_participants = (
    SELECT COALESCE(SUM(participants), 0)
    FROM event_bookings eb
    WHERE eb.event_id = e.event_id
    AND eb.booking_status = 'Confirmed'
);
```

## Version History

### Version 2.0 (Current)
- Modular SQL file structure
- Enhanced stored procedures
- Dynamic pricing algorithms
- Comprehensive views
- Full-text search indexes
- Audit logging system

### Version 1.0
- Initial database design
- Basic tables and relationships
- Simple stored procedures

## Support

For database-related issues:
1. Check this documentation
2. Review error logs in `system_logs` table
3. Test queries using phpMyAdmin
4. Verify data integrity with CHECK TABLE

## License
SmartStay Hotel Management System Database
© 2025 All Rights Reserved
