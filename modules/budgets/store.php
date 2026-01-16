<?php
session_start();
require_once '../../config/db.php';

if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $user_id = $_SESSION['user_id'];
    
    // 1. Sanitize
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $month_year = isset($_POST['month_year']) ? trim($_POST['month_year']) : ''; // YYYY-MM

    // 2. Validate
    if ($amount <= 0) {
        die("Số tiền hạn mức phải lớn hơn 0");
    }
    
    if ($category_id <= 0) {
        die("Vui lòng chọn danh mục hợp lệ");
    }

    if (empty($month_year) || !preg_match("/^\d{4}-\d{2}$/", $month_year)) {
        die("Tháng áp dụng không hợp lệ");
    }

    // Kiểm tra xem đã có hạn mức cho danh mục này trong tháng này chưa (tránh trùng lặp)
    $check_sql = "SELECT id FROM budgets WHERE user_id = ? AND category_id = ? AND month_year = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("iis", $user_id, $category_id, $month_year);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        echo "<script>alert('Bạn đã đặt hạn mức cho danh mục này trong tháng này rồi! Vui lòng sửa hạn mức cũ.'); window.location.href='index.php';</script>";
        exit();
    }

    // 3. Insert
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