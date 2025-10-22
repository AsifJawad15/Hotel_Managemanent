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
        textarea {
            font-family: 'Courier New', monospace;
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #475569;
            padding: 15px;
            width: 100%;
            border-radius: 6px;
        }
        .result-table {
            margin: 20px 0;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
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
    <h2>Advanced Database Operations</h2>
    <p>Execute custom SQL queries to analyze and manage your database.</p>

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

    <!-- Custom SQL Section -->
    <div class="query-section">
        <h3>Custom SQL Query</h3>
        
        <form method="post">
            <div class="form-group">
                <label>SQL Query</label>
                <textarea id="custom-sql-query" name="sql_query" rows="10" placeholder="Enter your SQL query here or click a predefined query below..."><?= htmlspecialchars($_POST['sql_query'] ?? '') ?></textarea>
            </div>
            <button type="submit" name="execute_query" class="btn btn-primary">Execute Query</button>
        </form>

        <!-- Predefined Query Buttons -->
        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
            <h4 style="color: #475569; margin-bottom: 15px;">Quick Query Templates</h4>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button onclick="setCustomQuery('SELECT * FROM hotels ORDER BY hotel_name LIMIT 10;')" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #3b82f6; color: white; cursor: pointer; font-size: 13px;">
                    View Hotels
                </button>
                <button onclick="setCustomQuery('SELECT * FROM guests ORDER BY created_at DESC LIMIT 10;')" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #3b82f6; color: white; cursor: pointer; font-size: 13px;">
                    Recent Guests
                </button>
                <button onclick="setCustomQuery('SELECT b.booking_id, g.name as guest_name, h.hotel_name, r.room_number, b.check_in, b.check_out, b.booking_status, b.final_amount FROM bookings b JOIN guests g ON b.guest_id = g.guest_id JOIN rooms r ON b.room_id = r.room_id JOIN hotels h ON r.hotel_id = h.hotel_id ORDER BY b.created_at DESC LIMIT 10;')" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #3b82f6; color: white; cursor: pointer; font-size: 13px;">
                    Recent Bookings
                </button>
                <button onclick="setCustomQuery(`SELECT h.hotel_name, SUM(b.final_amount) as total_revenue, COUNT(b.booking_id) as total_bookings, AVG(b.final_amount) as avg_booking_value FROM hotels h LEFT JOIN rooms r ON h.hotel_id = r.hotel_id LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed' GROUP BY h.hotel_id, h.hotel_name ORDER BY total_revenue DESC;`)" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #059669; color: white; cursor: pointer; font-size: 13px;">
                    Revenue by Hotel
                </button>
                <button onclick="setCustomQuery(`SELECT YEAR(b.check_in) as year, MONTH(b.check_in) as month, SUM(b.final_amount) as monthly_revenue, COUNT(b.booking_id) as monthly_bookings FROM bookings b WHERE b.booking_status = 'Completed' GROUP BY YEAR(b.check_in), MONTH(b.check_in) ORDER BY year DESC, month DESC LIMIT 12;`)" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #059669; color: white; cursor: pointer; font-size: 13px;">
                    Monthly Revenue
                </button>
                <button onclick="setCustomQuery(`SELECT h.hotel_name, rt.type_name, COUNT(r.room_id) as total_rooms, AVG(r.price) as avg_price FROM hotels h JOIN rooms r ON h.hotel_id = r.hotel_id JOIN room_types rt ON r.type_id = rt.type_id GROUP BY h.hotel_id, h.hotel_name, rt.type_id, rt.type_name ORDER BY h.hotel_name, rt.type_name;`)" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #7c3aed; color: white; cursor: pointer; font-size: 13px;">
                    Rooms by Type
                </button>
                <button onclick="setCustomQuery(`SELECT g.name, g.membership_level, SUM(b.final_amount) as total_spent, COUNT(b.booking_id) as total_bookings FROM guests g JOIN bookings b ON g.guest_id = b.guest_id WHERE b.booking_status = 'Completed' GROUP BY g.guest_id, g.name, g.membership_level ORDER BY total_spent DESC LIMIT 10;`)" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #dc2626; color: white; cursor: pointer; font-size: 13px;">
                    Top Spending Guests
                </button>
                <button onclick="setCustomQuery(`SELECT h.hotel_name, AVG(r.rating) as avg_rating, COUNT(r.review_id) as total_reviews FROM hotels h LEFT JOIN reviews r ON h.hotel_id = r.hotel_id AND r.is_approved = 1 GROUP BY h.hotel_id, h.hotel_name HAVING total_reviews > 0 ORDER BY avg_rating DESC;`)" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #ea580c; color: white; cursor: pointer; font-size: 13px;">
                    Hotel Ratings
                </button>
                <button onclick="setCustomQuery(`SELECT e.event_name, h.hotel_name, e.event_date, e.start_time, e.end_time, e.max_participants, e.current_participants, e.price, e.event_status FROM events e JOIN hotels h ON e.hotel_id = h.hotel_id WHERE e.event_status = 'Upcoming' ORDER BY e.event_date ASC LIMIT 10;`)" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #0891b2; color: white; cursor: pointer; font-size: 13px;">
                    Upcoming Events
                </button>
                <button onclick="setCustomQuery(`SELECT GetSeason(CURDATE()) as current_season, GetSeason('2025-12-25') as christmas_season, GetSeason('2025-07-15') as summer_season;`)" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #65a30d; color: white; cursor: pointer; font-size: 13px;">
                    Season Check
                </button>
                <button onclick="setCustomQuery(`SELECT r.room_id, h.hotel_name, r.room_number, rt.type_name, r.price as base_price, CalculateDynamicPrice(r.price, '2025-12-25', rt.type_name) as christmas_price, CalculateDynamicPrice(r.price, '2025-07-15', rt.type_name) as summer_price FROM rooms r JOIN hotels h ON r.hotel_id = h.hotel_id JOIN room_types rt ON r.type_id = rt.type_id LIMIT 10;`)" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #db2777; color: white; cursor: pointer; font-size: 13px;">
                    Dynamic Pricing
                </button>
                <button onclick="setCustomQuery('SELECT guest_id, name, date_of_birth, CalculateAge(date_of_birth) as age FROM guests WHERE date_of_birth IS NOT NULL ORDER BY age DESC LIMIT 10;')" 
                        style="padding: 8px 14px; border: none; border-radius: 6px; background: #4f46e5; color: white; cursor: pointer; font-size: 13px;">
                    Guest Ages
                </button>
            </div>
        </div>
    </div>

    <script>
    function setCustomQuery(query) {
        document.getElementById('custom-sql-query').value = query;
        // Scroll to the textarea
        document.getElementById('custom-sql-query').scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Focus on the textarea
        document.getElementById('custom-sql-query').focus();
    }
    </script>

    <!-- Functions Demonstration Section -->
    <div class="query-section" style="background: #f0fdf4;">
        <h3>📊 Interactive Database Functions</h3>
        <p>Test custom SQL functions with your own parameters:</p>

        <!-- CalculateAge Function -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 6px; border: 1px solid #d1fae5;">
            <h4 style="color: #059669; margin-top: 0;">1. CalculateAge Function</h4>
            <p><strong>Purpose:</strong> Calculate age from date of birth</p>
            <form method="post" style="margin: 15px 0;">
                <div style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Date of Birth:</label>
                        <input type="date" name="age_dob" value="<?= $_POST['age_dob'] ?? '2000-01-01' ?>" 
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <button type="submit" name="calc_age" class="btn btn-primary" style="padding: 8px 16px;">Calculate Age</button>
                </div>
            </form>
            <?php
            if (isset($_POST['calc_age']) && !empty($_POST['age_dob'])) {
                $dob = $conn->real_escape_string($_POST['age_dob']);
                $age_query = "SELECT CalculateAge('$dob') as age";
                $age_result = $conn->query($age_query);
                if ($age_result && $row = $age_result->fetch_assoc()) {
                    echo "<div style='background: #dcfce7; padding: 12px; border-radius: 6px; margin-top: 10px;'>";
                    echo "<strong style='color: #059669;'>Result:</strong> Age is <strong>{$row['age']} years</strong>";
                    echo "</div>";
                }
            }
            ?>
            <p style="font-size: 12px; color: #666; margin-top: 10px;"><strong>SQL:</strong> <code>SELECT CalculateAge('<?= $_POST['age_dob'] ?? '2000-01-01' ?>');</code></p>
        </div>

        <!-- GetSeason Function -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 6px; border: 1px solid #d1fae5;">
            <h4 style="color: #059669; margin-top: 0;">2. GetSeason Function</h4>
            <p><strong>Purpose:</strong> Determine season for dynamic pricing (Peak/High/Normal)</p>
            <form method="post" style="margin: 15px 0;">
                <div style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Check Date:</label>
                        <input type="date" name="season_date" value="<?= $_POST['season_date'] ?? date('Y-m-d') ?>" 
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <button type="submit" name="calc_season" class="btn btn-primary" style="padding: 8px 16px;">Get Season</button>
                </div>
            </form>
            <?php
            if (isset($_POST['calc_season']) && !empty($_POST['season_date'])) {
                $check_date = $conn->real_escape_string($_POST['season_date']);
                $season_query = "SELECT GetSeason('$check_date') as season";
                $season_result = $conn->query($season_query);
                if ($season_result && $row = $season_result->fetch_assoc()) {
                    $season_color = $row['season'] == 'Peak' ? '#dc2626' : ($row['season'] == 'High' ? '#ea580c' : '#059669');
                    echo "<div style='background: #dcfce7; padding: 12px; border-radius: 6px; margin-top: 10px;'>";
                    echo "<strong style='color: #059669;'>Result:</strong> Date falls in <strong style='color: $season_color;'>{$row['season']}</strong> season";
                    echo "</div>";
                }
            }
            ?>
            <p style="font-size: 12px; color: #666; margin-top: 10px;"><strong>SQL:</strong> <code>SELECT GetSeason('<?= $_POST['season_date'] ?? date('Y-m-d') ?>');</code></p>
        </div>

        <!-- CalculateDynamicPrice Function -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 6px; border: 1px solid #d1fae5;">
            <h4 style="color: #059669; margin-top: 0;">3. CalculateDynamicPrice Function</h4>
            <p><strong>Purpose:</strong> Calculate dynamic pricing based on season, advance booking, and room type</p>
            <form method="post" style="margin: 15px 0;">
                <div style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Base Price ($):</label>
                        <input type="number" name="base_price" value="<?= $_POST['base_price'] ?? '100' ?>" step="0.01" 
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Check-in Date:</label>
                        <input type="date" name="price_date" value="<?= $_POST['price_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>" 
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Room Type:</label>
                        <select name="room_type" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                            <?php
                            $types_query = "SELECT DISTINCT type_name FROM room_types ORDER BY type_name";
                            $types_result = $conn->query($types_query);
                            $selected_type = $_POST['room_type'] ?? 'Standard';
                            while ($type = $types_result->fetch_assoc()) {
                                $selected = ($type['type_name'] == $selected_type) ? 'selected' : '';
                                echo "<option value='{$type['type_name']}' $selected>{$type['type_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="calc_price" class="btn btn-primary" style="padding: 8px 16px;">Calculate Price</button>
                </div>
            </form>
            <?php
            if (isset($_POST['calc_price']) && !empty($_POST['base_price']) && !empty($_POST['price_date']) && !empty($_POST['room_type'])) {
                $base = $conn->real_escape_string($_POST['base_price']);
                $date = $conn->real_escape_string($_POST['price_date']);
                $type = $conn->real_escape_string($_POST['room_type']);
                $price_query = "SELECT CalculateDynamicPrice($base, '$date', '$type') as dynamic_price, GetSeason('$date') as season";
                $price_result = $conn->query($price_query);
                if ($price_result && $row = $price_result->fetch_assoc()) {
                    $diff = $row['dynamic_price'] - $base;
                    $diff_sign = $diff >= 0 ? '+' : '';
                    echo "<div style='background: #dcfce7; padding: 12px; border-radius: 6px; margin-top: 10px;'>";
                    echo "<strong style='color: #059669;'>Result:</strong> Base Price: \$$base → Dynamic Price: <strong style='color: #dc2626;'>\${$row['dynamic_price']}</strong> ";
                    echo "({$diff_sign}" . number_format($diff, 2) . ") - Season: <strong>{$row['season']}</strong>";
                    echo "</div>";
                }
            }
            ?>
            <p style="font-size: 12px; color: #666; margin-top: 10px;"><strong>SQL:</strong> <code>SELECT CalculateDynamicPrice(<?= $_POST['base_price'] ?? '100' ?>, '<?= $_POST['price_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>', '<?= $_POST['room_type'] ?? 'Standard' ?>');</code></p>
        </div>
    </div>

    <!-- Procedures Interactive Section -->
    <div class="query-section" style="background: #eff6ff;">
        <h3>🔧 Interactive Stored Procedures</h3>
        <p>Execute stored procedures with your own parameters:</p>

        <!-- CalculateLoyaltyPoints Procedure -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 6px; border: 1px solid #bfdbfe;">
            <h4 style="color: #2563eb; margin-top: 0;">1. CalculateLoyaltyPoints</h4>
            <p><strong>Purpose:</strong> Calculate and award loyalty points based on booking amount</p>
            <form method="post" style="margin: 15px 0;">
                <div style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Booking ID:</label>
                        <select name="loyalty_booking_id" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 200px;">
                            <?php
                            $bookings_query = "SELECT b.booking_id, g.name, h.hotel_name, b.final_amount 
                                             FROM bookings b 
                                             JOIN guests g ON b.guest_id = g.guest_id 
                                             JOIN rooms r ON b.room_id = r.room_id 
                                             JOIN hotels h ON r.hotel_id = h.hotel_id 
                                             ORDER BY b.booking_id DESC LIMIT 20";
                            $bookings_result = $conn->query($bookings_query);
                            while ($booking = $bookings_result->fetch_assoc()) {
                                $selected = (isset($_POST['loyalty_booking_id']) && $_POST['loyalty_booking_id'] == $booking['booking_id']) ? 'selected' : '';
                                echo "<option value='{$booking['booking_id']}' $selected>ID: {$booking['booking_id']} - {$booking['name']} @ {$booking['hotel_name']} (\${$booking['final_amount']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="exec_loyalty" class="btn btn-primary" style="padding: 8px 16px;">Calculate Points</button>
                </div>
            </form>
            <?php
            if (isset($_POST['exec_loyalty']) && !empty($_POST['loyalty_booking_id'])) {
                $booking_id = intval($_POST['loyalty_booking_id']);
                
                // Get guest info before
                $before_query = "SELECT g.name, g.loyalty_points, g.membership_level, b.final_amount 
                               FROM guests g 
                               JOIN bookings b ON g.guest_id = b.guest_id 
                               WHERE b.booking_id = $booking_id";
                $before_result = $conn->query($before_query);
                $before = $before_result->fetch_assoc();
                
                // Execute procedure
                $proc_query = "CALL CalculateLoyaltyPoints($booking_id)";
                $proc_result = $conn->query($proc_query);
                
                // Free the procedure result set
                if ($proc_result) {
                    while ($conn->more_results()) {
                        $conn->next_result();
                    }
                }
                
                // Get guest info after
                $after_query = "SELECT g.loyalty_points, g.membership_level 
                              FROM guests g 
                              JOIN bookings b ON g.guest_id = b.guest_id 
                              WHERE b.booking_id = $booking_id";
                $after_result = $conn->query($after_query);
                $after = $after_result->fetch_assoc();
                
                $points_earned = $after['loyalty_points'] - $before['loyalty_points'];
                echo "<div style='background: #dbeafe; padding: 12px; border-radius: 6px; margin-top: 10px;'>";
                echo "<strong style='color: #2563eb;'>Result for {$before['name']}:</strong><br>";
                echo "Booking Amount: <strong>\${$before['final_amount']}</strong> | ";
                echo "Points Earned: <strong style='color: #059669;'>+{$points_earned}</strong> | ";
                echo "Total Points: <strong>{$after['loyalty_points']}</strong> | ";
                if ($before['membership_level'] != $after['membership_level']) {
                    echo "Membership: <strong style='color: #dc2626;'>{$before['membership_level']} → {$after['membership_level']}</strong> 🎉";
                } else {
                    echo "Membership: <strong>{$after['membership_level']}</strong>";
                }
                echo "</div>";
            }
            ?>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">Awards 1 point per $10 spent and upgrades membership tier automatically</p>
        </div>

        <!-- CalculateRoomRevenue Procedure -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 6px; border: 1px solid #bfdbfe;">
            <h4 style="color: #2563eb; margin-top: 0;">2. CalculateRoomRevenue</h4>
            <p><strong>Purpose:</strong> Calculate total revenue for a hotel within date range</p>
            <form method="post" style="margin: 15px 0;">
                <div style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Hotel:</label>
                        <select name="revenue_hotel_id" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 180px;">
                            <?php
                            $hotels_query = "SELECT hotel_id, hotel_name FROM hotels ORDER BY hotel_name";
                            $hotels_result = $conn->query($hotels_query);
                            while ($hotel = $hotels_result->fetch_assoc()) {
                                $selected = (isset($_POST['revenue_hotel_id']) && $_POST['revenue_hotel_id'] == $hotel['hotel_id']) ? 'selected' : '';
                                echo "<option value='{$hotel['hotel_id']}' $selected>{$hotel['hotel_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Start Date:</label>
                        <input type="date" name="revenue_start" value="<?= $_POST['revenue_start'] ?? date('Y-01-01') ?>" 
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">End Date:</label>
                        <input type="date" name="revenue_end" value="<?= $_POST['revenue_end'] ?? date('Y-12-31') ?>" 
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <button type="submit" name="exec_revenue" class="btn btn-primary" style="padding: 8px 16px;">Calculate Revenue</button>
                </div>
            </form>
            <?php
            if (isset($_POST['exec_revenue']) && !empty($_POST['revenue_hotel_id'])) {
                $hotel_id = intval($_POST['revenue_hotel_id']);
                $start = $conn->real_escape_string($_POST['revenue_start']);
                $end = $conn->real_escape_string($_POST['revenue_end']);
                
                // Get hotel name first
                $hotel_query = "SELECT hotel_name FROM hotels WHERE hotel_id = $hotel_id";
                $hotel_result = $conn->query($hotel_query);
                $hotel = $hotel_result->fetch_assoc();
                
                // Call procedure
                $proc_query = "CALL CalculateRoomRevenue($hotel_id, '$start', '$end', @revenue)";
                $proc_result = $conn->query($proc_query);
                
                // Free the procedure result set
                if ($proc_result) {
                    while ($conn->more_results()) {
                        $conn->next_result();
                    }
                }
                
                // Now get the OUT parameter
                $result_query = "SELECT @revenue as total_revenue";
                $result = $conn->query($result_query);
                $row = $result->fetch_assoc();
                
                echo "<div style='background: #dbeafe; padding: 12px; border-radius: 6px; margin-top: 10px;'>";
                echo "<strong style='color: #2563eb;'>Result for {$hotel['hotel_name']}:</strong><br>";
                echo "Period: <strong>$start to $end</strong> | ";
                echo "Total Revenue: <strong style='color: #059669; font-size: 18px;'>\$" . number_format($row['total_revenue'], 2) . "</strong>";
                echo "</div>";
            }
            ?>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">Uses OUT parameter to return total revenue from completed bookings</p>
        </div>

        <!-- GenerateMonthlyHotelReport Procedure -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 6px; border: 1px solid #bfdbfe;">
            <h4 style="color: #2563eb; margin-top: 0;">3. GenerateMonthlyHotelReport</h4>
            <p><strong>Purpose:</strong> Generate comprehensive monthly performance report</p>
            <form method="post" style="margin: 15px 0;">
                <div style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Hotel:</label>
                        <select name="report_hotel_id" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 180px;">
                            <?php
                            $hotels_query = "SELECT hotel_id, hotel_name FROM hotels ORDER BY hotel_name";
                            $hotels_result = $conn->query($hotels_query);
                            while ($hotel = $hotels_result->fetch_assoc()) {
                                $selected = (isset($_POST['report_hotel_id']) && $_POST['report_hotel_id'] == $hotel['hotel_id']) ? 'selected' : '';
                                echo "<option value='{$hotel['hotel_id']}' $selected>{$hotel['hotel_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Year:</label>
                        <input type="number" name="report_year" value="<?= $_POST['report_year'] ?? date('Y') ?>" min="2020" max="2030"
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Month:</label>
                        <select name="report_month" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                            <?php
                            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                            $current_month = $_POST['report_month'] ?? date('n');
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($i == $current_month) ? 'selected' : '';
                                $month_index = $i - 1;
                                echo "<option value='$i' $selected>{$months[$month_index]}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="exec_report" class="btn btn-primary" style="padding: 8px 16px;">Generate Report</button>
                </div>
            </form>
            <?php
            if (isset($_POST['exec_report']) && !empty($_POST['report_hotel_id'])) {
                $hotel_id = intval($_POST['report_hotel_id']);
                $year = intval($_POST['report_year']);
                $month = intval($_POST['report_month']);
                
                // Get hotel name first, before calling procedure
                $hotel_query = "SELECT hotel_name FROM hotels WHERE hotel_id = $hotel_id";
                $hotel_result = $conn->query($hotel_query);
                $hotel = $hotel_result->fetch_assoc();
                
                // Now call the procedure
                $proc_query = "CALL GenerateMonthlyHotelReport($hotel_id, $year, $month)";
                $report_result = $conn->query($proc_query);
                
                if ($report_result && $report_result->num_rows > 0) {
                    $report = $report_result->fetch_assoc();
                    
                    echo "<div style='background: #dbeafe; padding: 15px; border-radius: 6px; margin-top: 10px;'>";
                    echo "<strong style='color: #2563eb; font-size: 16px;'>Monthly Report - {$hotel['hotel_name']}</strong><br>";
                    echo "<strong>Period:</strong> " . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . "<br><br>";
                    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;'>";
                    echo "<div style='background: white; padding: 10px; border-radius: 4px;'><strong>Total Bookings:</strong> " . ($report['total_bookings'] ?? 0) . "</div>";
                    echo "<div style='background: white; padding: 10px; border-radius: 4px;'><strong>Total Revenue:</strong> \$" . number_format($report['total_revenue'] ?? 0, 2) . "</div>";
                    echo "<div style='background: white; padding: 10px; border-radius: 4px;'><strong>Unique Guests:</strong> " . ($report['unique_guests'] ?? 0) . "</div>";
                    echo "<div style='background: white; padding: 10px; border-radius: 4px;'><strong>Avg Rating:</strong> " . ($report['avg_rating'] ?? 0) . "/5.0</div>";
                    echo "<div style='background: white; padding: 10px; border-radius: 4px;'><strong>Events Hosted:</strong> " . ($report['events_count'] ?? 0) . "</div>";
                    echo "</div></div>";
                }
            }
            ?>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">Returns comprehensive monthly statistics including bookings, revenue, guests, ratings, and events</p>
        </div>

        <!-- GetAvailableRooms Procedure -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 6px; border: 1px solid #bfdbfe;">
            <h4 style="color: #2563eb; margin-top: 0;">4. GetAvailableRooms</h4>
            <p><strong>Purpose:</strong> Find available rooms for specified dates and criteria</p>
            <form method="post" style="margin: 15px 0;">
                <div style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Hotel:</label>
                        <select name="avail_hotel_id" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 180px;">
                            <?php
                            $hotels_query = "SELECT hotel_id, hotel_name FROM hotels ORDER BY hotel_name";
                            $hotels_result = $conn->query($hotels_query);
                            while ($hotel = $hotels_result->fetch_assoc()) {
                                $selected = (isset($_POST['avail_hotel_id']) && $_POST['avail_hotel_id'] == $hotel['hotel_id']) ? 'selected' : '';
                                echo "<option value='{$hotel['hotel_id']}' $selected>{$hotel['hotel_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Check-in:</label>
                        <input type="date" name="avail_checkin" value="<?= $_POST['avail_checkin'] ?? date('Y-m-d', strtotime('+1 day')) ?>" 
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Check-out:</label>
                        <input type="date" name="avail_checkout" value="<?= $_POST['avail_checkout'] ?? date('Y-m-d', strtotime('+3 days')) ?>" 
                               style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 13px;">Room Type (Optional):</label>
                        <select name="avail_type_id" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;">
                            <option value="">All Types</option>
                            <?php
                            $types_query = "SELECT type_id, type_name FROM room_types ORDER BY type_name";
                            $types_result = $conn->query($types_query);
                            while ($type = $types_result->fetch_assoc()) {
                                $selected = (isset($_POST['avail_type_id']) && $_POST['avail_type_id'] == $type['type_id']) ? 'selected' : '';
                                echo "<option value='{$type['type_id']}' $selected>{$type['type_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="exec_available" class="btn btn-primary" style="padding: 8px 16px;">Find Rooms</button>
                </div>
            </form>
            <?php
            if (isset($_POST['exec_available']) && !empty($_POST['avail_hotel_id'])) {
                $hotel_id = intval($_POST['avail_hotel_id']);
                $checkin = $conn->real_escape_string($_POST['avail_checkin']);
                $checkout = $conn->real_escape_string($_POST['avail_checkout']);
                $type_id = !empty($_POST['avail_type_id']) ? intval($_POST['avail_type_id']) : 'NULL';
                
                $proc_query = "CALL GetAvailableRooms($hotel_id, '$checkin', '$checkout', $type_id)";
                $rooms_result = $conn->query($proc_query);
                
                if ($rooms_result && $rooms_result->num_rows > 0) {
                    echo "<div style='margin-top: 10px; overflow-x: auto;'>";
                    echo "<table class='table'>";
                    echo "<thead><tr><th>Room Number</th><th>Type</th><th>Capacity</th><th>Price</th><th>Status</th></tr></thead><tbody>";
                    while ($room = $rooms_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>{$room['room_number']}</strong></td>";
                        echo "<td>{$room['type_name']}</td>";
                        $capacity = isset($room['capacity']) ? $room['capacity'] . ' guests' : 'N/A';
                        echo "<td>$capacity</td>";
                        echo "<td style='color: #059669; font-weight: bold;'>\${$room['price']}/night</td>";
                        echo "<td><span style='background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>Available</span></td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<div style='background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-top: 10px;'>";
                    echo "No rooms available for the selected dates.";
                    echo "</div>";
                }
            }
            ?>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">Finds rooms not booked during the specified date range</p>
        </div>

        <!-- ProcessLoyaltyUpgrades Procedure -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 6px; border: 1px solid #bfdbfe;">
            <h4 style="color: #2563eb; margin-top: 0;">5. ProcessLoyaltyUpgrades</h4>
            <p><strong>Purpose:</strong> Process batch loyalty tier upgrades for all guests</p>
            <form method="post" style="margin: 15px 0;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button type="submit" name="exec_loyalty_upgrade" class="btn btn-primary" style="padding: 8px 16px;">Process All Upgrades</button>
                    <span style="font-size: 13px; color: #666;">⚠️ This will update membership levels for all guests based on their loyalty points</span>
                </div>
            </form>
            <?php
            if (isset($_POST['exec_loyalty_upgrade'])) {
                // Get counts before
                $before_query = "SELECT membership_level, COUNT(*) as count FROM guests GROUP BY membership_level";
                $before_result = $conn->query($before_query);
                $before = [];
                while ($row = $before_result->fetch_assoc()) {
                    $before[$row['membership_level']] = $row['count'];
                }
                
                // Execute procedure
                $proc_query = "CALL ProcessLoyaltyUpgrades()";
                $proc_result = $conn->query($proc_query);
                
                // Free the procedure result set
                if ($proc_result) {
                    while ($conn->more_results()) {
                        $conn->next_result();
                    }
                }
                
                // Get counts after
                $after_query = "SELECT membership_level, COUNT(*) as count FROM guests GROUP BY membership_level";
                $after_result = $conn->query($after_query);
                $after = [];
                while ($row = $after_result->fetch_assoc()) {
                    $after[$row['membership_level']] = $row['count'];
                }
                
                echo "<div style='background: #dbeafe; padding: 15px; border-radius: 6px; margin-top: 10px;'>";
                echo "<strong style='color: #2563eb; font-size: 16px;'>Loyalty Upgrades Processed Successfully! 🎉</strong><br><br>";
                echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;'>";
                foreach (['Bronze', 'Silver', 'Gold', 'Platinum'] as $level) {
                    $before_count = $before[$level] ?? 0;
                    $after_count = $after[$level] ?? 0;
                    $diff = $after_count - $before_count;
                    $diff_text = $diff > 0 ? "+$diff" : ($diff < 0 ? "$diff" : "0");
                    $color = $level == 'Bronze' ? '#cd7f32' : ($level == 'Silver' ? '#c0c0c0' : ($level == 'Gold' ? '#ffd700' : '#e5e4e2'));
                    echo "<div style='background: white; padding: 10px; border-radius: 4px; border-left: 4px solid $color;'>";
                    echo "<strong>$level:</strong> $after_count";
                    if ($diff != 0) echo " <span style='color: " . ($diff > 0 ? '#059669' : '#dc2626') . ";'>($diff_text)</span>";
                    echo "</div>";
                }
                echo "</div></div>";
            }
            ?>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">Bronze: 0-99 points | Silver: 100-499 | Gold: 500-999 | Platinum: 1000+</p>
        </div>
    </div>
</div>
</body>
</html>
