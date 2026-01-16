<?php
session_start();
require_once '../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
// Chỉ lấy danh mục CHI TIÊU (Expense) vì ít ai đặt hạn mức cho Thu nhập
$sql_cat = "SELECT * FROM categories WHERE (user_id = $user_id OR user_id IS NULL) AND type='expense'";
$list_cat = $conn->query($sql_cat);

include '../../includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <a href="index.php" style="text-decoration: none; color: #64748b; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">
        <span>←</span> Quay lại danh sách
    </a>

    <div class="card">
        <h2 style="margin-top: 0; text-align: center;">Thiết lập Hạn mức</h2>
        <p style="color: #64748b; text-align: center; margin-bottom: 30px;">Cảnh báo khi bạn chi tiêu quá tay.</p>

        <form action="store.php" method="POST">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Áp dụng cho tháng</label>
                <input type="month" name="month_year" class="form-control" required value="<?php echo date('Y-m'); ?>" 
                       style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Danh mục chi tiêu</label>
                <select name="category_id" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: white;">
                    <?php while($cat = $list_cat->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Số tiền giới hạn (VNĐ)</label>
                <input type="number" name="amount" placeholder="Ví dụ: 3000000" required
                       style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 16px; font-weight: bold;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 15px;">
                Lưu Hạn Mức
            </button>

        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>