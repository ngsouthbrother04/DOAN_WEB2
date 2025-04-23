<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WEB2_BookStore";

// Kết nối cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donhang_id = intval($_POST['donhang_id']);
    $chitietsach_id = $conn->real_escape_string($_POST['chitietsach_id']);
    $ly_do = $conn->real_escape_string($_POST['ly_do']);
    $ngay = date('Y-m-d');

    // Chèn yêu cầu bảo hành
    $sql = "INSERT INTO DONBAOHANH (donhang_id, chitietsach_id, ly_do, ngay, trang_thai)
            VALUES (?, ?, ?, ?, 'Chua hoan thanh')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $donhang_id, $chitietsach_id, $ly_do, $ngay);
    
    if ($stmt->execute()) {
        echo "Yêu cầu bảo hành đã được gửi thành công!";
    } else {
        echo "Lỗi: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>