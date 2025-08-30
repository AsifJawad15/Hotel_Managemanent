<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_guest.php");
$guest_id=(int)$_SESSION['guest_id']; $event_id=(int)($_GET['event_id']??0); $hotel_id=(int)($_GET['hotel_id']??0);
$e=$conn->query("SELECT * FROM events WHERE event_id=$event_id AND hotel_id=$hotel_id")->fetch_assoc();
if ($e) { $t="hotel{$hotel_id}_event{$event_id}"; $chk=$conn->query("SELECT * FROM $t WHERE guest_id=$guest_id");
  if (!$chk || !$chk->num_rows) $conn->query("INSERT INTO $t (guest_id) VALUES ($guest_id)"); }
header("Location: guest_hotel_view.php?hotel_id=$hotel_id"); exit();
