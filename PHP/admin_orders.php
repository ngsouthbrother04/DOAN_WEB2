<?php
session_start();
require_once 'db_connect.php';

// Biến lưu thông báo
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $donhang_id = intval($_POST['donhang_id']);
    $trang_thai = mysqli_real_escape_string($conn, $_POST['trang_thai']);

    // Kiểm tra trạng thái hợp lệ
    if (!in_array($trang_thai, ['cho_xac_nhan', 'da_xac_nhan', 'da_duoc_giao', 'da_bi_huy'])) {
        $_SESSION['message'] = "Trạng thái không hợp lệ!";
    } else {
        $sql_update = "UPDATE DONHANG SET trang_thai = ? WHERE donhang_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $trang_thai, $donhang_id);

        if ($stmt_update->execute()) {
            $_SESSION['message'] = "Cập nhật trạng thái thành công!";
        } else {
            $_SESSION['message'] = "Lỗi: " . $stmt_update->error;
        }
        $stmt_update->close();
    }

    // Redirect để tránh form resubmission
    header("Location: admin_orders.php");
    exit();
}

// Xử lý tìm kiếm, lọc trạng thái và lọc thời gian
$search_query = '';
$status_filter = '';
$from_date = '';
$to_date = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
}
if (isset($_GET['status'])) {
    $status_filter = mysqli_real_escape_string($conn, $_GET['status']);
}
if (isset($_GET['from_date']) && !empty($_GET['from_date'])) {
    $from_date = mysqli_real_escape_string($conn, $_GET['from_date']);
}
if (isset($_GET['to_date']) && !empty($_GET['to_date'])) {
    $to_date = mysqli_real_escape_string($conn, $_GET['to_date']);
}

$where_clause = [];
if ($search_query) {
    $where_clause[] = "dh.donhang_id LIKE '%$search_query%'";
}
if ($status_filter) {
    $where_clause[] = "dh.trang_thai = '$status_filter'";
}
if ($from_date && $to_date) {
    $where_clause[] = "dh.ngay_dat BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'";
} elseif ($from_date) {
    $where_clause[] = "dh.ngay_dat >= '$from_date 00:00:00'";
} elseif ($to_date) {
    $where_clause[] = "dh.ngay_dat <= '$to_date 23:59:59'";
}
$where_sql = $where_clause ? "WHERE " . implode(" AND ", $where_clause) : "";

// Xử lý sắp xếp
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'donhang_id';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'desc';
$valid_sort_fields = ['donhang_id', 'ho_ten', 'ngay_dat', 'tong_tien', 'trang_thai'];
if (!in_array($sort_field, $valid_sort_fields)) {
    $sort_field = 'donhang_id';
}
if ($sort_direction != 'asc' && $sort_direction != 'desc') {
    $sort_direction = 'desc';
}

// Lấy danh sách đơn hàng
$sql_orders = "SELECT dh.donhang_id, dh.ngay_dat, dh.tong_tien, dh.trang_thai, u.ho_ten, u.sdt, u.user_id
               FROM DONHANG dh
               JOIN USER u ON dh.user_id = u.user_id
               $where_sql
               ORDER BY $sort_field $sort_direction";
$result_orders = $conn->query($sql_orders);

// Hàm tạo liên kết sắp xếp
function getSortLink($field, $current_sort_field, $current_sort_direction, $search_query, $status_filter, $from_date, $to_date)
{
    $direction = ($field == $current_sort_field && $current_sort_direction == 'asc') ? 'desc' : 'asc';
    $link = "admin_orders.php?sort=" . $field . "&direction=" . $direction;
    if (!empty($search_query)) {
        $link .= "&search=" . urlencode($search_query);
    }
    if (!empty($status_filter)) {
        $link .= "&status=" . urlencode($status_filter);
    }
    if (!empty($from_date)) {
        $link .= "&from_date=" . urlencode($from_date);
    }
    if (!empty($to_date)) {
        $link .= "&to_date=" . urlencode($to_date);
    }
    return $link;
}

// Hàm hiển thị mũi tên sắp xếp
function getSortIcon($field, $current_sort_field, $current_sort_direction)
{
    if ($field == $current_sort_field) {
        return ($current_sort_direction == 'asc') ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
    }
    return '<i class="fas fa-sort"></i>';
}

