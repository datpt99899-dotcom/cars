<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/_invoice_helpers.php';

$maphieuban = (int)($_GET['id'] ?? 0);
$type = ($_GET['type'] ?? 'deposit') === 'sale' ? 'sale' : 'deposit';
$data = $maphieuban > 0 ? invoice_fetch_receipt($pdo, $maphieuban, $type) : null;
if (!$data) { http_response_code(404); exit('Không tìm thấy phiếu thu phù hợp.'); }
$sale = $data['sale'];
$tx = $data['tx'];
$company = invoice_company_info();
$docName = $type === 'sale' ? 'PHIẾU THU THANH TOÁN' : 'PHIẾU THU TIỀN CỌC';
$title = $docName . ' ' . $tx['magiaodich'];
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
<div class="invoice-sheet receipt-sheet">
  <div class="invoice-topbar"></div>
  <div class="invoice-header">
    <div>
      <div class="invoice-brand"><?= e($company['name']) ?></div>
      <div class="invoice-subbrand"><?= e($company['subtitle']) ?></div>
      <div class="invoice-meta-line"><i class="bi bi-geo-alt"></i> <?= e($company['address']) ?></div>
      <div class="invoice-meta-line"><i class="bi bi-telephone"></i> <?= e($company['phone']) ?></div>
    </div>
    <div class="invoice-code-box warning">
      <div class="invoice-doc-type"><?= e($docName) ?></div>
      <div class="invoice-doc-no">Mã phiếu thu: <?= (int)$tx['magiaodich'] ?></div>
      <div class="invoice-doc-date">Ngày thu: <?= e(date('d/m/Y', strtotime($tx['ngaygiaodich']))) ?></div>
    </div>
  </div>

  <div class="invoice-card mb-3">
    <div class="invoice-field"><span>Khách hàng nộp</span><strong><?= e($sale['tenkhach']) ?></strong></div>
    <div class="invoice-field"><span>Số điện thoại</span><strong><?= e($sale['sdtkhach']) ?></strong></div>
    <div class="invoice-field"><span>Nội dung thu</span><strong><?= e($tx['noidung']) ?></strong></div>
    <div class="invoice-field"><span>Liên quan giao dịch</span><strong>Mã <?= (int)$sale['maphieuban'] ?> • <?= e(trim($sale['hangxe'] . ' ' . $sale['dongxe'])) ?> / <?= e($sale['bienso']) ?></strong></div>
    <div class="invoice-field"><span>Hình thức thanh toán</span><strong><?= e(invoice_payment_label($tx['hinhthucthanhtoan'])) ?></strong></div>
  </div>

  <div class="receipt-amount-box">
    <div class="invoice-total-label">SỐ TIỀN THU</div>
    <div class="invoice-total-value"><?= money($tx['sotien']) ?></div>
    <div class="invoice-chip mt-2"><?= $type === 'sale' ? 'Phiếu thu phần thanh toán bán xe' : 'Phiếu thu tiền cọc giữ xe' ?></div>
  </div>

  <section class="invoice-card mt-3">
    <div class="invoice-section-title">Ghi chú</div>
    <div class="invoice-note">Phiếu thu này được lập để xác nhận showroom đã nhận khoản tiền từ khách hàng theo giao dịch mua bán xe. Khách hàng vui lòng kiểm tra thông tin trước khi ký xác nhận.</div>
  </section>

  <div class="invoice-signatures">
    <div class="sig-box"><div class="sig-title">Người nộp tiền</div><div class="sig-line"></div><div class="sig-note">Ký và ghi rõ họ tên</div></div>
    <div class="sig-box"><div class="sig-title">Người lập phiếu</div><div class="sig-line"></div><div class="sig-note">Ký và ghi rõ họ tên</div></div>
    <div class="sig-box"><div class="sig-title">Kế toán / Thủ quỹ</div><div class="sig-line"></div><div class="sig-note">Ký xác nhận</div></div>
  </div>
</div>
</body>
</html>
