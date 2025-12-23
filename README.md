<div align="center">
<h1>💰 QUẢN LÝ CHI TIÊU CÁ NHÂN</h1>
<h3>(Expense Tracker with Authorization)</h3>
<p>
<em>Dự án môn học: <strong>Lập trình Web với PHP & MySQL</strong></em>
</p>
</div>

<hr />

📖 1. Giới thiệu

Expense Tracker là phiên bản nâng cấp với tính năng Phân quyền (Role-based Authorization). Hệ thống phân chia rõ ràng giữa hai vai trò:

Admin: Quản lý toàn bộ người dùng, xem báo cáo tổng hợp, có quyền khóa/mở khóa tài khoản.

User: Chỉ quản lý dữ liệu cá nhân (Ví, Giao dịch) của chính mình, đảm bảo tính riêng tư.

🌟 Tính năng nổi bật mới cập nhật:

$$x$$

 Phân quyền Admin/User: Tự động điều hướng sau khi đăng nhập.

$$x$$

 Bảo mật: Chặn người dùng truy cập vào trang Admin trái phép.

$$x$$

 Khóa tài khoản: Admin có thể khóa (banned) tài khoản vi phạm, user bị khóa sẽ không thể đăng nhập.

$$x$$

 Giao diện Login: Thiết kế hiện đại (Instagram Style) với thông báo Toast.

👥 2. Phân công nhóm (5 Thành viên)

<table width="100%">
<thead>
<tr>
<th align="center" width="10%">STT</th>
<th align="left" width="20%">Thành viên</th>
<th align="left" width="15%">Module</th>
<th align="left" width="55%">🛠️ Nhiệm vụ chi tiết</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center">1</td>
<td><strong>Nguyễn Hà Đức Việt</strong>

<em>(Leader)</em></td>

<td><code>Auth & Admin</code></td>
<td>🔐 Code Login/Logout (Check quyền, MD5).

👮 Xây dựng trang Admin Dashboard, Khóa/Mở tài khoản User.</td>

</tr>
<tr>
<td align="center">2</td>
<td><strong>Đỗ Thị Thuý Quỳnh</strong></td>
<td><code>Categories</code></td>
<td>📂 CRUD Danh mục. Xử lý logic hiển thị danh mục riêng của User + danh mục chung của hệ thống.</td>
</tr>
<tr>
<td align="center">3</td>
<td><strong>Lê Văn Tuấn</strong></td>
<td><code>Wallets</code></td>
<td>💳 CRUD Ví tiền. Đảm bảo User A không xem được số dư ví của User B.</td>
</tr>
<tr>
<td align="center">4</td>
<td><strong>Trịnh Đăng Quang</strong></td>
<td><code>Budgets</code></td>
<td>📉 Quản lý Hạn mức chi tiêu. Cảnh báo khi chi tiêu vượt quá ngân sách.</td>
</tr>
<tr>
<td align="center">5</td>
<td><strong>Đàm Đình Long</strong></td>
<td><code>Transactions</code></td>
<td>💸 CRUD Giao dịch. Thống kê thu chi cá nhân cho User và Báo cáo tổng cho Admin.</td>
</tr>
</tbody>
</table>

⚙️ 3. Hướng dẫn Cài đặt & Chạy

Để chạy dự án, bạn làm theo 4 bước sau:

1️⃣ Tải mã nguồn

Clone dự án về thư mục htdocs của XAMPP:

git clone [https://github.com/vjettejv/expense-tracker.git](https://github.com/vjettejv/expense-tracker.git)


2️⃣ Cài đặt Database (Quan trọng)

Mở phpMyAdmin.

Tạo database mới tên: expense_tracker

Import file database/expense_tracker.sql.

Tài khoản test:

Admin: admin / 123456

User: userA / 123456

3️⃣ Cấu hình kết nối

Mở file config/db.php:

$servername = "localhost";
$username   = "root"; 
$password   = "";     
$dbname     = "expense_tracker"; // Chú ý tên DB mới


4️⃣ Khởi chạy

Mở trình duyệt và truy cập:

http://localhost/expense-tracker/

📂 4. Cấu trúc thư mục (Cập nhật)

Dự án được tổ chức lại để tách biệt khu vực Admin và User:

<pre>
expense-tracker/
├── <b>admin/</b>                  <span style="color: red"># [MỚI] Khu vực dành riêng cho Admin (Dashboard, Quản lý User)</span>
├── <b>assets/</b>                 <span style="color: gray"># CSS, JS, Images, Thư viện</span>
├── <b>config/</b>                 <span style="color: gray"># Cấu hình Database & Hằng số</span>
├── <b>database/</b>               <span style="color: gray"># File SQL backup (expense_tracker_pro.sql)</span>
├── <b>includes/</b>               <span style="color: gray"># Giao diện dùng chung (Header, Footer, Sidebar)</span>
├── <b>modules/</b>                <span style="color: gray"># CÁC MODULE CHỨC NĂNG</span>
│   ├── <b>auth/</b>               <span style="color: blue"># Đăng nhập/Đăng ký/Logout (Có check quyền)</span>
│   ├── <b>users/</b>              <span style="color: gray"># Quản lý hồ sơ cá nhân</span>
│   ├── <b>categories/</b>         <span style="color: gray"># Quản lý Danh mục</span>
│   ├── <b>wallets/</b>            <span style="color: gray"># Quản lý Ví tiền</span>
│   ├── <b>budgets/</b>            <span style="color: gray"># Quản lý Hạn mức</span>
│   └── <b>transactions/</b>       <span style="color: gray"># Quản lý Giao dịch</span>
└── <b>index.php</b>               <span style="color: gray"># Dashboard cho User thường</span>
</pre>

<hr>
<p align="center">
<i>Được thực hiện với ❤️ bởi nhóm 2</i>
</p>