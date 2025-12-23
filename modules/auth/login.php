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
</head>

<style>
    * {
        box-sizing: border-box;
        padding: 0;
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    body {
        background-color: #fafafa;
    }

    #main {
        margin: 48px auto;
        display: flex;
        width: 350px;
        align-items: center;
        flex-direction: column;
        border: 1px solid #dbdbdb;
        background-color: white;
        padding-bottom: 30px;
    }

    #logo {
        margin: 36px 0 12px 0;
    }

    .imgLogo {
        width: 175px;
        height: auto;
    }

    .form-col {
        margin-top: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .form-control {
        margin-bottom: 6px;
        padding: 9px 0 7px 8px;
        width: 268px;
        height: 38px;
        font-size: 12px;
        border: 1px solid #dbdbdb;
        border-radius: 3px;
        background-color: #fafafa;
    }

    .form-control:focus {
        border: 1px solid #a8a8a8;
        outline: none;
    }

    .btn-submit {
        margin: 15px 0;
        width: 268px;
        height: 32px;
        border-radius: 4px;
        border: none;
        background-color: #0095f6;
        font-size: 14px;
        color: #ffff;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-submit:hover {
        background-color: #1877f2;
    }

    #toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        flex-direction: column-reverse;
        gap: 10px;
        z-index: 1000;
    }

    .noti {
        min-width: 250px;
        padding: 15px 20px;
        border-radius: 4px;
        color: white;
        font-weight: bold;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideInRight 0.3s ease forwards;
    }

    .fadeOut {
        animation: fadeOutRight 0.5s ease forwards;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOutRight {
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .register-link {
        margin-top: 15px;
        font-size: 14px;
        color: #262626;
    }

    .register-link a {
        color: #0095f6;
        text-decoration: none;
        font-weight: 600;
    }
</style>

<body>
    <div id="main">
        <div id="logo">
            <img class="imgLogo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Instagram_logo.svg/1280px-Instagram_logo.svg.png" alt="Logo">
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