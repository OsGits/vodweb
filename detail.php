<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';

$id = intval($_GET['id'] ?? 0);
list($data, $err) = get_vod_detail(['ids' => $id]);
$item = null;
if (!$err && $data && !empty($data['list'])) {
    $item = $data['list'][0];
}
include __DIR__ . '/partials/header.php';
if (!$item) {
    echo '<p>未找到影片详情。</p>';
    include __DIR__ . '/partials/footer.php';
    exit;
}
$sources = parse_play_sources($item);
?>
<div class="detail">
  <div>
    <img class="poster" src="<?= h($item['vod_pic'] ?? '') ?>" alt="<?= h($item['vod_name'] ?? '') ?>" referrerpolicy="no-referrer" onerror="this.src='/assets/placeholder.svg'" />
  </div>
  <div class="info">
    <h2><?= h($item['vod_name'] ?? '') ?></h2>
    <div>类型：<?= h($item['type_name'] ?? '') ?></div>
    <div>地区：<?= h($item['vod_area'] ?? '') ?> / 语言：<?= h($item['vod_lang'] ?? '') ?></div>
    <div>年份：<?= h($item['vod_year'] ?? '') ?> / 备注：<?= h($item['vod_remarks'] ?? '') ?></div>
    <div>演员：<?= h($item['vod_actor'] ?? '') ?></div>
    <div>导演：<?= h($item['vod_director'] ?? '') ?></div>
    <div>更新时间：<?= h($item['vod_time'] ?? '') ?></div>
    <div>
      <h3>简介</h3>
      <?php
        // 清理简介内容：替换换行、去掉HTML标签、处理&nbsp;为普通空格
        $desc = (string)($item['vod_content'] ?? '');
        // 将常见换行标签转为\n，保留结构后再去标签
        $desc = str_ireplace(["<br>", "<br/>", "<br />"], "\n", $desc);
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
        $desc = trim($desc);
      ?>
      <div style="white-space: pre-wrap;"><?= h($desc) ?></div>
    </div>
    <div class="play-sources">
      <h3>播放源与剧集</h3>
      <?php foreach ($sources as $s): ?>
        <h4><?= h($s['name']) ?></h4>
        <div class="episodes">
        <?php foreach ($s['episodes'] as $ep): ?>
          <?php $playUrl = url_for('/play.php', ['url' => $ep['url'], 'title' => $ep['title'], 'id' => $item['vod_id']]); ?>
          <a href="<?= h($playUrl) ?>"><?= h($ep['title']) ?></a>
        <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>