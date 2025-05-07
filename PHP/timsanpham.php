<?php
include 'db_connect.php';
include 'pagination.php';

// Khởi tạo biến
$products = [];
$total_pages = 1;
$current_page = 1;
$items_per_page = 6;
$search_term = '';
$category_id = '';
$min_price = '';
$max_price = '';
$sort = 'default';
$categories = [];

// Lấy danh sách loại sách
$category_query = "SELECT loaisach_id, ten_loai FROM LOAISACH";
$category_result = $conn->query($category_query);
if ($category_result) {
   while ($row = $category_result->fetch_assoc()) {
      $categories[] = $row;
   }
}

// Lấy tham số từ URL
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
   $current_page = (int)$_GET['page'];
} else {
   $current_page = 1;
}

if (isset($_GET['category-search']) && !empty($_GET['category-search'])) {
   $category_id = $_GET['category-search'];
}

if (isset($_GET['price-min-search']) && $_GET['price-min-search'] !== '') {
   $min_price = (int)$_GET['price-min-search'];
}

if (isset($_GET['price-max-search']) && $_GET['price-max-search'] !== '') {
   $max_price = (int)$_GET['price-max-search'];
}

if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
   $search_term = trim($_GET['search_term']);
}

if (isset($_GET['sort'])) {
   $sort = $_GET['sort'];
}

// Tính vị trí bắt đầu cho LIMIT trong truy vấn
$start = ($current_page - 1) * $items_per_page;

// Khởi tạo truy vấn cơ bản
$base_query = "SELECT * FROM SACH WHERE trang_thai = 'active'";
$count_query = "SELECT COUNT(*) as total FROM SACH WHERE 1=1";
$params = [];
$param_types = "";

// Thêm điều kiện tìm kiếm theo từ khóa
if (!empty($search_term)) {
   $base_query .= " AND (tieu_de LIKE ? OR tac_gia LIKE ?)";
   $count_query .= " AND (tieu_de LIKE ? OR tac_gia LIKE ?)";
   $search_param = "%" . $search_term . "%";
   $params[] = $search_param;
   $params[] = $search_param;
   $param_types .= "ss";
}

// Thêm điều kiện lọc theo danh mục
if (!empty($category_id)) {
   $base_query .= " AND loaisach_id = ?";
   $count_query .= " AND loaisach_id = ?";
   $params[] = $category_id;
   $param_types .= "s";
}

// Thêm điều kiện lọc theo giá
if ($min_price !== '') {
   $base_query .= " AND gia_tien >= ?";
   $count_query .= " AND gia_tien >= ?";
   $params[] = $min_price;
   $param_types .= "i";
}

if ($max_price !== '') {
   $base_query .= " AND gia_tien <= ?";
   $count_query .= " AND gia_tien <= ?";
   $params[] = $max_price;
   $param_types .= "i";
}

// Thêm sắp xếp
switch ($sort) {
   case 'asc':
      $base_query .= " ORDER BY gia_tien ASC";
      break;
   case 'desc':
      $base_query .= " ORDER BY gia_tien DESC";
      break;
   case 'alpha-asc':
      $base_query .= " ORDER BY tieu_de ASC";
      break;
   case 'alpha-desc':
      $base_query .= " ORDER BY tieu_de DESC";
      break;
   default:
      // Sắp xếp mặc định
      $base_query .= " ORDER BY sach_id DESC";
      break;
}

// Thực hiện truy vấn đếm tổng số sản phẩm
if (!empty($params)) {
   $stmt = $conn->prepare($count_query);
   $stmt->bind_param($param_types, ...$params);
   $stmt->execute();
   $count_result = $stmt->get_result();
   $total_items = $count_result->fetch_assoc()['total'];
} else {
   $count_result = $conn->query($count_query);
   $total_items = $count_result->fetch_assoc()['total'];
}

// Tính tổng số trang
$total_pages = ceil($total_items / $items_per_page);

// Thêm LIMIT vào truy vấn cơ bản
$base_query .= " LIMIT ?, ?";
$params[] = $start;
$params[] = $items_per_page;
$param_types .= "ii";

// Thực hiện truy vấn lấy dữ liệu sản phẩm
if (!empty($params)) {
   $stmt = $conn->prepare($base_query);
   $stmt->bind_param($param_types, ...$params);
   $stmt->execute();
   $result = $stmt->get_result();
} else {
   $result = $conn->query($base_query);
}

