-- 1. Tạo Database mới
CREATE DATABASE IF NOT EXISTS expense_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE expense_tracker;

-- =============================================
-- 2. BẢNG USERS (Quan trọng nhất cho Phân Quyền)
-- =============================================
CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    avatar VARCHAR(255) DEFAULT 'default.png',
    
    -- Cột Phân Quyền: 'admin' (Quản trị) hoặc 'user' (Người thường)
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    
    -- Cột Trạng Thái: 'active' (Hoạt động) hoặc 'banned' (Bị khóa)
    status ENUM('active', 'banned') NOT NULL DEFAULT 'active',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 3. BẢNG WALLETS (Ví tiền cá nhân)
-- =============================================
CREATE TABLE wallets (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL, -- Ví này của ai?
    name VARCHAR(50) NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 4. BẢNG CATEGORIES (Danh mục chi tiêu)
-- =============================================
CREATE TABLE categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NULL, -- Nếu NULL nghĩa là Danh mục chung của hệ thống (do Admin tạo)
    name VARCHAR(50) NOT NULL,
    type ENUM('income', 'expense') NOT NULL DEFAULT 'expense',
    color VARCHAR(20) DEFAULT '#6c757d',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Chấp nhận user_id là NULL (cho danh mục hệ thống)
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 5. BẢNG BUDGETS (Hạn mức chi tiêu)
-- =============================================
CREATE TABLE budgets (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    category_id INT(11) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    month_year VARCHAR(7) NOT NULL, -- VD: "12-2023"
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- =============================================
-- 6. BẢNG TRANSACTIONS (Giao dịch)
-- =============================================
CREATE TABLE transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    wallet_id INT(11) NOT NULL,
    category_id INT(11) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    note TEXT,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
