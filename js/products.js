const products = [
  {
    id: 1,
    name: "Trường học Biết Tuốt – Tập 14",
    price: 100000,
    category: "truyen-tranh",
    nhaxuatban: "Kim Đồng",
    tentacgia: "Không có",
    image: "/Picture/Products/truong-hoc-biet-tuot_tap-14_dinh-li-pythagore_bia_704cef68f09847559bb86379104402f3_master.webp"
  },
  {
    id: 2,
    name: "Thợ săn game rác - Tập 2",
    price: 150000,
    category: "truyen-tranh",
    nhaxuatban: "Kim Đồng",
    tentacgia: "Không có",
    image: "/Picture/Products/shangri-la-frontier_bia_tap-2_01e88f01fad1421f80c1bfdeaa99a1e7_large.webp"
  },
  {
    id: 3,
    name: "Hiểu quy luật tâm lý tăng cường khả năng học tập",
    price: 200000,
    category: "sach-giao-khoa",
    nhaxuatban: "Kim Đồng",
    tentacgia: "Không có",
    image: "/Picture/Products/hieu-quy-luat-tam-li_tang-cuong-kha-nang-hoc-tap_f2956b39743e48ec97d5d33ba4afe886_large.webp"
  },
  {
    id: 4,
    name: "Búp sen xanh",
    price: 50000,
    category: "tieu-thuyet",
    nhaxuatban: "Kim Đồng",
    tentacgia: "Không có",
    image: "/Picture/Products/bup-sen-xanh---tb-2022_a6e962d1ceec452d8efaed8162fa0928_e40c26cb340a439185360675cff58ffa_large.webp"
  },
  {
    id: 5,
    name: "Tiền có tệ ?",
    price: 20000,
    category: "sach-giao-khoa",
    nhaxuatban: "FAHASA",
    tentacgia: "Không có",
    image: "/Picture/Products/tien-co-te_bia_5e29fcd7059a46d39dbc037b728021ac_master.webp"
  },
  {
    id: 6,
    name: "Truyện kinh dị Việt Nam - Hồi 2",
    price: 90000,
    category: "kinh-di",
    nhaxuatban: "BaoMoi",
    tentacgia: "Nguyễn Nam Tiến",
    image: "/Picture/Products/truyen-kinh-di-viet-nam---ba-hoi-kinh-di_642ecf79875746679e516f9acbbc89b6_large.webp"
  },
  {
    id: 7,
    name: "Truyện kinh dị Việt Nam - Hồi 3",
    price: 100000,
    category: "kinh-di",
    nhaxuatban: "BaoMoi",
    tentacgia: "Nguyễn Nam Tiến",
    image: "/Picture/Products/truyen-kinh-di-viet-nam---ba-hoi-kinh-di_642ecf79875746679e516f9acbbc89b6_large.webp"
  }
];

const itemsPerPage = 6; // Số sản phẩm mỗi trang (có thể điều chỉnh)
let currentPage = 1;
let filteredProducts = [...products];

function displayProducts(productArray, page = 1) {
  const productList = document.getElementById('product-list');
  productList.innerHTML = '';

  const startIndex = (page - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const pageProducts = productArray.slice(startIndex, endIndex);

  pageProducts.forEach(product => {
    const productDiv = document.createElement('div');
    productDiv.className = 'product-item';
    productDiv.innerHTML = `
            <a href="chitietsanpham.html?id=${product.id}" target="_blank">
                <img src="${product.image}" alt="${product.name}" class="product-image">
                <h3>${product.name}</h3>
                <p>Giá: ${product.price.toLocaleString('vi-VN')} VND</p>
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

function createPaginationControls(productArray) {
  const totalPages = Math.ceil(productArray.length / itemsPerPage);
  const existingPagination = document.querySelector('.pagination-controls');
  if (existingPagination) {
    existingPagination.remove();
  }

  // Chỉ hiển thị pagination nếu có hơn 1 trang
  if (totalPages <= 1) {
    return;
  }

  const paginationDiv = document.createElement('div');
  paginationDiv.className = 'pagination-controls';
  paginationDiv.style.cssText = 'margin-top: 20px; text-align: center;';

  paginationDiv.innerHTML = `
        <button onclick="previousPage()" ${currentPage === 1 ? 'disabled' : ''}>Trước</button>
        <span>Trang ${currentPage} / ${totalPages}</span>
        <button onclick="nextPage()" ${currentPage === totalPages ? 'disabled' : ''}>Tiếp</button>
    `;

  document.querySelector('.product-section').appendChild(paginationDiv);
}

function filterProducts() {
  const category = document.getElementById('category').value;
  const priceMin = parseInt(document.getElementById('price-min').value) || 0;
  const priceMax = parseInt(document.getElementById('price-max').value) || Infinity;
  const sortOrder = document.getElementById('sort-order').value;

  filteredProducts = products.filter(product => {
    const matchesCategory = category === '' || product.category === category;
    const matchesPrice = product.price >= priceMin && product.price <= priceMax;
    return matchesCategory && matchesPrice;
  });

  // Sắp xếp sản phẩm
  if (sortOrder === 'asc') {
    filteredProducts.sort((a, b) => a.price - b.price); // Giá: Từ thấp đến cao
  } else if (sortOrder === 'desc') {
    filteredProducts.sort((a, b) => b.price - a.price); // Giá: Từ cao đến thấp
  } else if (sortOrder === 'alpha-asc') {
    filteredProducts.sort((a, b) => a.name.localeCompare(b.name)); // Tên: A-Z
  } else if (sortOrder === 'alpha-desc') {
    filteredProducts.sort((a, b) => b.name.localeCompare(a.name)); // Tên: Z-A
  }

  currentPage = 1;
  displayProducts(filteredProducts, currentPage);
}

function nextPage() {
  const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
  if (currentPage < totalPages) {
    currentPage++;
    displayProducts(filteredProducts, currentPage);
  }
}

function previousPage() {
  if (currentPage > 1) {
    currentPage--;
    displayProducts(filteredProducts, currentPage);
  }
}

document.getElementById('filter-form').addEventListener('submit', function (e) {
  e.preventDefault();
  filterProducts();
});

window.addEventListener('load', () => {
  displayProducts(products, currentPage);
});