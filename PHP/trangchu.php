<?php
session_start();
include 'db_connect.php';
include 'pagination.php';
include 'product_filter.php';

$category_query = "SELECT loaisach_id, ten_loai FROM LOAISACH";
$category_result = $conn->query($category_query);
$categories = [];
if ($category_result) {
   while ($row = $category_result->fetch_assoc()) {
      $categories[] = $row;
   }
}

$items_per_page = 6;
$filtered_data = getFilteredProducts($conn, $items_per_page);
$products = $filtered_data['products'];
$pagination = $filtered_data['pagination'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Document</title>
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="../CSS/index.css">
   <link rel="stylesheet" href="../CSS/product.css">
</head>

<body>
   <div class="header">
      <div id="topmenu1">
         <img src="../Picture/CT_T3_header_1263x60.webp" alt="" id="anh_topmenu">
      </div>
      <div id="topmenu2">
         <a href="trangchu.php">
            <img src="../Picture/logo.png" alt="" id="logo">
         </a>
         <div class="menubutton">
            <img src="../icon/bars-solid.svg" alt="" id="menubutton">
            <p>Danh Mục</p>
            <div class="dropdown-menu">
               <ul>
                  <?php foreach ($categories as $category): ?>
                     <li>
                        <a href="category.php?category=<?php echo urlencode($category['loaisach_id']); ?>">
                           <?php echo htmlspecialchars($category['ten_loai']); ?>
                        </a>
                     </li>
                  <?php endforeach; ?>
               </ul>
            </div>
         </div>
         <div class="search-container">
            <form action="timsanpham.php" method="GET">
               <input type="text" autocomplete="off" name="search_term" class="search-input" placeholder="Tìm tại đây">
               <button type="submit" class="search-button">
                  <img src="../icon/magnifying-glass-solid.svg" alt="" style="width: 17px; height: 17px;">
               </button>
               <div class="advance_search">
                  <div class="advance_search-menu">
                     <div class="filter-search-group">
                        <label for="category-search">Danh mục:</label>
                        <select id="category-search" name="category-search">
                           <option value="">Tất cả</option>
                           <?php foreach ($categories as $category): ?>
                              <option value="<?php echo htmlspecialchars($category['loaisach_id']); ?>">
                                 <?php echo htmlspecialchars($category['ten_loai']); ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                     <div class="filter-search-group">
                        <label for="price-min-search">Giá từ:</label>
                        <input type="number" id="price-min-search" name="price-min-search" min="0" placeholder="0">
                     </div>
                     <div class="filter-search-group">
                        <label for="price-max-search">Đến:</label>
                        <input type="number" id="price-max-search" name="price-max-search" min="0" placeholder="1000000">
                     </div>
                  </div>
               </div>
            </form>
         </div>
         <div class="right-buttons">
            <div class="userbutton">
               <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_phone'])): ?>
                  <img src="../icon/user-regular.svg" alt="" id="userbutton">
                  <p title="<?php echo $_SESSION['user_name']; ?>"><?php echo $_SESSION['user_phone']; ?></p>
               <?php else: ?>
                  <img src="../icon/user-regular.svg" alt="" id="userbutton">
                  <p>Tài khoản</p>
               <?php endif; ?>
            </div>
            <div class="cartbutton">
               <img src="../icon/cart-shopping-solid.svg" alt="" id="cartbutton">
               <p>Giỏ Hàng</p>
            </div>
            <div class="bellbutton">
               <img src="../icon/bell-regular.svg" alt="" id="bellbutton">
               <p>Thông Báo</p>
            </div>
         </div>
      </div>
   </div>
   <div class="main">
      <div class="banner">
         <div id="bannerto">
            <img src="../Picture/Banner/MCBooks_Vangt2_840x320.webp" alt="">
         </div>
         <div class="bannernho">
            <div id="bannernho1">
               <img src="../Picture/Banner/thanhtoankhongtienmat_392x156_1.webp" alt="" style="margin-bottom: 3px;">
            </div>
            <div id="bannernho2">
               <img src="../Picture/Banner/UuDai_update_392x156.webp" alt="">
            </div>
         </div>
      </div>

      <div class="quangcao">
         <div>
            <img src="../Picture/Banner/ctt3_3_3_310x210.webp" alt="">
         </div>
         <div>
            <img src="../Picture/Banner/maytinh_T3_310x210_1.webp" alt="">
         </div>
         <div>
            <img src="../Picture/Banner/MCBooks_T3_310x210_1.webp" alt="">
         </div>
         <div>
            <img src="../Picture/Banner/NgoaiVan_T3_310x210_1.webp" alt="">
         </div>
      </div>

      <div class="product-container">
         <!-- Lọc sản phẩm -->
         <aside class="filter-section">
            <h3>Lọc sản phẩm</h3>
            <form id="filter-form" method="get" action="trangchu.php">
               <div class="filter-group">
                  <label for="category">Danh mục:</label>
                  <select id="category" name="category">
                     <option value="">Tất cả</option>
                     <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['loaisach_id']); ?>"
                           <?php if (isset($_GET['category']) && $_GET['category'] == $category['loaisach_id']) echo 'selected'; ?>>
                           <?php echo htmlspecialchars($category['ten_loai']); ?>
                        </option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="filter-group">
                  <label for="price-min">Giá từ:</label>
                  <input type="number" id="price-min" name="price_min" min="0" value="<?php echo isset($_GET['price_min']) ? $_GET['price_min'] : ''; ?>" placeholder="0">
               </div>
               <div class="filter-group">
                  <label for="price-max">Đến:</label>
                  <input type="number" id="price-max" name="price_max" min="0" value="<?php echo isset($_GET['price_max']) ? $_GET['price_max'] : ''; ?>" placeholder="1000000">
               </div>
               <div class="filter-group">
                  <label for="sort-order">Sắp xếp:</label>
                  <select id="sort-order" name="sort">
                     <option value="default" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'default') echo 'selected'; ?>>Mặc định</option>
                     <option value="asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'asc') echo 'selected'; ?>>Giá: Từ thấp đến cao</option>
                     <option value="desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'desc') echo 'selected'; ?>>Giá: Từ cao đến thấp</option>
                     <option value="alpha-asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'alpha-asc') echo 'selected'; ?>>Tên: A-Z</option>
                     <option value="alpha-desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'alpha-desc') echo 'selected'; ?>>Tên: Z-A</option>
                  </select>
               </div>
               <div class="filter-buttons">
                  <button type="submit" style="font-weight: bold; font-size: 16px;">Hoàn tất</button>
                  <a href="trangchu.php" class="reset-filter">Reset</a>
               </div>
            </form>
         </aside>

         <!-- Hiển thị sản phẩm -->
         <section class="product-section">
            <h2 style="margin-bottom: 10px;">SẢN PHẨM SÁCH</h2>
            <?php if (empty($products)): ?>
               <div id="no-products-message">Không có sản phẩm phù hợp với bộ lọc. Hãy thử thay đổi tiêu chí tìm kiếm!</div>
            <?php else: ?>
               <div id="product-list">
                  <?php foreach ($products as $row): ?>
                     <div class="product-item" data-id="<?php echo $row['sach_id']; ?>">
                        <?php
                        $file_name = basename($row['hinh_anh']);
                        $image_path = "../Picture/Products/" . $file_name;
                        ?>
                        <a href="chitietsanpham.php?id=<?php echo $row['sach_id']; ?>">
                           <img src="<?php echo $image_path; ?>" alt="<?php echo $row['tieu_de']; ?>">
                           <h3><?php echo $row['tieu_de']; ?></h3>
                        </a>
                        <?php if (!empty($row['tac_gia'])): ?>
                           <p>Tác giả: <?php echo $row['tac_gia']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($row['gia_tien'])): ?>
                           <p style="color: #c22432; font-weight: bold;">Giá: <?php echo number_format($row['gia_tien'], 0, ',', '.'); ?> VND</p>
                        <?php endif; ?>
                        <div class="button-container">
                           <button class="buy-now-btn">Mua</button>
                           <button class="add-to-cart-btn">Thêm</button>
                        </div>
                     </div>
                  <?php endforeach; ?>
               </div>
               <?php if ($pagination['total_pages'] > 1): ?>
                  <?php echo renderPagination($pagination['current_page'], $pagination['total_pages']); ?>
               <?php endif; ?>
            <?php endif; ?>
         </section>
      </div>
   </div>

   <!-- Phần còn lại của trang (login, footer, v.v.) giữ nguyên -->
