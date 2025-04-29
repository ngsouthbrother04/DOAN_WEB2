<div class="login-container" id="login-container">
    <div class="tabs">
        <a href="#" class="active" id="login-tab">Đăng nhập</a>
        <a href="#" id="register-tab">Đăng ký</a>
    </div>
    <!-- Form Đăng nhập -->
    <div class="login-form" id="login-form">
        <div id="login-message"></div>
        <form id="login-form-submit" method="post">
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" id="login-username" name="username" placeholder="Nhập số điện thoại"
                    style="border: 1px solid #ccc; outline: none;" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu"
                        style="border: none; outline: none;" required>
                    <button type="button" class="show-password" id="togglePassword">Hiện</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Đăng nhập</button>
            <button type="button" class="btn btn-secondary" id="exit">Thoát</button>
        </form>
    </div>

    <!-- Form Đăng ký -->
    <div class="register-form" id="register-form" style="display: none;">
        <form class="register-form-submit" method="post" id="register-form-submit">
            <div class="register-form-content">
                <!-- Cột trái -->
                <div class="form-left">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" id="full-name" placeholder="Nhập họ và tên"
                            style="border: 1px solid #ccc; outline: none; width: 200px;">
                    </div>

                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" id="dob"
                            style="border: 1px solid #ccc; outline: none; width: 200px;">
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <input type="text" id="address" placeholder="Nhập địa chỉ"
                            style="border: 1px solid #ccc; outline: none; width: 200px;">
                    </div>
                </div>

                <!-- Đường kẻ dọc -->
                <div class="vertical-line"></div>

                <!-- Cột phải -->
                <div class="form-right register-info">
                    <div class="form-group">
                        <label>Tên đăng nhập (Số điện thoại)</label>
                        <input type="text" id="username" placeholder="Nhập số điện thoại"
                            style="border: 1px solid #ccc; outline: none;">
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
                            <input type="password" id="reg-password" placeholder="Nhập mật khẩu"
                                style="border: none; outline: none;">
                            <button type="button" class="show-password" id="toggleRegPassword">Hiện</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Xác nhận mật khẩu</label>
                        <div class="password-container" style="border: 1px solid #ccc; border-radius: 5px;">
                            <input type="password" id="confirm-password" placeholder="Xác nhận mật khẩu"
                                style="border: none; outline: none;">
                            <button type="button" class="show-password" id="toggleConfirmPassword">Hiện</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group-buttons">
                <button type="submit" class="btn btn-primary">Đăng ký</button>
                <button type="button" class="btn btn-secondary" id="exit-reg">Thoát</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal"></div>

<script src="../js/account.js"></script>