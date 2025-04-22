<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: trangchu.php");
    exit;
}

require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT s.sach_id, s.tieu_de, g.so_luong, s.gia_tien
    FROM giohang g
    JOIN sach s ON g.sach_id = s.sach_id
    WHERE g.giohang_id = (SELECT user.giohang_id FROM user WHERE user.user_id = $user_id)";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
    <link rel="stylesheet" href="../CSS/thanhtoan.css">
</head>

<body>

    <div class="container">
        <?php
        $total = 0;
        if ($result->num_rows > 0) {
            echo "<h2>Trang Thanh Toán 
                    <img src='../icon/bill.png' alt='' id='billIcon' width='40px' height='40px'>
                </h2>";
                echo "
                        <h3 id='item_list'>Danh Sách Sản Phẩm</h3>
                        <div class='table-scroll'>
                                <table class='items-table'>
                                    <thead>
                                        <tr>
                                            <th>Tên sách</th>
                                            <th>Số lượng</th>
                                            <th>Đơn Giá</th>
                                            <th>Tổng</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                                    while ($row = $result->fetch_assoc()) {
                                        $thanhtien = $row['so_luong'] * $row['gia_tien'];
                                        $total += $thanhtien;
                                        echo "<tr>
                                                <td>{$row['tieu_de']}</td>
                                                <td>{$row['so_luong']}</td>
                                                <td>" . number_format($row['gia_tien'], 0, ',', '.') . " VND</td>
                                                <td>" . number_format($thanhtien, 0, ',', '.') . " VND</td>
                                            </tr>";
                                    }
                                    echo "  </tbody>
                                </table>
                        </div>";


                echo "<div class='items_summary'>
                        <p>Tạm tính: " . number_format($total, 0, ',', '.') . " VND</p>
                    </div>";
                echo "
                    <div class='user_info_checkout'>
                          <h3>Thông tin người nhận</h3>
                            <label for='ho_ten'>Họ và tên:</label>
                            <input type='text' id='ho_ten' name='ho_ten' value='{$_SESSION['user_name']}' disabled>

                            <label for='dia_chi'>Địa chỉ:</label>
                            <input type='text' id='dia_chi' name='dia_chi' value='{$_SESSION['user_address']}' disabled>
                            <label for='dia_chi_khac'>Địa chỉ nhận thay thế (nếu có):</label>
                            <input type='text' id='dia_chi_khac' name='dia_chi_khac' placeholder='Nhập địa chỉ người nhận...'>

                            <label for='sdt'>Số điện thoại:</label>
                            <input type='text' id='sdt' name='sdt' value='{$_SESSION['user_phone']}' disabled>
                            <label for='sdt_khac'>Số điện thoại liên lạc (nếu người nhận khác với người dùng):</label>
                            <input type='text' id='sdt_khac' name='sdt_khac' placeholder='SĐT người nhận để shipper liên hệ...'>

                            <label for='ghi_chu'>Ghi chú:</label>
                            <textarea id='ghi_chu' name='ghi_chu' rows='3' placeholder='Ghi chú cho người giao hàng (nếu có)...'></textarea>           
                    </div>
                ";
                echo "<div class='checkout-section'>
                        <h3>Phương thức thanh toán</h3>
                        <div class='payment-method'>
                            <label><input type='radio' name='payment' value='cod' checked>🛵 Thanh toán khi nhận hàng (COD)</label><br>
                            <label><input type='radio' name='payment' value='bank'>💳  Chuyển khoản </label><br>
                        </div>
                        <div class='checkout-buttons'>
                            <button type='submit' class='btn confirm-btn'>Xác nhận đặt hàng</button>
                            <a href='trangchu.php'><button type='button' class='btn back-btn'>Quay lại mua hàng</button></a>
                        </div>

                    </div>";
        } else {
            echo "<p class='empty-cart-message' style='font-size: 30px'><b>Giỏ hàng của bạn đang trống.</b></p>";
        }

        $conn->close();
        ?>
    </div>

    <div id="order-summary-modal" class="modal">
        <div class="modal-content">
            <h2>Xác nhận đơn hàng</h2>
            <div id="order-summary-details">
                <!-- Dữ liệu sản phẩm, địa chỉ, sdt, thanh toán sẽ được điền ở đây bằng JS -->
            </div>
            <div class="modal-buttons">
                <button id="final-confirm" class="btn confirm-btn">Xác nhận đặt hàng</button>
                <button id="cancel-summary" class="btn cancel-btn">Hủy</button>
            </div>
        </div>
    </div>

    <script src='../js/thanhtoan.js'></script>
</body>

</html>