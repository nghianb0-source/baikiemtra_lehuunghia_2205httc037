<?php
include 'config.php';
include 'functions.php';
checkAdmin(); // Chỉ Admin

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $pdo->prepare("UPDATE orders SET tinh_trang = ? WHERE id = ?")->execute([$status, $order_id]);
    $success = "Cập nhật trạng thái đơn hàng thành công!";
}

$stmt = $pdo->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.ngay_dat DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Đơn Hàng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="text-center mb-4">Quản Lý Đơn Hàng (Toàn Quyền)</h2>
        <?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách</th>
                    <th>Ngày Đặt</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
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
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                            <select name="status" class="form-select form-select-sm d-inline w-auto">
                                <option value="dang_xu_ly" <?php echo $o['tinh_trang']=='dang_xu_ly'?'selected':''; ?>>Đang xử lý</option>
                                <option value="da_giao" <?php echo $o['tinh_trang']=='da_giao'?'selected':''; ?>>Đã giao</option>
                                <option value="da_huy" <?php echo $o['tinh_trang']=='da_huy'?'selected':''; ?>>Đã hủy</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Cập nhật</button>
                        </form>
                    </td>
                    <td><a href="order_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-info btn-sm">Chi tiết</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>