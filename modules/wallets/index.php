<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra đăng nhập (đoạn này copy y hệt bài trước)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../../includes/header.php';

$user_id = $_SESSION['user_id'];

// Lấy danh sách ví của người dùng đang đăng nhập
$sql = "SELECT * FROM wallets WHERE user_id = $user_id ORDER BY id DESC";
$result = $conn->query($sql);
?>
<link rel="stylesheet" href="../../assets/css/wallet_index.css">
<div class="container">
    
    <!-- Phần tiêu đề dùng Flexbox -->
    <div class="tieu-de-trang">
        <h2>💰 Ví của tôi</h2>
        <a href="create.php" class="nut-them">+ Tạo Ví mới</a>
    </div>

    <!-- Bảng hiển thị -->
    <table class="bang-vi">
        <thead>
            <tr>
                <th>Tên Ví</th>
                <th>Mô tả</th>
                <th>Số dư hiện tại</th>
                <th width="100">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <b><?php echo $row['name']; ?></b>
                        </td>
                        <td><?php echo $row['description']; ?></td>
                        <td>
                            <!-- Format số tiền có dấu phẩy: 1,000,000 -->
                            <span class="so-tien"><?php echo number_format($row['balance']); ?> đ</span>
                        </td>
                        <td>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="nut-xoa" onclick="return confirm('Xóa ví này sẽ xóa hết giao dịch bên trong. Bạn chắc chưa?')">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="trong-tron">Bạn chưa có ví tiền nào. Hãy tạo cái đầu tiên!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>