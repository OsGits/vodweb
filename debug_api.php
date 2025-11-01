<?php
require_once __DIR__ . '/lib/api.php';
header('Content-Type: text/plain; charset=utf-8');

$ac = isset($_GET['ac']) ? (string)$_GET['ac'] : 'list';
$pg = max(1, intval($_GET['pg'] ?? 1));
$wd = trim($_GET['wd'] ?? '');
$t = intval($_GET['t'] ?? 0);
$ids = trim($_GET['ids'] ?? '');
$limit = intval($_GET['limit'] ?? get_setting('page_size', PAGE_SIZE));

$params = ['pg' => $pg, 'limit' => $limit];
if ($wd !== '') { $params['wd'] = $wd; }
if ($t > 0) { $params['t'] = $t; }
if ($ids !== '') { $params['ids'] = $ids; }

$url = build_url_with_query(api_base(), ['ac' => $ac] + $params);
echo "== Debug API ==\n";
echo "URL: $url\n";
echo "AC: $ac\n";
echo "Params: " . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";

list($resp, $httpErr) = http_get(api_base(), ['ac' => $ac] + $params, ['Accept: application/json']);
if ($httpErr) {
    echo "HTTP Error: $httpErr\n";
}
if (!$resp) {
    echo "Empty response\n";
    exit;
}
$snip = substr($resp, 0, 500);
echo "Raw (first 500):\n$snip\n";
$resp = preg_replace('/^\xEF\xBB\xBF/', '', $resp);
$data = json_decode($resp, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON error: " . json_last_error_msg() . "\n";
} else {
    echo "JSON ok. Keys: " . implode(',', array_keys($data)) . "\n";
    if (isset($data['page'])) { echo "Page: " . $data['page'] . "\n"; }
    if (isset($data['pagecount'])) { echo "Pagecount: " . $data['pagecount'] . "\n"; }
    if (isset($data['limit'])) { echo "Limit: " . $data['limit'] . "\n"; }
    if (isset($data['list'])) {
        $cnt = is_array($data['list']) ? count($data['list']) : 0;
        echo "List count: $cnt\n";
    }
}
?>