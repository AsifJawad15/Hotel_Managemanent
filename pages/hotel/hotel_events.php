<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
$hid = (int)$_SESSION['hotel_id']; $events = $conn->query("SELECT * FROM events WHERE hotel_id=$hid ORDER BY event_date DESC");
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Manage Events</title></head>
<body>
<div class="header"><div>Events</div><div class="nav"><a href="hotel_home.php">Dashboard</a> <a href="hotel_rooms.php">Rooms</a> <a href="hotel_services.php">Services</a> <a href="hotel_bookings.php">Bookings</a> <a href="hotel_logout.php">Logout</a></div></div>
<div class="main">
  <a class="btn btn-primary" href="hotel_add_event.php">+ Add Event</a>
  <table class="table">
    <thead><tr><th>ID</th><th>Name</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while($e = $events->fetch_assoc()): ?>
      <tr>
        <td><?= $e['event_id'] ?></td>
        <td><?= htmlspecialchars($e['event_name']) ?></td>
        <td><?= htmlspecialchars($e['event_date']) ?></td>
        <td>
          <a class="btn btn-secondary" href="hotel_edit_event.php?id=<?= $e['event_id'] ?>">Edit</a>
          <a class="btn btn-danger" onclick="return confirmDelete();" href="hotel_delete_event.php?id=<?= $e['event_id'] ?>">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<script src='../../js/script.js'></script>
</body></html>
