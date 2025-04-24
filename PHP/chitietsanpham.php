<?php
session_start();

// Kiểm tra xem có ID sản phẩm được truyền vào không
if (!isset($_GET['id']) || empty($_GET['id'])) {
   header("Location: trangchu.php");
   exit;
}

// Kết nối database
require_once 'db_connect.php';

// Lấy ID sản phẩm
$sach_id = mysqli_real_escape_string($conn, $_GET['id']);

// Xử lý form khi người dùng thay đổi số lượng hoặc thêm vào giỏ hàng
$quantity = 1; // Số lượng mặc định
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // Kiểm tra đăng nhập
   if (!isset($_SESSION['user_id'])) {
      $message = 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!';
      $message_type = 'error';
   } else {
      // Xử lý số lượng
      if (isset($_POST['quantity'])) {
         $quantity = (int)$_POST['quantity'];
      }

      // Xử lý tăng giảm số lượng
      if (isset($_POST['decrease'])) {
         $quantity = max(1, $quantity - 1);
      } elseif (isset($_POST['increase'])) {
         // Số lượng tối đa sẽ được kiểm tra sau khi lấy thông tin sản phẩm
      } elseif (isset($_POST['add_to_cart'])) {
         // Thêm vào giỏ hàng
         if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
         }

         // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
         $found = false;
         foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $sach_id) {
               $item['quantity'] += $quantity;
               $found = true;
               break;
            }
         }

         if (!$found) {
            $_SESSION['cart'][] = [
               'id' => $sach_id,
               'quantity' => $quantity
            ];
         }

         $message = 'Đã thêm sản phẩm vào giỏ hàng!';
         $message_type = 'success';
      } elseif (isset($_POST['buy_now'])) {
         // Thêm vào giỏ hàng và chuyển đến trang thanh toán
         if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
         }

         // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
         $found = false;
         foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $sach_id) {
               $item['quantity'] += $quantity;
               $found = true;
               break;
            }
         }

         if (!$found) {
            $_SESSION['cart'][] = [
               'id' => $sach_id,
               'quantity' => $quantity
            ];
         }

         // Chuyển hướng đến trang thanh toán
         header("Location: thanhtoan.php");
         exit;
      }
   }
}

// Truy vấn thông tin chi tiết sản phẩm
$query = "SELECT s.*, ls.ten_loai 
          FROM SACH s
          LEFT JOIN LOAISACH ls ON s.loaisach_id = ls.loaisach_id
          WHERE s.sach_id = '$sach_id'";

$result = mysqli_query($conn, $query);

// Kiểm tra xem sản phẩm có tồn tại không
if (mysqli_num_rows($result) == 0) {
   header("Location: trangchu.php");
   exit;
}

$product = mysqli_fetch_assoc($result);

// Kiểm tra số lượng tối đa
if (isset($_POST['increase'])) {
   $quantity = min($product['so_luong'], $quantity + 1);
}

// Đảm bảo số lượng không vượt quá tồn kho
$quantity = min($product['so_luong'], max(1, $quantity));

// Hàm lấy đường dẫn hình ảnh
function getImagePath($image_url)
{
   $file_name = basename($image_url);
   return "../Picture/Products/" . $file_name;
}

// Tiêu đề trang
$page_title = $product['tieu_de'];

// Hàm tính tổng số sản phẩm trong giỏ hàng
function getCartCount()
{
   if (!isset($_SESSION['cart'])) {
      return 0;
   }

   $count = 0;
   foreach ($_SESSION['cart'] as $item) {
      $count += $item['quantity'];
   }

   return $count;
}
?>

<!DOCTYPE html>
<html>

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php echo $page_title; ?> - Chi tiết sản phẩm</title>
   <link rel="stylesheet" href="../CSS/index.css">
   <link rel="stylesheet" href="../CSS/product-detail.css">
   <link rel="stylesheet" href="../CSS/chitietsanpham.css">
   <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>

<body>
   <?php include 'header.php'; ?>

   <div class="product-detail-container">
      <!-- Breadcrumb -->
      <div class="breadcrumb">
         <a href="trangchu.php">Trang chủ</a>
         <span>></span>
         <?php if (!empty($product['ten_loai'])): ?>
            <a href="category.php?category=<?php echo $product['loaisach_id']; ?>"><?php echo $product['ten_loai']; ?></a>
            <span>></span>
         <?php endif; ?>
         <span><?php echo $product['tieu_de']; ?></span>
      </div>

      <!-- Hiển thị thông báo nếu có -->
      <?php if (!empty($message)): ?>
         <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
         </div>
      <?php endif; ?>

      <!-- Chi tiết sản phẩm -->
      <div class="product-detail">
         <div class="product-images">
            <img src="<?php echo getImagePath($product['hinh_anh']); ?>" alt="<?php echo $product['tieu_de']; ?>" class="main-image">
         </div>

         <div class="product-info">
            <h1><?php echo $product['tieu_de']; ?></h1>

            <?php if (!empty($product['ten_loai'])): ?>
               <div class="category">Thể loại: <?php echo $product['ten_loai']; ?></div>
            <?php endif; ?>

            <?php if (!empty($product['tac_gia'])): ?>
               <div class="author">Tác giả: <?php echo $product['tac_gia']; ?></div>
            <?php endif; ?>

            <div class="price">
               <span class="current-price"><?php echo number_format($product['gia_tien'], 0, ',', '.'); ?> VND</span>
            </div>

            <?php if (!empty($product['mo_ta'])): ?>
               <div class="description">
                  <h3>Mô tả sản phẩm:</h3>
                  <p><?php echo nl2br($product['mo_ta']); ?></p>
               </div>
            <?php endif; ?>

            <!-- Form xử lý số lượng và thêm vào giỏ hàng -->
            <form method="post" action="chitietsanpham.php?id=<?php echo $sach_id; ?>" id="product-form">
               <div class="quantity">
                  <label for="quantity">Số lượng:</label>
                  <button type="button" id="decrease-btn" class="decrease-quantity" <?php echo ($quantity <= 1) ? 'disabled' : ''; ?>>-</button>
                  <input type="number" id="quantity" name="quantity" value="<?php echo $quantity; ?>" min="1" max="<?php echo $product['so_luong']; ?>">
                  <button type="button" id="increase-btn" class="increase-quantity" <?php echo ($quantity >= $product['so_luong']) ? 'disabled' : ''; ?>>+</button>
               </div>

               <div class="action-buttons">
                  <button type="submit" name="add_to_cart" class="add-to-cart" <?php echo ($product['so_luong'] <= 0) ? 'disabled' : ''; ?>>Thêm vào giỏ hàng</button>
                  <button type="submit" name="buy_now" class="buy-now" <?php echo ($product['so_luong'] <= 0) ? 'disabled' : ''; ?>>Mua ngay</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <?php include 'login-register/login-register-form.php'; ?>
   <?php include 'profile-form.php'; ?>
   <?php include 'footer.php'; ?>

   <script src="../js/chitietsanpham.js"></script>
</body>

</html>