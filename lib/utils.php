<?php
function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function param(string $key, $default = null) { return isset($_GET[$key]) ? $_GET[$key] : $default; }

function parse_episodes(?string $playUrl): array {
    // 解析 MacCMS 的 vod_play_url 字段：形如 "第1集$http://...|第2集$http://..."
    $episodes = [];
    if (!$playUrl) return $episodes;
    $parts = preg_split('/[\r\n#|]+/', $playUrl);
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') continue;
        $tmp = explode('$', $part, 2);
        if (count($tmp) === 2) {
            $episodes[] = ['name' => trim($tmp[0]), 'url' => trim($tmp[1])];
        } else {
            $episodes[] = ['name' => '播放', 'url' => trim($part)];
        }
    }
    return $episodes;
}

function is_m3u8(string $url): bool { return str_contains(strtolower($url), '.m3u8'); }
function is_mp4(string $url): bool { return str_contains(strtolower($url), '.mp4'); }

function site_url(string $path = ''): string {
    $cfg = require __DIR__ . '/../config.php';
    $base = rtrim($cfg['base_url'] ?? '/', '/');
    if ($path === '') return $base . '/';
    return $base . '/' . ltrim($path, '/');
}