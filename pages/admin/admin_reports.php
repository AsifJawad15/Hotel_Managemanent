<?php
session_start();
require_once '../../includes/db_connect.php';

// Query #1: LEFT JOIN - Hotel overview statistics with rooms and bookings
$hotel_stats = $conn->query("
    SELECT 
        h.hotel_id,
        h.hotel_name,
        h.city,
        h.rating,
        COUNT(DISTINCT r.room_id) as total_rooms,
        SUM(CASE WHEN r.status = 'Available' THEN 1 ELSE 0 END) as available_rooms,
        SUM(CASE WHEN r.status = 'Occupied' THEN 1 ELSE 0 END) as occupied_rooms,
        COUNT(DISTINCT b.booking_id) as total_bookings,
        SUM(CASE WHEN b.payment_status = 'Paid' THEN b.total_amount ELSE 0 END) as total_revenue
    FROM hotels h
    LEFT JOIN rooms r ON h.hotel_id = r.hotel_id
    LEFT JOIN bookings b ON h.hotel_id = b.hotel_id
    WHERE h.status = 'Active'
    GROUP BY h.hotel_id, h.hotel_name, h.city, h.rating
");

// Query #2: INNER JOIN - Room occupancy rates by hotel
$occupancy_rates = $conn->query("
    SELECT 
        h.hotel_name,
        COUNT(r.room_id) as total_rooms,
        COUNT(CASE WHEN r.status = 'Occupied' THEN 1 END) as occupied_rooms,
        ROUND((COUNT(CASE WHEN r.status = 'Occupied' THEN 1 END) / COUNT(r.room_id)) * 100, 2) as occupancy_percentage
    FROM hotels h
    INNER JOIN rooms r ON h.hotel_id = r.hotel_id
    GROUP BY h.hotel_name
    HAVING COUNT(r.room_id) > 0
");

// Query #3: INNER JOIN - Top customers by spending
$top_customers = $conn->query("
    SELECT 
        c.full_name,
        c.phone,
        COUNT(b.booking_id) as total_bookings,
        SUM(b.total_amount) as total_spent
    FROM customers c
    INNER JOIN bookings b ON c.customer_id = b.customer_id
    WHERE b.payment_status = 'Paid'
    GROUP BY c.customer_id, c.full_name, c.phone
    HAVING COUNT(b.booking_id) > 0
    ORDER BY total_spent DESC
    LIMIT 10
");

// Query #4: INNER JOIN - Room type performance analysis
$room_performance = $conn->query("
    SELECT 
        rt.type_name,
        COUNT(DISTINCT r.room_id) as total_rooms,
        COUNT(b.booking_id) as total_bookings,
        AVG(r.price) as avg_price,
        COALESCE(SUM(b.total_amount), 0) as total_revenue
    FROM room_types rt
    INNER JOIN rooms r ON rt.type_id = r.type_id
    LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status != 'Cancelled'
    GROUP BY rt.type_name
    ORDER BY total_revenue DESC
");

// Query #5: INNER JOIN - Monthly revenue breakdown by hotel
$monthly_revenue = $conn->query("
    SELECT 
        h.hotel_name,
        YEAR(b.check_in) as booking_year,
        MONTH(b.check_in) as booking_month,
        COUNT(b.booking_id) as total_bookings,
        SUM(CASE WHEN b.payment_status = 'Paid' THEN b.total_amount ELSE 0 END) as paid_revenue,
        SUM(CASE WHEN b.payment_status = 'Pending' THEN b.total_amount ELSE 0 END) as pending_revenue
    FROM hotels h
    INNER JOIN bookings b ON h.hotel_id = b.hotel_id
    WHERE b.booking_status != 'Cancelled' AND b.check_in >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY h.hotel_name, YEAR(b.check_in), MONTH(b.check_in)
    ORDER BY booking_year DESC, booking_month DESC
    LIMIT 20
");

// Query #6: SELECT - Overall system statistics
$overall_stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM hotels WHERE status='Active') as total_hotels,
        (SELECT COUNT(*) FROM rooms) as total_rooms,
        (SELECT COUNT(*) FROM bookings WHERE booking_status != 'Cancelled') as total_bookings,
        (SELECT COUNT(*) FROM customers) as total_customers,
        (SELECT SUM(total_amount) FROM bookings WHERE payment_status = 'Paid') as total_revenue,
        (SELECT COUNT(*) FROM rooms WHERE status = 'Available') as available_rooms,
        (SELECT COUNT(*) FROM rooms WHERE status = 'Occupied') as occupied_rooms
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - SmartStay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../css/admin_theme.css" rel="stylesheet">
    <style>
        .stat-card { background: rgba(125, 68, 199, 0.1); padding: 20px; border-radius: 15px; text-align: center; border: 1px solid var(--border-color); }
        .stat-card h3 { color: var(--secondary-light); font-weight: bold; font-size: 2rem; margin: 0; text-shadow: 0 2px 10px rgba(185, 112, 213, 0.5); }
        .stat-card p { color: var(--text-light); margin: 5px 0 0 0; }
    </style>
</head>
<body>
    <div class="container-fluid py-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-graph-up-arrow" style="color: #93BFC7;"></i> Reports & Analytics</h2>
                    <a href="../../index.php" class="btn btn-secondary"><i class="bi bi-house-fill"></i> Dashboard</a>
                </div>

        <!-- Overall Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?= $overall_stats['total_hotels'] ?></h3>
                    <p><i class="bi bi-building"></i> Active Hotels</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?= $overall_stats['total_rooms'] ?></h3>
                    <p><i class="bi bi-door-open"></i> Total Rooms</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?= $overall_stats['total_bookings'] ?></h3>
                    <p><i class="bi bi-calendar-check"></i> Total Bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>$<?= number_format($overall_stats['total_revenue'], 2) ?></h3>
                    <p><i class="bi bi-cash-stack"></i> Total Revenue</p>
                </div>
            </div>
        </div>

        <!-- Hotel Statistics -->
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Hotel Performance Overview</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Hotel Name</th>
                                <th>City</th>
                                <th>Rating</th>
                                <th>Total Rooms</th>
                                <th>Available</th>
                                <th>Occupied</th>
                                <th>Bookings</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hotel_stats->data_seek(0);
                            while ($row = $hotel_stats->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['hotel_name']) ?></strong></td>
                                <td><?= htmlspecialchars($row['city']) ?></td>
                                <td><span class="badge bg-warning"><?= $row['rating'] ?> ⭐</span></td>
                                <td><?= $row['total_rooms'] ?></td>
                                <td><span class="badge bg-success"><?= $row['available_rooms'] ?></span></td>
                                <td><span class="badge bg-danger"><?= $row['occupied_rooms'] ?></span></td>
                                <td><?= $row['total_bookings'] ?></td>
                                <td><strong>$<?= number_format($row['total_revenue'], 2) ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Room Occupancy Rate -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Room Occupancy Rate</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Hotel</th>
                                        <th>Total</th>
                                        <th>Occupied</th>
                                        <th>Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $occupancy_rates->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['hotel_name']) ?></td>
                                        <td><?= $row['total_rooms'] ?></td>
                                        <td><?= $row['occupied_rooms'] ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" style="width: <?= $row['occupancy_percentage'] ?>%">
                                                    <?= $row['occupancy_percentage'] ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Customers</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Bookings</th>
                                        <th>Total Spent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $top_customers->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                            <small><?= htmlspecialchars($row['phone']) ?></small>
                                        </td>
                                        <td><span class="badge bg-info"><?= $row['total_bookings'] ?></span></td>
                                        <td><strong>$<?= number_format($row['total_spent'], 2) ?></strong></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Type Performance -->
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Room Type Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Total Rooms</th>
                                <th>Total Bookings</th>
                                <th>Avg Price</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $room_performance->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['type_name']) ?></strong></td>
                                <td><?= $row['total_rooms'] ?></td>
                                <td><?= $row['total_bookings'] ?></td>
                                <td>$<?= number_format($row['avg_price'], 2) ?></td>
                                <td><strong>$<?= number_format($row['total_revenue'], 2) ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-calendar3"></i> Monthly Revenue Report (Last 6 Months)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Hotel</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Bookings</th>
                                <th>Paid Revenue</th>
                                <th>Pending Revenue</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $monthly_revenue->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['hotel_name']) ?></td>
                                <td><?= $row['booking_year'] ?></td>
                                <td><?= date('F', mktime(0, 0, 0, $row['booking_month'], 1)) ?></td>
                                <td><?= $row['total_bookings'] ?></td>
                                <td><span class="text-success">$<?= number_format($row['paid_revenue'], 2) ?></span></td>
                                <td><span class="text-warning">$<?= number_format($row['pending_revenue'], 2) ?></span></td>
                                <td><strong>$<?= number_format($row['paid_revenue'] + $row['pending_revenue'], 2) ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="text-center mt-4">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <button class="btn btn-success" onclick="exportToCSV()">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export to CSV
            </button>
        </div>
        </div>

            <!-- Right Column - SQL Queries -->
            <div class="col-lg-4">
                <div class="query-box">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-code-square" style="font-size: 1.5rem; color: #ABE7B2;"></i>
                        <span style="margin-left: 10px; font-weight: 700; font-size: 1.2rem;">SQL Queries</span>
                    </div>

                    <div class="mb-3">
                        <div class="query-badge">#1 COMPLEX JOIN + GROUP BY</div>
                        <div class="query-title-text">Purpose: Hotel performance overview</div>
                        <div class="query-sql">SELECT 
    h.hotel_id, h.hotel_name, 
    h.city, h.rating,
    COUNT(DISTINCT r.room_id) 
      as total_rooms,
    SUM(CASE WHEN r.status = 'Available' 
        THEN 1 ELSE 0 END) 
      as available_rooms,
    SUM(CASE WHEN r.status = 'Occupied' 
        THEN 1 ELSE 0 END) 
      as occupied_rooms,
    COUNT(DISTINCT b.booking_id) 
      as total_bookings,
    SUM(CASE WHEN b.payment_status='Paid'
        THEN b.total_amount ELSE 0 END) 
      as total_revenue
FROM hotels h
LEFT JOIN rooms r 
  ON h.hotel_id = r.hotel_id
LEFT JOIN bookings b 
  ON h.hotel_id = b.hotel_id
WHERE h.status = 'Active'
GROUP BY h.hotel_id, h.hotel_name, 
         h.city, h.rating</div>
                    </div>

                    <div class="mb-3">
                        <div class="query-badge">#2 HAVING + MATH</div>
                        <div class="query-title-text">Purpose: Room occupancy rate calculation</div>
                        <div class="query-sql">SELECT 
    h.hotel_name,
    COUNT(r.room_id) as total_rooms,
    COUNT(CASE WHEN r.status='Occupied' 
          THEN 1 END) as occupied_rooms,
    ROUND(
      (COUNT(CASE WHEN r.status='Occupied'
             THEN 1 END) / 
       COUNT(r.room_id)) * 100, 2
    ) as occupancy_percentage
FROM hotels h
INNER JOIN rooms r 
  ON h.hotel_id = r.hotel_id
GROUP BY h.hotel_name
HAVING COUNT(r.room_id) > 0</div>
                    </div>

                    <div class="mb-3">
                        <div class="query-badge">#3 TOP 10 + ORDER BY</div>
                        <div class="query-title-text">Purpose: Top customers by spending</div>
                        <div class="query-sql">SELECT 
    c.full_name, c.phone,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.total_amount) as total_spent
