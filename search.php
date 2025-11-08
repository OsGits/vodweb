<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/categories.php';
require_once __DIR__ . '/lib/template.php';

$wd = trim($_GET['wd'] ?? '');
$pg = current_page();
$title = '搜索：' . $wd . ' - ' . site_name();

list($data, $err) = get_vod_list(['wd' => $wd, 'pg' => $pg]);
// 当列表为空或请求错误时，降级尝试较小页尺寸
if (($err) || (!isset($data['list']) || empty($data['list']))) {
    list($data2, $err2) = get_vod_list(['wd' => $wd, 'pg' => $pg, 'pagesize' => 20]);
    if (!$err2 && !empty($data2['list'])) { $data = $data2; $err = null; }
}
// Batch fetch details to enrich missing poster images
$items = $data['list'] ?? [];
$picMap = [];
$ids = [];
foreach ($items as $it) { if (!empty($it['vod_id'])) { $ids[] = $it['vod_id']; } }
if (!empty($ids)) {
    // 分批查询详情（每批20个ID），避免超过接口限制导致图片缺失
    $chunks = array_chunk($ids, 20);
    foreach ($chunks as $chunk) {
        list($detailData, $detailErr) = get_vod_detail(['ids' => implode(',', $chunk)]);
        if (!$detailErr && !empty($detailData['list'])) {
            foreach ($detailData['list'] as $d) {
                if (!empty($d['vod_id'])) {
                    $picMap[$d['vod_id']] = $d['vod_pic'] ?? '';
                }
            }
        }
    }
}

$items = $data['list'] ?? [];
?>
$pagination = render_pagination(intval(($data['page'] ?? $pg)), intval(($data['pagecount'] ?? $pg)), '/search.php', ['wd' => $wd, '__pretty' => 'search']);

// 准备模板变量
$templateVars = [
    'title' => $title,
    'wd' => $wd,
    'err' => $err,
    'items' => $items,
    'picMap' => $picMap,
    'pagination' => $pagination,
    'categories' => get_categories()
];

// 渲染模板
echo $template->render('search', $templateVars, 'main');
?>