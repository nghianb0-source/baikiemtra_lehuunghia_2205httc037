<?php
include 'config.php';  // Nếu file nằm trong thư mục admin/, dùng '../'
include 'functions.php';
checkManager(); // Chỉ admin và manager mới vào được

$success = $error = '';

// Thư mục lưu ảnh sản phẩm (tạo thư mục nếu chưa có)
$upload_dir = '../linkanh/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// XỬ LÝ THÊM SẢN PHẨM
if (isset($_POST['add_product'])) {
    $ten_sp = trim($_POST['ten_sp']);
    $gia = (int)$_POST['gia'];
    $so_luong = (int)$_POST['so_luong'];
    $mo_ta = trim($_POST['mo_ta']);
    $hot = isset($_POST['hot']) ? 1 : 0;

    $hinh_anh = '';

    // Xử lý upload ảnh
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $file_name = time() . '_' . basename($_FILES['hinh_anh']['name']);
        $target_file = $upload_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Kiểm tra định dạng ảnh
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file)) {
                $hinh_anh = $file_name;
            } else {
                $error = "Lỗi khi upload ảnh!";
            }
        } else {
            $error = "Chỉ chấp nhận file JPG, JPEG, PNG & GIF!";
        }
    }

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (ten_sp, gia, so_luong, hinh_anh, mo_ta, hot) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ten_sp, $gia, $so_luong, $hinh_anh, $mo_ta, $hot]);
            $success = "Thêm sản phẩm thành công!";
        } catch (Exception $e) {
            $error = "Lỗi thêm sản phẩm: " . $e->getMessage();
        }
    }
}

// XỬ LÝ SỬA SẢN PHẨM
if (isset($_POST['edit_product'])) {
    $id = (int)$_POST['id'];
    $ten_sp = trim($_POST['ten_sp']);
    $gia = (int)$_POST['gia'];
    $so_luong = (int)$_POST['so_luong'];
    $mo_ta = trim($_POST['mo_ta']);
    $hot = isset($_POST['hot']) ? 1 : 0;

    $hinh_anh = $_POST['old_image']; // Giữ ảnh cũ nếu không upload mới

    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $file_name = time() . '_' . basename($_FILES['hinh_anh']['name']);
        $target_file = $upload_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file)) {
                // Xóa ảnh cũ nếu có
                if ($hinh_anh && file_exists($upload_dir . $hinh_anh)) {
                    unlink($upload_dir . $hinh_anh);
                }
                $hinh_anh = $file_name;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE products SET ten_sp = ?, gia = ?, so_luong = ?, hinh_anh = ?, mo_ta = ?, hot = ? WHERE id = ?");
        $stmt->execute([$ten_sp, $gia, $so_luong, $hinh_anh, $mo_ta, $hot, $id]);
        $success = "Cập nhật sản phẩm thành công!";
    } catch (Exception $e) {
        $error = "Lỗi cập nhật: " . $e->getMessage();
    }
}

// XỬ LÝ XÓA SẢN PHẨM
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Lấy ảnh để xóa file
        $stmt = $pdo->prepare("SELECT hinh_anh FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        // Xóa file ảnh nếu có
        if ($product['hinh_anh'] && file_exists($upload_dir . $product['hinh_anh'])) {
            unlink($upload_dir . $product['hinh_anh']);
        }

        $success = "Xóa sản phẩm thành công!";
    } catch (Exception $e) {
        $error = "Lỗi xóa sản phẩm: " . $e->getMessage();
    }
}

// Lấy danh sách sản phẩm
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sản Phẩm - Quản Lý Kho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .product-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="text-center text-primary mb-4">
            <i class="fas fa-boxes-stacked me-3"></i>Quản Lý Sản Phẩm (Kho Hàng)
        </h2>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form Thêm Sản Phẩm -->
        <div class="card mb-5 shadow">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-plus me-2"></i>Thêm Sản Phẩm Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" name="ten_sp" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Giá (VNĐ)</label>
                            <input type="number" name="gia" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Số lượng</label>
                            <input type="number" name="so_luong" class="form-control" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="mo_ta" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ảnh sản phẩm</label>
                            <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-3 align-self-end">
                            <div class="form-check">
                                <input type="checkbox" name="hot" class="form-check-input" id="hot">
                                <label class="form-check-label" for="hot">Sản phẩm Hot</label>
                            </div>
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" name="add_product" class="btn btn-success w-100">
                                <i class="fas fa-save me-2"></i>Thêm Sản Phẩm
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng Danh Sách Sản Phẩm -->
        <h4 class="mb-3">Danh Sách Sản Phẩm</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên SP</th>
                        <th>Giá</th>
                        <th>Số Lượng</th>
                        <th>Hot</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $img = $p['hinh_anh'] ? "../linkanh/" . $p['hinh_anh'] : "../linkanh/no-image.png";
                    ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td>
                            <img src="<?php echo $img; ?>" class="product-img shadow" alt="<?php echo htmlspecialchars($p['ten_sp']); ?>">
                        </td>
                        <td><strong><?php echo htmlspecialchars($p['ten_sp']); ?></strong></td>
                        <td><?php echo number_format($p['gia']); ?> đ</td>
                        <td><?php echo $p['so_luong']; ?></td>
                        <td>
                            <?php if ($p['hot']): ?>
                                <span class="badge bg-danger">Hot</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Bình thường</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Nút Sửa (modal) -->
                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $p['id']; ?>">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <!-- Nút Xóa -->
                            <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Xóa sản phẩm này? Ảnh và dữ liệu sẽ bị xóa vĩnh viễn!')">
                                <i class="fas fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>

                    <!-- Modal Sửa Sản Phẩm -->
                    <div class="modal fade" id="editModal<?php echo $p['id']; ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Sửa sản phẩm: <?php echo htmlspecialchars($p['ten_sp']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                        <input type="hidden" name="old_image" value="<?php echo $p['hinh_anh']; ?>">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label>Tên sản phẩm</label>
                                                <input type="text" name="ten_sp" value="<?php echo htmlspecialchars($p['ten_sp']); ?>" class="form-control" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Giá</label>
                                                <input type="number" name="gia" value="<?php echo $p['gia']; ?>" class="form-control" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Số lượng</label>
                                                <input type="number" name="so_luong" value="<?php echo $p['so_luong']; ?>" class="form-control" required>
                                            </div>
                                            <div class="col-12">
                                                <label>Mô tả</label>
                                                <textarea name="mo_ta" class="form-control" rows="3"><?php echo htmlspecialchars($p['mo_ta']); ?></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label>Ảnh hiện tại</label><br>
                                                <img src="<?php echo $img; ?>" class="product-img mb-2">
                                                <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                                            </div>
                                            <div class="col-md-6 align-self-end">
                                                <div class="form-check">
                                                    <input type="checkbox" name="hot" class="form-check-input" id="hot<?php echo $p['id']; ?>" <?php echo $p['hot'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="hot<?php echo $p['id']; ?>">Sản phẩm Hot</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" name="edit_product" class="btn btn-warning">Cập Nhật</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Quay Lại Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>