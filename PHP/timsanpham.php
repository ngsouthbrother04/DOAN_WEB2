<?php
include 'db_connect.php';
include 'pagination.php';

// Khởi tạo biến
$products = [];
$total_pages = 1;
$current_page = 1;
$items_per_page = 6;
$search_term = '';
$categories = [];

// Lấy danh sách loại sách
$category_query = "SELECT loaisach_id, ten_loai FROM LOAISACH";
$category_result = $conn->query($category_query);
if ($category_result) {
   while ($row = $category_result->fetch_assoc()) {
      $categories[] = $row;
   }
}

// Xử lý tìm kiếm
if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
   $search_term = trim($_GET['search_term']);
   
   // Tính toán phân trang
   if (isset($_GET['page']) && is_numeric($_GET['page'])) {
      $current_page = (int)$_GET['page'];
   } else {
      $current_page = 1;
   }
   
   $start = ($current_page - 1) * $items_per_page;
   
   // Tìm kiếm dựa trên tiêu đề hoặc tác giả
   $count_query = "SELECT COUNT(*) as total FROM SACH 
                  WHERE (tieu_de LIKE ? OR tac_gia LIKE ?)";
                  
   $search_param = "%" . $search_term . "%";
   $stmt = $conn->prepare($count_query);
   $stmt->bind_param("ss", $search_param, $search_param);
   $stmt->execute();
   $count_result = $stmt->get_result();
   $total_items = $count_result->fetch_assoc()['total'];
   $total_pages = ceil($total_items / $items_per_page);
   
   // Truy vấn sản phẩm
   $product_query = "SELECT * FROM SACH 
                     WHERE (tieu_de LIKE ? OR tac_gia LIKE ?) 
                     LIMIT ?, ?";
   $stmt = $conn->prepare($product_query);
   $stmt->bind_param("ssii", $search_param, $search_param, $start, $items_per_page);
   $stmt->execute();
   $result = $stmt->get_result();
   
   while ($row = $result->fetch_assoc()) {
      $products[] = $row;
   }
}

// Lọc theo danh mục nâng cao (nếu có)
if (isset($_GET['category-search']) && !empty($_GET['category-search'])) {
   $category_id = $_GET['category-search'];
   
   // Lọc lại mảng products nếu đã có kết quả tìm kiếm
   if (!empty($products)) {
      $products = array_filter($products, function($product) use ($category_id) {
         return $product['loaisach_id'] == $category_id;
      });
   } else {
      // Nếu chưa có tìm kiếm, thực hiện truy vấn mới
      $count_query = "SELECT COUNT(*) as total FROM SACH WHERE loaisach_id = ?";
      $stmt = $conn->prepare($count_query);
      $stmt->bind_param("s", $category_id);
      $stmt->execute();
      $total_items = $stmt->get_result()->fetch_assoc()['total'];
      $total_pages = ceil($total_items / $items_per_page);
      
      $product_query = "SELECT * FROM SACH WHERE loaisach_id = ? LIMIT ?, ?";
      $stmt = $conn->prepare($product_query);
      $stmt->bind_param("sii", $category_id, $start, $items_per_page);
      $stmt->execute();
      $result = $stmt->get_result();
      
      while ($row = $result->fetch_assoc()) {
         $products[] = $row;
      }
   }
}

// Lọc theo giá (nếu có)
if ((isset($_GET['price-min-search']) && $_GET['price-min-search'] !== '') || 
    (isset($_GET['price-max-search']) && $_GET['price-max-search'] !== '')) {
   
   $min_price = (isset($_GET['price-min-search']) && $_GET['price-min-search'] !== '') ? 
                (int)$_GET['price-min-search'] : 0;
   
   $max_price = (isset($_GET['price-max-search']) && $_GET['price-max-search'] !== '') ? 
                (int)$_GET['price-max-search'] : PHP_INT_MAX;
   
   // Lọc lại mảng products nếu đã có kết quả
   if (!empty($products)) {
      $products = array_filter($products, function($product) use ($min_price, $max_price) {
         $price = (int)$product['gia_tien'];
         return ($price >= $min_price && $price <= $max_price);
      });
   } else {
      // Thực hiện truy vấn mới nếu chưa có kết quả
      $count_query = "SELECT COUNT(*) as total FROM SACH WHERE gia_tien BETWEEN ? AND ?";
      $stmt = $conn->prepare($count_query);
      $stmt->bind_param("ii", $min_price, $max_price);
      $stmt->execute();
      $total_items = $stmt->get_result()->fetch_assoc()['total'];
      $total_pages = ceil($total_items / $items_per_page);
      
      $product_query = "SELECT * FROM SACH WHERE gia_tien BETWEEN ? AND ? LIMIT ?, ?";
      $stmt = $conn->prepare($product_query);
      $stmt->bind_param("iiii", $min_price, $max_price, $start, $items_per_page);
      $stmt->execute();
      $result = $stmt->get_result();
      
      while ($row = $result->fetch_assoc()) {
         $products[] = $row;
      }
   }
}

// Chuẩn bị thông tin phân trang
$pagination = [
   'current_page' => $current_page,
   'total_pages' => $total_pages
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kết quả tìm kiếm</title>
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="../CSS/index.css">
   <link rel="stylesheet" href="../CSS/product.css">
   <link rel="stylesheet" href="../CSS/timsanpham.css">
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
            <form action="timsanpham.php" method="GET" class="search-demo">
               <input type="text" name="search_term" class="search-input" placeholder="Tìm tại đây" value="<?php echo htmlspecialchars($search_term); ?>">
               <button type="submit" class="search-button">
                  <img src="../icon/magnifying-glass-solid.svg" alt="" style="width: 17px; height: 17px; filter: brightness(10);">
               </button>
            </form>
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
         <section class="product-section" style="width: 100%;">
            <h2 style="margin-bottom: 10px;">KẾT QUẢ TÌM KIẾM CHO: "<?php echo htmlspecialchars($search_term); ?>"</h2>
            <?php if (empty($products)): ?>
               <div id="no-products-message">Không tìm thấy sản phẩm phù hợp. Vui lòng thử lại với từ khóa khác!</div>
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

   <script src="../js/account.js"></script>
   <script src="../js/search.js"></script>
</body>
</html>
