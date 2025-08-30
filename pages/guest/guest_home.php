<?php require_once("../../includes/db_connect.php"); require_once("../../includes/auth_guest.php"); ?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Guest Home</title></head>
<body>
<div class="header">
  <div>Hello, <?= htmlspecialchars($_SESSION['guest_name']) ?></div>
<div class="nav">
  <a href="guest_search.php">Search Hotels</a>
  <a href="guest_my_bookings.php">My Bookings</a>
  <a href="guest_profile.php">My Profile</a>
  <a href="guest_logout.php">Logout</a>
</div>

</div>
<div class="main"><h2>Welcome!</h2><p>Search hotels and book rooms or events.</p></div>
</body></html>