</body>

</html>

<div class="login-container" id="login-container">
   <div class="tabs">
      <a href="#" class="active" id="login-tab">Đăng nhập</a>
      <a href="#" id="register-tab">Đăng ký</a>
   </div>
   <!-- Form Đăng nhập -->
   <div class="login-form" id="login-form">
      <div id="login-message"></div>
      <form id="login-form-submit" method="post">
         <div class="form-group">
            <label>Số điện thoại/Email</label>
            <input type="text" id="login-username" name="username" placeholder="Nhập số điện thoại hoặc email"
               style="border: 1px solid #ccc; outline: none;" required>
         </div>
         <div class="form-group">
            <label>Mật khẩu</label>
            <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
               <input type="password" id="password" name="password" placeholder="Nhập mật khẩu"
                  style="border: none; outline: none;" required>
               <button type="button" class="show-password" id="togglePassword">Hiện</button>
            </div>
         </div>
         <button type="submit" class="btn btn-primary">Đăng nhập</button>
         <button type="button" class="btn btn-secondary" id="exit">Thoát</button>
      </form>
   </div>

   <!-- Form Đăng ký -->
   <!-- Form Đăng ký -->
   <div class="register-form" id="register-form" style="display: none;">
      <form class="register-form-submit" method="post" id="register-form-submit"> <!-- Thêm thẻ <form> và ID -->
         <div class="form-line">
            <div class="form-group">
               <label>Họ và tên</label>
               <input type="text" id="full-name" placeholder="Nhập họ và tên" style="border: 1px solid #ccc; outline: none; width: 200px">
            </div>

            <div class="form-group">
               <label>Email</label>
               <input type="text" id="email" placeholder="Nhập email" style="border: 1px solid #ccc; outline: none; width: 200px">
            </div>
         </div>

         <div class="form-line">
            <div class="form-group">
               <label>Địa chỉ</label>
               <input type="text" id="address" placeholder="Nhập địa chỉ" style="border: 1px solid #ccc; outline: none; width: 200px">
            </div>

            <div class="form-group">
               <label>Ngày sinh</label>
               <input type="date" id="dob" style="border: 1px solid #ccc; outline: none; width: 178px; margin-right: 20px">
            </div>
         </div>

         <div class="register-info">
            <div class="form-group">
               <label>Tên đăng nhập (Số điện thoại)</label>
               <input type="text" id="username" placeholder="Nhập số điện thoại" style="border: 1px solid #ccc; outline: none;">
            </div>

            <div class="form-group">
               <label>Mật khẩu</label>
               <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
                  <input type="password" id="reg-password" placeholder="Nhập mật khẩu" style="border: none; outline: none;">
                  <button type="button" class="show-password" id="toggleRegPassword">Hiện</button>
               </div>
            </div>

            <div class="form-group">
               <label>Xác nhận mật khẩu</label>
               <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
                  <input type="password" id="confirm-password" placeholder="Xác nhận mật khẩu" style="border: none; outline: none;">
                  <button type="button" class="show-password" id="toggleConfirmPassword">Hiện</button>
               </div>
            </div>
         </div>

         <button type="submit" class="btn btn-primary">Đăng ký</button>
         <button class="btn btn-secondary" id="exit-reg">Thoát</button>
      </form>
   </div>


