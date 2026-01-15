<?php
session_start();
include '../config/db.php';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/admin_report.css">

<h1>Báo cáo Tổng hợp</h1>
<?php // --- XỬ LÝ XÓA GIAO DỊCH VÀ CẬP NHẬT VÍ ---
        function deleteTransaction($conn, $id) {
        // 1. Lấy thông tin giao dịch cũ
        $sql = "SELECT wallet_id, amount, category_id FROM transactions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $wallet_id = $row['wallet_id'];
            $amount = $row['amount'];
            $category_id = $row['category_id'];
            
            // 2. Lấy loại danh mục
            $sql_type = "SELECT type FROM categories WHERE id = ?";
            $stmt_type = $conn->prepare($sql_type);
            $stmt_type->bind_param("i", $category_id);
            $stmt_type->execute();
            $type_res = $stmt_type->get_result();
            if ($type_row = $type_res->fetch_assoc()) {
                $type = $type_row['type'];
                
                // 3. Cập nhật số dư ví (Hoàn tiền)
                if ($type == 'income') {
                    $conn->query("UPDATE wallets SET balance = balance - $amount WHERE id = $wallet_id");
                } else {
                    $conn->query("UPDATE wallets SET balance = balance + $amount WHERE id = $wallet_id");
                }
            }
            
            // 4. Xóa giao dịch
            $conn->query("DELETE FROM transactions WHERE id = $id");
        }
    }

    // Xử lý xóa nhiều dòng (Checkbox)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_items'])) {
        if (!empty($_POST['items'])) {
            foreach ($_POST['items'] as $id) {
                deleteTransaction($conn, intval($id));
            }
            echo "<script>alert('Đã xóa và cập nhật số dư ví thành công!');</script>";
        }
    }

    // Xử lý Reset (Xóa hết)
    if (isset($_GET['reset']) && $_GET['reset'] == 1) {
        $res_all = $conn->query("SELECT id FROM transactions");
        while ($row = $res_all->fetch_assoc()) {
            deleteTransaction($conn, $row['id']);
        }
        echo "<script>alert('Đã reset toàn bộ dữ liệu!'); window.location.href='admin_report.php';</script>";
    }
    // ------------------------------------------

    // Lấy tham số lọc
    $search_user = isset($_GET['search_user']) ? trim($_GET['search_user']) : '';
    $filter_category = isset($_GET['filter_category']) ? intval($_GET['filter_category']) : 0;
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
    $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
    
    // Giữ lại tham số lọc khi submit form xóa
    $queryString = http_build_query(['search_user' => $search_user, 'filter_category' => $filter_category, 'from_date' => $from_date, 'to_date' => $to_date]);

    // Xác định định dạng thời gian và tiêu đề cột
    $dateFormat = '%d/%m/%Y';
    $timeHeader = 'Ngày';

    // Lấy danh sách danh mục để hiển thị trong bộ lọc
    $sql_cats = "SELECT id, name FROM categories ORDER BY name";
    $result_cats = $conn->query($sql_cats);
    ?>

    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label>Từ ngày:</label>
            <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
        </div>
        
        <div class="filter-group">
            <label>Đến ngày:</label>
            <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
        </div>

        <div class="filter-group">
            <label>Tìm tên:</label>
            <input type="text" name="search_user" value="<?php echo htmlspecialchars($search_user); ?>" placeholder="Nhập tên...">
        </div>
        
        <div class="filter-group">
            <label>Danh mục:</label>
            <select name="filter_category">
                <option value="0">-- Tất cả --</option>
                <?php 
                if ($result_cats && $result_cats->num_rows > 0) {
                    while($cat = $result_cats->fetch_assoc()) {
                        $selected = ($filter_category == $cat['id']) ? 'selected' : '';
                        echo "<option value='" . $cat['id'] . "' $selected>" . htmlspecialchars($cat['name']) . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn-search">Tìm kiếm</button>
    </form>

    <?php
    $sql = "SELECT 
                t.id,
                DATE_FORMAT(t.transaction_date, '$dateFormat') as time_period, 
                u.id as user_id,
                u.full_name,
                c.id as category_id,
                c.name as category_name, 
                t.amount as total 
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            JOIN users u ON t.user_id = u.id
            WHERE 1=1";
    
    if (!empty($search_user)) {
        $sql .= " AND u.full_name LIKE '%" . $conn->real_escape_string($search_user) . "%'";
    }

    if (!empty($from_date)) {
        $sql .= " AND t.transaction_date >= '" . $conn->real_escape_string($from_date) . "'";
    }

    if (!empty($to_date)) {
        $sql .= " AND t.transaction_date <= '" . $conn->real_escape_string($to_date) . "'";
    }

    if ($filter_category > 0) {
        $sql .= " AND t.category_id = " . $filter_category;
    }

    $sql .= " ORDER BY t.transaction_date DESC, t.id DESC"; 

    $result = $conn->query($sql);
    ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <form method="POST" action="" onsubmit="return confirm('Bạn có chắc chắn muốn xóa các mục đã chọn?');">
                <div class="table-header-actions">
                    <span>Tổng cộng: <strong><?php echo $result->num_rows; ?></strong> giao dịch</span>
                    <button type="button" onclick="window.location.href='admin_report.php'" class="btn-reload">🔄 Tải lại</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="delete-col">Chọn</th>
                            <th><?php echo $timeHeader; ?></th>
                            <th>Người dùng</th>
                            <th>Danh mục</th>
                            <th>Số Tiền</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="delete-col">
                                    <input type="checkbox" name="items[]" value="<?php echo $row['id']; ?>">
                                </td>
                                <td><?php echo $row['time_period']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td><?php echo number_format($row['total'], 0, ',', '.'); ?> VNĐ</td>
                                <td>
                                    <a href="admin_view.php?id=<?php echo $row['id']; ?>" class="action-link" style="margin-right: 5px; text-decoration: none;">👁️ Xem</a>
                                    <a href="admin_edit.php?id=<?php echo $row['id']; ?>" class="action-link">✏️ Sửa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="table-actions">
                    <div class="confirm-delete-container">
                        <button type="submit" name="delete_items" class="btn-danger">Xác nhận xóa mục đã chọn</button>
                        <button type="button" onclick="toggleDeleteMode()" class="btn-secondary">Hủy</button>
                    </div>
                    <div class="start-delete-container">
                        <button type="button" onclick="toggleDeleteMode()" class="btn-secondary">🗑️ Chọn để xóa</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p style="text-align: center; color: #8e8e8e; margin: 40px 0;">Chưa có dữ liệu báo cáo.</p>
        <?php endif; ?>

        <!-- Nút Reset dữ liệu nguy hiểm, chỉ dành cho Admin -->
        <div class="reset-container">
            <a href="?reset=1" onclick="return confirm('CẢNH BÁO NGUY HIỂM:\nBạn có chắc chắn muốn xóa TOÀN BỘ dữ liệu giao dịch trong hệ thống?\nHành động này KHÔNG THỂ hoàn tác!');">
                <button type="button" class="btn-danger" style="font-weight: bold;">⚠️ Xóa tất cả dữ liệu (Reset Database)</button>
            </a>
        </div>

        <script>
            function toggleDeleteMode() {
                document.body.classList.toggle('delete-mode-active');
            }
        </script>

<?php include '../includes/footer.php'; ?>