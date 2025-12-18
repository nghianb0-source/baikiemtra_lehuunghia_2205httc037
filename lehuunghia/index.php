<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';
include 'functions.php';
 ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cửa Hàng Trực Tuyến Mini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { background: #f8f9fa; }
        .navbar { background: #007bff; }
        .carousel-item img { height: 500px; object-fit: cover; }
        .card:hover { transform: scale(1.05); transition: 0.3s; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Shop Le_Nghia</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
                    <?php if (!isLoggedIn()): ?>
                        <li><a class="nav-link" href="login.php">Đăng nhập</a></li>
                        <li><a class="nav-link" href="register.php">Đăng ký</a></li>
                    <?php else: ?>
                        <li><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                        <li><a class="nav-link" href="logout.php">Đăng xuất</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Carousel -->
    <div class="carousel slide" data-bs-ride="carousel" id="carouselExample">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="banner/1.jpg" class="d-block w-100" alt="Thư viện ấm cúng với kệ sách và ghế đọc">
        </div>
        <div class="carousel-item">
            <img src="banner/2.jpg" class="d-block w-100" alt="Thư viện trực tuyến hiện đại với sách điện tử">
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

    <!-- Sản phẩm hot -->
    <div class="container my-5">
        <h2 class="text-center">Sản Phẩm Nổi Bật</h2>
        <div class="row">
            <?php
            $stmt = $pdo->query("SELECT * FROM products WHERE hot = 1 LIMIT 6");
            while ($p = $stmt->fetch()):
                $img = $p['hinh_anh'] ? "linkanh/" . $p['hinh_anh'] : "linkanh/no-image.png";
            ?>
            <div class="col-md-4 mb-4">
                <a href="product_detail.php?id=<?php echo $p['id']; ?>" class="text-decoration-none">
                    <div class="card h-100 shadow">
                        <img src="<?php echo $img; ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($p['ten_sp']); ?></h5>
                            <p class="text-danger fw-bold"><?php echo number_format($p['gia']); ?> đ</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center">
            <a href="products.php" class="btn btn-primary btn-lg">Xem tất cả sản phẩm</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>