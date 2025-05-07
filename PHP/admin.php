<?php
// Kết nối database
include 'db_connect.php';

// Lấy số liệu thống kê
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM `USER` WHERE trang_thai = 'active'"))['total'];
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM SACH WHERE trang_thai = 'active'"))['total'];
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM DONHANG"))['total'];
$orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM DONHANG"))['total'];


?>

<!DOCTYPE html>
<html lang="vi">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>T1 Bookstore | ADMIN</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="../CSS/admin.css">
   <link rel="stylesheet" href="../CSS/index.css">
</head>

<body>
   <div class="sidebar">
      <div class="admin-profile">
         <div class="admin-avatar">
            <i class="fas fa-user"></i>
         </div>
         <div>Admin</div>
      </div>

      <div class="menu-item" data-target="edit_theLoai.php">Thể loại</div>
      <div class="menu-item" data-target="edit_sach.php">Sách</div>
      <div class="menu-item" data-target="edit_taikhoan.php">Tài khoản</div>
      <div class="menu-item" data-target="hoa_don.php">Đơn hàng</div>
      <div class="menu-item" data-target="bao_hanh.php">Bảo hành</div>
      <div class="menu-item" data-target="logout.php">Đăng xuất</div>
   </div>

   <div class="content">
      <div class="dashboard">
         <div class="dashboard-title">
            <i class="fas fa-chart-line"></i>
            Tổng quan hệ thống
         </div>

         <div class="stats-container">
            <div class="stat-card users-stat">
               <div class="stat-icon users-icon">
                  <i class="fas fa-users"></i>
               </div>
               <div class="stat-info">
                  <h3><?php echo $totalUsers; ?></h3>
                  <p>Người dùng hoạt động</p>
               </div>
            </div>

            <div class="stat-card products-stat">
               <div class="stat-icon products-icon">
                  <i class="fas fa-box"></i>
               </div>
               <div class="stat-info">
                  <h3><?php echo $totalProducts; ?></h3>
                  <p>Sản phẩm đang bán</p>
               </div>
            </div>

            <div class="stat-card orders-stat">
               <div class="stat-icon orders-icon">
                  <i class="fas fa-shopping-bag"></i>
               </div>
               <div class="stat-info">
                  <h3><?php echo $totalOrders; ?></h3>
                  <p>Đơn hàng đã đặt</p>
               </div>
            </div>

         </div>
      </div>
   </div>

   <script>
      // Click event cho menu ở ADMIN
      document.querySelectorAll('.menu-item').forEach(function(item) {
         item.addEventListener('click', function() {
            const menuText = this.textContent.trim();
            if (menuText === 'Đăng xuất') {
               if (confirm('Bạn có chắc muốn đăng xuất?')) {
                  window.location.href = 'logout.php';
               }
            } else if (menuText === 'Thể loại') {
               window.location.href = 'edit_loaisach.php';
            } else if (menuText === 'Sách') {
               window.location.href = 'edit_sach.php';
            } else if (menuText === 'Tài khoản') {
               window.location.href = 'edit_taikhoan.php';
            } else if (menuText === 'Đơn hàng') {
               window.location.href = 'admin_orders.php';
            } else if (menuText === 'Bảo hành') {
               window.location.href = 'admin_warranty.php';
            } else if (menuText === 'Trang chủ') {
               window.location.href = 'trangchu.php';
            }
         });
      });
   </script>
</body>

</html>