<?php
// Sử dụng file kết nối database đã có sẵn
require_once 'db_connect.php';

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
   $sql = "INSERT INTO SACH (tieu_de, tac_gia, gia_tien, so_luong, loaisach_id, mo_ta, hinh_anh, nha_xuat_ban) 
            VALUES ('$tieu_de', '$tac_gia', '$gia_tien', '$so_luong', '$loaisach_id', '$mo_ta', '$hinh_anh', '$nha_xuat_ban')";

   if (mysqli_query($conn, $sql)) {
      echo "<script>alert('Thêm sách thành công!');</script>";
   } else {
      echo "<script>alert('Lỗi: " . mysqli_error($conn) . "');</script>";
   }
}

// Xử lý xóa sách
if (isset($_GET['delete_id'])) {
   $delete_id = $_GET['delete_id'];
   $sql = "DELETE FROM SACH WHERE sach_id = $delete_id";

   if (mysqli_query($conn, $sql)) {
      echo "<script>alert('Xóa sách thành công!');</script>";
   } else {
      echo "<script>alert('Lỗi: " . mysqli_error($conn) . "');</script>";
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
      echo "<script>alert('Cập nhật sách thành công!');</script>";
   } else {
      echo "<script>alert('Lỗi: " . mysqli_error($conn) . "');</script>";
   }
}

// Lấy danh sách loại sách
$loaisach_result = mysqli_query($conn, "SELECT * FROM LOAISACH");

// Lấy danh sách nhà xuất bản (từ dữ liệu hiện có)
$nxb_result = mysqli_query($conn, "SELECT DISTINCT nha_xuat_ban FROM SACH");

// Lấy danh sách sách
$sach_result = mysqli_query($conn, "SELECT s.*, l.ten_loai FROM SACH s 
                                    LEFT JOIN LOAISACH l ON s.loaisach_id = l.loaisach_id 
                                    ORDER BY s.sach_id DESC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>T1 Bookstore | QUẢN LÝ SÁCH</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="../CSS/admin.css">
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
      }

      .product-table th,
      .product-table td {
         padding: 10px;
         text-align: left;
         border-bottom: 1px solid #ddd;
      }

      .product-table th {
         background-color: #000;
         color: white;
      }

      .action-buttons button {
         margin-right: 5px;
         padding: 5px 10px;
         border: none;
         border-radius: 3px;
         cursor: pointer;
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
         width: 80px;
         height: 80px;
         object-fit: cover;
      }
   </style>
</head>

<body>
   <div class="sidebar">
      <div class="admin-profile">
         <div class="admin-avatar">
            <i class="fas fa-user"></i>
         </div>
         <div>Admin</div>
      </div>

      <div class="menu-item">Thể loại</div>
      <div class="menu-item active">Sách</div>
      <div class="menu-item">Tài khoản</div>
      <div class="menu-item">Hóa đơn</div>
      <div class="menu-item">Bảo hành</div>
      <div class="menu-item">Đăng xuất</div>
   </div>

   <div class="content">
      <div class="header">QUẢN LÝ SÁCH</div>

      <div class="product-form">
         <h2>Sản Phẩm</h2>
         <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="sach_id" id="sach_id">
            <input type="text" name="tieu_de" id="tieu_de" placeholder="Nhập tên sản phẩm" required>
            <input type="file" name="hinh_anh" id="hinh_anh">
            <input type="text" name="gia_tien" id="gia_tien" placeholder="Giá sản phẩm" required>
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
               <?php while ($loai = mysqli_fetch_assoc($loaisach_result)): ?>
                  <option value="<?php echo $loai['loaisach_id']; ?>"><?php echo $loai['ten_loai']; ?></option>
               <?php endwhile; ?>
            </select>

            <div>Chọn nhà xuất bản:</div>
            <select name="nha_xuat_ban" id="nha_xuat_ban" required>
               <option value="">-- Chọn nhà xuất bản --</option>
               <?php while ($nxb = mysqli_fetch_assoc($nxb_result)): ?>
                  <option value="<?php echo $nxb['nha_xuat_ban']; ?>"><?php echo $nxb['nha_xuat_ban']; ?></option>
               <?php endwhile; ?>
            </select>

            <input type="text" name="tac_gia" id="tac_gia" placeholder="Tác giả" required>

            <button type="submit" name="them_sach" id="submit_btn">Thêm mới</button>
         </form>
      </div>

      <div class="product-list">
         <table class="product-table">
            <thead>
               <tr>
                  <th>STT</th>
                  <th>Tên sản phẩm</th>
                  <th>Ảnh sản phẩm</th>
                  <th>Giá</th>
                  <th>Mô tả</th>
                  <th>Số lượng</th>
                  <th>Trạng thái</th>
                  <th>Hành động</th>
               </tr>
            </thead>
            <tbody>
               <?php
               $stt = 1;
               while ($sach = mysqli_fetch_assoc($sach_result)):
                  $trang_thai = ($sach['so_luong'] > 0) ? "Còn hàng" : "Hết hàng";
               ?>
                  <tr>
                     <td><?php echo $stt++; ?></td>
                     <td><?php echo $sach['tieu_de']; ?></td>
                     <td>
                        <?php if (!empty($sach['hinh_anh'])): ?>
                           <img src="../<?php echo $sach['hinh_anh']; ?>" alt="<?php echo $sach['tieu_de']; ?>" class="product-image" onerror="this.onerror=null; this.src='../images/no-image.jpg'; console.log('Lỗi tải ảnh: <?php echo $sach['hinh_anh']; ?>');">
                        <?php else: ?>
                           <span>Không có ảnh</span>
                        <?php endif; ?>
                     </td>
                     <td><?php echo number_format($sach['gia_tien'], 0, ',', '.'); ?> VND</td>
                     <td><?php echo substr($sach['mo_ta'], 0, 50) . (strlen($sach['mo_ta']) > 50 ? '...' : ''); ?></td>
                     <td><?php echo $sach['so_luong']; ?></td>
                     <td class="<?php echo $trang_thai == 'Còn hàng' ? 'con-hang' : 'het-hang'; ?>">
                        <?php echo $trang_thai; ?>
                     </td>
                     <td class="action-buttons">
                        <button class="edit-btn" onclick="editSach(<?php echo htmlspecialchars(json_encode($sach)); ?>)">Sửa</button>
                        <button class="delete-btn" onclick="deleteSach(<?php echo $sach['sach_id']; ?>)">Xóa</button>
                     </td>
                  </tr>
               <?php endwhile; ?>
            </tbody>
         </table>
      </div>
   </div>

   <script>
      // JavaScript để xử lý sự kiện menu
      document.querySelectorAll('.menu-item').forEach(item => {
         item.addEventListener('click', function(e) {
            e.preventDefault(); // Ngăn chặn hành vi mặc định của thẻ a (nếu có)

            const menuText = this.textContent.trim(); // Lấy nội dung của mục menu
            let page = '';

            // Ánh xạ tên menu với tên file PHP
            switch (menuText) {
               case 'Thể loại':
                  page = 'loai_sach.php';
                  break;
               case 'Sách':
                  page = 'edit_sach.php';
                  break;
               case 'Tài khoản':
                  page = 'tai_khoan.php';
                  break;
               case 'Hóa đơn':
                  page = 'hoa_don.php';
                  break;
               case 'Bảo hành':
                  page = 'bao_hanh.php';
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
      function editSach(sach) {
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
      function deleteSach(sachId) {
         if (confirm('Bạn có chắc muốn xóa sách này?')) {
            window.location.href = 'edit_sach.php?delete_id=' + sachId;
         }
      }
   </script>
</body>

</html>