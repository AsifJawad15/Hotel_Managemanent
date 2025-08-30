<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_hotel.php");

$hid   = (int)$_SESSION['hotel_id'];
$hname = $_SESSION['hotel_name'] ?? '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
  if (!file_exists("../../images/hotels")) {
    @mkdir("../../images/hotels", 0777, true);
  }

  if ($_FILES['image']['error'] === 0) {
    // sanitize filename
    $orig = basename($_FILES['image']['name']);
    $safe = preg_replace('/[^A-Za-z0-9_.-]/', '_', $orig);
    // prefix with hotel id + timestamp so names are unique
    $newName = $hid . "_" . time() . "_" . $safe;
    $dest    = "../../images/hotels/" . $newName;

    // move file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
      // map image to this hotel (by id; name is just for display)
      $conn->query("INSERT INTO hotel_images (hotel_id, image_path) VALUES ($hid, '$newName')");
      header("Location: hotel_images.php"); exit();
    } else {
      $error = "Upload failed. Please try again.";
    }
  } else {
    $error = "No file chosen or file too large.";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Hotel Image</title>
  <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
<div class="header">
  <div>Add Hotel Image</div>
  <div class="nav">
    <a href="hotel_images.php">Back to Images</a>
    <a href="hotel_home.php">Dashboard</a>
  </div>
</div>

<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

  <form method="post" enctype="multipart/form-data" class="form-container">
    <div class="form-group">
      <label>Hotel Name</label>
      <input type="text" value="<?= htmlspecialchars($hname) ?>" readonly>
      <!-- we match by hotel_id in DB; hotel name is for display -->
      <input type="hidden" name="hotel_id" value="<?= $hid ?>">
    </div>

    <div class="form-group">
      <label>Choose Image</label>
      <input type="file" name="image" accept="image/*" required>
    </div>

    <button class="btn btn-primary">Upload Image</button>
  </form>
</div>
</body>
</html>
