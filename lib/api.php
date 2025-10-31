<?php
require_once __DIR__ . '/../config.php';

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
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        // On Windows/PHP7 environments, SSL CA often missing; avoid hard fail
        if (stripos($url, 'https://') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
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
                'header' => "User-Agent: $ua\r\n",
                'timeout' => 15,
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
        return [$resp, null];
    }
}

function api_json($ac, $params = []) {
    list($resp, $err) = http_get(API_BASE, ['ac' => $ac] + $params, ['Accept: application/json']);
    if ($err) return [null, $err];
    $resp = preg_replace('/^\xEF\xBB\xBF/', '', $resp); // strip BOM if any
    $data = json_decode($resp, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [null, 'JSON parse error: ' . json_last_error_msg()];
    }
    return [$data, null];
}

function api_xml($ac, $params = []) {
    $baseXml = rtrim(API_BASE, '/') . '/at/xml/';
    list($resp, $err) = http_get($baseXml, ['ac' => $ac] + $params, ['Accept: application/xml']);
    if ($err) return [null, $err];
    $resp = preg_replace('/^\xEF\xBB\xBF/', '', $resp);
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($resp);
    if ($xml === false) {
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