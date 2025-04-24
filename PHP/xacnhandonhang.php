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
   header("Location: login.php?return_url=" . urlencode("xacnhandonhang.php"));
   exit;
}

// Kiểm tra mã đơn hàng
if (!isset($_GET['donhang_id']) || empty($_GET['donhang_id'])) {
   header("Location: trangchu.php");
   exit;
}

$donhang_id = mysqli_real_escape_string($conn, $_GET['donhang_id']);
$user_id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng
$donhang_query = "SELECT d.donhang_id, d.ngay_dat, d.tong_tien, d.trang_thai, 
                         u.ho_ten, u.sdt, u.dia_chi
                  FROM DONHANG d
                  JOIN `USER` u ON d.user_id = u.user_id
                  WHERE d.donhang_id = '$donhang_id' AND d.user_id = '$user_id'";
$donhang_result = mysqli_query($conn, $donhang_query);

if (mysqli_num_rows($donhang_result) == 0) {
   header("Location: trangchu.php");
   exit;
}

$donhang = mysqli_fetch_assoc($donhang_result);

// Lấy chi tiết đơn hàng
$chitiet_query = "SELECT c.sach_id, c.gia_tien, c.so_luong, s.tieu_de, s.hinh_anh
                  FROM CHITIETDONHANG c
                  JOIN SACH s ON c.sach_id = s.sach_id
                  WHERE c.donhang_id = '$donhang_id'";
$chitiet_result = mysqli_query($conn, $chitiet_query);

$chitiet_items = [];
while ($row = mysqli_fetch_assoc($chitiet_result)) {
   $chitiet_items[] = [
      'sach_id' => $row['sach_id'],
      'tieu_de' => $row['tieu_de'],
      'gia_tien' => $row['gia_tien'],
      'so_luong' => $row['so_luong'],
      'hinh_anh' => $row['hinh_anh'],
      'thanh_tien' => $row['gia_tien'] * $row['so_luong']
   ];
}

$page_title = "Xác Nhận Đơn Hàng #{$donhang['donhang_id']}";
?>

<!DOCTYPE html>
<html>
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php echo $page_title; ?> - WEB2 BookStore</title>
   <link rel="stylesheet" href="../CSS/index.css">
   <link rel="stylesheet" href="../CSS/xacnhandonhang.css">
   <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
   <?php include 'header.php'; ?>

   <div class="order-confirmation-container" style="margin-top: 100px !important;">
      <h1>Xác Nhận Đơn Hàng</h1>
      <p class="success-message">Đặt hàng thành công! Cảm ơn bạn đã mua sắm tại WEB2 BookStore.</p>

      <div class="order-details">
         <!-- Thông tin đơn hàng -->
         <div class="order-info">
            <h2>Thông tin đơn hàng</h2>
            <p><strong>Mã đơn hàng:</strong> <?php echo $donhang['donhang_id']; ?></p>
            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($donhang['ngay_dat'])); ?></p>
            <p><strong>Trạng thái:</strong> 
               <?php 
               $trang_thai = [
                  'cho_xac_nhan' => 'Chờ xác nhận',
                  'da_xac_nhan' => 'Đã xác nhận',
                  'da_duoc_giao' => 'Đã được giao',
                  'da_bi_huy' => 'Đã bị hủy'
               ];
               echo $trang_thai[$donhang['trang_thai']]; 
               ?>
            </p>
         </div>

         <!-- Thông tin giao hàng -->
         <div class="shipping-info">
            <h2>Thông tin giao hàng</h2>
            <p><strong>Họ và tên:</strong> <?php echo htmlspecialchars($donhang['ho_ten']); ?></p>
            <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($donhang['sdt']); ?></p>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($donhang['dia_chi']); ?></p>
         </div>

         <!-- Chi tiết đơn hàng -->
         <div class="order-items">
            <h2>Chi tiết đơn hàng</h2>
            <table class="order-table">
               <thead>
                  <tr>
                     <th>Sản phẩm</th>
                     <th>Đơn giá</th>
                     <th>Số lượng</th>
                     <th>Thành tiền</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach ($chitiet_items as $item): ?>
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
            <div class="order-total">
               <h3>Tổng cộng: <span><?php echo number_format($donhang['tong_tien'], 0, ',', '.'); ?> VND</span></h3>
            </div>
         </div>
      </div>

      <div class="actions">
         <a href="trangchu.php" class="continue-shopping">Tiếp tục mua sắm</a>
      </div>
   </div>

   <?php include 'footer.php'; ?>
</body>
</html>