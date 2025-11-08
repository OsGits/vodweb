<?php
// 播放页模板
// 设置is_play为false以显示导航栏和搜索框
$is_play = false;
?>
<?php if (!$url): ?>
  <p>无效的播放地址或令牌。</p>
<?php else: ?>
  <!-- 播放器容器 -->
  <div style="background:#0b1220; border:1px solid #1f2937; border-radius:8px; padding:10px;">
    <?php $is_m3u8 = str_contains(strtolower($url), 'm3u8'); ?>
    <?php if ($is_m3u8 && m3u8_enabled()): ?>
      <?php $player = rtrim(m3u8_proxy_base(), '?url='); $player = $player . (str_contains($player, '?') ? '&' : '?') . 'url=' . urlencode($url); ?>
      <div style="position:relative; width:100%; padding-top:56.25%; background:#000;">
        <iframe src="<?= h($player) ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;" allow="autoplay; encrypted-media" allowfullscreen></iframe>
      </div>
    <?php elseif (is_stream_url($url)): ?>
      <video src="<?= h($url) ?>" controls preload="metadata" style="width:100%; height:auto; max-height:85vh; background:#000;"></video>
    <?php else: ?>
      <iframe src="<?= h($url) ?>" style="width:100%; height:85vh; border:0;" allowfullscreen></iframe>
    <?php endif; ?>
  </div>

<h2 class="section-title">播放：<?= h($title) ?></h2>

  <div style="margin-top:12px;">
    <a class="page-link" href="/detail/<?= h($id) ?>">返回详情</a>
  </div>
  <?php if (!empty($episodes)): ?>
  <div class="episodes" style="margin-top:12px;">
    <h3><?= h($source_name) ?> 分集</h3>
    <div>
      <?php foreach ($episodes as $idx => $ep): 
        $is_active = ($idx === $ep_index);
        $link = '/play/' . rawurlencode($token) . '/' . $idx;
        $style = $is_active
          ? 'display:inline-block;margin:4px;padding:6px 10px;border-radius:6px;background:#2563eb;color:#fff;'
          : 'display:inline-block;margin:4px;padding:6px 10px;border-radius:6px;background:#1f2937;color:#cbd5e1;';
      ?>
        <a href="<?= h($link) ?>" style="<?= $style ?>"><?= h($ep['title']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
<?php endif; ?>
