<?php
session_start();
require_once 'db_connect.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_POST['sach_id']) || !isset($_POST['quantity'])) {
    $response['message'] = 'Dữ liệu không hợp lệ';
    echo json_encode($response);
    exit;
}

$sach_id = mysqli_real_escape_string($conn, $_POST['sach_id']);
$quantity = (int)$_POST['quantity'];

// Kiểm tra sản phẩm tồn tại và số lượng hợp lệ
$query = "SELECT so_luong FROM SACH WHERE sach_id = '$sach_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $response['message'] = 'Sản phẩm không tồn tại';
    echo json_encode($response);
    exit;
}

$product = mysqli_fetch_assoc($result);
if ($quantity > $product['so_luong']) {
    $response['message'] = 'Số lượng vượt quá tồn kho';
    echo json_encode($response);
    exit;
}

// Thêm vào giỏ hàng
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

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

// Tính tổng số lượng trong giỏ hàng
$total_cart_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_cart_items += $item['quantity'];
}

$response['success'] = true;
$response['message'] = 'Đã thêm sản phẩm vào giỏ hàng';
$response['cart_count'] = $total_cart_items;

echo json_encode($response);
exit;