<?php
require_once 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// Hàm kiểm tra số điện thoại hợp lệ
function isValidPhoneNumber($sdt)
{
   return preg_match('/^0[0-9]{9}$/', $sdt);
}

// Xử lý thêm tài khoản mới
if (isset($_POST['them_taikhoan'])) {
   // Kiểm tra tất cả các trường bắt buộc
   if (!isset($_POST['ho_ten'], $_POST['sdt'], $_POST['dia_chi'], $_POST['ngay_sinh'], $_POST['quyen'], $_POST['mat_khau'])) {
      $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
      header("Location: edit_taikhoan.php?error=add");
      exit();
   }

   $ho_ten = mysqli_real_escape_string($conn, trim($_POST['ho_ten']));
   $sdt = mysqli_real_escape_string($conn, trim($_POST['sdt']));
   $dia_chi = mysqli_real_escape_string($conn, trim($_POST['dia_chi']));
   $ngay_sinh = $_POST['ngay_sinh'];
   $quyen = $_POST['quyen'];
   $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);

   // Kiểm tra các trường không được để trống
   if (empty($ho_ten) || empty($sdt) || empty($dia_chi) || empty($ngay_sinh) || empty($quyen) || empty($_POST['mat_khau'])) {
      $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
      header("Location: edit_taikhoan.php?error=add");
      exit();
   }

   // Kiểm tra số điện thoại hợp lệ
   if (!isValidPhoneNumber($sdt)) {
      $_SESSION['error'] = "Số điện thoại phải bắt đầu bằng 0 và có đúng 10 số!";
      header("Location: edit_taikhoan.php?error=add");
      exit();
   }

   // Kiểm tra trùng số điện thoại
   $check_sdt_sql = "SELECT user_id FROM `USER` WHERE sdt = '$sdt'";
   $sdt_result = mysqli_query($conn, $check_sdt_sql);

   if (mysqli_num_rows($sdt_result) > 0) {
      $_SESSION['error'] = "Số điện thoại '$sdt' đã được sử dụng!";
      header("Location: edit_taikhoan.php?error=add");
      exit();
   }

   // Thêm tài khoản vào database
   $sql = "INSERT INTO `USER` (ho_ten, sdt, dia_chi, ngay_sinh, quyen, mat_khau) 
           VALUES ('$ho_ten', '$sdt', '$dia_chi', '$ngay_sinh', '$quyen', '$mat_khau')";

   if (mysqli_query($conn, $sql)) {
      header("Location: edit_taikhoan.php?success=add");
      exit();
   } else {
      $_SESSION['error'] = "Lỗi: " . mysqli_error($conn);
      header("Location: edit_taikhoan.php?error=add");
      exit();
   }
}

// Xử lý khóa tài khoản
if (isset($_GET['delete_id'])) {
   $delete_id = intval($_GET['delete_id']);

   // Bắt đầu giao dịch
   mysqli_begin_transaction($conn);

   try {
      // Khóa tài khoản trong USER
      $sql_delete_user = "UPDATE USER SET trang_thai = 'isBlocked' WHERE user_id = $delete_id";
      if (mysqli_query($conn, $sql_delete_user)) {
         mysqli_commit($conn);
         header("Location: edit_taikhoan.php?success=delete");
         exit();
      } else {
         throw new Exception("Lỗi khi khóa tài khoản: " . mysqli_error($conn));
      }
   } catch (Exception $e) {
      mysqli_rollback($conn);
      $_SESSION['error'] = $e->getMessage();
      header("Location: edit_taikhoan.php?error=delete");
      exit();
   }
}

