<?php
session_start();
require_once '../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];

// TRUY VẤN THÔNG MINH:
// Lấy hạn mức VÀ tính luôn tổng tiền đã chi (spent) cho danh mục đó trong tháng đó
$sql = "SELECT b.*, c.name as cat_name, c.color as cat_color,
            (SELECT SUM(amount) FROM transactions t 
             WHERE t.user_id = b.user_id 
             AND t.category_id = b.category_id 
             AND DATE_FORMAT(t.transaction_date, '%Y-%m') = b.month_year
            ) as spent
        FROM budgets b
        JOIN categories c ON b.category_id = c.id
        WHERE b.user_id = $user_id
        ORDER BY b.month_year DESC";

$result = $conn->query($sql);

include '../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;">Kế hoạch Ngân sách</h2>
        <p style="color: #64748b; margin-top: 5px;">Kiểm soát chi tiêu, tránh vung tay quá trán.</p>
    </div>
    <a href="create.php" class="btn btn-primary">
        <span>+</span> Lập Hạn mức mới
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php 
                $limit = $row['amount'];
                $spent = $row['spent'] ?? 0; // Nếu chưa chi đồng nào thì là 0
                $percent = ($spent / $limit) * 100;
                $percent = min($percent, 100); // Tối đa 100% để vẽ CSS

                // Logic màu sắc
                $status_color = '#10b981'; // Xanh (An toàn)
                $status_text = 'An toàn';
                if ($spent > $limit) {
                    $status_color = '#ef4444'; // Đỏ (Vỡ nợ)
                    $status_text = 'Vượt quá hạn mức!';
                } elseif ($percent >= 80) {
                    $status_color = '#f59e0b'; // Vàng (Cảnh báo)
                    $status_text = 'Sắp hết tiền';
                }
            ?>

            <!-- Card Ngân Sách -->
            <div class="card" style="border-left: 5px solid <?php echo $row['cat_color']; ?>;">
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <div>
                        <div style="font-size: 13px; color: #64748b; font-weight: 600; text-transform: uppercase;">
                            Tháng <?php echo date("m/Y", strtotime($row['month_year'])); ?>
                        </div>
                        <h3 style="margin: 5px 0 0 0; font-size: 18px;"><?php echo htmlspecialchars($row['cat_name']); ?></h3>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: #64748b;">Hạn mức</div>
                        <div style="font-weight: bold;"><?php echo number_format($limit); ?> đ</div>
                    </div>
                </div>

                <!-- Thanh Tiến Độ -->
                <div style="margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px;">
                        <span>Đã chi: <b><?php echo number_format($spent); ?> đ</b></span>
                        <span style="color: <?php echo $status_color; ?>; font-weight: bold;"><?php echo $status_text; ?></span>
                    </div>
                    <div style="width: 100%; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                        <div style="width: <?php echo $percent; ?>%; height: 100%; background: <?php echo $status_color; ?>; border-radius: 4px;"></div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e2e8f0;">
                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn-sm" style="color: #ef4444; text-decoration: none;" onclick="return confirm('Xóa hạn mức này?')">🗑️ Xóa bỏ</a>
                </div>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px dashed #cbd5e1;">
            <div style="font-size: 40px; margin-bottom: 10px;">📉</div>
            <h3 style="color: #64748b;">Chưa có kế hoạch nào</h3>
            <p style="color: #94a3b8;">Đặt giới hạn chi tiêu giúp bạn tiết kiệm tiền hiệu quả hơn.</p>
            <a href="create.php" class="btn btn-primary" style="margin-top: 10px;">Lập ngân sách ngay</a>
        </div>
    <?php endif; ?>

</div>

<?php include '../../includes/footer.php'; ?>