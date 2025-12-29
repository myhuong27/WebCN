<?php
// Sidebar menu chuẩn cho tất cả trang admin
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar-menu">
    <a href="dashboard.php" class="menu-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Trang chủ</span>
    </a>
    <a href="quan_ly_nguoi_dung.php" class="menu-item <?php echo ($current_page == 'quan_ly_nguoi_dung.php') ? 'active' : ''; ?>">
        <i class="fas fa-users"></i>
        <span>Người dùng</span>
    </a>
    <a href="quan_ly_thucung.php" class="menu-item <?php echo ($current_page == 'quan_ly_thucung.php') ? 'active' : ''; ?>">
        <i class="fas fa-dog"></i>
        <span>Thú cưng</span>
    </a>
    <a href="quan_ly_dichvu.php" class="menu-item <?php echo ($current_page == 'quan_ly_dichvu.php') ? 'active' : ''; ?>">
        <i class="fas fa-concierge-bell"></i>
        <span>Dịch vụ</span>
    </a>
    <a href="quan_ly_lichhen.php" class="menu-item <?php echo ($current_page == 'quan_ly_lichhen.php') ? 'active' : ''; ?>">
        <i class="fas fa-calendar-alt"></i>
        <span>Lịch hẹn</span>
    </a>
    <a href="quan_ly_nuoiho.php" class="menu-item <?php echo ($current_page == 'quan_ly_nuoiho.php') ? 'active' : ''; ?>">
        <i class="fas fa-heart"></i>
        <span>Nuôi hộ</span>
    </a>
    <a href="quan_ly_chat.php" class="menu-item <?php echo ($current_page == 'quan_ly_chat.php') ? 'active' : ''; ?>">
        <i class="fas fa-comments"></i>
        <span>Chat Support</span>
    </a>
    <a href="quan_ly_cuoc_goi.php" class="menu-item <?php echo ($current_page == 'quan_ly_cuoc_goi.php') ? 'active' : ''; ?>">
        <i class="fas fa-headset"></i>
        <span>Yêu cầu tư vấn</span>
    </a>
    <a href="quan_ly_lich_kham.php" class="menu-item <?php echo ($current_page == 'quan_ly_lich_kham.php') ? 'active' : ''; ?>">
        <i class="fas fa-stethoscope"></i>
        <span>Lịch khám</span>
    </a>
    <a href="quan_ly_danh_gia.php" class="menu-item <?php echo ($current_page == 'quan_ly_danh_gia.php') ? 'active' : ''; ?>">
        <i class="fas fa-star"></i>
        <span>Đánh giá</span>
    </a>
    <a href="quan_ly_baiviet.php" class="menu-item <?php echo ($current_page == 'quan_ly_baiviet.php') ? 'active' : ''; ?>">
        <i class="fas fa-newspaper"></i>
        <span>Bài viết</span>
    </a>
    <a href="../index.php" class="menu-item">
        <i class="fas fa-globe"></i>
        <span>Xem website</span>
    </a>
    <a href="../auth/logout.php" class="menu-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Đăng xuất</span>
    </a>
</nav>
