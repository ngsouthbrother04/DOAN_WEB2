document.getElementById("togglePassword").addEventListener("click", function () {
    let passwordInput = document.getElementById("password");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        this.textContent = "Ẩn";
    } else {
        passwordInput.type = "password";
        this.textContent = "Hiện";
    }
});

document.getElementById("toggleRegPassword").addEventListener("click", function () {
    let regPasswordInput = document.getElementById("reg-password");
    if (regPasswordInput.type === "password") {
        regPasswordInput.type = "text";
        this.textContent = "Ẩn";
    } else {
        regPasswordInput.type = "password";
        this.textContent = "Hiện";
    }
});

document.getElementById("toggleConfirmPassword").addEventListener("click", function () {
    let confirmPasswordInput = document.getElementById("confirm-password");
    if (confirmPasswordInput.type === "password") {
        confirmPasswordInput.type = "text";
        this.textContent = "Ẩn";
    } else {
        confirmPasswordInput.type = "password";
        this.textContent = "Hiện";
    }
});

document.querySelector(".userbutton").addEventListener("click", function () {
    // Kiểm tra nếu container thông tin người dùng tồn tại
    const userInfoContainer = document.getElementById("user-info-container");
    
    if (userInfoContainer) {
        // Người dùng đã đăng nhập, hiển thị thông tin
        userInfoContainer.style.display = "block";
        document.getElementById("modal").classList.add("active");
    } else {
        // Người dùng chưa đăng nhập, hiển thị form đăng nhập
        document.getElementById("modal").classList.add("active");
        document.getElementById("login-container").style.display = "block";
    }
});

document.getElementById("modal").addEventListener("click", function (event) {
    if (event.target === this) {
        this.classList.remove("active");
        document.getElementById("login-container").style.display = "none";
        const userInfoContainer = document.getElementById("user-info-container");
        if (userInfoContainer) {
            userInfoContainer.style.display = "none";
        }
    }
});

document.getElementById("exit").addEventListener("click", function (event) {
    document.getElementById("modal").classList.remove("active");
    document.getElementById("login-container").style.display = "none";
});

document.getElementById("exit-reg").addEventListener("click", function (event) {
    document.getElementById("modal").classList.remove("active");
    document.getElementById("login-container").style.display = "none";
});

const loginTab = document.getElementById("login-tab");
const registerTab = document.getElementById("register-tab");
const loginForm = document.getElementById("login-form");
const registerForm = document.getElementById("register-form");

loginTab.addEventListener("click", function (e) {
    e.preventDefault();
    loginTab.classList.add("active");
    registerTab.classList.remove("active");
    loginForm.style.display = "block";
    registerForm.style.display = "none";
});

registerTab.addEventListener("click", function (e) {
    e.preventDefault();
    registerTab.classList.add("active");
    loginTab.classList.remove("active");
    registerForm.style.display = "block";
    loginForm.style.display = "none";
});

// Xử lý đăng nhập bằng AJAX
document.getElementById("login-form-submit").addEventListener("submit", function(event) {
    event.preventDefault(); // Ngăn form submit theo cách thông thường
    
    const username = document.getElementById("login-username").value;
    const password = document.getElementById("password").value;
    const messageDiv = document.getElementById("login-message");
    
    // Tạo đối tượng FormData
    const formData = new FormData();
    formData.append("username", username);
    formData.append("password", password);
    
    // Gửi yêu cầu AJAX
    fetch("login.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            messageDiv.innerHTML = `<div class="success-message">${data.message}</div>`;
            
            // Đóng modal sau 1 giây
            setTimeout(() => {
                document.getElementById("modal").classList.remove("active");
                document.getElementById("login-container").style.display = "none";
                
                // Tải lại trang để cập nhật trạng thái đăng nhập
                window.location.reload();
            }, 1000);
        } else {
            // Hiển thị thông báo lỗi
            messageDiv.innerHTML = `<div class="error-message">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error("Lỗi:", error);
        messageDiv.innerHTML = '<div class="error-message">Đã xảy ra lỗi khi đăng nhập!</div>';
    });
});