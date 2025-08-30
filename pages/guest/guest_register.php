<?php
require_once("../../includes/db_connect.php");
if (isset($_POST['register'])) {
  $name=esc($_POST['name']??''); $email=esc($_POST['email']??''); $phone=esc($_POST['phone']??'');
  $password=esc($_POST['password']??''); $confirm=esc($_POST['confirm']??'');
  if ($name && $email && $phone && $password) {
    if ($password!==$confirm) $error="Passwords do not match.";
    else {
      $exists=$conn->query("SELECT guest_id FROM guests WHERE email='$email'");
      if ($exists && $exists->num_rows) $error="Email already in use.";
      else {
        if ($conn->query("INSERT INTO guests (name,email,phone,password) VALUES ('$name','$email','$phone','$password')")) {
          $_SESSION['role']='guest'; $_SESSION['guest_id']=$conn->insert_id; $_SESSION['guest_name']=$_POST['name'];
          header("Location: guest_home.php"); exit();
        } else $error="Registration failed.";
      }
    }
  } else $error="All fields required.";
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Guest Register</title></head>
<body>
<div class="header"><div>Smart Stay – Guest Register</div><div class="nav"><a href="../../index.php">Home</a> <a href="guest_login.php">Login</a></div></div>
<div class="main">
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-group"><label>Phone</label><input type="text" name="phone" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm" required></div>
    <button class="btn btn-primary" name="register">Register</button>
  </form>
</div>
</body></html>
