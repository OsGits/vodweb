<?php
function cache_path(string $key): string {
    $cfg = require __DIR__ . '/../config.php';
    $dir = $cfg['cache_dir'];
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . md5($key) . '.cache.php';
}

function cache_get(string $key, int $ttl): mixed {
    $path = cache_path($key);
    if (!file_exists($path)) return null;
    $mtime = @filemtime($path);
    if ($mtime === false) return null;
    if (time() - $mtime > $ttl) return null;
    $data = @file_get_contents($path);
    if ($data === false) return null;
    return unserialize($data);
}

function cache_set(string $key, mixed $value): void {
    $path = cache_path($key);
    @file_put_contents($path, serialize($value), LOCK_EX);
}