<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "restaurant_db";
$port = 3307; // or 3307 if your WampServer uses MariaDB on 3307

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>