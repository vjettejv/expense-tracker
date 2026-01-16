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

$sql .= " ORDER BY t.transaction_date DESC, t.id DESC";
$result = $conn->query($sql);

// Lấy danh sách để fill vào dropdown filter
$cats = $conn->query("SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL");
$wallets = $conn->query("SELECT * FROM wallets WHERE user_id = $user_id");

include '../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;">Sổ Giao dịch</h2>
        <p style="color: #64748b; margin-top: 5px;">Xem lại lịch sử thu chi chi tiết.</p>
    </div>
    <a href="create.php" class="btn btn-primary">
        <span>📸</span> Thêm Giao dịch
    </a>
</div>

<!-- KHUNG BỘ LỌC -->
<div class="card" style="padding: 20px; background: #fff;">
    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b; display: block; margin-bottom: 5px;">Từ ngày</label>
            <input type="date" name="from" value="<?php echo $from_date; ?>" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b; display: block; margin-bottom: 5px;">Đến ngày</label>
            <input type="date" name="to" value="<?php echo $to_date; ?>" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b; display: block; margin-bottom: 5px;">Danh mục</label>
            <select name="cat" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                <option value="0">-- Tất cả --</option>
                <?php while ($c = $cats->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php if ($filter_cat == $c['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="font-size: 12px; font-weight: bold; color: #64748b; display: block; margin-bottom: 5px;">Ví</label>
            <select name="wallet" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px;">
                <option value="0">-- Tất cả --</option>
                <?php while ($w = $wallets->fetch_assoc()): ?>
                    <option value="<?php echo $w['id']; ?>" <?php if ($filter_wallet == $w['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($w['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="height: 38px;">Lọc</button>
        <a href="index.php" class="btn" style="background: #f1f5f9; color: #333; height: 38px;">Đặt lại</a>
    </form>
</div>

<!-- BẢNG GIAO DỊCH -->
<div class="card" style="padding: 0; overflow: hidden;">
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
                            <div style="font-weight: 600; color: #334155;"><?php echo date('d/m/Y', strtotime($row['transaction_date'])); ?></div>
                            <!-- Logic để hiện "Hôm nay" hoặc "Hôm qua" nếu muốn -->
                        </td>
                        <td>
                            <span class="badge <?php echo ($row['cat_type'] == 'income') ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo htmlspecialchars($row['cat_name']); ?>
                            </span>
                            <?php if (!empty($row['note'])): ?>
                                <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;"><?php echo htmlspecialchars($row['note']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['wallet_name']); ?></td>
                        <td>
                            <?php if ($row['cat_type'] == 'income'): ?>
                                <span style="color: #16a34a; font-weight: 700;">+<?php echo number_format($row['amount']); ?> đ</span>
                            <?php else: ?>
                                <span style="color: #dc2626; font-weight: 700;">-<?php echo number_format($row['amount']); ?> đ</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view.php?id=<?php echo $row['id']; ?>" title="Xem chi tiết" style="text-decoration: none; color: #3b82f6; margin-right: 15px;">👁️ Xem</a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" title="Xóa giao dịch" style="text-decoration: none; color: #ef4444;" onclick="return confirm('Bạn có chắc muốn xóa giao dịch này? Số dư ví sẽ được cập nhật lại (Rollback).')">❌ Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                        Không tìm thấy giao dịch nào phù hợp.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>