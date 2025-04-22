document.querySelectorAll('.increase-quantity').forEach(btn => {
    btn.addEventListener('click', function () {
      const container = this.closest('.so_luong');
      const input = container.querySelector('input[type="number"]');
      input.value = parseInt(input.value) + 1;
    });
  });
  
document.querySelectorAll('.decrease-quantity').forEach(btn => {
    btn.addEventListener('click', function () {
        const container = this.closest('.so_luong');
        const input = container.querySelector('input[type="number"]');
        if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const confirmBtn = document.querySelector(".cart-buttons .update-btn");
    const deletaAllBtn = document.querySelector(".cart-buttons .delete-btn");
    const form = confirmBtn.closest("form");

    confirmBtn.addEventListener("click", function (e) {
        const result = confirm("Xác nhận thay đổi?");
        if (!result) {
            e.preventDefault();
        }
    });
    deletaAllBtn.addEventListener("click", function (e) {
        const result = confirm("Bạn muốn xóa tất cả khỏi giỏ hàng?");
        if (!result) {
            e.preventDefault();
        }
    });
});