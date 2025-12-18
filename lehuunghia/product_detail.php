<?php include 'config.php'; include 'functions.php'; session_start(); 
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) die("Sản phẩm không tồn tại!");
$img = $product['hinh_anh'] ? "linkanh/" . $product['hinh_anh'] : "linkanh/no-image.png";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['ten_sp']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center">Đã thêm vào giỏ hàng thành công!</div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-5">
                <img src="<?php echo $img; ?>" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-7">
                <h1><?php echo htmlspecialchars($product['ten_sp']); ?></h1>
                <h3 class="text-danger"><?php echo number_format($product['gia']); ?> đ</h3>
                <p><strong>Còn lại:</strong> <?php echo $product['so_luong']; ?> sản phẩm</p>
                <p><?php echo nl2br(htmlspecialchars($product['mo_ta'])); ?></p>

                <?php if (isLoggedIn() && $product['so_luong'] > 0): ?>
                    <a href="cart.php?add=<?php echo $product['id']; ?>&redirect=1" class="btn btn-success btn-lg">
                        Thêm vào giỏ hàng
                    </a>
                <?php endif; ?>
                <a href="products.php" class="btn btn-secondary btn-lg ms-3">Quay lại</a>
            </div>
        </div>
    </div>
</body>
</html>