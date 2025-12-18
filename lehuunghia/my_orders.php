<?php include 'config.php'; include 'functions.php'; checkUser(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Đơn Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2>Lịch Sử Đơn Hàng</h2>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY ngay_dat DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $orders = $stmt->fetchAll();

        if (empty($orders)): ?>
            <p>Bạn chưa có đơn hàng nào.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['ngay_dat'])); ?></td>
                        <td><?php echo number_format($order['tong_tien']); ?> đ</td>
                        <td>
                            <?php
                            $status = ['dang_xu_ly' => 'Đang xử lý', 'da_giao' => 'Đã giao', 'da_huy' => 'Đã hủy'];
                            echo $status[$order['tinh_trang']];
                            ?>
                        </td>
                        <td><a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Xem</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>