// Xử lý mở khóa tài khoản
if (isset($_GET['unBlock_id'])) {
   $delete_id = intval($_GET['unBlock_id']);

   // Bắt đầu giao dịch
   mysqli_begin_transaction($conn);

   try {
      // Mở khóa tài khoản trong USER
      $sql_delete_user = "UPDATE USER SET trang_thai = 'active' WHERE user_id = $delete_id";
      if (mysqli_query($conn, $sql_delete_user)) {
         mysqli_commit($conn);
         header("Location: edit_taikhoan.php?success=unBlock");
         exit();
      } else {
         throw new Exception("Lỗi khi mở khóa tài khoản: " . mysqli_error($conn));
      }
   } catch (Exception $e) {
      mysqli_rollback($conn);
      $_SESSION['error'] = $e->getMessage();
      header("Location: edit_taikhoan.php?error=unBlock");
      exit();
   }
}

// Xử lý sửa tài khoản
if (isset($_POST['sua_taikhoan'])) {
   // Kiểm tra các trường bắt buộc
   if (!isset($_POST['user_id'], $_POST['ho_ten'], $_POST['sdt'], $_POST['dia_chi'], $_POST['ngay_sinh'], $_POST['quyen'])) {
      $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
      header("Location: edit_taikhoan.php?error=update");
      exit();
   }

   $user_id = intval($_POST['user_id']);
   $ho_ten = mysqli_real_escape_string($conn, trim($_POST['ho_ten']));
   $sdt = mysqli_real_escape_string($conn, trim($_POST['sdt']));
   $dia_chi = mysqli_real_escape_string($conn, trim($_POST['dia_chi']));
   $ngay_sinh = $_POST['ngay_sinh'];
   $quyen = $_POST['quyen'];

   // Kiểm tra các trường không được để trống
   if (empty($ho_ten) || empty($sdt) || empty($dia_chi) || empty($ngay_sinh) || empty($quyen)) {
      $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
      header("Location: edit_taikhoan.php?error=update");
      exit();
   }

   // Kiểm tra số điện thoại hợp lệ
   if (!isValidPhoneNumber($sdt)) {
      $_SESSION['error'] = "Số điện thoại phải bắt đầu bằng 0 và có đúng 10 số!";
      header("Location: edit_taikhoan.php?error=update");
      exit();
   }

   // Kiểm tra trùng số điện thoại (ngoại trừ chính tài khoản đang sửa)
   $check_sdt_sql = "SELECT user_id FROM `USER` WHERE sdt = '$sdt' AND user_id != $user_id";
   $sdt_result = mysqli_query($conn, $check_sdt_sql);

   if (mysqli_num_rows($sdt_result) > 0) {
      $_SESSION['error'] = "Số điện thoại '$sdt' đã được sử dụng bởi tài khoản khác!";
      header("Location: edit_taikhoan.php?error=update");
      exit();
   }

   $mat_khau_sql = "";
   if (!empty($_POST['mat_khau'])) {
      $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
      $mat_khau_sql = ", mat_khau='$mat_khau'";
   }

   $sql = "UPDATE `USER` SET ho_ten='$ho_ten', sdt='$sdt', 
           dia_chi='$dia_chi', ngay_sinh='$ngay_sinh', quyen='$quyen' $mat_khau_sql 
           WHERE user_id=$user_id";

   if (mysqli_query($conn, $sql)) {
      header("Location: edit_taikhoan.php?success=update");
      exit();
   } else {
      $_SESSION['error'] = "Lỗi: " . mysqli_error($conn);
      header("Location: edit_taikhoan.php?error=update");
      exit();
   }
}

// Hiển thị thông báo
$message = '';
if (isset($_GET['success'])) {
   switch ($_GET['success']) {
      case 'add':
         $message = 'Thêm tài khoản thành công!';
         break;
      case 'update':
         $message = 'Cập nhật tài khoản thành công!';
         break;
      case 'delete':
         $message = 'Khóa tài khoản thành công!';
         break;
      case 'unBlock':
         $message = 'Mở khóa tài khoản thành công!';
         break;
   }
}

if (isset($_GET['error']) && isset($_SESSION['error'])) {
   $message = $_SESSION['error'];
   unset($_SESSION['error']);
}

