<?php
// Sử dụng file kết nối database đã có sẵn
require_once 'db_connect.php';

// Bắt đầu session nếu chưa bắt đầu
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// Sửa đường dẫn ảnh cho các sản phẩm cũ
// Comment lại sau khi đã chạy 1 lần
$fix_image_path_sql = "UPDATE SACH SET hinh_anh = REPLACE(hinh_anh, '../Picture/Products/', 'Picture/Products/') WHERE hinh_anh LIKE '../Picture/Products/%'";
mysqli_query($conn, $fix_image_path_sql);

// Xử lý thêm sách mới
if (isset($_POST['them_sach'])) {
   $tieu_de = $_POST['tieu_de'];
   $tac_gia = $_POST['tac_gia'];
   $gia_tien = $_POST['gia_tien'];
   $so_luong = $_POST['so_luong'];
   $loaisach_id = $_POST['loaisach_id'];
   $mo_ta = $_POST['mo_ta'];
   $nha_xuat_ban = $_POST['nha_xuat_ban'];

   // Xử lý upload hình ảnh
   $hinh_anh = '';
   if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
      // Đảm bảo thư mục tồn tại
      $target_dir = "../Picture/Products/";
      if (!file_exists($target_dir)) {
         mkdir($target_dir, 0777, true);
      }

      $hinh_anh = "Picture/Products/" . basename($_FILES["hinh_anh"]["name"]);
      $target_file = $target_dir . basename($_FILES["hinh_anh"]["name"]);

      // Kiểm tra và upload file
      if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_file)) {
         // File đã được upload thành công
         echo "<script>console.log('Upload thành công: $hinh_anh');</script>";
      } else {
         echo "<script>alert('Có lỗi khi upload file: " . $_FILES['hinh_anh']['error'] . "');</script>";
      }
   }

   // Thêm sách vào database
   $sql = "INSERT INTO SACH (tieu_de, tac_gia, gia_tien, so_luong, loaisach_id, mo_ta, hinh_anh, nha_xuat_ban, trang_thai) 
            VALUES ('$tieu_de', '$tac_gia', '$gia_tien', '$so_luong', '$loaisach_id', '$mo_ta', '$hinh_anh', '$nha_xuat_ban', 'active')";

   if (mysqli_query($conn, $sql)) {
      // Thay vì hiển thị alert, chuyển hướng người dùng với thông báo thành công
      header("Location: edit_sach.php?success=add");
      exit();
   } else {
      // Lưu lỗi vào session và chuyển hướng
      $_SESSION['error'] = "Lỗi: " . mysqli_error($conn);
      header("Location: edit_sach.php?error=add");
      exit();
   }
}

// Xử lý xóa sách
if (isset($_GET['delete_id'])) {
   $delete_id = $_GET['delete_id'];
   $sql = "SELECT COUNT(*) AS total FROM CHITIETDONHANG WHERE sach_id = $delete_id";
   $result = mysqli_query($conn, $sql);
   $row = mysqli_fetch_assoc($result);
   $count = $row['total'];

   if($count > 0){
      $sql = "UPDATE SACH SET trang_thai = 'deleted' WHERE sach_id = $delete_id";
   } else{
      $sql = "DELETE FROM SACH WHERE sach_id = $delete_id";
   }


   if (mysqli_query($conn, $sql)) {
      header("Location: edit_sach.php?success=delete");
      exit();
   } else {
      $_SESSION['error'] = "Lỗi: " . mysqli_error($conn);
      header("Location: edit_sach.php?error=delete");
      exit();
   }
}

