<?php
include 'config.php';
include 'functions.php';

// Đặt múi giờ và kiểm tra session an toàn
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

checkUser(); // Phải đăng nhập mới vào được

$user_role = getUserRole(); // 'admin', 'manager', hoặc 'user'
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';

// Thống kê nhanh (cho admin/manager)
$stats = [];
if ($user_role === 'admin' || $user_role === 'manager') {
    $stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE tinh_trang = 'dang_xu_ly'")->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bảng Điều Khiển - Mini Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { background: #f8f9fa; }
        .dashboard-card {
            transition: all 0.3s;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .icon-bg {
            font-size: 3rem;
            opacity: 0.2;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <div class="text-center mb-5 animate__animated animate__fadeInDown">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-tachometer-alt me-3"></i>Bảng Điều Khiển
            </h1>
            <p class="lead text-muted">Xin chào, <strong><?php echo htmlspecialchars($user_name); ?></strong>! Vai trò: 
                <span class="badge bg-<?php echo $user_role === 'admin' ? 'danger' : ($user_role === 'manager' ? 'warning' : 'success'); ?>">
                    <?php echo ucfirst($user_role); ?>
                </span>
            </p>
        </div>

        <!-- Thống kê nhanh cho Admin/Manager -->
        <?php if ($user_role === 'admin' || $user_role === 'manager'): ?>
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card dashboard-card text-white bg-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-boxes icon-bg position-absolute end-0 bottom-0"></i>
                            <h4><?php echo number_format($stats['total_products']); ?></h4>
                            <p class="mb-0">Tổng Sản Phẩm</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card text-white bg-success">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart icon-bg position-absolute end-0 bottom-0"></i>
                            <h4><?php echo number_format($stats['total_orders']); ?></h4>
                            <p class="mb-0">Tổng Đơn Hàng</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card text-white bg-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-clock icon-bg position-absolute end-0 bottom-0"></i>
                            <h4><?php echo number_format($stats['pending_orders']); ?></h4>
                            <p class="mb-0">Đơn Đang Xử Lý</p>
                        </div>
                    </div>
                </div>
                <?php if ($user_role === 'admin'): ?>
                <div class="col-md-3">
                    <div class="card dashboard-card text-white bg-danger">
                        <div class="card-body text-center">
                            <i class="fas fa-users icon-bg position-absolute end-0 bottom-0"></i>
                            <h4><?php echo number_format($stats['total_users']); ?></h4>
                            <p class="mb-0">Tổng Người Dùng</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Các chức năng theo phân quyền -->
        <div class="row g-4">
            <!-- Quản lý Sản Phẩm (Admin & Manager) -->
            <?php if ($user_role === 'admin' || $user_role === 'manager'): ?>
                <div class="col-md-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-boxes-stacked text-primary fa-4x mb-3"></i>
                            <h5>Quản Lý Sản Phẩm</h5>
                            <p class="text-muted">Thêm, sửa, xóa sản phẩm và danh mục</p>
                            <a href="manage_products.php" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>Vào Quản Lý
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quản lý Đơn Hàng (Admin: toàn quyền, Manager: chỉ xem) -->
            <?php if ($user_role === 'admin'): ?>
                <div class="col-md-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-truck text-success fa-4x mb-3"></i>
                            <h5>Quản Lý Đơn Hàng</h5>
                            <p class="text-muted">Xử lý, giao, hủy đơn hàng toàn hệ thống</p>
                            <a href="manage_orders.php" class="btn btn-success">
                                <i class="fas fa-arrow-right me-2"></i>Vào Quản Lý
                            </a>
                        </div>
                    </div>
                </div>
            <?php elseif ($user_role === 'manager'): ?>
                <div class="col-md-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-eye text-info fa-4x mb-3"></i>
                            <h5>Xem Đơn Hàng</h5>
                            <p class="text-muted">Theo dõi trạng thái đơn hàng</p>
                            <a href="view_orders.php" class="btn btn-info">
                                <i class="fas fa-arrow-right me-2"></i>Xem Đơn Hàng
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quản lý Người Dùng (Chỉ Admin) -->
            <?php if ($user_role === 'admin'): ?>
                <div class="col-md-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users-cog text-danger fa-4x mb-3"></i>
                            <h5>Quản Lý Người Dùng</h5>
                            <p class="text-muted">Thêm, sửa, xóa tài khoản người dùng</p>
                            <a href="manage_users.php" class="btn btn-danger">
                                <i class="fas fa-arrow-right me-2"></i>Vào Quản Lý
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Chức năng cho Client (User thường) -->
            <?php if ($user_role === 'user'): ?>
                <div class="col-md-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-store text-primary fa-4x mb-3"></i>
                            <h5>Xem Sản Phẩm</h5>
                            <p class="text-muted">Duyệt và mua sắm sản phẩm</p>
                            <a href="products.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>Xem Sản Phẩm
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-history text-success fa-4x mb-3"></i>
                            <h5>Lịch Sử Đơn Hàng</h5>
                            <p class="text-muted">Xem và theo dõi đơn hàng của bạn</p>
                            <a href="my_orders.php" class="btn btn-success btn-lg">
                                <i class="fas fa-arrow-right me-2"></i>Xem Đơn Hàng
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-home me-2"></i>Quay Lại Trang Chủ
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>