// Xử lý lọc và sắp xếp
$filter_quyen = isset($_GET['filter_quyen']) ? mysqli_real_escape_string($conn, $_GET['filter_quyen']) : '';
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'user_id';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

$valid_sort_fields = ['user_id', 'ho_ten', 'quyen'];
if (!in_array($sort_by, $valid_sort_fields)) {
   $sort_by = 'user_id';
}

$valid_sort_orders = ['ASC', 'DESC'];
if (!in_array($sort_order, $valid_sort_orders)) {
   $sort_order = 'DESC';
}

$query = "SELECT * FROM `USER` WHERE 1=1";
if (!empty($filter_quyen)) {
   $query .= " AND quyen = '$filter_quyen'";
}
if (!empty($search_keyword)) {
   $query .= " AND (ho_ten LIKE '%$search_keyword%' OR sdt LIKE '%$search_keyword%')";
}
$query .= " ORDER BY $sort_by $sort_order";

$user_result = mysqli_query($conn, $query);

function getSortUrl($field, $current_sort, $current_order, $filter_quyen, $search_keyword)
{
   $new_order = ($field == $current_sort && $current_order == 'ASC') ? 'DESC' : 'ASC';
   $url = "edit_taikhoan.php?sort=$field&order=$new_order";
   if (!empty($filter_quyen)) {
      $url .= "&filter_quyen=" . urlencode($filter_quyen);
   }
   if (!empty($search_keyword)) {
      $url .= "&search=" . urlencode($search_keyword);
   }
   return $url;
}

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
   <title>T1 Bookstore | QUẢN LÝ TÀI KHOẢN</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="../CSS/admin.css">
   <link rel="stylesheet" href="../CSS/index.css">
   <style>
      .account-form {
         background-color: #fff;
         padding: 20px;
         border-radius: 5px;
         margin-bottom: 20px;
      }

      .account-form input,
      .account-form select {
         width: 100%;
         padding: 10px;
         margin-bottom: 15px;
         border: 1px solid #ddd;
         border-radius: 4px;
      }

      .account-form button {
         background-color: #000;
         color: white;
         border: none;
         padding: 10px 20px;
         border-radius: 4px;
         cursor: pointer;
      }

      .account-table {
         width: 100%;
         border-collapse: collapse;
         table-layout: fixed;
      }

      .account-table th,
      .account-table td {
         padding: 10px;
         text-align: left;
         border-bottom: 1px solid #ddd;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .account-table th {
         background-color: #000;
         color: white;
         font-size: 15px;
      }

      .account-table th:nth-child(1),
      .account-table td:nth-child(1) {
         width: 50px;
      }

      .account-table th:nth-child(2),
      .account-table td:nth-child(2) {
         width: 180px;
      }

      .account-table th:nth-child(3),
      .account-table td:nth-child(3) {
         width: 120px;
      }

      .account-table th:nth-child(4),
      .account-table td:nth-child(4) {
         width: 180px;
      }

      .account-table th:nth-child(5),
      .account-table td:nth-child(5) {
         width: 120px;
      }

      .account-table th:nth-child(6),
      .account-table td:nth-child(6) {
         width: 100px;
      }

      .account-table th:nth-child(7),
      .account-table td:nth-child(7) {
         width: 120px;
      }

      .account-table td {
         position: relative;
      }

      .account-table td:hover::after {
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
      }

      .account-table td[data-full-text]:hover::after {
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
         width: auto;
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

      .delete-btn.disabled {
         opacity: 0.5;
         cursor: not-allowed;
         pointer-events: none;
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

      .role-badge {
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

      .content {
         overflow-x: auto;
      }

      .alert.error {
         background-color: #f44336;
      }

      .alert.success {
         background-color: #4CAF50;
         color: #ffffff;
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
      <div class="menu-item">Thể loại</div>
      <div class="menu-item">Sách</div>
      <div class="menu-item active">Tài khoản</div>
      <div class="menu-item">Đơn hàng</div>
      <div class="menu-item">Bảo hành</div>
      <div class="menu-item">Đăng xuất</div>
   </div>

   <div class="content">
      <?php if (!empty($message)): ?>
         <div class="alert <?php echo (isset($_GET['error']) ? 'error' : 'success'); ?>" style="padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            <?php echo htmlspecialchars($message); ?>
         </div>
      <?php endif; ?>

      <div class="account-form">
         <form action="" method="post">
            <input type="hidden" name="user_id" id="user_id">
            <input type="text" name="ho_ten" id="ho_ten" placeholder="Họ tên" required>
            <input type="text" name="sdt" id="sdt" placeholder="Số điện thoại" pattern="0[0-9]{9}" title="Số điện thoại phải bắt đầu bằng 0 và có đúng 10 số" required>
            <input type="text" name="dia_chi" id="dia_chi" placeholder="Địa chỉ" required>
            <input type="date" name="ngay_sinh" id="ngay_sinh" required>
            <input type="password" name="mat_khau" id="mat_khau" placeholder="Mật khẩu">
            <select name="quyen" id="quyen" required>
               <option value="">-- Chọn quyền --</option>
               <option value="Admin">Admin</option>
               <option value="KhachHang">Khách Hàng</option>
            </select>
            <button type="submit" name="them_taikhoan" id="submit_btn">Thêm mới</button>
         </form>
      </div>

      <div class="filter-container">
         <form class="filter-form" method="GET" action="">
            <select name="filter_quyen">
               <option value="">Tất cả quyền</option>
               <option value="Admin" <?php echo ($filter_quyen == 'Admin') ? 'selected' : ''; ?>>Admin</option>
               <option value="KhachHang" <?php echo ($filter_quyen == 'KhachHang') ? 'selected' : ''; ?>>Khách Hàng</option>
            </select>
            <input type="text" name="search" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search_keyword); ?>">
            <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
            <input type="hidden" name="order" value="<?php echo $sort_order; ?>">
            <button type="submit"><i class="fas fa-filter"></i> Lọc</button>
            <?php if (!empty($filter_quyen) || !empty($search_keyword)): ?>
               <a href="edit_taikhoan.php" class="reset-filter"><i class="fas fa-times"></i> Xóa bộ lọc</a>
            <?php endif; ?>
         </form>
      </div>

      <div class="account-list">
         <table class="account-table">
            <thead>
               <tr>
                  <th class="sort-header">
                     <a href="<?php echo getSortUrl('user_id', $sort_by, $sort_order, $filter_quyen, $search_keyword); ?>">
                        STT <?php echo getSortIcon('user_id', $sort_by, $sort_order); ?>
                     </a>
                  </th>
                  <th class="sort-header">
                     <a href="<?php echo getSortUrl('ho_ten', $sort_by, $sort_order, $filter_quyen, $search_keyword); ?>">
                        Họ tên <?php echo getSortIcon('ho_ten', $sort_by, $sort_order); ?>
                     </a>
                  </th>
                  <th>Số điện thoại</th>
                  <th>Địa chỉ</th>
                  <th>Ngày sinh</th>
                  <th class="sort-header">
                     <a href="<?php echo getSortUrl('quyen', $sort_by, $sort_order, $filter_quyen, $search_keyword); ?>">
                        Quyền <?php echo getSortIcon('quyen', $sort_by, $sort_order); ?>
                     </a>
                  </th>
                  <th>Hành động</th>
               </tr>
            </thead>
            <tbody>
               <?php
               $stt = 1;
               if (mysqli_num_rows($user_result) > 0) {
                  while ($user = mysqli_fetch_assoc($user_result)):
               ?>
                     <tr>
                        <td><?php echo $stt++ . ($user['trang_thai'] != 'active' ? ' 🔒' : ''); ?></td>
                        <td data-full-text="<?php echo htmlspecialchars($user['ho_ten']); ?>"><?php echo htmlspecialchars($user['ho_ten']); ?></td>
                        <td><?php echo htmlspecialchars($user['sdt']); ?></td>
                        <td data-full-text="<?php echo htmlspecialchars($user['dia_chi']); ?>"><?php echo htmlspecialchars($user['dia_chi']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['ngay_sinh'])); ?></td>
                        <td>
                           <span class="role-badge" title="<?php echo htmlspecialchars($user['quyen']); ?>">
                              <?php echo htmlspecialchars($user['quyen']); ?>
                           </span>
                        </td>
                        <td class="action-buttons">
                           <button class="edit-btn" onclick="editTaiKhoan(<?php echo htmlspecialchars(json_encode($user)); ?>)">Sửa</button>
                           <button class="delete-btn" <?php echo ($user['quyen'] === 'Admin') ? "'disabled' disabled style='opacity: 0.5; pointer-events: none;'" : ''; ?>
                              onclick="deleteTaiKhoan(this, <?php echo $user['user_id']; ?>)"
                              title="<?php echo ($user['quyen'] === 'Admin') ? 'Không thể khóa tài khoản Admin' : 'Khóa tài khoản'; ?>"
                              id="<?php echo ($user['trang_thai'] == 'active' ? "Block" :  "unBlock")?>">
                              <?php echo($user['trang_thai'] == 'active' ? "Khóa" :  "Mở Khóa")?>
                           </button>
                        </td>
                     </tr>
                  <?php
                  endwhile;
               } else {
                  ?>
                  <tr>
                     <td colspan="7" style="text-align: center;">Không tìm thấy tài khoản nào</td>
                  </tr>
               <?php } ?>
            </tbody>
         </table>
      </div>
   </div>

   <script>
      document.querySelectorAll('.menu-item').forEach(function(item) {
         item.addEventListener('click', function(e) {
            e.preventDefault();
            const menuText = this.textContent.trim();
            let page = '';
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
               case 'Đơn hàng':
                  page = 'admin_orders.php';
                  break;
               case 'Bảo hành':
                  page = 'admin_warranty.php';
                  break;
               case 'Trang chủ':
                  page = 'trangchu.php';
                  break;
               case 'Đăng xuất':
                  if (confirm('Bạn có chắc muốn đăng xuất?')) {
                     page = 'logout.php';
                  } else {
                     return;
                  }
                  break;
               default:
                  page = 'edit_taikhoan.php';
            }
            window.location.href = window.location.origin + '/DOAN_WEB2/PHP/' + page;
         });
      });

      const editTaiKhoan = user => {
         document.getElementById('user_id').value = user.user_id;
         document.getElementById('ho_ten').value = user.ho_ten;
         document.getElementById('sdt').value = user.sdt;
         document.getElementById('dia_chi').value = user.dia_chi;
         document.getElementById('ngay_sinh').value = user.ngay_sinh.split(' ')[0];
         document.getElementById('quyen').value = user.quyen;
         document.getElementById('mat_khau').value = '';
         document.getElementById('mat_khau').placeholder = 'Nhập mật khẩu mới (nếu muốn thay đổi)';
         document.getElementById('submit_btn').textContent = 'Cập nhật';
         document.getElementById('submit_btn').name = 'sua_taikhoan';
         window.scrollTo(0, 0);
      }

      const deleteTaiKhoan = (button, userId) => {
         const isActive = button.textContent.trim() === 'Khóa';

         if (isActive) {
            if (confirm('Bạn có chắc muốn khóa tài khoản này?')) {
               window.location.href = 'edit_taikhoan.php?delete_id=' + userId;
            }
         } else {
            if (confirm('Bạn có chắc muốn mở khóa tài khoản này?')) {
               window.location.href = 'edit_taikhoan.php?unBlock_id=' + userId;
            }
         }
      };
   </script>
</body>

</html>