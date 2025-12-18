<?php
 include 'config.php';
 include 'functions.php'; 
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh Sách Sản Phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card:hover { transform: translateY(-10px); transition: 0.3s; }
        .book-img { height: 300px; object-fit: cover; }
    </style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; // Bạn tự tạo navbar giống index ?>

    <div class="container my-5">
        <h1 class="text-center mb-5">Tất Cả Sản Phẩm</h1>
        <div class="row g-4">
            <?php
            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
            while ($p = $stmt->fetch()):
                $img = $p['hinh_anh'] ? "linkanh/" . $p['hinh_anh'] : "linkanh/no-image.png";
            ?>
            <div class="col-md-4">
                <a href="product_detail.php?id=<?php echo $p['id']; ?>" class="text-decoration-none">
                    <div class="card h-100 shadow">
                        <img src="<?php echo $img; ?>" class="card-img-top book-img">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($p['ten_sp']); ?></h5>
                            <p class="text-danger fw-bold"><?php echo number_format($p['gia']); ?> đ</p>
                            <p>Còn: <?php echo $p['so_luong']; ?> sản phẩm</p>
                        </div>
                        <div class="card-footer">
                            <?php if (isLoggedIn() && $p['so_luong'] > 0): ?>
                                <a href="cart.php?add=<?php echo $p['id']; ?>" class="btn btn-success w-100">Thêm vào giỏ</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>