<div align="center">
<h1>💰 QUẢN LÝ CHI TIÊU CÁ NHÂN</h1>
<h3>(Expense Tracker Project)</h3>
<p>
<em>Đồ án môn học: <strong>Lập trình Web với PHP & MySQL</strong></em>
</p>

<!-- Badges -->

<p>
<img src="https://www.google.com/search?q=https://img.shields.io/badge/PHP-7.4%2B-777BB4%3Fstyle%3Dfor-the-badge%26logo%3Dphp%26logoColor%3Dwhite" />
<img src="https://www.google.com/search?q=https://img.shields.io/badge/Database-MySQL-4479A1%3Fstyle%3Dfor-the-badge%26logo%3Dmysql%26logoColor%3Dwhite" />
<img src="https://www.google.com/search?q=https://img.shields.io/badge/Frontend-Bootstrap_5-7952B3%3Fstyle%3Dfor-the-badge%26logo%3Dbootstrap%26logoColor%3Dwhite" />
</p>
</div>

<hr />

📖 1. Giới thiệu

Expense Tracker là website giúp quản lý tài chính cá nhân, hỗ trợ ghi chép thu chi, quản lý ví tiền và ngân sách. Dự án được xây dựng bằng PHP thuần (Native), tổ chức code theo mô hình Module, không sử dụng Framework để phục vụ mục đích học tập.

👥 2. Phân công nhóm (5 Thành viên)

<!-- Bảng dùng HTML để đảm bảo chia cột đẹp -->

<table width="100%">
<thead>
<tr>
<th align="center" width="10%">STT</th>
<th align="left" width="25%">Thành viên</th>
<th align="left" width="20%">Module</th>
<th align="left" width="45%">🛠️ Nhiệm vụ chi tiết</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center">1</td>
<td><strong>Nguyễn Văn A</strong>



<em>(Leader)</em></td>
<td><code>Users</code></td>
<td>🔐 Quản lý Tài khoản, Đăng nhập/Đăng ký, Setup Database chung.</td>
</tr>
<tr>
<td align="center">2</td>
<td><strong>Trần Văn B</strong></td>
<td><code>Categories</code></td>
<td>📂 Quản lý Danh mục chi tiêu (Thêm/Sửa/Xóa các loại: Ăn uống, Lương...).</td>
</tr>
<tr>
<td align="center">3</td>
<td><strong>Lê Văn C</strong></td>
<td><code>Wallets</code></td>
<td>💳 Quản lý Ví tiền (Tiền mặt, Thẻ ngân hàng, Ví điện tử).</td>
</tr>
<tr>
<td align="center">4</td>
<td><strong>Phạm Văn D</strong></td>
<td><code>Budgets</code></td>
<td>📉 Quản lý Hạn mức chi tiêu (Đặt ngân sách tối đa cho từng danh mục).</td>
</tr>
<tr>
<td align="center">5</td>
<td><strong>Hoàng Văn E</strong></td>
<td><code>Transactions</code></td>
<td>💸 Quản lý Giao dịch Thu/Chi hàng ngày, Xem Báo cáo & Dashboard.</td>
</tr>
</tbody>
</table>

⚙️ 3. Hướng dẫn Cài đặt & Chạy

Để chạy dự án, bạn làm theo 4 bước sau:

1️⃣ Tải mã nguồn

Download file ZIP hoặc Clone dự án về thư mục htdocs của XAMPP:

git clone [https://github.com/vjettejv/expense-tracker-project.git](https://github.com/vjettejv/expense-tracker-project.git)


2️⃣ Cài đặt Database

Mở phpMyAdmin (http://localhost/phpmyadmin).

Tạo database mới tên: expense_tracker.

Chọn tab Import ➔ Chọn file database/expense_tracker.sql ➔ Nhấn Go.

3️⃣ Cấu hình kết nối

Mở file config/db.php và kiểm tra thông tin:

$servername = "localhost";
$username   = "root"; // Mặc định XAMPP
$password   = "";     // Mặc định XAMPP để trống
$dbname     = "expense_tracker";


4️⃣ Khởi chạy

Mở trình duyệt và truy cập:

http://localhost/expense-tracker-project/

📂 4. Cấu trúc thư mục

Dự án được tổ chức gọn gàng để tránh xung đột code (Conflict):

<pre>
expense-tracker/
├── <b>assets/</b>                 <span style="color: gray"># CSS, JS, Images, Thư viện</span>
├── <b>config/</b>                 <span style="color: gray"># Cấu hình Database</span>
├── <b>database/</b>               <span style="color: gray"># File SQL backup</span>
├── <b>includes/</b>               <span style="color: gray"># Giao diện dùng chung (Header, Footer, Sidebar)</span>
├── <b>modules/</b>                <span style="color: gray"># KHÔNG GIAN LÀM VIỆC CHÍNH</span>
│   ├── <b>auth/</b>               <span style="color: gray"># Đăng nhập/Đăng ký</span>
│   ├── <b>users/</b>              <span style="color: gray"># Module của Leader</span>
│   ├── <b>categories/</b>         <span style="color: gray"># Module của Thành viên 2</span>
│   ├── <b>wallets/</b>            <span style="color: gray"># Module của Thành viên 3</span>
│   ├── <b>budgets/</b>            <span style="color: gray"># Module của Thành viên 4</span>
│   └── <b>transactions/</b>       <span style="color: gray"># Module của Thành viên 5</span>
└── <b>index.php</b>               <span style="color: gray"># Trang Dashboard</span>
</pre>

<hr>
<p align="center">
<i>Được thực hiện với ❤️ bởi Nhóm 5</i>
</p>