<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_admin.php");
$event_id = intval($_GET['id'] ?? 0);
$hotel_id = intval($_GET['hotel_id'] ?? 0);
if ($event_id > 0 && $hotel_id > 0) {
  $conn->query("DROP TABLE IF EXISTS hotel{$hotel_id}_event{$event_id}");
  $conn->query("DELETE FROM events WHERE event_id=$event_id AND hotel_id=$hotel_id");
}
header("Location: admin_events.php"); exit();
