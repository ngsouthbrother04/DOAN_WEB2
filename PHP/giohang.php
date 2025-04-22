<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: trangchu.php");
    exit;
}

if (isset($_SESSION['success_message'])) {
    echo "<script>alert('" . $_SESSION['success_message'] . "');</script>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['delete_message'])) {
    echo "<script>alert('" . $_SESSION['delete_message'] . "');</script>";
    unset($_SESSION['delete_message']);
}

require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT s.sach_id, s.hinh_anh, s.tieu_de, g.so_luong, s.gia_tien
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
    <title>Giỏ Hàng</title>
    <link rel="stylesheet" href="../CSS/giohang.css">
</head>

<body>

    <div class="container">
        <?php
        $total = 0;
        if ($result->num_rows > 0) {
            echo "<h2>Giỏ hàng của bạn 
                    <img src='../icon/cart-shopping-solid.svg' alt='' id='cartIcon' width='25px' height='25px'>
                </h2>";
                echo "<form action='update_cart.php' method='POST'>
                        <div class='table-scroll'>
                                <table class='cart-table'>
                                    <thead>
                                        <tr>
                                            <th>Ảnh bìa</th>
                                            <th>Tên sách</th>
                                            <th>Số lượng</th>
                                            <th>Đơn Giá</th>
                                            <th>Gỡ</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                                    while ($row = $result->fetch_assoc()) {
                                        $thanhtien = $row['so_luong'] * $row['gia_tien'];
                                        $total += $thanhtien;
                                        echo "<tr>
                                                <td><img id='book_img' src='{$row['hinh_anh']}' class='cart-item-image' alt='{$row['tieu_de']}'></td>
                                                <td>{$row['tieu_de']}</td>
                                                <td>
                                                    <div class='so_luong'>
                                                        <button type='button' id='decrease-btn' class='decrease-quantity'>-</button>
                                                        <input type='number' id='quantity' name='quantity[{$row['sach_id']}]' value='$row[so_luong]' min='1'>
                                                        <button type='button' id='increase-btn' class='increase-quantity'>+</button>
                                                    </div>
                                                </td>
                                                <td>" . number_format($row['gia_tien'], 0, ',', '.') . " VND</td>
                                                <td>
                                                    <button type='submit' name='delete_item' value='$row[sach_id]'>
                                                        <img id='trash_btn' src='../icon/trash-svgrepo-com.svg' style='height: 25px; width: 25px'>
                                                    </button>
                                                </td>
                                            </tr>";
                                    }
                                    echo "  </tbody>
                                </table>
                            </div>";


                echo "<div class='cart_summary'>
                        <p><strong>Tạm tính:</strong> " . number_format($total, 0, ',', '.') . " VND</p>
                    </div>";

                echo "<div class='cart-buttons'>
                        <button type='submit' class='btn update-btn' name='confirm'>Xác nhận</button>
                        <button type='submit' class='btn delete-btn' name='delete_all'>Xóa tất cả</button>
                        <button type='button' class='btn checkout-btn'>Thanh toán</button>
                        <a href='trangchu.php'><button type='button' class='btn continue-btn'>Tiếp tục mua hàng</button></a>
                    </div>
                    </form>";
        } else {
            echo "<p class='empty-cart-message' style='font-size: 30px'><b>Giỏ hàng của bạn đang trống.</b></p>";
        }

        $conn->close();
        ?>
    </div>
    <script src="../js/giohang.js"></script>
</body>

</html>