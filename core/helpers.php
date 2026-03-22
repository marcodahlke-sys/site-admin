<?php
declare(strict_types=1);

use App\Core\App;

function app(): App
{
    return App::getInstance();
}

function config(string $key, mixed $default = null): mixed
{
    return app()->config()->get($key, $default);
}

function db(): PDO
{
    return app()->db()->pdo();
}

function session_get(string $key, mixed $default = null): mixed
{
    return app()->session()->get($key, $default);
}

function flash(string $key, mixed $default = null): mixed
{
    return app()->session()->getFlash($key, $default);
}

function old(string $key, mixed $default = ''): mixed
{
    $old = session_get('_old', []);
    return $old[$key] ?? $default;
}

function with_old(array $data): void
{
    app()->session()->put('_old', $data);
}

function clear_old(): void
{
    app()->session()->forget('_old');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    $token = session_get('_csrf_token');
    if (!$token) {
        $token = bin2hex(random_bytes(32));
        app()->session()->put('_csrf_token', $token);
    }
    return $token;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_token'] ?? '';
    $sessionToken = session_get('_csrf_token', '');
    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        http_response_code(419);
        exit('Ungültiges Formular-Token.');
    }
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function request_path(): string
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
}

function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function query(string $key, mixed $default = null): mixed
{
    return $_GET[$key] ?? $default;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function now(): int
{
    return time();
}

function format_bytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    $value = max($bytes, 0);
    while ($value >= 1024 && $i < count($units) - 1) {
        $value /= 1024;
        $i++;
    }
    return number_format($value, $i === 0 ? 0 : 2, ',', '.') . ' ' . $units[$i];
}

function client_ip(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $value = explode(',', (string) $_SERVER[$key])[0];
            $ip = trim($value);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

function ensure_directory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

function uploads_path(string $file = ''): string
{
    return BASE_PATH . '/uploads/' . ltrim($file, '/');
}

function thumbs_path(string $file = ''): string
{
    return BASE_PATH . '/thumbs/' . ltrim($file, '/');
}

function image_url(string $file): string
{
    return '/uploads/' . rawurlencode($file);
}

function thumb_url(string $file): string
{
    return '/thumbs/' . rawurlencode($file);
}

function create_thumbnail_if_needed(string $filename): void
{
    $source = uploads_path($filename);
    $target = thumbs_path($filename);

    if (!is_file($source)) {
        return;
    }

    ensure_directory(dirname($target));

    if (is_file($target)) {
        return;
    }

    $info = @getimagesize($source);
    if (!$info || ($info[2] ?? null) !== IMAGETYPE_JPEG) {
        return;
    }

    [$srcWidth, $srcHeight] = $info;
    if ($srcWidth <= 0 || $srcHeight <= 0) {
        return;
    }

    $targetWidth = 500;
    $ratio = $targetWidth / $srcWidth;
    $targetHeight = (int) round($srcHeight * $ratio);

    $src = @imagecreatefromjpeg($source);
    if (!$src) {
        return;
    }

    $dst = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);
    imagejpeg($dst, $target, 85);

    imagedestroy($src);
    imagedestroy($dst);
}

function german_month_name(int $month): string
{
    $names = [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember',
    ];

    return $names[$month] ?? '';
}

function weekday_short_names(): array
{
    return ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
}

function category_field_map(): array
{
    return [
        1 => 'k1',
        3 => 'k3',
        4 => 'k4',
        7 => 'k7',
        9 => 'k9',
        10 => 'k10',
        13 => 'k13',
        15 => 'k15',
    ];
}

function is_valid_jpg_upload(array $file): bool
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    $tmp = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmp)) {
        return false;
    }

    $mime = mime_content_type($tmp);
    if (!in_array($mime, ['image/jpeg', 'image/pjpeg'], true)) {
        return false;
    }

    $info = @getimagesize($tmp);
    if (!$info || ($info[2] ?? null) !== IMAGETYPE_JPEG) {
        return false;
    }

    return true;
}

function selected_categories_from_post(): array
{
    $result = [];
    foreach (category_field_map() as $catId => $field) {
        if ((string) ($_POST['extra_categories'][$catId] ?? '0') === '1') {
            $result[] = $catId;
        }
    }
    return $result;
}

function current_month(): int
{
    $month = (int) query('monat', (int) date('n'));
    return max(1, min(12, $month));
}

function current_year(): int
{
    $year = (int) query('jahr', (int) date('Y'));
    return max(1970, min(2100, $year));
}

function preserve_query(array $replace = []): string
{
    $query = $_GET;
    foreach ($replace as $key => $value) {
        if ($value === null) {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }
    return '?' . http_build_query($query);
}