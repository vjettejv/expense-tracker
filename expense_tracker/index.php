<?php
session_start();
require_once 'config/db.php';

// =========================================================================
// PHẦN 1: ĐÃ ĐĂNG NHẬP -> HIỆN DASHBOARD + BIỂU ĐỒ
// =========================================================================
if (isset($_SESSION['user_id'])) {

    include 'includes/header.php';

    $user_id = $_SESSION['user_id'];

    // 1. Lấy tổng số dư
    $sql_balance = "SELECT SUM(balance) as total FROM wallets WHERE user_id = $user_id";
    $result = $conn->query($sql_balance);
    $total_balance = $result->fetch_assoc()['total'] ?? 0;

    // 2. Tính thu/chi tháng này
    $sql_income = "SELECT SUM(amount) as total FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = $user_id AND c.type = 'income' AND MONTH(transaction_date) = MONTH(CURRENT_DATE())";
    $income = $conn->query($sql_income)->fetch_assoc()['total'] ?? 0;

    $sql_expense = "SELECT SUM(amount) as total FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = $user_id AND c.type = 'expense' AND MONTH(transaction_date) = MONTH(CURRENT_DATE())";
    $expense = $conn->query($sql_expense)->fetch_assoc()['total'] ?? 0;

    // 3. LẤY DỮ LIỆU VẼ BIỂU ĐỒ (Chỉ lấy các khoản CHI trong tháng này)
    // Cần lấy: Tên danh mục, Tổng tiền, Mã màu
    $sql_chart = "SELECT c.name, SUM(t.amount) as total, c.color 
                  FROM transactions t 
                  JOIN categories c ON t.category_id = c.id 
                  WHERE t.user_id = $user_id 
                  AND c.type = 'expense' 
                  AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
                  GROUP BY c.id";
    $result_chart = $conn->query($sql_chart);

    $labels = [];
    $data = [];
    $colors = [];

    if ($result_chart->num_rows > 0) {
        while ($row = $result_chart->fetch_assoc()) {
            $labels[] = $row['name'];
            $data[] = $row['total'];
            // Nếu danh mục chưa có màu, dùng màu mặc định xám
            $colors[] = !empty($row['color']) ? $row['color'] : '#cccccc';
        }
    } else {
        // Nếu chưa có dữ liệu chi tiêu thì tạo dữ liệu giả để hiện biểu đồ trống cho đẹp
        $labels = ['Chưa có chi tiêu'];
        $data = [1];
        $colors = ['#e0e0e0'];
    }
?>
    <!-- Thêm thư viện Chart.js từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="./assets/css/style.css">
    <div class="welcome-text">
        <h2>Xin chào, <?php echo $_SESSION['full_name']; ?>! 👋</h2>
        <p style="color: #8e8e8e;">Tổng quan tài chính tháng <?php echo date('m/Y'); ?>:</p>
    </div>

    <!-- 3 Ô Thống Kê -->
    <div class="dashboard-container">
        <div class="card">
            <h3>Tổng tài sản hiện có</h3>
            <div class="money" style="color: #0095f6;"><?php echo number_format($total_balance); ?> đ</div>
            <div style="margin-top: 10px; font-size: 13px;">
                <a href="modules/wallets/index.php" style="text-decoration: none; color: #0095f6;">Quản lý ví tiền &rarr;</a>
            </div>
        </div>
        <div class="card">
            <h3>Thu nhập tháng này</h3>
            <div class="money" style="color: #2ecc71;">+<?php echo number_format($income); ?> đ</div>
        </div>
        <div class="card">
            <h3>Đã chi tiêu tháng này</h3>
            <div class="money" style="color: #ed4956;">-<?php echo number_format($expense); ?> đ</div>
        </div>
    </div>

    <!-- PHẦN BIỂU ĐỒ MỚI -->
    <div class="chart-section">

        <!-- Cột 1: Biểu đồ tròn -->
        <div class="chart-box">
            <h3 style="margin-bottom: 20px; color: #555;">Cơ cấu chi tiêu tháng này</h3>
            <div class="chart-container">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>

        <!-- Cột 2: Chi tiết danh sách -->
        <div class="chart-box">
            <h3 style="margin-bottom: 20px; color: #555;">Chi tiết theo danh mục</h3>
            <div class="chart-legend">
                <?php if ($result_chart->num_rows > 0):
                    // Reset con trỏ dữ liệu về đầu để lặp lại
                    $result_chart->data_seek(0);
                    while ($row = $result_chart->fetch_assoc()):
                ?>
                        <div class="legend-item">
                            <span style="display: flex; align-items: center;">
                                <span style="display:block; width:12px; height:12px; background-color: <?php echo $row['color']; ?>; margin-right:10px; border-radius:50%;"></span>
                                <?php echo $row['name']; ?>
                            </span>
                            <span style="font-weight: bold;"><?php echo number_format($row['total']); ?> đ</span>
                        </div>
                    <?php endwhile;
                else: ?>
                    <p style="text-align: center; color: #999;">Chưa có dữ liệu chi tiêu tháng này.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="card">
        <h3>Thao tác nhanh</h3>
        <div style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
            <a href="modules/transactions/user_add.php" style="background: #0095f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">+ Thêm Giao dịch</a>
            <a href="modules/wallets/create.php" style="background: #efefef; color: #262626; padding: 10px 20px; text-decoration: none; border-radius: 4px;">+ Tạo Ví mới</a>
        </div>
    </div>

    <!-- Script Vẽ Biểu Đồ -->
    <script>
        const ctx = document.getElementById('expenseChart').getContext('2d');

        // Dữ liệu từ PHP chuyển sang Javascript
        const chartData = {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($data); ?>,
                backgroundColor: <?php echo json_encode($colors); ?>,
                borderWidth: 0
            }]
        };

        new Chart(ctx, {
            type: 'doughnut', // Loại biểu đồ vành khuyên (tròn rỗng giữa) nhìn đẹp hơn pie
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Ẩn chú thích mặc định của Chartjs để dùng chú thích HTML bên cạnh
                    }
                }
            }
        });
    </script>

