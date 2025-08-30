<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_guest.php");
$q = trim($_GET['q'] ?? '');
$res = ($q==='') ? $conn->query("SELECT * FROM hotels ORDER BY hotel_id DESC")
                 : $conn->query("SELECT * FROM hotels WHERE hotel_name LIKE '%".esc($q)."%' OR description LIKE '%".esc($q)."%' ORDER BY hotel_id DESC");
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Search Hotels</title></head>
<body>
<div class="header"><div>Search Hotels</div><div class="nav"><a href="guest_home.php">Home</a> <a href="guest_logout.php">Logout</a></div></div>
<div class="main">
  <form method="get" class="form-container" style="margin-bottom:16px;">
    <div class="form-group"><label>Keyword</label><input type="text" name="q" value="<?= htmlspecialchars($q) ?>"></div>
    <button class="btn btn-primary">Search</button>
  </form>
  <table class="table">
    <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while($h = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $h['hotel_id'] ?></td>
        <td><?= htmlspecialchars($h['hotel_name']) ?></td>
        <td><?= nl2br(htmlspecialchars(substr($h['description'],0,140))) ?></td>
        <td><a class="btn btn-primary" href="guest_hotel_view.php?hotel_id=<?= $h['hotel_id'] ?>">View Details</a></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body></html>
