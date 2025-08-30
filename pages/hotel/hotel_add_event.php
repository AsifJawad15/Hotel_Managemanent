<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
$hid = (int)$_SESSION['hotel_id'];
if (isset($_POST['save'])) {
  $name = esc($_POST['event_name'] ?? ''); $desc = esc($_POST['description'] ?? ''); $date = esc($_POST['event_date'] ?? '');
  if ($name && $date) {
    if ($conn->query("INSERT INTO events (hotel_id,event_name,description,event_date) VALUES ($hid,'$name','$desc','$date')")) {
      $eid = $conn->insert_id; $table = "hotel{$hid}_event{$eid}";
      $conn->query("CREATE TABLE $table ( booking_id INT AUTO_INCREMENT PRIMARY KEY, guest_id INT NOT NULL )");
      header("Location: hotel_events.php"); exit();
    } else { $error="Failed to create event."; }
  } else { $error="Event name and date are required."; }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Add Event</title></head>
<body>
<div class="header"><div>Add Event</div><div class="nav"><a href="hotel_events.php">Back</a></div></div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Event Name</label><input type="text" name="event_name" required></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="5"></textarea></div>
    <div class="form-group"><label>Date</label><input type="date" name="event_date" required></div>
    <button class="btn btn-primary" name="save">Create</button>
  </form>
</div>
</body></html>
