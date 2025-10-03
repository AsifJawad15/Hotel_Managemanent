<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_hotel.php");

$hid = (int)$_SESSION['hotel_id'];
$id = (int)($_GET['id'] ?? 0);

$service = $conn->query("SELECT service_id FROM services WHERE service_id=$id AND hotel_id=$hid")->fetch_assoc();
if ($service) {
    $conn->query("DELETE FROM services WHERE service_id=$id AND hotel_id=$hid");
}

header("Location: hotel_services.php");
exit();