</div>
<div class="modal-overlay" id="modal"></div>

<!-- Hiển thị thông tin người dùng đã đăng nhập -->
<?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])): ?>
   <div id="user-info-container" style="display: none;">
      <div class="user-info">
         <div class="user-info-header">
            <h1>Thông Tin Cá Nhân</h1>
            <button class="btn-edit-user-info">Chỉnh sửa</button>
         </div>

         <hr style="margin: 20px">

         <div class="user-info-item user-name">
            <h3>Họ và tên:</h3>
            <span><?php echo $_SESSION['user_name']; ?></span>
         </div>

         <div class="user-info-item user-email">
            <h3>Email:</h3>
            <span><?php echo $_SESSION['user_email']; ?></span>
         </div>

         <div class="user-info-item user-dob">
            <h3>Ngày sinh:</h3>
            <span><?php echo $_SESSION['user_dob']; ?></span>
         </div>

         <div class="user-info-item user-phone">
            <h3>Số điện thoại:</h3>
            <span><?php echo $_SESSION['user_phone']; ?></span>
         </div>

         <div class="user-info-item user-address">
            <h3>Địa chỉ:</h3>
            <span><?php echo $_SESSION['user_address']; ?></span>
         </div>

         <div class="user-info-item user-role">
            <h3>Quyền:</h3>
            <span>
               <?php
               echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'KhachHang') ? 'Khách hàng' : 'Khách hàng';
               ?>
            </span>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'Admin'): ?>
               <a href="admin.php" class="btn">Quản trị</a>
            <?php endif; ?>
         </div>

         <a href="login.php?action=logout" class="btn-log-out">Đăng xuất</a>
      </div>
   </div>
<?php endif; ?>


<footer class="footer">
   <div class="footer-container">
      <div class="footer-column">
         <h3>DỊCH VỤ</h3>
         <ul>
            <li>Điều khoản sử dụng</li>
            <li>Chính sách bảo mật</li>
            <li>Liên hệ</li>
            <li>Hệ thống nhà sách</li>
            <li>Tra cứu đơn hàng</li>
         </ul>
      </div>
      <div class="footer-column">
         <h3>HỖ TRỢ</h3>
         <ul>
            <li>Hướng dẫn đặt hàng</li>
            <li>Chính sách đổi trả - hoàn tiền</li>
            <li>Phương thức vận chuyển</li>
            <li>Phương thức thanh toán</li>
            <li>Chính sách khách hàng mua sỉ</li>
            <li>Chính sách khách hàng cho</li>
            <li>Thu viện - Trường học</li>
         </ul>
      </div>
      <div class="footer-column">
         <h3>NHÀ XUẤT BẢN KIM ĐỒNG</h3>
         <p>Giám đốc: Bùi Tuấn Nghĩa</p>
         <p>Địa chỉ: Số 55 Quảng Trưng, Nguyễn Du, Hai Bà Trưng, Hà Nội</p>
         <p>Số điện thoại: (+84) 1900571595</p>
         <p>Email: cskh_online@nxbkimdong.com.vn</p>
      </div>
   </div>

   <script src="../js/account.js"></script>
   <script src="../js/search.js"></script>
   <script src="../js/edit-profile.js"></script>
</footer>

</body>

</html>