<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra đăng nhập đơn giản
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/cate_create.css">
<div class="container form-container">
    <h2 class="form-title">Thêm Danh mục mới</h2>
    <p class="form-desc">Tạo danh mục để phân loại các khoản thu chi của bạn.</p>

    <form action="store.php" method="POST" class="custom-form">
        
        <!-- Nhập tên -->
        <div class="form-group">
            <label class="form-label">Tên danh mục:</label>
            <input type="text" name="name" class="form-control" required placeholder="Ví dụ: Ăn sáng, Tiền nhà...">
        </div>

        <!-- Chọn loại -->
        <div class="form-group">
            <label class="form-label">Loại:</label>
            <select name="type" class="form-control">
                <option value="expense">Khoản Chi (Expense)</option>
                <option value="income">Khoản Thu (Income)</option>
            </select>
        </div>

        <!-- Chọn màu -->
        <div class="form-group">
            <label class="form-label">Màu sắc (để vẽ biểu đồ):</label>
            <input type="color" name="color" value="#0095f6" class="form-control input-color">
        </div>

        <!-- Nút bấm dùng Flexbox -->
        <div class="button-group">
            <button type="submit" class="btn btn-save">Lưu lại</button>
            <a href="index.php" class="btn btn-cancel">Hủy bỏ</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>