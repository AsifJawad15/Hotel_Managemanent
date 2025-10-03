<?php
require_once("../../includes/db_connect.php"); 
require_once("../../includes/auth_hotel.php");

$hid = (int)$_SESSION['hotel_id']; 

// Enhanced query with proper JOINs and booking status calculation
$query = "
SELECT 
    r.room_id,
    r.room_number,
    r.price,
    r.max_occupancy,
    r.is_active,
    r.maintenance_status,
    rt.type_name as room_type,
    CASE 
        WHEN EXISTS(
            SELECT 1 FROM bookings b 
            WHERE b.room_id = r.room_id 
            AND b.booking_status = 'Confirmed' 
            AND CURDATE() BETWEEN b.check_in AND b.check_out
        ) THEN 1
        ELSE 0
    END as is_currently_booked
FROM rooms r
JOIN room_types rt ON r.type_id = rt.type_id
WHERE r.hotel_id = $hid
ORDER BY r.room_id DESC
";

$rooms = $conn->query($query);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Manage Rooms</title></head>
<body>
<div class="header"><div>Rooms</div><div class="nav"><a href="hotel_home.php">Dashboard</a> <a href="hotel_services.php">Services</a> <a href="hotel_logout.php">Logout</a></div></div>
<div class="main">
  <a class="btn btn-primary" href="hotel_add_room.php">+ Add Room</a>
  <table class="table">
    <thead><tr><th>ID</th><th>Number</th><th>Type</th><th>Price</th><th>Status</th><th>Occupancy</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while($r = $rooms->fetch_assoc()): ?>
      <tr>
        <td><?= $r['room_id'] ?></td>
        <td><?= htmlspecialchars($r['room_number']) ?></td>
        <td><?= htmlspecialchars($r['room_type']) ?></td>
        <td>$<?= number_format($r['price'], 2) ?></td>
        <td>
          <?php if (!$r['is_active']): ?>
            <span class="badge badge-danger">Inactive</span>
          <?php elseif ($r['maintenance_status'] != 'Available'): ?>
            <span class="badge badge-warning"><?= $r['maintenance_status'] ?></span>
          <?php elseif ($r['is_currently_booked']): ?>
            <span class="badge badge-info">Occupied</span>
          <?php else: ?>
            <span class="badge badge-success">Available</span>
          <?php endif; ?>
        </td>
        <td><?= $r['max_occupancy'] ?> guests</td>
        <td>
          <a class="btn btn-secondary" href="hotel_edit_room.php?id=<?= $r['room_id'] ?>">Edit</a>
          <?php if (!$r['is_currently_booked'] && $r['is_active']): ?>
            <a class="btn btn-danger" onclick="return confirmDelete();" href="hotel_delete_room.php?id=<?= $r['room_id'] ?>">Delete</a>
          <?php else: ?>
            <span class="btn btn-secondary" style="opacity:.6;cursor:not-allowed;" title="Cannot delete occupied or inactive room">Delete</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<script src='../../js/script.js'></script>
</body></html>
