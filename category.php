<?php
// 分类页控制器
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/categories.php';
require_once __DIR__ . '/lib/template.php';

// 获取请求参数
$t = intval($_GET['t'] ?? 0);
$pg = current_page();

// 获取数据列表
list($data, $err) = get_vod_list(['t' => $t, 'pg' => $pg]);
// 当列表为空或请求错误时，降级尝试较小页尺寸
if (($err) || (!isset($data['list']) || empty($data['list']))) {
    list($data2, $err2) = get_vod_list(['t' => $t, 'pg' => $pg, 'pagesize' => 20]);
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

// 计算分页
$pagination = render_pagination(intval($data['page'] ?? $pg), intval($data['pagecount'] ?? $pg), '/category.php', ['t' => $t, '__pretty' => 'category']);

// 准备模板变量
$templateVars = [
    'items' => $items,
    'picMap' => $picMap,
    'cateName' => find_category_name($t) ?: '分类',
    'error' => $err,
    'pagination' => $pagination
];

// 使用模板引擎渲染，指定使用main布局
$template = template();
echo $template->render('category', $templateVars, 'main');
?>