<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../../includes/header.php';
$user_id = $_SESSION['user_id'];

// Lấy danh sách Danh mục (cả riêng và chung) để user chọn
$sql_cat = "SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL";
$list_cat = $conn->query($sql_cat);
?>
<link rel="stylesheet" href="../../assets/css/bud_create.css">
<div class="container">
    <div class="form-wrapper">
        <h2 style="text-align: center; margin-top: 0;">Lập Hạn Mức Mới</h2>
        
        <form action="store.php" method="POST">
            
            <!-- Chọn Tháng/Năm -->
            <div class="form-group">
                <label class="form-label">Áp dụng cho tháng:</label>
                <!-- Input type="month" cho phép chọn tháng và năm rất tiện -->
                <input type="month" name="month_year" class="form-input" required value="<?php echo date('Y-m'); ?>">
            </div>

            <!-- Chọn Danh mục -->
            <div class="form-group">
                <label class="form-label">Danh mục chi tiêu:</label>
                <select name="category_id" class="form-input">
                    <?php while($cat = $list_cat->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo $cat['name']; ?> 
                            (<?php echo ($cat['type'] == 'expense') ? 'Chi' : 'Thu'; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Nhập số tiền -->
            <div class="form-group">
                <label class="form-label">Số tiền tối đa (VNĐ):</label>
                <input type="number" name="amount" class="form-input" placeholder="Ví dụ: 2000000" required>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-save">Lưu Hạn Mức</button>
                <a href="index.php" class="btn btn-cancel">Quay lại</a>
            </div>

        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>