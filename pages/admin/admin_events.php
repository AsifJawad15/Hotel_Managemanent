<?php
require_once("../../includes/db_connect.php"); 
require_once("../../includes/auth_admin.php");

$events = $conn->query("SELECT e.*, h.hotel_name FROM events e JOIN hotels h ON e.hotel_id=h.hotel_id ORDER BY e.event_date DESC");

if (!$events) {
    die("Error fetching events: " . $conn->error);
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Manage Events</title>
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
  <div>Event Management</div>
  <div class="nav">
    <a href="admin_home.php">Dashboard</a>
    <a href="admin_hotels.php">Hotels</a>
    <a href="admin_guests.php">Guests</a>
    <a href="admin_logout.php">Logout</a>
  </div>
</div>
<div class="main">
<h2>🎉 Event Management</h2>
<p>View and manage all hotel events in the system.</p>

<?php if ($events->num_rows === 0): ?>
  <div style="padding: 20px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; margin: 20px 0;">
    <strong>⚠️ No events found</strong><br>
    No events are currently scheduled in the system.
  </div>
<?php else: ?>
<table class="table">
  <thead><tr><th>ID</th><th>Hotel</th><th>Event Name</th><th>Date</th><th>Price</th><th>Max Guests</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
    <?php while($e = $events->fetch_assoc()): ?>
    <tr>
      <td><?= $e['event_id'] ?></td>
      <td><?= htmlspecialchars($e['hotel_name']) ?></td>
      <td><strong><?= htmlspecialchars($e['event_name']) ?></strong></td>
      <td><?= date('M d, Y', strtotime($e['event_date'])) ?></td>
      <td>$<?= number_format($e['price'], 2) ?></td>
      <td><?= $e['max_guests'] ?></td>
      <td>
        <?php if ($e['is_active']): ?>
          <span style="padding: 4px 8px; background: #10b981; color: white; border-radius: 4px; font-size: 12px;">Active</span>
        <?php else: ?>
          <span style="padding: 4px 8px; background: #6b7280; color: white; border-radius: 4px; font-size: 12px;">Inactive</span>
        <?php endif; ?>
      </td>
      <td><a class="btn btn-danger" onclick="return confirmDelete();" href="admin_delete_event.php?id=<?= $e['event_id'] ?>&hotel_id=<?= $e['hotel_id'] ?>">Delete</a></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php endif; ?>
</div>
<script src='../../js/script.js'></script>
</body></html>
