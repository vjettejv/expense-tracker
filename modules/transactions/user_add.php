<?php
session_start();
// 1. Kết nối Database
require_once '../../config/db.php';

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// 3. Gọi Header (Header đã chứa <!DOCTYPE html>, <html>, <head>, <body> và thanh điều hướng)
include '../../includes/header.php';
?>

<!-- 4. Nhúng CSS riêng (Đã tách khỏi inline) -->
<link rel="stylesheet" href="../../assets/css/user_add.css">

<!-- 5. Nội dung chính: Chỉ viết phần "ruột" của trang -->
<div class="user-add-wrapper">
    <div class="container-custom">
        <a href="../../index.php" class="back-link">← Quay lại Dashboard</a>
        <h1>Thêm Giao dịch</h1>

        <?php
        // Xử lý khi nhấn nút Lưu
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = intval($_SESSION['user_id']);
            $wallet_id = isset($_POST['wallet_id']) ? intval($_POST['wallet_id']) : 0;
            $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
            $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
            $transaction_date = isset($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d');

            if ($user_id > 0 && $wallet_id > 0 && $category_id > 0 && $amount > 0) {
                // Lấy loại danh mục (Thu hay Chi)
                $sql_type = "SELECT type FROM categories WHERE id = ?";
                $stmt_type = $conn->prepare($sql_type);
                $stmt_type->bind_param("i", $category_id);
                $stmt_type->execute();
                $row_type = $stmt_type->get_result()->fetch_assoc();
                $type = $row_type['type'];
                $stmt_type->close();

                // Lưu vào bảng transactions
                $sql = "INSERT INTO transactions (user_id, wallet_id, category_id, amount, transaction_date) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiids", $user_id, $wallet_id, $category_id, $amount, $transaction_date);

                if ($stmt->execute()) {
                    // Cập nhật số dư trong ví
                    $update_sql = ($type == 'income') ? 
                        "UPDATE wallets SET balance = balance + ? WHERE id = ?" : 
                        "UPDATE wallets SET balance = balance - ? WHERE id = ?";
                    
                    $stmt_update = $conn->prepare($update_sql);
                    $stmt_update->bind_param("di", $amount, $wallet_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                    
                    echo '<p class="msg-success">✅ Thêm giao dịch thành công!</p>';
                } else {
                    echo '<p class="msg-error">Lỗi hệ thống: ' . $stmt->error . '</p>';
                }
                $stmt->close();
            } else {
                echo '<p class="msg-error">Vui lòng nhập đầy đủ thông tin hợp lệ!</p>';
            }
        }

        // Lấy dữ liệu cho các ô chọn (Dropdown)
        $result_cats = $conn->query("SELECT id, name, type FROM categories ORDER BY type, name");
        $user_id = intval($_SESSION['user_id']);
        $stmt_wallets = $conn->prepare("SELECT id, name, balance FROM wallets WHERE user_id = ?");
        $stmt_wallets->bind_param("i", $user_id);
        $stmt_wallets->execute();
        $result_wallets = $stmt_wallets->get_result();
        ?>

        <form method="post">
            <label>Ví thanh toán:</label>
            <select name="wallet_id" required>
                <option value="">-- Chọn ví --</option>
                <?php while($wallet = $result_wallets->fetch_assoc()): ?>
                    <option value="<?= $wallet['id'] ?>">
                        <?= htmlspecialchars($wallet['name']) ?> (Số dư: <?= number_format($wallet['balance']) ?> đ)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Danh mục:</label>
            <select name="category_id" required>
                <option value="">-- Chọn danh mục --</option>
                <?php while($cat = $result_cats->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['name']) ?> (<?= $cat['type'] == 'income' ? 'Thu' : 'Chi' ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Số tiền (VNĐ):</label>
            <input type="number" name="amount" min="1" required placeholder="Nhập số tiền...">

            <label>Ngày giao dịch:</label>
            <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>

            <button type="submit">Lưu Giao Dịch</button>
        </form>

        <a href="user_history.php" class="btn-secondary">📜 Xem lịch sử giao dịch</a>
    </div>
</div>

<?php 
// 6. Gọi Footer (Để đóng các thẻ </body>, </html>)
include '../../includes/footer.php'; 
?>