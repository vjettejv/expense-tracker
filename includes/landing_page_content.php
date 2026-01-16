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
                        <li class="nav-item-1"><a href="#">Giới thiệu</a></li>
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