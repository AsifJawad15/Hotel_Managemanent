<?php
require_once("../../includes/db_connect.php"); 
require_once("../../includes/auth_guest.php");

$guest_id = (int)$_SESSION['guest_id']; 
$event_booking_id = (int)($_GET['event_booking_id'] ?? 0);

// Delete from event_bookings table
if ($event_booking_id > 0) {
  $conn->query("DELETE FROM event_bookings WHERE event_booking_id=$event_booking_id AND guest_id=$guest_id");
}

header("Location: guest_my_bookings.php"); 
exit();
