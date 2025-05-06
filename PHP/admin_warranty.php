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
    $donbaohanh_id = intval($_POST['donbaohanh_id']);
    $trang_thai = mysqli_real_escape_string($conn, $_POST['trang_thai']);

    // Kiểm tra trạng thái hợp lệ
    if (!in_array($trang_thai, ['Chua hoan thanh', 'Hoan thanh', 'Tu choi'])) {
        $_SESSION['message'] = "Trạng thái không hợp lệ!";
    } else {
        $sql_update = "UPDATE DONBAOHANH SET trang_thai = ? WHERE donbaohanh_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $trang_thai, $donbaohanh_id);
        
        if ($stmt_update->execute()) {
            $_SESSION['message'] = "Cập nhật trạng thái thành công!";
        } else {
            $_SESSION['message'] = "Lỗi: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
    
    // Redirect để tránh form resubmission
    header("Location: admin_warranty.php");
    exit();
}

// Xử lý tìm kiếm và lọc
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
    $where_clause[] = "dbh.donbaohanh_id LIKE '%$search_query%'";
}
if ($status_filter) {
    $where_clause[] = "dbh.trang_thai = '$status_filter'";
}
$where_sql = $where_clause ? "WHERE " . implode(" AND ", $where_clause) : "";

// Xử lý sắp xếp
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'donbaohanh_id';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'desc';
$valid_sort_fields = ['donbaohanh_id', 'donhang_id', 'ho_ten', 'tieu_de', 'chitietsach_id', 'ngay', 'trang_thai'];
if (!in_array($sort_field, $valid_sort_fields)) {
    $sort_field = 'donbaohanh_id';
}
if ($sort_direction != 'asc' && $sort_direction != 'desc') {
    $sort_direction = 'desc';
}

// Lấy danh sách yêu cầu bảo hành
$sql_warranties = "SELECT dbh.donbaohanh_id, dbh.donhang_id, dbh.chitietsach_id, s.tieu_de, dbh.ly_do, dbh.ngay, dbh.trang_thai, u.sdt, u.ho_ten
                   FROM DONBAOHANH dbh
                   JOIN CHITIETSACH cts ON dbh.chitietsach_id = cts.chitietsach_id
                   JOIN SACH s ON cts.sach_id = s.sach_id
                   JOIN DONHANG dh ON dbh.donhang_id = dh.donhang_id
                   JOIN USER u ON dh.user_id = u.user_id
                   $where_sql
                   ORDER BY $sort_field $sort_direction";
$result_warranties = $conn->query($sql_warranties);

// Hàm tạo liên kết sắp xếp
function getSortLink($field, $current_sort_field, $current_sort_direction, $search_query, $status_filter) {
    $direction = ($field == $current_sort_field && $current_sort_direction == 'asc') ? 'desc' : 'asc';
    $link = "admin_warranty.php?sort=" . $field . "&direction=" . $direction;
    if (!empty($search_query)) {
        $link .= "&search=" . urlencode($search_query);
    }
    if (!empty($status_filter)) {
        $link .= "&status=" . urlencode($status_filter);
    }
    return $link;
}