// Hàm chuyển trạng thái không dấu sang có dấu
function displayStatus($status)
{
    switch ($status) {
        case 'da_duoc_giao':
            return '<span class="order-status-delivered">Đã được giao</span>';
        case 'cho_xac_nhan':
            return '<span class="order-status-pending">Chờ xác nhận</span>';
        case 'da_xac_nhan':
            return '<span class="order-status-confirmed">Đã xác nhận</span>';
        case 'da_bi_huy':
            return '<span class="order-status-canceled">Đã bị hủy</span>';
        default:
            return htmlspecialchars($status);
    }
}
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
    <style>
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-table th,
        .order-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .order-table th {
            background-color: #f2f2f2;
        }

        .sort-header {
            cursor: pointer;
            white-space: nowrap;
        }

        .sort-header a {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sort-header i {
            margin-left: 5px;
        }

        .action-btn {
            padding: 5px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .edit-btn {
            background-color: #2196F3;
            color: white;
        }

        .edit-btn:hover {
            background-color: #1976D2;
            transition: 0.3s;
        }

        .detail-btn {
            background-color: #4CAF50;
            color: white;
        }

        .detail-btn:hover {
            background-color: #45a049;
            transition: 0.3s;
        }

        .action-buttons select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-right: 5px;
        }

        .action-buttons form {
            display: inline-flex;
            align-items: center;
            margin-right: 5px;
        }

        .filter-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .filter-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-form input,
        .filter-form select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .filter-form button {
            padding: 8px 15px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: #1976D2;
            transition: 0.3s;
        }

        .reset-filter {
            color: #f44336;
            text-decoration: none;
            margin-left: 10px;
        }

        .reset-filter:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #f2f2f2;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 5px;
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
        }

        .modal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .modal-table th,
        .modal-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .modal-table th {
            background-color: #f2f2f2;
        }

        .modal-section {
            margin-bottom: 20px;
        }

        .modal-section h4 {
            margin-bottom: 10px;
        }

        /* Trạng thái đơn hàng */
        .order-status-delivered {
            color: #2ecc71;
            font-weight: 500;
        }

        .order-status-pending {
            color: #FFA500;
            font-weight: 500;
        }

        .order-status-confirmed {
            color: #27ae60;
            font-weight: 500;
        }

        .order-status-canceled {
            color: #c22432;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="admin-profile" onclick="window.location.href='admin.php';" style="cursor:pointer;">
            <div class="admin-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>Admin</div>
        </div>
        <div class="menu-item" data-target="edit_loaisach.php">Thể loại</div>
        <div class="menu-item" data-target="edit_sach.php">Sách</div>
        <div class="menu-item" data-target="edit_taikhoan.php">Tài khoản</div>
        <div class="menu-item active" data-target="admin_orders.php">Đơn hàng</div>
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
                    <option value="cho_xac_nhan" <?php if ($status_filter == 'cho_xac_nhan') echo 'selected'; ?>>Chờ xác nhận</option>
                    <option value="da_xac_nhan" <?php if ($status_filter == 'da_xac_nhan') echo 'selected'; ?>>Đã xác nhận</option>
                    <option value="da_duoc_giao" <?php if ($status_filter == 'da_duoc_giao') echo 'selected'; ?>>Đã được giao</option>
                    <option value="da_bi_huy" <?php if ($status_filter == 'da_bi_huy') echo 'selected'; ?>>Đã bị hủy</option>
                </select>
                <input type="date" name="from_date" placeholder="Từ ngày" value="<?php echo htmlspecialchars($from_date); ?>">
                <input type="date" name="to_date" placeholder="Đến ngày" value="<?php echo htmlspecialchars($to_date); ?>">
                <button type="submit"><i class="fas fa-filter"></i> Lọc</button>
                <?php if ($search_query || $status_filter || $from_date || $to_date): ?>
                    <a href="admin_orders.php" class="reset-filter"><i class="fas fa-times"></i> Xóa bộ lọc</a>
                <?php endif; ?>
                <!-- Giữ tham số sắp xếp -->
                <input type="hidden" name="sort" value="<?php echo $sort_field; ?>">
                <input type="hidden" name="direction" value="<?php echo $sort_direction; ?>">
            </form>
        </div>

        <div class="section">
            <h3>Danh Sách Đơn Hàng</h3>
            <?php if ($result_orders->num_rows > 0): ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('donhang_id', $sort_field, $sort_direction, $search_query, $status_filter, $from_date, $to_date); ?>">
                                    ID Đơn Hàng <?php echo getSortIcon('donhang_id', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('ho_ten', $sort_field, $sort_direction, $search_query, $status_filter, $from_date, $to_date); ?>">
                                    Khách Hàng <?php echo getSortIcon('ho_ten', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('ngay_dat', $sort_field, $sort_direction, $search_query, $status_filter, $from_date, $to_date); ?>">
                                    Ngày Đặt <?php echo getSortIcon('ngay_dat', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('tong_tien', $sort_field, $sort_direction, $search_query, $status_filter, $from_date, $to_date); ?>">
                                    Tổng Tiền <?php echo getSortIcon('tong_tien', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('trang_thai', $sort_field, $sort_direction, $search_query, $status_filter, $from_date, $to_date); ?>">
                                    Trạng Thái <?php echo getSortIcon('trang_thai', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $result_orders->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['donhang_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['ho_ten']); ?></td>
                                <td><?php echo htmlspecialchars($order['ngay_dat']); ?></td>
                                <td><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo displayStatus($order['trang_thai']); ?></td>
                                <td class="action-buttons">
                                    <form method="POST" action="">
                                        <input type="hidden" name="donhang_id" value="<?php echo $order['donhang_id']; ?>">
                                        <select name="trang_thai">
                                            <option value="cho_xac_nhan" <?php if ($order['trang_thai'] == 'cho_xac_nhan') echo 'selected'; ?>>Chờ xác nhận</option>
                                            <option value="da_xac_nhan" <?php if ($order['trang_thai'] == 'da_xac_nhan') echo 'selected'; ?>>Đã xác nhận</option>
                                            <option value="da_duoc_giao" <?php if ($order['trang_thai'] == 'da_duoc_giao') echo 'selected'; ?>>Đã được giao</option>
                                            <option value="da_bi_huy" <?php if ($order['trang_thai'] == 'da_bi_huy') echo 'selected'; ?>>Đã bị hủy</option>
                                        </select>
                                        <button type="submit" name="update_status" class="action-btn edit-btn"><i class="fas fa-save"></i> Lưu</button>
                                    </form>
                                    <button class="action-btn detail-btn" onclick="showOrderDetails(<?php echo $order['donhang_id']; ?>, <?php echo $order['user_id']; ?>)"><i class="fas fa-eye"></i> Xem chi tiết</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Không tìm thấy đơn hàng nào.</p>
            <?php endif; ?>
        </div>

        <!-- Modal chi tiết đơn hàng -->
        <div id="orderModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">×</span>
                <div id="orderDetailsContent"></div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', () => {
                const target = item.getAttribute('data-target');
                if (target === 'logout.php') {
                    // Sửa lại logic xử lý đăng xuất
                    if (confirm('Bạn có chắc muốn đăng xuất?')) {
                        window.location.href = target;
                    }
                    // Không làm gì nếu người dùng bấm Cancel
                } else {
                    window.location.href = target;
                }
            });
        });

        function showOrderDetails(orderId, userId) {
            const modal = document.getElementById('orderModal');
            const content = document.getElementById('orderDetailsContent');

            // Gửi AJAX request để lấy chi tiết đơn hàng
            fetch('fetch_order_details.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'donhang_id=' + orderId + '&user_id=' + userId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        content.innerHTML = '<p>' + data.error + '</p>';
                    } else {
                        let html = '<div class="modal-section">';
                        html += '<h4>Chi tiết đơn hàng #' + orderId + '</h4>';
                        html += '<table class="modal-table">';
                        html += '<thead><tr><th>Tựa sách</th><th>Số lượng</th><th>Giá tiền</th><th>Tổng</th></tr></thead>';
                        html += '<tbody>';
                        data.order_details.forEach(item => {
                            html += '<tr>';
                            html += '<td>' + item.tieu_de + '</td>';
                            html += '<td>' + item.so_luong + '</td>';
                            html += '<td>' + Number(item.gia_tien).toLocaleString('vi-VN') + ' VNĐ</td>';
                            html += '<td>' + (item.gia_tien * item.so_luong).toLocaleString('vi-VN') + ' VNĐ</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table>';
                        html += '</div>';

                        html += '<div class="modal-section">';
                        html += '<h4>Lịch sử mua hàng của khách hàng</h4>';
                        if (data.purchase_history.length > 0) {
                            html += '<table class="modal-table">';
                            html += '<thead><tr><th>ID Đơn hàng</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>';
                            html += '<tbody>';
                            data.purchase_history.forEach(order => {
                                let statusClass = '';
                                let statusDisplay = '';
                                switch (order.trang_thai) {
                                    case 'da_duoc_giao':
                                        statusClass = 'order-status-delivered';
                                        statusDisplay = 'Đã được giao';
                                        break;
                                    case 'cho_xac_nhan':
                                        statusClass = 'order-status-pending';
                                        statusDisplay = 'Chờ xác nhận';
                                        break;
                                    case 'da_xac_nhan':
                                        statusClass = 'order-status-confirmed';
                                        statusDisplay = 'Đã xác nhận';
                                        break;
                                    case 'da_bi_huy':
                                        statusClass = 'order-status-canceled';
                                        statusDisplay = 'Đã bị hủy';
                                        break;
                                    default:
                                        statusDisplay = order.trang_thai;
                                }
                                html += '<tr>';
                                html += '<td>' + order.donhang_id + '</td>';
                                html += '<td>' + order.ngay_dat + '</td>';
                                html += '<td>' + Number(order.tong_tien).toLocaleString('vi-VN') + ' VNĐ</td>';
                                html += '<td><span class="' + statusClass + '">' + statusDisplay + '</span></td>';
                                html += '</tr>';
                            });
                            html += '</tbody></table>';
                        } else {
                            html += '<p>Không có lịch sử mua hàng khác.</p>';
                        }
                        html += '</div>';

                        content.innerHTML = html;
                    }
                    modal.style.display = 'block';
                })
                .catch(error => {
                    content.innerHTML = '<p>Lỗi khi tải chi tiết đơn hàng: ' + error + '</p>';
                    modal.style.display = 'block';
                });
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>
