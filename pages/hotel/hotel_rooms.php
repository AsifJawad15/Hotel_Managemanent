<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
$hid = (int)$_SESSION['hotel_id']; $rooms = $conn->query("SELECT * FROM rooms WHERE hotel_id=$hid ORDER BY room_id DESC");
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Manage Rooms</title></head>
<body>
<div class="header"><div>Rooms</div><div class="nav"><a href="hotel_home.php">Dashboard</a> <a href="hotel_logout.php">Logout</a></div></div>
<div class="main">
  <a class="btn btn-primary" href="hotel_add_room.php">+ Add Room</a>
  <table class="table">
    <thead><tr><th>ID</th><th>Number</th><th>Type</th><th>Price</th><th>Booked?</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while($r = $rooms->fetch_assoc()): ?>
      <tr>
        <td><?= $r['room_id'] ?></td>
        <td><?= htmlspecialchars($r['room_number']) ?></td>
        <td><?= htmlspecialchars($r['type']) ?></td>
        <td><?= htmlspecialchars($r['price']) ?></td>
        <td><?= $r['is_booked'] ? 'Yes' : 'No' ?></td>
        <td>
          <a class="btn btn-secondary" href="hotel_edit_room.php?id=<?= $r['room_id'] ?>">Edit</a>
          <?php if (!$r['is_booked']): ?>
            <a class="btn btn-danger" onclick="return confirmDelete();" href="hotel_delete_room.php?id=<?= $r['room_id'] ?>">Delete</a>
          <?php else: ?>
            <span class="btn btn-secondary" style="opacity:.6;cursor:not-allowed;">Delete</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<script src='../../js/script.js'></script>
</body></html>
