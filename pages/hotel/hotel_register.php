<?php
require_once("../../includes/db_connect.php");
if (isset($_POST['register'])) {
  $name = esc($_POST['hotel_name'] ?? ''); $email = esc($_POST['email'] ?? '');
  $password = esc($_POST['password'] ?? ''); $desc = esc($_POST['description'] ?? '');
  if ($name && $email && $password) {
    $exists = $conn->query("SELECT hotel_id FROM hotels WHERE email='$email'");
    if ($exists && $exists->num_rows) { $error = "Email already in use."; }
    else {
      if ($conn->query("INSERT INTO hotels (hotel_name,email,password,description) VALUES ('$name','$email','$password','$desc')")) {
        $_SESSION['role']='hotel'; $_SESSION['hotel_id']=$conn->insert_id; $_SESSION['hotel_name']=$_POST['hotel_name'];
        header("Location: hotel_home.php"); exit();
      } else { $error="Failed to create account."; }
    }
  } else { $error="All fields required."; }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Hotel Register</title></head>
<body>
<div class="header"><div>Smart Stay – Hotel Register</div><div class="nav"><a href="../../index.php">Home</a> <a href="hotel_login.php">Login</a></div></div>
<div class="main">
  <h2>Create Hotel</h2>
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Hotel Name</label><input type="text" name="hotel_name" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <div class="form-group"><label>Description</label><textarea name="description" rows="5"></textarea></div>
    <button class="btn btn-primary" name="register">Register</button>
  </form>
</div>
</body></html>
