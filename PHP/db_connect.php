<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "WEB2_BookStore";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
   die("Kết nối thất bại: " . $conn->connect_error);
}
