<?php
session_start();
require_once '../../config/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// 2. Xử lý lưu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    if (empty($full_name) || empty($email)) {
        $message = '<div style="color: red; margin-bottom: 15px;">Vui lòng điền đủ thông tin!</div>';
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $email, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name; // Cập nhật session
            header("Location: profile.php"); // Quay về trang profile
            exit();
        } else {
            $message = '<div style="color: red;">Lỗi: ' . $conn->error . '</div>';
        }
    }
}

// 3. Lấy data cũ
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

include '../../includes/header.php';
?>

<div style="max-width: 500px; margin: 0 auto;">
    <!-- Nút quay lại -->
    <a href="profile.php" style="display: inline-flex; align-items: center; gap: 5px; color: #64748b; text-decoration: none; margin-bottom: 20px; font-weight: 600;">
        <span>←</span> Quay lại Hồ sơ
    </a>

    <div class="card">
        <h2 style="margin-top: 0; text-align: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
            ✏️ Cập nhật thông tin
        </h2>
        
        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Tên đăng nhập</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background: #f9fafb; color: #9ca3af;">
            </div>

            <div class="form-group">
                <label class="form-label">Họ và tên</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                Lưu Thay Đổi
            </button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>