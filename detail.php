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
      <div style="white-space: pre-wrap;"><?= h($item['vod_content'] ?? '') ?></div>
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