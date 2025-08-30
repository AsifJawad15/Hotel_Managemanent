<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_guest.php");
$guest_id=(int)$_SESSION['guest_id']; $room_id=(int)($_GET['room_id']??0); $hotel_id=(int)($_GET['hotel_id']??0);
$r=$conn->query("SELECT * FROM rooms WHERE room_id=$room_id AND hotel_id=$hotel_id")->fetch_assoc();
if ($r) { $b=$conn->query("SELECT * FROM bookings WHERE room_id=$room_id"); if (!$b || !$b->num_rows) {
  $conn->query("INSERT INTO bookings (guest_id, room_id) VALUES ($guest_id,$room_id)");
  $conn->query("UPDATE rooms SET is_booked=1 WHERE room_id=$room_id"); } }
header("Location: guest_hotel_view.php?hotel_id=$hotel_id"); exit();
