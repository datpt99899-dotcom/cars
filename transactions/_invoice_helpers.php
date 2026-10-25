<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

function invoice_payment_label(string $method): string {
    return [
        'cash' => 'Tiền mặt',
        'bank' => 'Chuyển khoản',
        'loan' => 'Trả góp / vay ngân hàng',
    ][$method] ?? $method;
}

function invoice_company_info(): array {
    return [
        'name' => 'THỦ ĐÔ CAR',
        'subtitle' => 'Showroom ô tô đã qua sử dụng',
        'address' => '11 Hoàng Quán Chi, Dịch Vọng, Cầu Giấy, Hà Nội',
        'phone' => '0818 060 461',
        'email' => 'thudocar@gmail.com',
    ];
}

function invoice_fetch_sale(PDO $pdo, int $maphieuban): ?array {
    $sql = "SELECT p.*, 
                   x.hangxe, x.dongxe, x.bienso, x.namsanxuat, x.mausac, x.odo, x.sokhung, x.somay,
                   k.hoten AS tenkhach, k.sodienthoai AS sdtkhach, k.email AS emailkhach, k.diachi AS diachikhach, k.socccd,
                   n.hoten AS tennhanvien,
                   b.ngaybangiao, b.noidungbangiao
            FROM phieuban p
            JOIN xe x ON x.maxe = p.maxe
            JOIN khachhang k ON k.makhachhang = p.makhachhang
            LEFT JOIN nhanvien n ON n.manhanvien = p.manhanvien
            LEFT JOIN bangiaoxe b ON b.maphieuban = p.maphieuban
            WHERE p.maphieuban = ?
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$maphieuban]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function invoice_fetch_receipt(PDO $pdo, int $maphieuban, string $type): ?array {
    $sale = invoice_fetch_sale($pdo, $maphieuban);
    if (!$sale) return null;

    $keyword = $type === 'sale' ? 'Thu tiền bán xe' : 'Thu tiền cọc bán xe';
    $stmt = $pdo->prepare("SELECT * FROM giaodichtaichinh WHERE maphieuban = ? AND noidung = ? ORDER BY magiaodich DESC LIMIT 1");
    $stmt->execute([$maphieuban, $keyword]);
    $tx = $stmt->fetch();
    if (!$tx) return null;

    return ['sale' => $sale, 'tx' => $tx, 'type' => $type];
}
