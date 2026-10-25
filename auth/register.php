<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $password2 = $_POST['password2'] ?? '';
  $role = 'staff';

  if (strlen($username) < 3) {
    $error = 'Tên đăng nhập phải từ 3 ký tự.';
  } elseif (strlen($password) < 6) {
    $error = 'Mật khẩu phải từ 6 ký tự.';
  } elseif ($password !== $password2) {
    $error = 'Nhập lại mật khẩu không khớp.';
  } else {
    // check exists
    $stmt = $pdo->prepare("SELECT 1 FROM taikhoan WHERE tendangnhap = ? LIMIT 1");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn()) {
      $error = 'Tên đăng nhập đã tồn tại.';
    } else {
      $hash = password_hash($password, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("INSERT INTO taikhoan(tendangnhap, matkhau, phanquyen, trangthai, ngaytao) VALUES(?, ?, ?, 1, NOW())");
      $stmt->execute([$username, $hash, $role]);
      $success = 'Đăng ký thành công. Bạn có thể đăng nhập ngay.';
    }
  }
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Đăng ký - Thủ Đô Car Manager</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="<?= url('assets/style.css') ?>" rel="stylesheet"></head><body><div class="login-shell"><div class="login-card"><div class="login-cover"><div><span class="badge rounded-pill text-bg-light text-primary px-3 py-2 mb-4">Tạo tài khoản mới</span><h1>Đăng ký người dùng</h1><p>Khởi tạo tài khoản để sử dụng hệ thống quản lý showroom.</p></div><div><div class="login-bullet"><i class="bi bi-shield-lock"></i><span>Mật khẩu được mã hóa trước khi lưu trữ</span></div><div class="login-bullet"><i class="bi bi-person-check"></i><span>Phân quyền theo vai trò trong hệ thống</span></div></div></div><div class="login-panel">
    <h3 class="mb-3">Đăng ký tài khoản</h3>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="post" class="card form-card border-0 shadow-none p-0">
      <div class="mb-3">
        <label class="form-label">Tên đăng nhập</label>
        <input name="username" class="form-control" required value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Mật khẩu</label>
        <div class="input-group"><input type="password" id="registerPassword" name="password" class="form-control" required><button class="btn btn-outline-secondary" type="button" data-password-toggle="registerPassword"><i class="bi bi-eye"></i></button></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Nhập lại mật khẩu</label>
        <input type="password" name="password2" class="form-control" required>
      </div>
      <button class="btn btn-dark w-100">Tạo tài khoản</button>

      <div class="mt-3 text-center">
        Đã có tài khoản? <a href="<?= url('auth/login.php') ?>">Đăng nhập</a>
      </div>
      <div class="mt-2 text-center">
        <a href="<?= url('index.php') ?>">Về trang chủ</a>
      </div>
    </form></div></div></div></body></html>
