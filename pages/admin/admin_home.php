<?php 
require_once("../../includes/db_connect.php"); 
require_once("../../includes/auth_admin.php"); 

// Get quick stats for dashboard
$hotel_count = $conn->query("SELECT COUNT(*) as count FROM hotels WHERE is_active = TRUE")->fetch_assoc()['count'];
$guest_count = $conn->query("SELECT COUNT(*) as count FROM guests WHERE is_active = TRUE")->fetch_assoc()['count'];
$booking_count = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$revenue = $conn->query("SELECT SUM(final_amount) as total FROM bookings WHERE booking_status = 'Completed'")->fetch_assoc()['total'];
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Admin Dashboard</title>
<style>
.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
.dashboard-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.dashboard-card h3 { margin-top: 0; color: #1e293b; }
.stat-number { font-size: 2em; font-weight: bold; color: #3b82f6; }
.feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0; }
.feature-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; transition: transform 0.3s; }
.feature-card:hover { transform: translateY(-5px); }
.feature-card a { color: white; text-decoration: none; display: block; }
.feature-card h4 { margin: 0 0 10px 0; }
</style>
</head>
<body>
<div class="header">
  <div>Welcome, Admin - Enhanced Hotel Management System</div>
  <div class="nav">
    <a href="admin_hotels.php">Hotels</a>
    <a href="admin_events.php">Events</a>
    <a href="admin_guests.php">Guests</a>
    <a href="admin_reports.php">Reports</a>
    <a href="admin_database.php">Database</a>
    <a href="admin_logout.php">Logout</a>
  </div>
</div>

<div class="main">
  <h2>Enhanced Admin Dashboard</h2>
  <p>Comprehensive hotel management system with advanced database features</p>

  <!-- Quick Statistics -->
  <div class="dashboard-grid">
    <div class="dashboard-card">
      <h3>System Overview</h3>
      <div style="display: flex; justify-content: space-between;">
        <div>
          <div class="stat-number"><?= number_format($hotel_count) ?></div>
          <div>Active Hotels</div>
        </div>
        <div>
          <div class="stat-number"><?= number_format($guest_count) ?></div>
          <div>Registered Guests</div>
        </div>
      </div>
    </div>
    
    <div class="dashboard-card">
      <h3>Business Metrics</h3>
      <div style="display: flex; justify-content: space-between;">
        <div>
          <div class="stat-number"><?= number_format($booking_count) ?></div>
          <div>Total Bookings</div>
        </div>
        <div>
          <div class="stat-number">$<?= number_format($revenue ?? 0) ?></div>
          <div>Total Revenue</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Enhanced Features -->
  <h3>Enhanced Database Features</h3>
  <div class="feature-grid">
    <div class="feature-card">
      <a href="admin_reports.php">
        <h4>📊 Analytics Dashboard</h4>
        <p>View comprehensive reports, statistics, and performance metrics</p>
      </a>
    </div>
    
    <div class="feature-card">
      <a href="admin_database.php">
        <h4>💾 Database Interface</h4>
        <p>Execute custom SQL queries, stored procedures, and functions</p>
      </a>
    </div>
    
    <div class="feature-card">
      <a href="admin_hotels.php">
        <h4>🏨 Hotel Management</h4>
        <p>Manage hotels, rooms, services, and staff with advanced features</p>
      </a>
    </div>
    
    <div class="feature-card">
      <a href="admin_guests.php">
        <h4>👥 Guest Analytics</h4>
        <p>Analyze guest behavior, loyalty programs, and satisfaction scores</p>
      </a>
    </div>
    
    <div class="feature-card">
      <a href="admin_events.php">
        <h4>🎉 Event Management</h4>
        <p>Comprehensive event booking and management system</p>
      </a>
    </div>
    
    <div class="feature-card">
      <a href="#" onclick="showDatabaseFeatures()">
        <h4>⚡ Quick Operations</h4>
        <p>Run procedures, update prices, process loyalty upgrades</p>
      </a>
    </div>
  </div>

  <!-- Database Features Overview -->
  <div class="dashboard-card">
    <h3>Database Features Implemented</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 15px;">
      <div>
        <h4>📝 DDL Features</h4>
        <ul>
          <li>Enhanced table structure with constraints</li>
          <li>Foreign key relationships</li>
          <li>Indexes for performance</li>
          <li>Check constraints for data integrity</li>
        </ul>
      </div>
      
      <div>
        <h4>🔧 DML Operations</h4>
        <ul>
          <li>Complex INSERT, UPDATE, DELETE</li>
          <li>Bulk data operations</li>
          <li>Transaction management</li>
          <li>Data manipulation procedures</li>
        </ul>
      </div>
      
      <div>
        <h4>📊 Advanced Queries</h4>
        <ul>
          <li>Subqueries and correlated subqueries</li>
          <li>Set operations (UNION, INTERSECT)</li>
          <li>Window functions and analytics</li>
          <li>Aggregate functions with grouping</li>
        </ul>
      </div>
      
      <div>
        <h4>👁️ Views & Procedures</h4>
        <ul>
          <li>Performance analysis views</li>
          <li>Stored procedures with cursors</li>
          <li>Custom functions for calculations</li>
          <li>Automated maintenance procedures</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
function showDatabaseFeatures() {
  alert('Quick Database Operations:\n\n' +
        '• Update Room Prices Based on Demand\n' +
        '• Process Loyalty Program Upgrades\n' +
        '• Generate Hotel Performance Reports\n' +
        '• Schedule Room Maintenance\n' +
        '• Calculate Guest Satisfaction Scores\n' +
        '• Analyze Seasonal Booking Patterns\n\n' +
        'Access these through the Database Interface or Reports section.');
}
</script>
</body></html>
