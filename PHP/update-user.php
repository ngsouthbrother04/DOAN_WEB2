<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

session_start();
include 'db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

// Kết nối database
$conn = new mysqli($servername, $username, $password, $dbname); 
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối database thất bại']);
    exit;
}

// Nhận dữ liệu từ frontend
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu gửi lên không hợp lệ']);
    exit;
}

// Lấy user_id từ session
$user_id = intval($_SESSION['user_id']);

// Lấy thông tin gửi lên và xử lý an toàn
$name = $conn->real_escape_string($data['user-name'] ?? '');
$dob = !empty($data['user-dob']) ? $conn->real_escape_string($data['user-dob']) : '0000-00-00';
$phone = $conn->real_escape_string($data['user-phone'] ?? '');
$address = $conn->real_escape_string($data['user-address'] ?? '');

// Cập nhật vào database
$sql = "UPDATE user SET 
            ho_ten = '$name',
            ngay_sinh = '$dob',
            sdt = '$phone',
            dia_chi = '$address'
        WHERE user_id = $user_id";

if ($conn->query($sql)) {
    // Cập nhật session
    $_SESSION['user_name'] = $name;
    $_SESSION['user_dob'] = $dob;
    $_SESSION['user_phone'] = $phone;
    $_SESSION['user_address'] = $address;

    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật: ' . $conn->error]);
}

$conn->close();
