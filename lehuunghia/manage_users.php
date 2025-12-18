<?php
include 'config.php';
include 'functions.php';
checkAdmin(); // Chỉ Admin mới vào được

$success = $error = '';

// XỬ LÝ THÊM NGƯỜI DÙNG
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($password)) {
        $error = "Mật khẩu không được để trống!";
    } else {
        $hashed = hashPassword($password);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashed, $full_name, $email, $role]);
            $success = "Thêm người dùng thành công!";
        } catch (PDOException $e) {
            $error = "Lỗi: " . ($e->getCode() == 23000 ? "Username hoặc email đã tồn tại!" : $e->getMessage());
        }
    }
}

// XỬ LÝ SỬA ROLE
if (isset($_POST['edit_role'])) {
    $id = (int)$_POST['id'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
        $success = "Cập nhật vai trò thành công!";
    } catch (Exception $e) {
        $error = "Lỗi cập nhật vai trò!";
    }
}

// XỬ LÝ XÓA NGƯỜI DÙNG
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id == $_SESSION['user_id']) {
        $error = "Bạn không thể tự xóa tài khoản của chính mình!";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Xóa người dùng thành công!";
        } catch (Exception $e) {
            $error = "Lỗi xóa người dùng!";
        }
    }
}

// Lấy danh sách người dùng
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Người Dùng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .user-avatar { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="text-center text-danger mb-4">
            <i class="fas fa-users-cog me-3"></i>Quản Lý Người Dùng (Chỉ Admin)
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

        <!-- Form Thêm Người Dùng -->
        <div class="card mb-5 shadow">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-user-plus me-2"></i>Thêm Người Dùng Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Họ tên</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Vai trò</label>
                            <select name="role" class="form-select" required>
                                <option value="user">User (Khách hàng)</option>
                                <option value="manager">Manager (Quản lý kho)</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" name="add_user" class="btn btn-success w-100">
                                <i class="fas fa-plus me-2"></i>Thêm Người Dùng
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng Danh Sách Người Dùng -->
        <h4 class="mb-3">Danh Sách Người Dùng</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr <?php echo $u['id'] == $_SESSION['user_id'] ? 'class="table-warning"' : ''; ?>>
                        <td><?php echo $u['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'manager' ? 'warning' : 'success'); ?>">
                                <?php echo ucfirst($u['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                        <td>
                            <!-- Nút Sửa Role -->
                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editRoleModal<?php echo $u['id']; ?>">
                                <i class="fas fa-user-edit"></i> Sửa Role
                            </button>

                            <!-- Nút Xóa (không cho xóa chính mình) -->
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('Xóa người dùng này? Tất cả dữ liệu liên quan (đơn hàng, giỏ hàng...) sẽ bị xóa!')">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Tài khoản hiện tại</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Modal Sửa Role -->
                    <div class="modal fade" id="editRoleModal<?php echo $u['id']; ?>">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Sửa vai trò: <?php echo htmlspecialchars($u['username']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                        <label>Chọn vai trò mới</label>
                                        <select name="role" class="form-select" required>
                                            <option value="user" <?php echo $u['role'] == 'user' ? 'selected' : ''; ?>>User (Khách hàng)</option>
                                            <option value="manager" <?php echo $u['role'] == 'manager' ? 'selected' : ''; ?>>Manager (Quản lý kho)</option>
                                            <option value="admin" <?php echo $u['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" name="edit_role" class="btn btn-warning">Cập Nhật Role</button>
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
            <a href="../dashboard.php" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Quay Lại Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>