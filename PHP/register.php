<?php
session_start();
include 'db_connect.php';

// Kiểm tra nếu form được gửi đi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $dob = $_POST['dob'];
    $username = trim($_POST['username']);  // Số điện thoại
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra dữ liệu nhập vào
    if (empty($full_name) || empty($email) || empty($address) || empty($dob) || empty($username) || empty($password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit;
    }

    if (!preg_match('/^[0-9]{10,11}$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ!']);
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp!']);
        exit;
    }

    // Kiểm tra xem email hoặc số điện thoại đã tồn tại chưa
    $stmt = $conn->prepare("SELECT * FROM `USER` WHERE email = ? OR sdt = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email hoặc số điện thoại đã được sử dụng!']);
        exit;
    }

    // Chèn dữ liệu vào database
    $stmt = $conn->prepare("INSERT INTO `USER` (ho_ten, sdt, dia_chi, ngay_sinh, email, mat_khau) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $full_name, $username, $address, $dob, $email, $password);

    if ($stmt->execute()) {
        // Lưu thông tin vào session sau khi đăng ký thành công
        $_SESSION['user_id'] = $conn->insert_id; // Lưu ID người dùng
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_phone'] = $username;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_dob'] = date('d-m-Y', strtotime($dob)); // Chuyển đổi ngày sinh sang định dạng 'd-m-Y'
        $_SESSION['user_address'] = $address;

        // Trả về kết quả thành công
        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Đăng ký thất bại!']);
    }

    // Đóng kết nối
    $stmt->close();
    $conn->close();
    exit;
}
