<?php
include '../config.php';
include '../functions.php';
checkManager(); // Manager và Admin vào được, nhưng chỉ xem

$stmt = $pdo->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.ngay_dat DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xem Đơn Hàng - Quản Lý Kho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../navbar.php'; ?>

    <div class="container my-5">
        <h2 class="text-center mb-4">Xem Toàn Bộ Đơn Hàng (Chỉ Xem)</h2>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Ngày Đặt</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td><?php echo htmlspecialchars($o['full_name']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($o['ngay_dat'])); ?></td>
                    <td><?php echo number_format($o['tong_tien']); ?> đ</td>
                    <td>
                        <span class="badge bg-info"><?php echo $o['tinh_trang']; ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>