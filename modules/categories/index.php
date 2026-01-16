<?php
session_start();
require_once '../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL ORDER BY created_at DESC");

include '../../includes/header.php';
?>

<style>
    /* CSS cho bộ chọn màu */
    .color-options {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 5px;
    }
    
    .color-preset {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: transform 0.2s, border-color 0.2s;
    }

    .color-preset:hover {
        transform: scale(1.1);
    }

    .color-preset.active {
        border-color: #333; /* Viền đen để đánh dấu đang chọn */
        transform: scale(1.1);
        box-shadow: 0 0 5px rgba(0,0,0,0.3);
    }

    /* Nút chọn màu tùy chỉnh (+) */
    .custom-color-wrapper {
        position: relative;
        width: 32px;
        height: 32px;
    }

    .custom-color-btn {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 2px dashed #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-weight: bold;
        cursor: pointer;
        background: #f9fafb;
    }

    /* Input color thật bị ẩn đi nhưng phủ lên trên nút + */
    #customColorInput {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        opacity: 0;
        cursor: pointer;
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;">Danh mục</h2>
        <p style="color: #64748b; margin-top: 5px;">Phân loại thu chi.</p>
    </div>
    <button class="btn btn-primary js-buy-tickets">
        <span>+</span> Thêm Danh mục
    </button>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>Tên</th>
                <th>Loại</th>
                <th>Màu</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:<?php echo $row['color']; ?>; margin-right:8px; border: 1px solid rgba(0,0,0,0.1);"></span>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </td>
                    <td><?php echo ($row['type']=='income') ? '<span class="badge badge-success">Thu</span>' : '<span class="badge badge-danger">Chi</span>'; ?></td>
                    <td>
                        <!-- Hiển thị ô màu thay vì mã hex -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display:inline-block; width:20px; height:20px; background:<?php echo $row['color']; ?>; border-radius: 4px; border: 1px solid #ddd;"></span>
                            <span style="font-family: monospace; color: #666; font-size: 12px;"><?php echo $row['color']; ?></span>
                        </div>
                    </td>
                    <td>
                        <?php if($row['user_id'] != null): ?>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" style="color:#ef4444; text-decoration: none; font-size: 13px;" onclick="return confirm('Xóa danh mục này sẽ ảnh hưởng đến các giao dịch cũ. Tiếp tục?')">🗑️ Xóa</a>
                        <?php else: ?>
                            <small style="color: #9ca3af;">(Mặc định)</small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- ================= MODAL THÊM DANH MỤC ================= -->
<div class="modal js-modal">
    <div class="modal-container js-modal-container">
        <div class="modal-close js-modal-close">✕</div>
        <header class="modal-header">Thêm Danh Mục</header>
        <div class="modal-body">
            <form action="store.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Tên danh mục</label>
                    <input type="text" name="name" class="form-control" required placeholder="VD: Ăn sáng, Xăng xe...">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Loại</label>
                    <select name="type" class="form-control">
                        <option value="expense">Khoản Chi (Expense)</option>
                        <option value="income">Khoản Thu (Income)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Màu đại diện</label>
                    
                    <!-- Input ẩn chứa giá trị màu cuối cùng gửi đi -->
                    <input type="hidden" name="color" id="finalColor" value="#0095f6">

                    <div class="color-options">
                        <!-- Các màu mẫu (Presets) -->
                        <div class="color-preset active" style="background: #0095f6;" onclick="pickColor(this, '#0095f6')"></div>
                        <div class="color-preset" style="background: #ef4444;" onclick="pickColor(this, '#ef4444')"></div>
                        <div class="color-preset" style="background: #f97316;" onclick="pickColor(this, '#f97316')"></div>
                        <div class="color-preset" style="background: #f59e0b;" onclick="pickColor(this, '#f59e0b')"></div>
                        <div class="color-preset" style="background: #10b981;" onclick="pickColor(this, '#10b981')"></div>
                        <div class="color-preset" style="background: #06b6d4;" onclick="pickColor(this, '#06b6d4')"></div>
                        <div class="color-preset" style="background: #8b5cf6;" onclick="pickColor(this, '#8b5cf6')"></div>
                        <div class="color-preset" style="background: #ec4899;" onclick="pickColor(this, '#ec4899')"></div>
                        <div class="color-preset" style="background: #6b7280;" onclick="pickColor(this, '#6b7280')"></div>
                        <div class="color-preset" style="background: #1e293b;" onclick="pickColor(this, '#1e293b')"></div>

                        <!-- Nút Chọn màu chi tiết (+) -->
                        <div class="custom-color-wrapper" title="Chọn màu khác...">
                            <div class="custom-color-btn" id="customBtn">+</div>
                            <input type="color" id="customColorInput" onchange="pickCustomColor(this)">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">Lưu Danh Mục</button>
            </form>
        </div>
    </div>
</div>

<!-- JS -->
<script>
    const buyBtns = document.querySelectorAll('.js-buy-tickets')
    const modal = document.querySelector('.js-modal')
    const modalContainer = document.querySelector('.js-modal-container')
    const modalClose = document.querySelector('.js-modal-close')

    // Logic Modal
    function showBuyTicket() { modal.classList.add('open') }
    function hideBuyTicket() { modal.classList.remove('open') }

    for (const buyBtn of buyBtns) { buyBtn.addEventListener('click', showBuyTicket) }
    modalClose.addEventListener('click', hideBuyTicket)
    modal.addEventListener('click', hideBuyTicket)
    modalContainer.addEventListener('click', function(event){ event.stopPropagation() })

    // Logic Chọn Màu
    function pickColor(element, color) {
        // 1. Xóa class active ở tất cả các nút
        document.querySelectorAll('.color-preset').forEach(el => el.classList.remove('active'));
        document.getElementById('customBtn').style.borderColor = '#9ca3af'; // Reset nút custom
        document.getElementById('customBtn').style.background = '#f9fafb'; // Reset nền nút custom
        document.getElementById('customBtn').innerText = '+'; // Reset text nút custom
        
        // 2. Thêm active vào nút được chọn
        element.classList.add('active');
        
        // 3. Cập nhật giá trị vào input ẩn
        document.getElementById('finalColor').value = color;
    }

    function pickCustomColor(input) {
        // 1. Xóa class active ở các màu mẫu
        document.querySelectorAll('.color-preset').forEach(el => el.classList.remove('active'));
        
        // 2. Cập nhật màu cho nút Custom để người dùng biết đang chọn màu gì
        const btn = document.getElementById('customBtn');
        btn.style.background = input.value;
        btn.style.borderColor = '#333'; // Viền đậm để biết đang chọn
        btn.innerText = ''; // Xóa dấu cộng đi
        
        // 3. Cập nhật giá trị vào input ẩn
        document.getElementById('finalColor').value = input.value;
    }
</script>

<?php include '../../includes/footer.php'; ?>