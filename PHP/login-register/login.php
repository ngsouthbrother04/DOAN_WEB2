<?php
session_start();
include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   session_unset();

   $username = trim($_POST['username']);
   $password = trim($_POST['password']);

   $sql = "SELECT * FROM `USER` WHERE sdt = ?";

   if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         $user = $result->fetch_assoc();

         if ($password === $user['mat_khau']) {
            if ($user['trang_thai'] === 'isBlocked') {
               echo json_encode(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa!']);
            } else {
               $_SESSION['user_id'] = $user['user_id'];
               $_SESSION['user_name'] = $user['ho_ten'];
               $_SESSION['user_phone'] = $user['sdt'];
               $_SESSION['user_role'] = $user['quyen'];
               $_SESSION['user_dob'] = date('d-m-Y', strtotime($user['ngay_sinh']));
               $_SESSION['user_address'] = $user['dia_chi'];

               echo json_encode([
                  'success' => true,
                  'message' => 'Đăng nhập thành công!',
                  'user_role' => $user['quyen'],
                  'user' => [
                     'ho_ten' => $user['ho_ten'],
                     'sdt' => $user['sdt'],
                     'quyen' => $user['quyen'],
                     'ngay_sinh' => date('d-m-Y', strtotime($user['ngay_sinh'])),
                     'dia_chi' => $user['dia_chi'],
                  ]
               ]);
            }
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

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
   session_unset();
   session_destroy();
   header('Location: ../trangchu.php');
   exit;
}
