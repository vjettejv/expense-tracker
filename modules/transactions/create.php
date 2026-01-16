<?php
session_start();
require_once '../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];

// Lấy danh sách Ví
$wallets = $conn->query("SELECT * FROM wallets WHERE user_id = $user_id");

// Lấy danh sách Danh mục (Gộp chung và riêng)
$categories = $conn->query("SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL ORDER BY type, name");

include '../../includes/header.php';
?>

<!-- Nhúng Tesseract.js để quét ảnh -->
<script src='https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js'></script>
<link rel="stylesheet" href="../../assets/css/transaction_create.css">

<div class="create-transaction-container">
    <a href="index.php" class="back-link">
        <span>←</span> Quay lại sổ giao dịch
    </a>

    <div class="transaction-layout">
        
        <!-- CỘT TRÁI: Form Nhập liệu -->
        <div class="card form-column">
            <h2 class="form-title">
                📝 Thêm Giao dịch Mới
            </h2>

            <form action="store.php" method="POST" id="transForm">
                
                <!-- Chọn Loại Giao dịch (Tab giả) -->
                <div class="type-selector">
                    <label>
                        <input type="radio" name="type_selector" value="expense" checked onchange="filterCategories('expense')">
                        <div class="type-btn" id="btn-expense">
                            💸 Chi Tiêu
                        </div>
                    </label>
                    <label>
                        <input type="radio" name="type_selector" value="income" onchange="filterCategories('income')">
                        <div class="type-btn" id="btn-income">
                            💰 Thu Nhập
                        </div>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Số tiền (VNĐ)</label>
                    <input type="number" name="amount" id="amount" class="amount-input" placeholder="0" required>
                    <small class="form-hint">Mẹo: Bạn có thể quét ảnh hóa đơn ở bên phải để tự điền số tiền.</small>
                </div>

                <div class="grid-2-col">
                    <div>
                        <label class="form-label">Ví thanh toán</label>
                        <select name="wallet_id" required class="form-control">
                            <?php while($w = $wallets->fetch_assoc()): ?>
                                <option value="<?php echo $w['id']; ?>">
                                    <?php echo htmlspecialchars($w['name']); ?> (<?php echo number_format($w['balance']); ?> đ)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Ngày giao dịch</label>
                        <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Danh mục</label>
                    <select name="category_id" id="category_select" required class="form-control">
                        <!-- Options sẽ được JS render lại -->
                        <?php 
                        // Lưu danh mục vào mảng JS để lọc
                        $js_cats = [];
                        while($c = $categories->fetch_assoc()) {
                            $js_cats[] = $c;
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group-lg">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" placeholder="Ví dụ: Ăn trưa cùng đồng nghiệp..." rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-submit-full">
                    Lưu Giao Dịch
                </button>
            </form>
        </div>

        <!-- CỘT PHẢI: Contextual Box (OCR/QR) -->
        <div class="context-column">
            
            <!-- OCR Scanner -->
            <div id="ocr-card" class="card context-card">
                <h3 class="context-title">📸 Quét Hóa Đơn</h3>
                <p class="context-desc">
                    Tải ảnh hóa đơn lên, AI sẽ tự động đọc tổng tiền giúp bạn.
                </p>

                <div>
                    <label for="bill_image" class="ocr-upload-box">
                        <span>📤</span><br>
                        <span>Chọn ảnh hóa đơn</span>
                        <input type="file" id="bill_image" accept="image/*">
                    </label>
                </div>

                <div id="ocr_status" class="ocr-status">
                    Chưa có ảnh nào được chọn.
                </div>
                
                <div id="loading_spinner" class="loading-spinner">
                    <div class="spinner"></div>
                    <span class="loading-text">Đang đọc ảnh...</span>
                </div>
            </div>

            <!-- QR Code Generator -->
            <div id="qr-card" class="card context-card">
                <h3 class="context-title">💸 Nhận Thanh Toán VietQR</h3>
                <p class="context-desc">
                    Hiển thị mã QR để nhận tiền vào tài khoản của bạn.
                </p>
                <div style="text-align: center;">
                    <img id="qr-code-image" src="" alt="VietQR Code" class="qr-code-image">
                </div>
                <div class="qr-info">
                    <p>Tài khoản: <b>DAM DINH LONG</b></p>
                    <p>Ngân hàng: <b>Vietcombank</b></p>
                    <p>Số tiền và nội dung sẽ được điền tự động.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // 1. LOGIC LỌC DANH MỤC (THU/CHI)
    const allCats = <?php echo json_encode($js_cats); ?>;
    const catSelect = document.getElementById('category_select');
    const btnExpense = document.getElementById('btn-expense');
    const btnIncome = document.getElementById('btn-income');
    const amountInput = document.getElementById('amount');
    const noteTextarea = document.querySelector('textarea[name="note"]');
    const ocrCard = document.getElementById('ocr-card');
    const qrCard = document.getElementById('qr-card');
    const qrImage = document.getElementById('qr-code-image');

    function filterCategories(type) {
        // Cập nhật giao diện nút
        btnExpense.classList.toggle('expense-active', type === 'expense');
        btnIncome.classList.toggle('income-active', type === 'income');

        if(type === 'expense') {
            amountInput.classList.remove('income');
            amountInput.classList.add('expense');

            // Hiển thị box tương ứng
            ocrCard.style.display = 'block';
            qrCard.style.display = 'none';
        } else {
            amountInput.classList.remove('expense');
            amountInput.classList.add('income');

            // Hiển thị box tương ứng
            ocrCard.style.display = 'none';
            qrCard.style.display = 'block';
            updateQRCode(); // Cập nhật QR ngay khi chuyển tab
        }

        // Lọc option
        catSelect.innerHTML = "";
        const filtered = allCats.filter(c => c.type === type);
        
        if (filtered.length === 0) {
            const opt = document.createElement('option');
            opt.text = "-- Chưa có danh mục --";
            catSelect.add(opt);
        } else {
            filtered.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.text = c.name;
                catSelect.add(opt);
            });
        }
    }

    // Chạy lần đầu
    filterCategories('expense');

    // 2. LOGIC TẠO QR CODE
    function updateQRCode() {
        // Chỉ chạy khi box QR đang hiển thị
        if (!qrCard || qrCard.style.display === 'none') return;

        const BANK_ID = '970436'; // BIN của Vietcombank
        const ACCOUNT_NO = '1024775440';
        const ACCOUNT_NAME = 'DAM DINH LONG';

        const amount = amountInput.value || 0;
        const note = noteTextarea.value.trim() || 'Chuyen khoan thu nhap';
        const safeDescription = encodeURIComponent(note);

        const qrUrl = `https://img.vietqr.io/image/${BANK_ID}-${ACCOUNT_NO}-compact2.png?amount=${amount}&addInfo=${safeDescription}&accountName=${ACCOUNT_NAME}`;
        
        qrImage.src = qrUrl;
    }

    // Gắn sự kiện để cập nhật QR code real-time
    amountInput.addEventListener('input', updateQRCode);
    noteTextarea.addEventListener('input', updateQRCode);

    // 3. LOGIC OCR (SCAN ẢNH)
    const fileInput = document.getElementById('bill_image');
    const statusText = document.getElementById('ocr_status');
    const loading = document.getElementById('loading_spinner');

    fileInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        statusText.style.display = 'none';
        loading.style.display = 'block';

        try {
            const { data } = await Tesseract.recognize(
                file,
                'vie', // Ngôn ngữ tiếng Việt
                { logger: m => console.log(m) }
            );

            console.log("OCR Result:", data.text);
            
            let foundAmount = 0;

            // --- LOGIC QUÉT THÔNG MINH HƠN ---
            // Ưu tiên 1: Tìm các từ khóa chỉ tổng tiền và lấy số lớn nhất trên dòng đó.
            const keywords = ['tổng cộng', 'thành tiền', 'tổng tiền', 'thanh toán', 'total', 'amount', 'cộng tiền hàng', 'tổng thanh toán'];
            const lines = data.lines;
            const amountCandidates = [];

            const extractAmountFromText = (str) => {
                // Regex tìm tất cả các chuỗi số, có thể có dấu . hoặc ,
                const matches = str.match(/[\d.,]+/g);
                if (!matches) return 0;

                let maxVal = 0;
                matches.forEach(numStr => {
                    // Bỏ qua các chuỗi quá ngắn hoặc không chứa số
                    if (numStr.length < 3 || !/\d/.test(numStr)) return;

                    // Chuẩn hóa chuỗi số: xóa hết dấu phân cách
                    const cleanStr = numStr.replace(/[.,]/g, '');

                    // HEURISTIC 1: Lọc số điện thoại
                    // Bỏ qua nếu bắt đầu bằng '0' và có độ dài của SĐT (9-11 số)
                    if (cleanStr.startsWith('0') && cleanStr.length >= 9 && cleanStr.length <= 11) {
                        console.log(`AI: Bỏ qua chuỗi giống SĐT -> ${numStr}`);
                        return;
                    }

                    const val = parseInt(cleanStr, 10);
                    if (isNaN(val)) return;

                    // HEURISTIC 2: Lọc theo khoảng giá trị hợp lệ
                    // Bỏ qua các số quá nhỏ (thường là số lượng) hoặc quá lớn (mã hóa đơn)
                    if (val < 1000 || val > 10000000000) {
                        console.log(`AI: Bỏ qua số ngoài khoảng hợp lệ -> ${val}`);
                        return;
                    }

                    if (val > maxVal) {
                        maxVal = val;
                    }
                });
                return maxVal;
            };

            lines.forEach(line => {
                const lineText = line.text.toLowerCase().replace(/\s+/g, ' '); // Chuẩn hóa text
                for (const keyword of keywords) {
                    if (lineText.includes(keyword)) {
                        const amount = extractAmountFromText(line.text);
                        if (amount > 0) {
                            amountCandidates.push(amount);
                            console.log(`Tìm thấy từ khóa '${keyword}'. Trích xuất số tiền: ${amount}`);
                        }
                        break; // Đã tìm thấy từ khóa trên dòng này, chuyển sang dòng tiếp theo
                    }
                }
            });

            if (amountCandidates.length > 0) {
                // Lấy số tiền lớn nhất từ các dòng chứa từ khóa
                foundAmount = Math.max(...amountCandidates);
            } else {
                // Ưu tiên 2 (Fallback): Nếu không có từ khóa, tìm số lớn nhất trong toàn bộ văn bản
                console.log("Không tìm thấy từ khóa, chuyển sang tìm số lớn nhất toàn văn bản.");
                foundAmount = extractAmountFromText(data.text);
            }

            loading.style.display = 'none';
            statusText.style.display = 'block';

            if (foundAmount > 0) {
                amountInput.value = foundAmount;
                statusText.innerHTML = `✅ Đã tìm thấy số tiền!<br><b>${new Intl.NumberFormat('vi-VN').format(foundAmount)} đ</b>`;
                showToast("Đã tự động điền số tiền từ hóa đơn!", "success");
            } else {
                statusText.innerHTML = "⚠️ Không tìm thấy số tiền rõ ràng.";
                showToast("Không tìm thấy số tiền, vui lòng nhập tay.", "error");
            }
        } catch (error) {
            loading.style.display = 'none';
            statusText.style.display = 'block';
            statusText.textContent = "❌ Lỗi khi đọc ảnh.";
            console.error(error);
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>