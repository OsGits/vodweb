<?php
// 详情页模板
if (!$item):
    ?>
    <p>未找到影片详情。</p>
    <?php
    return;
endif;
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
      <div style="white-space: pre-wrap;"><?= h($description) ?></div>
    </div>
    <div class="play-sources">
      <h3>播放源与剧集</h3>
      <?php if (empty($sources)): ?>
          <p>暂无可用播放源。</p>
      <?php else: ?>
          <?php foreach ($sources as $s): ?>
            <h4><?= h($s['name']) ?></h4>
            <div class="episodes">
            <?php foreach ($s['episodes'] as $idx => $ep): ?>
              <a href="<?= h($ep['play_url']) ?>"><?= h($ep['title']) ?></a>
            <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
