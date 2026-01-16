<?php
session_start();
require_once '../../config/db.php';
require_login();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($id == 0) {
    header("Location: index.php?error=invalid_id");
    exit();
}

// Truy vấn lấy thông tin chi tiết, quan trọng là phải kiểm tra user_id để bảo mật
$sql = "SELECT 
            t.id, 
            t.amount, 
            t.note,
            t.transaction_date, 
            c.name as category_name, 
            c.type as category_type,
            w.name as wallet_name
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        JOIN wallets w ON t.wallet_id = w.id
        WHERE t.id = ? AND t.user_id = ?"; // Chỉ lấy giao dịch của user đang đăng nhập

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    // Không tìm thấy giao dịch hoặc không có quyền truy cập
    header("Location: index.php?error=not_found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Giao dịch #<?php echo $id; ?></title>
    <!-- Nhúng thư viện html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="../../assets/css/transaction_view.css">
</head>
<body>

<div class="card" id="transaction-details">
    <h2>Chi tiết Giao dịch #<?php echo $data['id']; ?></h2>

    <div class="row">
        <span class="label">Ngày giao dịch:</span>
        <span class="value"><?php echo date('d/m/Y', strtotime($data['transaction_date'])); ?></span>
    </div>
    <div class="row">
        <span class="label">Loại giao dịch:</span>
        <span class="value"><?php echo ($data['category_type'] == 'income') ? '<span class="income">💰 Thu nhập</span>' : '<span class="expense">💸 Chi tiêu</span>'; ?></span>
    </div>
    <div class="row">
        <span class="label">Danh mục:</span>
        <span class="value"><?php echo htmlspecialchars($data['category_name']); ?></span>
    </div>
    <div class="row">
        <span class="label">Số tiền:</span>
        <span class="value amount-value <?php echo ($data['category_type'] == 'income') ? 'income' : 'expense'; ?>"><?php echo ($data['category_type'] == 'income') ? '+' : '-'; ?><?php echo number_format($data['amount'], 0, ',', '.'); ?> VNĐ</span>
    </div>
    <div class="row">
        <span class="label">Ví thanh toán:</span>
        <span class="value"><?php echo htmlspecialchars($data['wallet_name']); ?></span>
    </div>

    <?php if (!empty($data['note'])): ?>
    <div class="note-section">
        <span class="label">Ghi chú:</span>
        <div class="note-value"><?php echo htmlspecialchars($data['note']); ?></div>
    </div>
    <?php endif; ?>

    <div class="button-container">
        <a href="index.php" class="btn btn-back">← Quay lại</a>
        <button id="download-pdf" class="btn btn-download">Tải PDF</button>
    </div>
</div>

<script>
document.getElementById('download-pdf').addEventListener('click', function () {
    const element = document.getElementById('transaction-details');
    const transactionId = <?php echo $data['id']; ?>;
    const buttonContainer = document.querySelector('.button-container');
    const opt = { margin: [15, 10, 15, 10], filename: `Giao-dich-#${transactionId}.pdf`, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } };
    buttonContainer.style.display = 'none';
    html2pdf().set(opt).from(element).save().then(() => {
        buttonContainer.style.display = 'flex';
    });
});
</script>

</body>
</html>