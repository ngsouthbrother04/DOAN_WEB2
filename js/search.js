document.addEventListener("DOMContentLoaded", () => {
  // Lấy các form tìm kiếm
  const searchForms = document.querySelectorAll(
    'form[action="timsanpham.php"]'
  );

  searchForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      // Lấy giá trị từ ô tìm kiếm
      const searchInput = this.querySelector('input[name="search_term"]');
      const searchTerm = searchInput.value.trim();

      // Kiểm tra nếu không có dữ liệu tìm kiếm
      if (searchTerm === "") {
        e.preventDefault(); // Ngăn chặn form submit

        // Hiển thị thông báo lỗi
        alert("Vui lòng nhập từ khóa tìm kiếm!");
        searchInput.focus();
      }
    });
  });
});
