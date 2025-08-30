<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php");
$hid = (int)$_SESSION['hotel_id']; $id = (int)($_GET['id'] ?? 0);
$img = $conn->query("SELECT * FROM hotel_images WHERE image_id=$id AND hotel_id=$hid")->fetch_assoc();
if ($img) { $p = "../../images/hotels/".$img['image_path']; if (is_file($p)) @unlink($p); $conn->query("DELETE FROM hotel_images WHERE image_id=$id AND hotel_id=$hid"); }
header("Location: hotel_images.php"); exit();
