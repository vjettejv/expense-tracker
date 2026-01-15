<?php
include '../config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

// Lấy thông tin giao dịch hiện tại
$sql = "SELECT t.*, c.type as category_type FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$transaction) {
    die("Giao dịch không tồn tại!");
}

// Xử lý khi Admin nhấn Lưu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_wallet_id = intval($_POST['wallet_id']);
    $new_category_id = intval($_POST['category_id']);
    $new_amount = floatval($_POST['amount']);
    $new_date = $_POST['transaction_date'];

    // Lấy loại danh mục mới (Thu hay Chi)
    $sql_type = "SELECT type FROM categories WHERE id = ?";
    $stmt_type = $conn->prepare($sql_type);
    $stmt_type->bind_param("i", $new_category_id);
    $stmt_type->execute();
    $new_type = $stmt_type->get_result()->fetch_assoc()['type'];
    $stmt_type->close();

    // 1. HOÀN TÁC số dư ở ví CŨ (dựa trên dữ liệu cũ)
    // Nếu cũ là Thu -> Trừ đi. Nếu cũ là Chi -> Cộng lại.
    $old_wallet_id = $transaction['wallet_id'];
    $old_amount = $transaction['amount'];
    $old_type = $transaction['category_type'];

    if ($old_type == 'income') {
        $conn->query("UPDATE wallets SET balance = balance - $old_amount WHERE id = $old_wallet_id");
    } else {
        $conn->query("UPDATE wallets SET balance = balance + $old_amount WHERE id = $old_wallet_id");
    }

    // 2. CẬP NHẬT giao dịch
    $sql_update = "UPDATE transactions SET wallet_id=?, category_id=?, amount=?, transaction_date=? WHERE id=?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("idisi", $new_wallet_id, $new_category_id, $new_amount, $new_date, $id);
    
    if ($stmt_update->execute()) {
        // 3. ÁP DỤNG số dư vào ví MỚI (dựa trên dữ liệu mới)
        if ($new_type == 'income') {
            $conn->query("UPDATE wallets SET balance = balance + $new_amount WHERE id = $new_wallet_id");
        } else {
            $conn->query("UPDATE wallets SET balance = balance - $new_amount WHERE id = $new_wallet_id");
        }
        
        echo "<script>alert('Cập nhật thành công!'); window.location.href='admin_report.php';</script>";
        exit();
    } else {
        $message = "Lỗi: " . $conn->error;
    }
}

// Lấy danh sách Ví và Danh mục để hiển thị dropdown
$wallets = $conn->query("SELECT * FROM wallets WHERE user_id = " . $transaction['user_id']);
$categories = $conn->query("SELECT * FROM categories ORDER BY type, name");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Giao Dịch</title>
    <link rel="stylesheet" href="../assets/css/admin_edit.css">
</head>
<body>
    <div class="container">
        <h2>Sửa Giao Dịch #<?php echo $id; ?></h2>
        
        <?php if (!empty($message)): ?>
            <div class="error-msg"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label>Ngày giao dịch:</label>
            <input type="date" name="transaction_date" value="<?php echo $transaction['transaction_date']; ?>" required>

            <label>Số tiền:</label>
            <input type="number" name="amount" step="0.01" value="<?php echo $transaction['amount']; ?>" required>

            <label>Ví thanh toán:</label>
            <select name="wallet_id">
                <?php while ($w = $wallets->fetch_assoc()): ?>
                    <option value="<?php echo $w['id']; ?>" <?php if($w['id'] == $transaction['wallet_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($w['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Danh mục:</label>
            <select name="category_id">
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php if($c['id'] == $transaction['category_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($c['name']) . " (" . ($c['type']=='income'?'Thu':'Chi') . ")"; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="btn-group">
                <button type="submit">Lưu thay đổi</button>
                <a href="admin_report.php" class="back-btn">Hủy</a>
            </div>
        </form>
    </div>
</body>
</html>