// Lấy dữ liệu sản phẩm
if ($result && $result->num_rows > 0) {
   while ($row = $result->fetch_assoc()) {
      $products[] = $row;
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
   <!-- CSS trực tiếp để ghi đè -->
   <style>
      /* CSS cho danh sách sản phẩm */
      #product-list {
         display: grid !important;
         grid-template-columns: repeat(3, 1fr) !important;
         gap: 20px !important;
      }

      /* CSS cho mỗi sản phẩm */
      .product-item {
         padding: 15px !important;
         border: 1px solid #ddd !important;
         border-radius: 5px !important;
         text-align: center !important;
         background-color: #fff !important;
         display: flex !important;
         flex-direction: column !important;
         min-height: 350px !important;
         justify-content: space-between !important;
         box-shadow: none !important;
      }

      .product-item img {
         width: 100% !important;
         height: 280px !important;
         object-fit: contain !important;
         margin-bottom: 5px;
      }

      .product-item h3 {
         height: 20px;
         margin-bottom: 3px;
         object-fit: contain !important;
      }


      .button-container {
         display: flex !important;
         justify-content: space-between !important;
         gap: 10px !important;
         margin-top: auto !important;
         width: 100% !important;
      }

      .buy-now-btn,
      .add-to-cart-btn {
         background-color: #a81f1f !important;
         color: white !important;
         padding: 8px 16px !important;
         font-size: 14px !important;
         border: none !important;
      }

      .buy-now-btn:hover,
      .add-to-cart-btn:hover {
         background-color: white !important;
         color: #a81f1f !important;
         transition: 0.3s;
         border: 1px solid #a81f1f !important;
      }

      .main .product-container {
         display: flex !important;
         flex-direction: row !important;
         gap: 20px !important;
         max-width: 1200px !important;
         margin: 20px auto !important;
         padding: 0 15px !important;
      }

      .main .product-container .filter-section {
         width: 25% !important;
         min-width: 250px !important;
         height: fit-content !important;
         flex-shrink: 0 !important;
         order: 1 !important;
         /* Đảm bảo filter hiển thị trước tiên (bên trái) */
      }

      .main .product-container .product-section {
         width: 75% !important;
         flex-grow: 1 !important;
         order: 2 !important;
         /* Đảm bảo sản phẩm hiển thị bên phải */
      }

      /* Chỉnh sửa responsive khi màn hình nhỏ */
      @media screen and (max-width: 992px) {
         .main .product-container {
            flex-direction: column !important;
         }

         .main .product-container .filter-section,
         .main .product-container .product-section {
            width: 100% !important;
         }
      }
   </style>
</head>

<body>
   <?php include 'header.php'; ?>

   <div class="main">
      <div class="product-container">
         <!-- Thêm phần lọc sản phẩm -->
         <aside class="filter-section">
            <h3 style="margin-bottom: 20px">Lọc Sản Phẩm</h3>
            <form id="filter-form" method="get" action="timsanpham.php">
               <!-- Giữ lại từ khóa tìm kiếm khi lọc -->
               <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>">

               <div class="filter-group">
                  <label for="category">Danh mục:</label>
                  <select id="category" name="category-search">
                     <option value="">Tất cả</option>
                     <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['loaisach_id']); ?>"
                           <?php if (isset($_GET['category-search']) && $_GET['category-search'] == $category['loaisach_id']) echo 'selected'; ?>>
                           <?php echo htmlspecialchars($category['ten_loai']); ?>
                        </option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="filter-group">
                  <label for="price-min">Giá từ:</label>
                  <input type="number" id="price-min" name="price-min-search" min="0"
                     value="<?php echo htmlspecialchars($min_price); ?>" placeholder="0">
               </div>
               <div class="filter-group">
                  <label for="price-max">Đến:</label>
                  <input type="number" id="price-max" name="price-max-search" min="0"
                     value="<?php echo htmlspecialchars($max_price); ?>" placeholder="1000000">
               </div>
               <div class="filter-group">
                  <label for="sort-order">Sắp xếp:</label>
                  <select id="sort-order" name="sort">
                     <option value="default" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'default') echo 'selected'; ?>>Mặc định</option>
                     <option value="asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'asc') echo 'selected'; ?>>Giá: Từ thấp đến cao</option>
                     <option value="desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'desc') echo 'selected'; ?>>Giá: Từ cao đến thấp</option>
                     <option value="alpha-asc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'alpha-asc') echo 'selected'; ?>>Tên: A - Z</option>
                     <option value="alpha-desc" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'alpha-desc') echo 'selected'; ?>>Tên: Z - A</option>
                  </select>
               </div>
               <div class="filter-buttons">
                  <button type="submit">Lọc</button>
                  <a href="timsanpham.php?search_term=<?php echo urlencode($search_term); ?>" class="reset-filter">Reset</a>
               </div>
            </form>
         </aside>

         <section class="product-section">
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
                           <p style="margin-bottom: 5px; margin-top: 5px;">Tác giả: <?php echo $row['tac_gia']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($row['gia_tien'])): ?>
                           <p style="margin-bottom: 15px; color: #a81f1f; font-weight: bold;">Giá: <?php echo number_format($row['gia_tien'], 0, ',', '.'); ?> VND</p>
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

   <?php include 'footer.php'; ?>
   <?php include 'login-register/login-register-form.php'; ?>
   <?php include 'profile-form.php'; ?>

   <script src="../js/search.js"></script>

   <script>
      document.addEventListener('DOMContentLoaded', () => {
         // Thay đổi trực tiếp bằng JavaScript
         const productItems = document.querySelectorAll('.product-item');
         productItems.forEach(item => {
            item.style.padding = '15px';
            item.style.border = '1px solid #ddd';
            item.style.borderRadius = '5px';
            item.style.textAlign = 'center';
            item.style.minHeight = '350px';
            item.style.boxShadow = 'none';
         });

         const productImages = document.querySelectorAll('.product-item img');
         productImages.forEach(img => {
            img.style.height = '200px';
         });

         const buttons = document.querySelectorAll('.buy-now-btn, .add-to-cart-btn');
         buttons.forEach(btn => {
            btn.style.backgroundColor = '#c22432';
            btn.style.color = 'white';
            btn.style.padding = '8px 16px';
         });
      });
   </script>

</body>

</html>