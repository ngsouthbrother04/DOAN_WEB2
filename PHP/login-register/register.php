<?php
session_start();
include '../db_connect.php';

// Ensure proper character encoding for Vietnamese
header('Content-Type: application/json; charset=UTF-8');

// Check database connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $conn->connect_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy và làm sạch dữ liệu đầu vào
    $full_name = trim($_POST['full_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Kiểm tra dữ liệu bắt buộc
    if (empty($full_name) || empty($address) || empty($dob) || empty($username) || empty($password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit;
    }

    // Kiểm tra định dạng số điện thoại
    if (!preg_match('/^[0-9]{10,11}$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ!']);
        exit;
    }

    // Kiểm tra mật khẩu xác nhận
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp!']);
        exit;
    }

    // Kiểm tra trùng số điện thoại
    $stmt = $conn->prepare("SELECT * FROM `USER` WHERE sdt = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Lỗi thực thi truy vấn: ' . $stmt->error]);
        $stmt->close();
        exit;
    }
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại đã được sử dụng!']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Thêm người dùng mới
    $role = 'KhachHang';
    $status = 'active';

    $stmt = $conn->prepare("INSERT INTO `USER` (ho_ten, sdt, dia_chi, ngay_sinh, mat_khau, quyen, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("sssssss", $full_name, $username, $address, $dob, $password, $role, $status);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_phone'] = $username;
        $_SESSION['user_dob'] = date('d-m-Y', strtotime($dob));
        $_SESSION['user_address'] = $address;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_status'] = $status;

        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Đăng ký thất bại: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
