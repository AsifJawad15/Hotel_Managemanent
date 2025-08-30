<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_admin.php");
$hotels = $conn->query("SELECT * FROM hotels ORDER BY hotel_id DESC");
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Manage Hotels</title></head>
<body>
<div class="header"><div>Hotels</div><div class="nav"><a href="admin_home.php">Dashboard</a> <a href="admin_logout.php">Logout</a></div></div>
<div class="main">
<table class="table">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Description</th><th>Actions</th></tr></thead>
  <tbody>
    <?php while($h = $hotels->fetch_assoc()): ?>
      <tr>
        <td><?= $h['hotel_id'] ?></td>
        <td><?= htmlspecialchars($h['hotel_name']) ?></td>
        <td><?= htmlspecialchars($h['email']) ?></td>
        <td><?= nl2br(htmlspecialchars(substr($h['description'],0,120))) ?></td>
        <td><a class="btn btn-danger" onclick="return confirmDelete();" href="admin_delete_hotel.php?id=<?= $h['hotel_id'] ?>">Delete</a></td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
</div>
<script src='../../js/script.js'></script>
</body></html>
