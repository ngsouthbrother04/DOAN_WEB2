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
    document.getElementById("modal").classList.add("active");
    document.getElementById("login-container").style.display = "block";
});

document.getElementById("modal").addEventListener("click", function (event) {
    if (event.target === this) {
        this.classList.remove("active");
        document.getElementById("login-container").style.display = "none";
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