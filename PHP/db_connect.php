<?php
$servername = "localhost";
$username = "root";
$password = ""; // Để trống vì Xampp thường không cài đặt mật khẩu mặc định
$dbname = "WEB2_BookStore";
// Bỏ cổng vì Xampp sử dụng cổng mặc định 3306

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
   die("Kết nối thất bại: " . $conn->connect_error);
}
