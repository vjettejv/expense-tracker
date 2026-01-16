<?php
session_start();
require_once 'config/db.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    include 'includes/landing_page_content.php';
    exit();
}

$user_id = $_SESSION['user_id'];

// --- BỘ LỌC THỜI GIAN ---
// Mặc định là tháng hiện tại
$current_month_str = isset($_GET['month']) ? $_GET['month'] : date('Y-m'); 
$cur_m = date('m', strtotime($current_month_str));
$cur_y = date('Y', strtotime($current_month_str));

// Tháng trước để so sánh
$prev_month_str = date('Y-m', strtotime($current_month_str . " -1 month"));
$prev_m = date('m', strtotime($prev_month_str));
$prev_y = date('Y', strtotime($prev_month_str));

include 'includes/header.php';

// =========================================================================
// PHẦN 1: TRUY VẤN DỮ LIỆU (DATA FETCHING)
// =========================================================================

// Hàm lấy tổng tiền theo tháng (Fix lỗi: chỉ lấy của user_id hiện tại)
function get_total_by_month($conn, $uid, $month, $year, $type) {
    $sql = "SELECT SUM(t.amount) as total 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.user_id = ? AND c.type = ? 
            AND MONTH(t.transaction_date) = ? AND YEAR(t.transaction_date) = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $uid, $type, $month, $year);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}

// 1. Số liệu Thẻ Thống Kê (Stats Cards)
$income_cur = get_total_by_month($conn, $user_id, $cur_m, $cur_y, 'income');
$expense_cur = get_total_by_month($conn, $user_id, $cur_m, $cur_y, 'expense');

$income_prev = get_total_by_month($conn, $user_id, $prev_m, $prev_y, 'income');
$expense_prev = get_total_by_month($conn, $user_id, $prev_m, $prev_y, 'expense');

// Tính % Tăng trưởng Chi tiêu
$growth_percent = 0;
$growth_class = 'text-muted'; // Mặc định màu xám
$growth_text = 'Không đổi';

if ($expense_prev > 0) {
    $growth_percent = (($expense_cur - $expense_prev) / $expense_prev) * 100;
} elseif ($expense_cur > 0) {
    $growth_percent = 100; 
}

if ($growth_percent > 0) {
    $growth_text = "⬆️ Tăng " . round($growth_percent, 1) . "%";
    $growth_class = "text-danger"; // Đỏ
} elseif ($growth_percent < 0) {
    $growth_text = "⬇️ Giảm " . abs(round($growth_percent, 1)) . "%";
    $growth_class = "text-success"; // Xanh
}

// Tổng tài sản (Số dư các ví)
$balance_sql = "SELECT SUM(balance) as t FROM wallets WHERE user_id = ?";
$stmt_b = $conn->prepare($balance_sql);
$stmt_b->bind_param("i", $user_id);
$stmt_b->execute();
$balance = $stmt_b->get_result()->fetch_assoc()['t'] ?? 0;


// 2. Dữ liệu Pie Chart: Cơ cấu Chi tiêu (CÓ LẤY MÀU)
$sql_pie_exp = "SELECT c.name, c.color, SUM(t.amount) as total 
                FROM transactions t JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = $user_id AND c.type = 'expense' 
                AND MONTH(t.transaction_date) = $cur_m AND YEAR(t.transaction_date) = $cur_y
                GROUP BY c.name, c.color ORDER BY total DESC";
$res_pie_exp = $conn->query($sql_pie_exp);
$pie_exp_labels = [];
$pie_exp_data = [];
$pie_exp_colors = []; // Mảng màu
while($row = $res_pie_exp->fetch_assoc()) { 
    $pie_exp_labels[] = $row['name']; 
    $pie_exp_data[] = $row['total'];
    $pie_exp_colors[] = $row['color']; // Lấy màu từ DB
}

