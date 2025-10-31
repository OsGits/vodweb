<?php
require_once __DIR__ . '/config.php';

$url = $_GET['url'] ?? '';
$title = $_GET['title'] ?? '';
$id = intval($_GET['id'] ?? 0);
include __DIR__ . '/partials/header.php';
?>
<h2 class="section-title">播放：<?= h($title) ?></h2>
<?php if (!$url): ?>
  <p>无效的播放地址。</p>
<?php else: ?>
  <div style="background:#0b1220; border:1px solid #1f2937; border-radius:8px; padding:10px;">
    <?php if (is_stream_url($url)): ?>
      <video src="<?= h($url) ?>" controls preload="metadata" style="width:100%; max-height:70vh; background:#000;"></video>
    <?php else: ?>
      <iframe src="<?= h($url) ?>" style="width:100%; height:70vh; border:0;" allowfullscreen></iframe>
    <?php endif; ?>
  </div>
  <div style="margin-top:12px;">
    <a class="page-link" href="<?= h(url_for('/detail.php', ['id' => $id])) ?>">返回详情</a>
  </div>
<?php endif; ?>
<?php include __DIR__ . '/partials/footer.php'; ?>