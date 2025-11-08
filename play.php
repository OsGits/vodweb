<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/categories.php';
require_once __DIR__ . '/lib/template.php';

// 初始化模板引擎
set_template(template_name());

// 获取分类数据
template()->setGlobal('categories', get_categories());
template()->setGlobal('is_play', true);

$token = $_GET['token'] ?? '';
$ep_index = intval($_GET['ep'] ?? 0);
$bundle = play_token_get($token);
$url = '';
$title = '';
$id = 0;
$episodes = [];
$source_name = '';
if ($bundle) {
  $id = intval($bundle['id'] ?? 0);
  $source_name = (string)($bundle['source'] ?? '');
  $episodes = $bundle['episodes'] ?? [];
  if (isset($episodes[$ep_index])) {
    $url = $episodes[$ep_index]['url'] ?? '';
    $title = $episodes[$ep_index]['title'] ?? '';
  }
}

// 准备播放页特定样式
$styles = '/* Play page specific width: PC 1080px, Mobile 99% */
  @media (min-width: 769px) { main.container { max-width: 1080px; } }
  @media (max-width: 768px) { main.container { max-width: 99%; } }';

// 使用模板渲染
$vars = [
    'title' => $title ? $title . ' - ' . site_name() : '播放页面 - ' . site_name(),
    'url' => $url,
    'title' => $title,
    'id' => $id,
    'episodes' => $episodes,
    'source_name' => $source_name,
    'ep_index' => $ep_index,
    'token' => $token,
    'styles' => $styles
];

// 渲染播放页内容（明确指定使用main布局）
echo template('play', $vars, 'main');
?>