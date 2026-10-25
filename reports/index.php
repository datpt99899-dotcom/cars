<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/helpers.php";

$month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}

$start = $month . '-01';
$end = date('Y-m-d', strtotime($start . ' +1 month'));

$statusLabels = [
    'NEW' => 'Mới nhập',
    'SPA' => 'Đang spa',
    'READY' => 'Sẵn sàng bán',
    'DEPOSITED' => 'Đã cọc',
    'SOLD' => 'Đã bán',
];
$paymentLabels = ['cash' => 'Tiền mặt', 'bank' => 'Chuyển khoản', 'loan' => 'Trả góp'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS so_xe_ban, COALESCE(SUM(giaban_chot), 0) AS tong_doanh_thu FROM phieuban WHERE trangthai_giaodich = 'SOLD' AND ngayban >= ? AND ngayban < ?");
$stmt->execute([$start, $end]);
$sales = $stmt->fetch() ?: ['so_xe_ban' => 0, 'tong_doanh_thu' => 0];

$inv = $pdo->query("SELECT trangthai, COUNT(*) AS so_luong FROM xe GROUP BY trangthai ORDER BY trangthai")->fetchAll();

$labels6 = [];
$revenue6 = [];
$count6 = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime(date('Y-m-01') . " -$i month"));
    $monthEnd = date('Y-m-d', strtotime($monthStart . ' +1 month'));
    $label = date('m/Y', strtotime($monthStart));
    $q = $pdo->prepare("SELECT COUNT(*) AS so_xe_ban, COALESCE(SUM(giaban_chot),0) AS tong_doanh_thu FROM phieuban WHERE trangthai_giaodich='SOLD' AND ngayban >= ? AND ngayban < ?");
    $q->execute([$monthStart, $monthEnd]);
    $row = $q->fetch() ?: ['so_xe_ban' => 0, 'tong_doanh_thu' => 0];
    $labels6[] = $label;
    $revenue6[] = (float)$row['tong_doanh_thu'];
    $count6[] = (int)$row['so_xe_ban'];
}

$soldRowsStmt = $pdo->prepare("SELECT p.maphieuban,p.giaban_chot,p.tiencoc_ban,p.hinhthucthanhtoan,p.ngayban,x.maxe,x.hangxe,x.dongxe,x.bienso,x.giavon,x.giamua_vao,x.tongchiphi_spa,k.makhachhang,k.hoten,k.sodienthoai FROM phieuban p JOIN xe x ON x.maxe = p.maxe JOIN khachhang k ON k.makhachhang = p.makhachhang WHERE p.trangthai_giaodich = 'SOLD' AND p.ngayban >= ? AND p.ngayban < ? ORDER BY p.ngayban DESC, p.maphieuban DESC");
$soldRowsStmt->execute([$start, $end]);
$soldRows = $soldRowsStmt->fetchAll();

$totalCost = 0;
$totalProfit = 0;
foreach ($soldRows as $row) {
    $giaVon = isset($row['giavon']) && $row['giavon'] !== null
        ? (float)$row['giavon']
        : (float)($row['giamua_vao'] ?? 0) + (float)($row['tongchiphi_spa'] ?? 0);
    $giaBan = (float)($row['giaban_chot'] ?? 0);
    $totalCost += $giaVon;
    $totalProfit += ($giaBan - $giaVon);
}

$chartLabels = [];
$chartValues = [];
$inventoryRows = [];
foreach ($inv as $r) {
    $label = $statusLabels[$r['trangthai']] ?? $r['trangthai'];
    $count = (int)$r['so_luong'];
    $chartLabels[] = $label;
    $chartValues[] = $count;
    $inventoryRows[] = ['label' => $label, 'count' => $count];
}

$title = "Báo cáo";
require_once __DIR__ . "/../partials/header.php";
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Báo cáo và thống kê</h1>
    <p class="page-subtitle">Chỉ sửa giao diện báo cáo, giữ nguyên dữ liệu và chức năng.</p>
  </div>
</div>

<form class="row g-3 filter-bar mb-4" method="get" style="max-width: 520px;">
  <div class="col-8">
    <label class="form-label">Tháng báo cáo</label>
    <input class="form-control" type="text" name="month" value="<?= e($month) ?>" placeholder="YYYY-MM, ví dụ: 2026-03">
  </div>
  <div class="col-4 d-grid align-self-end">
    <button class="btn btn-outline-secondary">Xem</button>
  </div>
</form>

<div class="quick-grid compact-summary-row mb-4">
  <div class="stat-tile"><small>Số xe đã bán</small><b><?= (int)($sales['so_xe_ban'] ?? 0) ?></b></div>
  <div class="stat-tile"><small>Doanh thu</small><b><?= money($sales['tong_doanh_thu'] ?? 0) ?></b></div>
  <div class="stat-tile"><small>Tổng giá vốn</small><b><?= money($totalCost) ?></b></div>
  <div class="stat-tile"><small>Lãi / Lỗ</small><b class="<?= $totalProfit >= 0 ? 'text-success' : 'text-danger' ?>"><?= $totalProfit >= 0 ? money($totalProfit) : '-' . money(abs($totalProfit)) ?></b></div>
</div>

