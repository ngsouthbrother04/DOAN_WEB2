const loginTab = document.getElementById("login-tab");
const registerTab = document.getElementById("register-tab");
const loginForm = document.getElementById("login-form");
const registerForm = document.getElementById("register-form");

document
  .getElementById("togglePassword")
  .addEventListener("click", function () {
    let passwordInput = document.getElementById("password");
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      this.textContent = "Ẩn";
    } else {
      passwordInput.type = "password";
      this.textContent = "Hiện";
    }
  });

document
  .getElementById("toggleRegPassword")
  .addEventListener("click", function () {
    let regPasswordInput = document.getElementById("reg-password");
    if (regPasswordInput.type === "password") {
      regPasswordInput.type = "text";
      this.textContent = "Ẩn";
    } else {
      regPasswordInput.type = "password";
      this.textContent = "Hiện";
    }
  });

document
  .getElementById("toggleConfirmPassword")
  .addEventListener("click", function () {
    let confirmPasswordInput = document.getElementById("confirm-password");
    if (confirmPasswordInput.type === "password") {
      confirmPasswordInput.type = "text";
      this.textContent = "Ẩn";
    } else {
      confirmPasswordInput.type = "password";
      this.textContent = "Hiện";
    }
  });

const resetPasswordToggle = () => {
  document.getElementById("password").type = "password";
  document.getElementById("reg-password").type = "password";
  document.getElementById("confirm-password").type = "password";

  document.getElementById("togglePassword").textContent = "Hiện";
  document.getElementById("toggleRegPassword").textContent = "Hiện";
  document.getElementById("toggleConfirmPassword").textContent = "Hiện";
};

document.querySelector(".userbutton").addEventListener("click", () => {
  document.body.style.overflow = "hidden";

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
    document.body.style.overflow = "auto";
    this.classList.remove("active");
    document.getElementById("login-container").style.display = "none";

    resetPasswordToggle();
    resetRegisterForm();
    resetLoginForm();

    const messageDiv = document.getElementById("login-message");
    messageDiv.innerHTML = "";

    loginTab.classList.add("active");
    registerTab.classList.remove("active");
    loginForm.style.display = "block";
    registerForm.style.display = "none";

    let loginContainer = document.querySelector(".login-container");
    loginContainer.style.width = "400px";
    loginContainer.style.height = "auto";
  }
});

document.getElementById("exit").addEventListener("click", () => {
  document.body.style.overflow = "auto";
  document.getElementById("modal").classList.remove("active");
  document.getElementById("login-container").style.display = "none";

  const messageDiv = document.getElementById("login-message");
  messageDiv.innerHTML = "";

  resetPasswordToggle();
  resetRegisterForm();
  resetLoginForm();
});

document.getElementById("exit-reg").addEventListener("click", () => {
  document.body.style.overflow = "auto";
  document.getElementById("modal").classList.remove("active");
  document.getElementById("login-container").style.display = "none";

  resetPasswordToggle();
  resetRegisterForm();
  resetLoginForm();

  loginTab.classList.add("active");
  registerTab.classList.remove("active");
  loginForm.style.display = "block";
  registerForm.style.display = "none";

  let loginContainer = document.querySelector(".login-container");
  loginContainer.style.width = "400px";
  loginContainer.style.height = "auto";
});

const resetLoginForm = () => {
  document.getElementById("login-username").value = "";
  document.getElementById("password").value = "";
};

const resetRegisterForm = () => {
  document.getElementById("full-name").value = "";
  document.getElementById("address").value = "";
  document.getElementById("dob").value = "";
  document.getElementById("username").value = "";
  document.getElementById("reg-password").value = "";
  document.getElementById("confirm-password").value = "";
};

const loginContainer = document.querySelector(".login-container");

loginTab.addEventListener("click", (e) => {
  e.preventDefault();
  loginContainer.style.width = "400px";
  loginContainer.style.height = "auto";

  loginTab.classList.add("active");
  registerTab.classList.remove("active");
  loginForm.style.display = "block";
  registerForm.style.display = "none";

  resetPasswordToggle();
  resetRegisterForm();
});

registerTab.addEventListener("click", (e) => {
  e.preventDefault();
  registerTab.classList.add("active");
  loginTab.classList.remove("active");
  registerForm.style.display = "block";
  loginForm.style.display = "none";

  resetPasswordToggle();
  resetLoginForm();

  loginContainer.style.width = "600px";
  loginContainer.style.height = "470px";
});

// Xử lý đăng nhập bằng AJAX
document
  .getElementById("login-form-submit")
  .addEventListener("submit", (event) => {
    event.preventDefault(); // Ngăn form submit theo cách thông thường

    const username = document.getElementById("login-username").value;
    const password = document.getElementById("password").value;
    const messageDiv = document.getElementById("login-message");

    // Tạo đối tượng FormData
    const formData = new FormData();
    formData.append("username", username);
    formData.append("password", password);

    // Gửi yêu cầu AJAX
    fetch("login-register/login.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Lỗi phản hồi từ server.");
        }
        return response.json();
      })
      .then((data) => {
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
      .catch((error) => {
        console.error("Lỗi:", error);
        messageDiv.innerHTML =
          '<div class="error-message">Đã xảy ra lỗi khi đăng nhập! Vui lòng thử lại.</div>';
      });
  });

document
  .getElementById("register-form-submit")
  .addEventListener("submit", (event) => {
    event.preventDefault(); // Ngừng gửi form theo cách thông thường

    const full_name = document.getElementById("full-name").value.trim();
    const address = document.getElementById("address").value.trim();
    const dob = document.getElementById("dob").value;
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("reg-password").value;
    const confirm_password = document.getElementById("confirm-password").value;

    // Kiểm tra các trường hợp nhập liệu cần thiết
    if (
      !full_name ||
      !address ||
      !dob ||
      !username ||
      !password ||
      !confirm_password
    ) {
      alert("Vui lòng điền đầy đủ thông tin.");
      return;
    }

    // Kiểm tra định dạng số điện thoại (phải là 10 hoặc 11 chữ số)
    const phoneRegex = /^[0-9]{10,11}$/;
    if (!phoneRegex.test(username)) {
      alert("Số điện thoại không hợp lệ! Vui lòng nhập 10 hoặc 11 chữ số.");
      return;
    }

    // Kiểm tra mật khẩu và xác nhận mật khẩu có khớp không
    if (password !== confirm_password) {
      alert("Mật khẩu xác nhận không khớp.");
      return;
    }

    const formData = new FormData();
    formData.append("full_name", full_name);
    formData.append("address", address);
    formData.append("dob", dob);
    formData.append("username", username);
    formData.append("password", password);
    formData.append("confirm_password", confirm_password);

    // Gửi yêu cầu AJAX
    fetch("login-register/register.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Đã xảy ra lỗi với phản hồi từ server.");
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          alert(data.message);
          resetRegisterForm();
          setTimeout(() => {
            document.getElementById("modal").classList.remove("active");
            document.getElementById("login-container").style.display = "none";
            window.location.reload();
          }, 1000);
        } else {
          alert(data.message);
        }
      })
      .catch((error) => {
        console.error("Lỗi chi tiết:", error);
        alert("Đã xảy ra lỗi khi đăng ký! Vui lòng thử lại.");
      });
  });