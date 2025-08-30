<?php
require_once("../../includes/db_connect.php");
if (isset($_POST['login'])) {
  $username = esc($_POST['username'] ?? '');
  $password = esc($_POST['password'] ?? '');
  $res = $conn->query("SELECT * FROM admins WHERE username='$username' AND password='$password'");
  if ($res && $res->num_rows === 1) {
    $_SESSION['role'] = 'admin';
    $_SESSION['admin_username'] = $username;
    header("Location: admin_home.php"); exit();
  } else { $error = "Invalid credentials"; }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Admin Login</title></head>
<body>
<div class="header"><div>Smart Stay – Admin</div><div class="nav"><a href="../../index.php">Home</a></div></div>
<div class="main">
  <h2>Admin Login</h2>
  <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="post" class="form-container">
    <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <button class="btn btn-primary" name="login">Login</button>
  </form>
</div>
</body></html>
