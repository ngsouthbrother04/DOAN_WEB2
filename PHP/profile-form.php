<!-- Hiển thị thông tin người dùng đã đăng nhập -->
<?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])): ?>
    <div id="user-info-container" style="display: none;">
        <div class="user-info">
            <div class="user-info-header">
                <h1>Thông Tin Cá Nhân</h1>
                <button class="btn-edit-user-info">Chỉnh sửa</button>
            </div>

            <hr style="margin: 20px">

            <div class="user-info-item user-name">
                <h3>Họ và tên</h3>
                <span><?php echo $_SESSION['user_name']; ?></span>
            </div>

            <div class="user-info-item user-dob">
                <h3>Ngày sinh</h3>
                <span><?php echo $_SESSION['user_dob']; ?></span>
            </div>

            <div class="user-info-item user-phone">
                <h3>Số điện thoại</h3>
                <span><?php echo $_SESSION['user_phone']; ?></span>
            </div>

            <div class="user-info-item user-address">
                <h3>Địa chỉ</h3>
                <span><?php echo $_SESSION['user_address']; ?></span>
            </div>

            <div class="user-info-item user-actions">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                    <a href="admin.php" class="btn">Quản trị</a>
                <?php endif; ?>

                <a href="login-register/login.php?action=logout" class="btn-log-out">Đăng xuất</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="../js/edit-profile.js"></script>