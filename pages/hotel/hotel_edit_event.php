<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
$hid = (int)$_SESSION['hotel_id']; $eid = (int)($_GET['id'] ?? 0);
$e = $conn->query("SELECT * FROM events WHERE event_id=$eid AND hotel_id=$hid")->fetch_assoc();
if (!$e) { header("Location: hotel_events.php"); exit(); }
if (isset($_POST['save'])) {
  $name = esc($_POST['event_name'] ?? ''); $desc = esc($_POST['description'] ?? ''); $date = esc($_POST['event_date'] ?? '');
  if ($name && $date) {
    $conn->query("UPDATE events SET event_name='$name', description='$desc', event_date='$date' WHERE event_id=$eid AND hotel_id=$hid");
    header("Location: hotel_events.php"); exit();
  } else { $error="Event name and date are required."; }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Edit Event</title></head>
<body>
<div class="header"><div>Edit Event</div><div class="nav"><a href="hotel_events.php">Back</a></div></div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Event Name</label><input type="text" name="event_name" value="<?= htmlspecialchars($e['event_name']) ?>" required></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="5"><?= htmlspecialchars($e['description']) ?></textarea></div>
    <div class="form-group"><label>Date</label><input type="date" name="event_date" value="<?= htmlspecialchars($e['event_date']) ?>" required></div>
    <button class="btn btn-primary" name="save">Update</button>
  </form>
</div>
</body></html>
