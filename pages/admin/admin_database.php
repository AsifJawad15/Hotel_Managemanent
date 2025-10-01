<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_admin.php");

$result_message = '';
$query_result = null;
$error_message = '';

// Handle procedure execution
if (isset($_POST['execute_procedure'])) {
    $procedure_name = $_POST['procedure_name'];
    $parameters = $_POST['parameters'] ?? '';
    
    try {
        if (!empty($parameters)) {
            $sql = "CALL $procedure_name($parameters)";
        } else {
            $sql = "CALL $procedure_name()";
        }
        
        $query_result = $conn->query($sql);
        
        if ($query_result === TRUE) {
            $result_message = "Procedure executed successfully.";
        } elseif ($query_result === FALSE) {
            $error_message = "Procedure failed: " . $conn->error;
        } else {
            $result_message = "Procedure executed successfully. Rows returned: " . $query_result->num_rows;
        }
    } catch (Exception $e) {
        $error_message = "Error executing procedure: " . $e->getMessage();
    }
}

// Handle custom SQL execution for reports
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

// Predefined reporting procedures
$predefined_procedures = [
    'Generate Monthly Hotel Report' => [
        'name' => 'GenerateMonthlyHotelReport', 
        'params' => '2024, 11',
        'description' => 'Generates comprehensive monthly performance report for all hotels'
    ],
    'Calculate Total Revenue' => [
        'name' => 'CalculateRoomRevenue', 
        'params' => '1, 2024',
        'description' => 'Calculates total room revenue for a specific hotel and year'
    ]
];

