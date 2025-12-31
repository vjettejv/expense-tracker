<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../../includes/header.php';
$user_id = $_SESSION['user_id'];

// Lấy danh sách hạn mức + Tên danh mục tương ứng
$sql = "SELECT budgets.*, categories.name as cat_name 
        FROM budgets 
        JOIN categories ON budgets.category_id = categories.id 
        WHERE budgets.user_id = $user_id 
        ORDER BY budgets.month_year DESC";

$result = $conn->query($sql);
?>
<link rel="stylesheet" href="../../assets/css/bud_index.css">
<div class="container">
    
    <!-- Tiêu đề dùng Flexbox -->
    <div class="header-box">
        <h2 class="page-title">📉 Kế hoạch Ngân sách</h2>
        <a href="create.php" class="btn-create">+ Lập Ngân sách</a>
    </div>

    <table class="budget-table">
        <thead>
            <tr>
                <th>Tháng/Năm</th>
                <th>Danh mục</th>
                <th>Số tiền giới hạn</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <!-- Hiển thị tháng năm -->
                            Tháng <?php echo date("m-Y", strtotime($row['month_year'])); ?>
                        </td>
                        <td>
                            <b><?php echo $row['cat_name']; ?></b>
                        </td>
                        <td>
                            <span class="money-amount"><?php echo number_format($row['amount']); ?> đ</span>
                        </td>
                        <td>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Xóa hạn mức này?')">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">Bạn chưa đặt hạn mức nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>