// Hàm hiển thị mũi tên sắp xếp
function getSortIcon($field, $current_sort_field, $current_sort_direction) {
    if ($field == $current_sort_field) {
        return ($current_sort_direction == 'asc') ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
    }
    return '<i class="fas fa-sort"></i>';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bảo Hành - WEB2 BookStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="../CSS/index.css">
    <style>
        .warranty-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .warranty-table th,
        .warranty-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .warranty-table th {
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

        .action-buttons select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-right: 5px;
        }

        .action-buttons form {
            display: inline-flex;
            align-items: center;
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
        <div class="menu-item" data-target="admin_orders.php">Đơn hàng</div>
        <div class="menu-item active" data-target="admin_warranty.php">Bảo hành</div>
        <div class="menu-item" data-target="logout.php">Đăng xuất</div>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="filter-container">
            <form class="filter-form" method="GET" action="">
                <input type="text" name="search" placeholder="Tìm theo ID yêu cầu..." value="<?php echo htmlspecialchars($search_query); ?>">
                <select name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Chua hoan thanh" <?php if ($status_filter == 'Chua hoan thanh') echo 'selected'; ?>>Chưa hoàn thành</option>
                    <option value="Hoan thanh" <?php if ($status_filter == 'Hoan thanh') echo 'selected'; ?>>Hoàn thành</option>
                    <option value="Tu choi" <?php if ($status_filter == 'Tu choi') echo 'selected'; ?>>Từ chối</option>
                </select>
                <button type="submit"><i class="fas fa-filter"></i> Lọc</button>
                <?php if ($search_query || $status_filter): ?>
                    <a href="admin_warranty.php" class="reset-filter"><i class="fas fa-times"></i> Xóa bộ lọc</a>
                <?php endif; ?>
                <!-- Giữ tham số sắp xếp -->
                <input type="hidden" name="sort" value="<?php echo $sort_field; ?>">
                <input type="hidden" name="direction" value="<?php echo $sort_direction; ?>">
            </form>
        </div>

        <div class="section">
            <h3>Danh Sách Yêu Cầu Bảo Hành</h3>
            <?php if ($result_warranties->num_rows > 0): ?>
                <table class="warranty-table">
                    <thead>
                        <tr>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('donbaohanh_id', $sort_field, $sort_direction, $search_query, $status_filter); ?>">
                                    ID Yêu Cầu <?php echo getSortIcon('donbaohanh_id', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('donhang_id', $sort_field, $sort_direction, $search_query, $status_filter); ?>">
                                    Đơn Hàng <?php echo getSortIcon('donhang_id', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('ho_ten', $sort_field, $sort_direction, $search_query, $status_filter); ?>">
                                    Tên Khách Hàng <?php echo getSortIcon('ho_ten', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('tieu_de', $sort_field, $sort_direction, $search_query, $status_filter); ?>">
                                    Tựa Sách <?php echo getSortIcon('tieu_de', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('chitietsach_id', $sort_field, $sort_direction, $search_query, $status_filter); ?>">
                                    Mã Bản Sao <?php echo getSortIcon('chitietsach_id', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th>Lý Do</th>
                            <th class="sort-header">
                                <a href="<?php echo getSortLink('ngay', $sort_field, $sort_direction, $search_query, $status_filter); ?>">
                                    Ngày <?php echo getSortIcon('ngay', $sort_field, $sort_direction); ?>
                                </a>
                            </th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($warranty = $result_warranties->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($warranty['donbaohanh_id']); ?></td>
                                <td><?php echo htmlspecialchars($warranty['donhang_id']); ?></td>
                                <td><?php echo htmlspecialchars($warranty['ho_ten']); ?></td>
                                <td><?php echo htmlspecialchars($warranty['tieu_de']); ?></td>
                                <td><?php echo htmlspecialchars($warranty['chitietsach_id']); ?></td>
                                <td><?php echo htmlspecialchars($warranty['ly_do']); ?></td>
                                <td><?php echo htmlspecialchars($warranty['ngay']); ?></td>
                                <td class="action-buttons">
                                    <form method="POST" action="">
                                        <input type="hidden" name="donbaohanh_id" value="<?php echo $warranty['donbaohanh_id']; ?>">
                                        <select name="trang_thai">
                                            <option value="Chua hoan thanh" <?php if ($warranty['trang_thai'] == 'Chua hoan thanh') echo 'selected'; ?>>Chưa hoàn thành</option>
                                            <option value="Hoan thanh" <?php if ($warranty['trang_thai'] == 'Hoan thanh') echo 'selected'; ?>>Hoàn thành</option>
                                            <option value="Tu choi" <?php if ($warranty['trang_thai'] == 'Tu choi') echo 'selected'; ?>>Từ chối</option>
                                        </select>
                                        <button type="submit" name="update_status" class="action-btn edit-btn"><i class="fas fa-save"></i> Lưu</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Không tìm thấy yêu cầu bảo hành nào.</p>
            <?php endif; ?>
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
    </script>
</body>
</html>
<?php $conn->close(); ?>
