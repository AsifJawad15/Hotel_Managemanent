<?php
require_once("../../includes/db_connect.php"); 
require_once("../../includes/auth_admin.php");

$hotels = $conn->query("SELECT * FROM hotels ORDER BY hotel_id DESC");

if (!$hotels) {
    die("Error fetching hotels: " . $conn->error);
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Manage Hotels</title>
<style>
.table { width: 100%; border-collapse: collapse; margin: 20px 0; }
.table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
.table th { background: #3b82f6; color: white; }
.table tr:hover { background: #f5f5f5; }
.btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; }
</style>
</head>
<body>
<div class="header">
  <div>Hotel Management</div>
  <div class="nav">
    <a href="admin_home.php">Dashboard</a>
    <a href="admin_guests.php">Guests</a>
    <a href="admin_events.php">Events</a>
    <a href="admin_logout.php">Logout</a>
  </div>
</div>
<div class="main">
<h2>🏨 Hotel Management</h2>
<p>View and manage all registered hotels in the system.</p>

<?php if ($hotels->num_rows === 0): ?>
  <div style="padding: 20px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; margin: 20px 0;">
    <strong>⚠️ No hotels found</strong><br>
    No hotels are currently registered in the system.
  </div>
<?php else: ?>
<table class="table">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
    <?php while($h = $hotels->fetch_assoc()): ?>
      <tr>
        <td><?= $h['hotel_id'] ?></td>
        <td><strong><?= htmlspecialchars($h['hotel_name']) ?></strong></td>
        <td><?= htmlspecialchars($h['email']) ?></td>
        <td><?= htmlspecialchars($h['phone']) ?></td>
        <td><?= htmlspecialchars($h['city']) ?></td>
        <td>
          <?php if ($h['is_active']): ?>
            <span style="padding: 4px 8px; background: #10b981; color: white; border-radius: 4px; font-size: 12px;">Active</span>
          <?php else: ?>
            <span style="padding: 4px 8px; background: #6b7280; color: white; border-radius: 4px; font-size: 12px;">Inactive</span>
          <?php endif; ?>
        </td>
        <td><a class="btn btn-danger" onclick="return confirmDelete();" href="admin_delete_hotel.php?id=<?= $h['hotel_id'] ?>">Delete</a></td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php endif; ?>
</div>
<script src='../../js/script.js'></script>
</body></html>
