<?php
include 'config.php';
include 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

checkUser();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Không tìm thấy đơn hàng!";
    header("Location: my_orders.php");
    exit();
}

$order_id = (int)$_GET['id'];
$current_user_id = $_SESSION['user_id'];
$current_role = getUserRole();

// Phân quyền xem đơn hàng chi tiết
if ($current_role === 'admin') {
    $stmt = $pdo->prepare("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
} else {
    $stmt = $pdo->prepare("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ?");
    $stmt->execute([$order_id, $current_user_id]);
}

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error'] = "Đơn hàng không tồn tại hoặc bạn không có quyền xem!";
    header("Location: my_orders.php");
    exit();
}

// Lấy chi tiết sản phẩm trong đơn
$stmt = $pdo->prepare("SELECT od.*, p.ten_sp, p.hinh_anh FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?");
$stmt->execute([$order_id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng #<?php echo $order['id']; ?> - Mini Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .order-card { border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .product-img { width: 80px; height: 80px; object-fit: cover; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card order-card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3><i class="fas fa-receipt me-2"></i>Chi Tiết Đơn Hàng #<?php echo $order['id']; ?></h3>
                        <p class="mb-0">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['ngay_dat'])); ?></p>
                    </div>
                    <div class="card-body">
                        <!-- Thông tin khách hàng & trạng thái -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Thông tin khách hàng</h5>
                                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                                <p><strong>ID User:</strong> <?php echo $order['user_id']; ?></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h5>Trạng thái đơn hàng</h5>
                                <?php
                                $status_class = [
                                    'dang_xu_ly' => 'warning',
                                    'da_giao' => 'success',
                                    'da_huy' => 'danger'
                                ];
                                $status_text = [
                                    'dang_xu_ly' => 'Đang xử lý',
                                    'da_giao' => 'Đã giao',
                                    'da_huy' => 'Đã hủy'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $status_class[$order['tinh_trang']]; ?> fs-5 px-4 py-2">
                                    <?php echo $status_text[$order['tinh_trang']]; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Danh sách sản phẩm trong đơn -->
                        <h5 class="mb-3">Sản phẩm đã mua</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hình ảnh</th>
                                        <th>Sản phẩm</th>
                                        <th>Giá bán</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    foreach ($details as $item): 
                                        $subtotal = $item['gia_ban'] * $item['so_luong'];
                                        $total += $subtotal;
                                        $img = $item['hinh_anh'] ? "linkanh/" . $item['hinh_anh'] : "linkanh/no-image.png";
                                    ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo $img; ?>" class="rounded product-img" alt="<?php echo htmlspecialchars($item['ten_sp']); ?>">
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($item['ten_sp']); ?></strong></td>
                                        <td><?php echo number_format($item['gia_ban']); ?> đ</td>
                                        <td><?php echo $item['so_luong']; ?></td>
                                        <td><strong><?php echo number_format($subtotal); ?> đ</strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-primary">
                                        <td colspan="4" class="text-end"><strong>Tổng cộng</strong></td>
                                        <td><strong class="fs-4 text-primary"><?php echo number_format($order['tong_tien']); ?> đ</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Nút quay lại -->
                        <div class="text-center mt-4">
                            <a href="my_orders.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Quay Lại Lịch Sử Đơn Hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng #<?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>Chi Tiết Đơn Hàng #<?php echo $order['id']; ?></h3>
                <p class="mb-0">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['ngay_dat'])); ?></p>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p><strong>Trạng thái:</strong> 
                            <span class="badge bg-<?php echo $order['tinh_trang'] == 'da_giao' ? 'success' : ($order['tinh_trang'] == 'da_huy' ? 'danger' : 'warning'); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order['tinh_trang'])); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h4>Tổng tiền: <strong class="text-danger"><?php echo number_format($order['tong_tien']); ?> đ</strong></h4>
                    </div>
                </div>

                <h5>Sản phẩm trong đơn</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $item): 
                            $thanhtien = $item['gia_ban'] * $item['so_luong'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['ten_sp']); ?></td>
                            <td><?php echo number_format($item['gia_ban']); ?> đ</td>
                            <td><?php echo $item['so_luong']; ?></td>
                            <td><?php echo number_format($thanhtien); ?> đ</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="text-center">
                    <a href="my_orders.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Quay Lại
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>