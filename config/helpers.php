<?php
// Helpers dùng chung cho toàn bộ project.
// Mục tiêu: Tự nhận diện đúng BASE_URL dù bạn đặt project ở /car/ hay /car_showroom/car/ ...

if (!defined('BASE_URL')) {
    $projectRoot = realpath(dirname(__DIR__));
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;

    $base = '';
    if ($docRoot && $projectRoot && strpos($projectRoot, $docRoot) === 0) {
        $rel = substr($projectRoot, strlen($docRoot));
        $rel = str_replace('\\', '/', $rel);
        if ($rel === '') {
            $base = '';
        } else {
            $base = '/' . ltrim($rel, '/');
            $base = rtrim($base, '/');
        }
    } else {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($scriptName));
        $base = $dir === '/' ? '' : rtrim($dir, '/');
    }

    define('BASE_URL', $base);
}

function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return (BASE_URL === '' ? '' : BASE_URL) . '/' . $path;
}

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function flash_set(string $key, string $msg): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['_flash'][$key] = $msg;
}

function flash_get(string $key): ?string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['_flash'][$key])) return null;
    $msg = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function money($amount): string {
    $amount = (float)($amount ?? 0);
    $sign = $amount < 0 ? '-' : '';
    $value = abs((int)round($amount));

    if ($value === 0) return '0';

    $ty = intdiv($value, 1000000000);
    $trieu = intdiv($value % 1000000000, 1000000);
    $nghin = intdiv($value % 1000000, 1000);

    if ($ty > 0) {
        if ($trieu > 0) return $sign . $ty . ' tỉ ' . $trieu . ' triệu';
        return $sign . $ty . ' tỉ';
    }

    if ($trieu > 0) {
        return $sign . $trieu . ' triệu';
    }

    if ($nghin > 0) {
        return $sign . $nghin . ' nghìn';
    }

    return $sign . number_format($value, 0, ',', '.');
}

function current_user(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['user'] ?? null;
}

function current_role(): string {
    $user = current_user();
    return (string)($user['role'] ?? '');
}

function has_role($roles): bool {
    $role = current_role();
    $roles = is_array($roles) ? $roles : [$roles];
    return in_array($role, $roles, true);
}

function require_roles($roles): void {
    if (!has_role($roles)) {
        flash_set('error', 'Bạn không có quyền truy cập chức năng này.');
        redirect('index.php');
    }
}

function role_label(string $role): string {
    $labels = [
        'giamdoc' => 'Tổng Giám đốc',
        'sales' => 'Sales',
        'thumua' => 'Thu mua',
        'kithuat' => 'Kỹ thuật',
        'ketoan' => 'Kế toán',
        'admin' => 'Quản trị',
        'staff' => 'Nhân viên',
    ];
    return $labels[$role] ?? $role;
}

function get_current_employee_id(PDO $pdo): int {
    $user = current_user();
    $accountId = (int)($user['id'] ?? 0);
    if ($accountId <= 0) return 0;
    if (!empty($user['employee_id'])) return (int)$user['employee_id'];

    $stmt = $pdo->prepare('SELECT manhanvien FROM nhanvien WHERE mataikhoan=? LIMIT 1');
    $stmt->execute([$accountId]);
    $employeeId = (int)($stmt->fetchColumn() ?: 0);

    if ($employeeId > 0 && session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['user']['employee_id'] = $employeeId;
    }
    return $employeeId;
}

function ensure_default_garage(PDO $pdo, string $name = 'Gara liên kết'): int {
    $name = trim($name) !== '' ? trim($name) : 'Gara liên kết';
    $stmt = $pdo->prepare("SELECT madoitac FROM doitac WHERE loai_doitac='gara' AND tendoitac=? LIMIT 1");
    $stmt->execute([$name]);
    $id = (int)($stmt->fetchColumn() ?: 0);
    if ($id > 0) return $id;

    $stmt = $pdo->prepare("INSERT INTO doitac(tendoitac, loai_doitac, ghichu) VALUES(?, 'gara', 'Tạo tự động từ chức năng Spa xe')");
    $stmt->execute([$name]);
    return (int)$pdo->lastInsertId();
}

