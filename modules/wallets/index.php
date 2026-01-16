<?php
session_start();
require_once '../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM wallets WHERE user_id = $user_id ORDER BY id DESC";
$result = $conn->query($sql);

include '../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;">Ví của tôi</h2>
        <p style="color: #64748b; margin-top: 5px;">Quản lý nguồn tiền.</p>
    </div>
    
    <!-- NÚT MỞ MODAL (Class js-buy-tickets như bạn yêu cầu) -->
    <button class="btn btn-primary js-buy-tickets">
        <span>+</span> Thêm Ví Mới
    </button>
</div>

<!-- Grid hiển thị Ví -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="card" style="border-left: 5px solid #0095f6; margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between;">
                    <h3 style="margin: 0; font-size: 18px;"><?php echo htmlspecialchars($row['name']); ?></h3>
                    <div style="font-size: 24px;">💳</div>
                </div>
                <div style="font-size: 22px; font-weight: 800; color: #262626; margin: 15px 0;">
                    <?php echo number_format($row['balance']); ?> đ
                </div>
                <div style="border-top: 1px solid #f1f5f9; padding-top: 10px; display: flex; justify-content: space-between;">
                    <small style="color: #999;"><?php echo htmlspecialchars($row['description']); ?></small>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" style="color: #ef4444; text-decoration: none;" onclick="return confirm('Xóa ví này?')">🗑️</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Chưa có ví nào.</p>
    <?php endif; ?>
</div>

<!-- ================= MODAL THÊM VÍ ================= -->
<div class="modal js-modal">
    <div class="modal-container js-modal-container">
        
        <div class="modal-close js-modal-close">✕</div>

        <header class="modal-header">
            Thêm Ví Mới
        </header>

        <div class="modal-body">
            <form action="store.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Tên ví</label>
                    <input type="text" name="name" class="form-control" placeholder="Ví dụ: Tiền mặt, Vietcombank..." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Số dư ban đầu</label>
                    <input type="number" name="balance" class="form-control" placeholder="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Lưu Ví
                </button>
            </form>
        </div>
    </div>
</div>

<!-- JAVASCRIPT XỬ LÝ MODAL (Code của bạn) -->
<script>
    const buyBtns = document.querySelectorAll('.js-buy-tickets')
    const modal = document.querySelector('.js-modal')
    const modalContainer = document.querySelector('.js-modal-container')
    const modalClose = document.querySelector('.js-modal-close')

    function showBuyTicket() {
        modal.classList.add('open')
    }

    function hideBuyTicket() {
        modal.classList.remove('open')
    }

    for (const buyBtn of buyBtns) {
        buyBtn.addEventListener('click', showBuyTicket)
    }

    modalClose.addEventListener('click', hideBuyTicket)

    modal.addEventListener('click', hideBuyTicket)
    
    modalContainer.addEventListener('click', function(event){
        event.stopPropagation()
    })
</script>

<?php include '../../includes/footer.php'; ?>