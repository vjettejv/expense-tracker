<?php

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "expense_tracker";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    printf("Lỗi khi set utf8mb4: %s\n", $conn->error);
    exit();
}


define('BASE_URL', 'http://localhost/expense-tracker');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
