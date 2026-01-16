<?php
session_start();
require_once '../config/db.php';

// 1. Kiểm tra quyền Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 2. Xử lý logic xóa giao dịch (Giữ nguyên logic cũ của bạn)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    // Lấy thông tin để hoàn tiền
    $trans = $conn->query("SELECT * FROM transactions WHERE id=$id")->fetch_assoc();
    if ($trans) {
        $wallet_id = $trans['wallet_id'];
        $amount = $trans['amount'];
        // Xác định loại (Thu/Chi) qua Category
        $cat = $conn->query("SELECT type FROM categories WHERE id=" . $trans['category_id'])->fetch_assoc();
        
        if ($cat['type'] == 'expense') {
            $conn->query("UPDATE wallets SET balance = balance + $amount WHERE id=$wallet_id");
        } else {
            $conn->query("UPDATE wallets SET balance = balance - $amount WHERE id=$wallet_id");
        }
        
        $conn->query("DELETE FROM transactions WHERE id=$id");
        header("Location: admin_report.php?msg=deleted");
    }
}

// 3. Xử lý Reset toàn bộ dữ liệu (Nguy hiểm)
if (isset($_GET['reset']) && $_GET['reset'] == 1) {
    $conn->query("TRUNCATE TABLE transactions");
    // Reset ví về 0 (hoặc số dư ban đầu tùy logic, ở đây mình reset về 0 cho sạch)
    $conn->query("UPDATE wallets SET balance = 0");
    header("Location: admin_report.php?msg=reset_success");
}

// 4. Bộ lọc tìm kiếm
$where = "WHERE 1=1";
if (isset($_GET['user_id']) && $_GET['user_id'] != '') {
    $uid = intval($_GET['user_id']);
    $where .= " AND t.user_id = $uid";
}
if (isset($_GET['month']) && $_GET['month'] != '') {
    $m = $_GET['month']; // định dạng 2023-12
    $where .= " AND DATE_FORMAT(t.transaction_date, '%Y-%m') = '$m'";
}

// Truy vấn danh sách
$sql = "SELECT t.*, u.full_name, c.name as cat_name, c.type as cat_type, w.name as wallet_name 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN categories c ON t.category_id = c.id
        JOIN wallets w ON t.wallet_id = w.id
        $where
        ORDER BY t.transaction_date DESC, t.id DESC";
$result = $conn->query($sql);

// Lấy danh sách User để lọc
$users = $conn->query("SELECT id, full_name, username FROM users WHERE role='user'");

include '../includes/header.php';
?>

<!-- Thông báo Toast -->
<?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <script>document.addEventListener('DOMContentLoaded', ()=> showToast('Đã xóa giao dịch và hoàn tiền!', 'success'));</script>
<?php endif; ?>
<?php if(isset($_GET['msg']) && $_GET['msg'] == 'reset_success'): ?>
    <script>document.addEventListener('DOMContentLoaded', ()=> showToast('Đã xóa sạch dữ liệu hệ thống!', 'success'));</script>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;">Quản lý Giao dịch Hệ thống</h2>
        <p style="color: #64748b; margin-top: 5px;">Xem và kiểm soát toàn bộ dòng tiền của người dùng.</p>
    </div>
    
    <a href="?reset=1" class="btn" style="background: #fee2e2; color: #ef4444; border: 1px solid #ef4444;" 
       onclick="return confirm('CẢNH BÁO CỰC KỲ NGUY HIỂM!\n\nHành động này sẽ XÓA SẠCH toàn bộ lịch sử giao dịch của TẤT CẢ người dùng.\n\nBạn có chắc chắn muốn thực hiện không?')">
        ⚠️ Reset Database
    </a>
</div>

<!-- CARD BỘ LỌC -->
<div class="card" style="padding: 20px;">
    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
        
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label">Người dùng</label>
            <select name="user_id" class="form-control">
                <option value="">-- Tất cả --</option>
                <?php while($u = $users->fetch_assoc()): ?>
                    <option value="<?php echo $u['id']; ?>" <?php if(isset($_GET['user_id']) && $_GET['user_id']==$u['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($u['full_name']); ?> (@<?php echo $u['username']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div style="flex: 1; min-width: 150px;">
            <label class="form-label">Tháng</label>
            <input type="month" name="month" class="form-control" value="<?php echo isset($_GET['month']) ? $_GET['month'] : ''; ?>">
        </div>

        <button type="submit" class="btn btn-primary" style="height: 42px;">🔍 Tìm kiếm</button>
        <a href="admin_report.php" class="btn" style="background: #f1f5f9; color: #333; height: 42px; display: inline-flex; align-items: center;">Xóa lọc</a>
    </form>
</div>

<!-- BẢNG DỮ LIỆU -->
<div class="card" style="padding: 0; overflow: hidden;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Người dùng</th>
                <th>Ngày</th>
                <th>Chi tiết</th>
                <th>Số tiền</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td>
                            <div style="font-weight: 600; color: #334155;"><?php echo htmlspecialchars($row['full_name']); ?></div>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['transaction_date'])); ?></td>
                        <td>
                            <span class="badge <?php echo ($row['cat_type'] == 'income') ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo htmlspecialchars($row['cat_name']); ?>
                            </span>
                            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
                                Ví: <?php echo htmlspecialchars($row['wallet_name']); ?>
                            </div>
                            <?php if(!empty($row['note'])): ?>
                                <div style="font-size: 11px; color: #64748b; font-style: italic; margin-top: 2px;">
                                    "<?php echo htmlspecialchars($row['note']); ?>"
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['cat_type'] == 'income'): ?>
                                <span style="color: #16a34a; font-weight: 700;">+<?php echo number_format($row['amount']); ?></span>
                            <?php else: ?>
                                <span style="color: #dc2626; font-weight: 700;">-<?php echo number_format($row['amount']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin_edit.php?id=<?php echo $row['id']; ?>" class="btn-sm" style="color: #0095f6; text-decoration: none; margin-right: 10px;">✏️ Sửa</a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-sm" style="color: #ef4444; text-decoration: none;" onclick="return confirm('Admin xóa giao dịch này sẽ hoàn lại tiền vào ví User. Tiếp tục?')">❌ Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                        Không có dữ liệu nào phù hợp.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>