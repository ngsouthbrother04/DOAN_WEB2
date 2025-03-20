function setupSearchFunctionality() {
  const searchInput = document.querySelector('.search-input');
  const searchButton = document.querySelector('.search-button');
  const filterSearchForm = document.getElementById('filter-search');
  const mainSection = document.querySelector('.main'); // Thêm tham chiếu đến .main

  function performSearch() {
    const searchTerm = searchInput.value.trim().toLowerCase();
    const category = document.getElementById('category-search').value;
    const priceMin = parseInt(document.getElementById('price-min-search').value) || 0;
    const priceMax = parseInt(document.getElementById('price-max-search').value) || Infinity;

    filteredProducts = products.filter(product => {
      const matchesSearch = searchTerm === '' ||
        product.name.toLowerCase().includes(searchTerm);

      const matchesCategory = category === '' ||
        product.category === category;

      const matchesPrice = product.price >= priceMin &&
        product.price <= priceMax;

      return matchesSearch && matchesCategory && matchesPrice;
    });

    // Kiểm tra nếu có tìm kiếm (searchTerm không rỗng hoặc có bộ lọc), thêm class search-active
    if (searchTerm !== '' || category !== '' || priceMin > 0 || priceMax < Infinity) {
      mainSection.classList.add('search-active');
    } else {
      mainSection.classList.remove('search-active');
    }

    currentPage = 1;
    displayProducts(filteredProducts, currentPage);
  }

  searchButton.addEventListener('click', function (e) {
    e.preventDefault();
    performSearch();
  });

  searchInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      performSearch();
    }
  });

  filterSearchForm.addEventListener('submit', function (e) {
    e.preventDefault();
    performSearch();
  });

  searchInput.addEventListener('focus', function () {
    const advanceSearch = document.querySelector('.advance_search');
    advanceSearch.style.display = 'block';
  });

  filterSearchForm.addEventListener('click', function (e) {
    e.stopPropagation();
  });

  document.addEventListener('click', function (e) {
    const searchContainer = document.querySelector('.search-container');
    const advanceSearch = document.querySelector('.advance_search');

    if (!searchContainer.contains(e.target)) {
      advanceSearch.style.display = 'none';
    }
  });
}

window.addEventListener('load', () => {
  displayProducts(products, currentPage);
  setupSearchFunctionality();
});