// Xử lý sửa sách
if (isset($_POST['sua_sach'])) {
   $sach_id = $_POST['sach_id'];
   $tieu_de = $_POST['tieu_de'];
   $tac_gia = $_POST['tac_gia'];
   $gia_tien = $_POST['gia_tien'];
   $so_luong = $_POST['so_luong'];
   $loaisach_id = $_POST['loaisach_id'];
   $mo_ta = $_POST['mo_ta'];
   $nha_xuat_ban = $_POST['nha_xuat_ban'];

   // Xử lý upload hình ảnh mới nếu có
   if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
      $target_dir = "../Picture/Products/";
      if (!file_exists($target_dir)) {
         mkdir($target_dir, 0777, true);
      }

      $hinh_anh = "Picture/Products/" . basename($_FILES["hinh_anh"]["name"]);
      $target_file = $target_dir . basename($_FILES["hinh_anh"]["name"]);

      // Kiểm tra và upload file
      if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_file)) {
         // Cập nhật với hình ảnh mới
         $sql = "UPDATE SACH SET tieu_de='$tieu_de', tac_gia='$tac_gia', gia_tien='$gia_tien', 
               so_luong='$so_luong', loaisach_id='$loaisach_id', mo_ta='$mo_ta', 
               hinh_anh='$hinh_anh', nha_xuat_ban='$nha_xuat_ban' WHERE sach_id=$sach_id";
         echo "<script>console.log('Upload thành công: $hinh_anh');</script>";
      } else {
         echo "<script>alert('Có lỗi khi upload file: " . $_FILES['hinh_anh']['error'] . "');</script>";
      }
   } else {
      // Cập nhật không thay đổi hình ảnh
      $sql = "UPDATE SACH SET tieu_de='$tieu_de', tac_gia='$tac_gia', gia_tien='$gia_tien', 
               so_luong='$so_luong', loaisach_id='$loaisach_id', mo_ta='$mo_ta', 
               nha_xuat_ban='$nha_xuat_ban' WHERE sach_id=$sach_id";
   }

   if (mysqli_query($conn, $sql)) {
      header("Location: edit_sach.php?success=update");
      exit();
   } else {
      $_SESSION['error'] = "Lỗi: " . mysqli_error($conn);
      header("Location: edit_sach.php?error=update");
      exit();
   }
}

// Hiển thị thông báo từ GET parameters
$message = '';
if (isset($_GET['success'])) {
   switch ($_GET['success']) {
      case 'add':
         $message = 'Thêm sách thành công!';
         break;
      case 'update':
         $message = 'Cập nhật sách thành công!';
         break;
      case 'delete':
         $message = 'Xóa sách thành công!';
         break;
   }
}

if (isset($_GET['error']) && isset($_SESSION['error'])) {
   $message = $_SESSION['error'];
   unset($_SESSION['error']);
}

// Lấy danh sách loại sách
$loaisach_result = mysqli_query($conn, "SELECT * FROM LOAISACH ORDER BY ten_loai");

// Lấy danh sách nhà xuất bản (từ dữ liệu hiện có)
$nxb_result = mysqli_query($conn, "SELECT DISTINCT nha_xuat_ban FROM SACH");

// Xử lý lọc và sắp xếp
$filter_category = isset($_GET['filter_category']) ? intval($_GET['filter_category']) : 0;
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'sach_id';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Đảm bảo các tham số sắp xếp hợp lệ
$valid_sort_fields = ['sach_id', 'tieu_de', 'gia_tien', 'so_luong', 'ten_loai'];
if (!in_array($sort_by, $valid_sort_fields)) {
   $sort_by = 'sach_id';
}

$valid_sort_orders = ['ASC', 'DESC'];
if (!in_array($sort_order, $valid_sort_orders)) {
   $sort_order = 'DESC';
}

// Xây dựng câu truy vấn với điều kiện lọc
$query = "SELECT s.*, l.ten_loai FROM SACH s 
          LEFT JOIN LOAISACH l ON s.loaisach_id = l.loaisach_id WHERE s.trang_thai = 'active'";

if ($filter_category > 0) {
   $query .= " AND s.loaisach_id = $filter_category";
}

if (!empty($search_keyword)) {
   $query .= " AND (s.tieu_de LIKE '%$search_keyword%' OR s.tac_gia LIKE '%$search_keyword%' OR l.ten_loai LIKE '%$search_keyword%')";
}

$query .= " ORDER BY $sort_by $sort_order";

$sach_result = mysqli_query($conn, $query);

