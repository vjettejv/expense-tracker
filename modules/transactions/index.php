<?php
session_start();
require_once '../../config/db.php';
require_login(); 

$user_id = $_SESSION['user_id'];

// --- XỬ LÝ BỘ LỌC & VALIDATE ---
$filter_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
$filter_wallet = isset($_GET['wallet']) ? intval($_GET['wallet']) : 0;

$from_date = isset($_GET['from']) ? trim($_GET['from']) : date('Y-m-01');
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $from_date)) {
    $from_date = date('Y-m-01');
}

$to_date = isset($_GET['to']) ? trim($_GET['to']) : date('Y-m-d');
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $to_date)) {
    $to_date = date('Y-m-d');
}

// Xây dựng câu truy vấn cơ bản
$sql_base = "SELECT t.*, c.name as cat_name, c.type as cat_type, w.name as wallet_name 
             FROM transactions t 
             JOIN categories c ON t.category_id = c.id
             JOIN wallets w ON t.wallet_id = w.id
             WHERE t.user_id = $user_id";

if ($filter_cat > 0) $sql_base .= " AND t.category_id = $filter_cat";
if ($filter_wallet > 0) $sql_base .= " AND t.wallet_id = $filter_wallet";
if (!empty($from_date)) $sql_base .= " AND t.transaction_date >= '$from_date'";
if (!empty($to_date))   $sql_base .= " AND t.transaction_date <= '$to_date'";

$sql_base .= " ORDER BY t.transaction_date DESC, t.id DESC";

// --- XỬ LÝ XUẤT BÁO CÁO (EXPORT EXCEL - SỬA LỖI FONT) ---
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $filename = "Bao_cao_thu_chi_" . date('Ymd') . ".xls";
    
    // Header báo trình duyệt tải file Excel
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // QUAN TRỌNG: Thêm BOM để Excel nhận diện UTF-8
    echo "\xEF\xBB\xBF"; 

    // Xuất dữ liệu dạng bảng HTML để giữ định dạng và font chuẩn
    echo "<table border='1' style='font-family: Arial, sans-serif; border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0; font-weight: bold;'>
            <th>Ngày</th>
            <th>Danh mục</th>
            <th>Loại</th>
            <th>Ví</th>
            <th>Số tiền</th>
            <th>Ghi chú</th>
          </tr>";

    $result_export = $conn->query($sql_base);
    if ($result_export->num_rows > 0) {
        while($row = $result_export->fetch_assoc()) {
            $loai = ($row['cat_type'] == 'income') ? 'Thu nhập' : 'Chi tiêu';
            
            // Màu sắc cho đẹp (Optional)
            $color = ($row['cat_type'] == 'income') ? '#2ecc71' : '#ed4956';
            
            echo "<tr>";
            echo "<td>" . date('d/m/Y', strtotime($row['transaction_date'])) . "</td>";
            echo "<td>" . $row['cat_name'] . "</td>";
            echo "<td>" . $loai . "</td>";
            echo "<td>" . $row['wallet_name'] . "</td>";
            echo "<td style='color: $color; font-weight: bold;'>" . number_format($row['amount']) . "</td>";
            echo "<td>" . $row['note'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center'>Không có dữ liệu</td></tr>";
    }
    echo "</table>";
    
    exit();
}

// Thực thi truy vấn để hiển thị
$result = $conn->query($sql_base);

// Lấy danh sách cho Filter Form
$cats = $conn->query("SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL");
$wallets = $conn->query("SELECT * FROM wallets WHERE user_id = $user_id");

include '../../includes/header.php';
?>

<!-- Hiển thị thông báo Toast nếu có msg trên URL -->
<?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof showToast === 'function') showToast("Đã xóa giao dịch và cập nhật số dư ví!", "success");
        });
    </script>
<?php endif; ?>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof showToast === 'function') showToast("Không thể xóa giao dịch này.", "error");
        });
    </script>
<?php endif; ?>

<!-- Thêm script xử lý msg=success_add từ store.php -->
<?php if(isset($_GET['msg']) && $_GET['msg'] == 'success_add'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof showToast === 'function') showToast("Thêm giao dịch thành công!", "success");
        });
    </script>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;">Sổ Giao dịch</h2>
        <p style="color: #64748b; margin-top: 5px;">Xem lại lịch sử thu chi chi tiết.</p>
    </div>
    
    <div style="display: flex; gap: 10px;">
        <!-- Nút Xuất Excel -->
        <?php 
            $query_params = $_GET; 
            $query_params['export'] = 'excel'; 
            $export_link = '?' . http_build_query($query_params);
        ?>
        <a href="<?php echo $export_link; ?>" class="btn" style="background: #10b981; color: white; display: flex; align-items: center; gap: 5px;">
            📊 Xuất Excel
        </a>

        <!-- Nút Thêm Giao Dịch -->
        <a href="create.php" class="btn btn-primary">
            <span>📸</span> Thêm Giao dịch
        </a>
    </div>
</div>

<!-- KHUNG BỘ LỌC -->
<div class="card" style="padding: 20px; background: #fff;">
    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 150px;">
            <label class="form-label">Từ ngày</label>
            <input type="date" name="from" value="<?php echo $from_date; ?>" class="form-control">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label class="form-label">Đến ngày</label>
            <input type="date" name="to" value="<?php echo $to_date; ?>" class="form-control">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label class="form-label">Danh mục</label>
            <select name="cat" class="form-control">
                <option value="0">-- Tất cả --</option>
                <?php while($c = $cats->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php if($filter_cat==$c['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label class="form-label">Ví</label>
            <select name="wallet" class="form-control">
                <option value="0">-- Tất cả --</option>
                <?php while($w = $wallets->fetch_assoc()): ?>
                    <option value="<?php echo $w['id']; ?>" <?php if($filter_wallet==$w['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($w['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="height: 42px;">Lọc</button>
        <a href="index.php" class="btn" style="background: #f1f5f9; color: #333; height: 42px; display: inline-flex; align-items: center;">Đặt lại</a>
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
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: #334155;"><?php echo date('d/m/Y', strtotime($row['transaction_date'])); ?></div>
                        </td>
                        <td>
                            <span class="badge <?php echo ($row['cat_type'] == 'income') ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo htmlspecialchars($row['cat_name']); ?>
                            </span>
                            <?php if(!empty($row['note'])): ?>
                                <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;"><?php echo htmlspecialchars($row['note']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['wallet_name']); ?></td>
                        <td>
                            <?php if($row['cat_type'] == 'income'): ?>
                                <span style="color: #16a34a; font-weight: 700;">+<?php echo number_format($row['amount']); ?> đ</span>
                            <?php else: ?>
                                <span style="color: #dc2626; font-weight: 700;">-<?php echo number_format($row['amount']); ?> đ</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Link xóa trỏ về file delete.php riêng biệt -->
                            <a href="delete.php?id=<?php echo $row['id']; ?>" 
                               style="color: #ef4444; text-decoration: none; font-size: 13px; font-weight: 600;" 
                               onclick="return confirm('Bạn có chắc muốn xóa giao dịch này? Tiền sẽ được hoàn lại vào ví.')">
                               ❌ Xóa
                            </a>
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