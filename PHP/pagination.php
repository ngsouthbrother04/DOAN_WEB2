<?php

/**
 * Hàm tạo phân trang có thể tái sử dụng
 * 
 * @param string $table_name Tên bảng cần phân trang
 * @param int $items_per_page Số mục trên mỗi trang
 * @param string $where_clause Điều kiện WHERE (tùy chọn)
 * @param string $order_by Điều kiện ORDER BY (tùy chọn)
 * @param string $page_param Tên tham số URL cho trang (mặc định là 'page')
 * @return array Mảng chứa thông tin phân trang
 */
function getPagination($conn, $table_name, $items_per_page = 10, $where_clause = '', $order_by = '', $page_param = 'page')
{

   $current_page = isset($_GET[$page_param]) ? (int)$_GET[$page_param] : 1;
   if ($current_page < 1) $current_page = 1;


   $offset = ($current_page - 1) * $items_per_page;


   $count_sql = "SELECT COUNT(*) as total FROM $table_name";
   if (!empty($where_clause)) {
      $count_sql .= " WHERE $where_clause";
   }


   $count_result = $conn->query($count_sql);
   $total_items = $count_result->fetch_assoc()['total'];
   $total_pages = ceil($total_items / $items_per_page);


   if ($current_page > $total_pages && $total_pages > 0) {
      $current_page = $total_pages;
      $offset = ($current_page - 1) * $items_per_page;
   }


   $query = "SELECT * FROM $table_name";
   if (!empty($where_clause)) {
      $query .= " WHERE $where_clause";
   }
   if (!empty($order_by)) {
      $query .= " ORDER BY $order_by";
   }
   $query .= " LIMIT $offset, $items_per_page";


   return array(
      'query' => $query,
      'current_page' => $current_page,
      'total_pages' => $total_pages,
      'total_items' => $total_items,
      'items_per_page' => $items_per_page,
      'offset' => $offset
   );
}

/**
 * Hàm tạo HTML cho thanh phân trang
 * 
 * @param int $current_page Trang hiện tại
 * @param int $total_pages Tổng số trang
 * @param string $page_param Tên tham số URL cho trang
 * @return string HTML của thanh phân trang
 */
function renderPagination($current_page, $total_pages, $page_param = 'page')
{

   $url = strtok($_SERVER['REQUEST_URI'], '?');
   $query_params = $_GET;
   unset($query_params[$page_param]);


   $base_url = $url;
   if (!empty($query_params)) {
      $base_url .= '?' . http_build_query($query_params) . '&' . $page_param . '=';
   } else {
      $base_url .= '?' . $page_param . '=';
   }


   $html = '<div class="pagination">';


   if ($current_page > 1) {
      $html .= '<a href="' . $base_url . ($current_page - 1) . '" class="page-btn">Trước</a>';
   } else {
      $html .= '<a href="#" class="page-btn disabled">Trước</a>';
   }


   $html .= '<span class="page-info">Trang ' . $current_page . ' / ' . $total_pages . '</span>';


   if ($current_page < $total_pages) {
      $html .= '<a href="' . $base_url . ($current_page + 1) . '" class="page-btn">Tiếp</a>';
   } else {
      $html .= '<a href="#" class="page-btn disabled">Tiếp</a>';
   }

   $html .= '</div>';

   return $html;
}


function getPaginationWithFilter($conn, $table, $items_per_page, $where_clause = '', $order_by = '', $params = [], $param_types = '')
{
   $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
   $current_page = max(1, $current_page);

   $count_query = "SELECT COUNT(*) as total FROM $table" . $where_clause;

   $total_items = 0;
   if (!empty($params)) {
      $stmt = $conn->prepare($count_query);
      if ($stmt) {
         $stmt->bind_param($param_types, ...$params);
         $stmt->execute();
         $result = $stmt->get_result();
         $row = $result->fetch_assoc();
         $total_items = $row['total'];
         $stmt->close();
      }
   } else {
      $result = $conn->query($count_query);
      if ($result) {
         $row = $result->fetch_assoc();
         $total_items = $row['total'];
      }
   }

   $total_pages = ceil($total_items / $items_per_page);
   $current_page = min($current_page, max(1, $total_pages));

   $offset = ($current_page - 1) * $items_per_page;


   $query = "SELECT * FROM $table" . $where_clause . $order_by . " LIMIT ?, ?";

   $stmt = null;
   if ($conn->prepare($query)) {
      $stmt = $conn->prepare($query);
      if ($stmt) {

         $params[] = $offset;
         $params[] = $items_per_page;
         $param_types .= 'ii';

         $stmt->bind_param($param_types, ...$params);
         $stmt->execute();
      }
   }

   return [
      'stmt' => $stmt,
      'current_page' => $current_page,
      'total_pages' => $total_pages,
      'total_items' => $total_items
   ];
}
