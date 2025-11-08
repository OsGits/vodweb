<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/categories.php';
require_once __DIR__ . '/lib/template.php';

// 初始化模板引擎
set_template(template_name());

// 获取分类数据
template()->setGlobal('categories', get_categories());

$id = intval($_GET['id'] ?? 0);
list($data, $err) = get_vod_detail(['ids' => $id]);
$item = null;
if (!$err && $data && !empty($data['list'])) {
    $item = $data['list'][0];
}

// 清理简介内容：替换换行、去掉HTML标签、处理&nbsp;为普通空格
$description = '';
$sources = [];

if ($item) {
    $desc = (string)($item['vod_content'] ?? '');
    // 将常见换行标签转为\n，保留结构后再去标签
    $desc = str_ireplace(["<br>", "<br/>", "<br />"] , "\n", $desc);
    $desc = preg_replace('/<\s*\/\s*p\s*>/i', "\n", $desc);
    // 解码HTML实体（把&nbsp;等转为字符）
    $desc = html_entity_decode($desc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // 去掉所有HTML标签
    $desc = strip_tags($desc);
    // 将不间断空格替换为普通空格
    $desc = preg_replace('/\x{00A0}/u', ' ', $desc);
    // 规范空白：压缩多余空格，保留换行
    $desc = preg_replace('/[ \t]+/', ' ', $desc);
    $desc = preg_replace('/\n{3,}/', "\n\n", $desc);
    $description = trim($desc);
    
    // 解析播放源
    $sources = parse_play_sources($item);
    
    // 为每个剧集生成播放链接
    foreach ($sources as &$source) {
        $token = play_token_store($item['vod_id'], $item['vod_name'] ?? '', $source['name'], $source['episodes']);
        foreach ($source['episodes'] as $idx => &$episode) {
            $episode['play_url'] = '/play/' . rawurlencode($token) . '/' . $idx;
        }
    }
}

// 使用模板渲染
$vars = [
    'title' => $item ? ($item['vod_name'] ?? '') . ' - ' . site_name() : '未找到影片 - ' . site_name(),
    'item' => $item,
    'description' => $description,
    'sources' => $sources
];

// 渲染详情页内容（使用主布局）
echo template('detail', $vars, 'main');
?>