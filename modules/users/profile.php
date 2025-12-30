<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra nếu chưa đăng nhập thì đá ra trang login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin user hiện tại
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Thống kê nhanh: Tổng số tiền hiện có trong tất cả các ví
$sql_total = "SELECT SUM(balance) as total_money FROM wallets WHERE user_id = $user_id";
$total_money = $conn->query($sql_total)->fetch_assoc()['total_money'] ?? 0;

include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/user_profile.css">
<div class="profile-card">
    <div class="profile-header">
        <div class="avatar-placeholder">
            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
        </div>
        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
        <p style="color: #8e8e8e;">@<?php echo htmlspecialchars($user['username']); ?></p>
    </div>

    <div class="info-row">
        <span class="info-label">Email:</span>
        <span><?php echo htmlspecialchars($user['email']); ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Vai trò:</span>
        <span style="text-transform: capitalize;"><?php echo $user['role']; ?></span>
    </div>
    <div class="info-row">
        <span class="info-label">Tổng tài sản:</span>
        <span style="color: #2ecc71; font-weight: bold;"><?php echo number_format($total_money); ?> đ</span>
    </div>
    <div class="info-row">
        <span class="info-label">Ngày tham gia:</span>
        <span><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
    </div>

    <a href="edit.php" class="btn-edit-profile">Chỉnh sửa hồ sơ</a>
</div>

<?php include '../../includes/footer.php'; ?>