<?php
    include 'includes/footer.php';
    exit(); // Dừng code tại đây nếu đã đăng nhập
}
?>
<!-- ========================================================================= -->
<!-- PHẦN 2: CHƯA ĐĂNG NHẬP -> HIỆN TRANG GIỚI THIỆU (Tiếng Việt) -->
<!-- ========================================================================= -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon-32x32.png">
    <title>Quản lý tài chính - Nhóm phát triển</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght,SOFT,WONK@0,9..144,700,100,1;1,9..144,700,100,1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@600&display=swap" rel="stylesheet">
</head>

<body>
    <div id="main">
        <div id="header">
            <div id="nav">
                <div class="nav logo">
                    <div class="img-logo">
                        <a href="#" class="logo-text">ExpenseTracker</a>
                    </div>
                    <ul class="nav menu">
                        <li class="nav-item-1"><a href=".intro">Giới thiệu</a></li>
                        <li class="nav-item-2"><a href="#content">Tính năng</a></li>
                        <li class="nav-item-3"><a href="#team-section">Nhóm</a></li>
                        <li class="nav-item-4"><a href="modules/auth/login.php">Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="intro">
                <div class="intro-text">Quản lý tài chính</div>
            </div>
        </div>

        <div id="content">
            <div class="content item-1">
                <div class="text-1">
                    <h3>Kiểm soát dòng tiền</h3>
                    <p>Ghi chép thu chi hàng ngày một cách nhanh chóng. Giúp bạn phân loại các khoản chi tiêu để biết chính xác tiền của mình đi đâu về đâu.</p>
                    <h4><a href="modules/auth/login.php" style="text-decoration: none; color: inherit;">Đăng nhập ngay</a></h4>
                </div>
            </div>

            <div class="content item-2">
                <img class="img-content" src="./assets/images/content-1" alt="Finance">
            </div>

            <div class="content item-3">
                <img class="img-content" src="./assets/images/content-2" alt="Saving">
            </div>

            <div class="content item-4">
                <div class="text-1">
                    <h3>Tiết kiệm tương lai</h3>
                    <p>Đặt hạn mức chi tiêu cho từng danh mục (Ăn uống, Mua sắm...). Hệ thống sẽ cảnh báo khi bạn tiêu quá tay để đảm bảo kế hoạch tiết kiệm.</p>
                    <h4><a href="modules/auth/register.php" style="text-decoration: none; color: inherit;">Đăng ký ngay</a></h4>
                </div>
            </div>

            <div class="content item-5">
                <div class="text-2 graphic">
                    <h4>Đa nền tảng</h4>
                    <p>Đồng bộ dữ liệu trên mọi thiết bị: Điện thoại, Máy tính bảng và Website.</p>
                </div>
            </div>

            <div class="content item-6">
                <div class="text-2 photography">
                    <h4>Báo cáo trực quan</h4>
                    <p>Xem biểu đồ thống kê chi tiết theo tuần, tháng để đưa ra quyết định đúng đắn.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="team-section">
        <h3>Đội ngũ phát triển</h3>

        <div class="team-container">
            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Dam+Dinh+Long&background=60c5a8&color=fff&size=128" alt="Long">
                <div class="member-info">
                    <h5>Đàm Đình Long</h5>
                    <h6>Thành viên nhóm</h6>
                </div>
            </div>

            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Do+Thi+Thuy+Quynh&background=ffbc66&color=fff&size=128" alt="Quynh">
                <div class="member-info">
                    <h5>Đỗ Thị Thuý Quỳnh</h5>
                    <h6>Thành viên nhóm</h6>
                </div>
            </div>

            <div class="team-member leader">
                <img src="https://ui-avatars.com/api/?name=Nguyen+Ha+Duc+Viet&background=fe7867&color=fff&size=128" alt="Viet">
                <div class="member-info">
                    <h5>Nguyễn Hà Đức Việt</h5>
                    <h6>Trưởng nhóm</h6>
                </div>
            </div>

            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Trinh+Dang+Quang&background=60c5a8&color=fff&size=128" alt="Quang">
                <div class="member-info">
                    <h5>Trịnh Đăng Quang</h5>
                    <h6>Thành viên nhóm</h6>
                </div>
            </div>

            <div class="team-member">
                <img src="https://ui-avatars.com/api/?name=Le+Van+Tuan&background=ffbc66&color=fff&size=128" alt="Tuan">
                <div class="member-info">
                    <h5>Lê Văn Tuấn</h5>
                    <h6>Thành viên nhóm</h6>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <a href="#" class="footer-logo">ExpenseTracker</a>

        <div class="footer-nav">
            <a href="modules/auth/login.php">Đăng nhập</a>
            <span>|</span>
            <a href="modules/auth/register.php">Đăng ký</a>
        </div>

        <div class="footer-copyright">
            &copy; 2025 Expense Tracker
        </div>
    </footer>

</body>

</html>