<?php
// Cấu hình DB: sửa $port nếu MySQL của bạn chạy 3307 (phổ biến khi XAMPP đổi port)
$host = "127.0.0.1";
$port = 3307;
$db   = "car_showroom";
$user = "root";
$pass = "";

try {
  $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Exception $e) {
  die("DB connection failed: " . $e->getMessage());
}
