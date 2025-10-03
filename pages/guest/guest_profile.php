<?php
require_once("../../includes/db_connect.php"); require_once("../../includes/auth_guest.php");
$gid=(int)$_SESSION['guest_id']; $g=$conn->query("SELECT * FROM guests WHERE guest_id=$gid")->fetch_assoc();
if (!$g) { header("Location: guest_home.php"); exit(); }
if (isset($_POST['save'])) {
  $name=esc($_POST['name']??''); $email=esc($_POST['email']??''); $phone=esc($_POST['phone']??''); $password=esc($_POST['password']??'');
  $check=$conn->query("SELECT guest_id FROM guests WHERE email='$email' AND guest_id<>$gid");
  if ($check && $check->num_rows) $error="Email already used by another account.";
  else {
    $set="name='$name', email='$email', phone='$phone'"; if ($password!=='') $set.=", password='$password'";
    if ($conn->query("UPDATE guests SET $set WHERE guest_id=$gid")) { $_SESSION['guest_name']=$_POST['name']; $msg="Profile updated."; $g=$conn->query("SELECT * FROM guests WHERE guest_id=$gid")->fetch_assoc(); }
    else $error="Update failed.";
  }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>My Profile</title></head>
<body>
<div class="header"><div>My Profile</div><div class="nav"><a href="guest_home.php">Home</a> <a href="guest_search.php">Search</a> <a href="guest_my_bookings.php">My Bookings</a> <a href="guest_my_reviews.php">My Reviews</a> <a href="guest_logout.php">Logout</a></div></div>
<div class="main">
  <?php if (!empty($msg)) echo "<p class='notice'>$msg</p>"; ?>
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($g['name']) ?>" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($g['email']) ?>" required></div>
    <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($g['phone']) ?>" required></div>
    <div class="form-group"><label>New Password (leave blank to keep)</label><input type="password" name="password"></div>
    <button class="btn btn-primary" name="save">Save</button>
  </form>
</div>
</body></html>
