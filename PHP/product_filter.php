<?php
function getFilteredProducts($conn, $items_per_page = 6, $page_param = 'page')
{
   $category = isset($_GET['category']) ? $_GET['category'] : '';
   $price_min = isset($_GET['price_min']) ? (int)$_GET['price_min'] : 0;
   $price_max = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 0;
   $sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

   $where_clause = "trang_thai = 'active'";
   $params = [];
   $param_types = '';
   if (!empty($category)) {
      $where_clause .= " AND loaisach_id = ?";
      $params[] = $category;
      $param_types .= 's';
   }
   if ($price_min > 0) {
      $where_clause .= ($where_clause ? ' AND ' : '') . "gia_tien >= ?";
      $params[] = $price_min;
      $param_types .= 'i';
   }
   if ($price_max > 0) {
      $where_clause .= ($where_clause ? ' AND ' : '') . "gia_tien <= ?";
      $params[] = $price_max;
      $param_types .= 'i';
   }

   $order_by = '';
   switch ($sort) {
      case 'asc':
         $order_by = ' ORDER BY gia_tien ASC';
         break;
      case 'desc':
         $order_by = ' ORDER BY gia_tien DESC';
         break;
      case 'alpha-asc':
         $order_by = ' ORDER BY tieu_de ASC';
         break;
      case 'alpha-desc':
         $order_by = ' ORDER BY tieu_de DESC';
         break;
      default:
         $order_by = ' ORDER BY sach_id ASC';
         break;
   }

   $pagination = getPaginationWithFilter($conn, 'SACH', $items_per_page, $where_clause ? " WHERE $where_clause" : '', $order_by, $params, $param_types);

   $products = [];
   if ($pagination['stmt']) {
      $result = $pagination['stmt']->get_result();
      while ($row = $result->fetch_assoc()) {
         $products[] = $row;
      }
      $pagination['stmt']->close();
   }

   return [
      'products' => $products,
      'pagination' => [
         'current_page' => $pagination['current_page'],
         'total_pages' => $pagination['total_pages'],
         'total_items' => $pagination['total_items']
      ]
   ];
}
