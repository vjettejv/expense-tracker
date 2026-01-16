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

<div style="max-width: 800px; margin: 0 auto;">
    <a href="index.php" style="text-decoration: none; color: #64748b; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">
        <span>←</span> Quay lại sổ giao dịch
    </a>

    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
        
        <!-- FORM NHẬP LIỆU -->
        <div class="card" style="flex: 3; min-width: 300px;">
            <h2 style="margin-top: 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                📝 Thêm Giao dịch Mới
            </h2>

            <form action="store.php" method="POST" id="transForm">
                
                <!-- Chọn Loại: Thu / Chi -->
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="type_selector" value="expense" checked style="display: none;" onchange="filterCategories('expense')">
                        <div class="type-btn active" id="btn-expense" 
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

        <!-- OCR SCANNER -->
        <div class="card" style="flex: 2; min-width: 280px; height: fit-content; background: #f0fdf4; border: 1px dashed #4ade80;">
            <h3 style="margin-top: 0; color: #15803d; text-align: center;">📸 Quét Hóa Đơn</h3>
            <div style="text-align: center;">
                <label for="bill_image" style="display: block; width: 100%; padding: 40px 20px; background: white; border: 2px dashed #cbd5e1; border-radius: 12px; cursor: pointer;">
                    <span style="font-size: 32px;">📤</span><br>
                    <span style="font-weight: 600; color: #64748b;">Chọn ảnh</span>
                    <input type="file" id="bill_image" accept="image/*" style="display: none;">
                </label>
            </div>
            <div id="ocr_status" style="margin-top: 15px; font-size: 13px; text-align: center; color: #64748b;">Chưa chọn ảnh.</div>
            <div id="loading_spinner" style="display: none; margin-top: 15px; text-align: center; color: #16a34a; font-weight: bold;">⏳ Đang xử lý...</div>
        </div>
    </div>
</div>

<script>
    // 1. LOGIC LỌC DANH MỤC & MÀU SẮC
    const allCats = <?php echo json_encode($js_cats); ?>;
    const catSelect = document.getElementById('category_select');
    const amountInput = document.getElementById('amount');
    const btnExpense = document.getElementById('btn-expense');
    const btnIncome = document.getElementById('btn-income');

    function filterCategories(type) {
        catSelect.innerHTML = "";
        
        // Đổi màu nút
        if(type === 'expense') {
            btnExpense.style.cssText = "text-align: center; padding: 12px; border: 1px solid #ef4444; background: #fee2e2; color: #ef4444; border-radius: 8px; font-weight: bold;";
            btnIncome.style.cssText = "text-align: center; padding: 12px; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; border-radius: 8px; font-weight: bold; cursor: pointer;";
            amountInput.style.color = "#ef4444"; 
        } else {
            btnIncome.style.cssText = "text-align: center; padding: 12px; border: 1px solid #10b981; background: #d1fae5; color: #059669; border-radius: 8px; font-weight: bold;";
            btnExpense.style.cssText = "text-align: center; padding: 12px; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; border-radius: 8px; font-weight: bold; cursor: pointer;";
            amountInput.style.color = "#10b981"; 
        }

        // Lọc danh mục
        const filtered = allCats.filter(c => c.type === type);
        if (filtered.length === 0) {
            catSelect.add(new Option("-- Chưa có danh mục --", ""));
        } else {
            filtered.forEach(c => catSelect.add(new Option(c.name, c.id)));
        }
    }
    filterCategories('expense'); // Init

    // 2. LOGIC CẢNH BÁO SỐ DƯ (METHOD 1)
    document.getElementById('transForm').addEventListener('submit', function(e) {
        const type = document.querySelector('input[name="type_selector"]:checked').value;
        
        // Chỉ cảnh báo khi CHI TIÊU
        if (type === 'expense') {
            const walletSelect = document.getElementById('wallet_select');
            const selectedOption = walletSelect.options[walletSelect.selectedIndex];
            
            // Lấy số dư từ data-balance và số tiền nhập
            const currentBalance = parseFloat(selectedOption.getAttribute('data-balance'));
            const expenseAmount = parseFloat(amountInput.value);

            if (expenseAmount > currentBalance) {
                // Hiển thị hộp thoại xác nhận
                const confirmMsg = `⚠️ CẢNH BÁO: Số dư ví không đủ!\n\n` +
                                   `- Số dư hiện tại: ${new Intl.NumberFormat().format(currentBalance)} đ\n` +
                                   `- Khoản chi: ${new Intl.NumberFormat().format(expenseAmount)} đ\n\n` +
                                   `Ví sẽ bị ÂM tiền. Bạn có chắc chắn muốn tiếp tục?`;
                
                if (!confirm(confirmMsg)) {
                    e.preventDefault(); // Hủy submit nếu người dùng chọn Cancel
                }
            }
        }
    });

    // 3. LOGIC OCR (GIỮ NGUYÊN)
    const fileInput = document.getElementById('bill_image');
    const statusText = document.getElementById('ocr_status');
    const loading = document.getElementById('loading_spinner');

    fileInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        statusText.style.display = 'none'; loading.style.display = 'block';
        try {
            const { data: { text } } = await Tesseract.recognize(file, 'vie');
            const numbers = text.match(/\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?/g);
            let foundAmount = 0;
            if (numbers) {
                numbers.forEach(numStr => {
                    let val = parseInt(numStr.replace(/[.,]/g, ''));
                    if (!isNaN(val) && val > 1000 && numStr.length < 15) {
                        if (val > foundAmount) foundAmount = val;
                    }
                });
            }
            loading.style.display = 'none'; statusText.style.display = 'block';
            if (foundAmount > 0) {
                amountInput.value = foundAmount;
                statusText.innerHTML = `✅ Tìm thấy: <b>${new Intl.NumberFormat().format(foundAmount)} đ</b>`;
                if(typeof showToast === 'function') showToast("Đã quét được số tiền!", "success");
            } else {
                statusText.innerHTML = "⚠️ Không đọc được số tiền.";
            }
        } catch (error) {
            loading.style.display = 'none'; statusText.style.display = 'block';
            statusText.innerHTML = "❌ Lỗi đọc ảnh.";
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>