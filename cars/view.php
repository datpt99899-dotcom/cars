<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM xe WHERE maxe = ? LIMIT 1');
$stmt->execute([$id]);
$car = $stmt->fetch();
if (!$car) {
    flash_set('error', 'Không tìm thấy xe.');
    redirect('index.php');
}

$statusLabels = ['NEW'=>'Mới nhập','SPA'=>'Đang spa','READY'=>'Sẵn sàng bán','DEPOSITED'=>'Đã cọc','SOLD'=>'Đã bán','CANCEL'=>'Đã hủy'];
$statusClasses = ['NEW'=>'status-new','SPA'=>'status-spa','READY'=>'status-ready','DEPOSITED'=>'status-deposited','SOLD'=>'status-sold','CANCEL'=>'status-cancel'];
$title = car_title($car);
require_once __DIR__ . '/../partials/header.php';
?>
<div class="page-header">
  <div>
    <h1 class="page-title">Chi tiết xe</h1>
    <p class="page-subtitle">Thông tin chi tiết, giá bán và thao tác nhanh cho từng đầu xe.</p>
  </div>
  <a class="btn btn-outline-secondary" href="<?= url('index.php') ?>"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
</div>

<div class="detail-shell">
  <div class="detail-gallery">
    <img class="detail-hero-img" src="<?= e(get_car_image($car)) ?>" alt="<?= e(car_title($car)) ?>">
  </div>
  <div class="detail-info">
    <span class="status-badge <?= $statusClasses[$car['trangthai']] ?? 'status-new' ?> mb-3"><?= e($statusLabels[$car['trangthai']] ?? $car['trangthai']) ?></span>
    <h1><?= e(car_title($car)) ?></h1>
    <div class="detail-price"><?= money($car['giaban_dukien'] ?? 0) ?></div>
    <div class="detail-meta">
      <div class="meta-box"><small>Hãng</small><b><?= e($car['hangxe'] ?? '') ?></b></div>
      <div class="meta-box"><small>Dòng xe</small><b><?= e($car['dongxe'] ?? '') ?></b></div>
      <div class="meta-box"><small>Năm</small><b><?= e($car['namsanxuat'] ?? '') ?></b></div>
      <div class="meta-box"><small>Biển số</small><b><?= e($car['bienso'] ?? '') ?></b></div>
      <div class="meta-box"><small>Giá vốn</small><b><?= money($car['giavon'] ?? 0) ?></b></div>
      <div class="meta-box"><small>ODO</small><b><?= number_format((int)($car['odo'] ?? 0), 0, ',', '.') ?> km</b></div>
    </div>
    <div class="meta-box">
      <small>Mô tả</small>
      <b style="font-size:1rem;font-weight:600;"><?= e($car['mota'] ?: 'Chưa có mô tả.') ?></b>
    </div>
    <div class="detail-actions">
      <?php if (has_role(['giamdoc','sales','ketoan','admin']) && ($car['trangthai'] ?? '') === 'READY'): ?>
        <a class="btn btn-primary" href="<?= url('transactions/index.php?maxe=' . (int)$car['maxe'] . '#deposit-form') ?>">Tạo giao dịch</a>
      <?php endif; ?>
      <?php if (has_role(['giamdoc','thumua','admin'])): ?>
        <a class="btn btn-outline-secondary" href="<?= url('cars/form.php?id=' . (int)$car['maxe']) ?>">Sửa</a>
      <?php endif; ?>
      <?php if (has_role(['giamdoc','kithuat','ketoan','admin']) && !in_array($car['trangthai'], ['DEPOSITED','SOLD'], true)): ?>
        <a class="btn btn-outline-primary" href="<?= url('spa/form.php?maxe=' . (int)$car['maxe']) ?>">Spa</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
