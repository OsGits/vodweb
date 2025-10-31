<?php
require_once __DIR__ . '/../config.php';

// 简易文件缓存配置
function api_cache_dir() {
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    return $dir;
}
function api_cache_key($ac, $params, $type = 'json') {
    ksort($params);
    $base = rtrim(API_BASE, '/') . '|ac=' . $ac . '|' . http_build_query($params);
    return $type . '_' . md5($base);
}
function api_cache_path($key) { return api_cache_dir() . '/' . $key . '.cache'; }
function api_cache_read($key, $ttl) {
    $path = api_cache_path($key);
    if (is_file($path)) {
        $age = time() - intval(@filemtime($path));
        if ($age <= $ttl) {
            return @file_get_contents($path);
        }
    }
    return null;
}
function api_cache_read_any($key) {
    $path = api_cache_path($key);
    return is_file($path) ? @file_get_contents($path) : null;
}
function api_cache_write($key, $content) {
    $path = api_cache_path($key);
    @file_put_contents($path, $content);
}
function api_ttl($ac, $params, $type = 'json') {
    // 根据接口与参数设置不同TTL
    if ($ac === 'detail') return 86400; // 24小时
    if ($ac === 'list') {
        if (isset($params['h'])) return 120; // 最新更新更频繁
        if (isset($params['wd'])) return 600; // 搜索结果相对稳定
        return 300; // 分类/普通列表
    }
    return 300;
}

function build_url_with_query($url, $params) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $url .= (str_contains($url, '?') ? '&' : '?') . $qs;
    }
    return $url;
}

function http_get($url, $params = [], $headers = []) {
    $url = build_url_with_query($url, $params);
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) PHP/7.0';

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); // 启用压缩
        // On Windows/PHP7 environments, SSL CA often missing; avoid hard fail
        if (stripos($url, 'https://') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        // Accept-Encoding header
        $headers = array_merge($headers, ['Accept-Encoding: gzip, deflate']);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($err) {
            return [null, 'HTTP error: ' . $err];
        }
        if ($code >= 400) {
            return [null, 'HTTP status ' . $code];
        }
        return [$resp, null];
    } else {
        // Fallback to file_get_contents
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: $ua\r\nAccept-Encoding: gzip, deflate\r\n",
                'timeout' => 10,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        if (!empty($headers)) {
            $opts['http']['header'] .= implode("\r\n", $headers) . "\r\n";
        }
        $context = stream_context_create($opts);
        $resp = @file_get_contents($url, false, $context);
        if ($resp === false) {
            return [null, 'file_get_contents failed'];
        }
        // 注意：file_get_contents不会自动解压gzip，绝大多数源仍返回未压缩内容，保持兼容
        return [$resp, null];
    }
}

function api_json($ac, $params = []) {
    $ttl = api_ttl($ac, $params, 'json');
    $key = api_cache_key($ac, $params, 'json');
    $cached = api_cache_read($key, $ttl);
    if ($cached !== null) {
        $cached = preg_replace('/^\xEF\xBB\xBF/', '', $cached);
        $data = json_decode($cached, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [$data, null];
        }
    }
    list($resp, $err) = http_get(API_BASE, ['ac' => $ac] + $params, ['Accept: application/json']);
    if ($err || !$resp) {
        // 回退到过期缓存（若存在）
        $stale = api_cache_read_any($key);
        if ($stale !== null) {
            $stale = preg_replace('/^\xEF\xBB\xBF/', '', $stale);
            $data = json_decode($stale, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [$data, null];
            }
        }
        return [null, $err ?: 'Empty response'];
    }
    $resp = preg_replace('/^\xEF\xBB\xBF/', '', $resp); // strip BOM if any
    api_cache_write($key, $resp);
    $data = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // 解析失败也尝试回退到旧缓存
        $stale = api_cache_read_any($key);
        if ($stale !== null) {
            $stale = preg_replace('/^\xEF\xBB\xBF/', '', $stale);
            $data2 = json_decode($stale, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [$data2, null];
            }
        }
        return [null, 'JSON parse error: ' . json_last_error_msg()];
    }
    return [$data, null];
}

function api_xml($ac, $params = []) {
    $ttl = api_ttl($ac, $params, 'xml');
    $key = api_cache_key($ac, $params, 'xml');
    $cached = api_cache_read($key, $ttl);
    if ($cached !== null) {
        $cached = preg_replace('/^\xEF\xBB\xBF/', '', $cached);
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($cached);
        if ($xml !== false) {
            return [$xml, null];
        }
    }
    $baseXml = rtrim(API_BASE, '/') . '/at/xml/';
    list($resp, $err) = http_get($baseXml, ['ac' => $ac] + $params, ['Accept: application/xml']);
    if ($err || !$resp) {
        $stale = api_cache_read_any($key);
        if ($stale !== null) {
            $stale = preg_replace('/^\xEF\xBB\xBF/', '', $stale);
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($stale);
            if ($xml !== false) {
                return [$xml, null];
            }
        }
        return [null, $err ?: 'Empty response'];
    }
    $resp = preg_replace('/^\xEF\xBB\xBF/', '', $resp);
    api_cache_write($key, $resp);
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($resp);
    if ($xml === false) {
        $stale = api_cache_read_any($key);
        if ($stale !== null) {
            $stale = preg_replace('/^\xEF\xBB\xBF/', '', $stale);
            $xml2 = simplexml_load_string($stale);
            if ($xml2 !== false) {
                return [$xml2, null];
            }
        }
        return [null, 'XML parse error'];
    }
    return [$xml, null];
}

function get_vod_list($params = []) {
    // Supported params: t, pg, wd, h
    return api_json('list', $params);
}

function get_vod_detail($params = []) {
    // Supported params: ids, h
    return api_json('detail', $params);
}

function parse_play_sources($vod) {
    $from = $vod['vod_play_from'] ?? '';
    $urls = $vod['vod_play_url'] ?? '';
    $sources = [];
    if (!$urls) return $sources;

    $fromArr = array_filter(array_map('trim', explode(',', (string)$from)));
    // Multiple sources are typically separated by $$$ in MacCMS
    $sourceBlocks = explode('$$$', (string)$urls);
    if (count($sourceBlocks) === 1) {
        $sourceBlocks = [$urls];
    }
    foreach ($sourceBlocks as $idx => $block) {
        $episodes = [];
        $parts = array_filter(array_map('trim', explode('|', $block)));
        foreach ($parts as $p) {
            // Format: title$url
            $pair = explode('$', $p, 2);
            if (count($pair) === 2) {
                $episodes[] = ['title' => $pair[0], 'url' => $pair[1]];
            } elseif (!empty($p)) {
                $episodes[] = ['title' => '正片', 'url' => $p];
            }
        }
        $name = $fromArr[$idx] ?? ('源' . ($idx + 1));
        $sources[] = ['name' => $name, 'episodes' => $episodes];
    }
    return $sources;
}

?>