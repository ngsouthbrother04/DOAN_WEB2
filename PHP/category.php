<?php
include 'db_connect.php';
include 'pagination.php';
include 'product_filter.php';

// Kiểm tra xem có tham số category được truyền vào không
if (!isset($_GET['category']) || empty($_GET['category'])) {
   // Nếu không có, chuyển hướng về trang chủ
   header('Location: trangchu.php');
   exit;
}

$category_id = $_GET['category'];

// Lấy thông tin của danh mục hiện tại
$current_category_query = "SELECT ten_loai FROM LOAISACH WHERE loaisach_id = ?";
$stmt = $conn->prepare($current_category_query);
$stmt->bind_param("s", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$current_category = $result->fetch_assoc();

if (!$current_category) {
   // Nếu không tìm thấy danh mục, chuyển hướng về trang chủ
   header('Location: trangchu.php');
   exit;
}

// Lấy danh sách tất cả các danh mục cho dropdown menu
$category_query = "SELECT loaisach_id, ten_loai FROM LOAISACH";
$category_result = $conn->query($category_query);
$categories = [];
if ($category_result) {
   while ($row = $category_result->fetch_assoc()) {
      $categories[] = $row;
   }
}

// Lấy sản phẩm theo danh mục với phân trang
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
   <title><?php echo htmlspecialchars($current_category['ten_loai']); ?> - Nhà sách</title>
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
         <div class="userbutton">
            <img src="../icon/user-regular.svg" alt="" id="userbutton">
            <p>Tài khoản</p>
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
   <div class="main">
      <div class="product-container">
         <!-- Lọc sản phẩm -->
         <aside class="filter-section">
            <h3>Lọc sản phẩm</h3>
            <form id="filter-form" method="get" action="category.php">
               <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_id); ?>">
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
                  <button type="submit">Lọc</button>
                  <a href="category.php?category=<?php echo htmlspecialchars($category_id); ?>" class="reset-filter">Reset</a>
               </div>
            </form>
         </aside>

         <!-- Hiển thị sản phẩm -->
         <section class="product-section">
            <h2 style="margin-bottom: 10px;"><?php echo htmlspecialchars($current_category['ten_loai']); ?></h2>
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
                           <p>Giá: <?php echo number_format($row['gia_tien'], 0, ',', '.'); ?> VND</p>
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

   <div class="login-container" id="login-container">
      <div class="tabs">
         <a href="#" class="active" id="login-tab">Đăng nhập</a>
         <a href="#" id="register-tab">Đăng ký</a>
      </div>
      <!-- Form Đăng nhập -->
      <div class="login-form" id="login-form">
         <div class="form-group">
            <label>Số điện thoại/Email</label>
            <input type="text" placeholder="Nhập số điện thoại hoặc email"
               style="border: 1px solid #ccc; outline: none;">
         </div>
         <div class="form-group">
            <label>Mật khẩu</label>
            <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
               <input type="password" id="password" placeholder="Nhập mật khẩu"
                  style="border: none; outline: none;">
               <button type="button" class="show-password" id="togglePassword">Hiện</button>
            </div>
         </div>
         <button class="btn btn-primary">Đăng nhập</button>
         <button class="btn btn-secondary" id="exit">Thoát</button>
      </div>
      <!-- Form Đăng ký -->
      <div class="register-form" id="register-form" style="display: none;">
         <div class="form-group">
            <label>Tên đăng nhập (Số điện thoại/Email)</label>
            <input type="text" placeholder="Nhập số điện thoại hoặc email"
               style="border: 1px solid #ccc; outline: none;">
         </div>

         <div class="form-group">
            <label>Mật khẩu</label>
            <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
               <input type="password" id="reg-password" placeholder="Nhập mật khẩu"
                  style="border: none; outline: none;">
               <button type="button" class="show-password" id="toggleRegPassword">Hiện</button>
            </div>
         </div>
         <div class="form-group">
            <label>Xác nhận mật khẩu</label>
            <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
               <input type="password" id="confirm-password" placeholder="Xác nhận mật khẩu"
                  style="border: none; outline: none;">
               <button type="button" class="show-password" id="toggleConfirmPassword">Hiện</button>
            </div>
         </div>
         <button class="btn btn-primary">Đăng ký</button>
         <button class="btn btn-secondary" id="exit-reg">Thoát</button>
      </div>
   </div>
   <div class="modal-overlay" id="modal"></div>

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
   </footer>
</body>

</html>