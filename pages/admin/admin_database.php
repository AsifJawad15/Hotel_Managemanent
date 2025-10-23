<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_admin.php");

$result_message = '';
$query_result = null;
$error_message = '';

// Handle custom SQL execution
if (isset($_POST['execute_query']) && !empty($_POST['sql_query'])) {
    $sql_query = trim($_POST['sql_query']);
    
    try {
        $query_result = $conn->query($sql_query);
        
        if ($query_result === TRUE) {
            $result_message = "Query executed successfully.";
            if ($conn->affected_rows > 0) {
                $result_message .= " Rows affected: " . $conn->affected_rows;
            }
        } elseif ($query_result === FALSE) {
            $error_message = "Query failed: " . $conn->error;
        } else {
            $result_message = "Query executed successfully. Rows returned: " . $query_result->num_rows;
        }
    } catch (Exception $e) {
        $error_message = "Error executing query: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Database Query Interface - Admin</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .query-section { 
            background: #f8fafc; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .category-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .category-header {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .category-ddl { border-bottom-color: #3b82f6; color: #3b82f6; }
        .category-dml { border-bottom-color: #059669; color: #059669; }
        .category-subquery { border-bottom-color: #dc2626; color: #dc2626; }
        .category-set { border-bottom-color: #7c3aed; color: #7c3aed; }
        .category-view { border-bottom-color: #ea580c; color: #ea580c; }
        .category-join { border-bottom-color: #0891b2; color: #0891b2; }
        
        .query-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }
        .query-button {
            padding: 12px 16px;
            border: 2px solid;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            text-align: left;
            background: white;
        }
        .query-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-ddl { border-color: #3b82f6; color: #3b82f6; }
        .btn-ddl:hover { background: #3b82f6; color: white; }
        .btn-dml { border-color: #059669; color: #059669; }
        .btn-dml:hover { background: #059669; color: white; }
        .btn-subquery { border-color: #dc2626; color: #dc2626; }
        .btn-subquery:hover { background: #dc2626; color: white; }
        .btn-set { border-color: #7c3aed; color: #7c3aed; }
        .btn-set:hover { background: #7c3aed; color: white; }
        .btn-view { border-color: #ea580c; color: #ea580c; }
        .btn-view:hover { background: #ea580c; color: white; }
        .btn-join { border-color: #0891b2; color: #0891b2; }
        .btn-join:hover { background: #0891b2; color: white; }
        
        textarea {
            font-family: 'Courier New', monospace;
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #475569;
            padding: 15px;
            width: 100%;
            border-radius: 6px;
            min-height: 150px;
        }
        .result-table {
            margin: 20px 0;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            max-height: 500px;
            overflow-y: auto;
        }
        .result-table table {
            margin: 0;
            width: 100%;
        }
        .success { 
            background: #dcfce7; 
            color: #166534; 
            padding: 12px; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        .error { 
            background: #fef2f2; 
            color: #dc2626; 
            padding: 12px; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .query-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        .query-desc {
            font-size: 12px;
            opacity: 0.8;
        }
        .icon {
            font-size: 24px;
        }
    </style>
</head>
<body>
<div class="header">
    <div>Database Query Interface</div>
    <div class="nav">
        <a href="admin_home.php">Dashboard</a>
        <a href="admin_logout.php">Logout</a>
    </div>
</div>

<div class="main">
    <h2>🗄️ Advanced Database Query Interface</h2>
    <p style="color: #64748b; margin-bottom: 30px;">Explore comprehensive SQL operations organized by category: DDL, DML, Subqueries, Set Operations, Views, and Joins</p>

    <!-- Results Display -->
    <?php if ($result_message): ?>
        <div class="success"><?= htmlspecialchars($result_message) ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Query Results Table -->
    <?php if ($query_result && is_object($query_result) && $query_result->num_rows > 0): ?>
        <div class="result-table">
            <table class="table">
                <thead>
                    <tr>
                        <?php
                        $fields = $query_result->fetch_fields();
                        foreach ($fields as $field) {
                            echo "<th>" . htmlspecialchars($field->name) . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $query_result->fetch_assoc()): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value ?? 'NULL') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Query Builder Section -->
    <div class="query-section">
        <h3>🔍 Query Builder</h3>
        <p style="color: #64748b; margin-bottom: 20px;">Select a query category, choose a specific query, review it, then execute to see results.</p>
        
        <form method="post" id="query-form">
            <!-- Step 1: Select Query Type -->
            <div class="form-group">
                <label style="font-size: 16px; color: #1e293b;">📂 Step 1: Select Query Type</label>
                <select id="query-type" name="query_type" onchange="updateQueryList()" 
                        style="width: 100%; padding: 12px; border: 2px solid #cbd5e1; border-radius: 8px; font-size: 15px; background: white;">
                    <option value="">-- Select a Query Type --</option>
                    <option value="ddl" <?= ($_POST['query_type'] ?? '') == 'ddl' ? 'selected' : '' ?>>🏗️ DDL - Data Definition Language</option>
                    <option value="dml" <?= ($_POST['query_type'] ?? '') == 'dml' ? 'selected' : '' ?>>✏️ DML - Data Manipulation Language</option>
                    <option value="join" <?= ($_POST['query_type'] ?? '') == 'join' ? 'selected' : '' ?>>🔗 Joins - Combining Tables</option>
                    <option value="subquery" <?= ($_POST['query_type'] ?? '') == 'subquery' ? 'selected' : '' ?>>🔍 Subqueries - Nested Queries</option>
                    <option value="aggregate" <?= ($_POST['query_type'] ?? '') == 'aggregate' ? 'selected' : '' ?>>📊 Aggregate Functions - Analytics</option>
                    <option value="view" <?= ($_POST['query_type'] ?? '') == 'view' ? 'selected' : '' ?>>👁️ Views - Predefined Reports</option>
                </select>
            </div>

            <!-- Step 2: Select Specific Query -->
            <div class="form-group" id="query-list-container" style="display: none;">
                <label style="font-size: 16px; color: #1e293b;">📋 Step 2: Select Specific Query</label>
                <select id="query-list" name="query_name" onchange="loadQuery()" 
                        style="width: 100%; padding: 12px; border: 2px solid #cbd5e1; border-radius: 8px; font-size: 15px; background: white;">
                    <option value="">-- Select a Query --</option>
                </select>
            </div>

            <!-- Hidden SQL Query Field (not shown to user) -->
            <input type="hidden" id="custom-sql-query" name="sql_query">
            
            <!-- Query Description (shows what the query does) -->
            <div id="query-description" style="display: none; margin-top: 15px; padding: 12px; background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 4px; font-size: 14px; color: #0c4a6e;"></div>

            <!-- Execute Button -->
            <div id="execute-button-container" style="display: none; margin-top: 20px;">
                <button type="submit" name="execute_query" class="btn btn-primary" 
                        style="padding: 14px 32px; font-size: 17px; font-weight: bold; background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%); border: none; border-radius: 8px; color: white; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s;">
                    ▶️ Execute Query & Show Results
                </button>
                <button type="button" onclick="resetForm()" 
                        style="padding: 14px 32px; font-size: 17px; margin-left: 10px; background: #f1f5f9; border: 2px solid #cbd5e1; border-radius: 8px; color: #475569; cursor: pointer;">
                    🔄 Reset
                </button>
            </div>
        </form>
    </div>

    <script>
    // Query database organized by category
    const queries = {
        ddl: [
            {
                name: 'Show All Tables',
                sql: 'SHOW TABLES;',
                description: 'Display all tables in the smart_stay database'
            },
            {
                name: 'Describe Hotels Table',
                sql: 'DESCRIBE hotels;',
                description: 'Show structure and columns of hotels table'
            },
            {
                name: 'Describe Bookings Table',
                sql: 'DESCRIBE bookings;',
                description: 'Show structure and columns of bookings table'
            },
            {
                name: 'Describe Rooms Table',
                sql: 'DESCRIBE rooms;',
                description: 'Show structure and columns of rooms table'
            },
            {
                name: 'Describe Guests Table',
                sql: 'DESCRIBE guests;',
                description: 'Show structure and columns of guests table'
            },
            {
                name: 'Show Indexes on Bookings',
                sql: 'SHOW INDEX FROM bookings;',
                description: 'View all indexes on bookings table for query optimization'
            },
            {
                name: 'Table Statistics',
                sql: `SELECT TABLE_NAME, TABLE_TYPE, ENGINE, TABLE_ROWS, 
       ROUND(DATA_LENGTH/1024/1024, 2) as 'Size_MB',
       ROUND(INDEX_LENGTH/1024/1024, 2) as 'Index_MB'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'smart_stay' 
ORDER BY DATA_LENGTH DESC;`,
                description: 'Size, row count, and storage statistics for all tables'
            },
            {
                name: 'Foreign Key Relationships',
                sql: `SELECT 
    TABLE_NAME as 'Table', 
    COLUMN_NAME as 'Column', 
    CONSTRAINT_NAME as 'Constraint',
    REFERENCED_TABLE_NAME as 'References_Table', 
    REFERENCED_COLUMN_NAME as 'References_Column'
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'smart_stay' 
  AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;`,
                description: 'Show all foreign key relationships between tables'
            }
        ],
        dml: [
            {
                name: 'All Active Hotels',
                sql: 'SELECT * FROM hotels WHERE is_active = 1 ORDER BY star_rating DESC, hotel_name LIMIT 20;',
                description: 'Retrieve all active hotels ordered by star rating'
            },
            {
                name: 'Recent Guests',
                sql: 'SELECT guest_id, name, email, phone, membership_level, loyalty_points, created_at FROM guests ORDER BY created_at DESC LIMIT 20;',
                description: 'Latest registered guests in the system'
            },
            {
                name: 'Confirmed Bookings',
                sql: `SELECT booking_id, guest_id, room_id, check_in, check_out, 
       booking_status, payment_status, final_amount 
FROM bookings 
WHERE booking_status = 'Confirmed' 
ORDER BY check_in DESC LIMIT 20;`,
                description: 'All confirmed bookings with payment status'
            },
            {
                name: 'Available Rooms Today',
                sql: `SELECT r.room_id, h.hotel_name, r.room_number, rt.type_name, 
       r.price, r.max_occupancy, r.maintenance_status
FROM rooms r
JOIN hotels h ON r.hotel_id = h.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.is_active = 1 
  AND r.room_id NOT IN (
      SELECT room_id FROM bookings 
      WHERE booking_status = 'Confirmed'
      AND CURDATE() BETWEEN check_in AND check_out
  )
ORDER BY h.hotel_name, r.room_number
LIMIT 30;`,
                description: 'Rooms available for booking today'
            },
            {
                name: 'Upcoming Events',
                sql: `SELECT e.event_id, h.hotel_name, e.event_name, e.event_date, 
       e.start_time, e.max_participants, e.current_participants, 
       e.price, e.event_status
FROM events e
JOIN hotels h ON e.hotel_id = h.hotel_id
WHERE e.event_status = 'Upcoming' 
  AND e.event_date >= CURDATE()
ORDER BY e.event_date 
LIMIT 20;`,
                description: 'Future events scheduled at hotels'
            },
            {
                name: 'High-Rated Reviews',
                sql: `SELECT r.review_id, g.name as guest_name, h.hotel_name, 
       r.rating, r.comment, r.created_at
FROM reviews r
JOIN guests g ON r.guest_id = g.guest_id
JOIN hotels h ON r.hotel_id = h.hotel_id
WHERE r.is_approved = 1 AND r.rating >= 4
ORDER BY r.created_at DESC 
LIMIT 20;`,
                description: 'Approved reviews with ratings 4 stars and above'
            },
            {
                name: 'Recent Payments',
                sql: `SELECT payment_id, booking_id, payment_method, amount, 
       payment_status, payment_date, transaction_id
FROM payments 
WHERE payment_status = 'Completed'
  AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY payment_date DESC 
LIMIT 25;`,
                description: 'Completed payments in the last 30 days'
            },
            {
                name: 'Premium Members',
                sql: `SELECT guest_id, name, email, membership_level, loyalty_points, 
       date_of_birth, created_at
FROM guests 
WHERE membership_level IN ('Gold', 'Platinum')
ORDER BY loyalty_points DESC 
LIMIT 25;`,
                description: 'Gold and Platinum tier members with highest loyalty points'
            }
        ],
        join: [
            {
                name: 'Complete Booking Details',
                sql: `SELECT 
    b.booking_id,
    g.name as guest_name,
    g.email,
    g.phone,
    h.hotel_name,
    h.city,
    r.room_number,
    rt.type_name as room_type,
    b.check_in,
    b.check_out,
    DATEDIFF(b.check_out, b.check_in) as nights,
    b.adults,
    b.children,
    b.final_amount,
    b.booking_status,
    b.payment_status
FROM bookings b
INNER JOIN guests g ON b.guest_id = g.guest_id
INNER JOIN rooms r ON b.room_id = r.room_id
INNER JOIN hotels h ON r.hotel_id = h.hotel_id
INNER JOIN room_types rt ON r.type_id = rt.type_id
WHERE b.check_in >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
ORDER BY b.created_at DESC
LIMIT 25;`,
                description: '4-way INNER JOIN showing complete booking information with guest, room, and hotel details'
            },
            {
                name: 'Hotel Performance Report',
                sql: `SELECT 
    h.hotel_name,
    h.city,
    h.star_rating,
    COUNT(DISTINCT r.room_id) as total_rooms,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    COUNT(DISTINCT CASE WHEN b.booking_status = 'Completed' THEN b.booking_id END) as completed_bookings,
    ROUND(SUM(CASE WHEN b.payment_status = 'Paid' THEN b.final_amount ELSE 0 END), 2) as total_revenue,
    ROUND(AVG(CASE WHEN b.payment_status = 'Paid' THEN b.final_amount END), 2) as avg_booking_value
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id
WHERE h.is_active = 1
GROUP BY h.hotel_id, h.hotel_name, h.city, h.star_rating
ORDER BY total_revenue DESC;`,
                description: 'LEFT JOIN with GROUP BY showing hotel statistics and revenue'
            },
            {
                name: 'Guest Booking Summary',
                sql: `SELECT 
    g.name as guest_name,
    g.email,
    g.membership_level,
    g.loyalty_points,
    COUNT(b.booking_id) as total_bookings,
    ROUND(SUM(CASE WHEN b.payment_status = 'Paid' THEN b.final_amount ELSE 0 END), 2) as total_spent,
    ROUND(AVG(b.final_amount), 2) as avg_booking_value,
    MAX(b.check_in) as last_booking_date
FROM guests g
LEFT JOIN bookings b ON g.guest_id = b.guest_id
GROUP BY g.guest_id, g.name, g.email, g.membership_level, g.loyalty_points
HAVING total_bookings > 0
ORDER BY total_spent DESC
LIMIT 25;`,
                description: 'Guest spending analysis using LEFT JOIN with aggregation'
            },
            {
                name: 'Room Inventory by Hotel',
                sql: `SELECT 
    h.hotel_name,
    h.city,
    rt.type_name,
    COUNT(r.room_id) as room_count,
    ROUND(MIN(r.price), 2) as min_price,
    ROUND(MAX(r.price), 2) as max_price,
    ROUND(AVG(r.price), 2) as avg_price,
    ROUND(AVG(r.area_sqft), 0) as avg_area_sqft
FROM hotels h
INNER JOIN rooms r ON h.hotel_id = r.hotel_id
INNER JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.is_active = 1
GROUP BY h.hotel_id, h.hotel_name, h.city, rt.type_id, rt.type_name
ORDER BY h.hotel_name, rt.type_name;`,
                description: 'Room statistics by hotel and type using INNER JOIN'
            },
            {
                name: 'Event Registration Details',
                sql: `SELECT 
    e.event_name,
    h.hotel_name,
    e.event_date,
    e.venue,
    e.max_participants,
    e.current_participants,
    COUNT(eb.event_booking_id) as total_bookings,
    SUM(eb.participants) as registered_participants,
    GROUP_CONCAT(DISTINCT g.name SEPARATOR ', ') as attendee_names
FROM events e
INNER JOIN hotels h ON e.hotel_id = h.hotel_id
LEFT JOIN event_bookings eb ON e.event_id = eb.event_id 
    AND eb.booking_status = 'Confirmed'
LEFT JOIN guests g ON eb.guest_id = g.guest_id
WHERE e.event_status = 'Upcoming'
GROUP BY e.event_id, e.event_name, h.hotel_name, e.event_date, 
         e.venue, e.max_participants, e.current_participants
ORDER BY e.event_date
LIMIT 15;`,
                description: 'Event details with attendee information using mixed INNER and LEFT JOINs'
            },
            {
                name: 'Payment Transaction History',
                sql: `SELECT 
    p.payment_id,
    g.name as guest_name,
    h.hotel_name,
    b.booking_id,
    b.check_in,
    b.final_amount as booking_amount,
    p.amount as paid_amount,
    p.payment_method,
    p.payment_date,
    p.transaction_id
FROM payments p
INNER JOIN bookings b ON p.booking_id = b.booking_id
INNER JOIN guests g ON b.guest_id = g.guest_id
INNER JOIN rooms r ON b.room_id = r.room_id
INNER JOIN hotels h ON r.hotel_id = h.hotel_id
WHERE p.payment_status = 'Completed'
ORDER BY p.payment_date DESC
LIMIT 30;`,
                description: 'Payment history with complete transaction details using multiple INNER JOINs'
            }
        ],
        subquery: [
            {
                name: 'Hotels with Budget Rooms',
                sql: `SELECT h.hotel_name, h.city, h.star_rating
FROM hotels h
WHERE h.hotel_id IN (
    SELECT DISTINCT r.hotel_id 
    FROM rooms r 
    WHERE r.price < 150 AND r.is_active = 1
)
ORDER BY h.hotel_name;`,
                description: 'Using IN subquery to find hotels that have rooms under $150'
            },
            {
                name: 'Above-Average Loyalty Points',
                sql: `SELECT g.name, g.email, g.membership_level, g.loyalty_points
FROM guests g
WHERE g.loyalty_points > (
    SELECT AVG(loyalty_points) 
    FROM guests
)
ORDER BY g.loyalty_points DESC
LIMIT 20;`,
                description: 'Scalar subquery to find guests with above-average loyalty points'
            },
            {
                name: 'Hotel Room Statistics',
                sql: `SELECT 
    h.hotel_name, 
    h.city,
    (SELECT COUNT(*) FROM rooms r WHERE r.hotel_id = h.hotel_id AND r.is_active = 1) as total_rooms,
    (SELECT ROUND(AVG(price), 2) FROM rooms r WHERE r.hotel_id = h.hotel_id) as avg_price,
    (SELECT MIN(price) FROM rooms r WHERE r.hotel_id = h.hotel_id) as min_price,
    (SELECT MAX(price) FROM rooms r WHERE r.hotel_id = h.hotel_id) as max_price
FROM hotels h
WHERE h.is_active = 1
ORDER BY total_rooms DESC;`,
                description: 'Correlated subqueries in SELECT clause for room statistics'
            },
            {
                name: 'Most Expensive Room per Hotel',
                sql: `SELECT r.room_id, h.hotel_name, r.room_number, rt.type_name, r.price
FROM rooms r
JOIN hotels h ON r.hotel_id = h.hotel_id
JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.price = (
    SELECT MAX(price) 
    FROM rooms r2 
    WHERE r2.hotel_id = r.hotel_id
)
ORDER BY r.price DESC;`,
                description: 'Correlated subquery to find the highest priced room in each hotel'
            },
            {
                name: 'Hotels with Upcoming Events',
                sql: `SELECT h.hotel_name, h.city, h.star_rating
FROM hotels h
WHERE EXISTS (
    SELECT 1 
    FROM events e 
    WHERE e.hotel_id = h.hotel_id 
      AND e.event_status = 'Upcoming'
      AND e.event_date >= CURDATE()
)
ORDER BY h.hotel_name;`,
                description: 'Using EXISTS to find hotels that have upcoming events'
            },
            {
                name: 'Guests Without Bookings',
                sql: `SELECT g.guest_id, g.name, g.email, g.created_at
FROM guests g
WHERE NOT EXISTS (
    SELECT 1 
    FROM bookings b 
    WHERE b.guest_id = g.guest_id
)
ORDER BY g.created_at DESC
LIMIT 25;`,
                description: 'Using NOT EXISTS to find guests who have never made a booking'
            },
            {
                name: 'High-Value Bookings',
                sql: `SELECT 
    b.booking_id,
    g.name as guest_name,
    h.hotel_name,
    b.check_in,
    b.final_amount,
    ROUND((b.final_amount / (SELECT AVG(final_amount) FROM bookings) * 100), 2) as percent_of_avg
FROM bookings b
JOIN guests g ON b.guest_id = g.guest_id
JOIN rooms r ON b.room_id = r.room_id
JOIN hotels h ON r.hotel_id = h.hotel_id
WHERE b.final_amount > (
    SELECT AVG(final_amount) * 1.5 
    FROM bookings
)
ORDER BY b.final_amount DESC
LIMIT 20;`,
                description: 'Find bookings 50% above average using scalar subquery'
            }
        ],
        aggregate: [
            {
                name: 'Revenue by Hotel',
                sql: `SELECT 
    h.hotel_name,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    COUNT(DISTINCT b.guest_id) as unique_guests,
    ROUND(SUM(b.final_amount), 2) as total_revenue,
    ROUND(AVG(b.final_amount), 2) as avg_booking_value,
    MAX(b.final_amount) as highest_booking,
    MIN(b.final_amount) as lowest_booking
FROM hotels h
LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b ON r.room_id = b.room_id 
    AND b.payment_status = 'Paid'
GROUP BY h.hotel_id, h.hotel_name
ORDER BY total_revenue DESC;`,
                description: 'Aggregate functions: COUNT, SUM, AVG, MAX, MIN for hotel revenue analysis'
            },
            {
                name: 'Monthly Booking Trends',
                sql: `SELECT 
    YEAR(check_in) as year,
    MONTH(check_in) as month,
    MONTHNAME(check_in) as month_name,
    COUNT(booking_id) as total_bookings,
    ROUND(SUM(final_amount), 2) as revenue,
    ROUND(AVG(final_amount), 2) as avg_booking_value,
    COUNT(DISTINCT guest_id) as unique_guests
FROM bookings
WHERE booking_status IN ('Confirmed', 'Completed')
  AND check_in >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY YEAR(check_in), MONTH(check_in), MONTHNAME(check_in)
ORDER BY year DESC, month DESC;`,
                description: 'Time-based aggregation showing monthly booking and revenue trends'
            },
            {
                name: 'Guest Segmentation by Spending',
                sql: `SELECT 
    CASE 
        WHEN total_spent >= 2000 THEN 'High Value'
        WHEN total_spent >= 500 THEN 'Medium Value'
        ELSE 'Low Value'
    END as customer_segment,
    COUNT(*) as guest_count,
    ROUND(AVG(total_spent), 2) as avg_spent,
    ROUND(SUM(total_spent), 2) as segment_revenue,
    ROUND(MIN(total_spent), 2) as min_spent,
    ROUND(MAX(total_spent), 2) as max_spent
FROM (
    SELECT g.guest_id, SUM(b.final_amount) as total_spent
    FROM guests g
    JOIN bookings b ON g.guest_id = b.guest_id
    WHERE b.payment_status = 'Paid'
    GROUP BY g.guest_id
) as guest_spending
GROUP BY customer_segment
ORDER BY avg_spent DESC;`,
                description: 'CASE statement with aggregation for customer value segmentation'
            },
            {
                name: 'Room Type Performance',
                sql: `SELECT 
    rt.type_name,
    COUNT(DISTINCT r.room_id) as total_rooms,
    COUNT(DISTINCT b.booking_id) as bookings_count,
    ROUND(AVG(r.price), 2) as avg_room_price,
    ROUND(SUM(b.final_amount), 2) as total_revenue,
    ROUND(AVG(b.final_amount), 2) as avg_booking_revenue,
    ROUND(COUNT(b.booking_id) / COUNT(DISTINCT r.room_id), 2) as bookings_per_room
FROM room_types rt
LEFT JOIN rooms r ON rt.type_id = r.type_id
LEFT JOIN bookings b ON r.room_id = b.room_id 
    AND b.booking_status IN ('Confirmed', 'Completed')
GROUP BY rt.type_id, rt.type_name
ORDER BY total_revenue DESC;`,
                description: 'Performance metrics by room type with calculated ratios'
            },
            {
                name: 'Membership Tier Analysis',
                sql: `SELECT 
    membership_level,
    COUNT(*) as member_count,
    ROUND(AVG(loyalty_points), 0) as avg_points,
    MIN(loyalty_points) as min_points,
    MAX(loyalty_points) as max_points,
    ROUND(AVG(YEAR(CURDATE()) - YEAR(date_of_birth)), 0) as avg_age
FROM guests
WHERE is_active = 1 AND date_of_birth IS NOT NULL
GROUP BY membership_level
ORDER BY 
    CASE membership_level
        WHEN 'Platinum' THEN 1
        WHEN 'Gold' THEN 2
        WHEN 'Silver' THEN 3
        WHEN 'Bronze' THEN 4
    END;`,
                description: 'Membership statistics with custom sorting using CASE'
            },
            {
                name: 'Event Participation Stats',
                sql: `SELECT 
    e.event_type,
    COUNT(DISTINCT e.event_id) as total_events,
    SUM(e.current_participants) as total_participants,
    ROUND(AVG(e.current_participants), 1) as avg_participants,
    ROUND(AVG((e.current_participants / e.max_participants) * 100), 2) as avg_fill_rate,
    ROUND(SUM(e.price * e.current_participants), 2) as total_revenue
FROM events e
WHERE e.event_status IN ('Upcoming', 'Completed')
GROUP BY e.event_type
ORDER BY total_revenue DESC;`,
                description: 'Event metrics with fill rate calculations'
            }
        ],
        view: [
            {
                name: 'Hotel Occupancy View',
                sql: 'SELECT * FROM vw_hotel_occupancy ORDER BY occupancy_rate DESC;',
                description: 'Real-time hotel occupancy statistics with check-in/check-out today'
            },
            {
                name: 'Guest Booking History View',
                sql: 'SELECT * FROM vw_guest_booking_history ORDER BY booking_date DESC LIMIT 30;',
                description: 'Complete guest booking history with hotel and room details'
            },
            {
                name: 'Hotel Revenue Summary View',
                sql: 'SELECT * FROM vw_hotel_revenue_summary ORDER BY total_revenue DESC;',
                description: 'Revenue analytics by hotel with booking statistics and ratings'
            },
            {
                name: 'Room Availability View',
                sql: 'SELECT * FROM vw_room_availability WHERE current_status = "Available" LIMIT 40;',
                description: 'Current room availability status across all hotels'
            },
            {
                name: 'Upcoming Events View',
                sql: 'SELECT * FROM vw_upcoming_events ORDER BY event_date LIMIT 20;',
                description: 'Upcoming events with participation details and fill rates'
            },
            {
                name: 'Guest Loyalty Tiers View',
                sql: 'SELECT * FROM vw_guest_loyalty_tiers;',
                description: 'Guest loyalty statistics aggregated by membership tier'
            }
        ]
    };

    // Update query list based on selected type
    function updateQueryList() {
        const queryType = document.getElementById('query-type').value;
        const queryList = document.getElementById('query-list');
        const listContainer = document.getElementById('query-list-container');
        const descContainer = document.getElementById('query-description');
        const executeContainer = document.getElementById('execute-button-container');
        
        // Clear previous selections
        queryList.innerHTML = '<option value="">-- Select a Query --</option>';
        document.getElementById('custom-sql-query').value = '';
        descContainer.innerHTML = '';
        descContainer.style.display = 'none';
        
        if (queryType && queries[queryType]) {
            // Show query list
            listContainer.style.display = 'block';
            
            // Populate queries
            queries[queryType].forEach((query, index) => {
                const option = document.createElement('option');
                option.value = index;
                option.textContent = query.name;
                queryList.appendChild(option);
            });
            
            // Hide execute until query selected
            executeContainer.style.display = 'none';
        } else {
            listContainer.style.display = 'none';
            descContainer.style.display = 'none';
            executeContainer.style.display = 'none';
        }
    }

    // Load selected query
    function loadQuery() {
        const queryType = document.getElementById('query-type').value;
        const queryIndex = document.getElementById('query-list').value;
        const descContainer = document.getElementById('query-description');
        const executeContainer = document.getElementById('execute-button-container');
        
        if (queryType && queryIndex !== '' && queries[queryType][queryIndex]) {
            const selectedQuery = queries[queryType][queryIndex];
            
            // Set SQL query in hidden field
            document.getElementById('custom-sql-query').value = selectedQuery.sql;
            
            // Show description
            descContainer.innerHTML = '<strong>📝 Query Description:</strong> ' + selectedQuery.description;
            descContainer.style.display = 'block';
            
            // Show execute button
            executeContainer.style.display = 'block';
            
            // Scroll to execute button
            executeContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            descContainer.style.display = 'none';
            executeContainer.style.display = 'none';
        }
    }

    // Reset form
    function resetForm() {
        document.getElementById('query-form').reset();
        document.getElementById('query-list-container').style.display = 'none';
        document.getElementById('query-description').style.display = 'none';
        document.getElementById('execute-button-container').style.display = 'none';
        document.getElementById('custom-sql-query').value = '';
        document.getElementById('query-description').innerHTML = '';
    }

    // Initialize on page load
    window.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($_POST['query_type'])): ?>
        updateQueryList();
        <?php if (!empty($_POST['query_name'])): ?>
        document.getElementById('query-list').value = '<?= $_POST['query_name'] ?? '' ?>';
        loadQuery();
        <?php endif; ?>
        <?php endif; ?>
    });
    </script>

</div>
</body>
</html>