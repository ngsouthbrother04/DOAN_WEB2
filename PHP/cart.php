<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Vui lòng đăng nhập để xem giỏ hàng.";
    $_SESSION['message_type'] = "error";
    header("Location: trangchu.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Xử lý cập nhật số lượng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    $sach_id = intval($_POST['sach_id']);
    $so_luong = intval($_POST['so_luong']);

    // Kiểm tra tồn kho
    $sql_check = "SELECT so_luong FROM SACH WHERE sach_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $sach_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $stock = $result_check->fetch_assoc()['so_luong'];
    $stmt_check->close();

    if ($so_luong <= $stock && $so_luong > 0) {
        $sql_update = "UPDATE GIOHANG SET so_luong = ? WHERE user_id = ? AND sach_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iii", $so_luong, $user_id, $sach_id);
        $stmt_update->execute();
        $stmt_update->close();
        $_SESSION['message'] = "Cập nhật giỏ hàng thành công.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Số lượng không hợp lệ hoặc vượt quá tồn kho.";
        $_SESSION['message_type'] = "error";
    }
}

// Xử lý xóa sản phẩm
if (isset($_GET['remove'])) {
    $sach_id = intval($_GET['remove']);
    $sql_delete = "DELETE FROM GIOHANG WHERE user_id = ? AND sach_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $user_id, $sach_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    $_SESSION['message'] = "Đã xóa sản phẩm khỏi giỏ hàng.";
    $_SESSION['message_type'] = "success";
    header("Location: cart.php");
    exit;
}

// Lấy danh sách sản phẩm trong giỏ hàng
$sql = "SELECT g.sach_id, s.tieu_de, s.gia_tien, s.hinh_anh, g.so_luong, s.so_luong as stock
        FROM GIOHANG g
        JOIN SACH s ON g.sach_id = s.sach_id
        WHERE g.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['gia_tien'] * $row['so_luong'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - WEB2 BookStore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/index.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="cart-container">
        <h2>Giỏ Hàng</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>
        <?php if (empty($cart_items)): ?>
            <p>Giỏ hàng của bạn đang trống.</p>
        <?php else: ?>
            <table class="cart-table">
                <tr>
                    <th>Sản Phẩm</th>
                    <th>Giá</th>
                    <th>Số Lượng</th>
                    <th>Tổng</th>
                    <th>Thao Tác</th>
                </tr>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <img src="../Picture/Products/<?php echo basename($item['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($item['tieu_de']); ?>">
                            <?php echo htmlspecialchars($item['tieu_de']); ?>
                        </td>
                        <td><?php echo number_format($item['gia_tien'], 0, ',', '.'); ?> VND</td>
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="sach_id" value="<?php echo $item['sach_id']; ?>">
                                <div class="quantity">
                                    <input type="number" name="so_luong" value="<?php echo $item['so_luong']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                                    <button type="submit" name="update_cart">Cập nhật</button>
                                </div>
                            </form>
                        </td>
                        <td><?php echo number_format($item['gia_tien'] * $item['so_luong'], 0, ',', '.'); ?> VND</td>
                        <td>
                            <a href="cart.php?remove=<?php echo $item['sach_id']; ?>" class="remove-btn">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="cart-total">
                <strong>Tổng cộng: <? EFFORTLESS_CHECKOUT.php number_format($total, 0, ',', '.'); ?> VND</strong>
            </div>
            <a href="checkout.php" class="checkout-btn">Thanh Toán</a>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>