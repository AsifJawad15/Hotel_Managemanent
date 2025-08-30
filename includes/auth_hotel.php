<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'hotel' || !isset($_SESSION['hotel_id'])) {
  header("Location: ../../index.php"); exit();
}
?>