// Predefined revenue and reporting queries
$predefined_queries = [
    'Total Revenue by Hotel' => "
        SELECT h.hotel_name, 
               SUM(b.final_amount) as total_revenue,
               COUNT(b.booking_id) as total_bookings,
               AVG(b.final_amount) as avg_booking_value
        FROM hotels h
        LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
        LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed'
        GROUP BY h.hotel_id, h.hotel_name
        ORDER BY total_revenue DESC
    ",
    'Monthly Revenue Trend' => "
        SELECT YEAR(b.check_in) as year,
               MONTH(b.check_in) as month,
               SUM(b.final_amount) as monthly_revenue,
               COUNT(b.booking_id) as monthly_bookings
        FROM bookings b
        WHERE b.booking_status = 'Completed'
        GROUP BY YEAR(b.check_in), MONTH(b.check_in)
        ORDER BY year DESC, month DESC
        LIMIT 12
    ",
    'Hotel Performance Summary' => "
        SELECT h.hotel_name,
               h.star_rating,
               COUNT(DISTINCT r.room_id) as total_rooms,
               COUNT(DISTINCT b.booking_id) as total_bookings,
               SUM(b.final_amount) as total_revenue,
               AVG(rev.rating) as avg_rating
        FROM hotels h
        LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
        LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed'
        LEFT JOIN reviews rev ON h.hotel_id = rev.hotel_id AND rev.is_approved = TRUE
        GROUP BY h.hotel_id, h.hotel_name, h.star_rating
        ORDER BY total_revenue DESC
    "
];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Database Reports - Admin</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .report-section { 
            background: #f8fafc; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .procedure-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 15px 0;
        }
        .procedure-buttons button {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            background: #059669;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }
        .procedure-buttons button:hover {
            background: #047857;
        }
        .query-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 15px 0;
        }
        .query-buttons button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            background: #3b82f6;
            color: white;
            cursor: pointer;
            font-size: 12px;
        }
        .query-buttons button:hover {
            background: #2563eb;
        }
        textarea {
            font-family: 'Courier New', monospace;
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #374151;
            border-radius: 6px;
            padding: 15px;
        }
        .result-table {
            margin: 20px 0;
            overflow-x: auto;
        }
        .success {
            background: #d1fae5;
            border: 1px solid #059669;
            color: #065f46;
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .error {
            background: #fee2e2;
            border: 1px solid #dc2626;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .procedure-info {
            background: #eff6ff;
            border: 1px solid #3b82f6;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="header">
    <div>Database Reports & Analytics</div>
    <div class="nav">
        <a href="admin_home.php">Dashboard</a>
        <a href="admin_room_price_update.php">Price Management</a>
        <a href="admin_logout.php">Logout</a>
    </div>
</div>

<div class="main">
    <h2>Revenue Reports & Monthly Analytics</h2>
    <p>Execute stored procedures for monthly reports and revenue calculations.</p>

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

    <!-- Stored Procedures Section -->
    <div class="report-section">
        <h3>📊 Revenue & Monthly Report Procedures</h3>
        
        <div class="procedure-info">
            <h4>Available Procedures:</h4>
            <ul>
                <li><strong>GenerateMonthlyHotelReport</strong> - Comprehensive monthly performance analysis</li>
                <li><strong>CalculateRoomRevenue</strong> - Calculate total revenue for specific hotel/year</li>
            </ul>
        </div>

        <form method="post">
            <div class="form-group">
                <label for="procedure-name">Select Procedure:</label>
                <select id="procedure-name" name="procedure_name" required>
                    <option value="">Choose a procedure...</option>
                    <?php foreach ($predefined_procedures as $label => $proc): ?>
                        <option value="<?= htmlspecialchars($proc['name']) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="procedure-params">Parameters (comma-separated):</label>
                <input type="text" id="procedure-params" name="parameters" placeholder="e.g., 2024, 11">
                <small>Leave empty for procedures without parameters</small>
            </div>
            
            <button type="submit" name="execute_procedure" class="btn btn-primary">Execute Procedure</button>
        </form>

        <div class="procedure-buttons">
            <?php foreach ($predefined_procedures as $label => $proc): ?>
                <button onclick="setProcedure('<?= htmlspecialchars($proc['name']) ?>', '<?= htmlspecialchars($proc['params']) ?>')">
                    <?= htmlspecialchars($label) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Revenue Queries Section -->
    <div class="report-section">
        <h3>💰 Revenue Analysis Queries</h3>
        
        <form method="post">
            <div class="form-group">
                <label for="predefined-query">Custom SQL Query:</label>
                <textarea id="predefined-query" name="sql_query" rows="8" cols="100" placeholder="Enter your SQL query here..."></textarea>
            </div>
            
            <button type="submit" name="execute_query" class="btn btn-primary">Execute Query</button>
        </form>

        <div class="query-buttons">
            <?php foreach ($predefined_queries as $label => $query): ?>
                <button onclick="setQuery(`<?= htmlspecialchars(trim($query)) ?>`)">
                    <?= htmlspecialchars($label) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function setQuery(query) {
    document.getElementById('predefined-query').value = query;
}

function setProcedure(name, params) {
    document.getElementById('procedure-name').value = name;
    document.getElementById('procedure-params').value = params;
}
</script>
</body>
</html>
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

// Predefined reporting procedures
$predefined_procedures = [
    'Generate Monthly Hotel Report' => [
        'name' => 'GenerateMonthlyHotelReport', 
        'params' => '2024, 11',
        'description' => 'Generates comprehensive monthly performance report for all hotels'
    ],
    'Calculate Total Revenue' => [
        'name' => 'CalculateRoomRevenue', 
        'params' => '1, 2024',
        'description' => 'Calculates total room revenue for a specific hotel and year'
    ]
];

// Predefined revenue and reporting queries
$predefined_queries = [
    'Total Revenue by Hotel' => "
        SELECT h.hotel_name, 
               SUM(b.final_amount) as total_revenue,
               COUNT(b.booking_id) as total_bookings,
               AVG(b.final_amount) as avg_booking_value
        FROM hotels h
        LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
        LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed'
        GROUP BY h.hotel_id, h.hotel_name
        ORDER BY total_revenue DESC
    ",
    'Monthly Revenue Trend' => "
        SELECT YEAR(b.check_in) as year,
               MONTH(b.check_in) as month,
               SUM(b.final_amount) as monthly_revenue,
               COUNT(b.booking_id) as monthly_bookings
        FROM bookings b
        WHERE b.booking_status = 'Completed'
        GROUP BY YEAR(b.check_in), MONTH(b.check_in)
        ORDER BY year DESC, month DESC
        LIMIT 12
    ",
    'Hotel Performance Summary' => "
        SELECT h.hotel_name,
               h.star_rating,
               COUNT(DISTINCT r.room_id) as total_rooms,
               COUNT(DISTINCT b.booking_id) as total_bookings,
               SUM(b.final_amount) as total_revenue,
               AVG(rev.rating) as avg_rating
        FROM hotels h
        LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
        LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed'
        LEFT JOIN reviews rev ON h.hotel_id = rev.hotel_id AND rev.is_approved = TRUE
        GROUP BY h.hotel_id, h.hotel_name, h.star_rating
        ORDER BY total_revenue DESC
    "
];
?>

// Predefined queries for quick access
$predefined_queries = [
    'Hotel Performance' => "SELECT * FROM hotel_performance ORDER BY total_revenue DESC LIMIT 10",
    'Guest Booking History' => "SELECT * FROM guest_booking_history ORDER BY created_at DESC LIMIT 20",
    'Room Occupancy' => "SELECT * FROM room_occupancy WHERE current_status = 'Occupied' LIMIT 20",
    'Monthly Revenue' => "SELECT * FROM monthly_revenue_report WHERE revenue_year = YEAR(CURDATE()) ORDER BY revenue_month DESC",
    'Event Participation' => "SELECT * FROM event_participation ORDER BY event_date DESC LIMIT 10",
    'Top Spending Guests' => "
        SELECT g.name, g.membership_level, SUM(b.final_amount) as total_spent
        FROM guests g
        JOIN bookings b ON g.guest_id = b.guest_id
        WHERE b.booking_status = 'Completed'
        GROUP BY g.guest_id, g.name, g.membership_level
        ORDER BY total_spent DESC
        LIMIT 10
    ",
    'Seasonal Analysis' => "
        SELECT 
            GetSeason(b.check_in) as season,
            COUNT(b.booking_id) as total_bookings,
            SUM(b.final_amount) as total_revenue,
            AVG(b.final_amount) as avg_booking_value
        FROM bookings b
        WHERE b.booking_status = 'Completed'
        AND b.check_in >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
        GROUP BY GetSeason(b.check_in)
        ORDER BY total_revenue DESC
    ",
    'Hotel Analytics' => "
        SELECT 
            h.hotel_name,
            h.city,
            COUNT(DISTINCT r.room_id) as total_rooms,
            COUNT(DISTINCT b.booking_id) as total_bookings,
            AVG(rev.rating) as avg_rating,
            SUM(b.final_amount) as total_revenue
        FROM hotels h
        LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
        LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed'
        LEFT JOIN reviews rev ON h.hotel_id = rev.hotel_id
        GROUP BY h.hotel_id, h.hotel_name, h.city
        ORDER BY total_revenue DESC
    "
];

$predefined_procedures = [
    'Update Room Prices' => ['name' => 'UpdateRoomPricesBasedOnDemand', 'params' => ''],
    'Generate Monthly Report' => ['name' => 'GenerateMonthlyHotelReport', 'params' => '2024, 11'],
    'Process Loyalty Upgrades' => ['name' => 'ProcessLoyaltyUpgrades', 'params' => ''],
    'Schedule Maintenance' => ['name' => 'ScheduleRoomMaintenance', 'params' => ''],
    'Get Available Rooms' => ['name' => 'GetAvailableRooms', 'params' => '1, "2024-12-15", "2024-12-18", NULL'],
    'Calculate Revenue' => ['name' => 'CalculateRoomRevenue', 'params' => '1, 2024']
];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Database Query Interface - Admin</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .query-interface { margin: 20px 0; }
        .query-section { 
            background: #f8fafc; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .query-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 15px 0;
        }
        .query-buttons button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            background: #3b82f6;
            color: white;
            cursor: pointer;
            font-size: 12px;
        }
        .query-buttons button:hover {
            background: #2563eb;
        }
        textarea {
            font-family: 'Courier New', monospace;
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #475569;
            padding: 15px;
        }
        .result-table {
            max-height: 400px;
            overflow: auto;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .result-table table {
            margin: 0;
        }
        .success { 
            background: #dcfce7; 
            color: #166534; 
            padding: 10px; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        .error { 
            background: #fef2f2; 
            color: #dc2626; 
            padding: 10px; 
            border-radius: 6px; 
            margin: 10px 0;
        }
        .tabs {
            display: flex;
            background: #f1f5f9;
            border-radius: 6px 6px 0 0;
            margin: 20px 0 0 0;
        }
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        .tab.active {
            background: white;
            border-bottom-color: #3b82f6;
            color: #3b82f6;
        }
        .tab-content {
            display: none;
            background: white;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 6px 6px;
        }
        .tab-content.active {
            display: block;
        }
        .function-examples {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
<div class="header">
    <div>Database Query Interface</div>
    <div class="nav">
        <a href="admin_home.php">Dashboard</a>
        <a href="admin_reports.php">Reports</a>
        <a href="admin_logout.php">Logout</a>
    </div>
</div>

<div class="main">
    <h2>Advanced Database Operations</h2>
    <p>Execute custom SQL queries, stored procedures, and view database analytics.</p>

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

    <!-- Tabs for different query types -->
    <div class="tabs">
        <div class="tab active" onclick="showTab('custom-queries')">Custom SQL</div>
        <div class="tab" onclick="showTab('predefined-queries')">Quick Queries</div>
        <div class="tab" onclick="showTab('procedures')">Procedures</div>
        <div class="tab" onclick="showTab('functions')">Functions</div>
    </div>

    <!-- Custom SQL Tab -->
    <div id="custom-queries" class="tab-content active">
        <form method="post">
            <div class="form-group">
                <label>SQL Query</label>
                <textarea name="sql_query" rows="8" cols="100" placeholder="Enter your SQL query here..."><?= htmlspecialchars($_POST['sql_query'] ?? '') ?></textarea>
            </div>
            <button type="submit" name="execute_query" class="btn btn-primary">Execute Query</button>
        </form>
        
        <div class="function-examples">
            <h4>Example Queries:</h4>
            <p><strong>View with Functions:</strong><br>
            <code>SELECT guest_id, name, CalculateGuestSatisfactionScore(guest_id) as satisfaction FROM guests LIMIT 5;</code></p>
            
            <p><strong>Dynamic Pricing:</strong><br>
            <code>SELECT room_id, room_number, price, CalculateDynamicPrice(room_id, '2024-12-25', 3) as dynamic_price FROM rooms LIMIT 5;</code></p>
            
            <p><strong>Complex Aggregation:</strong><br>
            <code>SELECT h.hotel_name, AVG(b.final_amount) as avg_revenue, STDDEV(b.final_amount) as revenue_variance FROM hotels h JOIN rooms r ON h.hotel_id = r.hotel_id JOIN bookings b ON r.room_id = b.room_id GROUP BY h.hotel_id, h.hotel_name;</code></p>
        </div>
    </div>

    <!-- Predefined Queries Tab -->
    <div id="predefined-queries" class="tab-content">
        <div class="query-buttons">
            <?php foreach ($predefined_queries as $name => $query): ?>
                <button onclick="setQuery('<?= addslashes($query) ?>')"><?= $name ?></button>
            <?php endforeach; ?>
        </div>
        
        <form method="post" id="predefined-form">
            <div class="form-group">
                <label>Selected Query</label>
                <textarea name="sql_query" id="predefined-query" rows="8" cols="100" readonly></textarea>
            </div>
            <button type="submit" name="execute_query" class="btn btn-primary">Execute Query</button>
        </form>
    </div>

    <!-- Procedures Tab -->
    <div id="procedures" class="tab-content">
        <div class="query-buttons">
            <?php foreach ($predefined_procedures as $name => $proc): ?>
                <button onclick="setProcedure('<?= $proc['name'] ?>', '<?= addslashes($proc['params']) ?>')"><?= $name ?></button>
            <?php endforeach; ?>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label>Procedure Name</label>
                <input type="text" name="procedure_name" id="procedure-name" placeholder="e.g., UpdateRoomPricesBasedOnDemand">
            </div>
            <div class="form-group">
                <label>Parameters (comma-separated)</label>
                <input type="text" name="parameters" id="procedure-params" placeholder="e.g., 1, '2024-12-15', '2024-12-18', NULL">
            </div>
            <button type="submit" name="execute_procedure" class="btn btn-primary">Execute Procedure</button>
        </form>
        
        <div class="function-examples">
            <h4>Available Procedures:</h4>
            <ul>
                <li><strong>UpdateRoomPricesBasedOnDemand()</strong> - Adjusts room prices based on booking history</li>
                <li><strong>GenerateMonthlyHotelReport(year, month)</strong> - Creates performance report</li>
                <li><strong>ProcessLoyaltyUpgrades()</strong> - Updates guest membership levels</li>
                <li><strong>ScheduleRoomMaintenance()</strong> - Generates maintenance schedule</li>
                <li><strong>GetAvailableRooms(hotel_id, check_in, check_out, room_type)</strong> - Find available rooms</li>
                <li><strong>CalculateRoomRevenue(hotel_id, year)</strong> - Revenue statistics by room type</li>
            </ul>
        </div>
    </div>

    <!-- Functions Tab -->
    <div id="functions" class="tab-content">
        <p>Use these functions in your SQL queries:</p>
        
        <div class="query-section">
            <h4>Custom Functions Available:</h4>
            <ul>
                <li><strong>CalculateAge(birth_date)</strong> - Returns age in years</li>
                <li><strong>CalculateNights(check_in, check_out)</strong> - Returns number of nights</li>
                <li><strong>GetSeason(date)</strong> - Returns season name</li>
                <li><strong>GetMembershipDiscount(membership_level)</strong> - Returns discount percentage</li>
                <li><strong>CalculateDynamicPrice(room_id, check_in, nights)</strong> - Dynamic pricing based on demand</li>
                <li><strong>CalculateGuestSatisfactionScore(guest_id)</strong> - Guest satisfaction score</li>
                <li><strong>GetOptimalRoomAssignment(hotel_id, guest_id, check_in, check_out, room_type)</strong> - Best room for guest</li>
            </ul>
        </div>

        <div class="function-examples">
            <h4>Example Function Usage:</h4>
            <textarea rows="12" cols="100" readonly>
-- Guest age analysis
SELECT name, date_of_birth, CalculateAge(date_of_birth) as age 
FROM guests 
WHERE CalculateAge(date_of_birth) >= 25;

-- Dynamic pricing example
SELECT r.room_number, r.price as base_price,
       CalculateDynamicPrice(r.room_id, '2024-12-25', 3) as holiday_price
FROM rooms r
WHERE r.hotel_id = 1;

-- Guest satisfaction analysis
SELECT g.name, g.membership_level,
       CalculateGuestSatisfactionScore(g.guest_id) as satisfaction_score
FROM guests g
ORDER BY satisfaction_score DESC
LIMIT 10;

-- Optimal room assignment
SELECT GetOptimalRoomAssignment(1, 1, '2024-12-15', '2024-12-18', 3) as recommended_room;
            </textarea>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected tab content
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked tab
    event.target.classList.add('active');
}

function setQuery(query) {
    document.getElementById('predefined-query').value = query;
}

function setProcedure(name, params) {
    document.getElementById('procedure-name').value = name;
    document.getElementById('procedure-params').value = params;
}
</script>
</body>
</html>