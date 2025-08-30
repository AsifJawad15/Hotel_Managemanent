<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guest' || !isset($_SESSION['guest_id'])) {
  header("Location: ../../index.php"); exit();
}
?>