<div class="report-grid-6m mb-4">
  <div class="chart-panel">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
      <div>
        <h3>Doanh thu 6 tháng gần nhất</h3>
        <div class="chart-sub">So sánh doanh thu và số xe đã bán theo tháng</div>
      </div>
      <div class="info-chip"><i class="bi bi-graph-up-arrow me-2"></i>Xu hướng doanh thu</div>
    </div>
    <div class="chart-6m-wrap"><canvas id="sales6MonthChart"></canvas></div>
  </div>
  <div class="chart-panel">
    <h3>Tỷ lệ trạng thái xe</h3>
    <div class="chart-sub">Cơ cấu tồn kho theo trạng thái</div>
    <div class="chart-wrap-sm"><canvas id="inventoryChart"></canvas></div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header"><b>Báo cáo doanh số tháng <?= e($month) ?></b></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6"><div class="info-chip w-100 justify-content-between"><span><i class="bi bi-car-front me-2"></i>Số xe đã bán</span><strong><?= (int)($sales['so_xe_ban'] ?? 0) ?></strong></div></div>
          <div class="col-md-6"><div class="info-chip w-100 justify-content-between"><span><i class="bi bi-cash-coin me-2"></i>Doanh thu</span><strong><?= money($sales['tong_doanh_thu'] ?? 0) ?></strong></div></div>
          <div class="col-md-6"><div class="info-chip w-100 justify-content-between"><span><i class="bi bi-wallet2 me-2"></i>Giá vốn</span><strong><?= money($totalCost) ?></strong></div></div>
          <div class="col-md-6"><div class="info-chip w-100 justify-content-between"><span><i class="bi bi-graph-up me-2"></i>Lãi / Lỗ</span><strong class="<?= $totalProfit >= 0 ? 'text-success' : 'text-danger' ?>"><?= $totalProfit >= 0 ? money($totalProfit) : '-' . money(abs($totalProfit)) ?></strong></div></div>
        </div>
        <div class="small text-muted mt-3">Tính theo các phiếu bán có trạng thái Đã bán trong tháng.</div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header"><b>Báo cáo tồn kho theo trạng thái</b></div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr><th>Trạng thái</th><th class="text-end">Số lượng</th></tr>
            </thead>
            <tbody>
              <?php foreach ($inventoryRows as $item): ?>
                <tr>
                  <td><?= e($item['label']) ?></td>
                  <td class="text-end fw-bold"><?= (int)$item['count'] ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($inventoryRows)): ?>
                <tr><td colspan="2" class="text-center text-muted py-4">Chưa có dữ liệu tồn kho</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header"><b>Chi tiết xe đã bán trong tháng <?= e($month) ?></b></div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th># Phiếu</th>
            <th>Xe</th>
            <th>Biển số</th>
            <th>Khách hàng</th>
            <th>Số điện thoại</th>
            <th>Giá vốn</th>
            <th>Giá bán</th>
            <th>Lãi / Lỗ</th>
            <th>Thanh toán</th>
            <th>Ngày bán</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($soldRows as $row): ?>
            <?php
              $giaVon = isset($row['giavon']) && $row['giavon'] !== null
                  ? (float)$row['giavon']
                  : (float)($row['giamua_vao'] ?? 0) + (float)($row['tongchiphi_spa'] ?? 0);
              $giaBan = (float)($row['giaban_chot'] ?? 0);
              $laiLo = $giaBan - $giaVon;
            ?>
            <tr>
              <td><?= (int)$row['maphieuban']; ?></td>
              <td><?= e(trim(($row['hangxe'] ?? '') . ' ' . ($row['dongxe'] ?? ''))); ?></td>
              <td><?= e($row['bienso'] ?? ''); ?></td>
              <td><?= e($row['hoten'] ?? ''); ?></td>
              <td><?= e($row['sodienthoai'] ?? ''); ?></td>
              <td><?= money($giaVon); ?></td>
              <td><?= money($giaBan); ?></td>
              <td>
                <?php if ($laiLo >= 0): ?>
                  <span class="text-success fw-bold"><?= money($laiLo); ?></span>
                <?php else: ?>
                  <span class="text-danger fw-bold">-<?= money(abs($laiLo)); ?></span>
                <?php endif; ?>
              </td>
              <td><?= e($paymentLabels[$row['hinhthucthanhtoan']] ?? $row['hinhthucthanhtoan']); ?></td>
              <td><?= e($row['ngayban'] ?? ''); ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($soldRows)): ?>
            <tr>
              <td colspan="10">
                <div class="empty-state">
                  <div class="empty-icon"><i class="bi bi-bar-chart"></i></div>
                  <div>Không có xe đã bán trong tháng này</div>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

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
document.addEventListener('DOMContentLoaded', function () {
  const inventory = document.getElementById('inventoryChart');
  if (inventory) {
    new Chart(inventory, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
          data: <?= json_encode($chartValues) ?>,
          backgroundColor: ['#94a3b8', '#f59e0b', '#3b82f6', '#f97316', '#22c55e'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }

  const sales6 = document.getElementById('sales6MonthChart');
  if (sales6) {
    new Chart(sales6, {
      data: {
        labels: <?= json_encode($labels6) ?>,
        datasets: [
          {
            type: 'bar',
            label: 'Doanh thu',
            data: <?= json_encode($revenue6) ?>,
            backgroundColor: 'rgba(59,130,246,.85)',
            borderRadius: 12,
            yAxisID: 'y'
          },
          {
            type: 'line',
            label: 'Số xe bán',
            data: <?= json_encode($count6) ?>,
            borderColor: '#16a34a',
            backgroundColor: '#16a34a',
            tension: .35,
            fill: false,
            pointRadius: 4,
            pointHoverRadius: 5,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'bottom' } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (v) => formatMoneyText(v)
            }
          },
          y1: {
            beginAtZero: true,
            position: 'right',
            grid: { drawOnChartArea: false },
            ticks: { precision: 0 }
          }
        }
      }
    });
  }
});
</script>

<?php require_once __DIR__ . "/../partials/footer.php"; ?>
