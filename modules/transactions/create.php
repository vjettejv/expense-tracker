<?php
session_start();
require_once '../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];

// Lấy danh sách Ví (Kèm số dư để JS kiểm tra)
$wallets = $conn->query("SELECT * FROM wallets WHERE user_id = $user_id");

// Lấy danh sách Danh mục
$categories = $conn->query("SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL ORDER BY type, name");

include '../../includes/header.php';
?>

<!-- Nhúng Tesseract.js cho OCR -->
<script src='https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js'></script>

<div style="max-width: 900px; margin: 0 auto;">
    <a href="index.php" style="text-decoration: none; color: #64748b; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">
        <span>←</span> Quay lại sổ giao dịch
    </a>

    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
        
        <!-- CỘT TRÁI: FORM NHẬP LIỆU -->
        <div class="card" style="flex: 3; min-width: 300px;">
            <h2 style="margin-top: 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                📝 Thêm Giao dịch Mới
            </h2>

            <form action="store.php" method="POST" id="transForm">
                
                <!-- Chọn Loại: Thu / Chi -->
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="type_selector" value="expense" checked style="display: none;" onchange="filterCategories('expense')">
                        <div class="type-btn" id="btn-expense" 
                             style="text-align: center; padding: 12px; border: 1px solid #ef4444; background: #fee2e2; color: #ef4444; border-radius: 8px; font-weight: bold; transition: 0.2s;">
                            💸 Chi Tiêu
                        </div>
                    </label>
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="type_selector" value="income" style="display: none;" onchange="filterCategories('income')">
                        <div class="type-btn" id="btn-income"
                             style="text-align: center; padding: 12px; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; border-radius: 8px; font-weight: bold; transition: 0.2s;">
                            💰 Thu Nhập
                        </div>
                    </label>
                </div>

                <!-- Nhập Số tiền -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Số tiền (VNĐ)</label>
                    <input type="number" name="amount" id="amount" placeholder="0" required
                           style="width: 100%; padding: 15px; font-size: 24px; font-weight: bold; color: #ef4444; border: 2px solid #e2e8f0; border-radius: 8px; outline: none;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label class="form-label">Ví thanh toán</label>
                        <select name="wallet_id" id="wallet_select" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <?php while($w = $wallets->fetch_assoc()): ?>
                                <!-- LƯU Ý: Thêm data-balance để JS đọc số dư -->
                                <option value="<?php echo $w['id']; ?>" data-balance="<?php echo $w['balance']; ?>">
                                    <?php echo htmlspecialchars($w['name']); ?> (<?php echo number_format($w['balance']); ?> đ)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Ngày giao dịch</label>
                        <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required
                               style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Danh mục</label>
                    <select name="category_id" id="category_select" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <!-- JS sẽ điền options vào đây -->
                        <?php 
                        $js_cats = [];
                        while($c = $categories->fetch_assoc()) { $js_cats[] = $c; }
                        ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" placeholder="Ví dụ: Ăn trưa..." rows="3" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 15px; font-size: 16px;">
                    Lưu Giao Dịch
                </button>
            </form>
        </div>

        <!-- CỘT PHẢI: SCANNER & QR -->
        <div style="flex: 2; min-width: 280px; display: flex; flex-direction: column; gap: 20px;">
            
            <!-- 1. OCR Scanner (Cho phần Chi Tiêu) -->
            <div id="ocr-card" class="card" style="height: fit-content; background: #f0fdf4; border: 1px dashed #4ade80; margin-bottom: 0;">
                <h3 style="margin-top: 0; color: #15803d; text-align: center;">📸 Quét Hóa Đơn</h3>
                <p style="font-size: 13px; color: #166534; text-align: center; margin-bottom: 20px;">
                    Tải ảnh hóa đơn lên, AI sẽ tự động đọc tổng tiền giúp bạn.
                </p>

                <div style="text-align: center;">
                    <label for="bill_image" style="display: block; width: 100%; padding: 30px 20px; background: white; border: 2px dashed #cbd5e1; border-radius: 12px; cursor: pointer; transition: 0.2s;">
                        <span style="font-size: 32px;">📤</span><br>
                        <span style="font-weight: 600; color: #64748b;">Chọn ảnh</span>
                        <input type="file" id="bill_image" accept="image/*" style="display: none;">
                    </label>
                </div>

                <div id="ocr_status" style="margin-top: 15px; font-size: 13px; text-align: center; color: #64748b;">
                    Chưa có ảnh nào được chọn.
                </div>
                
                <div id="loading_spinner" style="display: none; margin-top: 15px; text-align: center;">
                    <div style="display: inline-block; width: 20px; height: 20px; border: 3px solid #cbd5e1; border-top-color: #16a34a; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <span style="margin-left: 10px; color: #16a34a; font-weight: bold;">Đang đọc ảnh...</span>
                </div>
            </div>

            <!-- 2. QR Code Generator (Cho phần Thu Nhập) -->
            <div id="qr-card" class="card" style="display: none; height: fit-content; background: #f0f9ff; border: 1px dashed #38bdf8; margin-bottom: 0;">
                <h3 style="margin-top: 0; color: #0369a1; text-align: center;">💸 Nhận Thanh Toán VietQR</h3>
                <p style="font-size: 13px; color: #075985; text-align: center; margin-bottom: 20px;">
                    Quét mã này để nhận tiền vào tài khoản.
                </p>
                <div style="text-align: center;">
                    <img id="qr-code-image" src="" alt="VietQR Code" style="width: 100%; max-width: 250px; border-radius: 8px; background: #fff; padding: 10px; border: 1px solid #e2e8f0;">
                </div>
                <div style="text-align: center; margin-top: 15px; font-size: 13px; color: #075985;">
                    <p style="margin: 5px 0;">Ngân hàng: <b>Vietcombank</b></p>
                    <p style="margin: 5px 0; font-style: italic;">Số tiền và nội dung tự động cập nhật.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
    // 1. DỮ LIỆU & ELEMENT
    const allCats = <?php echo json_encode($js_cats); ?>;
    const catSelect = document.getElementById('category_select');
    const amountInput = document.getElementById('amount');
    const btnExpense = document.getElementById('btn-expense');
    const btnIncome = document.getElementById('btn-income');
    const noteTextarea = document.querySelector('textarea[name="note"]');
    const ocrCard = document.getElementById('ocr-card');
    const qrCard = document.getElementById('qr-card');
    const qrImage = document.getElementById('qr-code-image');

    // 2. HÀM LỌC DANH MỤC & CHUYỂN TAB
    function filterCategories(type) {
        catSelect.innerHTML = "";
        
        if(type === 'expense') {
            // Style nút Chi tiêu
            btnExpense.style.cssText = "text-align: center; padding: 12px; border: 1px solid #ef4444; background: #fee2e2; color: #ef4444; border-radius: 8px; font-weight: bold;";
            btnIncome.style.cssText = "text-align: center; padding: 12px; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; border-radius: 8px; font-weight: bold; cursor: pointer;";
            amountInput.style.color = "#ef4444"; 

            // Hiện OCR, Ẩn QR
            ocrCard.style.display = 'block';
            qrCard.style.display = 'none';
        } else {
            // Style nút Thu nhập
            btnIncome.style.cssText = "text-align: center; padding: 12px; border: 1px solid #10b981; background: #d1fae5; color: #059669; border-radius: 8px; font-weight: bold;";
            btnExpense.style.cssText = "text-align: center; padding: 12px; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; border-radius: 8px; font-weight: bold; cursor: pointer;";
            amountInput.style.color = "#10b981"; 

            // Ẩn OCR, Hiện QR
            ocrCard.style.display = 'none';
            qrCard.style.display = 'block';
            updateQRCode();
        }

        // Lọc options
        const filtered = allCats.filter(c => c.type === type);
        if (filtered.length === 0) {
            catSelect.add(new Option("-- Chưa có danh mục --", ""));
        } else {
            filtered.forEach(c => catSelect.add(new Option(c.name, c.id)));
        }
    }
    filterCategories('expense'); // Chạy lần đầu

    // 3. LOGIC CẢNH BÁO SỐ DƯ (WARNING BALANCE)
    document.getElementById('transForm').addEventListener('submit', function(e) {
        const type = document.querySelector('input[name="type_selector"]:checked').value;
        
        if (type === 'expense') {
            const walletSelect = document.getElementById('wallet_select');
            const selectedOption = walletSelect.options[walletSelect.selectedIndex];
            
            const currentBalance = parseFloat(selectedOption.getAttribute('data-balance'));
            const expenseAmount = parseFloat(amountInput.value);

            if (expenseAmount > currentBalance) {
                const confirmMsg = `⚠️ CẢNH BÁO: Số dư ví không đủ!\n\n` +
                                   `- Hiện tại: ${new Intl.NumberFormat().format(currentBalance)} đ\n` +
                                   `- Cần chi: ${new Intl.NumberFormat().format(expenseAmount)} đ\n\n` +
                                   `Ví sẽ bị ÂM. Bạn có chắc chắn muốn tiếp tục?`;
                
                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                }
            }
        }
    });

    // 4. LOGIC TẠO QR CODE (VIETQR)
    function updateQRCode() {
        if (!qrCard || qrCard.style.display === 'none') return;

        const BANK_ID = '970436'; // Vietcombank
        const ACCOUNT_NO = '1024775440'; // Thay bằng STK của bạn
        const ACCOUNT_NAME = 'DAM DINH LONG'; // Thay bằng Tên TK của bạn

        const amount = amountInput.value || 0;
        const note = noteTextarea.value.trim() || 'Chuyen tien';
        const safeDescription = encodeURIComponent(note);

        const qrUrl = `https://img.vietqr.io/image/${BANK_ID}-${ACCOUNT_NO}-compact2.png?amount=${amount}&addInfo=${safeDescription}&accountName=${encodeURIComponent(ACCOUNT_NAME)}`;
        qrImage.src = qrUrl;
    }

    amountInput.addEventListener('input', updateQRCode);
    noteTextarea.addEventListener('input', updateQRCode);

    // 5. LOGIC OCR (SCAN ẢNH)
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
                'vie', 
                { logger: m => console.log(m) }
            );

            console.log("OCR Result:", data.text);
            
            let foundAmount = 0;
            const keywords = ['tổng cộng', 'thành tiền', 'tổng tiền', 'thanh toán', 'total', 'amount', 'cộng tiền hàng'];
            const lines = data.lines;
            const amountCandidates = [];

            const extractAmountFromText = (str) => {
                const matches = str.match(/[\d.,]+/g);
                if (!matches) return 0;
                let maxVal = 0;
                matches.forEach(numStr => {
                    if (numStr.length < 3 || !/\d/.test(numStr)) return;
                    const cleanStr = numStr.replace(/[.,]/g, '');
                    // Lọc SĐT
                    if (cleanStr.startsWith('0') && cleanStr.length >= 9 && cleanStr.length <= 11) return;
                    const val = parseInt(cleanStr, 10);
                    if (isNaN(val)) return;
                    // Lọc số quá nhỏ/lớn
                    if (val < 1000 || val > 10000000000) return;
                    if (val > maxVal) maxVal = val;
                });
                return maxVal;
            };

            lines.forEach(line => {
                const lineText = line.text.toLowerCase().replace(/\s+/g, ' ');
                for (const keyword of keywords) {
                    if (lineText.includes(keyword)) {
                        const amount = extractAmountFromText(line.text);
                        if (amount > 0) {
                            amountCandidates.push(amount);
                        }
                        break;
                    }
                }
            });

            if (amountCandidates.length > 0) {
                foundAmount = Math.max(...amountCandidates);
            } else {
                foundAmount = extractAmountFromText(data.text);
            }

            loading.style.display = 'none';
            statusText.style.display = 'block';

            if (foundAmount > 0) {
                amountInput.value = foundAmount;
                statusText.innerHTML = `✅ Đã tìm thấy số tiền!<br><b>${new Intl.NumberFormat('vi-VN').format(foundAmount)} đ</b>`;
                if(typeof showToast === 'function') showToast("Đã tự động điền số tiền!", "success");
            } else {
                statusText.innerHTML = "⚠️ Không tìm thấy số tiền rõ ràng.";
            }

        } catch (error) {
            loading.style.display = 'none';
            statusText.style.display = 'block';
            statusText.innerHTML = "❌ Lỗi khi đọc ảnh.";
            console.error(error);
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>