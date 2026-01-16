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

<div style="max-width: 800px; margin: 0 auto;">
    <a href="index.php" style="text-decoration: none; color: #64748b; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">
        <span>←</span> Quay lại sổ giao dịch
    </a>

    <div style="display: flex; gap: 24px; flex-wrap: wrap;">
        
        <!-- CỘT TRÁI: Form Nhập liệu -->
        <div class="card" style="flex: 3; min-width: 300px;">
            <h2 style="margin-top: 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                📝 Thêm Giao dịch Mới
            </h2>

            <form action="store.php" method="POST" id="transForm">
                
                <!-- Chọn Loại Giao dịch (Tab giả) -->
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="type_selector" value="expense" checked style="display: none;" onchange="filterCategories('expense')">
                        <div class="type-btn active" id="btn-expense" 
                             style="text-align: center; padding: 12px; border: 1px solid #ef4444; background: #fee2e2; color: #ef4444; border-radius: 8px; font-weight: bold;">
                            💸 Chi Tiêu
                        </div>
                    </label>
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="type_selector" value="income" style="display: none;" onchange="filterCategories('income')">
                        <div class="type-btn" id="btn-income"
                             style="text-align: center; padding: 12px; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; border-radius: 8px; font-weight: bold;">
                            💰 Thu Nhập
                        </div>
                    </label>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Số tiền (VNĐ)</label>
                    <input type="number" name="amount" id="amount" placeholder="0" required
                           style="width: 100%; padding: 15px; font-size: 24px; font-weight: bold; color: #ef4444; border: 2px solid #e2e8f0; border-radius: 8px; outline: none;">
                    <small style="color: #64748b;">Mẹo: Bạn có thể quét ảnh hóa đơn ở bên phải để tự điền số tiền.</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 8px;">Ví thanh toán</label>
                        <select name="wallet_id" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <?php while($w = $wallets->fetch_assoc()): ?>
                                <option value="<?php echo $w['id']; ?>">
                                    <?php echo htmlspecialchars($w['name']); ?> (<?php echo number_format($w['balance']); ?> đ)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 8px;">Ngày giao dịch</label>
                        <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required
                               style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Danh mục</label>
                    <select name="category_id" id="category_select" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
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

                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 8px;">Ghi chú</label>
                    <textarea name="note" placeholder="Ví dụ: Ăn trưa cùng đồng nghiệp..." rows="3"
                              style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 15px; font-size: 16px;">
                    Lưu Giao Dịch
                </button>
            </form>
        </div>

        <!-- CỘT PHẢI: OCR Scanner -->
        <div class="card" style="flex: 2; min-width: 280px; height: fit-content; background: #f0fdf4; border: 1px dashed #4ade80;">
            <h3 style="margin-top: 0; color: #15803d; text-align: center;">📸 Quét Hóa Đơn</h3>
            <p style="font-size: 13px; color: #166534; text-align: center; margin-bottom: 20px;">
                Tải ảnh hóa đơn lên, AI sẽ tự động đọc tổng tiền giúp bạn.
            </p>

            <div style="text-align: center;">
                <label for="bill_image" style="display: block; width: 100%; padding: 40px 20px; background: white; border: 2px dashed #cbd5e1; border-radius: 12px; cursor: pointer; transition: 0.2s;">
                    <span style="font-size: 32px;">📤</span><br>
                    <span style="font-weight: 600; color: #64748b;">Chọn ảnh hóa đơn</span>
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

    </div>
</div>

<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
    // 1. LOGIC LỌC DANH MỤC (THU/CHI)
    const allCats = <?php echo json_encode($js_cats); ?>;
    const catSelect = document.getElementById('category_select');
    const btnExpense = document.getElementById('btn-expense');
    const btnIncome = document.getElementById('btn-income');
    const amountInput = document.getElementById('amount');

    function filterCategories(type) {
        // Cập nhật giao diện nút
        if(type === 'expense') {
            btnExpense.style.cssText = "text-align: center; padding: 12px; border: 1px solid #ef4444; background: #fee2e2; color: #ef4444; border-radius: 8px; font-weight: bold;";
            btnIncome.style.cssText = "text-align: center; padding: 12px; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; border-radius: 8px; font-weight: bold; cursor: pointer;";
            amountInput.style.color = "#ef4444"; // Màu đỏ
        } else {
            btnIncome.style.cssText = "text-align: center; padding: 12px; border: 1px solid #10b981; background: #d1fae5; color: #059669; border-radius: 8px; font-weight: bold;";
            btnExpense.style.cssText = "text-align: center; padding: 12px; border: 1px solid #cbd5e1; background: #f8fafc; color: #64748b; border-radius: 8px; font-weight: bold; cursor: pointer;";
            amountInput.style.color = "#10b981"; // Màu xanh
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

    // 2. LOGIC OCR (SCAN ẢNH)
    const fileInput = document.getElementById('bill_image');
    const statusText = document.getElementById('ocr_status');
    const loading = document.getElementById('loading_spinner');

    fileInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        statusText.style.display = 'none';
        loading.style.display = 'block';

        try {
            const { data: { text } } = await Tesseract.recognize(
                file,
                'vie', // Ngôn ngữ tiếng Việt
                { logger: m => console.log(m) }
            );

            console.log("OCR Result:", text);
            
            // Logic tìm số tiền lớn nhất (thường là tổng tiền)
            // Regex tìm các chuỗi số có thể có dấu phẩy hoặc chấm (VD: 100.000, 50,000)
            const numbers = text.match(/\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?/g);
            let foundAmount = 0;

            if (numbers) {
                numbers.forEach(numStr => {
                    // Xóa dấu chấm/phẩy để thành số thuần
                    let cleanStr = numStr.replace(/[.,]/g, '');
                    let val = parseInt(cleanStr);
                    
                    // Lọc số rác (số quá nhỏ hoặc quá lớn vô lý)
                    if (!isNaN(val) && val > 1000 && cleanStr.length < 12) {
                        if (val > foundAmount) foundAmount = val;
                    }
                });
            }

            loading.style.display = 'none';
            statusText.style.display = 'block';

            if (foundAmount > 0) {
                amountInput.value = foundAmount;
                statusText.innerHTML = `✅ Đã quét thành công!<br>Số tiền: <b>${new Intl.NumberFormat().format(foundAmount)} đ</b>`;
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