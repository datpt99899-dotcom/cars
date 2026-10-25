<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/helpers.php";

$id = (int)($_POST['makhachhang'] ?? 0);

$hoten = trim($_POST['hoten'] ?? "");
$sodienthoai = trim($_POST['sodienthoai'] ?? "");
$socccd = trim($_POST['socccd'] ?? "");
$email = trim($_POST['email'] ?? "");
$diachi = trim($_POST['diachi'] ?? "");
$nguonkhach = trim($_POST['nguonkhach'] ?? "");
$nhucau = trim($_POST['nhucau'] ?? "");
$ghichu = trim($_POST['ghichu'] ?? "");

if ($id > 0) {
  $stmt = $pdo->prepare("UPDATE khachhang SET hoten=?, socccd=?, sodienthoai=?, email=?, diachi=?, nguonkhach=?, nhucau=?, ghichu=? WHERE makhachhang=?");
  $stmt->execute([$hoten, $socccd ?: null, $sodienthoai, $email ?: null, $diachi ?: null, $nguonkhach ?: null, $nhucau ?: null, $ghichu ?: null, $id]);
} else {
  $stmt = $pdo->prepare("INSERT INTO khachhang(hoten, socccd, sodienthoai, email, diachi, nguonkhach, nhucau, ghichu) VALUES(?,?,?,?,?,?,?,?)");
  $stmt->execute([$hoten, $socccd ?: null, $sodienthoai, $email ?: null, $diachi ?: null, $nguonkhach ?: null, $nhucau ?: null, $ghichu ?: null]);
}

flash_set('success', $id > 0 ? 'Cập nhật khách hàng thành công.' : 'Thêm khách hàng thành công.');
header('Location: ' . url('customers/index.php'));
exit;
