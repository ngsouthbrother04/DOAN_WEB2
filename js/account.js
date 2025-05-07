document.addEventListener("DOMContentLoaded", function () {
  const loginTab = document.getElementById("login-tab");
  const registerTab = document.getElementById("register-tab");
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");
  const loginContainer = document.querySelector(".login-container");

  // Tab switching
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

  // Password toggle functionality
  const togglePassword = (inputId, buttonId) => {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    button.addEventListener("click", function () {
      if (input.type === "password") {
        input.type = "text";
        this.textContent = "Ẩn";
      } else {
        input.type = "password";
        this.textContent = "Hiện";
      }
    });
  };

  togglePassword("password", "togglePassword");
  togglePassword("reg-password", "toggleRegPassword");
  togglePassword("confirm-password", "toggleConfirmPassword");

  // Reset password fields
  const resetPasswordToggle = () => {
    document.getElementById("password").type = "password";
    document.getElementById("reg-password").type = "password";
    document.getElementById("confirm-password").type = "password";
    document.getElementById("togglePassword").textContent = "Hiện";
    document.getElementById("toggleRegPassword").textContent = "Hiện";
    document.getElementById("toggleConfirmPassword").textContent = "Hiện";
  };

  // Reset forms
  const resetLoginForm = () => {
    document.getElementById("login-username").value = "";
    document.getElementById("password").value = "";
    document.getElementById("login-message").innerHTML = "";
  };

  const resetRegisterForm = () => {
    document.getElementById("full-name").value = "";
    document.getElementById("address").value = "";
    document.getElementById("dob").value = "";
    document.getElementById("username").value = "";
    document.getElementById("reg-password").value = "";
    document.getElementById("confirm-password").value = "";
    const registerMessage = document.getElementById("register-message");
    if (registerMessage) registerMessage.innerHTML = "";
  };

  // Modal and user button handling
  document.querySelector(".userbutton").addEventListener("click", () => {
    document.body.style.overflow = "hidden";
    const userInfoContainer = document.getElementById("user-info-container");
    if (userInfoContainer) {
      userInfoContainer.style.display = "block";
      document.getElementById("modal").classList.add("active");
    } else {
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
      loginTab.classList.add("active");
      registerTab.classList.remove("active");
      loginForm.style.display = "block";
      registerForm.style.display = "none";
      loginContainer.style.width = "400px";
      loginContainer.style.height = "auto";
    }
  });

  // Exit buttons
  document.getElementById("exit").addEventListener("click", () => {
    document.body.style.overflow = "auto";
    document.getElementById("modal").classList.remove("active");
    document.getElementById("login-container").style.display = "none";
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
    loginContainer.style.width = "400px";
    loginContainer.style.height = "auto";
  });

  // Login form submission
  document
    .getElementById("login-form-submit")
    .addEventListener("submit", (event) => {
      event.preventDefault();
      const username = document.getElementById("login-username").value;
      const password = document.getElementById("password").value;
      const messageDiv = document.getElementById("login-message");

      const formData = new FormData();
      formData.append("username", username);
      formData.append("password", password);

      fetch("login-register/login.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          messageDiv.style.color = data.success ? "green" : "red";
          messageDiv.style.marginTop = "10px";

          messageDiv.innerHTML = `<div class="${
            data.success ? "success-message" : "error-message"
          }">${data.message}</div>`;

          if (data.success) {
            setTimeout(() => {
              document.getElementById("modal").classList.remove("active");
              document.getElementById("login-container").style.display = "none";
        
              if (data.user_role && data.user_role === "Admin") {
                window.location.href = "admin.php";
              } else {
                window.location.reload();
              }
            }, 1000);
          }
        })
        .catch((error) => {
          console.error("Lỗi:", error);
          messageDiv.style.color = "red";
          messageDiv.style.marginTop = "10px";

          messageDiv.innerHTML =
            '<div class="error-message">Đã xảy ra lỗi khi đăng nhập!</div>';
        });
    });

  // Registration form submission
  document
    .getElementById("register-form-submit")
    .addEventListener("submit", (event) => {
      event.preventDefault();

      const full_name = document.getElementById("full-name").value.trim();
      const address = document.getElementById("address").value.trim();
      const dob = document.getElementById("dob").value;
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("reg-password").value;
      const confirm_password =
        document.getElementById("confirm-password").value;

      // Client-side validation
      if (
        !full_name ||
        !address ||
        !dob ||
        !username ||
        !password ||
        !confirm_password
      ) {
        showRegisterMessage("Vui lòng điền đầy đủ thông tin!", false);
        return;
      }

      if (!/^[0-9]{10,11}$/.test(username)) {
        showRegisterMessage("Số điện thoại không hợp lệ!", false);
        return;
      }

      if (password !== confirm_password) {
        showRegisterMessage("Mật khẩu xác nhận không khớp!", false);
        return;
      }

      const formData = new FormData();
      formData.append("full_name", full_name);
      formData.append("address", address);
      formData.append("dob", dob);
      formData.append("username", username);
      formData.append("password", password);
      formData.append("confirm_password", confirm_password);

      fetch("login-register/register.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            return response.text().then((text) => {
              throw new Error(`Server error: ${response.status} - ${text}`);
            });
          }
          return response.json();
        })
        .then((data) => {
          showRegisterMessage(data.message, data.success);

          if (data.success) {
            resetRegisterForm();
            setTimeout(() => {
              loginTab.click();
              document.getElementById("modal").classList.remove("active");
              document.getElementById("login-container").style.display = "none";

              window.location.reload();
            }, 1500);
          }
        })
        .catch((error) => {
          console.error("Lỗi chi tiết:", error);
          showRegisterMessage(
            "Đã xảy ra lỗi khi đăng ký! Vui lòng thử lại.",
            false
          );
        });
    });

  // Helper function to show registration messages
  const showRegisterMessage = (message, isSuccess) => {
    let messageDiv = document.getElementById("register-message");

    if (!messageDiv) {
      messageDiv = document.createElement("div");
      messageDiv.id = "register-message";
      registerForm.insertBefore(messageDiv, registerForm.firstChild);
      messageDiv.style.marginTop = "10px";
    }

    loginContainer.style.height = "520px";
    messageDiv.style.color = isSuccess ? "green" : "red";

    messageDiv.innerHTML = `<div class="${
      isSuccess ? "success-message" : "error-message"
    }">${message}</div>`;
  };
});
