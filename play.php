<?php
require_once __DIR__ . '/config.php';

$token = $_GET['token'] ?? '';
$epIndex = intval($_GET['ep'] ?? 0);
$bundle = play_token_get($token);
$url = '';
$title = '';
$id = 0;
$episodes = [];
$sourceName = '';
if ($bundle) {
  $id = intval($bundle['id'] ?? 0);
  $sourceName = (string)($bundle['source'] ?? '');
  $episodes = $bundle['episodes'] ?? [];
  if (isset($episodes[$epIndex])) {
    $url = $episodes[$epIndex]['url'] ?? '';
    $title = $episodes[$epIndex]['title'] ?? '';
  }
}

?>
<?php include __DIR__ . '/partials/header.php'; ?>
<style>
  /* Play page specific width: PC 1080px, Mobile 99% */
  @media (min-width: 769px) { main.container { max-width: 1080px; } }
  @media (max-width: 768px) { main.container { max-width: 99%; } }
</style>
<?php if (!$url): ?>
  <p>无效的播放地址或令牌。</p>
<?php else: ?>
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
    <a class="page-link" href="<?= h(url_for('/detail.php', ['id' => $id])) ?>">返回详情</a>
  </div>
  <?php if ($episodes): ?>
  <div class="episodes" style="margin-top:12px;">
    <h3><?= h($sourceName) ?> 分集</h3>
    <div>
      <?php foreach ($episodes as $idx => $ep): 
        $isActive = ($idx === $epIndex);
        $link = url_for('/play.php', ['token' => $token, 'ep' => $idx]);
        $style = $isActive
          ? 'display:inline-block;margin:4px;padding:6px 10px;border-radius:6px;background:#2563eb;color:#fff;'
          : 'display:inline-block;margin:4px;padding:6px 10px;border-radius:6px;background:#1f2937;color:#cbd5e1;';
      ?>
        <a href="<?= h($link) ?>" style="<?= $style ?>"><?= h($ep['title']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
<?php endif; ?>
<?php include __DIR__ . '/partials/footer.php'; ?>