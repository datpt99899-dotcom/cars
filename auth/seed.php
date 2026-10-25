<?php
require_once __DIR__ . "/../config/db.php";

$username = "admin";
$password = "123456";
$hash = password_hash($password, PASSWORD_BCRYPT);

$pdo->beginTransaction();
try {
  // Tạo tài khoản admin nếu chưa có
  $stmt = $pdo->prepare("SELECT mataikhoan FROM taikhoan WHERE tendangnhap = ?");
  $stmt->execute([$username]);
  $row = $stmt->fetch();

  if (!$row) {
    $stmt = $pdo->prepare("INSERT INTO taikhoan(tendangnhap, matkhau, phanquyen, trangthai) VALUES(?,?, 'admin', 1)");
    $stmt->execute([$username, $hash]);
    $mataikhoan = (int)$pdo->lastInsertId();
  } else {
    $mataikhoan = (int)$row['mataikhoan'];
  }

  // Tạo nhân viên gắn với tài khoản (1:1) nếu chưa có
  $stmt = $pdo->prepare("SELECT 1 FROM nhanvien WHERE mataikhoan = ?");
  $stmt->execute([$mataikhoan]);
  if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO nhanvien(hoten, chucvu, mataikhoan) VALUES('Quản trị viên', 'Admin', ?)");
    $stmt->execute([$mataikhoan]);
  }

  $pdo->commit();
  echo "Seed OK. Login: admin / 123456";
} catch (Exception $e) {
  $pdo->rollBack();
  echo "Seed failed: " . $e->getMessage();
}
