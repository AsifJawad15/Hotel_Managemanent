<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
if (isset($_POST['save'])) {
  $num = esc($_POST['room_number'] ?? ''); $type = esc($_POST['type'] ?? ''); $price = (float)($_POST['price'] ?? 0);
  $hid = (int)$_SESSION['hotel_id'];
  if ($num && $type) {
    if ($conn->query("INSERT INTO rooms (hotel_id,room_number,type,price,is_booked) VALUES ($hid,'$num','$type',$price,0)")) {
      header("Location: hotel_rooms.php"); exit();
    } else { $error="Failed to add room."; }
  } else { $error="All fields required."; }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Add Room</title></head>
<body>
<div class="header"><div>Add Room</div><div class="nav"><a href="hotel_rooms.php">Back</a></div></div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Room Number</label><input type="text" name="room_number" required></div>
    <div class="form-group"><label>Type</label><input type="text" name="type" required></div>
    <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" required></div>
    <button class="btn btn-primary" name="save">Save</button>
  </form>
</div>
</body></html>
