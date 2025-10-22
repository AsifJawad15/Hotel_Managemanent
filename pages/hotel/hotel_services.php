<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_hotel.php");

$hid = (int)$_SESSION['hotel_id'];
$services = $conn->query("SELECT * FROM services WHERE hotel_id=$hid ORDER BY service_type, service_name");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../../css/style.css">
  <title>Manage Services</title>
</head>
<body>
<div class="header">
  <div>Services</div>
  <div class="nav">
    <a href="hotel_home.php">Dashboard</a>
    <a href="hotel_rooms.php">Rooms</a>
    <a href="hotel_events.php">Events</a>
    <a href="hotel_bookings.php">Bookings</a>
    <a href="hotel_logout.php">Logout</a>
  </div>
</div>
<div class="main">
  <a class="btn btn-primary" href="hotel_add_service.php">+ Add Service</a>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Type</th>
        <th>Price</th>
        <th>Status</th>
        <th>Description</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($services && $services->num_rows): while ($s = $services->fetch_assoc()): ?>
      <tr>
        <td><?= $s['service_id'] ?></td>
        <td><?= htmlspecialchars($s['service_name']) ?></td>
        <td><?= $s['service_type'] ? htmlspecialchars($s['service_type']) : 'General' ?></td>
  <td><?= ((float)$s['price']) > 0 ? '$' . number_format((float)$s['price'], 2) : 'Included' ?></td>
        <td>
          <?php if ((int)$s['is_active'] === 1): ?>
            <span class="badge badge-success">Active</span>
          <?php else: ?>
            <span class="badge badge-secondary">Inactive</span>
          <?php endif; ?>
        </td>
        <td><?= $s['description'] ? htmlspecialchars($s['description']) : '—' ?></td>
        <td>
          <a class="btn btn-secondary" href="hotel_edit_service.php?id=<?= $s['service_id'] ?>">Edit</a>
          <a class="btn btn-danger" onclick="return confirmDelete();" href="hotel_delete_service.php?id=<?= $s['service_id'] ?>">Delete</a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="6">No services created yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<script src='../../js/script.js'></script>
</body>
</html>
