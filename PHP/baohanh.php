<?php
session_start();
require_once 'db_connect.php';

// Giả định user_id từ session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Nếu người dùng đã đăng nhập, truy vấn danh sách đơn hàng
if ($user_id) {
  $sql_orders = "SELECT donhang_id, ngay_dat, tong_tien, trang_thai
                   FROM DONHANG
                   WHERE user_id = ? AND trang_thai = 'da_duoc_giao'
                   ORDER BY ngay_dat DESC";
  $stmt_orders = $conn->prepare($sql_orders);
  $stmt_orders->bind_param("i", $user_id);
  $stmt_orders->execute();
  $result_orders = $stmt_orders->get_result();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yêu Cầu Bảo Hành - WEB2 BookStore</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=Roboto" rel="stylesheet">
  <link rel="stylesheet" href="../CSS/index.css">
  <link rel="stylesheet" href="../CSS/product.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
    }

    .main {
      max-width: 1000px;
      margin: 0 auto;
      padding: 20px;
      padding-top: 150px;
      background-color: #f9f9f9;
      box-sizing: border-box;
    }

    .product-section {
      width: 900px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin: 0 auto;
      margin-bottom: 30px;
    }

    .product-section h2 {
      color: #c22432;
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 15px;
      border-left: 4px solid #c22432;
      padding-left: 6px;
      border-radius: 2px;
    }

    .product-section h3 {
      font-size: 18px;
      font-weight: 600;
      margin: 15px 0;
      color: #333;
      text-align: center;
    }

    .warranty-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      border: 10px;
    }

    .warranty-table th,
    .warranty-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .warranty-table th {
      background-color: #f2f2f2;
      font-weight: 600;
      color: #333;
      text-align: center;
    }

    .warranty-table td {
      text-align: center;
    }

    .warranty-table tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .warranty-table tbody tr:hover {
      background-color: #f1f1f1;
      transition: background-color 0.3s ease;
    }

    .warranty-table tbody tr.disabled {
      opacity: 0.5;
      pointer-events: none;
    }

    .warranty-table a {
      color: #2196F3;
      text-decoration: none;
    }

    .warranty-table a:hover {
      text-decoration: underline;
    }

    .filter-section {
      width: 800px;
      background-color: #fff;
      margin: 0 auto;
    }

    .filter-group {
      margin-bottom: 15px;
    }

    .filter-group label {
      display: block;
      font-weight: 500;
      margin-bottom: 5px;
      color: #333;
      text-align: center;
    }

    .filter-group textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-family: 'Roboto', sans-serif;
      font-size: 14px;
      resize: vertical;
      box-sizing: border-box;
    }

    .filter-group textarea:focus {
      outline: none;
      border-color: #c22432;
      box-shadow: 0 0 5px rgba(194, 36, 50, 0.2);
    }

    .warranty-table input[type="radio"] {
      appearance: none;
      width: 16px;
      height: 16px;
      border: 2px solid #c22432;
      border-radius: 50%;
      position: relative;
      cursor: pointer;
      margin: 0 auto;
      display: block;
    }

    .warranty-table input[type="radio"]:checked::before {
      content: '';
      width: 8px;
      height: 8px;
      background-color: #c22432;
      border-radius: 50%;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .warranty-table input[type="radio"]:disabled {
      border-color: #ccc;
      cursor: not-allowed;
    }

    .filter-buttons {
      text-align: center;
    }

    .filter-buttons button {
      width: 200px;
      background-color: #c22432;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      font-family: 'Roboto', sans-serif;
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin: 0 auto;
    }

    .filter-buttons button:hover {
      border: 1px solid #c22432;
    }

    .filter-buttons button:disabled {
      background-color: #cccccc;
      cursor: not-allowed;
    }

    .error {
      max-width: 600px;
      margin: 0 auto 15px auto;
      padding: 15px;
      background-color: #ffe6e6;
      color: #c22432;
      border-radius: 4px;
      font-size: 14px;
      text-align: center;
      display: none;
    }

    .success {
      max-width: 600px;
      margin: 0 auto 15px auto;
      padding: 15px;
      background-color: #e6ffe6;
      color: #27ae60;
      border-radius: 4px;
      font-size: 14px;
      text-align: center;
      display: none;
    }

    .login-prompt {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .login-prompt h2 {
      color: #c22432;
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 15px;
    }

    .login-prompt p {
      font-size: 16px;
      color: #333;
      margin-bottom: 20px;
    }

    .login-prompt a {
      display: inline-block;
      background-color: #c22432;
      color: white;
      padding: 10px 20px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 14px;
    }

    .login-prompt a:hover {
      background-color: #a71d2a;
    }

    /* Trạng thái bảo hành */
    .status-rejected {
      color: #c22432;
      font-weight: 500;
    }

    .status-pending {
      color: #FFA500;
      font-weight: 500;
    }

    .status-completed {
      color: #2ecc71;
      font-weight: 500;
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
  <?php include 'header.php'; ?>

  <div class="main">
    <?php if (!$user_id): ?>
      <!-- Hiển thị thông báo nếu người dùng chưa đăng nhập -->
      <div class="login-prompt">
        <h2>Đăng Nhập Yêu Cầu</h2>
        <p>Vui lòng đăng nhập để xem và gửi yêu cầu bảo hành.</p>
        <a href="javascript:void(0);" onclick="document.querySelector('.userbutton').click();">Đăng Nhập Ngay</a>
      </div>
    <?php else: ?>
      <!-- Danh sách đơn hàng -->
      <section class="product-section">
        <h2>Danh Sách Đơn Hàng</h2>
        <?php
        // Truy vấn tất cả đơn hàng của người dùng
        $sql_orders = "SELECT donhang_id, ngay_dat, tong_tien, trang_thai
                       FROM DONHANG
                       WHERE user_id = ?
                       ORDER BY ngay_dat DESC";
        $stmt_orders = $conn->prepare($sql_orders);
        $stmt_orders->bind_param("i", $user_id);
        $stmt_orders->execute();
        $result_orders = $stmt_orders->get_result();
        ?>

        <?php if ($result_orders->num_rows > 0): ?>
          <table class="warranty-table">
            <thead>
              <tr>
                <th>ID Đơn Hàng</th>
                <th>Ngày Đặt</th>
                <th>Tổng Tiền</th>
                <th>Trạng Thái</th>
                <th>Chi Tiết</th>
                <th>Bảo Hành</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($order = $result_orders->fetch_assoc()): ?>
                <?php
                // Chuyển trạng thái không dấu sang có dấu và thêm màu sắc
                $trang_thai_display = '';
                $order_status_class = '';
                switch ($order['trang_thai']) {
                  case 'da_duoc_giao':
                    $trang_thai_display = 'Đã được giao';
                    $order_status_class = 'order-status-delivered';
                    break;
                  case 'cho_xac_nhan':
                    $trang_thai_display = 'Chờ xác nhận';
                    $order_status_class = 'order-status-pending';
                    break;
                  case 'da_xac_nhan':
                    $trang_thai_display = 'Đã xác nhận';
                    $order_status_class = 'order-status-confirmed';
                    break;
                  case 'da_bi_huy':
                    $trang_thai_display = 'Đã bị hủy';
                    $order_status_class = 'order-status-canceled';
                    break;
                  default:
                    $trang_thai_display = $order['trang_thai'];
                    $order_status_class = '';
                }
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($order['donhang_id']); ?></td>
                  <td><?php echo htmlspecialchars($order['ngay_dat']); ?></td>
                  <td><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> VNĐ</td>
                  <td><span class="<?php echo $order_status_class; ?>"><?php echo htmlspecialchars($trang_thai_display); ?></span></td>
                  <td><a href="javascript:void(0);" class="view-details" data-donhang-id="<?php echo $order['donhang_id']; ?>">Xem chi tiết</a></td>
                  <td>
                    <?php if ($order['trang_thai'] === 'da_duoc_giao'): ?>
                      <a href="?donhang_id=<?php echo $order['donhang_id']; ?>">Bảo hành</a>
                    <?php else: ?>
                      <span style="color: #999;">Bảo hành</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="error" style="display: block;">Bạn chưa có đơn hàng nào.</p>
        <?php endif; ?>
        <?php $stmt_orders->close(); ?>
      </section>

      <!-- Modal hiển thị chi tiết đơn hàng -->
      <div id="order-details-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="background: #fff; width: 600px; margin: 100px auto; padding: 20px; border-radius: 8px; position: relative;">
          <h3 style="margin-top: 0;">Chi Tiết Đơn Hàng</h3>
          <table class="warranty-table" id="order-details-table">
            <thead>
              <tr>
                <th>Tên Sách</th>
                <th>Đơn Giá</th>
                <th>Số Lượng</th>
                <th>Tổng Tiền</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <button onclick="closeModal()" style="margin-top: 20px; background-color: #c22432; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Đóng</button>
        </div>
      </div>

      <script>
        $(document).ready(function() {
          // Xử lý khi nhấn "Xem chi tiết"
          $('.view-details').on('click', function() {
            var donhang_id = $(this).data('donhang-id');

            // Gửi yêu cầu AJAX để lấy chi tiết đơn hàng
            $.ajax({
              url: 'get_order_details.php',
              method: 'POST',
              data: {
                donhang_id: donhang_id
              },
              dataType: 'json',
              success: function(response) {
                if (response.success) {
                  var tbody = $('#order-details-table tbody');
                  tbody.empty(); // Xóa nội dung cũ

                  // Thêm dữ liệu vào bảng
                  response.data.forEach(function(item) {
                    var row = '<tr>' +
                      '<td>' + item.tieu_de + '</td>' +
                      '<td>' + Number(item.gia_tien).toLocaleString('vi-VN') + ' VNĐ</td>' +
                      '<td>' + item.so_luong + '</td>' +
                      '<td>' + Number(item.tong_tien).toLocaleString('vi-VN') + ' VNĐ</td>' +
                      '</tr>';
                    tbody.append(row);
                  });

                  // Hiển thị modal
                  $('#order-details-modal').show();
                } else {
                  alert('Không thể lấy chi tiết đơn hàng: ' + response.message);
                }
              },
              error: function() {
                alert('Có lỗi xảy ra khi lấy chi tiết đơn hàng.');
              }
            });
          });
        });

        // Đóng modal
        function closeModal() {
          $('#order-details-modal').hide();
        }
      </script>

      <!-- Chọn bản sao để bảo hành -->
      <?php if (isset($_GET['donhang_id'])): ?>
        <?php
        $donhang_id = intval($_GET['donhang_id']);

        // Kiểm tra đơn hàng thuộc về người dùng và có trạng thái đã hoàn thành
        $sql_check = "SELECT user_id FROM DONHANG WHERE donhang_id = ? AND trang_thai = 'da_duoc_giao'";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $donhang_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows == 0 || $result_check->fetch_assoc()['user_id'] != $user_id) {
          echo "<p class='error' style='display: block;'>Đơn hàng không hợp lệ, không thuộc về bạn hoặc chưa hoàn thành.</p>";
          $stmt_check->close();
        } else {
          $stmt_check->close();

          // Truy vấn danh sách sách và bản sao trong đơn hàng
          $sql_books = "SELECT s.sach_id, s.tieu_de
                                  FROM CHITIETDONHANG ctd
                                  JOIN SACH s ON ctd.sach_id = s.sach_id
                                  WHERE ctd.donhang_id = ?
                                  GROUP BY s.sach_id";
          $stmt_books = $conn->prepare($sql_books);
          $stmt_books->bind_param("i", $donhang_id);
          $stmt_books->execute();
          $result_books = $stmt_books->get_result();
        ?>
          <section class="product-section">
            <h2>Chọn Sách Bạn Muốn Bảo Hành (Đơn Hàng #<?php echo $donhang_id; ?>)</h2>
            <div class="error"></div>
            <div class="success"></div>
            <?php if ($result_books->num_rows > 0): ?>
              <form id="warranty-form" class="filter-section">
                <input type="hidden" name="donhang_id" value="<?php echo $donhang_id; ?>">
                <?php while ($book = $result_books->fetch_assoc()): ?>
                  <?php
                  $sach_id = $book['sach_id'];
                  // Truy vấn danh sách bản sao và kiểm tra trạng thái bảo hành
                  $sql_copies = "SELECT cts.chitietsach_id,
                                                   CASE WHEN dbh.chitietsach_id IS NOT NULL THEN 1 ELSE 0 END AS has_warranty
                                                   FROM CHITIETSACH cts
                                                   JOIN CHITIETDONHANG ctd ON cts.chitietdonhang_id = ctd.chitiet_id
                                                   LEFT JOIN DONBAOHANH dbh ON cts.chitietsach_id = dbh.chitietsach_id
                                                   WHERE ctd.donhang_id = ? AND cts.sach_id = ?";
                  $stmt_copies = $conn->prepare($sql_copies);
                  $stmt_copies->bind_param("ii", $donhang_id, $sach_id);
                  $stmt_copies->execute();
                  $result_copies = $stmt_copies->get_result();
                  ?>
                  <h3><?php echo htmlspecialchars($book['tieu_de']); ?></h3>
                  <?php if ($result_copies->num_rows > 0): ?>
                    <table class="warranty-table">
                      <thead>
                        <tr>
                          <th>Chọn</th>
                          <th>Mã Sách</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($copy = $result_copies->fetch_assoc()): ?>
                          <tr data-chitietsach-id="<?php echo htmlspecialchars($copy['chitietsach_id']); ?>" class="<?php echo $copy['has_warranty'] ? 'disabled' : ''; ?>">
                            <td><input type="radio" name="chitietsach_id" value="<?php echo htmlspecialchars($copy['chitietsach_id']); ?>" <?php echo $copy['has_warranty'] ? 'disabled' : 'required'; ?>></td>
                            <td><?php echo htmlspecialchars($copy['chitietsach_id']); ?></td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  <?php else: ?>
                    <p class="error" style="display: block;">Không có bản sao nào cho sách này.</p>
                  <?php endif; ?>
                  <?php $stmt_copies->close(); ?>
                <?php endwhile; ?>
                <div class="filter-group">
                  <label style="padding-top: 20px;" for="ly_do">Lý do bảo hành:</label>
                  <textarea id="ly_do" name="ly_do" required></textarea>
                </div>
                <div class="filter-buttons">
                  <button type="submit">Gửi Yêu Cầu Bảo Hành</button>
                </div>
              </form>
            <?php else: ?>
              <p class="error" style="display: block;">Không tìm thấy sách nào trong đơn hàng này.</p>
            <?php endif; ?>
            <?php $stmt_books->close(); ?>
          </section>
        <?php } ?>
      <?php endif; ?>

      <!-- Lịch sử yêu cầu bảo hành -->
      <?php
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
      ?>
      <section class="product-section">
        <h2>Lịch Sử Yêu Cầu Bảo Hành</h2>
        <?php if ($result_warranties->num_rows > 0): ?>
          <table class="warranty-table">
            <thead>
              <tr>
                <th>ID-BH</th>
                <th>ID-DH</th>
                <th>Tên Sách</th>
                <th>Mã Bản Sao</th>
                <th>Lý Do</th>
                <th>Ngày</th>
                <th>Trạng Thái</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($warranty = $result_warranties->fetch_assoc()): ?>
                <?php
                // Chuyển trạng thái không dấu sang có dấu và thêm màu sắc
                $trang_thai_display = '';
                $status_class = '';
                switch ($warranty['trang_thai']) {
                  case 'Tu choi':
                    $trang_thai_display = 'Từ chối';
                    $status_class = 'status-rejected';
                    break;
                  case 'Chua hoan thanh':
                    $trang_thai_display = 'Chưa hoàn thành';
                    $status_class = 'status-pending';
                    break;
                  case 'Hoan thanh':
                    $trang_thai_display = 'Hoàn thành';
                    $status_class = 'status-completed';
                    break;
                  default:
                    $trang_thai_display = $warranty['trang_thai'];
                    $status_class = '';
                }
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($warranty['donbaohanh_id']); ?></td>
                  <td><?php echo htmlspecialchars($warranty['donhang_id']); ?></td>
                  <td><?php echo htmlspecialchars($warranty['tieu_de']); ?></td>
                  <td><?php echo htmlspecialchars($warranty['chitietsach_id']); ?></td>
                  <td><?php echo htmlspecialchars($warranty['ly_do']); ?></td>
                  <td><?php echo htmlspecialchars($warranty['ngay']); ?></td>
                  <td><span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($trang_thai_display); ?></span></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="error" style="display: block;">Chưa có yêu cầu bảo hành nào.</p>
        <?php endif; ?>
        <?php $stmt_warranties->close(); ?>
      </section>
    <?php endif; ?>
  </div>

  <script>
    $(document).ready(function() {
      $('#warranty-form').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var button = form.find('button[type="submit"]');
        var errorDiv = form.closest('.product-section').find('.error');
        var successDiv = form.closest('.product-section').find('.success');

        // Ẩn thông báo trước đó
        errorDiv.hide();
        successDiv.hide();

        // Vô hiệu hóa nút gửi
        button.prop('disabled', true).text('Đang gửi...');

        $.ajax({
          url: 'submit_warranty.php',
          method: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              // Hiển thị thông báo thành công
              successDiv.text(response.message).show();

              // Làm mờ bản sao vừa gửi
              var chitietsach_id = form.find('input[name="chitietsach_id"]:checked').val();
              var row = form.find('tr[data-chitietsach-id="' + chitietsach_id + '"]');
              row.addClass('disabled');
              row.find('input[name="chitietsach_id"]').prop('disabled', true);

              // Reset form
              form.find('input[name="chitietsach_id"]').prop('checked', false);
              form.find('textarea[name="ly_do"]').val('');
            } else {
              // Hiển thị thông báo lỗi
              errorDiv.text(response.message).show();
            }
          },
          error: function() {
            errorDiv.text('Có lỗi xảy ra. Vui lòng thử lại sau.').show();
          },
          complete: function() {
            // Kích hoạt lại nút gửi
            button.prop('disabled', false).text('Gửi Yêu Cầu Bảo Hành');
          }
        });
      });
    });
  </script>
  <?php include 'login-register/login-register-form.php'; ?>
  <?php include 'profile-form.php'; ?>
  <?php include 'footer.php'; ?>

  <script src="../js/search.js"></script>
  <?php $conn->close(); ?>
</body>

</html>