<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WEB2_BookStore"; // Tên cơ sở dữ liệu

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
   die("Kết nối thất bại: " . $conn->connect_error);
}
