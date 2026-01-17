<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Xử lý xóa user
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    // Không cho phép Admin tự xóa chính mình
    if ($del_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $del_id");
        header("Location: users.php?msg=deleted");
    }
}

// Xử lý Khóa/Mở khóa User (nếu cần sau này)
if (isset($_GET['ban_id'])) {
    $ban_id = intval($_GET['ban_id']);
    if ($ban_id != $_SESSION['user_id']) {
        $conn->query("UPDATE users SET status = IF(status='active', 'banned', 'active') WHERE id = $ban_id");
        header("Location: users.php?msg=status_updated");
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

include '../includes/header.php';
?>

<!-- Thông báo Toast -->
<?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <script>document.addEventListener('DOMContentLoaded', ()=> showToast('Đã xóa người dùng thành công!', 'success'));</script>
<?php endif; ?>
<?php if(isset($_GET['msg']) && $_GET['msg'] == 'status_updated'): ?>
    <script>document.addEventListener('DOMContentLoaded', ()=> showToast('Đã cập nhật trạng thái người dùng!', 'success'));</script>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;">Quản lý Người dùng</h2>
        <p style="color: #64748b; margin-top: 5px;">Danh sách tất cả thành viên trong hệ thống.</p>
    </div>
    
    <!-- Bạn có thể thêm nút "Thêm User" ở đây nếu muốn -->
    <!-- <a href="user_create.php" class="btn btn-primary">Thêm mới</a> -->
</div>

<!-- BẢNG DỮ LIỆU (Đóng khung Card giống admin_report) -->
<div class="card" style="padding: 0; overflow: hidden;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>Họ Tên</th>
                <th>Username</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Ngày tham gia</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users->num_rows > 0): ?>
                <?php while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #334155;"><?php echo htmlspecialchars($u['full_name']); ?></div>
                    </td>
                    <td>@<?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <span class="badge <?php echo ($u['role']=='admin') ? 'badge-primary' : 'badge-info'; ?>" 
                              style="<?php echo ($u['role']=='admin') ? 'background:#e0f2fe; color:#0369a1;' : 'background:#f1f5f9; color:#475569;'; ?>">
                            <?php echo strtoupper($u['role']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($u['status'] == 'active'): ?>
                            <span class="badge badge-success">Hoạt động</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Bị khóa</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                    <td>
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                            <div style="display: flex; gap: 10px;">
                                <!-- Nút Khóa/Mở khóa -->
                                <a href="?ban_id=<?php echo $u['id']; ?>" 
                                   style="text-decoration: none; font-size: 13px; font-weight: 600; color: <?php echo ($u['status']=='active') ? '#f59e0b' : '#10b981'; ?>;"
                                   onclick="return confirm('Bạn muốn thay đổi trạng thái người dùng này?')">
                                   <?php echo ($u['status']=='active') ? '🔒 Khóa' : '🔓 Mở'; ?>
                                </a>

                                <!-- Nút Xóa -->
                                <a href="?delete_id=<?php echo $u['id']; ?>" 
                                   style="color: #ef4444; text-decoration: none; font-size: 13px; font-weight: 600;" 
                                   onclick="return confirm('CẢNH BÁO: Xóa người dùng sẽ xóa HẾT dữ liệu của họ.\nBạn có chắc chắn không?')">
                                   ❌ Xóa
                                </a>
                            </div>
                        <?php else: ?>
                            <small style="color: #cbd5e1; font-style: italic;">(Bạn)</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                        Chưa có người dùng nào.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>