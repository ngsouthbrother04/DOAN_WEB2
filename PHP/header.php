<?php
// Ensure the database connection is available
require_once 'db_connect.php';

// Fetch categories for the dropdown menu
$category_query = "SELECT loaisach_id, ten_loai FROM LOAISACH";
$category_result = $conn->query($category_query);
$categories = [];
if ($category_result) {
   while ($row = $category_result->fetch_assoc()) {
      $categories[] = $row;
   }
}
?>

<div class="header">
   <div id="topmenu1">
      <img src="../Picture/CT_T3_header_1263x60.webp" alt="" id="anh_topmenu">
   </div>
   <div id="topmenu2">
      <a href="trangchu.php">
         <img src="../Picture/logo.png" alt="" id="logo">
      </a>
      <div class="menubutton">
         <img src="../icon/bars-solid.svg" alt="" id="menubutton">
         <p>Danh Mục</p>
         <div class="dropdown-menu">
            <ul>
               <?php foreach ($categories as $category): ?>
                  <li>
                     <a href="category.php?category=<?php echo urlencode($category['loaisach_id']); ?>">
                        <?php echo htmlspecialchars($category['ten_loai']); ?>
                     </a>
                  </li>
               <?php endforeach; ?>
            </ul>
         </div>
      </div>
      <div class="search-container">
            <form action="timsanpham.php" method="GET">
               <input type="text" name="search_term" class="search-input" placeholder="Tìm tại đây">
               <button type="submit" class="search-button">
                  <img src="../icon/magnifying-glass-solid.svg" alt="" style="width: 17px; height: 17px;">
               </button>
               <div class="advance_search">
                  <div class="advance_search-menu">
                     <div class="filter-search-group">
                        <label for="category-search">Danh mục:</label>
                        <select id="category-search" name="category-search">
                           <option value="">Tất cả</option>
                           <?php foreach ($categories as $category): ?>
                              <option value="<?php echo htmlspecialchars($category['loaisach_id']); ?>">
                                 <?php echo htmlspecialchars($category['ten_loai']); ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                     <div class="filter-search-group">
                        <label for="price-min-search">Giá từ:</label>
                        <input type="number" id="price-min-search" name="price-min-search" min="0" placeholder="0">
                     </div>
                     <div class="filter-search-group">
                        <label for="price-max-search">Đến:</label>
                        <input type="number" id="price-max-search" name="price-max-search" min="0" placeholder="1000000">
                     </div>
                  </div>
               </div>
            </form>
         </div>
      <div class="userbutton">
         <img src="../icon/user-regular.svg" alt="" id="userbutton">
         <p>Tài khoản</p>
      </div>
      <div class="cartbutton">
         <img src="../icon/cart-shopping-solid.svg" alt="" id="cartbutton">
         <p>Giỏ Hàng</p>
      </div>
      <div class="bellbutton">
         <img src="../icon/bell-regular.svg" alt="" id="bellbutton">
         <p>Thông Báo</p>
      </div>
   </div>
</div>