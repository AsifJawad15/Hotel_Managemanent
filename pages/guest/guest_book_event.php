<?php
require_once("../../includes/db_connect.php"); 
require_once("../../includes/auth_guest.php");

$guest_id = (int)$_SESSION['guest_id']; 
$event_id = (int)($_GET['event_id'] ?? 0); 
$hotel_id = (int)($_GET['hotel_id'] ?? 0);

// Get event details
$e = $conn->query("SELECT * FROM events WHERE event_id=$event_id AND hotel_id=$hotel_id")->fetch_assoc();

if ($e) {
  // Check if already booked
  $chk = $conn->query("SELECT * FROM event_bookings WHERE event_id=$event_id AND guest_id=$guest_id");
  
  if (!$chk || $chk->num_rows == 0) {
    // Insert into event_bookings table
    $price = (float)$e['price'];
    $conn->query("INSERT INTO event_bookings (event_id, guest_id, participants, amount_paid, booking_status) 
                  VALUES ($event_id, $guest_id, 1, $price, 'Confirmed')");
  }
}

header("Location: guest_hotel_view.php?hotel_id=$hotel_id"); 
exit();
