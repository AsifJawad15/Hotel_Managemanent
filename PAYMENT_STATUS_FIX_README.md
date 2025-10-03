# Payment Status Issue - Complete Guide

## 🔴 Problem: Why Payment Status is Always "Pending"

### Root Cause
The payment status defaults to `'Pending'` in your booking system because:

1. **Database Default**: The schema defines `payment_status` with a default value of `'Pending'`
   ```sql
   `payment_status` enum('Pending','Paid','Partial','Refunded') DEFAULT 'Pending'
   ```

2. **No Payment Gateway**: Your application doesn't have payment integration (Stripe, PayPal, etc.)
   - When guests book rooms, the system sets `payment_status = 'Pending'` 
   - Nothing updates it to `'Paid'` automatically

3. **Frontend Issue**: In `pages/guest/guest_book_room_dates.php` line 86:
   ```php
   'Confirmed', 'Pending', '" . esc($special_requests) . "'"
   //          ^^^^^^^^^ This hardcodes Pending status
   ```

## ✅ Solutions

### Solution 1: Quick Fix - Update Existing Code

Update `pages/guest/guest_book_room_dates.php` line 86 to set payment as `'Paid'`:

**Change from:**
```php
$booking_query = "INSERT INTO bookings (
  guest_id, room_id, check_in, check_out, adults, children, 
  total_amount, discount_amount, tax_amount, final_amount,
  booking_status, payment_status, special_requests
) VALUES (
  $guest_id, $room_id, '$in', '$out', $adults, $children,
  $subtotal, $discount_amount, $tax_amount, $final_amount,
  'Confirmed', 'Pending', '" . esc($special_requests) . "'
)";
```

**Change to:**
```php
$booking_query = "INSERT INTO bookings (
  guest_id, room_id, check_in, check_out, adults, children, 
  total_amount, discount_amount, tax_amount, final_amount,
  booking_status, payment_status, special_requests
) VALUES (
  $guest_id, $room_id, '$in', '$out', $adults, $children,
  $subtotal, $discount_amount, $tax_amount, $final_amount,
  'Confirmed', 'Paid', '" . esc($special_requests) . "'
  --          ^^^^^^ Changed from 'Pending' to 'Paid'
)";
```

This simulates instant payment (good for testing and simple systems).

---

### Solution 2: Database Fixes

Run the SQL file `db/09_add_old_bookings_guest1.sql` which includes:

#### A. Fix Existing Data
```sql
-- Mark all completed bookings as Paid
UPDATE bookings 
SET payment_status = 'Paid' 
WHERE booking_status = 'Completed';

-- Mark all past bookings as Completed and Paid
UPDATE bookings 
SET booking_status = 'Completed',
    payment_status = 'Paid'
WHERE check_out < CURDATE() 
  AND booking_status = 'Confirmed';
```

#### B. Auto-Update Trigger
Automatically marks bookings as Paid when they're completed:
```sql
CREATE TRIGGER auto_mark_payment_paid
BEFORE UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF NEW.booking_status = 'Completed' AND OLD.booking_status != 'Completed' THEN
        SET NEW.payment_status = 'Paid';
    END IF;
END;
```

#### C. Daily Auto-Complete Event
Automatically completes past bookings every day:
```sql
CREATE EVENT auto_complete_past_bookings
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    UPDATE bookings
    SET booking_status = 'Completed',
        payment_status = 'Paid'
    WHERE check_out < CURDATE()
      AND booking_status = 'Confirmed';
END;
```

---

### Solution 3: Add Old Bookings for Testing

The SQL file adds 7 test bookings for Guest ID 1 (john.smith@email.com):

| Booking | Hotel | Check-in | Check-out | Status | Payment | Amount |
|---------|-------|----------|-----------|--------|---------|--------|
| 1 | Grand Plaza | Jul 10, 2024 | Jul 14, 2024 | Completed | Paid | $540 |
| 2 | Seaside Resort | Aug 5, 2024 | Aug 12, 2024 | Completed | Paid | $1,134 |
| 3 | Metropolitan Art | Sep 1, 2024 | Sep 5, 2024 | Completed | Paid | $504 |
| 4 | Grand Plaza | Mar 15, 2025 | Mar 18, 2025 | Completed | Paid | $405 |
| 5 | Downtown Business | May 10, 2025 | May 13, 2025 | Completed | Paid | $324 |
| 6 | Royal Crown | Sep 20, 2025 | Sep 23, 2025 | Confirmed | Pending | $648 |
| 7 | City Center | Aug 1, 2025 | Aug 5, 2025 | Confirmed | Pending | $432 |

---

## 🚀 How to Apply the Fixes

