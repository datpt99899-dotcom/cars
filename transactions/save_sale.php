<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/helpers.php";

$manhanvien = get_current_employee_id($pdo);

$maphieuban = (int)($_POST['maphieuban'] ?? 0);
$giaban_chot = (float)($_POST['giaban_chot'] ?? 0);
$hinhthucthanhtoan = trim($_POST['hinhthucthanhtoan'] ?? 'cash');
$trangthaivay = trim($_POST['trangthaivay'] ?? '');
$noidungbangiao = trim($_POST['noidungbangiao'] ?? '');

if (!$manhanvien || !$maphieuban || $giaban_chot <= 0) {
  header('Location: ' . url('transactions/index.php'));
  exit;
}

$pdo->beginTransaction();
try {
  // Lock phiếu bán
  $stmt = $pdo->prepare("SELECT * FROM phieuban WHERE maphieuban=? FOR UPDATE");
  $stmt->execute([$maphieuban]);
  $p = $stmt->fetch();
  if (!$p || $p['trangthai_giaodich'] !== 'DEPOSITED') {
    throw new Exception("Phiếu bán không hợp lệ.");
  }
  $maxe = (int)$p['maxe'];

  // Lock xe
  $stmt = $pdo->prepare("SELECT trangthai FROM xe WHERE maxe=? FOR UPDATE");
  $stmt->execute([$maxe]);
  $car = $stmt->fetch();
  if (!$car || $car['trangthai'] !== 'DEPOSITED') {
    throw new Exception("Xe không ở trạng thái DEPOSITED.");
  }

  // Update phiếu bán
$stmt = $pdo->prepare("UPDATE phieuban 
  SET giaban_chot=?, hinhthucthanhtoan=?, trangthaivay=?, trangthai_giaodich='SOLD', ngayban=NOW()
  WHERE maphieuban=?");
$stmt->execute([$giaban_chot, $hinhthucthanhtoan, $trangthaivay ?: null, $maphieuban]);

  // Update xe status + log
  $stmt = $pdo->prepare("UPDATE xe SET trangthai='SOLD' WHERE maxe=?");
  $stmt->execute([$maxe]);

  $stmt = $pdo->prepare("INSERT INTO trangthaixe_log(maxe, trangthai_cu, trangthai_moi, manhanvien, ghichu)
                         VALUES(?, 'DEPOSITED', 'SOLD', ?, 'Xác nhận bán')");
  $stmt->execute([$maxe, $manhanvien]);

  // Giao dịch tài chính THU (ghi nhận tổng tiền bán để báo cáo đơn giản)
  $stmt = $pdo->prepare("INSERT INTO giaodichtaichinh(loaiphieu, sotien, noidung, hinhthucthanhtoan, manhanvien, maphieuban)
                         VALUES('THU', ?, 'Thu tiền bán xe', ?, ?, ?)");
  $stmt->execute([$giaban_chot, $hinhthucthanhtoan, $manhanvien, $maphieuban]);

  // Bàn giao xe (tùy chọn)
  if ($noidungbangiao !== '') {
    $stmt = $pdo->prepare("INSERT INTO bangiaoxe(maphieuban, noidungbangiao) VALUES(?, ?)");
    $stmt->execute([$maphieuban, $noidungbangiao]);
  }

  $pdo->commit();
  flash_set('success', 'Xác nhận bán xe thành công.');
} catch (Exception $e) {
  $pdo->rollBack();
}

header('Location: ' . url('transactions/index.php'));
exit;
