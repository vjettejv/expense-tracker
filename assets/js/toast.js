// assets/js/toast.js - Hàm hiển thị thông báo đẹp
function showToast(message, type = 'success') {
    let container = document.getElementById("toast-container");
    if (!container) return; // Nếu trang nào quên chưa thêm div container

    let noti = document.createElement("div");
    noti.className = "noti";
    noti.textContent = message;

    // Set màu dựa trên loại
    if (type === 'success') {
        noti.style.backgroundColor = '#2ecc71'; // Xanh lá
        noti.style.borderLeft = '5px solid #27ae60';
    } else if (type === 'error') {
        noti.style.backgroundColor = '#ed4956'; // Đỏ
        noti.style.borderLeft = '5px solid #c0392b';
    } else {
        noti.style.backgroundColor = '#3498db'; // Xanh dương (Info)
        noti.style.borderLeft = '5px solid #2980b9';
    }

    container.appendChild(noti);

    // Tự động ẩn sau 3s
    setTimeout(function() {
        noti.classList.add("fadeOut");
        noti.addEventListener('animationend', function() {
            noti.remove();
        });
    }, 3000);
}