<?php
// Bootstrap
$cfg = require __DIR__ . '/config.php';
if (($cfg['debug'] ?? false) === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
require_once __DIR__ . '/lib/http.php';
require_once __DIR__ . '/lib/template.php';
require_once __DIR__ . '/lib/utils.php';

$page = strtolower((string) param('page', 'home'));

switch ($page) {
    case 'home':
        $classes = api_classes();
        $latest = api_list(0, 1);
        render('home.php', ['classes' => $classes, 'latest' => $latest]);
        break;
    case 'category':
        $t = (int) param('t', 0);
        $pg = (int) param('pg', 1);
        $data = api_list($t, $pg);
        render('category.php', ['t' => $t, 'pg' => $pg, 'data' => $data]);
        break;
    case 'detail':
        $id = (int) param('id', 0);
        $detail = api_detail_by_id($id);
        render('detail.php', ['id' => $id, 'detail' => $detail]);
        break;
    case 'play':
        $id = (int) param('id', 0);
        $ep = (int) param('ep', 0); // 从0开始索引
        $detail = api_detail_by_id($id);
        render('play.php', ['id' => $id, 'ep' => $ep, 'detail' => $detail]);
        break;
    case 'search':
        $wd = trim((string) param('wd', ''));
        $pg = (int) param('pg', 1);
        $data = api_list(0, $pg, $wd);
        render('search.php', ['wd' => $wd, 'pg' => $pg, 'data' => $data]);
        break;
    default:
        http_response_code(404);
        echo '404 Not Found';
}