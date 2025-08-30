<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_hotel.php");

$hid   = (int)$_SESSION['hotel_id'];
$hname = $_SESSION['hotel_name'] ?? '';

$imgs = $conn->query("SELECT * FROM hotel_images WHERE hotel_id=$hid ORDER BY image_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Hotel Images</title>
  <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="header">
  <div>Images</div>
  <div class="nav">
    <a href="hotel_home.php">Dashboard</a>
    <a href="add_hotel_image.php" class="btn btn-primary" style="margin-left:10px;">+ Add Image</a>
    <a href="hotel_logout.php">Logout</a>
  </div>
</div>

<div class="main">
  <h3>Hotel: <?= htmlspecialchars($hname) ?></h3>

  <?php if ($imgs && $imgs->num_rows > 0): ?>
    <!-- one-by-one list style (each image in its own row) -->
    <table class="table">
      <thead>
        <tr>
          <th>Image ID</th>
          <th>Hotel Name</th>
          <th>Preview</th>
          <th>File</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($im = $imgs->fetch_assoc()): ?>
        <tr>
          <td><?= $im['image_id'] ?></td>
          <td><?= htmlspecialchars($hname) ?></td>
          <td>
            <img src="../../images/hotels/<?= htmlspecialchars($im['image_path']) ?>"
                 alt="hotel image" style="width:180px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb">
          </td>
          <td><?= htmlspecialchars($im['image_path']) ?></td>
          <td>
            <a class="btn btn-danger" onclick="return confirmDelete();" href="hotel_delete_image.php?id=<?= $im['image_id'] ?>">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No images uploaded yet.</p>
  <?php endif; ?>
</div>

<script src='../../js/script.js'></script>
</body>
</html>
