<?php
header('Content-Type: application/json');

require_once 'db_connect.php';

// Khởi tạo session
session_start();

// Giả định user_id từ session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2;

// Nhận dữ liệu từ request
$donhang_id = isset($_POST['donhang_id']) ? intval($_POST['donhang_id']) : 0;
$chitietsach_id = isset($_POST['chitietsach_id']) ? $_POST['chitietsach_id'] : '';
$ly_do = isset($_POST['ly_do']) ? trim($_POST['ly_do']) : '';

// Kiểm tra dữ liệu đầu vào
if ($donhang_id <= 0 || empty($chitietsach_id) || empty($ly_do)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

// Kiểm tra xem đơn hàng thuộc về người dùng
$sql_check = "SELECT user_id FROM DONHANG WHERE donhang_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $donhang_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows == 0 || $result_check->fetch_assoc()['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng không hợp lệ hoặc không thuộc về bạn.']);
    $stmt_check->close();
    exit;
}
$stmt_check->close();

// Kiểm tra xem bản sao đã có yêu cầu bảo hành chưa
$sql_exists = "SELECT donbaohanh_id FROM DONBAOHANH WHERE chitietsach_id = ?";
$stmt_exists = $conn->prepare($sql_exists);
$stmt_exists->bind_param("s", $chitietsach_id);
$stmt_exists->execute();
$result_exists = $stmt_exists->get_result();
if ($result_exists->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Bản sao này đã được gửi yêu cầu bảo hành.']);
    $stmt_exists->close();
    exit;
}
$stmt_exists->close();

// Chèn yêu cầu bảo hành vào bảng DONBAOHANH
$sql_insert = "INSERT INTO DONBAOHANH (donhang_id, chitietsach_id, ly_do, ngay, trang_thai)
               VALUES (?, ?, ?, CURDATE(), 'Chua hoan thanh')";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("iss", $donhang_id, $chitietsach_id, $ly_do);
if ($stmt_insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Yêu cầu bảo hành đã được gửi thành công.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi gửi yêu cầu bảo hành.']);
}
$stmt_insert->close();

$conn->close();
?>