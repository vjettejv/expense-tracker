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

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

include '../includes/header.php';
?>

<style>
    .user-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }
    .user-table th, .user-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    .user-table th { background: #f8f9fa; color: #666; font-size: 13px; }
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }
    .badge-admin { background: #e3f2fd; color: #1976d2; }
    .badge-user { background: #f5f5f5; color: #616161; }
    .btn-del { color: #ed4956; text-decoration: none; font-size: 13px; font-weight: bold; }
</style>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>👥 Quản lý người dùng</h2>
        <a href="dashboard.php" style="text-decoration: none; color: #0095f6;">&larr; Quay lại Dashboard</a>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ Tên</th>
                <th>Username</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><b><?php echo htmlspecialchars($u['full_name']); ?></b></td>
                <td>@<?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <span class="badge <?php echo ($u['role']=='admin') ? 'badge-admin' : 'badge-user'; ?>">
                        <?php echo strtoupper($u['role']); ?>
                    </span>
                </td>
                <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete_id=<?php echo $u['id']; ?>" class="btn-del" onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">Xóa</a>
                    <?php else: ?>
                        <small style="color: #ccc;">(Bạn)</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>