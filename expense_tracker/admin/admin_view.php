<?php
session_start();
include '../config/db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); // Chuyển về trang đăng nhập
    exit();
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
    <link rel="stylesheet" href="../assets/css/admin_view.css">
    <!-- Thư viện để tạo PDF từ HTML -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>

<div class="view-card">
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

    <div class="btn-group">
        <button onclick="downloadPDF()" class="btn btn-download">📄 Tải về PDF</button>
        <a href="admin_report.php" class="btn btn-back">← Quay lại</a>
    </div>
</div>

<script>
    function downloadPDF() {
        const cardElement = document.querySelector('.view-card');
        const transactionId = "<?php echo $data['id']; ?>";
        const fileName = `chi-tiet-giao-dich-${transactionId}.pdf`;

        // Tạm thời ẩn nút bấm để không xuất hiện trong file PDF
        cardElement.querySelector('.btn-group').style.display = 'none';

        html2canvas(cardElement, {
            scale: 2, // Tăng độ phân giải cho ảnh chụp
            useCORS: true
        }).then(canvas => {
            // Hiện lại nút bấm sau khi đã chụp ảnh
            cardElement.querySelector('.btn-group').style.display = 'flex';

            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf;

            // Khởi tạo file PDF khổ A4
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            const pdfWidth = pdf.internal.pageSize.getWidth();
            const canvasWidth = canvas.width;
            const canvasHeight = canvas.height;
            const ratio = canvasWidth / canvasHeight;

            const imgWidth = pdfWidth - 20; // Chiều rộng ảnh trong PDF, trừ 10mm lề mỗi bên
            const imgHeight = imgWidth / ratio;

            pdf.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight); // Thêm ảnh vào PDF với lề 10mm
            pdf.save(fileName);
        });
    }
</script>
</body>
</html>