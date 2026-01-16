<?php
session_start();
require_once '../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];

// --- XỬ LÝ BỘ LỌC ---
$filter_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
$filter_wallet = isset($_GET['wallet']) ? intval($_GET['wallet']) : 0;
$from_date = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01'); // Mặc định từ đầu tháng
$to_date = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d'); // Đến hôm nay

// Build Query
$sql = "SELECT t.*, c.name as cat_name, c.type as cat_type, w.name as wallet_name 
        FROM transactions t 
        JOIN categories c ON t.category_id = c.id
        JOIN wallets w ON t.wallet_id = w.id
        WHERE t.user_id = $user_id";

if ($filter_cat > 0) $sql .= " AND t.category_id = $filter_cat";
if ($filter_wallet > 0) $sql .= " AND t.wallet_id = $filter_wallet";
if (!empty($from_date)) $sql .= " AND t.transaction_date >= '$from_date'";
if (!empty($to_date))   $sql .= " AND t.transaction_date <= '$to_date'";

$sql .= " ORDER BY t    .transaction_date DESC, t.id DESC";
$result = $conn->query($sql);

// Lấy danh sách để fill vào dropdown filter
$cats = $conn->query("SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL");
$wallets = $conn->query("SELECT * FROM wallets WHERE user_id = $user_id");

include '../../includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/transaction_index.css">

<div class="page-header">
    <div>
        <h2>Sổ Giao dịch</h2>
        <p>Xem lại lịch sử thu chi chi tiết.</p>
    </div>
    <a href="create.php" class="btn btn-primary">
        <span>📸</span> Thêm Giao dịch
    </a>
</div>

<!-- KHUNG BỘ LỌC -->
<div class="card filter-card">
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label class="filter-label">Từ ngày</label>
            <input type="date" name="from" value="<?php echo $from_date; ?>" class="filter-control">
        </div>
        <div class="filter-group">
            <label class="filter-label">Đến ngày</label>
            <input type="date" name="to" value="<?php echo $to_date; ?>" class="filter-control">
        </div>
        <div class="filter-group">
            <label class="filter-label">Danh mục</label>
            <select name="cat" class="filter-control">
                <option value="0">-- Tất cả --</option>
                <?php while ($c = $cats->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php if ($filter_cat == $c['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Ví</label>
            <select name="wallet" class="filter-control">
                <option value="0">-- Tất cả --</option>
                <?php while ($w = $wallets->fetch_assoc()): ?>
                    <option value="<?php echo $w['id']; ?>" <?php if ($filter_wallet == $w['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($w['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-filter">Lọc</button>
            <a href="index.php" class="btn btn-reset btn-filter">Đặt lại</a>
        </div>
    </form>
</div>

<!-- BẢNG GIAO DỊCH -->
<div class="card table-card">
    <table class="custom-table">
        <thead>
            <tr>
                <th>Ngày</th>
                <th>Danh mục</th>
                <th>Ví thanh toán</th>
                <th>Số tiền</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="date-display"><?php echo date('d/m/Y', strtotime($row['transaction_date'])); ?></div>
                            <!-- Logic để hiện "Hôm nay" hoặc "Hôm qua" nếu muốn -->
                        </td>
                        <td>
                            <span class="badge <?php echo ($row['cat_type'] == 'income') ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo htmlspecialchars($row['cat_name']); ?>
                            </span>
                            <?php if (!empty($row['note'])): ?>
                                <div class="transaction-note"><?php echo htmlspecialchars($row['note']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['wallet_name']); ?></td>
                        <td>
                            <?php if ($row['cat_type'] == 'income'): ?>
                                <span class="amount-income">+<?php echo number_format($row['amount']); ?> đ</span>
                            <?php else: ?>
                                <span class="amount-expense">-<?php echo number_format($row['amount']); ?> đ</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view.php?id=<?php echo $row['id']; ?>" title="Xem chi tiết" class="action-link action-view">👁️ Xem</a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" title="Xóa giao dịch" class="action-link action-delete" onclick="return confirm('Bạn có chắc muốn xóa giao dịch này? Số dư ví sẽ được cập nhật lại (Rollback).')">❌ Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="empty-state">
                    <td colspan="5">
                        Không tìm thấy giao dịch nào phù hợp.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>