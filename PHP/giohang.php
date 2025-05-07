<?php
session_start();
require_once 'db_connect.php';

// Hàm lấy đường dẫn hình ảnh
function getImagePath($image_url)
{
    $file_name = basename($image_url);
    return "../Picture/Products/" . $file_name;
}

// Xử lý cập nhật giỏ hàng
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   if (isset($_POST['update_cart'])) {
       // Kiểm tra giỏ hàng tồn tại
       if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
           $message = 'Giỏ hàng của bạn đang trống!';
           $message_type = 'error';
       } else {
           $updated_cart = [];
           // Duyệt qua giỏ hàng hiện tại
           foreach ($_SESSION['cart'] as $item) {
               $sach_id = $item['id'];
               // Kiểm tra xem số lượng mới có được gửi không
               if (isset($_POST['quantity'][$sach_id])) {
                   $quantity = (int)$_POST['quantity'][$sach_id];
                   
                   // Kiểm tra số lượng hợp lệ
                   if ($quantity <= 0) {
                       // Bỏ qua mục này (không thêm vào giỏ hàng mới)
                       continue;
                   }

                   // Xác thực số lượng so với tồn kho
                   $query = "SELECT so_luong FROM SACH WHERE sach_id = '" . mysqli_real_escape_string($conn, $sach_id) . "'";
                   $result = mysqli_query($conn, $query);
                   if ($result && mysqli_num_rows($result) > 0) {
                       $product = mysqli_fetch_assoc($result);
                       $max_quantity = $product['so_luong'];
                       // Cập nhật số lượng, không vượt quá tồn kho
                       $quantity = min($quantity, $max_quantity);
                       // Thêm mục vào giỏ hàng mới
                       $updated_cart[] = [
                           'id' => $sach_id,
                           'quantity' => $quantity
                       ];
                   }
               } else {
                   // Nếu không có số lượng mới, giữ nguyên mục này
                   $updated_cart[] = $item;
               }
           }

           // Cập nhật giỏ hàng
           $_SESSION['cart'] = $updated_cart;
           if (empty($_SESSION['cart'])) {
               unset($_SESSION['cart']);
               $message = 'Giỏ hàng của bạn đã trống sau khi cập nhật!';
               $message_type = 'warning';
           } else {
               $message = 'Đã cập nhật giỏ hàng!';
               $message_type = 'success';
           }
       }
   } elseif (isset($_POST['remove_item'])) {
       $sach_id = $_POST['remove_item'];
       $updated_cart = [];
       foreach ($_SESSION['cart'] as $item) {
           if ($item['id'] != $sach_id) {
               $updated_cart[] = $item;
           }
       }
       $_SESSION['cart'] = $updated_cart;
       if (empty($_SESSION['cart'])) {
           unset($_SESSION['cart']);
           $message = 'Giỏ hàng của bạn đã trống!';
           $message_type = 'warning';
       } else {
           $message = 'Đã xóa sản phẩm khỏi giỏ hàng!';
           $message_type = 'success';
       }
   }
}

// Lấy thông tin sản phẩm trong giỏ hàng
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $sach_ids = array_column($_SESSION['cart'], 'id');
    $sach_ids = array_map(function ($id) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $id) . "'";
    }, $sach_ids);
    $sach_ids_str = implode(',', $sach_ids);

    $query = "SELECT sach_id, tieu_de, gia_tien, hinh_anh, so_luong FROM SACH WHERE sach_id IN ($sach_ids_str)";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        foreach ($_SESSION['cart'] as $item) {
            if ($item['id'] == $row['sach_id']) {
                $cart_items[] = [
                    'id' => $row['sach_id'],
                    'tieu_de' => $row['tieu_de'],
                    'gia_tien' => $row['gia_tien'],
                    'hinh_anh' => $row['hinh_anh'],
                    'so_luong' => $item['quantity'],
                    'max_so_luong' => $row['so_luong'],
                    'thanh_tien' => $row['gia_tien'] * $item['quantity']
                ];
                $total_price += $row['gia_tien'] * $item['quantity'];
            }
        }
    }
}

$page_title = "Giỏ Hàng";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - WEB2 BookStore</title>
    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/giohang.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="cart-container">
        <h1>Giỏ Hàng</h1>

        <!-- Hiển thị thông báo nếu có -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <p class="empty-cart">Giỏ hàng của bạn đang trống.</p>
        <?php else: ?>
            <form method="post" action="giohang.php">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
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
                                <td>
                                    <div class="quantity">
                                        <button type="button" class="decrease-quantity" <?php echo ($item['so_luong'] <= 1) ? 'disabled' : ''; ?>>-</button>
                                        <input type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['so_luong']; ?>" min="1" max="<?php echo $item['max_so_luong']; ?>">
                                        <button type="button" class="increase-quantity" <?php echo ($item['so_luong'] >= $item['max_so_luong']) ? 'disabled' : ''; ?>>+</button>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['thanh_tien'], 0, ',', '.'); ?> VND</td>
                                <td>
                                    <button type="submit" name="remove_item" value="<?php echo $item['id']; ?>" class="remove-item">Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="update-cart">Cập nhật</button>
                    <a href="thanhtoan.php" class="checkout-button">Thanh toán</a>
                </div>
            </form>

            <div class="cart-total">
                <h3>Tổng cộng: <span><?php echo number_format($total_price, 0, ',', '.'); ?> VND</span></h3>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'login-register/login-register-form.php'; ?>
    <?php include 'profile-form.php'; ?>
    <?php include 'footer.php'; ?>

    <script src="../js/giohang.js"></script>
</body>
</html>