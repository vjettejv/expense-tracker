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
        <h1>Thêm Giao dịch</h1>

        <?php
        // Xử lý khi nhấn nút Lưu
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Khởi tạo mảng lỗi
            $errors = [];

            // Lấy và làm sạch dữ liệu
            $user_id = intval($_SESSION['user_id']);
            $wallet_id = isset($_POST['wallet_id']) ? intval($_POST['wallet_id']) : 0;
            $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
            $amount_raw = isset($_POST['amount']) ? trim($_POST['amount']) : '';
            $transaction_date = isset($_POST['transaction_date']) ? $_POST['transaction_date'] : '';

            // Bắt lỗi chi tiết
            if (empty($wallet_id)) {
                $errors[] = "Vui lòng chọn ví thanh toán.";
            }
            if (empty($category_id)) {
                $errors[] = "Vui lòng chọn danh mục.";
            }
            if (!is_numeric($amount_raw) || floatval($amount_raw) <= 0) {
                $errors[] = "Số tiền phải là một số hợp lệ và lớn hơn 0.";
            }
            if (empty($transaction_date)) {
                $errors[] = "Vui lòng chọn ngày giao dịch.";
            }

            // Nếu không có lỗi thì tiến hành xử lý
            if (empty($errors)) {
                $amount = floatval($amount_raw);

                // Lấy loại danh mục (Thu hay Chi)
                $sql_type = "SELECT type FROM categories WHERE id = ?";
                $stmt_type = $conn->prepare($sql_type);
                $stmt_type->bind_param("i", $category_id);
                $stmt_type->execute();
                $row_type = $stmt_type->get_result()->fetch_assoc();
                $type = $row_type['type'] ?? null;
                $stmt_type->close();

                if ($type) { // Chỉ tiếp tục nếu tìm thấy loại danh mục hợp lệ
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
                    echo '<p class="msg-error">Danh mục đã chọn không hợp lệ.</p>';
                }
            } else {
                // Hiển thị tất cả các lỗi đã tìm thấy
                foreach ($errors as $error) {
                    echo '<p class="msg-error">' . htmlspecialchars($error) . '</p>';
                }
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
            <select name="wallet_id" required oninvalid="this.setCustomValidity('Vui lòng chọn ví thanh toán.')" oninput="this.setCustomValidity('')">
                <option value="">-- Chọn ví --</option>
                <?php while($wallet = $result_wallets->fetch_assoc()): ?>
                    <option value="<?= $wallet['id'] ?>">
                        <?= htmlspecialchars($wallet['name']) ?> (Số dư: <?= number_format($wallet['balance']) ?> đ)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Danh mục:</label>
            <select name="category_id" required oninvalid="this.setCustomValidity('Vui lòng chọn danh mục giao dịch.')" oninput="this.setCustomValidity('')">
                <option value="">-- Chọn danh mục --</option>
                <?php while($cat = $result_cats->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['name']) ?> (<?= $cat['type'] == 'income' ? 'Thu' : 'Chi' ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Số tiền (VNĐ):</label>
            <input type="number" name="amount" min="1" required placeholder="Nhập số tiền..." oninvalid="this.setCustomValidity('Vui lòng nhập một số tiền hợp lệ.')" oninput="this.setCustomValidity('')">

            <label>Ngày giao dịch:</label>
            <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required oninvalid="this.setCustomValidity('Vui lòng chọn ngày giao dịch.')" oninput="this.setCustomValidity('')">

            <div class="button-group">
                <button type="submit">Lưu Giao Dịch</button>
                <button type="button" id="generate-qr-btn" class="btn-qr">💰 Tạo QR Nạp tiền</button>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generate-qr-btn');
    const amountInput = document.querySelector('input[name="amount"]');
    const categorySelect = document.querySelector('select[name="category_id"]');
    const walletSelect = document.querySelector('select[name="wallet_id"]');

    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const amount = amountInput.value;
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const categoryText = selectedOption.text;
            const categoryId = categorySelect.value;
            const walletId = walletSelect.value;

            // 1. Kiểm tra đã chọn ví chưa
            if (!walletId) {
                alert('Vui lòng chọn một ví để nạp tiền vào.');
                return;
            }

            // 2. Chỉ tạo QR cho giao dịch "Thu"
            if (!categoryText.includes('(Thu)')) {
                alert('Chức năng tạo QR chỉ dành cho các danh mục "Thu".');
                return;
            }

            // 3. Kiểm tra số tiền
            if (!amount || amount <= 0) {
                alert('Vui lòng nhập số tiền hợp lệ để tạo mã QR.');
                return;
            }

            // 4. Chuyển hướng sang trang tạo QR với các tham số cần thiết
            window.location.href = `generate_qr.php?amount=${amount}&wallet_id=${walletId}&category_id=${categoryId}`;
        });
    }
});
</script>

<?php 
// 6. Gọi Footer (Để đóng các thẻ </body>, </html>)
include '../../includes/footer.php'; 
?>