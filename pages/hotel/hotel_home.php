<?php require_once("../../includes/db_connect.php"); require_once("../../includes/auth_hotel.php"); ?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Hotel Dashboard</title></head>
<body>
<div class="header">
  <div>Hotel: <?= htmlspecialchars($_SESSION['hotel_name']) ?></div>
  <div class="nav">
    <a href="hotel_rooms.php">Rooms</a>
    <a href="hotel_events.php">Events</a>
    <a href="hotel_logout.php">Logout</a>
  </div>
</div>
<div class="main"><h2>Welcome to your Hotel Dashboard</h2><p>Manage rooms and events.</p></div>
</body></html>
