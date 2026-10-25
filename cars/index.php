<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/helpers.php";

$kw = trim($_GET['kw'] ?? '');
$max = trim($_GET['max'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM xe WHERE 1=1";
$params = [];
if ($kw !== '') { $sql .= " AND (hangxe LIKE ? OR dongxe LIKE ? OR bienso LIKE ?)"; $params[] = "%{$kw}%"; $params[] = "%{$kw}%"; $params[] = "%{$kw}%"; }
if ($max !== '' && is_numeric($max)) { $sql .= " AND giaban_dukien <= ?"; $params[] = (float)$max; }
if ($status !== '') { $sql .= " AND trangthai = ?"; $params[] = $status; }
$sql .= " ORDER BY maxe DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $cars = $stmt->fetchAll();

$summary = $pdo->query("SELECT COUNT(*) AS total, SUM(trangthai='READY') AS ready_count, SUM(trangthai='DEPOSITED') AS deposited_count, SUM(trangthai='SOLD') AS sold_count FROM xe")->fetch();
$statusLabels = ['NEW'=>'Mới nhập','SPA'=>'Đang spa','READY'=>'Sẵn sàng bán','DEPOSITED'=>'Đã cọc','SOLD'=>'Đã bán'];
$statusClasses = ['NEW'=>'status-new','SPA'=>'status-spa','READY'=>'status-ready','DEPOSITED'=>'status-deposited','SOLD'=>'status-sold'];
$statusIcons = ['NEW'=>'bi-box-seam','SPA'=>'bi-brush','READY'=>'bi-check2-circle','DEPOSITED'=>'bi-bookmark-check','SOLD'=>'bi-patch-check'];
$title = 'Quản lý xe'; require_once __DIR__ . '/../partials/header.php';
?>
<div class="page-header">
  <div><h1 class="page-title">Quản lý xe</h1><p class="page-subtitle">Theo dõi tồn kho, hình ảnh, trạng thái và thông tin tài chính của từng đầu xe.</p></div>
</div>
<form class="row g-3 filter-bar" method="get">
  <div class="col-lg-5"><label class="form-label">Từ khóa</label><input class="form-control" name="kw" value="<?= e($kw) ?>" placeholder="Tìm theo hãng / dòng / biển số"></div>
  <div class="col-lg-3"><label class="form-label">Giá bán dự kiến tối đa</label><input class="form-control" name="max" value="<?= e($max) ?>" placeholder="VD: 1000000000"></div>
  <div class="col-lg-3"><label class="form-label">Trạng thái</label><select class="form-select" name="status"><option value="">Tất cả trạng thái</option><?php foreach (['NEW'=>'Mới nhập','SPA'=>'Đang spa','READY'=>'Sẵn sàng bán','DEPOSITED'=>'Đã cọc','SOLD'=>'Đã bán'] as $k=>$v): ?><option value="<?= $k ?>" <?= $status===$k?'selected':''; ?>><?= $v ?></option><?php endforeach; ?></select></div>
  <div class="col-lg-1 d-grid align-self-end"><button class="btn btn-outline-secondary">Lọc</button></div>
</form>
<div class="quick-grid quick-grid-five compact-summary-row">
  <div class="stat-tile"><small>Tổng đầu xe</small><b><?= (int)($summary['total'] ?? 0) ?></b></div>
  <div class="stat-tile"><small>Sẵn sàng bán</small><b><?= (int)($summary['ready_count'] ?? 0) ?></b></div>
  <div class="stat-tile"><small>Đã cọc</small><b><?= (int)($summary['deposited_count'] ?? 0) ?></b></div>
  <div class="stat-tile"><small>Đã bán</small><b><?= (int)($summary['sold_count'] ?? 0) ?></b></div>
  <a class="stat-tile stat-action-tile" href="<?= url('cars/form.php') ?>"><span><i class="bi bi-plus-lg me-2"></i>Thêm xe</span></a>
</div>
<div class="car-list-wrap">
  <?php if (!empty($cars)): ?>
    <div class="car-listings">
      <?php foreach ($cars as $c): ?>
        <article class="car-list-card">
          <div class="car-list-media">
            <a href="<?= url('cars/view.php?id='.(int)$c['maxe']) ?>">
              <img class="car-list-image" src="<?= e(get_car_image($c)) ?>" alt="<?= e(trim($c['hangxe'].' '.$c['dongxe'])) ?>">
            </a>
          </div>
          <div class="car-list-main">
            <div class="car-list-topline">
              <div class="car-list-id">Mã xe <?= (int)$c['maxe']; ?></div>
              <div class="car-list-biense">Biển số: <?= e($c['bienso']) ?></div>
            </div>
            <h2 class="car-list-title">
              <a href="<?= url('cars/view.php?id='.(int)$c['maxe']) ?>"><?= e(trim($c['hangxe'].' '.$c['dongxe'])) ?></a>
            </h2>
            <div class="car-list-color">Màu: <?= e($c['mausac'] ?: 'Chưa cập nhật') ?></div>
            <div class="car-list-meta">
              <span><strong>Năm:</strong> <?= e($c['namsanxuat']) ?></span>
              <span><strong>ODO:</strong> <?= number_format((int)$c['odo'], 0, ',', '.') ?> km</span>
              <span><strong>Giá vốn:</strong> <?= money($c['giavon']) ?></span>
              <span><strong>Giá bán DK:</strong> <?= money($c['giaban_dukien']) ?></span>
            </div>
          </div>
          <div class="car-list-side">
            <div class="car-list-price"><?= money($c['giaban_dukien']) ?></div>
            <div class="car-list-status">
              <span class="status-badge <?= $statusClasses[$c['trangthai']] ?? 'status-new' ?>">
                <i class="bi <?= $statusIcons[$c['trangthai']] ?? 'bi-circle' ?>"></i><?= e($statusLabels[$c['trangthai']] ?? $c['trangthai']) ?>
              </span>
            </div>
            <div class="car-list-actions">
              <a class="btn btn-sm btn-outline-dark" href="<?= url('cars/view.php?id='.(int)$c['maxe']) ?>" title="Xem"><i class="bi bi-eye"></i></a>
              <a class="btn btn-sm btn-outline-secondary" href="<?= url('cars/form.php?id='.(int)$c['maxe']) ?>" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
              <?php if (!in_array($c['trangthai'], ['DEPOSITED','SOLD'], true)): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?= url('spa/form.php?maxe='.(int)$c['maxe']) ?>" title="Spa xe"><i class="bi bi-brush"></i></a>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-danger" href="<?= url('cars/delete.php?id='.(int)$c['maxe']) ?>" onclick="return confirm('Xóa xe này?')" title="Xóa"><i class="bi bi-trash3"></i></a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="table-responsive"><div class="empty-state"><div class="empty-icon"><i class="bi bi-car-front"></i></div><div>Chưa có xe nào phù hợp bộ lọc.</div></div></div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>