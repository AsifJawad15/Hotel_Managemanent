<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_admin.php");
$guests = $conn->query("SELECT * FROM guests ORDER BY guest_id DESC");
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Manage Guests</title></head>
<body>
<div class="header"><div>Guests</div><div class="nav"><a href="admin_home.php">Dashboard</a> <a href="admin_logout.php">Logout</a></div></div>
<div class="main">
<table class="table">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th></tr></thead>
  <tbody>
    <?php while($g = $guests->fetch_assoc()): ?>
      <tr>
        <td><?= $g['guest_id'] ?></td>
        <td><?= htmlspecialchars($g['name']) ?></td>
        <td><?= htmlspecialchars($g['email']) ?></td>
        <td><?= htmlspecialchars($g['phone']) ?></td>
        <td><a class="btn btn-danger" onclick="return confirmDelete();" href="admin_delete_guest.php?id=<?= $g['guest_id'] ?>">Delete</a></td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
</div>
<script src='../../js/script.js'></script>
</body></html>
