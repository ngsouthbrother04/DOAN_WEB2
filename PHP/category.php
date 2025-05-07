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
$category_query = "SELECT loaisach_id, ten_loai FROM LOAISACH WHERE trang_thai = 'active'";
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
   <?php include 'header.php'; ?>

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
                           <p style="color: #c22432; font-weight: bold;">Giá: <?php echo number_format($row['gia_tien'], 0, ',', '.'); ?> VND</p>
                        <?php endif; ?>
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


   <?php include 'login-register/login-register-form.php'; ?>
   <?php include 'profile-form.php'; ?>
   <?php include 'footer.php'; ?>
</body>

</html>