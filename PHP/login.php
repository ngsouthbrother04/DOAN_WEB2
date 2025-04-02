<?php
session_start();
include 'db_connect.php';

// Kiểm tra nếu form đăng nhập được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Xóa session cũ
   session_unset();

   // Lấy dữ liệu từ form
   $username = trim($_POST['username']);
   $password = trim($_POST['password']);

   // Kiểm tra xem username là số điện thoại
   $sql = "SELECT * FROM `USER` WHERE sdt = ?";

   // Chuẩn bị và thực thi truy vấn
   if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         $user = $result->fetch_assoc();

         if ($password === $user['mat_khau']) {
            // Lưu thông tin vào session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['ho_ten'];
            $_SESSION['user_phone'] = $user['sdt'];
            $_SESSION['user_role'] = $user['quyen'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_dob'] = date('d-m-Y', strtotime($user['ngay_sinh']));
            $_SESSION['user_address'] = $user['dia_chi'];

            // Trả về kết quả thành công
            echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công!', 'user' => [
               'ho_ten' => $user['ho_ten'],
               'sdt' => $user['sdt'],
               'quyen' => $user['quyen'],
               'email' => $user['email'],
               'ngay_sinh' => $user['ngay_sinh'],
               'dia_chi' => $user['dia_chi'],
            ]]);
         } else {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu không đúng!']);
         }
      } else {
         echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại!']);
      }

      $stmt->close();
   }

   $conn->close();
   exit;
}

// Kiểm tra nếu yêu cầu đăng xuất
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
   session_unset();
   session_destroy();
   header('Location: trangchu.php');
   exit;
}
