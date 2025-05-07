<?php
session_start();
require_once 'db_connect.php';

// Hàm lấy đường dẫn hình ảnh
function getImagePath($image_url)
{
   $file_name = basename($image_url);
   return "../Picture/Products/" . $file_name;
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
   header("Location: login.php?return_url=" . urlencode("thanhtoan.php"));
   exit;
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$user_query = "SELECT ho_ten, sdt, dia_chi FROM `USER` WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Lấy thông tin giỏ hàng
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
   $sach_ids = array_column($_SESSION['cart'], 'id');
   $sach_ids = array_map(function($id) use ($conn) {
      return "'" . mysqli_real_escape_string($conn, $id) . "'";
   }, $sach_ids);
   $sach_ids_str = implode(',', $sach_ids);

   $query = "SELECT sach_id, tieu_de, gia_tien, hinh_anh, so_luong FROM SACH WHERE sach_id IN ($sach_ids_str)";
   $result = mysqli_query($conn, $query);

   while ($row = mysqli_fetch_assoc($result)) {
      foreach ($_SESSION['cart'] as $item) {
         if ($item['id'] == $row['sach_id']) {
            // Kiểm tra số lượng tồn kho
            $quantity = min($item['quantity'], $row['so_luong']);
            if ($quantity > 0) {
               $cart_items[] = [
                  'id' => $row['sach_id'],
                  'tieu_de' => $row['tieu_de'],
                  'gia_tien' => $row['gia_tien'],
                  'hinh_anh' => $row['hinh_anh'],
                  'so_luong' => $quantity,
                  'thanh_tien' => $row['gia_tien'] * $quantity
               ];
               $total_price += $row['gia_tien'] * $quantity;
            }
         }
      }
   }
}

// Xử lý đặt hàng
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
   $ho_ten = mysqli_real_escape_string($conn, $_POST['ho_ten']);
   $sdt = mysqli_real_escape_string($conn, $_POST['sdt']);
   $dia_chi = mysqli_real_escape_string($conn, $_POST['dia_chi']);

   // Kiểm tra giỏ hàng không rỗng
   if (empty($cart_items)) {
      $message = 'Giỏ hàng của bạn đang trống!';
      $message_type = 'error';
   } else {
      // Bắt đầu transaction
      mysqli_begin_transaction($conn);

      try {
         // Tạo đơn hàng
         $ngay_dat = date('Y-m-d H:i:s');
         $trang_thai = 'cho_xac_nhan';
         $insert_donhang = "INSERT INTO DONHANG (user_id, ngay_dat, tong_tien, trang_thai) 
                           VALUES ('$user_id', '$ngay_dat', '$total_price', '$trang_thai')";
         mysqli_query($conn, $insert_donhang);
         $donhang_id = mysqli_insert_id($conn);

         // Tạo chi tiết đơn hàng và gọi stored procedure
         foreach ($cart_items as $item) {
            $sach_id = $item['id'];
            $gia_tien = $item['gia_tien'];
            $so_luong = $item['so_luong'];

            // Thêm chi tiết đơn hàng
            $insert_chitiet = "INSERT INTO CHITIETDONHANG (donhang_id, sach_id, gia_tien, so_luong) 
                              VALUES ('$donhang_id', '$sach_id', '$gia_tien', '$so_luong')";
            mysqli_query($conn, $insert_chitiet);
            $chitiet_id = mysqli_insert_id($conn);

            // Gọi stored procedure CreateChiTietSach
            $call_procedure = "CALL CreateChiTietSach('$sach_id', '$chitiet_id', '$so_luong')";
            mysqli_query($conn, $call_procedure);

            // Cập nhật số lượng tồn kho
            $update_sach = "UPDATE SACH SET so_luong = so_luong - '$so_luong' WHERE sach_id = '$sach_id'";
            mysqli_query($conn, $update_sach);
         }

         // Xóa giỏ hàng
         unset($_SESSION['cart']);

         // Commit transaction
         mysqli_commit($conn);

         $message = 'Đặt hàng thành công! Đơn hàng của bạn đang chờ xác nhận.';
         $message_type = 'success';

         // Chuyển hướng đến trang xác nhận
         header("Location: xacnhandonhang.php?donhang_id=$donhang_id");
         exit;
      } catch (Exception $e) {
         // Rollback nếu có lỗi
         mysqli_rollback($conn);
         $message = 'Đã xảy ra lỗi khi đặt hàng: ' . $e->getMessage();
         $message_type = 'error';
      }
   }
}

$page_title = "Thanh Toán";
?>

<!DOCTYPE html>
<html>
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php echo $page_title; ?> - WEB2 BookStore</title>
   <link rel="stylesheet" href="../CSS/index.css">
   <link rel="stylesheet" href="../CSS/thanhtoan.css">
   <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
   <?php include 'header.php'; ?>

   <div class="checkout-container" style="margin-top: 100px !important; margin: 0 auto;">
      <h1>Thanh Toán</h1>

      <!-- Hiển thị thông báo nếu có -->
      <?php if (!empty($message)): ?>
         <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
         </div>
      <?php endif; ?>

      <?php if (empty($cart_items)): ?>
         <p class="empty-cart">Giỏ hàng của bạn đang trống.</p>
      <?php else: ?>
         <div class="checkout-content">
            <!-- Thông tin giao hàng -->
            <div class="shipping-info">
               <h2>Thông tin giao hàng</h2>
               <form method="post" action="thanhtoan.php">
                  <div class="form-group">
                     <label for="ho_ten">Họ và tên:</label>
                     <input disabled type="text" id="ho_ten" name="ho_ten" value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
                  </div>
                  <div class="form-group">
                     <label for="sdt">Số điện thoại:</label>
                     <input disabled type="tel" id="sdt" name="sdt" value="<?php echo htmlspecialchars($user['sdt']); ?>" required>
                  </div>
                  <div class="form-group">
                     <label for="dia_chi">Địa chỉ giao hàng:</label>
                     <textarea disabled id="dia_chi" name="dia_chi" required><?php echo htmlspecialchars($user['dia_chi']); ?></textarea>
                  </div>
                  <button type="submit" name="place_order" class="place-order-button">Đặt hàng</button>
               </form>
            </div>

            <!-- Chi tiết giỏ hàng -->
            <div class="cart-details">
               <h2>Chi tiết đơn hàng</h2>
               <table class="cart-table">
                  <thead>
                     <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($cart_items as $item): ?>
                        <tr>
                           <td class="product-info">
                              <img src="<?php echo getImagePath($item['hinh_anh']); ?>" alt="<?php echo $item['tieu_de']; ?>">
                              <span><?php echo htmlspecialchars($item['tieu_de']); ?></span>
                           </td>
                           <td><?php echo number_format($item['gia_tien'], 0, ',', '.'); ?> VND</td>
                           <td><?php echo $item['so_luong']; ?></td>
                           <td><?php echo number_format($item['thanh_tien'], 0, ',', '.'); ?> VND</td>
                        </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
               <div class="cart-total">
                  <h3>Tổng cộng: <span><?php echo number_format($total_price, 0, ',', '.'); ?> VND</span></h3>
               </div>
            </div>
         </div>
      <?php endif; ?>
   </div>
   <?php include 'login-register/login-register-form.php'; ?>
    <?php include 'profile-form.php'; ?>
   <?php include 'footer.php'; ?>
</body>
</html>