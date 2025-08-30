<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
$hid = (int)$_SESSION['hotel_id']; $id = (int)($_GET['id'] ?? 0);
$r = $conn->query("SELECT * FROM rooms WHERE room_id=$id AND hotel_id=$hid")->fetch_assoc();
if ($r && !$r['is_booked']) {
  $conn->query("DELETE FROM bookings WHERE room_id=$id"); // safety
  $conn->query("DELETE FROM rooms WHERE room_id=$id AND hotel_id=$hid");
}
header("Location: hotel_rooms.php"); exit();
