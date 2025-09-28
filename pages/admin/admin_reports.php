<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_admin.php");

// Get summary statistics
$stats = [];

// Total counts
$stats['total_hotels'] = $conn->query("SELECT COUNT(*) as count FROM hotels WHERE is_active = TRUE")->fetch_assoc()['count'];
$stats['total_guests'] = $conn->query("SELECT COUNT(*) as count FROM guests WHERE is_active = TRUE")->fetch_assoc()['count'];
$stats['total_rooms'] = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE is_active = TRUE")->fetch_assoc()['count'];
$stats['total_bookings'] = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$stats['total_events'] = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];

// Revenue statistics
$revenue_stats = $conn->query("
    SELECT 
        SUM(final_amount) as total_revenue,
        AVG(final_amount) as avg_booking_value,
        COUNT(*) as completed_bookings
    FROM bookings 
    WHERE booking_status = 'Completed'
")->fetch_assoc();

// Monthly revenue (last 6 months)
$monthly_revenue = $conn->query("
    SELECT 
        YEAR(check_in) as year,
        MONTH(check_in) as month,
        MONTHNAME(check_in) as month_name,
        SUM(final_amount) as revenue,
        COUNT(*) as bookings
    FROM bookings 
    WHERE booking_status = 'Completed'
    AND check_in >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(check_in), MONTH(check_in)
    ORDER BY year DESC, month DESC
");

// Top performing hotels
$top_hotels = $conn->query("
    SELECT 
        h.hotel_name,
        h.city,
        COUNT(b.booking_id) as total_bookings,
        SUM(b.final_amount) as total_revenue,
        AVG(rev.rating) as avg_rating
    FROM hotels h
    LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
    LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'Completed'
    LEFT JOIN reviews rev ON h.hotel_id = rev.hotel_id
    WHERE h.is_active = TRUE
    GROUP BY h.hotel_id, h.hotel_name, h.city
    HAVING total_revenue > 0
    ORDER BY total_revenue DESC
    LIMIT 5
");

// Guest membership distribution
$membership_dist = $conn->query("
    SELECT 
        membership_level,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM guests WHERE is_active = TRUE), 2) as percentage
    FROM guests 
    WHERE is_active = TRUE
    GROUP BY membership_level
    ORDER BY FIELD(membership_level, 'Platinum', 'Gold', 'Silver', 'Bronze')
");

// Occupancy rates by hotel
$occupancy_rates = $conn->query("
    SELECT 
        h.hotel_name,
        COUNT(DISTINCT r.room_id) as total_rooms,
        COUNT(DISTINCT CASE WHEN b.booking_status = 'Confirmed' AND CURDATE() BETWEEN b.check_in AND b.check_out THEN b.room_id END) as occupied_rooms,
        ROUND(
            COUNT(DISTINCT CASE WHEN b.booking_status = 'Confirmed' AND CURDATE() BETWEEN b.check_in AND b.check_out THEN b.room_id END) * 100.0 
            / COUNT(DISTINCT r.room_id), 2
        ) as occupancy_rate
    FROM hotels h
    JOIN rooms r ON h.hotel_id = r.hotel_id
    LEFT JOIN bookings b ON r.room_id = b.room_id
    WHERE h.is_active = TRUE AND r.is_active = TRUE
    GROUP BY h.hotel_id, h.hotel_name
    ORDER BY occupancy_rate DESC
");

// Recent activities (system logs)
$recent_activities = $conn->query("
    SELECT 
        user_type,
        action,
        table_name,
        created_at
    FROM system_logs 
    ORDER BY created_at DESC 
    LIMIT 10
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Reports & Analytics</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .report-section {
            background: white;
            margin: 20px 0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chart-container {
            height: 300px;
            margin: 20px 0;
        }
        .performance-table {
            width: 100%;
            border-collapse: collapse;
        }
        .performance-table th,
        .performance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .performance-table th {
            background: #f8fafc;
            font-weight: 600;
        }
        .rating-stars {
            color: #fbbf24;
        }
        .progress-bar {
            background: #e2e8f0;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: 5px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #34d399);
            transition: width 0.3s ease;
        }
        .activity-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .activity-item {
            padding: 10px;
            border-left: 3px solid #3b82f6;
            margin: 10px 0;
            background: #f8fafc;
            border-radius: 0 6px 6px 0;
        }
        .activity-time {
            font-size: 0.8em;
            color: #64748b;
        }
        .membership-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .membership-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        .badge-platinum { background: #1e293b; color: white; }
        .badge-gold { background: #f59e0b; color: white; }
        .badge-silver { background: #6b7280; color: white; }
        .badge-bronze { background: #92400e; color: white; }
    </style>
</head>
<body>
<div class="header">
    <div>Reports & Analytics</div>
    <div class="nav">
        <a href="admin_home.php">Dashboard</a>
        <a href="admin_database.php">Database</a>
        <a href="admin_hotels.php">Hotels</a>
        <a href="admin_logout.php">Logout</a>
    </div>
</div>

<div class="main">
    <h2>System Analytics Dashboard</h2>

    <!-- Key Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['total_hotels']) ?></div>
            <div class="stat-label">Active Hotels</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['total_guests']) ?></div>
            <div class="stat-label">Registered Guests</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['total_rooms']) ?></div>
            <div class="stat-label">Available Rooms</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($stats['total_bookings']) ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?= number_format($revenue_stats['total_revenue'] ?? 0, 0) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?= number_format($revenue_stats['avg_booking_value'] ?? 0, 0) ?></div>
            <div class="stat-label">Avg Booking Value</div>
        </div>
    </div>

    <!-- Top Performing Hotels -->
    <div class="report-section">
        <h3>Top Performing Hotels</h3>
        <table class="performance-table">
            <thead>
                <tr>
                    <th>Hotel Name</th>
                    <th>City</th>
                    <th>Total Bookings</th>
                    <th>Revenue</th>
                    <th>Avg Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($hotel = $top_hotels->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($hotel['hotel_name']) ?></td>
                    <td><?= htmlspecialchars($hotel['city']) ?></td>
                    <td><?= number_format($hotel['total_bookings'] ?? 0) ?></td>
                    <td>$<?= number_format($hotel['total_revenue'] ?? 0, 2) ?></td>
                    <td>
                        <?php if ($hotel['avg_rating']): ?>
                            <span class="rating-stars">
                                <?= str_repeat('★', round($hotel['avg_rating'])) ?>
                                <?= str_repeat('☆', 5 - round($hotel['avg_rating'])) ?>
                            </span>
                            (<?= number_format($hotel['avg_rating'], 1) ?>)
                        <?php else: ?>
                            No ratings yet
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Monthly Revenue Trend -->
    <div class="report-section">
        <h3>Monthly Revenue Trend (Last 6 Months)</h3>
        <table class="performance-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Revenue</th>
                    <th>Bookings</th>
                    <th>Avg per Booking</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($month = $monthly_revenue->fetch_assoc()): ?>
                <tr>
                    <td><?= $month['month_name'] ?> <?= $month['year'] ?></td>
                    <td>$<?= number_format($month['revenue'], 2) ?></td>
                    <td><?= number_format($month['bookings']) ?></td>
                    <td>$<?= number_format($month['revenue'] / $month['bookings'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <!-- Guest Membership Distribution -->
        <div class="report-section">
            <h3>Guest Membership Distribution</h3>
            <?php while ($membership = $membership_dist->fetch_assoc()): ?>
                <div style="margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="membership-badge badge-<?= strtolower($membership['membership_level']) ?>">
                            <?= $membership['membership_level'] ?>
                        </span>
                        <span><?= $membership['count'] ?> (<?= $membership['percentage'] ?>%)</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $membership['percentage'] ?>%"></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Hotel Occupancy Rates -->
        <div class="report-section">
            <h3>Current Occupancy Rates</h3>
            <?php while ($occupancy = $occupancy_rates->fetch_assoc()): ?>
                <div style="margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span><?= htmlspecialchars($occupancy['hotel_name']) ?></span>
                        <span><?= $occupancy['occupancy_rate'] ?>% (<?= $occupancy['occupied_rooms'] ?>/<?= $occupancy['total_rooms'] ?>)</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= min($occupancy['occupancy_rate'], 100) ?>%"></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Recent System Activities -->
    <div class="report-section">
        <h3>Recent System Activities</h3>
        <div class="activity-list">
            <?php if ($recent_activities && $recent_activities->num_rows > 0): ?>
                <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                <div class="activity-item">
                    <strong><?= htmlspecialchars($activity['action']) ?></strong> 
                    on <?= htmlspecialchars($activity['table_name']) ?> 
                    by <?= htmlspecialchars($activity['user_type']) ?>
                    <div class="activity-time"><?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?></div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No recent activities recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Database Analysis Tools -->
    <div class="report-section">
        <h3>Advanced Analytics</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <a href="admin_database.php" class="btn btn-primary" style="display: block; text-align: center; padding: 15px;">
                Database Query Interface
            </a>
            <button onclick="generateReport('occupancy')" class="btn btn-secondary" style="padding: 15px;">
                Generate Occupancy Report
            </button>
            <button onclick="generateReport('revenue')" class="btn btn-secondary" style="padding: 15px;">
                Generate Revenue Report  
            </button>
            <button onclick="generateReport('guest-analysis')" class="btn btn-secondary" style="padding: 15px;">
                Guest Analysis Report
            </button>
        </div>
    </div>

    <!-- Quick Database Operations -->
    <div class="report-section">
        <h3>Quick Database Operations</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <button onclick="runProcedure('UpdateRoomPricesBasedOnDemand')" class="btn btn-primary">
                Update Room Prices
            </button>
            <button onclick="runProcedure('ProcessLoyaltyUpgrades')" class="btn btn-primary">
                Process Loyalty Upgrades
            </button>
            <button onclick="runProcedure('ScheduleRoomMaintenance')" class="btn btn-primary">
                Schedule Maintenance
            </button>
            <button onclick="showView('hotel_performance')" class="btn btn-secondary">
                Hotel Performance View
            </button>
        </div>
    </div>
</div>

<script>
function generateReport(type) {
    // Redirect to database interface with predefined query
    const queries = {
        'occupancy': "CALL GenerateOccupancyReport(1, DATE_SUB(CURDATE(), INTERVAL 30 DAY), CURDATE())",
        'revenue': "SELECT * FROM monthly_revenue_report WHERE revenue_year = YEAR(CURDATE()) ORDER BY revenue_month DESC",
        'guest-analysis': "SELECT g.name, g.membership_level, SUM(b.final_amount) as total_spent, COUNT(b.booking_id) as total_bookings, CalculateGuestSatisfactionScore(g.guest_id) as satisfaction_score FROM guests g LEFT JOIN bookings b ON g.guest_id = b.guest_id WHERE b.booking_status = 'Completed' GROUP BY g.guest_id ORDER BY total_spent DESC LIMIT 20"
    };
    
    if (queries[type]) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin_database.php';
        
        const queryInput = document.createElement('input');
        queryInput.type = 'hidden';
        queryInput.name = 'sql_query';
        queryInput.value = queries[type];
        
        const executeInput = document.createElement('input');
        executeInput.type = 'hidden';
        executeInput.name = 'execute_query';
        executeInput.value = '1';
        
        form.appendChild(queryInput);
        form.appendChild(executeInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function runProcedure(procedureName) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'admin_database.php';
    
    const procInput = document.createElement('input');
    procInput.type = 'hidden';
    procInput.name = 'procedure_name';
    procInput.value = procedureName;
    
    const executeInput = document.createElement('input');
    executeInput.type = 'hidden';
    executeInput.name = 'execute_procedure';
    executeInput.value = '1';
    
    form.appendChild(procInput);
    form.appendChild(executeInput);
    document.body.appendChild(form);
    form.submit();
}

function showView(viewName) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'admin_database.php';
    
    const queryInput = document.createElement('input');
    queryInput.type = 'hidden';
    queryInput.name = 'sql_query';
    queryInput.value = `SELECT * FROM ${viewName} ORDER BY total_revenue DESC LIMIT 20`;
    
    const executeInput = document.createElement('input');
    executeInput.type = 'hidden';
    executeInput.name = 'execute_query';
    executeInput.value = '1';
    
    form.appendChild(queryInput);
    form.appendChild(executeInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
</body>
</html>