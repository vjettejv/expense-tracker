<?php
session_start();
// 1. Kết nối Database
require_once '../../config/db.php';

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// 3. Gọi Header
include '../../includes/header.php';
?>

<!-- 1. Nhúng thư viện Tesseract.js -->
<script src='https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js'></script>

<!-- 2. Link CSS -->
<link rel="stylesheet" href="../../assets/css/user_add.css">

<div class="container">
    <div class="form-container">
        <a href="../../index.php" style="text-decoration: none; color: #666; font-weight: bold;">&larr; Quay lại Dashboard</a>
        <h2 class="form-title">Thêm Giao Dịch</h2>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = intval($_SESSION['user_id']);
            $wallet_id = isset($_POST['wallet_id']) ? intval($_POST['wallet_id']) : 0;
            $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
            $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
            $transaction_date = $_POST['transaction_date'];

            if ($wallet_id > 0 && $category_id > 0 && $amount > 0) {
                // Lấy loại danh mục
                $stmt = $conn->prepare("SELECT type FROM categories WHERE id = ?");
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $type = $stmt->get_result()->fetch_assoc()['type'];
                $stmt->close();

                // Thêm giao dịch
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, wallet_id, category_id, amount, transaction_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiids", $user_id, $wallet_id, $category_id, $amount, $transaction_date);
                
                if ($stmt->execute()) {
                    // Cập nhật ví
                    $sql_update = ($type == 'income') ? 
                        "UPDATE wallets SET balance = balance + ? WHERE id = ?" : 
                        "UPDATE wallets SET balance = balance - ? WHERE id = ?";
                    $stmt_up = $conn->prepare($sql_update);
                    $stmt_up->bind_param("di", $amount, $wallet_id);
                    $stmt_up->execute();
                    $stmt_up->close();

                    echo '<div class="msg-box msg-success">✅ Thêm thành công!</div>';
                } else {
                    echo '<div class="msg-box msg-error">Lỗi: ' . $stmt->error . '</div>';
                }
                $stmt->close();
            } else {
                echo '<div class="msg-box msg-error">Vui lòng nhập đầy đủ thông tin!</div>';
            }
        }

        $wallets = $conn->query("SELECT * FROM wallets WHERE user_id = " . $_SESSION['user_id']);
        $cats = $conn->query("SELECT * FROM categories ORDER BY type, name");
        ?>

        <!-- 2. Khu vực Scan ảnh -->
        <div class="scan-area">
            <label for="bill_image" class="scan-label">
                📸 Tải ảnh Giao dịch ngân hàng (Auto-fill)
            </label>
            <input type="file" id="bill_image" accept="image/*">
            <div id="scan-status">Chưa chọn ảnh</div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Chọn Ví:</label>
                <select name="wallet_id" class="form-control" required>
                    <option value="">-- Chọn ví --</option>
                    <?php while($w = $wallets->fetch_assoc()): ?>
                        <option value="<?php echo $w['id']; ?>">
                            <?php echo $w['name']; ?> (<?php echo number_format($w['balance']); ?> đ)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Danh mục:</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php while($c = $cats->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>">
                            <?php echo $c['name']; ?> (<?php echo ($c['type']=='income' ? 'Thu' : 'Chi'); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Số tiền (VNĐ):</label>
                <input type="number" id="input_amount" name="amount" class="form-control" min="0" placeholder="0" required>
                <small style="color: #888;">(Có thể nhập tay hoặc dùng tính năng Scan ảnh ở trên)</small>
            </div>

            <div class="form-group">
                <label class="form-label">Ngày giao dịch:</label>
                <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <button type="submit" class="btn-submit">Lưu Giao Dịch</button>
        </form>

        <a href="user_history.php" class="link-history">📜 Xem lịch sử giao dịch</a>
    </div>
</div>

<!-- 3. Script xử lý OCR (Quét ảnh) -->
<script>
    const fileInput = document.getElementById('bill_image');
    const statusText = document.getElementById('scan-status');
    const amountInput = document.getElementById('input_amount');

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) return;

        statusText.innerHTML = '<div class="loading-spinner"></div> Đang quét ảnh... Vui lòng đợi!';
        
        try {
            // Sử dụng Tesseract để quét (ngôn ngữ: tiếng Việt)
            const { data: { text } } = await Tesseract.recognize(
                file,
                'vie', 
                { logger: m => console.log(m) }
            );

            console.log("Văn bản quét được:", text);

            // --- LOGIC MỚI: TÌM SỐ TIỀN CÓ ĐƠN VỊ TIỀN TỆ ---
            // Tìm các chuỗi số đi kèm với VND, VNĐ, đ, d (không phân biệt hoa thường)
            // Ví dụ: 100.000 VND, 50,000d, 200.000 VNĐ
            // Regex: \d{1,3}(?:[.,]\d{3})* -> tìm số có dấu phân cách
            // \s* -> khoảng trắng tùy ý
            // (?:VND|VNĐ|đ|d) -> đơn vị tiền tệ
            
            // Tìm tất cả các chuỗi khớp với mẫu "Số tiền + Đơn vị"
            const moneyMatches = text.match(/[\d,.]+\s*(?:VND|VNĐ|đ|d)/gi);
            
            let foundAmount = 0;

            if (moneyMatches) {
                console.log("Các chuỗi tiền tệ tìm thấy:", moneyMatches);
                
                // Duyệt qua các chuỗi tìm được để lấy số lớn nhất (thường là số tiền giao dịch)
                moneyMatches.forEach(str => {
                    // Loại bỏ chữ cái và ký tự lạ, chỉ giữ lại số
                    let cleanStr = str.replace(/[^\d]/g, '');
                    let val = parseInt(cleanStr);

                    // Lọc nhiễu: Số tiền thường > 1000 và không quá dài (tránh nhầm mã giao dịch dài dằng dặc)
                    if (!isNaN(val) && val > 1000 && cleanStr.length < 15) {
                        if (val > foundAmount) {
                            foundAmount = val;
                        }
                    }
                });
            }

            // Nếu không tìm thấy bằng cách trên (do thiếu chữ VND), thử tìm số đứng sau từ khóa
            if (foundAmount === 0) {
                // Tìm số đứng sau chữ "Số tiền", "Giao dịch", "Amount"
                const keywordMatches = text.match(/(?:Số tiền|Giao dịch|Amount)[:\s]+([\d,.]+)/i);
                if (keywordMatches && keywordMatches[1]) {
                    let cleanStr = keywordMatches[1].replace(/[^\d]/g, '');
                    let val = parseInt(cleanStr);
                    if (!isNaN(val) && val > 1000) {
                        foundAmount = val;
                    }
                }
            }

            // Cập nhật giao diện
            if (foundAmount > 0) {
                amountInput.value = foundAmount;
                statusText.innerHTML = '✅ Đã tìm thấy số tiền: <b>' + new Intl.NumberFormat().format(foundAmount) + ' đ</b>';
            } else {
                statusText.innerHTML = '⚠️ Không tìm thấy số tiền rõ ràng (Thử ảnh nét hơn hoặc có chữ VND).';
            }

        } catch (error) {
            console.error(error);
            statusText.innerHTML = '❌ Lỗi khi quét ảnh. Thử lại sau.';
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>