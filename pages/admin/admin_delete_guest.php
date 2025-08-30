<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_admin.php");
$guest_id = intval($_GET['id'] ?? 0);
if ($guest_id > 0) {
  $conn->query("DELETE FROM bookings WHERE guest_id=$guest_id");
  $ev = $conn->query("SELECT event_id, hotel_id FROM events");
  while($ev && $row = $ev->fetch_assoc()) {
    $conn->query("DELETE FROM hotel{$row['hotel_id']}_event{$row['event_id']} WHERE guest_id=$guest_id");
  }
  $conn->query("DELETE FROM guests WHERE guest_id=$guest_id");
}
header("Location: admin_guests.php"); exit();
