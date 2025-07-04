<?php
$host = "localhost";
$username = "mwa";
$password = "mwa";
$dbname = "placement_vit";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kolkata');
?>