FROM customers c
INNER JOIN bookings b 
  ON c.customer_id = b.customer_id
WHERE b.payment_status = 'Paid'
GROUP BY c.customer_id, 
         c.full_name, c.phone
HAVING COUNT(b.booking_id) > 0
ORDER BY total_spent DESC
LIMIT 10</div>
                    </div>

                    <div class="mb-3">
                        <div class="query-badge">#4 LEFT JOIN + COALESCE</div>
                        <div class="query-title-text">Purpose: Room type performance analysis</div>
                        <div class="query-sql">SELECT 
    rt.type_name,
    COUNT(DISTINCT r.room_id) 
      as total_rooms,
    COUNT(b.booking_id) 
      as total_bookings,
    AVG(r.price) as avg_price,
    COALESCE(SUM(b.total_amount), 0) 
      as total_revenue
FROM room_types rt
INNER JOIN rooms r 
  ON rt.type_id = r.type_id
LEFT JOIN bookings b 
  ON r.room_id = b.room_id 
  AND b.booking_status != 'Cancelled'
GROUP BY rt.type_name
ORDER BY total_revenue DESC</div>
                    </div>

                    <div class="mb-3">
                        <div class="query-badge">#5 DATE FUNCTIONS</div>
                        <div class="query-title-text">Purpose: Monthly revenue (last 6 months)</div>
                        <div class="query-sql">SELECT 
    h.hotel_name,
    YEAR(b.check_in) as booking_year,
    MONTH(b.check_in) as booking_month,
    COUNT(b.booking_id) 
      as total_bookings,
    SUM(CASE WHEN b.payment_status='Paid'
        THEN b.total_amount ELSE 0 END) 
      as paid_revenue,
    SUM(CASE WHEN b.payment_status='Pending' 
        THEN b.total_amount ELSE 0 END) 
      as pending_revenue
FROM hotels h
INNER JOIN bookings b 
  ON h.hotel_id = b.hotel_id
WHERE b.booking_status != 'Cancelled' 
  AND b.check_in >= 
      DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY h.hotel_name, 
         YEAR(b.check_in), 
         MONTH(b.check_in)
ORDER BY booking_year DESC, 
         booking_month DESC</div>
                    </div>

                    <div class="mb-3">
                        <div class="query-badge">#6 SUBQUERY</div>
                        <div class="query-title-text">Purpose: Overall statistics summary</div>
                        <div class="query-sql">SELECT 
    (SELECT COUNT(*) FROM hotels 
     WHERE status='Active') 
      as total_hotels,
    (SELECT COUNT(*) FROM rooms) 
      as total_rooms,
    (SELECT COUNT(*) FROM bookings 
     WHERE booking_status!='Cancelled') 
      as total_bookings,
    (SELECT COUNT(*) FROM customers) 
      as total_customers,
    (SELECT SUM(total_amount) FROM bookings
     WHERE payment_status='Paid') 
      as total_revenue</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToCSV() {
            alert('CSV Export functionality would be implemented here.');
        }
    </script>
</body>
</html>