function recalc_car_spa_cost(PDO $pdo, int $maxe): void {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(c.chiphi_thucte),0)
                           FROM phieuspa p
                           JOIN chitietspa c ON c.maphieuspa = p.maphieuspa
                           WHERE p.maxe=? AND p.trangthai <> 'CANCEL'");
    $stmt->execute([$maxe]);
    $totalSpa = (float)($stmt->fetchColumn() ?: 0);

    $stmt = $pdo->prepare("UPDATE xe SET tongchiphi_spa=?, giavon=giamua_vao + ? WHERE maxe=?");
    $stmt->execute([$totalSpa, $totalSpa, $maxe]);
}

function sync_car_status_from_spa(PDO $pdo, int $maxe): void {
    $stmt = $pdo->prepare('SELECT trangthai FROM xe WHERE maxe=? LIMIT 1');
    $stmt->execute([$maxe]);
    $current = (string)($stmt->fetchColumn() ?: '');
    if (in_array($current, ['SOLD', 'DEPOSITED'], true)) return;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM phieuspa WHERE maxe=? AND trangthai IN ('OPEN','IN_PROGRESS')");
    $stmt->execute([$maxe]);
    $openSpa = (int)$stmt->fetchColumn();

    $newStatus = $openSpa > 0 ? 'SPA' : 'READY';
    $stmt = $pdo->prepare('UPDATE xe SET trangthai=? WHERE maxe=?');
    $stmt->execute([$newStatus, $maxe]);
}


function resolve_image_path(?string $path): string {
    $path = trim((string)$path);
    if ($path === '') return '';
    if (preg_match('~^https?://~i', $path)) return $path;
    if (strpos($path, '/') === 0) return $path;
    return url(ltrim($path, '/'));
}

function fallback_car_images(): array {
    return [
        'vinfast' => url('uploads/cars/vf3.jpg'),
        'kia' => url('uploads/cars/morning.jpg'),
        'suzuki' => url('uploads/cars/carry.jpg'),
        'hyundai' => url('uploads/cars/elantra.jpg'),
        'ford' => url('uploads/cars/everest.jpg'),
        'default_0' => url('uploads/cars/vf3.jpg'),
        'default_1' => url('uploads/cars/morning.jpg'),
        'default_2' => url('uploads/cars/carry.jpg'),
        'default_3' => url('uploads/cars/elantra.jpg'),
        'default_4' => url('uploads/cars/everest.jpg'),
        'default_5' => url('uploads/cars/vf3b.jpg'),
        'default_6' => url('uploads/cars/morningb.jpg'),
        'default_7' => url('uploads/cars/carryb.jpg'),
        'default_8' => url('uploads/cars/elantrab.jpg'),
        'default_9' => url('uploads/cars/everestb.jpg'),
    ];
}

function get_car_image(array $car, int $index = 0): string {
    $direct = resolve_image_path($car['hinhanh'] ?? '');
    if ($direct !== '') return $direct;
    $pool = fallback_car_images();
    $haystack = mb_strtolower(trim(($car['hangxe'] ?? '') . ' ' . ($car['dongxe'] ?? '')));
    foreach (['vinfast','kia','suzuki','hyundai','ford'] as $key) {
        if ($haystack !== '' && strpos($haystack, $key) !== false) return $pool[$key];
    }
    $pick = 'default_' . (((int)($car['maxe'] ?? $index) + $index) % 10);
    return $pool[$pick] ?? $pool['default_0'];
}

function car_title(array $car): string {
    $year = trim((string)($car['namsanxuat'] ?? ''));
    $name = trim(($car['hangxe'] ?? '') . ' ' . ($car['dongxe'] ?? ''));
    return trim(($year !== '' ? $year . ' - ' : '') . $name);
}

function short_text(?string $text, int $limit = 80): string {
    $text = trim((string)$text);
    if ($text === '') return 'Xe đẹp, hồ sơ rõ ràng, sẵn sàng kiểm tra và giao dịch.';
    if (mb_strlen($text) <= $limit) return $text;
    return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
}
