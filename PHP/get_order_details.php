<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => []];

if (isset($_POST['donhang_id'])) {
   $donhang_id = intval($_POST['donhang_id']);

   // Truy vấn chi tiết đơn hàng
   $sql = "SELECT s.tieu_de, ctd.gia_tien, ctd.so_luong, (ctd.gia_tien * ctd.so_luong) AS tong_tien
            FROM CHITIETDONHANG ctd
            JOIN SACH s ON ctd.sach_id = s.sach_id
            WHERE ctd.donhang_id = ?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $donhang_id);
   $stmt->execute();
   $result = $stmt->get_result();

   if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
         $response['data'][] = [
            'tieu_de' => htmlspecialchars($row['tieu_de']),
            'gia_tien' => $row['gia_tien'],
            'so_luong' => $row['so_luong'],
            'tong_tien' => $row['tong_tien']
         ];
      }
      $response['success'] = true;
   } else {
      $response['message'] = 'Không tìm thấy chi tiết đơn hàng.';
   }

   $stmt->close();
} else {
   $response['message'] = 'Thiếu ID đơn hàng.';
}

echo json_encode($response);
$conn->close();
