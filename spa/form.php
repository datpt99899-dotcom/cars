<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_roles(['giamdoc','kithuat','ketoan','admin']);

$id = (int)($_GET['id'] ?? 0);
$prefCarId = (int)($_GET['maxe'] ?? 0);
$prefPartnerId = (int)($_GET['madoitac'] ?? 0);

$spa = [
  'maphieuspa'=>0,'maxe'=>$prefCarId,'madoitac'=>$prefPartnerId,'ngaybatdau'=>date('Y-m-d'),'ngayketthuc_dukien'=>'',
  'tongchiphi_dukien'=>0,'trangthai'=>'OPEN','ghichu'=>'', 'gara_tu_nhap'=>''
];
$details = [];
if ($id > 0) {
  $stmt = $pdo->prepare("SELECT * FROM phieuspa WHERE maphieuspa=?");
  $stmt->execute([$id]);
  $spa = $stmt->fetch() ?: $spa;
  $stmt = $pdo->prepare("SELECT * FROM chitietspa WHERE maphieuspa=? ORDER BY machitietspa ASC");
  $stmt->execute([$id]);
  $details = $stmt->fetchAll();
}
if (!$details) {
  $details = [
    ['hangmuc_congviec'=>'Sơn tỉa','chiphi_thucte'=>0,'ghichu'=>''],
    ['hangmuc_congviec'=>'Đánh bóng','chiphi_thucte'=>0,'ghichu'=>''],
    ['hangmuc_congviec'=>'Dọn nội thất','chiphi_thucte'=>0,'ghichu'=>''],
  ];
}
$cars = $pdo->query("SELECT maxe, hangxe, dongxe, bienso, trangthai, giamua_vao, tongchiphi_spa, giavon FROM xe ORDER BY maxe DESC")->fetchAll();
$garages = $pdo->query("SELECT madoitac, tendoitac FROM doitac WHERE loai_doitac='gara' ORDER BY tendoitac ASC")->fetchAll();
$statusLabels = ['OPEN'=>'Mới tạo','IN_PROGRESS'=>'Đang thực hiện','DONE'=>'Hoàn tất','CANCEL'=>'Đã hủy'];
$title = $id ? 'Chi tiết phiếu Spa' : 'Tạo phiếu Spa';
require_once __DIR__ . '/../partials/header.php';
?>
<div class="page-header">
  <div><h1 class="page-title"><?= $id ? 'Chi tiết phiếu Spa '.(int)$id : 'Tạo phiếu Spa xe' ?></h1><p class="page-subtitle">Lập hạng mục làm đẹp, nhập chi phí thực tế và đồng bộ ngay vào giá vốn của xe.</p></div>
  <a class="btn btn-outline-secondary" href="<?= url('spa/index.php') ?>">Quay lại</a>
