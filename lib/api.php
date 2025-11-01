<?php
require_once __DIR__ . '/../config.php';
if (!defined('API_BASE')) {
    define('API_BASE', 'https://cj.lziapi.com/api.php/provide/vod/');
}

// 简易文件缓存配置
function api_cache_dir() {
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    return $dir;
}
function api_cache_key($ac, $params, $type = 'json') {
    ksort($params);
    $base = rtrim(api_base(), '/') . '|ac=' . $ac . '|' . http_build_query($params);
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
    if ($ac === 'detail') return 3600; // 1小时
    if ($ac === 'list') {
        if (isset($params['h'])) return 1800; // 最新更新更频繁
        if (isset($params['wd'])) return 1800; // 搜索结果相对稳定
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

function http_get($url, $params = [], $headers = [], $retries = 1) {
    $url = build_url_with_query($url, $params);
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) PHP/7.0';
    $attempt = 0;
    $lastErr = null;
    $lastResp = null;
    while ($attempt <= $retries) {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
            if (stripos($url, 'https://') === 0) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            $headers = array_merge($headers, ['Accept-Encoding: gzip, deflate']);
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            $resp = curl_exec($ch);
            $err = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            if ($err) { $lastErr = 'HTTP error: ' . $err; }
            elseif ($code >= 400) { $lastErr = 'HTTP status ' . $code; }
            elseif ($resp) { return [$resp, null]; }
            $lastResp = $resp;
        } else {
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: $ua\r\n",
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
            if ($resp === false) { $lastErr = 'file_get_contents failed'; }
            else {
                $hdrs = isset($http_response_header) ? implode("\n", (array)$http_response_header) : '';
                if ((stripos($hdrs, 'Content-Encoding: gzip') !== false || substr($resp, 0, 2) === "\x1F\x8B") && function_exists('gzdecode')) {
                    $decoded = @gzdecode($resp);
                    if ($decoded !== false) { $resp = $decoded; }
                }
                return [$resp, null];
            }
            $lastResp = $resp;
        }
        $attempt++;
        if ($attempt <= $retries) { usleep(200000); }
    }
    return [$lastResp, $lastErr ?: 'Empty response'];
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
    if (!api_enabled()) {
        $stale = api_cache_read_any($key);
        if ($stale !== null) {
            $stale = preg_replace('/^\xEF\xBB\xBF/', '', $stale);
            $data = json_decode($stale, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [$data, null];
            }
        }
        return [null, 'API disabled'];
    }
    list($resp, $err) = http_get(api_base(), ['ac' => $ac] + $params, ['Accept: application/json']);
    if ($err || !$resp) {
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
    $resp = preg_replace('/^\xEF\xBB\xBF/', '', $resp);
    $data = json_decode($resp, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        api_cache_write($key, $resp);
        return [$data, null];
    }
    // JSON 解析失败：尝试旧缓存
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
    if (!api_enabled()) {
        $stale = api_cache_read_any($key);
        if ($stale !== null) {
            $stale = preg_replace('/^\xEF\xBB\xBF/', '', $stale);
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($stale);
            if ($xml !== false) {
                return [$xml, null];
            }
        }
        return [null, 'API disabled'];
    }
    $baseXml = rtrim(api_base(), '/') . '/at/xml/';
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
    if (!isset($params['pagesize'])) {
        $params['pagesize'] = get_setting('page_size', PAGE_SIZE);
    }
    // MacApi 兼容：仅向接口发送 limit，避免部分源将 pagesize 误当作 page
    $params['limit'] = intval($params['pagesize']);
    unset($params['pagesize']);

    list($data, $err) = api_json('list', $params);
    // 当 JSON 失败或返回结构异常时，回退到 XML 并转换为标准结构
    if ($err || !$data || !isset($data['list'])) {
        list($xml, $xerr) = api_xml('list', $params);
        if (!$xerr && $xml && isset($xml->list)) {
            $ln = $xml->list;
            $attrs = $ln->attributes();
            $page = intval($attrs['page'] ?? 1);
            $pagecount = intval($attrs['pagecount'] ?? 1);
            $pagesize = intval($attrs['pagesize'] ?? $params['limit']);
            $items = [];
            foreach ($ln->video as $v) {
                $items[] = [
                    'vod_id' => intval($v->id ?? 0),
                    'vod_name' => (string)($v->name ?? ''),
                    'type_name' => (string)($v->type ?? ''),
                    'vod_time' => (string)($v->last ?? ''),
                    'vod_remarks' => (string)($v->note ?? ''),
                    'vod_pic' => (string)($v->pic ?? ''),
                ];
            }
            return [[
                'code' => 1,
                'msg' => '数据列表(XML fallback)',
                'page' => $page,
                'pagecount' => $pagecount,
                'limit' => (string)$pagesize,
                'total' => count($items),
                'list' => $items,
            ], null];
        }
        return [$data, $err];
    }
    return [$data, null];
}

function get_vod_detail($params = []) {
    // Supported params: ids, h
    return api_json('detail', $params);
}

function play_source_name_aliases() {
    $defaults = [ 'lzm3u8' => '电信线路' ];
    $custom = get_setting('source_aliases', []);
    if (is_array($custom)) {
        // custom overrides defaults
        return array_merge($defaults, $custom);
    }
    return $defaults;
}

function play_source_display_name($name) {
    $aliases = play_source_name_aliases();
    $n = strtolower(trim((string)$name));
    foreach ($aliases as $k => $v) {
        $kk = strtolower($k);
        if ($n === $kk || strpos($n, $kk) !== false) {
            return $v;
        }
    }
    return $name;
}

function parse_play_sources($vod) {
    $from = $vod['vod_play_from'] ?? '';
    $urls = $vod['vod_play_url'] ?? '';
    $sources = [];
    if (!$urls) return $sources;

    // 分线名称：如 "lzm3u8$$$liangzi"
    $fromArr = array_filter(array_map('trim', explode('$$$', (string)$from)));
    if (empty($fromArr)) {
        // 有的源用逗号分隔from，兼容处理
        $fromArr = array_filter(array_map('trim', explode(',', (string)$from)));
    }

    // 分线地址块，按 $$$ 分隔
    $sourceBlocks = explode('$$$', (string)$urls);
    if (count($sourceBlocks) === 1) {
        $sourceBlocks = [$urls];
    }

    foreach ($sourceBlocks as $idx => $block) {
        $nameRaw = $fromArr[$idx] ?? ('源' . ($idx + 1));
        $nameLower = strtolower($nameRaw);
        // 只保留 m3u8 分线；若分线名不含 m3u8，但地址块中含 m3u8，也予以保留
        $blockHasM3u8 = (stripos($block, 'm3u8') !== false);
        if (stripos($nameLower, 'm3u8') === false && !$blockHasM3u8) {
            continue;
        }

        $episodes = [];
        // 每一集使用 # 分隔；兼容部分源仍使用 | 分隔
        $parts = array_filter(array_map('trim', explode('#', $block)));
        if (count($parts) <= 1) {
            $parts = array_filter(array_map('trim', explode('|', $block)));
        }
        foreach ($parts as $p) {
            // 格式：第几集$title$播放链接$url
            $pair = explode('$', $p, 2);
            $title = '';
            $url = '';
            if (count($pair) === 2) {
                $title = $pair[0];
                $url = $pair[1];
            } else {
                // 兜底：没有 $ 分隔则整段视为链接
                $title = '正片';
                $url = $p;
            }
            // 仅保留 m3u8 链接
            if ($url && stripos($url, 'm3u8') !== false) {
                $episodes[] = ['title' => $title ?: '正片', 'url' => $url];
            }
        }
        if (!empty($episodes)) {
            $sources[] = ['name' => play_source_display_name($nameRaw), 'episodes' => $episodes];
        }
    }
    return $sources;
}

?>