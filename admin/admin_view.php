<?php
session_start();
include '../config/db.php';

// Kiểm tra quyền admin (nếu cần thiết, dựa trên logic các file khác)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Nếu không phải admin, có thể chuyển hướng hoặc báo lỗi
    // header("Location: ../../index.php");
    // exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id == 0) {
    die("ID giao dịch không hợp lệ.");
}

// Truy vấn lấy thông tin chi tiết
$sql = "SELECT 
            t.id, 
            t.amount, 
            t.transaction_date, 
            t.created_at,
            u.full_name, 
            u.username, 
            u.email,
            c.name as category_name, 
            c.type as category_type,
            w.name as wallet_name,
            w.balance as wallet_balance
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN categories c ON t.category_id = c.id
        JOIN wallets w ON t.wallet_id = w.id
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Không tìm thấy giao dịch này.");
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
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; padding-top: 50px; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 500px; }
        h2 { text-align: center; color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .label { font-weight: bold; color: #555; }
        .value { color: #333; }
        .income { color: #2ecc71; font-weight: bold; }
        .expense { color: #e74c3c; font-weight: bold; }
        .button-container { display: flex; gap: 10px; margin-top: 30px; }
        .btn { flex: 1; text-align: center; color: white; padding: 12px 0; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 15px; font-family: Arial, sans-serif; }
        .btn-back { background: #6c757d; }
        .btn-back:hover { background: #5a6268; }
        .btn-download { background: #007bff; }
        .btn-download:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="card">
    <h2>Chi tiết Giao dịch #<?php echo $data['id']; ?></h2>

    <div class="row">
        <span class="label">Người thực hiện:</span>
        <span class="value"><?php echo htmlspecialchars($data['full_name']); ?> (<?php echo htmlspecialchars($data['username']); ?>)</span>
    </div>

    <div class="row">
        <span class="label">Ngày giao dịch:</span>
        <span class="value"><?php echo date('d/m/Y', strtotime($data['transaction_date'])); ?></span>
    </div>

    <div class="row">
        <span class="label">Loại giao dịch:</span>
        <span class="value">
            <?php echo ($data['category_type'] == 'income') ? '<span class="income">Thu nhập</span>' : '<span class="expense">Chi tiêu</span>'; ?>
        </span>
    </div>

    <div class="row">
        <span class="label">Danh mục:</span>
        <span class="value"><?php echo htmlspecialchars($data['category_name']); ?></span>
    </div>

    <div class="row">
        <span class="label">Số tiền:</span>
        <span class="value" style="font-size: 1.2em; font-weight: bold;"><?php echo number_format($data['amount'], 0, ',', '.'); ?> VNĐ</span>
    </div>

    <div class="row">
        <span class="label">Ví thanh toán:</span>
        <span class="value"><?php echo htmlspecialchars($data['wallet_name']); ?></span>
    </div>

    <div class="button-container">
        <a href="admin_report.php" class="btn btn-back">← Quay lại</a>
        <button id="download-pdf" class="btn btn-download">Tải PDF</button>
    </div>
</div>

<script>
document.getElementById('download-pdf').addEventListener('click', function () {
    const element = document.querySelector('.card');
    const transactionId = <?php echo $data['id']; ?>;
    const buttonContainer = document.querySelector('.button-container');

    // Tùy chọn cho file PDF
    const opt = {
      margin:       15,
      filename:     `Giao-dich-#${transactionId}.pdf`,
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true, logging: false },
      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Tạm thời ẩn các nút trước khi tạo PDF
    buttonContainer.style.display = 'none';

    // Tạo và tải file PDF
    html2pdf().set(opt).from(element).save().then(() => {
        // Hiện lại các nút sau khi hoàn tất
        buttonContainer.style.display = 'flex';
    });
});
</script>

</body>
</html>