<h1 align="center">
💰 Quản lý Chi tiêu Cá nhân





<small>(Expense Tracker)</small>
</h1>

<p align="center">
<em>Đồ án môn học: <strong>Lập trình Web với PHP & MySQL</strong></em>
</p>

<p align="center">
<img src="https://www.google.com/search?q=https://img.shields.io/badge/Language-PHP-777BB4%3Fstyle%3Dfor-the-badge%26logo%3Dphp%26logoColor%3Dwhite" alt="PHP">
<img src="https://www.google.com/search?q=https://img.shields.io/badge/Database-MySQL-4479A1%3Fstyle%3Dfor-the-badge%26logo%3Dmysql%26logoColor%3Dwhite" alt="MySQL">
<img src="https://www.google.com/search?q=https://img.shields.io/badge/Frontend-Bootstrap-7952B3%3Fstyle%3Dfor-the-badge%26logo%3Dbootstrap%26logoColor%3Dwhite" alt="Bootstrap">
</p>

<hr>

📖 Giới thiệu

Expense Tracker là website giúp người dùng ghi chép thu chi hàng ngày, quản lý ví tiền và xem báo cáo tài chính trực quan. Dự án được viết bằng PHP thuần (Native), tổ chức code theo mô hình Module, không sử dụng Framework.

👥 Phân công nhóm (5 Thành viên)

STT

Thành viên

Vai trò (Module)

🛠️ Nhiệm vụ chi tiết

1

Bạn số 1 



 (Leader)

Auth & Users

🔐 Quản lý Tài khoản, Đăng nhập/Đăng ký, Setup Database

2

Bạn số 2

Categories

📂 Quản lý Danh mục chi tiêu (Ăn uống, Lương, Xăng xe...)

3

Bạn số 3

Wallets

💳 Quản lý Ví tiền / Nguồn tiền (Tiền mặt, ATM, Ví điện tử)

4

Bạn số 4

Budgets

📉 Quản lý Hạn mức chi tiêu (Lập ngân sách dự kiến)

5

Bạn số 5

Transactions

💸 Quản lý Giao dịch Thu/Chi, Báo cáo & Dashboard

⚙️ Hướng dẫn Cài đặt & Chạy

Để chạy dự án trên máy cá nhân (Localhost), bạn làm theo 4 bước sau:

1️⃣ Tải mã nguồn

Download file .zip hoặc Clone dự án về thư mục htdocs của XAMPP:

git clone [https://github.com/vjettejv/expense-tracker-project.git](https://github.com/vjettejv/expense-tracker-project.git)


2️⃣ Cài đặt Database

Mở phpMyAdmin (thường là http://localhost/phpmyadmin).

Tạo database mới tên: expense_tracker.

Chọn tab Import ➔ Chọn file database/expense_tracker.sql ➔ Nhấn Go.

3️⃣ Cấu hình kết nối

Mở file config/db.php và kiểm tra thông tin:

$servername = "localhost";
$username   = "root"; // Mặc định XAMPP
$password   = "";     // Mặc định XAMPP để trống
$dbname     = "expense_tracker";


4️⃣ Khởi chạy

Mở trình duyệt và truy cập đường dẫn:

http://localhost/expense-tracker-project/

📂 Cấu trúc thư mục

<pre>
expense-tracker/
├── <b>assets/</b>                 # CSS, JS, Images
├── <b>config/</b>                 # Cấu hình Database
├── <b>database/</b>               # File SQL backup
├── <b>includes/</b>               # Giao diện dùng chung (Header, Footer...)
├── <b>modules/</b>                # KHÔNG GIAN LÀM VIỆC CHÍNH
│   ├── <b>auth/</b>               # Đăng nhập/Đăng ký
│   ├── <b>users/</b>              # Module của Leader
│   ├── <b>categories/</b>         # Module của Bạn số 2
│   ├── <b>wallets/</b>            # Module của Bạn số 3
│   ├── <b>budgets/</b>            # Module của Bạn số 4
│   └── <b>transactions/</b>       # Module của Bạn số 5
└── <b>index.php</b>               # Trang Dashboard
</pre>

<p align="center">
<i>Được thực hiện với ❤️ bởi Nhóm ...</i>
</p>