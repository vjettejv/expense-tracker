-- 1. Tạo Database (Nếu chưa có)
CREATE DATABASE IF NOT EXISTS expense_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE expense_tracker;

-- 2. Bảng Users (Dành cho Thành viên 1)
-- Lưu thông tin người dùng đăng nhập
CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Nhớ lưu mật khẩu đã mã hóa (MD5 hoặc password_hash)
    email VARCHAR(100) NOT NULL UNIQUE,
    avatar VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Bảng Wallets (Dành cho Thành viên 3)
-- Quản lý các ví tiền (Tiền mặt, ATM, Momo...)
CREATE TABLE wallets (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    name VARCHAR(50) NOT NULL, -- Ví dụ: "Ví tiền mặt", "Techcombank"
    balance DECIMAL(15, 2) DEFAULT 0, -- Số dư hiện tại
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Bảng Categories (Dành cho Thành viên 2)
-- Quản lý danh mục (Ăn uống, Xăng xe, Nhận lương...)
CREATE TABLE categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    name VARCHAR(50) NOT NULL,
    type ENUM('income', 'expense') NOT NULL DEFAULT 'expense', -- Loại: Thu hoặc Chi
    color VARCHAR(20) DEFAULT '#000000', -- Mã màu để vẽ biểu đồ cho đẹp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Bảng Budgets (Dành cho Thành viên 4)
-- Quản lý hạn mức chi tiêu (Ví dụ: Tháng 12 chỉ được ăn 2 triệu)
CREATE TABLE budgets (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    category_id INT(11) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL, -- Số tiền hạn mức
    month_year VARCHAR(7) NOT NULL, -- Ví dụ: "12-2023"
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- 6. Bảng Transactions (Dành cho Thành viên 5)
-- Bảng quan trọng nhất: Lưu lịch sử thu chi
CREATE TABLE transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    wallet_id INT(11) NOT NULL, -- Tiền lấy từ ví nào?
    category_id INT(11) NOT NULL, -- Chi cho việc gì?
    amount DECIMAL(15, 2) NOT NULL, -- Số tiền
    note TEXT, -- Ghi chú (VD: Ăn sáng cùng người yêu)
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);