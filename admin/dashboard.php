<?php
session_start();
require_once '../config/db.php';
require_admin();

// 1. TOP ĐẠI GIA (Người có tổng số dư ví cao nhất)
$sql_rich = "SELECT u.full_name, SUM(w.balance) as total_asset 
             FROM wallets w JOIN users u ON w.user_id = u.id 
             GROUP BY w.user_id ORDER BY total_asset DESC LIMIT 5";
$top_rich = $conn->query($sql_rich);

// 2. TOP TIÊU HOANG (Người chi tiêu nhiều nhất tháng này)
$sql_spend = "SELECT u.full_name, SUM(t.amount) as total_spent 
              FROM transactions t JOIN users u ON t.user_id = u.id 
              JOIN categories c ON t.category_id = c.id
              WHERE c.type = 'expense' AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
              GROUP BY t.user_id ORDER BY total_spent DESC LIMIT 5";
$top_spenders = $conn->query($sql_spend);

include '../includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <h2 style="margin: 0;">Admin Control Center ⚡</h2>
    <p style="color: #64748b;">Góc nhìn toàn cảnh hệ thống (God View)</p>
</div>

<div style="display: flex; gap: 24px; flex-wrap: wrap;">
    
    <!-- Bảng xếp hạng ĐẠI GIA -->
    <div class="card" style="flex: 1;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="margin: 0;">🏆 Top Tài Sản Cao Nhất</h3>
        </div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Thành viên</th>
                    <th>Tổng tài sản</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; while($row = $top_rich->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td style="color: #10b981; font-weight: bold;"><?php echo number_format($row['total_asset']); ?> đ</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Bảng xếp hạng TIÊU HOANG -->
    <div class="card" style="flex: 1;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="margin: 0;">🔥 Top Chi Tiêu Tháng Này</h3>
        </div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Thành viên</th>
                    <th>Đã chi</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; while($row = $top_spenders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td style="color: #ef4444; font-weight: bold;"><?php echo number_format($row['total_spent']); ?> đ</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>🛠 Quản lý hệ thống</h3>
    <div style="display: flex; gap: 15px;">
        <a href="users.php" class="btn btn-primary">Quản lý User (Khóa/Mở)</a>
        <a href="../modules/categories/index.php" class="btn btn-primary" style="background: #6366f1;">Quản lý Danh mục Chung</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>