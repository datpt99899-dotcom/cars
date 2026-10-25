<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/helpers.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
  $stmt = $pdo->prepare("DELETE FROM khachhang WHERE makhachhang = ?");
  $stmt->execute([$id]);
}
flash_set('info', 'Đã xóa khách hàng.');
header('Location: ' . url('customers/index.php'));
exit;
