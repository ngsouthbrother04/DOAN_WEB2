const itemsPerPage = 6;
let currentPage = 1;
let filteredProducts = [];

const urlParams = new URLSearchParams(window.location.search);
let category = urlParams.get("category");

if (category) category = category.toLowerCase();

console.log("Danh mục từ URL:", category);

function displayProducts(productArray, page = 1) {
  const productList = document.getElementById("product-list");
  if (!productList) {
    console.error("Không tìm thấy phần tử #product-list trong DOM");
    return;
  }
  productList.innerHTML = "";

  if (!productArray || !Array.isArray(productArray)) {
    console.error("Dữ liệu productArray không tồn tại hoặc không phải là mảng");
    productList.innerHTML = "<p>Lỗi: Không thể tải dữ liệu sản phẩm.</p>";
    return;
  }

  if (productArray.length === 0) {
    productList.innerHTML = "<p>Không có sản phẩm nào trong danh mục này.</p>";
    return;
  }

  const startIndex = (page - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const pageProducts = productArray.slice(startIndex, endIndex);

  pageProducts.forEach((product) => {
    const productDiv = document.createElement("div");
    productDiv.className = "product-item";
    productDiv.innerHTML = `
            <a href="chitietsanpham.html?id=${product.id}" target="_blank">
                <img src="${product.image}" alt="${
      product.name
    }" class="product-image">
                <h3>${product.name}</h3>
                <p>Giá: ${product.price.toLocaleString("vi-VN")} VND</p>
            </a>
            <div class="button-container">
                <button class="btn btn-primary">Thêm</button>
                <button class="btn btn-secondary">Mua</button>
            </div>
        `;
    productList.appendChild(productDiv);
  });

  createPaginationControls(productArray);
}

const createPaginationControls = (productArray) => {
  const totalPages = Math.ceil(productArray.length / itemsPerPage);
  const existingPagination = document.querySelector(".pagination-controls");

  if (existingPagination) existingPagination.remove();

  if (totalPages <= 1) return;

  const paginationDiv = document.createElement("div");
  paginationDiv.className = "pagination-controls";
  paginationDiv.style.cssText = "margin-top: 20px; text-align: center;";

  paginationDiv.innerHTML = `
        <button onclick="previousPage()" ${
          currentPage === 1 ? "disabled" : ""
        }>Trước</button>
        <span>Trang ${currentPage} / ${totalPages}</span>
        <button onclick="nextPage()" ${
          currentPage === totalPages ? "disabled" : ""
        }>Tiếp</button>
    `;

  document.querySelector(".product-section").appendChild(paginationDiv);
};

const filterProducts = () => {
  if (filterCategory !== "" && filterCategory !== category) {
    window.location.href = `category.html?category=${filterCategory}`;
    return;
  }

  const filterCategory = document
    .getElementById("category")
    .value.toLowerCase();
  const priceMin = parseInt(document.getElementById("price-min").value) || 0;
  const priceMax =
    parseInt(document.getElementById("price-max").value) || Infinity;
  const sortOrder = document.getElementById("sort-order").value;

  let tempProducts = products.filter((product) => {
    const productCategory = product.category
      ? product.category.toLowerCase()
      : "";
    return productCategory === category;
  });

  if (filterCategory !== "") {
    tempProducts = tempProducts.filter((product) => {
      const productCategory = product.category
        ? product.category.toLowerCase()
        : "";
      return productCategory === filterCategory;
    });
  }

  filteredProducts = tempProducts.filter((product) => {
    const matchesPrice = product.price >= priceMin && product.price <= priceMax;
    return matchesPrice;
  });

  if (sortOrder === "asc") {
    filteredProducts.sort((a, b) => a.price - b.price);
  } else if (sortOrder === "desc") {
    filteredProducts.sort((a, b) => b.price - a.price);
  } else if (sortOrder === "alpha-asc") {
    filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
  } else if (sortOrder === "alpha-desc") {
    filteredProducts.sort((a, b) => b.name.localeCompare(a.name));
  }

  currentPage = 1;
  displayProducts(filteredProducts, currentPage);
};

const previousPage = () => {
  if (currentPage > 1) {
    currentPage--;
    displayProducts(filteredProducts, currentPage);
  }
};

const nextPage = () => {
  const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);

  if (currentPage < totalPages) {
    currentPage++;
    displayProducts(filteredProducts, currentPage);
  }
};

window.addEventListener("load", () => {
  if (!category) {
    document.getElementById("product-list").innerHTML =
      "<p>Không có danh mục được chọn.</p>";
    return;
  }

  document.querySelector(
    ".product-section h2"
  ).textContent = `Sản phẩm - ${category.replace("-", " ").toUpperCase()}`;

  filteredProducts = products.filter((product) => {
    const productCategory = product.category
      ? product.category.toLowerCase()
      : "";
    return productCategory === category;
  });

  displayProducts(filteredProducts, currentPage);

  const categorySelect = document.getElementById("category");

  if (categorySelect) categorySelect.value = category;
});

document.getElementById("filter-form").addEventListener("submit", (e) => {
  e.preventDefault();
  filterProducts();
});
