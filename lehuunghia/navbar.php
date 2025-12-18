<?php
// navbar.php - Thanh điều hướng chung (đã nâng cấp)
// Kiểm tra session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'functions.php'; // Để dùng isLoggedIn(), getUserRole()

// Tính số lượng sản phẩm trong giỏ hàng (nếu dùng session cart như dự án cũ)
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += $qty;
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            <i class="fas fa-book-open-reader me-2"></i>Shop Le_Nghia
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Tìm kiếm nhanh -->
            <form class="d-flex mx-auto my-2 my-lg-0" method="GET" action="search.php" style="max-width: 400px;">
                <input class="form-control me-2" type="search" name="query" placeholder="Tìm product..." aria-label="Tìm kiếm">
                <button class="btn btn-light" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i>Trang Chủ
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <!-- Giỏ hàng với badge số lượng -->
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart fa-lg me-1"></i>Giỏ Hàng
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $cart_count; ?>
                                    <span class="visually-hidden">sản phẩm</span>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <!-- Dropdown user -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="dashboard.php">Bảng Điều Khiển</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Đăng Xuất</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng Nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Đăng Ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>