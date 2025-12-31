<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../../includes/header.php';

$user_id = $_SESSION['user_id'];

// Lấy danh sách danh mục
$sql = "SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<link rel="stylesheet" href="../../assets/css/cate_index.css">
<div class="container">
    
    <!-- Phần tiêu đề và nút bấm (Dùng Flexbox) -->
    <div class="header-row">
        <h2>📂 Quản lý Danh mục</h2>
        <a href="create.php" class="btn-them">+ Thêm Danh mục</a>
    </div>

    <!-- Bảng hiển thị -->
    <table>
        <thead>
            <tr>
                <th>Tên danh mục</th>
                <th>Loại</th>
                <th>Màu</th>
                <th>Nguồn gốc</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <!-- Hiện dấu chấm màu -->
                            <span class="dot" style="background-color: <?php echo $row['color']; ?>;"></span>
                            <b><?php echo $row['name']; ?></b>
                        </td>
                        <td>
                            <?php if ($row['type'] == 'income'): ?>
                                <span class="thu">Khoản Thu</span>
                            <?php else: ?>
                                <span class="chi">Khoản Chi</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['color']; ?></td>
                        <td>
                            <?php if ($row['user_id'] == null): ?>
                                <span style="color: #666; font-style: italic;">Mặc định</span>
                            <?php else: ?>
                                <span style="color: #0095f6; font-weight: bold;">Của tôi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Chỉ hiện nút Xóa nếu là danh mục của mình -->
                            <?php if ($row['user_id'] != null): ?>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="xoa" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                            <?php else: ?>
                                <span style="color: #ccc;">--</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #888;">Chưa có danh mục nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>