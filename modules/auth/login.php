<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        header("Location: ../../admin/dashboard.php");
    } else {
        header("Location: ../../index.php");
    }
    exit();
}

require_once '../../config/db.php';

$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_msg = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $password_hash = md5($password);

        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $username, $password_hash);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if ($user['status'] === 'banned') {
                    $error_msg = "Tài khoản bị KHÓA do vi phạm chính sách!";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['avatar'] = $user['avatar'];

                    if ($user['role'] === 'admin') {
                        header("Location: ../../admin/dashboard.php");
                    } else {
                        header("Location: ../../index.php");
                    }
                    exit();
                }
            } else {
                $error_msg = "Sai tên đăng nhập hoặc mật khẩu!";
            }
            $stmt->close();
        } else {
            $error_msg = "Lỗi hệ thống, vui lòng thử lại sau.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Expense Tracker</title>
    <link rel="stylesheet" href="../../assets/css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Barlow:wght@600&display=swap" rel="stylesheet">
</head>
<body>
    <div id="main">
        <!-- LOGO MỚI Ở ĐÂY -->
        <div id="logo">
            <a href="../../index.php" class="logo-text">ExpenseTracker</a>
        </div>
        
        <div id="loginFrom">
            <form id="formmain" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-col">
                    <div class="form-group">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Tên đăng nhập" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                    </div>
                    <div class="form-button">
                        <button type="submit" id="btn-login" class="btn-submit">Đăng nhập</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="register-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>
    <div id="toast-container"></div>
</body>

<script>
    function showToast(message, type = 'error') {
        let container = document.getElementById("toast-container");
        let noti = document.createElement("div");
        noti.className = "noti";
        noti.textContent = message;

        if (type === 'success') {
            noti.style.backgroundColor = '#2ecc71';
        } else {
            noti.style.backgroundColor = '#ed4956';
        }

        container.appendChild(noti);

        setTimeout(function() {
            noti.classList.add("fadeOut");
            noti.addEventListener('animationend', function() {
                noti.remove();
            });
        }, 3000);
    }

    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status === 'logout_success') {
        showToast("Đăng xuất thành công!", "success");
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    <?php if (!empty($error_msg)): ?>
        showToast(<?php echo json_encode($error_msg); ?>, "error");
    <?php endif; ?>

    document.getElementById("formmain").addEventListener("submit", function(event) {
        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value.trim();

        if (username === "" || password === "") {
            event.preventDefault();
            showToast('Vui lòng nhập đầy đủ thông tin!', 'error');
        }
    });
</script>

</html>