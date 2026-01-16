<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra user có đăng nhập chưa và có bấm nút submit không
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $user_id = $_SESSION['user_id'];
    
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $balance = $_POST['balance'];
    $description = $_POST['description'];

    // Kiểm tra tên ví không được để trống (validate đơn giản)
    if ($name == "") {
        echo "Tên ví không được để trống!";
        exit();
    }

    // Câu lệnh chèn vào bảng wallets
    $sql = "INSERT INTO wallets (user_id, name, balance, description) VALUES (?, ?, ?, ?)";
    
    // Chuẩn bị statement để chống hack
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isds", $user_id, $name, $balance, $description);
    
    if ($stmt->execute()) {
        // Lưu xong thì quay về trang danh sách
        header("Location: index.php");
    } else {
        echo "Có lỗi xảy ra: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

} else {
    // Nếu cố tình truy cập trực tiếp file này thì đuổi về
    header("Location: index.php");
}
?>