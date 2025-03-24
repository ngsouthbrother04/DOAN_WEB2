<?php
session_start();
include 'db_connect.php';

// Kiểm tra nếu form đăng nhập được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Xóa session cũ
   session_unset();
   
   // Lấy dữ liệu từ form
   $username = $_POST['username'];
   $password = $_POST['password'];

   // Kiểm tra xem username là email hay số điện thoại
   if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
      $sql = "SELECT * FROM `USER` WHERE email = ?";
   } else {
      $sql = "SELECT * FROM `USER` WHERE sdt = ?";
   }

   // Chuẩn bị và thực thi truy vấn
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("s", $username);
   $stmt->execute();
   $result = $stmt->get_result();

   if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();

      // Kiểm tra mật khẩu
      if ($password === $user['mat_khau']) { // Trong thực tế nên dùng password_verify()
         // Đăng nhập thành công
         $_SESSION['user_id'] = $user['user_id'];
         $_SESSION['user_name'] = $user['ho_ten']; // Đổi tên biến để khớp với session hiện tại
         $_SESSION['user_phone'] = $user['sdt']; // Lưu thêm số điện thoại
         $_SESSION['user_role'] = $user['quyen']; // Đổi tên biến để khớp với session hiện tại

         // Trả về kết quả thành công
         echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công!', 'user' => [
            'ho_ten' => $user['ho_ten'],
            'sdt' => $user['sdt'],
            'quyen' => $user['quyen']
         ]]);
      } else {
         // Mật khẩu không đúng
         echo json_encode(['success' => false, 'message' => 'Mật khẩu không đúng!']);
      }
   } else {
      // Không tìm thấy người dùng
      echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại!']);
   }

   $stmt->close();
   $conn->close();
   exit;
}

// Kiểm tra nếu yêu cầu đăng xuất
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
   // Hủy session
   session_unset();
   session_destroy();

   // Chuyển hướng về trang chủ
   header('Location: trangchu.php');
   exit;
}
