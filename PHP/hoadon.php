<?php
session_start();
require_once 'db_connect.php';


// Biến lưu thông báo
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $donhang_id = intval($_POST['donhang_id']);
    $trang_thai = mysqli_real_escape_string($conn, $_POST['trang_thai']);

    $sql_update = "UPDATE DONHANG SET trang_thai = ? WHERE donhang_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $trang_thai, $donhang_id);
    
    if ($stmt_update->execute()) {
        $message = "Cập nhật trạng thái đơn hàng thành công!";
    } else {
        $message = "Lỗi: " . $stmt_update->error;
    }
    $stmt_update->close();
}

// Xử lý tìm kiếm
$search_query = '';
$status_filter = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
}
if (isset($_GET['status'])) {
    $status_filter = mysqli_real_escape_string($conn, $_GET['status']);
}

$where_clause = [];
if ($search_query) {
    $where_clause[] = "dh.donhang_id LIKE '%$search_query%'";
}
if ($status_filter) {
    $where_clause[] = "dh.trang_thai = '$status_filter'";
}
$where_sql = $where_clause ? "WHERE " . implode(" AND ", $where_clause) : "";

// Lấy danh sách đơn hàng
$sql_orders = "SELECT dh.donhang_id, dh.ngay_dat, dh.tong_tien, dh.trang_thai, u.sdt
               FROM DONHANG dh
               JOIN USER u ON dh.user_id = u.user_id
               $where_sql
               ORDER BY dh.ngay_dat DESC";
$result_orders = $conn->query($sql_orders);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - WEB2 BookStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="../CSS/index.css">
</head>
<body>
    <div class="sidebar">
        <div class="admin-profile" onclick="window.location.href='admin.php';" style="cursor:pointer;">
            <div class="admin-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>Admin</div>
        </div>
        <div class="menu-item" data-target="admin.php">Tổng quan</div>
        <div class="menu-item" data-target="edit_loaisach.php">Thể loại</div>
        <div class="menu-item" data-target="edit_sach.php">Sách</div>
        <div class="menu-item" data-target="edit_taikhoan.php">Tài khoản</div>
        <div class="menu-item active" data-target="hoa_don.php">Hóa đơn</div>
        <div class="menu-item" data-target="admin_warranty.php">Bảo hành</div>
        <div class="menu-item" data-target="logout.php">Đăng xuất</div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="filter-container">
            <form class="filter-form" method="GET" action="">
                <input type="text" name="search" placeholder="Tìm theo ID đơn hàng..." value="<?php echo htmlspecialchars($search_query); ?>">
                <select name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Dang xu ly" <?php if ($status_filter == 'Dang xu ly') echo 'selected'; ?>>Đang xử lý</option>
                    <option value="Da giao" <?php if ($status_filter == 'Da giao') echo 'selected'; ?>>Đã giao</option>
                    <option value="Da huy" <?php if ($status_filter == 'Da huy') echo 'selected'; ?>>Đã hủy</option>
                </select>
                <button type="submit"><i class="fas fa-filter"></i> Lọc</button>
                <?php if ($search_query || $status_filter): ?>
                    <a href="hoa_don.php" class="reset-filter"><i class="fas fa-times"></i> Xóa bộ lọc</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section">
            <h3>Danh Sách Đơn Hàng</h3>
            <?php if ($result_orders->num_rows > 0): ?>
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID Đơn Hàng</th>
                            <th>SĐT Khách</th>
                            <th>Ngày Đặt</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $result_orders->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['donhang_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['sdt']); ?></td>
                                <td><?php echo htmlspecialchars($order['ngay_dat']); ?></td>
                                <td><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> VND</td>
                                <td><?php echo htmlspecialchars($order['trang_thai']); ?></td>
                                <td class="action-buttons">
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="donhang_id" value="<?php echo $order['donhang_id']; ?>">
                                        <select name="trang_thai">
                                            <option value="Dang xu ly" <?php if ($order['trang_thai'] == ' разом xu ly') echo 'selected'; ?>>Đang xử lý</option>
                                            <option value="Da giao" <?php if ($order['trang_thai'] == 'Da giao') echo 'selected'; ?>>Đã giao</option>
                                            <option value="Da huy" <?php if ($order['trang_thai'] == 'Da huy') echo 'selected'; ?>>Đã hủy</option>
                                        </select>
                                        <button type="submit" name="update_status" class="edit-btn"><i class="fas fa-save"></i></button>
                                    </form>
                                    <a href="?details=<?php echo $order['donhang_id']; ?>" class="edit-btn"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Không tìm thấy đơn hàng nào.</p>
            <?php endif; ?>

            <?php if (isset($_GET['details'])): ?>
                <?php
                $donhang_id = intval($_GET['details']);
                $sql_details = "SELECT ctd.chitiet_id, s.tieu_de, ctd.so_luong, ctd.gia, cts.chitietsach_id
                                FROM CHITIETDONHANG ctd
                                JOIN SACH s ON ctd.sach_id = s.sach_id
                                JOIN CHITIETSACH cts ON ctd.chitiet_id = cts.chitietdonhang_id
                                WHERE ctd.donhang_id = ?";
                $stmt_details = $conn->prepare($sql_details);
                $stmt_details->bind_param("i", $donhang_id);
                $stmt_details->execute();
                $result_details = $stmt_details->get_result();
                ?>
                <div class="section">
                    <h3>Chi Tiết Đơn Hàng #<?php echo $donhang_id; ?></h3>
                    <?php if ($result_details->num_rows > 0): ?>
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>Tựa Sách</th>
                                    <th>Số Lượng</th>
                                    <th>Giá</th>
                                    <th>Mã Bản Sao</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($detail = $result_details->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($detail['tieu_de']); ?></td>
                                        <td><?php echo htmlspecialchars($detail['so_luong']); ?></td>
                                        <td><?php echo number_format($detail['gia'], 0, ',', '.'); ?> VND</td>
                                        <td><?php echo htmlspecialchars($detail['chitietsach_id']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Không tìm thấy chi tiết đơn hàng.</p>
                    <?php endif; ?>
                    <?php $stmt_details->close(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', () => {
                const target = item.getAttribute('data-target');
                if (target === 'logout.php' && confirm('Bạn có chắc muốn đăng xuất?')) {
                    window.location.href = target;
                } else {
                    window.location.href = target;
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>