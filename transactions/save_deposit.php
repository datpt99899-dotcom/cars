<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/helpers.php";

$manhanvien = get_current_employee_id($pdo);

$makhachhang = (int)($_POST['makhachhang'] ?? 0);
$maxe = (int)($_POST['maxe'] ?? 0);
$giaban_chot = (float)($_POST['giaban_chot'] ?? 0);
$tiencoc_ban = (float)($_POST['tiencoc_ban'] ?? 0);
$hinhthucthanhtoan = trim($_POST['hinhthucthanhtoan'] ?? 'cash');

if (!$manhanvien || !$makhachhang || !$maxe || $tiencoc_ban <= 0) {
  header('Location: ' . url('transactions/index.php'));
  exit;
}

$pdo->beginTransaction();
try {
  // Kiểm tra xe phải READY
  $stmt = $pdo->prepare("SELECT trangthai, giaban_dukien FROM xe WHERE maxe=? FOR UPDATE");
  $stmt->execute([$maxe]);
  $car = $stmt->fetch();
  if (!$car || $car['trangthai'] !== 'READY') {
    throw new Exception("Xe không ở trạng thái READY.");
  }

  if ($giaban_chot <= 0) {
    $giaban_chot = (float)($car['giaban_dukien'] ?? 0);
    if ($giaban_chot <= 0) $giaban_chot = 0.01; // tránh NOT NULL
  }

  // Tạo phiếu bán (DEPOSITED)
  $stmt = $pdo->prepare("INSERT INTO phieuban(maxe, makhachhang, manhanvien, giaban_chot, tiencoc_ban, hinhthucthanhtoan, trangthai_giaodich)
                         VALUES(?,?,?,?,?,?, 'DEPOSITED')");
  $stmt->execute([$maxe, $makhachhang, $manhanvien, $giaban_chot, $tiencoc_ban, $hinhthucthanhtoan]);
  $maphieuban = (int)$pdo->lastInsertId();

  // Cập nhật trạng thái xe + log
  $stmt = $pdo->prepare("UPDATE xe SET trangthai='DEPOSITED' WHERE maxe=?");
  $stmt->execute([$maxe]);

  $stmt = $pdo->prepare("INSERT INTO trangthaixe_log(maxe, trangthai_cu, trangthai_moi, manhanvien, ghichu)
                         VALUES(?, 'READY', 'DEPOSITED', ?, 'Đặt cọc')");
  $stmt->execute([$maxe, $manhanvien]);

  // Ghi nhận giao dịch tài chính THU tiền cọc (tham chiếu phiếu bán)
  $stmt = $pdo->prepare("INSERT INTO giaodichtaichinh(loaiphieu, sotien, noidung, hinhthucthanhtoan, manhanvien, maphieuban)
                         VALUES('THU', ?, 'Thu tiền cọc bán xe', ?, ?, ?)");
  $stmt->execute([$tiencoc_ban, $hinhthucthanhtoan, $manhanvien, $maphieuban]);

  $pdo->commit();
  flash_set('success', 'Tạo phiếu đặt cọc thành công.');
} catch (Exception $e) {
  $pdo->rollBack();
  // Có thể hiển thị lỗi đơn giản
}

header('Location: ' . url('transactions/index.php'));
exit;
