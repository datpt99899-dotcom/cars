<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/helpers.php";

$maxe = (int)($_POST['maxe'] ?? 0);

$bienso = trim($_POST['bienso'] ?? "");
$sokhung = trim($_POST['sokhung'] ?? "");
$somay = trim($_POST['somay'] ?? "");
$hangxe = trim($_POST['hangxe'] ?? "");
$dongxe = trim($_POST['dongxe'] ?? "");
$namsanxuat = (int)($_POST['namsanxuat'] ?? date('Y'));
$odo = (int)($_POST['odo'] ?? 0);
$ngaynhap = $_POST['ngaynhap'] ?? null;

$mausac = trim($_POST['mausac'] ?? "");
$xuatxu = trim($_POST['xuatxu'] ?? "");
$tinhtrang_phaply = trim($_POST['tinhtrang_phaply'] ?? "");
$trangthai = trim($_POST['trangthai'] ?? "NEW");

$giamua_vao = (float)($_POST['giamua_vao'] ?? 0);
$tongchiphi_spa = (float)($_POST['tongchiphi_spa'] ?? 0);
$giavon = $giamua_vao + $tongchiphi_spa;
$giaban_dukien = (float)($_POST['giaban_dukien'] ?? 0);

$mota = trim($_POST['mota'] ?? "");
$hinhanh = trim($_POST['hinhanh'] ?? "");
$hinhanhUpload = $_FILES['hinhanh_file'] ?? null;
if ($hinhanhUpload && (int)($hinhanhUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
  if ((int)$hinhanhUpload['error'] !== UPLOAD_ERR_OK) {
    flash_set('error', 'Tải ảnh lên thất bại. Vui lòng thử lại.');
    header('Location: ' . url('cars/form.php' . ($maxe > 0 ? '?id=' . $maxe : '')));
    exit;
  }
  $tmp = (string)($hinhanhUpload['tmp_name'] ?? '');
  $size = (int)($hinhanhUpload['size'] ?? 0);
  $original = (string)($hinhanhUpload['name'] ?? '');
  $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
  $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
  if (!in_array($ext, $allowed, true)) {
    flash_set('error', 'Ảnh xe chỉ hỗ trợ JPG, PNG, WEBP hoặc GIF.');
    header('Location: ' . url('cars/form.php' . ($maxe > 0 ? '?id=' . $maxe : '')));
    exit;
  }
  if ($size > 8 * 1024 * 1024) {
    flash_set('error', 'Ảnh xe vượt quá giới hạn 8MB.');
    header('Location: ' . url('cars/form.php' . ($maxe > 0 ? '?id=' . $maxe : '')));
    exit;
  }
  $uploadDir = dirname(__DIR__) . '/uploads/cars';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
  $fileName = 'car_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $destination = $uploadDir . '/' . $fileName;
  if (!move_uploaded_file($tmp, $destination)) {
    flash_set('error', 'Không thể lưu ảnh xe lên máy chủ.');
    header('Location: ' . url('cars/form.php' . ($maxe > 0 ? '?id=' . $maxe : '')));
    exit;
  }
  $hinhanh = 'uploads/cars/' . $fileName;
}

if ($maxe > 0) {
  $stmt = $pdo->prepare("UPDATE xe SET
    bienso=?, sokhung=?, somay=?, hangxe=?, dongxe=?, namsanxuat=?, odo=?, mausac=?, xuatxu=?, tinhtrang_phaply=?,
    trangthai=?, giamua_vao=?, tongchiphi_spa=?, giavon=?, giaban_dukien=?, mota=?, hinhanh=?, ngaynhap=?
    WHERE maxe=?");
  $stmt->execute([
    $bienso, $sokhung, $somay ?: null, $hangxe, $dongxe, $namsanxuat, $odo, $mausac ?: null, $xuatxu ?: null, $tinhtrang_phaply ?: null,
    $trangthai, $giamua_vao, $tongchiphi_spa, $giavon, $giaban_dukien ?: null, $mota ?: null, $hinhanh ?: null, $ngaynhap ?: null,
    $maxe
  ]);
} else {
  $stmt = $pdo->prepare("INSERT INTO xe(
    bienso, sokhung, somay, hangxe, dongxe, namsanxuat, odo, mausac, xuatxu, tinhtrang_phaply,
    trangthai, giamua_vao, tongchiphi_spa, giavon, giaban_dukien, mota, hinhanh, ngaynhap
  ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $stmt->execute([
    $bienso, $sokhung, $somay ?: null, $hangxe, $dongxe, $namsanxuat, $odo, $mausac ?: null, $xuatxu ?: null, $tinhtrang_phaply ?: null,
    $trangthai, $giamua_vao, $tongchiphi_spa, $giavon, $giaban_dukien ?: null, $mota ?: null, $hinhanh ?: null, $ngaynhap ?: null
  ]);
}

flash_set('success', $maxe > 0 ? 'Cập nhật xe thành công.' : 'Thêm xe mới thành công.');
header('Location: ' . url('cars/index.php'));
exit;
