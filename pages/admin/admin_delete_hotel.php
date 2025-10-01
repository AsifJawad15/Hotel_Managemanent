<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_admin.php");
$hotel_id = intval($_GET['id'] ?? 0);
if ($hotel_id > 0) {
  // Delete bookings for rooms of this hotel
  $rids = [];
  $rr = $conn->query("SELECT room_id FROM rooms WHERE hotel_id=$hotel_id");
  while($rr && $row = $rr->fetch_assoc()) $rids[] = (int)$row['room_id'];
  if ($rids) {
    $in = implode(',', $rids);
    $conn->query("DELETE FROM bookings WHERE room_id IN ($in)");
  }
  // Delete rooms
  $conn->query("DELETE FROM rooms WHERE hotel_id=$hotel_id");
  // Drop event tables & delete events
  $ev = $conn->query("SELECT event_id FROM events WHERE hotel_id=$hotel_id");
  while($ev && $row = $ev->fetch_assoc()) {
    $eid = (int)$row['event_id'];
    $conn->query("DROP TABLE IF EXISTS hotel{$hotel_id}_event{$eid}");
  }
  $conn->query("DELETE FROM events WHERE hotel_id=$hotel_id");
  // Finally hotel
  $conn->query("DELETE FROM hotels WHERE hotel_id=$hotel_id");
}
header("Location: admin_hotels.php"); exit();
