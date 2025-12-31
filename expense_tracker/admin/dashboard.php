<?php
session_start();
require_once '../config/db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Thống kê dữ liệu
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_transactions = $conn->query("SELECT COUNT(*) as total FROM transactions")->fetch_assoc()['total'];
$total_balance = $conn->query("SELECT SUM(balance) as total FROM wallets")->fetch_assoc()['total'];

include '../includes/header.php';
?>

<style>
    .admin-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    .stat-box {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
        border-top: 4px solid #0095f6;
    }
    .stat-box h3 { font-size: 14px; color: #8e8e8e; text-transform: uppercase; }
    .stat-box p { font-size: 28px; font-weight: bold; margin-top: 10px; color: #333; }
    
    .admin-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    .btn-admin {
        padding: 15px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        color: white;
    }
    .bg-blue { background: #0095f6; }
    .bg-green { background: #2ecc71; }
</style>

<div class="container">
    <h2 style="margin-bottom: 30px;">⚡ Bảng điều khiển Quản trị viên</h2>

    <div class="admin-stats">
        <div class="stat-box">
            <h3>Tổng người dùng</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="stat-box" style="border-top-color: #2ecc71;">
            <h3>Tổng giao dịch</h3>
            <p><?php echo $total_transactions; ?></p>
        </div>
        <div class="stat-box" style="border-top-color: #f1c40f;">
            <h3>Dòng tiền hệ thống</h3>
            <p><?php echo number_format($total_balance); ?> đ</p>
        </div>
    </div>

    <div class="admin-actions">
        <a href="users.php" class="btn-admin bg-blue">👥 Quản lý Thành viên</a>
        <a href="admin_report.php" class="btn-admin bg-green">📊 Báo cáo Giao dịch</a>
        <a href="../index.php" class="btn-admin bg-blue">Chi tiêu</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>