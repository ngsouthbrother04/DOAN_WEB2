<?php
session_start();
include 'db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối database thất bại']);
    exit;
}

// Nhận dữ liệu từ frontend
$data = json_decode(file_get_contents('php://input'), true);

// Lấy user_id từ session
$user_id = $_SESSION['user_id'];

// Lấy thông tin gửi lên
$name = $conn->real_escape_string($data['user-name'] ?? '');
$dob = !empty($data['user-dob']) ? $conn->real_escape_string($data['user-dob']) : '0000-00-00';
$phone = $conn->real_escape_string($data['user-phone'] ?? '');
$address = $conn->real_escape_string($data['user-address'] ?? '');

// Cập nhật vào database (KHÔNG cập nhật email)
$sql = "UPDATE user SET 
            ho_ten = '$name',
            ngay_sinh = '$dob',
            sdt = '$phone',
            dia_chi = '$address'
        WHERE user_id = $user_id";

if ($conn->query($sql)) {
    // Cập nhật lại session
    $_SESSION['user_name'] = $name;
    $_SESSION['user_dob'] = $dob;
    $_SESSION['user_phone'] = $phone;
    $_SESSION['user_address'] = $address;

    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật: ' . $conn->error]);
}

$conn->close();
