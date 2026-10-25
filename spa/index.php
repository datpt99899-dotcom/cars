<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_roles(['giamdoc','kithuat','ketoan','admin']);

$status = trim($_GET['status'] ?? '');
$kw = trim($_GET['kw'] ?? '');
$params = [];
$sql = "SELECT p.*, x.hangxe, x.dongxe, x.bienso, x.giamua_vao, x.tongchiphi_spa, d.tendoitac, n.hoten AS tennv,
               COALESCE(SUM(c.chiphi_thucte),0) AS tongchiphi_thucte, COUNT(c.machitietspa) AS so_hangmuc
        FROM phieuspa p
        JOIN xe x ON x.maxe = p.maxe
        LEFT JOIN doitac d ON d.madoitac = p.madoitac
        LEFT JOIN nhanvien n ON n.manhanvien = p.manhanvien
        LEFT JOIN chitietspa c ON c.maphieuspa = p.maphieuspa
        WHERE 1=1";
if ($status !== '') { $sql .= " AND p.trangthai=?"; $params[] = $status; }
if ($kw !== '') {
    $sql .= " AND (x.hangxe LIKE ? OR x.dongxe LIKE ? OR x.bienso LIKE ? OR d.tendoitac LIKE ?)";
    for ($i=0;$i<4;$i++) $params[] = "%{$kw}%";
}
$sql .= " GROUP BY p.maphieuspa ORDER BY p.maphieuspa DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();

$summary = $pdo->query("SELECT COUNT(*) total,
    SUM(trangthai='OPEN') open_count,
    SUM(trangthai='IN_PROGRESS') doing_count,
    SUM(trangthai='DONE') done_count,
    SUM(trangthai='CANCEL') cancel_count
    FROM phieuspa")->fetch();

$readyCars = $pdo->query("SELECT maxe, hangxe, dongxe, bienso, trangthai FROM xe WHERE trangthai IN ('NEW','SPA','READY') ORDER BY maxe DESC")->fetchAll();
$garages = $pdo->query("SELECT madoitac, tendoitac FROM doitac WHERE loai_doitac='gara' ORDER BY tendoitac ASC")->fetchAll();

$statusLabels = ['OPEN'=>'Mới tạo','IN_PROGRESS'=>'Đang thực hiện','DONE'=>'Hoàn tất','CANCEL'=>'Đã hủy'];
$statusClasses = ['OPEN'=>'status-new','IN_PROGRESS'=>'status-spa','DONE'=>'status-ready','CANCEL'=>'status-cancel'];
$title = 'Spa xe';
require_once __DIR__ . '/../partials/header.php';
?>
<div class="page-header">
  <div><h1 class="page-title">Quản lý Spa xe</h1><p class="page-subtitle">Theo dõi làm đẹp, sửa chữa, chi phí thực tế và cập nhật giá vốn cho từng đầu xe.</p></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-lg-5">
    <div class="card form-card h-100">
      <h5 class="mb-3">Tạo nhanh phiếu Spa</h5>
      <form method="get" action="<?= url('spa/form.php') ?>">
        <div class="mb-3"><label class="form-label">Chọn xe</label><select class="form-select" name="maxe" required><option value="">Chọn xe</option><?php foreach($readyCars as $x): ?><option value="<?= (int)$x['maxe'] ?>"><?= e($x['hangxe'].' '.$x['dongxe'].' - '.$x['bienso'].' ('.$x['trangthai'].')') ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label">Gara liên kết</label><select class="form-select" name="madoitac"><option value="">Tự tạo mặc định nếu chưa có</option><?php foreach($garages as $g): ?><option value="<?= (int)$g['madoitac'] ?>"><?= e($g['tendoitac']) ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-primary w-100">Tiếp tục tạo phiếu</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <form class="row g-3 filter-bar" method="get">
      <div class="col-md-7"><label class="form-label">Từ khóa</label><input class="form-control" name="kw" value="<?= e($kw) ?>" placeholder="Tìm theo xe / biển số / gara"></div>
      <div class="col-md-4"><label class="form-label">Trạng thái Spa</label><select class="form-select" name="status"><option value="">Tất cả trạng thái</option><?php foreach($statusLabels as $k=>$v): ?><option value="<?= $k ?>" <?= $status===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div>
      <div class="col-md-1 d-grid align-self-end"><button class="btn btn-outline-secondary">Lọc</button></div>
    </form>
  </div>
</div>
<div class="quick-grid quick-grid-five compact-summary-row">
  <div class="stat-tile"><small>Tổng phiếu Spa</small><b><?= (int)($summary['total'] ?? 0) ?></b></div>
  <div class="stat-tile"><small>Mới tạo</small><b><?= (int)($summary['open_count'] ?? 0) ?></b></div>
  <div class="stat-tile"><small>Đang thực hiện</small><b><?= (int)($summary['doing_count'] ?? 0) ?></b></div>
  <div class="stat-tile"><small>Hoàn tất</small><b><?= (int)($summary['done_count'] ?? 0) ?></b></div>
  <a class="stat-tile stat-action-tile" href="<?= url('spa/form.php') ?>"><span><i class="bi bi-plus-lg me-2"></i>Tạo phiếu Spa</span></a>
</div>
<div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Mã</th><th>Xe</th><th>Gara</th><th>Nhân viên</th><th>Ngày bắt đầu</th><th>Số hạng mục</th><th>Chi phí dự kiến</th><th>Chi phí thực tế</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
      <tr>
        <td><?= (int)$r['maphieuspa'] ?></td>
        <td><div class="fw-semibold"><?= e($r['hangxe'].' '.$r['dongxe']) ?></div><div class="small muted"><?= e($r['bienso']) ?></div></td>
        <td><?= e($r['tendoitac'] ?: 'Chưa gán') ?></td>
        <td><?= e($r['tennv'] ?: '—') ?></td>
        <td><?= e($r['ngaybatdau']) ?></td>
        <td><?= (int)$r['so_hangmuc'] ?></td>
        <td><?= money($r['tongchiphi_dukien']) ?></td>
        <td><?= money($r['tongchiphi_thucte']) ?></td>
        <td><span class="status-badge <?= $statusClasses[$r['trangthai']] ?? 'status-new' ?>"><?= e($statusLabels[$r['trangthai']] ?? $r['trangthai']) ?></span></td>
        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?= url('spa/form.php?id='.(int)$r['maphieuspa']) ?>"><i class="bi bi-pencil-square me-1"></i>Chi tiết</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="10"><div class="empty-state"><div class="empty-icon"><i class="bi bi-brush"></i></div><div>Chưa có phiếu Spa nào.</div></div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
