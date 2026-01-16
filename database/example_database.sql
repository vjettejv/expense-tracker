-- Tạo 1 ông ADMIN (Pass: 123456)
INSERT INTO users (full_name, username, password, email, role, status) VALUES 
('Quản Trị Viên', 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'admin@gmail.com', 'admin', 'active');

-- Tạo 1 ông USER thường (Pass: 123456)
INSERT INTO users (full_name, username, password, email, role, status) VALUES 
('Người Dùng A', 'userA', 'e10adc3949ba59abbe56e057f20f883e', 'userA@gmail.com', 'user', 'active'),
('Người Dùng B', 'userB', 'e10adc3949ba59abbe56e057f20f883e', 'userB@gmail.com', 'user', 'active');

-- Tạo Danh mục mặc định (Của hệ thống - Admin tạo - user_id là NULL)
INSERT INTO categories (user_id, name, type, color) VALUES 
(NULL, 'Ăn uống', 'expense', '#FF5733'),
(NULL, 'Di chuyển', 'expense', '#28A745'),
(NULL, 'Lương', 'income', '#007BFF');