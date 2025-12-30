<?php
session_start();
require_once '../../config/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = ''; // Biến lưu thông báo

// 2. Xử lý khi bấm nút Lưu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    // Validate cơ bản
    if (empty($full_name) || empty($email)) {
        $message = '<div style="color: red;">Vui lòng không để trống thông tin!</div>';
    } else {
        // Cập nhật vào Database
        $sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $full_name, $email, $user_id);
        
        if ($stmt->execute()) {
            // Cập nhật lại Session tên mới để hiển thị ngay trên Menu
            $_SESSION['full_name'] = $full_name;
            
            // Chuyển hướng về trang Profile
            header("Location: profile.php");
            exit();
        } else {
            $message = '<div style="color: red;">Lỗi hệ thống: ' . $conn->error . '</div>';
        }
    }
}

// 3. Lấy thông tin cũ để điền vào form
$sql = "SELECT * FROM users WHERE id = $user_id";
$user = $conn->query($sql)->fetch_assoc();

// 4. Include Header
include '../../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/user_edit.css">
<div class="edit-profile-wrapper">
    <h2 style="text-align: center; margin-bottom: 25px; color: #333;">Cập nhật hồ sơ</h2>
    
    <?php echo $message; ?>

    <form method="POST">
        <!-- Username không được sửa -->
        <div class="form-group">
            <label class="form-label">Tên đăng nhập:</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            <small style="color: #999;">Bạn không thể thay đổi tên đăng nhập.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Họ và tên:</label>
            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-save">Lưu thay đổi</button>
            <a href="profile.php" class="btn btn-cancel">Hủy bỏ</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>