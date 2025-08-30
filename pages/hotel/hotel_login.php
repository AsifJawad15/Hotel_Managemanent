<?php
require_once("../../includes/db_connect.php");
if (isset($_POST['login'])) {
  $email = esc($_POST['email'] ?? ''); $password = esc($_POST['password'] ?? '');
  $res = $conn->query("SELECT * FROM hotels WHERE email='$email' AND password='$password'");
  if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc(); $_SESSION['role']='hotel'; $_SESSION['hotel_id']=$row['hotel_id']; $_SESSION['hotel_name']=$row['hotel_name'];
    header("Location: hotel_home.php"); exit();
  } else { $error = "Invalid credentials"; }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Hotel Login</title></head>
<body>
<div class="header"><div>Smart Stay – Hotel Login</div><div class="nav"><a href="../../index.php">Home</a> <a href="hotel_register.php">Register</a></div></div>
<div class="main">
  <h2>Hotel Login</h2>
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <button class="btn btn-primary" name="login">Login</button>
  </form>
</div>
</body></html>
