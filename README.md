💰 Quản lý Chi tiêu Cá nhân (Expense Tracker)

Đồ án môn học: Lập trình Web với PHP & MySQL

📖 Giới thiệu

Expense Tracker là website giúp người dùng ghi chép thu chi hàng ngày, quản lý ví tiền và xem báo cáo tài chính trực quan.

Dự án được xây dựng với tiêu chí:

Ngôn ngữ: PHP thuần (Native PHP), không dùng Framework.

Cấu trúc: Tổ chức code theo Module (dễ chia việc nhóm).

Giao diện: Sử dụng Bootstrap 5.

👥 Phân công nhóm (5 Thành viên)

STT

Thành viên

Module phụ trách

🛠️ Nhiệm vụ chi tiết

1

[Tên Trưởng Nhóm]

Auth & Users

Quản lý Tài khoản, Đăng nhập, Setup Database

2

[Tên Thành Viên 2]

Categories

Quản lý Danh mục (Ăn uống, Lương, Xăng xe...)

3

[Tên Thành Viên 3]

Wallets

Quản lý Ví tiền (Tiền mặt, ATM, Ví điện tử)

4

[Tên Thành Viên 4]

Budgets

Quản lý Hạn mức chi tiêu (Lập ngân sách)

5

[Tên Thành Viên 5]

Transactions

Giao dịch Thu/Chi, Báo cáo & Dashboard

⚙️ Hướng dẫn Cài đặt & Chạy

Để chạy dự án trên máy cá nhân (Localhost), bạn làm theo 4 bước sau:

1. Tải mã nguồn

Download file .zip hoặc Clone dự án về thư mục htdocs của XAMPP:

git clone [https://github.com/vjettejv/expense-tracker-project.git](https://github.com/vjettejv/expense-tracker-project.git)


2. Cài đặt Database

Mở phpMyAdmin (truy cập http://localhost/phpmyadmin).

Tạo database mới tên: expense_tracker.

Chọn tab Import ➔ Chọn file database/expense_tracker.sql trong thư mục dự án ➔ Nhấn Go.

3. Cấu hình kết nối

Mở file config/db.php và kiểm tra thông tin kết nối:

$servername = "localhost";
$username   = "root"; // Mặc định XAMPP
$password   = "";     // Mặc định XAMPP để trống
$dbname     = "expense_tracker";


4. Khởi chạy

Mở trình duyệt và truy cập đường dẫn:

http://localhost/expense-tracker-project/

📂 Cấu trúc thư mục

Dự án được tổ chức gọn gàng để tránh xung đột code:

expense-tracker/
├── assets/                 # CSS, JS, Images, Thư viện
├── config/                 # Cấu hình Database
├── database/               # File SQL backup
├── includes/               # Giao diện dùng chung (Header, Footer, Sidebar)
├── modules/                # KHÔNG GIAN LÀM VIỆC CHÍNH
│   ├── auth/               # Đăng nhập/Đăng ký
│   ├── users/              # Module của Leader
│   ├── categories/         # Module của Bạn số 2
│   ├── wallets/            # Module của Bạn số 3
│   ├── budgets/            # Module của Bạn số 4
│   └── transactions/       # Module của Bạn số 5
└── index.php               # Trang Dashboard


Dự án phục vụ mục đích học tập.