</div>
<form method="post" action="<?= url('spa/save.php') ?>">
  <input type="hidden" name="maphieuspa" value="<?= (int)$spa['maphieuspa'] ?>">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="form-section h-100">
        <div class="form-section-title"><span class="dot"></span>Thông tin phiếu</div>
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Xe</label><select class="form-select" name="maxe" required><?php foreach($cars as $c): ?><option value="<?= (int)$c['maxe'] ?>" <?= (int)$spa['maxe']===(int)$c['maxe']?'selected':'' ?>><?= e($c['hangxe'].' '.$c['dongxe'].' - '.$c['bienso'].' ('.$c['trangthai'].')') ?></option><?php endforeach; ?></select></div>
          <div class="col-12"><label class="form-label">Gara liên kết</label><select class="form-select" name="madoitac"><option value="">Chọn gara hoặc nhập mới bên dưới</option><?php foreach($garages as $g): ?><option value="<?= (int)$g['madoitac'] ?>" <?= (int)$spa['madoitac']===(int)$g['madoitac']?'selected':'' ?>><?= e($g['tendoitac']) ?></option><?php endforeach; ?></select></div>
          <div class="col-12"><label class="form-label">Tên gara mới (nếu chưa có)</label><input class="form-control" name="gara_tu_nhap" value="<?= e($spa['gara_tu_nhap'] ?? '') ?>" placeholder="VD: Gara Thành Công"></div>
          <div class="col-md-6"><label class="form-label">Ngày bắt đầu</label><input class="form-control" type="date" name="ngaybatdau" value="<?= e($spa['ngaybatdau']) ?>" required></div>
          <div class="col-md-6"><label class="form-label">Ngày kết thúc dự kiến</label><input class="form-control" type="date" name="ngayketthuc_dukien" value="<?= e($spa['ngayketthuc_dukien']) ?>"></div>
          <div class="col-md-6"><label class="form-label">Chi phí dự kiến</label><input class="form-control" type="number" step="0.01" name="tongchiphi_dukien" value="<?= e($spa['tongchiphi_dukien']) ?>"></div>
          <div class="col-md-6"><label class="form-label">Trạng thái</label><select class="form-select" name="trangthai"><?php foreach($statusLabels as $k=>$v): ?><option value="<?= $k ?>" <?= $spa['trangthai']===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div>
          <div class="col-12"><label class="form-label">Ghi chú</label><textarea class="form-control" rows="5" name="ghichu"><?= e($spa['ghichu']) ?></textarea></div>
        </div>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="form-section">
        <div class="d-flex justify-content-between align-items-center mb-3"><div class="form-section-title mb-0"><span class="dot"></span>Hạng mục Spa</div><button class="btn btn-sm btn-outline-secondary no-print" type="button" id="addSpaRow"><i class="bi bi-plus-lg me-1"></i>Thêm hạng mục</button></div>
        <div class="table-responsive">
          <table class="table align-middle" id="spaDetailTable">
            <thead><tr><th>Hạng mục</th><th style="width:180px;">Chi phí thực tế</th><th>Ghi chú</th><th style="width:56px;"></th></tr></thead>
            <tbody>
              <?php foreach($details as $d): ?>
              <tr>
                <td><input class="form-control" name="hangmuc_congviec[]" value="<?= e($d['hangmuc_congviec']) ?>" required></td>
                <td><input class="form-control spa-cost" type="number" step="0.01" min="0" name="chiphi_thucte[]" value="<?= e($d['chiphi_thucte']) ?>"></td>
                <td><input class="form-control" name="ghichu_ct[]" value="<?= e($d['ghichu']) ?>"></td>
                <td><button class="btn btn-sm btn-outline-danger" type="button" onclick="this.closest('tr').remove(); recalcSpaTotal();"><i class="bi bi-trash3"></i></button></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="receipt-amount-box mt-3" style="margin:0;">
          <div class="invoice-total-label">Tổng chi phí thực tế</div>
          <div class="invoice-total-value" id="spaTotalDisplay">0</div>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-4 d-flex gap-2"><button class="btn btn-primary"><i class="bi bi-floppy me-2"></i>Lưu phiếu Spa</button><a class="btn btn-outline-secondary" href="<?= url('spa/index.php') ?>">Hủy</a></div>
</form>
<script>
function formatMoneyText(value){
  const amount = Math.round(Number(value) || 0);
  const sign = amount < 0 ? '-' : '';
  const abs = Math.abs(amount);
  const ty = Math.floor(abs / 1000000000);
  const trieu = Math.floor((abs % 1000000000) / 1000000);
  const nghin = Math.floor((abs % 1000000) / 1000);
  if (abs === 0) return '0';
  if (ty > 0) return sign + ty + ' tỉ' + (trieu > 0 ? ' ' + trieu + ' triệu' : '');
  if (trieu > 0) return sign + trieu + ' triệu';
  if (nghin > 0) return sign + nghin + ' nghìn';
  return sign + new Intl.NumberFormat('vi-VN').format(abs);
}
function recalcSpaTotal(){
  let total = 0;
  document.querySelectorAll('.spa-cost').forEach(i => total += parseFloat(i.value || 0) || 0);
  document.getElementById('spaTotalDisplay').textContent = formatMoneyText(total);
}
document.addEventListener('input', function(e){ if (e.target.classList.contains('spa-cost')) recalcSpaTotal(); });
document.getElementById('addSpaRow').addEventListener('click', function(){
  const tr = document.createElement('tr');
  tr.innerHTML = `<td><input class="form-control" name="hangmuc_congviec[]" required></td><td><input class="form-control spa-cost" type="number" step="0.01" min="0" name="chiphi_thucte[]" value="0"></td><td><input class="form-control" name="ghichu_ct[]"></td><td><button class="btn btn-sm btn-outline-danger" type="button" onclick="this.closest('tr').remove(); recalcSpaTotal();"><i class="bi bi-trash3"></i></button></td>`;
  document.querySelector('#spaDetailTable tbody').appendChild(tr);
  recalcSpaTotal();
});
recalcSpaTotal();
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
