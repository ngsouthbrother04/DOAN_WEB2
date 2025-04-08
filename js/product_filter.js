document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded - checking filter functionality");

  const filterForm = document.getElementById("filter-form");
  const categorySelect = document.getElementById("category");
  const priceMinInput = document.getElementById("price-min");
  const priceMaxInput = document.getElementById("price-max");
  const sortOrderSelect = document.getElementById("sort-order");
  const searchForm = document.getElementById("search-form");
  const productItems = document.querySelectorAll(".product-item");
  const paginationContainer = document.querySelector(".pagination");
  const productList = document.getElementById("product-list");

  if (filterForm) {
    console.log("Filter form found");

    filterForm.addEventListener("submit", function (e) {
      console.log("Filter form submitted");
      // Không ngăn chặn submit để form được gửi đến server
    });

    console.log("Category select exists:", !!categorySelect);
    console.log("Price min input exists:", !!priceMinInput);
    console.log("Price max input exists:", !!priceMaxInput);
    console.log("Sort order select exists:", !!sortOrderSelect);
  } else {
    console.error("Filter form not found!");
  }

  if (searchForm) {
    console.log("Search form found");
  } else {
    console.error("Search form not found!");
  }

  // Hàm lọc và sắp xếp sản phẩm
  function filterAndSortProducts() {
    if (
      !categorySelect ||
      !priceMinInput ||
      !priceMaxInput ||
      !sortOrderSelect
    ) {
      console.error("Một hoặc nhiều phần tử không tồn tại");
      return;
    }

    const category = categorySelect.value;
    const priceMin = priceMinInput.value ? parseFloat(priceMinInput.value) : 0;
    const priceMax = priceMaxInput.value
      ? parseFloat(priceMaxInput.value)
      : Infinity;
    const sortOrder = sortOrderSelect.value;

    let visibleProducts = Array.from(productItems).filter((item) => {
      const itemCategory = item.getAttribute("data-category");
      const itemPrice = parseFloat(item.getAttribute("data-price")) || 0;
      const categoryMatch = !category || itemCategory === category;
      const priceMatch = itemPrice >= priceMin && itemPrice <= priceMax;

      return categoryMatch && priceMatch;
    });

    if (sortOrder && sortOrder !== "default") {
      visibleProducts.sort((a, b) => {
        const priceA = parseFloat(a.getAttribute("data-price")) || 0;
        const priceB = parseFloat(b.getAttribute("data-price")) || 0;
        const titleA = a.getAttribute("data-title")?.toLowerCase() || "";
        const titleB = b.getAttribute("data-title")?.toLowerCase() || "";

        switch (sortOrder) {
          case "asc":
            return priceA - priceB;
          case "desc":
            return priceB - priceA;
          case "alpha-asc":
            return titleA.localeCompare(titleB);
          case "alpha-desc":
            return titleB.localeCompare(titleA);
          default:
            return 0;
        }
      });
    }

    productItems.forEach((item) => (item.style.display = "none"));
    visibleProducts.forEach((item) => (item.style.display = "block"));

    const noProductsMessage = document.getElementById("no-products-message");
    if (visibleProducts.length === 0) {
      if (!noProductsMessage) {
        const message = document.createElement("p");

        message.id = "no-products-message";
        message.textContent = "Không tìm thấy sản phẩm phù hợp.";
        message.style.textAlign = "center";
        message.style.padding = "20px";
        productList.appendChild(message);
      }
      if (paginationContainer) paginationContainer.style.display = "none";
    } else {
      if (noProductsMessage) noProductsMessage.remove();
      if (paginationContainer) paginationContainer.style.display = "flex";
    }
  }

  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      filterAndSortProducts();
    });

    [categorySelect, priceMinInput, priceMaxInput, sortOrderSelect].forEach(
      (element) => {
        element.addEventListener("change", filterAndSortProducts);
      }
    );
  }

  // Sự kiện form tìm kiếm nâng cao
  const advanceSearchForm = document.getElementById("filter-search");
  const categorySearchSelect = document.getElementById("category-search");
  const priceMinSearchInput = document.getElementById("price-min-search");
  const priceMaxSearchInput = document.getElementById("price-max-search");

  if (advanceSearchForm) {
    advanceSearchForm.addEventListener("change", () => {
      if (categorySearchSelect && categorySelect)
        categorySelect.value = categorySearchSelect.value;
      if (priceMinSearchInput && priceMinInput)
        priceMinInput.value = priceMinSearchInput.value;
      if (priceMaxSearchInput && priceMaxInput)
        priceMaxInput.value = priceMaxSearchInput.value;
      filterAndSortProducts();
    });
  }

  // Sự kiện tìm kiếm bằng từ khóa
  const searchInput = document.querySelector(".search-input");
  const searchButton = document.querySelector(".search-button");

  if (searchInput && searchButton) {
    searchButton.addEventListener("click", () => {
      const searchTerm = searchInput.value.toLowerCase().trim();

      productItems.forEach((item) => {
        const title = item.getAttribute("data-title")?.toLowerCase() || "";
        item.style.display = title.includes(searchTerm) ? "block" : "none";
      });

      const visibleProducts = Array.from(productItems).filter(
        (item) => item.style.display !== "none"
      );
      const noProductsMessage = document.getElementById("no-products-message");

      if (visibleProducts.length === 0) {
        if (!noProductsMessage) {
          const message = document.createElement("p");

          message.id = "no-products-message";
          message.textContent = "Không tìm thấy sản phẩm phù hợp với từ khóa.";
          message.style.textAlign = "center";
          message.style.padding = "20px";
          productList.appendChild(message);
        }
        if (paginationContainer) paginationContainer.style.display = "none";
      } else {
        if (noProductsMessage) noProductsMessage.remove();
        if (paginationContainer) paginationContainer.style.display = "flex";
      }
    });

    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        searchButton.click();
      }
    });
  }

  // Hiển thị/ẩn tìm kiếm nâng cao
  const advanceSearch = document.querySelector(".advance_search");

  if (searchInput && advanceSearch) {
    searchInput.addEventListener(
      "focus",
      () => (advanceSearch.style.display = "block")
    );

    document.addEventListener("click", (e) => {
      if (!advanceSearch.contains(e.target) && e.target !== searchInput)
        advanceSearch.style.display = "none";
    });
  }

  // Đồng bộ giữa form lọc chính và form tìm kiếm nâng cao
  if (categorySelect && categorySearchSelect)
    categorySelect.addEventListener("change", function () {
      categorySearchSelect.value = this.value;
    });

  if (priceMinInput && priceMinSearchInput)
    priceMinInput.addEventListener("input", function () {
      priceMinSearchInput.value = this.value;
    });

  if (priceMaxInput && priceMaxSearchInput)
    priceMaxInput.addEventListener("input", function () {
      priceMaxSearchInput.value = this.value;
    });

  // Đồng bộ từ form tìm kiếm nâng cao sang form lọc chính
  if (categorySearchSelect && categorySelect)
    categorySearchSelect.addEventListener("change", function () {
      categorySelect.value = this.value;
    });

  if (priceMinSearchInput && priceMinInput)
    priceMinSearchInput.addEventListener("input", function () {
      priceMinInput.value = this.value;
    });

  if (priceMaxSearchInput && priceMaxInput)
    priceMaxSearchInput.addEventListener("input", function () {
      priceMaxInput.value = this.value;
    });

  // Xử lý nút reset bộ lọc
  const resetFilterBtn = document.querySelector(".reset-filter");
  if (resetFilterBtn)
    resetFilterBtn.addEventListener("click", (e) => {
      e.preventDefault();
      window.location.href = "trangchu.php";
    });

  // Xử lý thêm vào giỏ hàng
  const addToCartBtns = document.querySelectorAll(".add-to-cart-btn");
  addToCartBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const productItem = this.closest(".product-item");
      const productId = productItem.getAttribute("data-id");
      const productTitle = productItem.getAttribute("data-title");

      // Gửi yêu cầu Ajax để thêm vào giỏ hàng
      fetch("add_to_cart.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `product_id=${productId}&quantity=1`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert(`Đã thêm "${productTitle}" vào giỏ hàng!`);
          } else {
            alert(data.message || "Có lỗi xảy ra khi thêm vào giỏ hàng.");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Có lỗi xảy ra khi thêm vào giỏ hàng.");
        });
    });
  });

  // Xử lý nút mua ngay
  const buyNowBtns = document.querySelectorAll(".buy-now-btn");
  buyNowBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const productItem = this.closest(".product-item");
      const productId = productItem.getAttribute("data-id");

      // Chuyển hướng đến trang thanh toán với sản phẩm đã chọn
      window.location.href = `checkout.php?product_id=${productId}&quantity=1`;
    });
  });
});
