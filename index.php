<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/categories.php';
require_once __DIR__ . '/lib/template.php';

// 初始化模板引擎
set_template(template_name());

// 获取分类数据
template()->setGlobal('categories', get_categories());

/* 首页精选配置与获取逻辑已移除以优化性能 */
function home_feature_names() { return []; }
function home_search_by_name($name) { return null; }
function home_fetch_featured() { return []; }

$pg = current_page();
// Show latest updates; h=24 shows items updated within last 24 hours, fallback to regular list
list($data, $err) = get_vod_list(['pg' => $pg, 'h' => 24]);
if ($err || !$data || empty($data['list'])) {
    list($data, $err) = get_vod_list(['pg' => $pg]);
}
// 当列表为空或请求错误时，降级尝试较小页尺寸（兼容部分源接口限制）
if (($err) || (!isset($data['list']) || empty($data['list']))) {
    list($data2, $err2) = get_vod_list(['pg' => $pg, 'pagesize' => 20]);
    if (!$err2 && !empty($data2['list'])) { $data = $data2; $err = null; }
}

// Batch fetch details to enrich missing poster images（仅在缺图时请求）
$items = $data['list'] ?? [];
$picMap = [];
$needIds = [];
foreach ($items as $it) {
    $id = !empty($it['vod_id']) ? $it['vod_id'] : null;
    $pic = $it['vod_pic'] ?? '';
    if ($id && (empty($pic) || $pic === '')) { $needIds[] = $id; }
}
if (!empty($needIds)) {
    // 分批查询详情，避免一次请求ID过多导致后续图片缺失
    $chunks = array_chunk($needIds, 20);
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

// 准备分页
$pagination = render_pagination(intval($data['page'] ?? $pg), intval($data['pagecount'] ?? $pg), '/index.php', ['__pretty' => 'home']);

// 准备banner数据
$banners = [
    [
        'active' => true,
        'image' => '/uploads/banners/2922bf058b011e7e6a252e50a0513ea4_20251101_113920_71c7a8.jpg',
        'title' => '热门影视推荐',
        'link' => '/'
    ],
    [
        'active' => false,
        'image' => '/uploads/banners/6b5574b50d2e17cfc14f69260ce495cd_20251101_113832_2dc0d8.jpg',
        'title' => '精选内容',
        'link' => '/'
    ]
];

// 初始化页面数据
$vars = [
    'title' => site_name() . ' - 首页',
    'items' => $items,
    'picMap' => $picMap,
    'error' => $err,
    'pagination' => $pagination,
    'banners' => $banners
];

// 渲染首页内容（明确指定使用main布局）
echo template('home', $vars, 'main');
?>