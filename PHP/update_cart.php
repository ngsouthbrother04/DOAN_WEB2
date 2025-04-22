<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: trangchu.php");
    exit;
}

require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(isset($_POST['confirm'])){
        $quantityData = $_POST['quantity'];

        foreach ($quantityData as $sach_id => $quantity) {
            $quantity = intval($quantity);
            if ($quantity > 0) {
                $stmt = $conn->prepare("UPDATE giohang SET so_luong = ? WHERE sach_id = ? AND giohang_id = (SELECT giohang_id FROM user WHERE user_id = ?)");
                $stmt->bind_param("iii", $quantity, $sach_id, $user_id);
                $stmt->execute();
            }
        }
        $_SESSION['success_message'] = "Cập nhật giỏ hàng thành công!";
        header("Location: giohang.php");
    }

    if(isset($_POST['delete_all'])){
        $stmt = $conn->prepare("DELETE FROM giohang WHERE giohang_id = (SELECT giohang_id FROM user WHERE user_id = ?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $_SESSION['delete_message'] = "Làm trống giỏ hàng thành công!";
        header("Location: giohang.php");
    }
    
    if(isset($_POST['delete_item'])){
        $sach_id = $_POST['delete_item'];
        $stmt = $conn->prepare("DELETE FROM giohang WHERE sach_id = ? AND giohang_id = (SELECT giohang_id FROM user WHERE user_id = ?)");
        $stmt->bind_param("ii", $sach_id, $user_id);
        $stmt->execute();
        header("Location:giohang.php");
    }
}


$conn->close();
?>