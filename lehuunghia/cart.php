<?php
include 'config.php';
include 'functions.php';

// Kiểm tra session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

checkUser(); // Phải đăng nhập

$cart = $_SESSION['cart'] ?? [];

// Lấy flash message (chỉ hiện 1 lần)
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';

unset($_SESSION['flash_success']);
unset($_SESSION['flash_error']);

// Thêm sản phẩm
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];
    if ($id > 0) {
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $_SESSION['cart'] = $cart;
    }
    if (isset($_GET['redirect'])) {
        header("Location: product_detail.php?id=$id&success=1");
    } else {
        header("Location: cart.php");
    }
    exit();
}

// Xóa sản phẩm
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    unset($cart[$id]);
    $_SESSION['cart'] = $cart;
    header("Location: cart.php");
    exit();
}

// Xóa toàn bộ giỏ
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

// Thanh toán - DÙNG FLASH MESSAGE
if (isset($_POST['checkout'])) {
    if (empty($cart)) {
        $_SESSION['flash_error'] = "Giỏ hàng của bạn đang trống!";
        header("Location: cart.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        $total = 0;
        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("SELECT id, ten_sp, gia, so_luong FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($products as $p) {
            $items[$p['id']] = $p;
        }

        foreach ($cart as $id => $qty) {
            if (!isset($items[$id])) {
                throw new Exception("Sản phẩm ID $id không tồn tại!");
            }
            if ($items[$id]['so_luong'] < $qty) {
                throw new Exception("Sản phẩm '{$items[$id]['ten_sp']}' không đủ hàng (còn {$items[$id]['so_luong']})!");
            }
            $total += $items[$id]['gia'] * $qty;
        }

        // Tạo đơn hàng
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, tong_tien, tinh_trang) VALUES (?, ?, 'dang_xu_ly')");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $order_id = $pdo->lastInsertId();

        // Thêm chi tiết và giảm tồn kho
        foreach ($cart as $id => $qty) {
            $gia = $items[$id]['gia'];
            $stmt = $pdo->prepare("INSERT INTO order_details (order_id, product_id, so_luong, gia_ban) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $id, $qty, $gia]);

            $stmt = $pdo->prepare("UPDATE products SET so_luong = so_luong - ? WHERE id = ?");
            $stmt->execute([$qty, $id]);
        }

        $pdo->commit();

        // Xóa giỏ hàng
        unset($_SESSION['cart']);

        // Gửi flash success
        $_SESSION['flash_success'] = "Đặt hàng thành công! Mã đơn hàng: <strong>#$order_id</strong><br>
                                      Tổng tiền: <strong>" . number_format($total) . " đ</strong><br>
                                      Xem chi tiết tại <a href='my_orders.php'>Lịch sử đơn hàng</a>.";

        header("Location: cart.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = "Lỗi khi đặt hàng: " . $e->getMessage();
        header("Location: cart.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ Hàng - Mini Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Giỏ Hàng</h2>

        <!-- ALERT CHỈ HIỆN 1 LẦN NHỜ FLASH MESSAGE -->
        <?php if ($flash_success): ?>
            <div class="alert alert-success alert-dismissible fade show text-center shadow">
                <h4><i class="fas fa-check-circle me-2"></i>Thanh toán thành công!</h4>
                <?php echo $flash_success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($flash_error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $flash_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                <h4>Giỏ hàng trống</h4>
                <p>Hãy thêm sản phẩm để mua sắm nhé!</p>
                <a href="products.php" class="btn btn-primary btn-lg">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <?php
            $total = 0;
            $ids = array_keys($cart);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT id, ten_sp, gia FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $items = [];
            foreach ($products as $p) {
                $items[$p['id']] = $p;
            }
            ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $id => $qty):
                            if (!isset($items[$id])) continue;
                            $p = $items[$id];
                            $subtotal = $p['gia'] * $qty;
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($p['ten_sp']); ?></strong></td>
                            <td><?php echo number_format($p['gia']); ?> đ</td>
                            <td><?php echo $qty; ?></td>
                            <td><?php echo number_format($subtotal); ?> đ</td>
                            <td>
                                <a href="cart.php?remove=<?php echo $id; ?>" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-success">
                            <td colspan="3"><strong>Tổng cộng</strong></td>
                            <td><strong><?php echo number_format($total); ?> đ</strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <a href="cart.php?clear=1" class="btn btn-outline-danger me-3">Xóa giỏ hàng</a>
                <form method="POST" class="d-inline">
                    <button type="submit" name="checkout" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-credit-card me-2"></i>Thanh Toán Ngay
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>