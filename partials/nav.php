<?php
require_once __DIR__ . '/../config/helpers.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$user = $_SESSION['user'] ?? null;
$role = current_role();
$uri = $_SERVER['REQUEST_URI'] ?? '';
function active_menu($needle, $uri) { return strpos($uri, $needle) !== false ? 'is-active' : ''; }
$showXe = has_role(['giamdoc','sales','thumua','kithuat','admin']);
$showKh = has_role(['giamdoc','sales','ketoan','admin']);
$showGd = has_role(['giamdoc','sales','ketoan','admin']);
$showSpa = has_role(['giamdoc','kithuat','ketoan','admin']);
$showBc = has_role(['giamdoc','ketoan','admin']);
?>
<nav class="site-nav sticky-top">
  <div class="container-fluid nav-shell">
    <a class="site-logo" href="<?= url('index.php') ?>">
      <img src="<?= url('assets/img/logo-thudocar.png') ?>" alt="Thủ Đô Car">
    </a>

    <div class="nav-links d-none d-lg-flex">
      <a class="nav-link-item <?= active_menu('/index.php', $uri) && !active_menu('/cars/', $uri) && !active_menu('/customers/', $uri) && !active_menu('/transactions/', $uri) && !active_menu('/spa/', $uri) && !active_menu('/reports/', $uri) ? 'is-active' : '' ?>" href="<?= url('index.php') ?>">Tổng quan</a>
      <?php if ($showXe): ?><a class="nav-link-item <?= active_menu('/cars/', $uri) ?>" href="<?= url('cars/index.php') ?>">Xe</a><?php endif; ?>
      <?php if ($showKh): ?><a class="nav-link-item <?= active_menu('/customers/', $uri) ?>" href="<?= url('customers/index.php') ?>">Khách hàng</a><?php endif; ?>
      <?php if ($showGd): ?><a class="nav-link-item <?= active_menu('/transactions/', $uri) ?>" href="<?= url('transactions/index.php') ?>">Giao dịch</a><?php endif; ?>
      <?php if ($showSpa): ?><a class="nav-link-item <?= active_menu('/spa/', $uri) ?>" href="<?= url('spa/index.php') ?>">Spa xe</a><?php endif; ?>
      <?php if ($showBc): ?><a class="nav-link-item <?= active_menu('/reports/', $uri) ?>" href="<?= url('reports/index.php') ?>">Báo cáo</a><?php endif; ?>
    </div>

    <div class="nav-actions d-none d-lg-flex">
      <form action="<?= url('cars/index.php') ?>" method="get" class="nav-search">
        <i class="bi bi-search"></i>
        <input type="text" name="kw" placeholder="Tìm theo hãng xe">
      </form>
      <a class="icon-action" href="<?= url('cars/index.php?status=READY') ?>" title="Xe sẵn sàng bán"><i class="bi bi-heart"></i></a>
      <?php if ($user): ?>
        <span class="login-state"><?= e(role_label($role)) ?></span>
        <a class="btn btn-primary btn-logout" href="<?= url('auth/logout.php') ?>">Đăng xuất</a>
      <?php else: ?>
        <a class="btn btn-primary btn-logout" href="<?= url('auth/login.php') ?>">Đăng nhập</a>
      <?php endif; ?>
    </div>

    <button class="navbar-toggler mobile-menu-btn d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>

  <div class="collapse d-lg-none" id="mobileNav">
    <div class="mobile-nav-panel">
      <a class="mobile-link" href="<?= url('index.php') ?>">Tổng quan</a>
      <?php if ($showXe): ?><a class="mobile-link" href="<?= url('cars/index.php') ?>">Xe</a><?php endif; ?>
      <?php if ($showKh): ?><a class="mobile-link" href="<?= url('customers/index.php') ?>">Khách hàng</a><?php endif; ?>
      <?php if ($showGd): ?><a class="mobile-link" href="<?= url('transactions/index.php') ?>">Giao dịch</a><?php endif; ?>
      <?php if ($showSpa): ?><a class="mobile-link" href="<?= url('spa/index.php') ?>">Spa xe</a><?php endif; ?>
      <?php if ($showBc): ?><a class="mobile-link" href="<?= url('reports/index.php') ?>">Báo cáo</a><?php endif; ?>
      <a class="mobile-link" href="<?= $user ? url('auth/logout.php') : url('auth/login.php') ?>"><?= $user ? 'Đăng xuất' : 'Đăng nhập' ?></a>
    </div>
  </div>
</nav>
