<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
$hid = (int)$_SESSION['hotel_id']; $id = (int)($_GET['id'] ?? 0);
$r = $conn->query("SELECT * FROM rooms WHERE room_id=$id AND hotel_id=$hid")->fetch_assoc();
if (!$r) { header("Location: hotel_rooms.php"); exit(); }
if (isset($_POST['save'])) {
  $num = esc($_POST['room_number'] ?? ''); $type = esc($_POST['type'] ?? ''); $price = (float)($_POST['price'] ?? 0);
  if ($num && $type) {
    $conn->query("UPDATE rooms SET room_number='$num', type='$type', price=$price WHERE room_id=$id AND hotel_id=$hid");
    header("Location: hotel_rooms.php"); exit();
  } else { $error="All fields required."; }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Edit Room</title></head>
<body>
<div class="header"><div>Edit Room</div><div class="nav"><a href="hotel_rooms.php">Back</a></div></div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Room Number</label><input type="text" name="room_number" value="<?= htmlspecialchars($r['room_number']) ?>" required></div>
    <div class="form-group"><label>Type</label><input type="text" name="type" value="<?= htmlspecialchars($r['type']) ?>" required></div>
    <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" value="<?= htmlspecialchars($r['price']) ?>" required></div>
    <button class="btn btn-primary" name="save">Update</button>
  </form>
</div>
</body></html>
