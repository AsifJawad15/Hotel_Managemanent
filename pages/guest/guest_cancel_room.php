<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_guest.php");

$guest_id   = (int)$_SESSION['guest_id'];
$booking_id = (int)($_GET['booking_id'] ?? 0);

// find the booking and its room
$bk = $conn->query("SELECT * FROM bookings WHERE booking_id=$booking_id AND guest_id=$guest_id")->fetch_assoc();
if ($bk) {
  $room_id = (int)$bk['room_id'];
  $conn->query("DELETE FROM bookings WHERE booking_id=$booking_id AND guest_id=$guest_id");
  // if room has no more bookings, mark as not booked
  $left = $conn->query("SELECT COUNT(*) c FROM bookings WHERE room_id=$room_id")->fetch_assoc();
  if ((int)$left['c'] === 0) {
    $conn->query("UPDATE rooms SET is_booked=0 WHERE room_id=$room_id");
  }
}

header("Location: guest_my_bookings.php");
exit();
