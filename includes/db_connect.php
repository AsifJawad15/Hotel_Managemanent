<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "smart_stay";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");
function esc($s) { global $conn; return $conn->real_escape_string($s); }
?>
