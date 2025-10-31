<?php
require_once __DIR__ . '/cache.php';

function api_request(array $params): array {
    $cfg = require __DIR__ . '/../config.php';
    $base = rtrim($cfg['api_base'], '/') . '/';

    if (!isset($params['ac'])) {
        $params['ac'] = 'list';
    }
    // 强制使用 JSON
    $params['at'] = 'json';

    $qs = http_build_query($params);
    $url = $base . '?' . $qs;

    $cacheKey = 'api:' . $url;
    $cached = cache_get($cacheKey, $cfg['cache_ttl']);
    if ($cached !== null) {
        return $cached;
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: LiteCinema/1.0\r\nAccept: application/json\r\n",
            'timeout' => 15,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return ['code' => 0, 'msg' => '网络错误'];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['code' => 0, 'msg' => '接口返回非JSON'];
    }

    cache_set($cacheKey, $data);
    return $data;
}

function api_list(int $t = 0, int $pg = 1, string $wd = '', int $h = 0): array {
    $params = ['ac' => 'list', 'pg' => max(1, $pg)];
    if ($t > 0) $params['t'] = $t;
    if ($wd !== '') $params['wd'] = $wd;
    if ($h > 0) $params['h'] = $h;
    return api_request($params);
}

function api_detail_by_id(int $id): array {
    return api_request(['ac' => 'detail', 'ids' => $id]);
}

function api_classes(): array {
    $data = api_list(0, 1);
    // MacCMS 扩展字段 class
    if (isset($data['class']) && is_array($data['class'])) {
        return $data['class'];
    }
    // 兜底：从列表的 type 映射（可能缺失）
    return [];
}