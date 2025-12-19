<?php 
    session_start();

    if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
        session_unset(); 
        session_destroy();
        header("Location: ..\modules\auth\login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Đã đăng nhập thành công bằng quyền admin</h1>
    
    <a href="?logout=true">Log out tại đây</a>
</body>
</html>