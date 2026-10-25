<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/_invoice_helpers.php';

$maphieuban = (int)($_GET['id'] ?? 0);
$row = $maphieuban > 0 ? invoice_fetch_sale($pdo, $maphieuban) : null;
if (!$row) { http_response_code(404); exit('Không tìm thấy hóa đơn bán xe.'); }
$company = invoice_company_info();
$title = 'Hóa đơn bán xe ' . $maphieuban;
$remaining = max(0, (float)$row['giaban_chot'] - (float)$row['tiencoc_ban']);
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
    <div class="invoice-code-box success">
      <div class="invoice-doc-type">HÓA ĐƠN BÁN XE</div>
      <div class="invoice-doc-no">Mã hóa đơn: <?= (int)$row['maphieuban'] ?></div>
      <div class="invoice-doc-date">Ngày bán: <?= e(date('d/m/Y', strtotime($row['ngayban'] ?: $row['ngaytao']))) ?></div>
    </div>
  </div>

  <div class="invoice-grid two">
    <section class="invoice-card">
      <div class="invoice-section-title">Thông tin khách hàng</div>
      <div class="invoice-field"><span>Họ và tên</span><strong><?= e($row['tenkhach']) ?></strong></div>
      <div class="invoice-field"><span>Số điện thoại</span><strong><?= e($row['sdtkhach']) ?></strong></div>
      <div class="invoice-field"><span>Email</span><strong><?= e($row['emailkhach'] ?: 'Chưa cập nhật') ?></strong></div>
      <div class="invoice-field"><span>Địa chỉ</span><strong><?= e($row['diachikhach'] ?: 'Chưa cập nhật') ?></strong></div>
    </section>
    <section class="invoice-card accent">
      <div class="invoice-section-title">Tóm tắt thanh toán</div>
      <div class="invoice-field"><span>Nhân viên phụ trách</span><strong><?= e($row['tennhanvien'] ?: 'Hệ thống') ?></strong></div>
      <div class="invoice-field"><span>Hình thức thanh toán</span><strong><?= e(invoice_payment_label($row['hinhthucthanhtoan'])) ?></strong></div>
      <div class="invoice-field"><span>Tiền cọc trước đó</span><strong><?= money($row['tiencoc_ban']) ?></strong></div>
      <div class="invoice-field"><span>Còn lại cần thanh toán</span><strong class="invoice-money"><?= money($remaining) ?></strong></div>
    </section>
  </div>

  <section class="invoice-card mt-3">
    <div class="invoice-section-title">Thông tin xe bán ra</div>
    <table class="invoice-table">
      <thead>
        <tr>
          <th>Dòng xe</th><th>Biển số</th><th>Số khung</th><th>Số máy</th><th>Năm SX</th><th>Giá bán chốt</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= e(trim($row['hangxe'] . ' ' . $row['dongxe'])) ?></td>
          <td><?= e($row['bienso']) ?></td>
          <td><?= e($row['sokhung'] ?: '—') ?></td>
          <td><?= e($row['somay'] ?: '—') ?></td>
          <td><?= e($row['namsanxuat']) ?></td>
          <td><?= money($row['giaban_chot']) ?></td>
        </tr>
      </tbody>
    </table>
  </section>

  <div class="invoice-grid two mt-3">
    <section class="invoice-card">
      <div class="invoice-section-title">Nội dung bàn giao</div>
      <div class="invoice-note"><?= nl2br(e($row['noidungbangiao'] ?: 'Xe, chìa khóa, hồ sơ pháp lý và các giấy tờ liên quan được bàn giao theo thực tế giao dịch.')) ?></div>
    </section>
    <section class="invoice-card accent soft-success">
      <div class="invoice-section-title">Tổng giá trị hóa đơn</div>
      <div class="invoice-total-label mt-2">TỔNG THANH TOÁN</div>
      <div class="invoice-total-value success"><?= money($row['giaban_chot']) ?></div>
      <div class="invoice-note mt-3">Hóa đơn này là chứng từ xác nhận giao dịch bán xe hoàn tất giữa showroom và khách hàng.</div>
    </section>
  </div>

  <div class="invoice-signatures">
    <div class="sig-box"><div class="sig-title">Khách hàng</div><div class="sig-line"></div><div class="sig-note">Ký và ghi rõ họ tên</div></div>
    <div class="sig-box"><div class="sig-title">Nhân viên phụ trách</div><div class="sig-line"></div><div class="sig-note">Ký xác nhận</div></div>
    <div class="sig-box"><div class="sig-title">Đại diện showroom</div><div class="sig-line"></div><div class="sig-note">Đóng dấu / xác nhận</div></div>
  </div>
</div>
</body>
</html>
