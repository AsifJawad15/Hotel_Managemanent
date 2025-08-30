<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
$hid = (int)$_SESSION['hotel_id']; $eid = (int)($_GET['id'] ?? 0);
$e = $conn->query("SELECT * FROM events WHERE event_id=$eid AND hotel_id=$hid")->fetch_assoc();
if ($e) { $conn->query("DROP TABLE IF EXISTS hotel{$hid}_event{$eid}"); $conn->query("DELETE FROM events WHERE event_id=$eid AND hotel_id=$hid"); }
header("Location: hotel_events.php"); exit();
