<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/wallet_create.css">
<div class="container">
    <div class="khung-form">
        <h2 class="tieu-de-form">Thêm Nguồn Tiền Mới</h2>
        
        <form action="store.php" method="POST">
            
            <div class="dong-nhap">
                <label class="nhan-de">Tên ví:</label>
                <input type="text" name="name" class="o-nhap" placeholder="VD: Ví tiền mặt, Thẻ Vietcombank..." required>
            </div>

            <div class="dong-nhap">
                <label class="nhan-de">Số dư ban đầu (VNĐ):</label>
                <!-- Type number để chỉ cho nhập số -->
                <input type="number" name="balance" class="o-nhap" placeholder="0" required>
            </div>

            <div class="dong-nhap">
                <label class="nhan-de">Mô tả (Tùy chọn):</label>
                <textarea name="description" class="o-nhap" rows="3" placeholder="Ví dụ: Dùng để chi tiêu hàng ngày"></textarea>
            </div>

            <div class="nhom-nut">
                <button type="submit" class="nut nut-luu">Lưu ví</button>
                <a href="index.php" class="nut nut-huy">Hủy</a>
            </div>

        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>