### Step 1: Run SQL Fixes
1. Open phpMyAdmin
2. Select `smart_stay` database
3. Go to SQL tab
4. Run the entire `db/09_add_old_bookings_guest1.sql` file
5. Verify with:
   ```sql
   SELECT booking_id, check_in, check_out, booking_status, payment_status, final_amount
   FROM bookings
   WHERE guest_id = 1
   ORDER BY check_in DESC;
   ```

### Step 2: Update PHP Code
Edit `pages/guest/guest_book_room_dates.php`:
- Find line 86
- Change `'Pending'` to `'Paid'`
- Save file

### Step 3: Enable Event Scheduler (Optional)
For automatic daily completion of past bookings:
```sql
SET GLOBAL event_scheduler = ON;
```

### Step 4: Test the System
1. Login as guest: `john.smith@email.com` / `guest123`
2. Go to "My Bookings"
3. You should now see:
   - ✅ Past bookings showing "Completed" status
   - ⭐ "Write Review" buttons for completed bookings
   - Payment status showing "Paid"

---

## 📊 Why This Matters

### Revenue Calculation
Your stored procedures require `payment_status = 'Paid'` for revenue:
```sql
-- From 02_procedures.sql line 69-70
SUM(b.final_amount) as total_revenue
FROM bookings b
WHERE b.booking_status = 'Completed'
  AND b.payment_status = 'Paid';
```

Without fixing payment status, **total revenue shows $0**!

### Review System
The review system now shows "Write Review" buttons for:
- Bookings with `booking_status = 'Completed'`
- OR bookings where `check_out < current_date` (past bookings)

This was just fixed in your latest code!

---

## 🔮 Future Enhancement: Real Payment Gateway

For a production system, consider integrating:

### Option 1: Stripe
```php
// In guest_book_room_dates.php
require_once('vendor/autoload.php');
\Stripe\Stripe::setApiKey('your_secret_key');

$session = \Stripe\Checkout\Session::create([
  'payment_method_types' => ['card'],
  'line_items' => [[
    'price_data' => [
      'currency' => 'usd',
      'product_data' => ['name' => 'Hotel Booking'],
      'unit_amount' => $final_amount * 100, // cents
    ],
    'quantity' => 1,
  ]],
  'mode' => 'payment',
  'success_url' => 'http://localhost/SmartStay/pages/guest/payment_success.php?booking_id=' . $booking_id,
  'cancel_url' => 'http://localhost/SmartStay/pages/guest/payment_cancel.php',
]);

// Redirect to Stripe checkout
header("Location: " . $session->url);
```

### Option 2: PayPal
```php
// Similar integration with PayPal SDK
```

When payment succeeds:
```php
// In payment_success.php
UPDATE bookings 
SET payment_status = 'Paid' 
WHERE booking_id = ?
```

---

## 📝 Summary

**Current State:**
- ❌ All bookings stuck at `payment_status = 'Pending'`
- ❌ Revenue reports show $0
- ❌ Review buttons not showing for past bookings

**After Fixes:**
- ✅ Existing bookings updated to `'Paid'`
- ✅ New bookings automatically marked as `'Paid'`
- ✅ Trigger ensures completed bookings are marked paid
- ✅ Daily event auto-completes past bookings
- ✅ Review system works for completed bookings
- ✅ Revenue reports show accurate data

---

## 🎯 Quick Commands

### View all bookings for Guest 1:
```sql
SELECT b.*, h.hotel_name, r.room_number
FROM bookings b
JOIN rooms r ON b.room_id = r.room_id
JOIN hotels h ON r.hotel_id = h.hotel_id
WHERE b.guest_id = 1
ORDER BY b.check_in DESC;
```

### Check revenue with new data:
```sql
SELECT 
    SUM(CASE WHEN booking_status = 'Completed' AND payment_status = 'Paid' 
        THEN final_amount ELSE 0 END) as total_revenue
FROM bookings;
```

### Manually complete a booking:
```sql
UPDATE bookings 
SET booking_status = 'Completed', payment_status = 'Paid'
WHERE booking_id = 10;
```

---

## Need Help?

1. **SQL Not Running?** Make sure you're in the `smart_stay` database
2. **Trigger Errors?** Drop existing trigger first with `DROP TRIGGER IF EXISTS auto_mark_payment_paid;`
3. **Event Scheduler Won't Start?** Check MySQL privileges: `GRANT EVENT ON *.* TO 'root'@'localhost';`
4. **Still Showing Pending?** Clear browser cache and refresh the page

---

**Created:** October 3, 2025  
**Last Updated:** October 3, 2025  
**Files Modified:**
- `db/09_add_old_bookings_guest1.sql` (NEW)
- `pages/guest/guest_book_room_dates.php` (Line 86 - to be updated)
