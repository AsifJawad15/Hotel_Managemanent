# SQL Functions Implementation Guide

## Overview
SmartStay uses custom SQL functions for dynamic pricing, age calculation, and satisfaction scoring.

## Available Functions

### 1. **CalculateAge(date_of_birth)**
Calculate a person's age from their date of birth.

**Example:**
```sql
SELECT name, CalculateAge(date_of_birth) as age FROM guests;
```

**Used in:** Guest demographics, age verification

---

### 2. **GetSeason(date)**
Determine the season (Peak/High/Normal/Low) for a given date.

**Returns:**
- `Peak` - Dec 15-Feb, Jun-Aug (Holiday & Summer)
- `High` - Mar-May, Sep-Nov (Spring & Fall)
- `Normal` - Other dates

**Example:**
```sql
SELECT GetSeason('2025-12-25') as season; -- Returns 'Peak'
```

**Used in:** Dynamic pricing calculations

---

### 3. **CalculateDynamicPrice(base_price, check_in_date, room_type)**
Calculate dynamic room pricing based on multiple factors.

**Pricing Factors:**
1. **Season Multiplier:**
   - Peak: +30%
   - High: +15%
   - Low: -15%
   - Normal: No change

2. **Advance Booking:**
   - Last minute (≤7 days): +15%
   - Short notice (≤14 days): +10%
   - Early booking (≥60 days): -10%

3. **Room Type:**
   - Suite: +20%
   - Deluxe: +10%
   - Standard: No change

**Example:**
```sql
SELECT CalculateDynamicPrice(150.00, '2025-12-25', 'Suite') as final_price;
-- Base: $150, Peak season (+30%), Suite (+20%), = $234
```

**Used in:** `pages/guest/guest_book_room_dates.php` (line 48)

---

### 4. **CalculateGuestSatisfactionScore(hotel_id)**
Calculate a hotel's satisfaction score (0-100) based on reviews.

**Formula:**
```
Score = (Avg Rating / 5.0) × 70% 
      + (Min(Total Reviews, 100) / 100) × 20%
      + (Response Rate / 100) × 10%
```

**Components:**
- **Rating (70%):** Average guest rating
- **Volume (20%):** Number of reviews (capped at 100)
- **Engagement (10%):** Admin response rate

**Example:**
```sql
SELECT hotel_name, CalculateGuestSatisfactionScore(hotel_id) as score 
FROM hotels;
```

**Used in:** Hotel ranking, admin analytics

---

## Testing the Functions

### Option 1: Use Test Page
1. Navigate to: `http://localhost/SmartStay/test_functions.php`
2. View all functions with sample data
3. See real calculations in action

### Option 2: Direct SQL Queries
Open phpMyAdmin and run:

```sql
-- Test age calculation
SELECT name, date_of_birth, CalculateAge(date_of_birth) as age 
FROM guests LIMIT 5;

-- Test season detection
SELECT GetSeason('2025-12-25') as christmas_season,
       GetSeason('2025-07-15') as summer_season;

-- Test dynamic pricing
SELECT 
    room_number,
    price as base_price,
    CalculateDynamicPrice(price, '2025-12-25', 'Suite') as christmas_price
FROM rooms LIMIT 5;

-- Test satisfaction score
SELECT 
    hotel_name,
    CalculateGuestSatisfactionScore(hotel_id) as satisfaction_score
FROM hotels;
```

### Option 3: Real Application Test
1. **Login as Guest:**
   - Email: `john.smith@email.com`
   - Password: `guest123`

2. **Book a Room:**
   - Search for a hotel
   - Select a room
   - Try different check-in dates:
     - Christmas (Dec 25): See Peak pricing
     - Summer (Jul 15): See Peak pricing
     - Spring (Mar 10): See High pricing
     - Off-season: See lower prices

3. **Observe Dynamic Pricing:**
   - Suite rooms cost more than Standard
   - Last-minute bookings cost more
   - Early bookings get discounts

---

## Sample Data Included

The `db/07_sample_data.sql` includes:
- 10 guests with different birth dates
- 9 hotels with various star ratings
- 90+ rooms across different types
- 10 bookings (past, current, future)
- 14 reviews with ratings
- 5 events for booking

All designed to test the functions realistically!

---

## Where Functions Are Used in Frontend

### CalculateDynamicPrice
**File:** `pages/guest/guest_book_room_dates.php`
**Line:** 48
```php
$dynamic_price_query = $conn->query(
    "SELECT CalculateDynamicPrice($room_id, '$in', $nights) as dynamic_price"
);
```

**User Experience:**
- Guest sees different prices for different dates
- Christmas bookings cost more than off-season
- Early bookings get automatic discounts

### CalculateAge
Can be added to:
- Guest profile pages
- Age verification for services
- Demographics reporting

### CalculateGuestSatisfactionScore
Can be added to:
- Admin dashboard for hotel rankings
- Public hotel listings (sort by satisfaction)
- Hotel performance reports

---

## Database Setup

1. **Run schema:** `db/01_schema.sql`
2. **Run functions:** `db/03_functions.sql`
3. **Load sample data:** `db/07_sample_data.sql`

All functions are now ready to use!

---

## Tips for Development

1. **Testing Dates:** Use future dates to see pricing changes
2. **Room Types:** Compare Standard vs Suite pricing
3. **Seasons:** Test Dec 25 (Peak), Jul 15 (Peak), Mar 10 (High)
4. **Advance Booking:** Try dates 5 days, 30 days, 90 days ahead

Happy coding! 🚀