// 3. Dữ liệu Pie Chart: Cơ cấu Thu nhập (CÓ LẤY MÀU)
$sql_pie_inc = "SELECT c.name, c.color, SUM(t.amount) as total 
                FROM transactions t JOIN categories c ON t.category_id = c.id 
                WHERE t.user_id = $user_id AND c.type = 'income' 
                AND MONTH(t.transaction_date) = $cur_m AND YEAR(t.transaction_date) = $cur_y
                GROUP BY c.name, c.color ORDER BY total DESC";
$res_pie_inc = $conn->query($sql_pie_inc);
$pie_inc_labels = [];
$pie_inc_data = [];
$pie_inc_colors = []; // Mảng màu
while($row = $res_pie_inc->fetch_assoc()) { 
    $pie_inc_labels[] = $row['name']; 
    $pie_inc_data[] = $row['total'];
    $pie_inc_colors[] = $row['color']; // Lấy màu từ DB
}

// 4. Dữ liệu Bar Chart: Lịch sử 6 tháng
$bar_labels = [];
$bar_income = [];
$bar_expense = [];
for ($i = 5; $i >= 0; $i--) {
    $time = strtotime("-$i months");
    $m = date('m', $time); $y = date('Y', $time);
    $bar_labels[] = "T$m";
    $bar_income[] = get_total_by_month($conn, $user_id, $m, $y, 'income');
    $bar_expense[] = get_total_by_month($conn, $user_id, $m, $y, 'expense');
}

// 5. Dữ liệu Line Chart: Xu hướng theo ngày
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $cur_m, $cur_y);
$days = range(1, $days_in_month);
$line_cur_data = array_fill(0, $days_in_month, 0);

$sql_line = "SELECT DAY(t.transaction_date) as d, SUM(t.amount) as total 
             FROM transactions t JOIN categories c ON t.category_id = c.id 
             WHERE t.user_id=$user_id AND c.type='expense' AND MONTH(t.transaction_date)=$cur_m AND YEAR(t.transaction_date)=$cur_y 
             GROUP BY d";
$res_line = $conn->query($sql_line);
while($row = $res_line->fetch_assoc()) { $line_cur_data[$row['d'] - 1] = $row['total']; }
?>

<!-- HEADER DASHBOARD -->
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0;">Dashboard Tài Chính</h2>
        <p class="text-muted" style="margin-top: 5px;">Thống kê tháng <b><?php echo "$cur_m/$cur_y"; ?></b></p>
    </div>
    
    <form method="GET" style="display: flex; align-items: center; gap: 10px; background: white; padding: 5px 10px; border-radius: 8px; border: 1px solid #dbdbdb;">
        <label style="font-weight: 600; font-size: 13px; color: #555;">Tháng:</label>
        <input type="month" name="month" value="<?php echo $current_month_str; ?>" onchange="this.form.submit()" style="border: none; outline: none; font-family: inherit; color: #333; cursor: pointer;">
    </form>
</div>

<!-- 1. STATS CARDS -->
<div class="stats-grid">
    <!-- Tổng Tài Sản -->
    <div class="stat-card">
        <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">💰</div>
        <div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Tổng Tài Sản</div>
            <div class="stat-value text-primary"><?php echo number_format($balance); ?> đ</div>
            <div class="stat-sub text-muted">Tất cả các ví</div>
        </div>
    </div>

    <!-- Thu Nhập -->
    <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #166534;">⬇️</div>
        <div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Thu Nhập</div>
            <div class="stat-value text-success">+<?php echo number_format($income_cur); ?> đ</div>
            <div class="stat-sub text-muted">Tháng trước: <?php echo number_format($income_prev); ?> đ</div>
        </div>
    </div>

    <!-- Chi Tiêu -->
    <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #991b1b;">⬆️</div>
        <div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Chi Tiêu</div>
            <div class="stat-value text-danger">-<?php echo number_format($expense_cur); ?> đ</div>
            <div class="stat-sub <?php echo $growth_class; ?>"><?php echo $growth_text; ?> so với tháng trước</div>
        </div>
    </div>
</div>

