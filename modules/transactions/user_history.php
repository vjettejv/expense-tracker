<?php
session_start();
// 1. Kết nối Database
include '../../config/db.php';

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 3. Xử lý các tham số lọc từ URL (GET)
$filter_category = isset($_GET['filter_category']) ? intval($_GET['filter_category']) : 0;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// 4. Lấy danh sách danh mục để hiển thị trong bộ lọc
$sql_cats = "SELECT id, name FROM categories ORDER BY name";
$result_cats = $conn->query($sql_cats);

// 5. Truy vấn danh sách giao dịch (JOIN với categories để lấy tên và loại)
$sql = "SELECT t.*, c.name as category_name, c.type as category_type 
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?";

$params = [$user_id];
$types = "i";

if (!empty($from_date)) {
    $sql .= " AND t.transaction_date >= ?";
    $params[] = $from_date;
    $types .= "s";
}
if (!empty($to_date)) {
    $sql .= " AND t.transaction_date <= ?";
    $params[] = $to_date;
    $types .= "s";
}
if ($filter_category > 0) {
    $sql .= " AND t.category_id = ?";
    $params[] = $filter_category;
    $types .= "i";
}

$sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// 6. Gọi Header
// QUAN TRỌNG: File header.php của bạn phải kết thúc ở thẻ mở <div class="container">
include '../../includes/header.php';
?>

<!-- Gọi file CSS riêng cho trang lịch sử -->
<link rel="stylesheet" href="../../assets/css/user_history.css">

<div class="user-history-content">
    <div class="history-card">
        <div class="history-header">
            <h1>Lịch sử giao dịch</h1>
            <p>Theo dõi các khoản thu chi của bạn</p>
        </div>

        <!-- Bộ lọc tìm kiếm -->
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Từ ngày</label>
                    <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Đến ngày</label>
                    <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
                </div>

                <div class="filter-group">
                    <label>Danh mục</label>
                    <select name="filter_category">
                        <option value="0">-- Tất cả danh mục --</option>
                        <?php if ($result_cats->num_rows > 0): ?>
                            <?php while($cat = $result_cats->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filter_category == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-filter">Áp dụng bộ lọc</button>
        </form>

        <!-- Bảng kết quả -->
        <div class="table-container">
            <?php if ($result->num_rows == 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">📂</div>
                    <p>Không có dữ liệu giao dịch nào được tìm thấy.</p>
                    <a href="user_history.php" class="reset-link">Xem tất cả giao dịch</a>
                </div>
            <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Danh mục</th>
                            <th>Số tiền</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $stt = 1; while ($transaction = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="stt-cell"><?= $stt++ ?></td>
                                <td>
                                    <span class="cat-badge <?= $transaction['category_type'] ?>">
                                        <?= htmlspecialchars($transaction['category_name']) ?>
                                    </span>
                                </td>
                                <td class="amount-cell <?= $transaction['category_type'] ?>">
                                    <?= ($transaction['category_type'] == 'income' ? '+' : '-') ?>
                                    <?= number_format($transaction['amount'], 0, ',', '.') ?> đ
                                </td>
                                <td class="date-cell">
                                    <?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="history-footer-actions">
            <a href="user_add.php" class="btn-back-add">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Thêm giao dịch mới
            </a>
        </div>
    </div>
</div>

<?php 
// 7. Gọi Footer
// QUAN TRỌNG: File này sẽ đóng thẻ </div> của container, sau đó là </body> và </html>
include '../../includes/footer.php'; 
?>