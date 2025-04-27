<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

if (!isset($_POST['donhang_id']) || !isset($_POST['user_id'])) {
    echo json_encode(['error' => 'Thiếu tham số']);
    exit;
}

$donhang_id = intval($_POST['donhang_id']);
$user_id = intval($_POST['user_id']);

// Lấy chi tiết đơn hàng
$sql_details = "SELECT s.tieu_de, ctdh.so_luong, ctdh.gia_tien
                FROM CHITIETDONHANG ctdh
                JOIN SACH s ON ctdh.sach_id = s.sach_id
                WHERE ctdh.donhang_id = ?";
$stmt_details = $conn->prepare($sql_details);
$stmt_details->bind_param("i", $donhang_id);
$stmt_details->execute();
$result_details = $stmt_details->get_result();
$order_details = [];
while ($row = $result_details->fetch_assoc()) {
    $order_details[] = $row;
}
$stmt_details->close();

// Lấy lịch sử mua hàng (loại trừ đơn hàng hiện tại)
$sql_history = "SELECT donhang_id, ngay_dat, tong_tien, trang_thai
                FROM DONHANG
                WHERE user_id = ? AND donhang_id != ?
                ORDER BY ngay_dat DESC";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("ii", $user_id, $donhang_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
$purchase_history = [];
while ($row = $result_history->fetch_assoc()) {
    $purchase_history[] = $row;
}
$stmt_history->close();

echo json_encode([
    'order_details' => $order_details,
    'purchase_history' => $purchase_history
]);

$conn->close();
?>