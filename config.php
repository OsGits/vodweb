<?php
// Basic configuration and helpers

define('API_BASE', 'https://cj.lziapi.com/api.php/provide/vod/');

define('SITE_NAME', '影视聚合站');

define('PAGE_SIZE', 20); // default requested size if API supports

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// PHP 7.0 compatibility polyfills for PHP 8 functions
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        if ($needle === '') return true;
        $len = strlen($needle);
        if ($len === 0) return true;
        return substr($haystack, -$len) === $needle;
    }
}

function url_for($path, $params = []) {
    $query = http_build_query($params);
    return $path . ($query ? ('?' . $query) : '');
}

function current_page() {
    return max(1, intval($_GET['pg'] ?? 1));
}

function render_pagination($page, $pagecount, $basePath, $extraParams = []) {
    if ($pagecount <= 1) return '';
    $html = '<div class="pagination">';
    $prev = max(1, $page - 1);
    $next = min($pagecount, $page + 1);
    $html .= '<a class="page-link" href="' . h(url_for($basePath, $extraParams + ['pg' => 1])) . '">首页</a>';
    $html .= '<a class="page-link" href="' . h(url_for($basePath, $extraParams + ['pg' => $prev])) . '">上一页</a>';
    $html .= '<span class="page-info">第 ' . h($page) . ' / ' . h($pagecount) . ' 页</span>';
    $html .= '<a class="page-link" href="' . h(url_for($basePath, $extraParams + ['pg' => $next])) . '">下一页</a>';
    $html .= '<a class="page-link" href="' . h(url_for($basePath, $extraParams + ['pg' => $pagecount])) . '">末页</a>';
    $html .= '</div>';
    return $html;
}

function is_stream_url($url) {
    $url = strtolower($url);
    return (str_ends_with($url, '.m3u8') || str_contains($url, '.m3u8')) ||
           (str_ends_with($url, '.mp4') || str_contains($url, '.mp4'));
}

if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

function play_token_store($id, $vod_title, $source_name, $episodes) {
    if (!isset($_SESSION['play_tokens'])) $_SESSION['play_tokens'] = [];
    try { $token = bin2hex(random_bytes(16)); } catch (Exception $e) { $token = uniqid('pt_', true); }
    $_SESSION['play_tokens'][$token] = [
        'id' => $id,
        'title' => $vod_title,
        'source' => $source_name,
        'episodes' => $episodes,
        'created' => time(),
    ];
    return $token;
}

function play_token_get($token) {
    $bundle = $_SESSION['play_tokens'][$token] ?? null;
    return is_array($bundle) ? $bundle : null;
}

function settings_path() {
    return __DIR__ . '/settings.json';
}
function load_settings() {
    $path = settings_path();
    if (is_file($path)) {
        $raw = @file_get_contents($path);
        if ($raw !== false) {
            $data = json_decode($raw, true);
            if (is_array($data)) return $data;
        }
    }
    return [];
}
function save_settings($arr) {
    $path = settings_path();
    $json = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return @file_put_contents($path, $json) !== false;
}
function get_setting($key, $default = null) {
    static $cache = null;
    if ($cache === null) $cache = load_settings();
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}
function set_setting($key, $value) {
    $all = load_settings();
    $all[$key] = $value;
    return save_settings($all);
}

function api_base() {
    $val = get_setting('api_base', null);
    return $val ? $val : API_BASE;
}
function m3u8_proxy_base() {
    return get_setting('m3u8_proxy', 'http://anyn.cc/m3u8/?url=');
}