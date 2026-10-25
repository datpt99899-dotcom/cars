<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_roles(['giamdoc','kithuat','ketoan','admin']);

$maphieuspa = (int)($_POST['maphieuspa'] ?? 0);
$maxe = (int)($_POST['maxe'] ?? 0);
$madoitac = (int)($_POST['madoitac'] ?? 0);
$garaMoi = trim($_POST['gara_tu_nhap'] ?? '');
$ngaybatdau = trim($_POST['ngaybatdau'] ?? date('Y-m-d'));
$ngayketthuc = trim($_POST['ngayketthuc_dukien'] ?? '');
$tongchiphi_dukien = (float)($_POST['tongchiphi_dukien'] ?? 0);
$trangthai = trim($_POST['trangthai'] ?? 'OPEN');
$ghichu = trim($_POST['ghichu'] ?? '');
$hangmucs = $_POST['hangmuc_congviec'] ?? [];
$costs = $_POST['chiphi_thucte'] ?? [];
$notes = $_POST['ghichu_ct'] ?? [];
$manhanvien = get_current_employee_id($pdo);

if ($maxe <= 0 || $manhanvien <= 0) {
    flash_set('error', 'Thiếu thông tin xe hoặc nhân viên thực hiện.');
    redirect('spa/index.php');
}
if ($madoitac <= 0) {
    $madoitac = ensure_default_garage($pdo, $garaMoi !== '' ? $garaMoi : 'Gara liên kết');
}

$detailRows = [];
for ($i=0; $i<count($hangmucs); $i++) {
    $name = trim((string)($hangmucs[$i] ?? ''));
    $cost = (float)($costs[$i] ?? 0);
    $note = trim((string)($notes[$i] ?? ''));
    if ($name === '') continue;
    $detailRows[] = ['name'=>$name, 'cost'=>$cost, 'note'=>$note];
}
if (empty($detailRows)) {
    flash_set('error', 'Phiếu Spa phải có ít nhất 1 hạng mục.');
    redirect($maphieuspa ? 'spa/form.php?id='.$maphieuspa : 'spa/form.php?maxe='.$maxe);
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT trangthai FROM xe WHERE maxe=? FOR UPDATE');
    $stmt->execute([$maxe]);
    $xeTrangThai = (string)($stmt->fetchColumn() ?: '');
    if (in_array($xeTrangThai, ['SOLD','DEPOSITED'], true) && $maphieuspa <= 0) {
        throw new Exception('Xe này đang ở trạng thái không thể mở phiếu Spa mới.');
    }

    if ($maphieuspa > 0) {
        $stmt = $pdo->prepare("UPDATE phieuspa SET maxe=?, madoitac=?, manhanvien=?, ngaybatdau=?, ngayketthuc_dukien=?, tongchiphi_dukien=?, trangthai=?, ghichu=? WHERE maphieuspa=?");
        $stmt->execute([$maxe, $madoitac, $manhanvien, $ngaybatdau, $ngayketthuc !== '' ? $ngayketthuc : null, $tongchiphi_dukien, $trangthai, $ghichu, $maphieuspa]);
        $stmt = $pdo->prepare('DELETE FROM chitietspa WHERE maphieuspa=?');
        $stmt->execute([$maphieuspa]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO phieuspa(maxe, madoitac, manhanvien, ngaybatdau, ngayketthuc_dukien, tongchiphi_dukien, trangthai, ghichu) VALUES(?,?,?,?,?,?,?,?)");
        $stmt->execute([$maxe, $madoitac, $manhanvien, $ngaybatdau, $ngayketthuc !== '' ? $ngayketthuc : null, $tongchiphi_dukien, $trangthai, $ghichu]);
        $maphieuspa = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('INSERT INTO chitietspa(maphieuspa, hangmuc_congviec, chiphi_thucte, ghichu) VALUES(?,?,?,?)');
    foreach ($detailRows as $row) {
        $stmt->execute([$maphieuspa, $row['name'], $row['cost'], $row['note']]);
    }

    recalc_car_spa_cost($pdo, $maxe);
    sync_car_status_from_spa($pdo, $maxe);

    $totalActual = array_sum(array_map(fn($x)=>(float)$x['cost'], $detailRows));
    $stmt = $pdo->prepare("DELETE FROM giaodichtaichinh WHERE maphieuspa=? AND loaiphieu='CHI'");
    $stmt->execute([$maphieuspa]);
    $stmt = $pdo->prepare("INSERT INTO giaodichtaichinh(loaiphieu, sotien, noidung, hinhthucthanhtoan, manhanvien, maphieuspa)
                           VALUES('CHI', ?, ?, 'bank', ?, ?)");
    $stmt->execute([$totalActual, 'Chi phí Spa xe', $manhanvien, $maphieuspa]);

    $pdo->commit();
    flash_set('success', 'Đã lưu phiếu Spa thành công.');
} catch (Exception $e) {
    $pdo->rollBack();
    flash_set('error', 'Không thể lưu phiếu Spa: ' . $e->getMessage());
}

redirect('spa/index.php');
