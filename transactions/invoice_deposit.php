<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/_invoice_helpers.php';

$maphieuban = (int)($_GET['id'] ?? 0);
$row = $maphieuban > 0 ? invoice_fetch_sale($pdo, $maphieuban) : null;
if (!$row) { http_response_code(404); exit('Không tìm thấy phiếu đặt cọc.'); }
$company = invoice_company_info();
$title = 'Phiếu đặt cọc ' . $maphieuban;
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= url('assets/style.css') ?>" rel="stylesheet">
</head>
<body class="invoice-page-bg">
<div class="invoice-toolbar no-print">
  <a href="<?= url('transactions/index.php') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
  <button type="button" class="btn btn-primary" onclick="window.print()"><i class="bi bi-file-earmark-pdf"></i> Xuất PDF / In</button>
</div>
<div class="invoice-sheet">
  <div class="invoice-topbar"></div>
  <div class="invoice-header">
    <div>
      <div class="invoice-brand"><?= e($company['name']) ?></div>
      <div class="invoice-subbrand"><?= e($company['subtitle']) ?></div>
      <div class="invoice-meta-line"><i class="bi bi-geo-alt"></i> <?= e($company['address']) ?></div>
      <div class="invoice-meta-line"><i class="bi bi-telephone"></i> <?= e($company['phone']) ?> &nbsp; <i class="bi bi-envelope ms-2"></i> <?= e($company['email']) ?></div>
    </div>
    <div class="invoice-code-box">
      <div class="invoice-doc-type">PHIẾU ĐẶT CỌC</div>
      <div class="invoice-doc-no">Mã phiếu: <?= (int)$row['maphieuban'] ?></div>
      <div class="invoice-doc-date">Ngày lập: <?= e(date('d/m/Y', strtotime($row['ngaytao']))) ?></div>
    </div>
  </div>

  <div class="invoice-grid two">
    <section class="invoice-card">
      <div class="invoice-section-title">Thông tin khách hàng</div>
      <div class="invoice-field"><span>Họ và tên</span><strong><?= e($row['tenkhach']) ?></strong></div>
      <div class="invoice-field"><span>Số điện thoại</span><strong><?= e($row['sdtkhach']) ?></strong></div>
      <div class="invoice-field"><span>CCCD</span><strong><?= e($row['socccd'] ?: 'Chưa cập nhật') ?></strong></div>
      <div class="invoice-field"><span>Địa chỉ</span><strong><?= e($row['diachikhach'] ?: 'Chưa cập nhật') ?></strong></div>
    </section>
    <section class="invoice-card accent">
      <div class="invoice-section-title">Thông tin giao dịch</div>
      <div class="invoice-field"><span>Nhân viên lập</span><strong><?= e($row['tennhanvien'] ?: 'Hệ thống') ?></strong></div>
      <div class="invoice-field"><span>Hình thức thanh toán</span><strong><?= e(invoice_payment_label($row['hinhthucthanhtoan'])) ?></strong></div>
      <div class="invoice-field"><span>Trạng thái phiếu</span><strong>Đã đặt cọc</strong></div>
      <div class="invoice-field"><span>Số tiền đặt cọc</span><strong class="invoice-money"><?= money($row['tiencoc_ban']) ?></strong></div>
    </section>
  </div>

  <section class="invoice-card mt-3">
    <div class="invoice-section-title">Thông tin xe đặt cọc</div>
    <table class="invoice-table">
      <thead>
        <tr>
          <th>Dòng xe</th><th>Biển số</th><th>Năm SX</th><th>Màu sắc</th><th>ODO</th><th>Giá chốt dự kiến</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= e(trim($row['hangxe'] . ' ' . $row['dongxe'])) ?></td>
          <td><?= e($row['bienso']) ?></td>
          <td><?= e($row['namsanxuat']) ?></td>
          <td><?= e($row['mausac'] ?: '—') ?></td>
          <td><?= number_format((float)$row['odo'], 0, ',', '.') ?> km</td>
          <td><?= money($row['giaban_chot']) ?></td>
        </tr>
      </tbody>
    </table>
    <div class="invoice-note mt-3">
      Phiếu này xác nhận khách hàng đã đặt cọc để giữ xe. Hai bên tiếp tục hoàn tất các thủ tục mua bán và thanh toán theo thỏa thuận của showroom.
    </div>
  </section>

  <div class="invoice-total-box">
    <div>
      <div class="invoice-total-label">TỔNG TIỀN ĐẶT CỌC</div>
      <div class="invoice-total-value"><?= money($row['tiencoc_ban']) ?></div>
    </div>
    <div class="invoice-chip">Phiếu giao dịch đặt cọc</div>
  </div>

  <div class="invoice-signatures">
    <div class="sig-box"><div class="sig-title">Khách hàng</div><div class="sig-line"></div><div class="sig-note">Ký và ghi rõ họ tên</div></div>
    <div class="sig-box"><div class="sig-title">Nhân viên lập phiếu</div><div class="sig-line"></div><div class="sig-note">Ký và ghi rõ họ tên</div></div>
    <div class="sig-box"><div class="sig-title">Đại diện showroom</div><div class="sig-line"></div><div class="sig-note">Ký xác nhận</div></div>
  </div>
</div>
</body>
</html>
