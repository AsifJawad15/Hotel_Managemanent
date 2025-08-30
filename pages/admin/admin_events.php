<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_admin.php");
$events = $conn->query("SELECT e.*, h.hotel_name FROM events e JOIN hotels h ON e.hotel_id=h.hotel_id ORDER BY e.event_date DESC");
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Manage Events</title></head>
<body>
<div class="header"><div>Events</div><div class="nav"><a href="admin_home.php">Dashboard</a> <a href="admin_logout.php">Logout</a></div></div>
<div class="main">
<table class="table">
  <thead><tr><th>ID</th><th>Hotel</th><th>Name</th><th>Date</th><th>Actions</th></tr></thead>
  <tbody>
    <?php while($e = $events->fetch_assoc()): ?>
    <tr>
      <td><?= $e['event_id'] ?></td>
      <td><?= htmlspecialchars($e['hotel_name']) ?></td>
      <td><?= htmlspecialchars($e['event_name']) ?></td>
      <td><?= htmlspecialchars($e['event_date']) ?></td>
      <td><a class="btn btn-danger" onclick="return confirmDelete();" href="admin_delete_event.php?id=<?= $e['event_id'] ?>&hotel_id=<?= $e['hotel_id'] ?>">Delete</a></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
</div>
<script src='../../js/script.js'></script>
</body></html>
