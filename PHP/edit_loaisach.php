<?php
// Kết nối database
include 'db_connect.php';
// Bắt đầu hoặc tiếp tục session để lưu thông báo
session_start();

// Biến lưu thông báo
$message = '';

// Xử lý thêm thể loại mới
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    // Kiểm tra thể loại đã tồn tại chưa
    $check_query = "SELECT * FROM LOAISACH WHERE ten_loai = '$category_name'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['message'] = "Thể loại này đã tồn tại!";
    } else {
        $insert_query = "INSERT INTO LOAISACH (ten_loai, trang_thai) VALUES ('$category_name', 'active')";
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['message'] = "Thêm thể loại thành công!";
        } else {
            $_SESSION['message'] = "Lỗi: " . mysqli_error($conn);
        }
    }

    // Redirect để tránh form resubmission
    header("Location: edit_loaisach.php");
    exit();
}

// Xử lý cập nhật thể loại
if (isset($_POST['update_category'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    $update_query = "UPDATE LOAISACH SET ten_loai = '$category_name' WHERE loaisach_id = '$category_id'";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Cập nhật thể loại thành công!";
    } else {
        $_SESSION['message'] = "Lỗi: " . mysqli_error($conn);
    }

    // Redirect để tránh form resubmission
    header("Location: edit_loaisach.php");
    exit();
}

// Xử lý xóa thể loại
if (isset($_GET['delete'])) {
    $category_id = mysqli_real_escape_string($conn, $_GET['delete']);

    // Kiểm tra xem có sách nào thuộc thể loại này không
    $check_books = "SELECT COUNT(*) as total FROM SACH WHERE loaisach_id = '$category_id' AND trang_thai = 'active'";
    $result_check = mysqli_query($conn, $check_books);
    $count = mysqli_fetch_assoc($result_check)['total'];

    if ($count > 0) {
        $_SESSION['message'] = "Không thể xóa thể loại này vì có $count sách đang sử dụng!";
    } else {
        $delete_query = "UPDATE LOAISACH SET trang_thai = 'deleted' WHERE loaisach_id = '$category_id'";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['message'] = "Xóa thể loại thành công!";
        } else {
            $_SESSION['message'] = "Lỗi: " . mysqli_error($conn);
        }
    }

    // Redirect để tránh form resubmission
    header("Location: edit_loaisach.php");
    exit();
}

// Lấy thông báo từ session (nếu có)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    // Xóa thông báo sau khi lấy để không hiển thị lại
    unset($_SESSION['message']);
}

// Xử lý lọc dữ liệu
$search_keyword = '';
$filter_query = " WHERE trang_thai = 'active'";

if (isset($_GET['search'])) {
    $search_keyword = mysqli_real_escape_string($conn, $_GET['search']);
    $filter_query = " WHERE ten_loai LIKE '%$search_keyword%' AND trang_thai = 'active'";
}

// Xử lý sắp xếp
$sort_field = isset($_GET['sort']) ? $_GET['sort'] : 'loaisach_id';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'asc';

// Đảm bảo sort_field chỉ là một trong các tùy chọn hợp lệ
$valid_sort_fields = ['loaisach_id', 'ten_loai', 'book_count'];
if (!in_array($sort_field, $valid_sort_fields)) {
    $sort_field = 'loaisach_id';
}

// Đảm bảo sort_direction chỉ là 'asc' hoặc 'desc'
if ($sort_direction != 'asc' && $sort_direction != 'desc') {
    $sort_direction = 'asc';
}

// Lấy tất cả thể loại với số lượng sách
$categories_query = "SELECT l.*,
                    (SELECT COUNT(*) FROM SACH s WHERE s.loaisach_id = l.loaisach_id AND s.trang_thai = 'active') as book_count 
                    FROM LOAISACH l" . $filter_query;

// Thêm mệnh đề ORDER BY
if ($sort_field == 'book_count') {
    $categories_query .= " ORDER BY book_count $sort_direction, l.ten_loai ASC";
} else {
    $categories_query .= " ORDER BY $sort_field $sort_direction";
}

$categories_result = mysqli_query($conn, $categories_query);

