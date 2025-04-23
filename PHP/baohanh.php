<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Yêu Cầu Bảo Hành</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h3 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error { color: red; }
        .success { color: green; }
        textarea { width: 100%; height: 100px; }
        .section { margin-bottom: 30px; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Yêu Cầu Bảo Hành Sách</h2>

    <?php
    // Khởi tạo session
    session_start();

    // Giả định user_id từ session (có thể thay đổi theo hệ thống đăng nhập của bạn)
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2; // Ví dụ: user_id = 2 (Nguyen Van A)

    // Thông tin kết nối cơ sở dữ liệu
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "WEB2_BookStore";

    // Kết nối cơ sở dữ liệu
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("<p class='error'>Kết nối thất bại: " . $conn->connect_error . "</p>");
    }

    // Hiển thị danh sách đơn hàng
    $sql_orders = "SELECT donhang_id, ngay_dat, tong_tien, trang_thai
                   FROM DONHANG
                   WHERE user_id = ?
                   ORDER BY ngay_dat DESC";
    $stmt_orders = $conn->prepare($sql_orders);
    $stmt_orders->bind_param("i", $user_id);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();

    echo "<div class='section'>";
    echo "<h3>Danh Sách Đơn Hàng</h3>";
    if ($result_orders->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID Đơn Hàng</th><th>Ngày Đặt</th><th>Tổng Tiền</th><th>Trạng Thái</th><th>Chi Tiết</th></tr>";
        while ($order = $result_orders->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($order['donhang_id']) . "</td>";
            echo "<td>" . htmlspecialchars($order['ngay_dat']) . "</td>";
            echo "<td>" . number_format($order['tong_tien'], 0, ',', '.') . " VNĐ</td>";
            echo "<td>" . htmlspecialchars($order['trang_thai']) . "</td>";
            echo "<td><a href='?donhang_id=" . $order['donhang_id'] . "'>Xem bản sao</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>Bạn chưa có đơn hàng nào.</p>";
    }
    $stmt_orders->close();
    echo "</div>";

    // Hiển thị danh sách bản sao sách trong đơn hàng được chọn
    if (isset($_GET['donhang_id'])) {
        $donhang_id = intval($_GET['donhang_id']);

        // Kiểm tra xem đơn hàng thuộc về người dùng
        $sql_check = "SELECT user_id FROM DONHANG WHERE donhang_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $donhang_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows == 0 || $result_check->fetch_assoc()['user_id'] != $user_id) {
            echo "<p class='error'>Đơn hàng không hợp lệ hoặc không thuộc về bạn.</p>";
            $stmt_check->close();
        } else {
            $stmt_check->close();

            // Truy vấn danh sách bản sao
            $sql_copies = "SELECT cts.chitietsach_id, s.tieu_de
                           FROM CHITIETSACH cts
                           JOIN CHITIETDONHANG ctd ON cts.chitietdonhang_id = ctd.chitiet_id
                           JOIN SACH s ON cts.sach_id = s.sach_id
                           WHERE ctd.donhang_id = ?";
            $stmt_copies = $conn->prepare($sql_copies);
            $stmt_copies->bind_param("i", $donhang_id);
            $stmt_copies->execute();
            $result_copies = $stmt_copies->get_result();

            echo "<div class='section'>";
            echo "<h3>Chọn Bản Sao Để Bảo Hành (Đơn Hàng #$donhang_id)</h3>";
            if ($result_copies->num_rows > 0) {
                echo "<form action='submit_warranty.php' method='POST'>";
                echo "<input type='hidden' name='donhang_id' value='$donhang_id'>";
                echo "<table>";
                echo "<tr><th>Chọn</th><th>Tựa Sách</th><th>Mã Bản Sao</th></tr>";
                while ($copy = $result_copies->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='radio' name='chitietsach_id' value='" . htmlspecialchars($copy['chitietsach_id']) . "' required></td>";
                    echo "<td>" . htmlspecialchars($copy['tieu_de']) . "</td>";
                    echo "<td>" . htmlspecialchars($copy['chitietsach_id']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<label for='ly_do'>Lý do bảo hành:</label><br>";
                echo "<textarea name='ly_do' required></textarea><br><br>";
                echo "<input type='submit' value='Gửi Yêu Cầu Bảo Hành'>";
                echo "</form>";
            } else {
                echo "<p class='error'>Không tìm thấy bản sao nào trong đơn hàng này.</p>";
            }
            $stmt_copies->close();
            echo "</div>";
        }
    }

    // Hiển thị lịch sử yêu cầu bảo hành
    $sql_warranties = "SELECT dbh.donbaohanh_id, dbh.donhang_id, dbh.chitietsach_id, s.tieu_de, dbh.ly_do, dbh.ngay, dbh.trang_thai
                       FROM DONBAOHANH dbh
                       JOIN CHITIETSACH cts ON dbh.chitietsach_id = cts.chitietsach_id
                       JOIN SACH s ON cts.sach_id = s.sach_id
                       JOIN DONHANG dh ON dbh.donhang_id = dh.donhang_id
                       WHERE dh.user_id = ?
                       ORDER BY dbh.ngay DESC";
    $stmt_warranties = $conn->prepare($sql_warranties);
    $stmt_warranties->bind_param("i", $user_id);
    $stmt_warranties->execute();
    $result_warranties = $stmt_warranties->get_result();

    echo "<div class='section'>";
    echo "<h3>Lịch Sử Yêu Cầu Bảo Hành</h3>";
    if ($result_warranties->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID Yêu Cầu</th><th>Đơn Hàng</th><th>Tựa Sách</th><th>Mã Bản Sao</th><th>Lý Do</th><th>Ngày</th><th>Trạng Thái</th></tr>";
        while ($warranty = $result_warranties->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($warranty['donbaohanh_id']) . "</td>";
            echo "<td>" . htmlspecialchars($warranty['donhang_id']) . "</td>";
            echo "<td>" . htmlspecialchars($warranty['tieu_de']) . "</td>";
            echo "<td>" . htmlspecialchars($warranty['chitietsach_id']) . "</td>";
            echo "<td>" . htmlspecialchars($warranty['ly_do']) . "</td>";
            echo "<td>" . htmlspecialchars($warranty['ngay']) . "</td>";
            echo "<td>" . htmlspecialchars($warranty['trang_thai']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Chưa có yêu cầu bảo hành nào.</p>";
    }
    $stmt_warranties->close();

    // Đóng kết nối
    $conn->close();
    ?>
</body>
</html>