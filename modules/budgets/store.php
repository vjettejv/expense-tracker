<?php
session_start();
require_once '../../config/db.php';

if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $user_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $month_year = $_POST['month_year']; // Giá trị dạng: 2023-12

    // Kiểm tra số tiền hợp lệ
    if ($amount <= 0) {
        die("Số tiền hạn mức phải lớn hơn 0");
    }

    // Chèn vào Database
    $sql = "INSERT INTO budgets (user_id, category_id, amount, month_year) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $user_id, $category_id, $amount, $month_year);
    
    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "Lỗi: " . $conn->error;
    }

} else {
    header("Location: index.php");
}
?>