// Lấy danh sách thể loại không thể xóa (đã có sách liên kết)
$unremovable_query = "SELECT DISTINCT loaisach_id FROM SACH";
$unremovable_result = mysqli_query($conn, $unremovable_query);
$unremovable_categories = [];
while ($row = mysqli_fetch_assoc($unremovable_result)) {
    $unremovable_categories[] = $row['loaisach_id'];
}

// Hàm tạo liên kết sắp xếp
function getSortLink($field, $current_sort_field, $current_sort_direction, $search_keyword)
{
    $direction = ($field == $current_sort_field && $current_sort_direction == 'asc') ? 'desc' : 'asc';
    $link = "edit_loaisach.php?sort=" . $field . "&direction=" . $direction;

    if (!empty($search_keyword)) {
        $link .= "&search=" . urlencode($search_keyword);
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
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T1 Bookstore | Quản lý thể loại</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/admin.css">
    <link rel="stylesheet" href="../CSS/index.css">
    <style>
        .category-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .category-form h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .btn:hover {
            background-color: #45a049;
            transition: 0.3s;
        }

        #cancel-btn {
            background-color: #f44336;
        }

        #cancel-btn:hover {
            background-color: #d32f2f;
        }

        .categories-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .categories-table th,
        .categories-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .categories-table th {
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

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            background-color: #f2f2f2;
        }

        .disabled {
            opacity: 0.6;
            pointer-events: none;
        }

        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-box button {
            padding: 8px 15px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .book-count {
            font-weight: bold;
            color: #000;
        }

        .book-count a {
            color: #000;
            text-decoration: none;
        }

        .book-count a:hover {
            text-decoration: underline;
            transition: 0.3s;
        }

        .zero-books {
            opacity: 0.5;
            font-style: italic;
        }

        .reset-filter {
            display: inline-block;
            margin-left: 10px;
            color: #f44336;
            text-decoration: none;
        }

        .view-books-btn {
            display: inline-block;
            margin-left: 8px;
            padding: 2px 5px;
            background-color: #f2f2f2;
            color: #333;
            border-radius: 3px;
            font-size: 12px;
            text-decoration: none;
        }

        .view-books-btn:hover {
            background-color: #e0e0e0;
            transition: 0.3s;
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
        <div class="menu-item" data-target="tai_khoan.php">Tài khoản</div>
        <div class="menu-item" data-target="admin_orders.php">Đơn hàng</div>
        <div class="menu-item" data-target="admin_warranty.php">Bảo hành</div>
        <div class="menu-item" data-target="logout.php">Đăng xuất</div>

    </div>

    <div class="content">
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="dashboard">
            <div class="dashboard-title">
                <i class="fas fa-tags"></i>
                Quản lý thể loại sách
            </div>

            <!-- Form thêm/sửa thể loại -->
            <div class="category-form">
                <h3 id="form-title">Thêm thể loại mới</h3>
                <form method="POST" id="category-form">
                    <input type="hidden" name="category_id" id="category_id">
                    <div class="form-group">
                        <label for="category_name">Tên thể loại:</label>
                        <input type="text" id="category_name" name="category_name" required>
                    </div>
                    <button style="width: 200px;" type="submit" class="btn" id="submit-btn" name="add_category">Thêm thể loại</button>
                    <button type="button" class="btn" id="cancel-btn" style="width: 200px; display:none; background-color: #ccc;">Hủy</button>
                </form>
            </div>

            <!-- Tìm kiếm/lọc thể loại -->
            <div class="search-box">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Tìm kiếm thể loại..." value="<?php echo $search_keyword; ?>">
                    <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>

                    <!-- Giữ lại các tham số sắp xếp hiện tại -->
                    <input type="hidden" name="sort" value="<?php echo $sort_field; ?>">
                    <input type="hidden" name="direction" value="<?php echo $sort_direction; ?>">

                    <?php if (!empty($search_keyword)): ?>
                        <a href="edit_loaisach.php?sort=<?php echo $sort_field; ?>&direction=<?php echo $sort_direction; ?>" class="reset-filter"><i class="fas fa-times"></i> Xóa bộ lọc</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Danh sách thể loại -->
            <h3>Danh sách thể loại</h3>
            <table class="categories-table">
                <thead>
                    <tr>
                        <th class="sort-header">
                            <a href="<?php echo getSortLink('loaisach_id', $sort_field, $sort_direction, $search_keyword); ?>">
                                ID <?php echo getSortIcon('loaisach_id', $sort_field, $sort_direction); ?>
                            </a>
                        </th>
                        <th class="sort-header">
                            <a href="<?php echo getSortLink('ten_loai', $sort_field, $sort_direction, $search_keyword); ?>">
                                Tên thể loại <?php echo getSortIcon('ten_loai', $sort_field, $sort_direction); ?>
                            </a>
                        </th>
                        <th class="sort-header">
                            <a href="<?php echo getSortLink('book_count', $sort_field, $sort_direction, $search_keyword); ?>">
                                Số lượng sách <?php echo getSortIcon('book_count', $sort_field, $sort_direction); ?>
                            </a>
                        </th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($categories_result) > 0) {
                        while ($category = mysqli_fetch_assoc($categories_result)):
                    ?>
                            <tr>
                                <td><?php echo $category['loaisach_id']; ?></td>
                                <td><?php echo $category['ten_loai']; ?></td>
                                <td class="book-count <?php echo ($category['book_count'] == 0) ? 'zero-books' : ''; ?>">
                                    <?php echo $category['book_count']; ?> cuốn sách
                                </td>
                                <td>
                                    <button class="action-btn edit-btn"
                                        data-id="<?php echo $category['loaisach_id']; ?>"
                                        data-name="<?php echo $category['ten_loai']; ?>">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <a href="javascript:void(0);" class="action-btn delete-btn <?php echo ($category['book_count'] > 0) ? 'disabled' : ''; ?>"
                                        onclick="<?php echo ($category['book_count'] > 0) ? 'alert(\'Không thể xóa thể loại này vì đã có sách liên kết\'); return false;' : 'confirmDelete(' . $category['loaisach_id'] . ')'; ?>"
                                        title="<?php echo ($category['book_count'] > 0) ? 'Không thể xóa thể loại này vì đã có sách liên kết' : 'Xóa thể loại'; ?>">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    } else {
                        ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">Không tìm thấy thể loại nào</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Click event cho menu ở ADMIN
        document.querySelectorAll('.menu-item').forEach(function(item) {
            item.addEventListener('click', function() {
                const menuText = this.textContent.trim();
                if (menuText === 'Đăng xuất') {
                    if (confirm('Bạn có chắc muốn đăng xuất?')) {
                        window.location.href = 'logout.php';
                    }
                } else if (menuText === 'Thể loại') {
                    window.location.href = 'edit_loaisach.php';
                } else if (menuText === 'Sách') {
                    window.location.href = 'edit_sach.php';
                } else if (menuText === 'Tài khoản') {
                    window.location.href = 'edit_taikhoan.php';
                } else if (menuText === 'Trang chủ') {
                    window.location.href = 'trangchu.php';
                } else if (menuText === 'Đơn hàng') {
                    window.location.href = 'admin_orders.php';
                } else if (menuText === 'Bảo hành') {
                    window.location.href = 'admin_warranty.php';
                }
            });
        });

        // Xử lý nút sửa
        document.querySelectorAll('.edit-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                document.getElementById('category_id').value = id;
                document.getElementById('category_name').value = name;

                document.getElementById('form-title').textContent = 'Sửa thể loại';
                document.getElementById('submit-btn').textContent = 'Cập nhật';
                document.getElementById('submit-btn').name = 'update_category';
                document.getElementById('cancel-btn').style.display = 'inline-block';
            });
        });

        // Xử lý nút hủy
        document.getElementById('cancel-btn').addEventListener('click', function() {
            document.getElementById('category-form').reset();
            document.getElementById('form-title').textContent = 'Thêm thể loại mới';
            document.getElementById('submit-btn').textContent = 'Thêm thể loại';
            document.getElementById('submit-btn').name = 'add_category';
            this.style.display = 'none';
        });

        // Hàm xác nhận xóa
        function confirmDelete(id) {
            if (confirm('Bạn có chắc muốn xóa thể loại này?')) {
                window.location.href = 'edit_loaisach.php?delete=' + id;
            }
        }
    </script>
</body>

</html>
