<?php
session_start();
require_once '../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM categories WHERE user_id = $user_id OR user_id IS NULL ORDER BY created_at DESC");

include '../../includes/header.php';
?>

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
                        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?php echo $row['color']; ?>; margin-right:5px;"></span>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </td>
                    <td><?php echo ($row['type']=='income') ? '<span class="badge badge-success">Thu</span>' : '<span class="badge badge-danger">Chi</span>'; ?></td>
                    <td><?php echo $row['color']; ?></td>
                    <td>
                        <?php if($row['user_id'] != null): ?>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" style="color:#ef4444;" onclick="return confirm('Xóa?')">Xóa</a>
                        <?php else: ?>
                            <small>Mặc định</small>
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
                    <input type="text" name="name" class="form-control" required placeholder="VD: Ăn sáng...">
                </div>
                <div class="form-group">
                    <label class="form-label">Loại</label>
                    <select name="type" class="form-control">
                        <option value="expense">Khoản Chi (Expense)</option>
                        <option value="income">Khoản Thu (Income)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Màu sắc</label>
                    <input type="color" name="color" class="form-control" style="height: 50px;" value="#0095f6">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Lưu</button>
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

    function showBuyTicket() { modal.classList.add('open') }
    function hideBuyTicket() { modal.classList.remove('open') }

    for (const buyBtn of buyBtns) { buyBtn.addEventListener('click', showBuyTicket) }
    modalClose.addEventListener('click', hideBuyTicket)
    modal.addEventListener('click', hideBuyTicket)
    modalContainer.addEventListener('click', function(event){ event.stopPropagation() })
</script>

<?php include '../../includes/footer.php'; ?>