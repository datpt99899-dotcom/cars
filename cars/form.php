<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$car = [
  'maxe' => 0,'bienso' => '','sokhung' => '','somay' => '','hangxe' => '','dongxe' => '',
  'namsanxuat' => date('Y'),'odo' => 0,'mausac' => '','xuatxu' => '','tinhtrang_phaply' => '',
  'trangthai' => 'NEW','giamua_vao' => 0,'tongchiphi_spa' => 0,'giavon' => 0,'giaban_dukien' => 0,
  'mota' => '','hinhanh' => '','ngaynhap' => date('Y-m-d'),
];
if ($id) {
  $stmt = $pdo->prepare("SELECT * FROM xe WHERE maxe = ?"); $stmt->execute([$id]);
  $row = $stmt->fetch(); if ($row) $car = $row; else { header('Location: ' . url('cars/index.php')); exit; }
}
$title = $id ? 'Cập nhật xe' : 'Thêm xe mới'; require_once __DIR__ . '/../partials/header.php';
?>
<div class="page-header">
  <div><h1 class="page-title"><?= $id ? 'Cập nhật xe' : 'Thêm xe mới' ?></h1><p class="page-subtitle">Nhập đầy đủ thông tin kỹ thuật, tài chính và hình ảnh của đầu xe.</p></div>
  <a class="btn btn-outline-secondary" href="<?= url('cars/index.php') ?>"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
</div>
<form method="post" action="<?= url('cars/save.php') ?>" class="card form-card" enctype="multipart/form-data">
  <input type="hidden" name="maxe" value="<?= (int)$car['maxe']; ?>">
  <div class="row g-4">
    <div class="col-xl-4">
      <div class="form-section h-100">
        <div class="form-section-title"><span class="dot"></span>Hình ảnh & trạng thái</div>
        <div id="imagePreview" class="preview-box mb-3"></div>
        <div class="mb-3"><label class="form-label">Tải ảnh xe</label><input class="form-control" type="file" name="hinhanh_file" accept="image/*"><div class="form-text">Chọn ảnh từ máy tính để tải lên hệ thống.</div></div>
        <div class="mb-3"><label class="form-label">Hình ảnh (URL hoặc tên file)</label><input class="form-control" name="hinhanh" value="<?= e($car['hinhanh']); ?>"><div class="form-text">Có thể để trống nếu đã tải ảnh ở trên.</div></div>
        <div class="row g-3"><div class="col-12"><label class="form-label">Trạng thái</label><select class="form-select" name="trangthai"><?php foreach (['NEW'=>'Mới nhập','SPA'=>'Đang spa','READY'=>'Sẵn sàng bán','DEPOSITED'=>'Đã cọc','SOLD'=>'Đã bán'] as $k=>$v): ?><option value="<?= $k ?>" <?= $car['trangthai']===$k?'selected':''; ?>><?= $v ?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label">Ngày nhập</label><input class="form-control" name="ngaynhap" type="date" value="<?= e($car['ngaynhap']); ?>"></div></div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="form-section h-100">
        <div class="form-section-title"><span class="dot"></span>Thông tin nhận diện</div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Biển số *</label><input class="form-control" name="bienso" required value="<?= e($car['bienso']); ?>"></div>
          <div class="col-md-6"><label class="form-label">Số khung (VIN) *</label><input class="form-control" name="sokhung" required value="<?= e($car['sokhung']); ?>"></div>
          <div class="col-md-6"><label class="form-label">Số máy</label><input class="form-control" name="somay" value="<?= e($car['somay']); ?>"></div>
          <div class="col-md-6"><label class="form-label">Hãng xe *</label><input class="form-control" name="hangxe" required value="<?= e($car['hangxe']); ?>"></div>
          <div class="col-md-6"><label class="form-label">Dòng xe *</label><input class="form-control" name="dongxe" required value="<?= e($car['dongxe']); ?>"></div>
          <div class="col-md-3"><label class="form-label">Năm SX *</label><input class="form-control" name="namsanxuat" type="number" required value="<?= e($car['namsanxuat']); ?>"></div>
          <div class="col-md-3"><label class="form-label">ODO</label><input class="form-control" name="odo" type="number" value="<?= e($car['odo']); ?>"></div>
          <div class="col-md-3"><label class="form-label">Màu sắc</label><input class="form-control" name="mausac" value="<?= e($car['mausac']); ?>"></div>
          <div class="col-md-3"><label class="form-label">Xuất xứ</label><input class="form-control" name="xuatxu" value="<?= e($car['xuatxu']); ?>"></div>
          <div class="col-12"><label class="form-label">Tình trạng pháp lý</label><input class="form-control" name="tinhtrang_phaply" value="<?= e($car['tinhtrang_phaply']); ?>"></div>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="form-section h-100">
        <div class="form-section-title"><span class="dot"></span>Tài chính & mô tả</div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Giá mua vào</label><input class="form-control" name="giamua_vao" type="number" step="0.01" value="<?= e($car['giamua_vao']); ?>"></div>
          <div class="col-md-6"><label class="form-label">Tổng chi phí spa</label><input class="form-control" name="tongchiphi_spa" type="number" step="0.01" value="<?= e($car['tongchiphi_spa']); ?>"></div>
          <div class="col-md-6"><label class="form-label">Giá vốn</label><input class="form-control" name="giavon" type="number" step="0.01" value="<?= e($car['giavon']); ?>" readonly><div class="form-text">Tự động tính = Giá mua + chi phí spa</div></div>
          <div class="col-md-6"><label class="form-label">Giá bán dự kiến</label><input class="form-control" name="giaban_dukien" type="number" step="0.01" value="<?= e($car['giaban_dukien']); ?>"></div>
          <div class="col-12"><label class="form-label">Mô tả</label><textarea class="form-control" name="mota" rows="7"><?= e($car['mota']); ?></textarea></div>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-4 d-flex gap-2"><button class="btn btn-primary"><i class="bi bi-floppy me-2"></i>Lưu dữ liệu</button><a class="btn btn-outline-secondary" href="<?= url('cars/index.php') ?>">Hủy</a></div>
</form>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>