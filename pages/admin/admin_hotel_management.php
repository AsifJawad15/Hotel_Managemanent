<?php
require_once("../../includes/db_connect.php");
require_once("../../includes/auth_admin.php");
// Scaffold for hotel management features
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../css/style.css"><title>Hotel Management</title></head>
<body>
<div class="header"><div>Hotel Management</div><div class="nav"><a href="admin_home.php">Dashboard</a> <a href="admin_logout.php">Logout</a></div></div>
<div class="main">
<h2>Hotel Management</h2>
<p>Add, edit, delete hotel services, staff, and details here.</p>
<!-- TODO: Implement hotel services, staff, details CRUD -->
</div>
<script src='../../js/script.js'></script>
</body></html>