<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $stmt = $pdo->prepare("SELECT mataikhoan, tendangnhap, matkhau, phanquyen, trangthai FROM taikhoan WHERE tendangnhap = ? LIMIT 1");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$user || !password_verify($password, $user['matkhau'])) $error = 'Sai tài khoản hoặc mật khẩu.';
  elseif ((int)$user['trangthai'] === 0) $error = 'Tài khoản đang bị khóa.';
  else { $_SESSION['user'] = ['id'=>$user['mataikhoan'],'username'=>$user['tendangnhap'],'role'=>$user['phanquyen']]; redirect('index.php'); }
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Đăng nhập - Thủ Đô Car Manager</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="<?= url('assets/style.css') ?>" rel="stylesheet"></head>
<body>
<div class="login-shell">
  <div class="login-card">
    <div class="login-cover">
      <div>
        <span class="badge rounded-pill text-bg-light text-primary px-3 py-2 mb-4">Hệ thống quản lý showroom ô tô cũ</span>
        <h1>Thủ Đô Car Manager</h1>
        <p>Theo dõi xe, khách hàng, giao dịch và báo cáo trên một hệ thống thống nhất.</p>
      </div>
      <div>
        <div class="login-bullet"><i class="bi bi-check2-circle"></i><span>Theo dõi trạng thái xe theo thời gian thực</span></div>
        <div class="login-bullet"><i class="bi bi-check2-circle"></i><span>Thực hiện đặt cọc và bán xe ngay trên hệ thống</span></div>
        <div class="login-bullet"><i class="bi bi-check2-circle"></i><span>Xem báo cáo doanh số, giá vốn và lợi nhuận theo tháng</span></div>
      </div>
    </div>
    <div class="login-panel">
      <div class="mb-4"><h3 class="mb-2">Đăng nhập</h3><p class="text-muted mb-0">Nhập tài khoản để vào hệ thống.</p></div>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="card form-card border-0 shadow-none p-0">
        <div class="mb-3"><label class="form-label">Tài khoản</label><input name="username" class="form-control" required value="<?= e($_POST['username'] ?? '') ?>"></div>
        <div class="mb-3"><label class="form-label">Mật khẩu</label><div class="input-group"><input type="password" id="loginPassword" name="password" class="form-control" required><button class="btn btn-outline-secondary" type="button" data-password-toggle="loginPassword"><i class="bi bi-eye"></i></button></div></div>
        <button class="btn btn-primary w-100 mt-2">Đăng nhập</button>
        <div class="mt-3 text-center small">Chưa có tài khoản? <a href="<?= url('auth/register.php') ?>">Đăng ký</a></div>
      </form>
    </div>
  </div>
</div>
</body></html>