<!-- 2. BIỂU ĐỒ TRÒN (PIE) -->
<div style="display: flex; gap: 24px; margin-bottom: 24px; flex-wrap: wrap;">
    <!-- Pie: Chi tiêu -->
    <div class="card" style="flex: 1; min-width: 300px;">
        <h3 style="margin-top: 0; text-align: center;">💸 Cơ cấu Chi Tiêu</h3>
        <div class="chart-wrapper">
            <canvas id="pieExpense"></canvas>
        </div>
        <?php if(empty($pie_exp_data)) echo "<p style='text-align:center;color:#999;font-size:13px;margin-top:10px;'>Chưa có dữ liệu chi tiêu tháng này.</p>"; ?>
    </div>

    <!-- Pie: Thu nhập -->
    <div class="card" style="flex: 1; min-width: 300px;">
        <h3 style="margin-top: 0; text-align: center;">💰 Cơ cấu Thu Nhập</h3>
        <div class="chart-wrapper">
            <canvas id="pieIncome"></canvas>
        </div>
        <?php if(empty($pie_inc_data)) echo "<p style='text-align:center;color:#999;font-size:13px;margin-top:10px;'>Chưa có dữ liệu thu nhập tháng này.</p>"; ?>
    </div>
</div>

<!-- 3. BIỂU ĐỒ CỘT & ĐƯỜNG -->
<div style="display: flex; gap: 24px; flex-wrap: wrap;">
    <!-- Bar: Lịch sử 6 tháng -->
    <div class="card" style="flex: 1; min-width: 400px;">
        <h3 style="margin-top: 0;">📊 Thu vs Chi (6 tháng gần nhất)</h3>
        <div class="chart-wrapper">
            <canvas id="barHistory"></canvas>
        </div>
    </div>

    <!-- Line: Xu hướng ngày -->
    <div class="card" style="flex: 1; min-width: 400px;">
        <h3 style="margin-top: 0;">📈 Xu hướng chi tiêu theo ngày</h3>
        <div class="chart-wrapper">
            <canvas id="lineTrend"></canvas>
        </div>
    </div>
</div>

<!-- JAVASCRIPT CHART -->
<script>
    // Config chung cho font chữ đẹp hơn
    Chart.defaults.font.family = "'Barlow', sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. PIE EXPENSE
    <?php if(!empty($pie_exp_data)): ?>
    new Chart(document.getElementById('pieExpense'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($pie_exp_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($pie_exp_data); ?>,
                backgroundColor: <?php echo json_encode($pie_exp_colors); ?>, // DÙNG MÀU CỦA DANH MỤC
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } } }
    });
    <?php endif; ?>

    // 2. PIE INCOME
    <?php if(!empty($pie_inc_data)): ?>
    new Chart(document.getElementById('pieIncome'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($pie_inc_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($pie_inc_data); ?>,
                backgroundColor: <?php echo json_encode($pie_inc_colors); ?>, // DÙNG MÀU CỦA DANH MỤC
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } } }
    });
    <?php endif; ?>

    // 3. BAR HISTORY
    new Chart(document.getElementById('barHistory'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($bar_labels); ?>,
            datasets: [
                {
                    label: 'Thu',
                    data: <?php echo json_encode($bar_income); ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    barPercentage: 0.6
                },
                {
                    label: 'Chi',
                    data: <?php echo json_encode($bar_expense); ?>,
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                    barPercentage: 0.6
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 4. LINE TREND
    new Chart(document.getElementById('lineTrend'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($days); ?>,
            datasets: [{
                label: 'Chi tiêu (VNĐ)',
                data: <?php echo json_encode($line_cur_data); ?>,
                borderColor: '#0095f6',
                backgroundColor: 'rgba(0, 149, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4, // Đường cong mềm mại
                pointRadius: 2,
                pointHoverRadius: 5
            }]
        },
        options: {
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            scales: { 
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false }, title: { display: true, text: 'Ngày' } }
            },
            plugins: { legend: { display: false } }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>