// Hàm tạo URL sắp xếp
function getSortUrl($field, $current_sort, $current_order, $filter_category, $search_keyword)
{
   $new_order = ($field == $current_sort && $current_order == 'ASC') ? 'DESC' : 'ASC';
   $url = "edit_sach.php?sort=$field&order=$new_order";

   if ($filter_category > 0) {
      $url .= "&filter_category=$filter_category";
   }

   if (!empty($search_keyword)) {
      $url .= "&search=" . urlencode($search_keyword);
   }

   return $url;
}

// Hàm hiển thị biểu tượng sắp xếp
function getSortIcon($field, $current_sort, $current_order)
{
   if ($field == $current_sort) {
      return ($current_order == 'ASC') ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
   }
   return '<i class="fas fa-sort"></i>';
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>T1 Bookstore | QUẢN LÝ SÁCH</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="../CSS/admin.css">
   <link rel="stylesheet" href="../CSS/index.css">
   <style>
      .product-form {
         background-color: #fff;
         padding: 20px;
         border-radius: 5px;
         margin-bottom: 20px;
      }

      .product-form input,
      .product-form select,
      .product-form textarea {
         width: 100%;
         padding: 10px;
         margin-bottom: 15px;
         border: 1px solid #ddd;
         border-radius: 4px;
      }

      .product-form button {
         background-color: #000;
         color: white;
         border: none;
         padding: 10px 20px;
         border-radius: 4px;
         cursor: pointer;
      }

      .product-table {
         width: 100%;
         border-collapse: collapse;
         table-layout: fixed;
         /* Thêm thuộc tính này để cố định chiều rộng các cột */
      }

      .product-table th,
      .product-table td {
         padding: 10px;
         text-align: left;
         border-bottom: 1px solid #ddd;
         white-space: nowrap;
         /* Ngăn văn bản xuống dòng */
         overflow: hidden;
         /* Ẩn nội dung tràn ra */
         text-overflow: ellipsis;
         /* Hiện dấu ... khi văn bản quá dài */
      }

      .product-table th {
         background-color: #000;
         color: white;
         font-size: 15px;
      }

      /* Thiết lập chiều rộng cụ thể cho từng cột */
      .product-table th:nth-child(1),
      .product-table td:nth-child(1) {
         width: 50px;
         /* STT */
      }

      .product-table th:nth-child(2),
      .product-table td:nth-child(2) {
         width: 180px;
         /* Tên sản phẩm */
      }

      .product-table th:nth-child(3),
      .product-table td:nth-child(3) {
         width: 100px;
         /* Ảnh sản phẩm */
         text-align: center;
      }

      .product-table th:nth-child(4),
      .product-table td:nth-child(4) {
         width: 120px;
         /* Giá */
      }

      .product-table th:nth-child(5),
      .product-table td:nth-child(5) {
         width: 200px;
         /* Mô tả */
      }

      .product-table th:nth-child(6),
      .product-table td:nth-child(6) {
         width: 80px;
         /* Số lượng */
         text-align: center;
      }

      .product-table th:nth-child(7),
      .product-table td:nth-child(7) {
         width: 100px;
         /* Thể loại */
         text-align: center;
      }

      .product-table th:nth-child(8),
      .product-table td:nth-child(8) {
         width: 100px;
         /* Trạng thái */
         text-align: center;
      }

      .product-table th:nth-child(9),
      .product-table td:nth-child(9) {
         width: 120px;
         /* Hành động */
         text-align: center;
      }

      /* Thêm tooltip để hiện đầy đủ nội dung khi hover */
      .product-table td {
         position: relative;
      }

      .product-table td:hover::after {
         content: attr(data-full-text);
         position: absolute;
         left: 0;
         top: 100%;
         background-color: #333;
         color: white;
         padding: 5px;
         border-radius: 3px;
         z-index: 999;
         white-space: normal;
         max-width: 300px;
         display: none;
         transition: 0.3s;
         /* Ẩn mặc định, chỉ hiển thị cho những cột có đặt thuộc tính data-full-text */
      }

      .product-table td[data-full-text]:hover::after {
         display: block;
         transition: 0.3s;
      }

      .sort-header {
         cursor: pointer;
      }

      .sort-header a {
         color: white;
         text-decoration: none;
         display: flex;
         align-items: center;
         justify-content: space-between;
      }

      .sort-header i {
         margin-left: 5px;
      }

      .action-buttons {
         width: 120px;
         white-space: nowrap;
      }

      .action-buttons button {
         border: none;
         border-radius: 3px;
         cursor: pointer;
         padding: 8px;
         margin: 2px;
         width: 50px;
         font-size: 12px;
      }

      .edit-btn {
         background-color: #000;
         color: white;
      }

      .delete-btn {
         background-color: #f44336;
         color: white;
      }

      .product-image {
         width: 60px;
         height: 80px;
         object-fit: cover;
      }

      .filter-container {
         display: flex;
         justify-content: space-between;
         margin-bottom: 20px;
         background-color: #f5f5f5;
         padding: 15px;
         border-radius: 5px;
      }

      .filter-form {
         display: flex;
         gap: 10px;
      }

      .filter-form select,
      .filter-form input {
         padding: 8px;
         border: 1px solid #ddd;
         border-radius: 4px;
      }

      .filter-form button {
         background-color: #2196F3;
         color: white;
         border: none;
         padding: 8px 15px;
         border-radius: 4px;
         cursor: pointer;
      }

      .reset-filter {
         background-color: #f44336;
         color: white;
         text-decoration: none;
         padding: 8px 15px;
         border-radius: 4px;
         display: inline-block;
      }

      .category-badge {
         background-color: #2196F3;
         color: white;
         padding: 3px 8px;
         border-radius: 12px;
         font-size: 12px;
         font-weight: bold;
         display: inline-block;
         max-width: 90px;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .con-hang {
         color: green;
         font-weight: bold;
      }

      .het-hang {
         color: red;
         font-weight: bold;
      }

      /* Đảm bảo nội dung không bị tràn container chính */
      .content {
         overflow-x: auto;
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
         <div class="alert" style="padding: 10px; margin-bottom: 15px; background-color: #4CAF50; color: white; border-radius: 4px;">
            <?php echo $message; ?>
         </div>
      <?php endif; ?>

      <div class="product-form">
         <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="sach_id" id="sach_id">
            <input type="text" name="tieu_de" id="tieu_de" placeholder="Nhập tên sản phẩm" required>
            <input type="file" name="hinh_anh" id="hinh_anh">
            <input type="number" name="gia_tien" id="gia_tien" placeholder="Giá sản phẩm" required>
            <textarea name="mo_ta" id="mo_ta" placeholder="Mô tả sản phẩm"></textarea>
            <input type="number" name="so_luong" id="so_luong" placeholder="Số lượng sản phẩm" required>

            <div>Trạng thái sản phẩm:</div>
            <select name="trang_thai" id="trang_thai">
               <option value="Còn hàng">Còn hàng</option>
               <option value="Hết hàng">Hết hàng</option>
            </select>

            <div>Chọn thể loại:</div>
            <select name="loaisach_id" id="loaisach_id" required>
               <option value="">-- Chọn thể loại --</option>
               <?php
               // Reset con trỏ kết quả để đọc lại từ đầu
               mysqli_data_seek($loaisach_result, 0);
               while ($loai = mysqli_fetch_assoc($loaisach_result)):
               ?>
                  <option value="<?php echo $loai['loaisach_id']; ?>"><?php echo $loai['ten_loai']; ?></option>
               <?php endwhile; ?>
            </select>

            <div>Chọn nhà xuất bản:</div>
            <select name="nha_xuat_ban" id="nha_xuat_ban" required>
               <option value="">-- Chọn nhà xuất bản --</option>
               <?php
               // Reset con trỏ kết quả để đọc lại từ đầu
               mysqli_data_seek($nxb_result, 0);
               while ($nxb = mysqli_fetch_assoc($nxb_result)):
               ?>
                  <option value="<?php echo $nxb['nha_xuat_ban']; ?>"><?php echo $nxb['nha_xuat_ban']; ?></option>
               <?php endwhile; ?>
            </select>

            <input type="text" name="tac_gia" id="tac_gia" placeholder="Tác giả" required>

            <button type="submit" name="them_sach" id="submit_btn">Thêm mới</button>
         </form>
      </div>

      <!-- Filter và tìm kiếm đã được di chuyển xuống đây -->
      <div class="filter-container">
         <form class="filter-form" method="GET" action="">
            <select name="filter_category">
               <option value="0">Tất cả thể loại</option>
               <?php
               // Reset con trỏ kết quả để đọc lại từ đầu
               mysqli_data_seek($loaisach_result, 0);
               while ($loai = mysqli_fetch_assoc($loaisach_result)):
               ?>
                  <option value="<?php echo $loai['loaisach_id']; ?>" <?php echo ($filter_category == $loai['loaisach_id']) ? 'selected' : ''; ?>>
                     <?php echo $loai['ten_loai']; ?>
                  </option>
               <?php endwhile; ?>
            </select>

            <input type="text" name="search" placeholder="Tìm kiếm..." value="<?php echo $search_keyword; ?>">

            <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
            <input type="hidden" name="order" value="<?php echo $sort_order; ?>">

            <button type="submit"><i class="fas fa-filter"></i> Lọc</button>

            <?php if ($filter_category > 0 || !empty($search_keyword)): ?>
               <a href="edit_sach.php" class="btn reset-filter"><i class="fas fa-times"></i> Xóa bộ lọc</a>
            <?php endif; ?>
         </form>
      </div>

      <div class="product-list">
         <table class="product-table">
            <thead>
               <tr>
                  <th class="sort-header">
                     <a href="<?php echo getSortUrl('sach_id', $sort_by, $sort_order, $filter_category, $search_keyword); ?>">
                        STT <?php echo getSortIcon('sach_id', $sort_by, $sort_order); ?>
                     </a>
                  </th>
                  <th class="sort-header">
                     <a href="<?php echo getSortUrl('tieu_de', $sort_by, $sort_order, $filter_category, $search_keyword); ?>">
                        Tên sản phẩm <?php echo getSortIcon('tieu_de', $sort_by, $sort_order); ?>
                     </a>
                  </th>
                  <th>Ảnh sản phẩm</th>
                  <th class="sort-header">
                     <a href="<?php echo getSortUrl('gia_tien', $sort_by, $sort_order, $filter_category, $search_keyword); ?>">
                        Giá <?php echo getSortIcon('gia_tien', $sort_by, $sort_order); ?>
                     </a>
                  </th>
                  <th>Mô tả</th>
                  <th class="sort-header">
                     <a href="<?php echo getSortUrl('so_luong', $sort_by, $sort_order, $filter_category, $search_keyword); ?>">
                        Số lượng <?php echo getSortIcon('so_luong', $sort_by, $sort_order); ?>
                     </a>
                  </th>
                  <th class="sort-header">
                     <a href="<?php echo getSortUrl('ten_loai', $sort_by, $sort_order, $filter_category, $search_keyword); ?>">
                        Thể loại <?php echo getSortIcon('ten_loai', $sort_by, $sort_order); ?>
                     </a>
                  </th>
                  <th>Trạng thái</th>
                  <th>Hành động</th>
               </tr>
            </thead>
            <tbody>
               <?php
               $stt = 1;
               if (mysqli_num_rows($sach_result) > 0) {
                  while ($sach = mysqli_fetch_assoc($sach_result)):
                     $trang_thai = ($sach['so_luong'] > 0) ? "Còn hàng" : "Hết hàng";
                     $mo_ta_short = substr($sach['mo_ta'], 0, 50) . (strlen($sach['mo_ta']) > 50 ? '...' : '');
               ?>
                     <tr>
                        <td><?php echo $stt++; ?></td>
                        <td data-full-text="<?php echo htmlspecialchars($sach['tieu_de']); ?>"><?php echo $sach['tieu_de']; ?></td>
                        <td>
                           <?php if (!empty($sach['hinh_anh'])): ?>
                              <img src="../<?php echo $sach['hinh_anh']; ?>" alt="<?php echo $sach['tieu_de']; ?>" class="product-image" onerror="this.onerror=null; this.src='../images/no-image.jpg'; console.log('Lỗi tải ảnh: <?php echo $sach['hinh_anh']; ?>');">
                           <?php else: ?>
                              <span>Không có ảnh</span>
                           <?php endif; ?>
                        </td>
                        <td><?php echo number_format($sach['gia_tien'], 0, ',', '.'); ?> VND</td>
                        <td data-full-text="<?php echo htmlspecialchars($sach['mo_ta']); ?>"><?php echo $mo_ta_short; ?></td>
                        <td><?php echo $sach['so_luong']; ?></td>
                        <td>
                           <span class="category-badge" title="<?php echo $sach['ten_loai']; ?>">
                              <?php echo $sach['ten_loai']; ?>
                           </span>
                        </td>
                        <td class="<?php echo $trang_thai == 'Còn hàng' ? 'con-hang' : 'het-hang'; ?>">
                           <?php echo $trang_thai; ?>
                        </td>
                        <td class="action-buttons">
                           <button class="edit-btn" onclick="editSach(<?php echo htmlspecialchars(json_encode($sach)); ?>)">Sửa</button>
                           <button class="delete-btn" onclick="deleteSach(<?php echo $sach['sach_id']; ?>)">Xóa</button>
                        </td>
                     </tr>
                  <?php
                  endwhile;
               } else {
                  ?>
                  <tr>
                     <td colspan="9" style="text-align: center;">Không tìm thấy sách nào</td>
                  </tr>
               <?php } ?>
            </tbody>
         </table>
      </div>
   </div>

   <script>
      // JavaScript để xử lý sự kiện menu
      document.querySelectorAll('.menu-item').forEach(function(item) {
         item.addEventListener('click', function(e) {
            e.preventDefault(); // Ngăn chặn hành vi mặc định của thẻ a (nếu có)

            const menuText = this.textContent.trim(); // Lấy nội dung của mục menu
            let page = '';

            // Ánh xạ tên menu với tên file PHP
            switch (menuText) {
               case 'Thể loại':
                  page = 'edit_loaisach.php';
                  break;
               case 'Sách':
                  page = 'edit_sach.php';
                  break;
               case 'Tài khoản':
                  page = 'edit_taikhoan.php';
                  break;
               case 'Hóa đơn':
                  page = 'hoa_don.php';
                  break;
               case 'Trang chủ':
                  page = 'trangchu.php';
                  break;
               case 'Đơn hàng':
                  page = 'admin_orders.php';
                  break;
               case 'Bảo hành':
                  page = 'admin_warranty.php';
                  break;
               case 'Đăng xuất':
                  if (confirm('Bạn có chắc muốn đăng xuất?')) {
                     page = 'logout.php';
                  } else {
                     return; // Thoát nếu người dùng không xác nhận đăng xuất
                  }
                  break;
               default:
                  page = 'edit_sach.php'; // Trang mặc định nếu không khớp
            }

            // Tạo đường dẫn tuyệt đối đến thư mục PHP
            window.location.href = window.location.origin + '/DOAN_WEB2/PHP/' + page;
         });
      });

      // Hàm xử lý sửa sách
      const editSach = sach => {
         document.getElementById('sach_id').value = sach.sach_id;
         document.getElementById('tieu_de').value = sach.tieu_de;
         document.getElementById('gia_tien').value = sach.gia_tien;
         document.getElementById('mo_ta').value = sach.mo_ta;
         document.getElementById('so_luong').value = sach.so_luong;
         document.getElementById('trang_thai').value = sach.so_luong > 0 ? 'Còn hàng' : 'Hết hàng';
         document.getElementById('loaisach_id').value = sach.loaisach_id;
         document.getElementById('nha_xuat_ban').value = sach.nha_xuat_ban;
         document.getElementById('tac_gia').value = sach.tac_gia;

         document.getElementById('submit_btn').textContent = 'Cập nhật';
         document.getElementById('submit_btn').name = 'sua_sach';

         // Cuộn lên đầu trang để người dùng thấy form
         window.scrollTo(0, 0);
      }

      // Hàm xử lý xóa sách
      const deleteSach = sachId => {
         if (confirm('Bạn có chắc muốn xóa sách này?')) window.location.href = 'edit_sach.php?delete_id=' + sachId;
      }
   </script>
</body>

</html>