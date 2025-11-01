<?php
// Simple image proxy to bypass hotlink protection and unify headers
// Usage: /img.php?url=<encoded remote image url>

// Security: allow only http/https and reasonably sized responses
$u = isset($_GET['url']) ? (string)$_GET['url'] : (isset($_GET['u']) ? (string)$_GET['u'] : '');
if ($u === '' || !preg_match('#^https?://#i', $u)) {
    http_response_code(400);
    header('Content-Type: image/svg+xml');
    echo file_exists(__DIR__ . '/assets/placeholder.svg') ? file_get_contents(__DIR__ . '/assets/placeholder.svg') : '';
    exit;
}

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0777, true); }
$hash = md5($u);
$cacheFile = $cacheDir . '/img_' . $hash . '.cache';
$ttl = 86400; // 1 day

function detect_content_type($data) {
    if (strlen($data) >= 4) {
        $h = substr($data, 0, 4);
        if (strncmp($h, "\x89PNG", 4) === 0) return 'image/png';
        if (strncmp($h, "\xFF\xD8\xFF", 3) === 0) return 'image/jpeg';
        if (strncmp($h, 'GIF8', 4) === 0) return 'image/gif';
    }
    if (stripos($data, '<svg') !== false) return 'image/svg+xml';
    return 'application/octet-stream';
}

// Serve from cache if fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
    $data = file_get_contents($cacheFile);
    $ctype = detect_content_type($data);
    header('Content-Type: ' . $ctype);
    header('Cache-Control: public, max-age=' . $ttl);
    header('Content-Length: ' . strlen($data));
    echo $data;
    exit;
}

// Fetch remote image
$data = '';
$err = '';
if (function_exists('curl_init')) {
    $ch = curl_init($u);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (img-proxy)');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: image/*,*/*;q=0.8',
        'Referer:' // no-referrer
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) { $err = curl_error($ch); }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300 && $resp) { $data = $resp; }
} else {
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => "User-Agent: Mozilla/5.0 (img-proxy)\r\nAccept: image/*,*/*;q=0.8\r\n",
        ]
    ]);
    $resp = @file_get_contents($u, false, $ctx);
    if ($resp) { $data = $resp; }
}

if (!$data) {
    // Fallback to placeholder
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=300');
    $ph = file_exists(__DIR__ . '/assets/placeholder.svg') ? file_get_contents(__DIR__ . '/assets/placeholder.svg') : '';
    echo $ph;
    exit;
}

// Write cache and serve
@file_put_contents($cacheFile, $data);
$ctype = detect_content_type($data);
header('Content-Type: ' . $ctype);
header('Cache-Control: public, max-age=' . $ttl);
header('Content-Length: ' . strlen($data));
echo $data;