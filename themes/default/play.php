<?php
$detail = $detail ?? [];
$item = $detail['list'][0] ?? [];
echo '<section class="hero small"><h1>'.e($item['vod_name'] ?? '').'</h1><p class="muted">正在播放</p></section>';
$episodes = parse_episodes($item['vod_play_url'] ?? '');
$index = max(0, min((int)($ep ?? 0), max(0, count($episodes)-1)));
$current = $episodes[$index] ?? null;
?>

<?php if ($current): ?>
<div class="player">
  <?php if (is_m3u8($current['url'])): ?>
    <video id="video" controls></video>
    <script>
      if (Hls.isSupported()) {
        var hls = new Hls();
        hls.loadSource('<?php echo e($current['url']); ?>');
        hls.attachMedia(document.getElementById('video'));
      } else {
        var v = document.getElementById('video');
        v.src = '<?php echo e($current['url']); ?>';
      }
    </script>
  <?php elseif (is_mp4($current['url'])): ?>
    <video src="<?php echo e($current['url']); ?>" controls class="native"></video>
  <?php else: ?>
    <iframe src="<?php echo e($current['url']); ?>" class="iframe" allowfullscreen referrerpolicy="no-referrer"></iframe>
  <?php endif; ?>
</div>
<?php else: ?>
<p>当前无可播放地址。</p>
<?php endif; ?>

<?php if (!empty($episodes)): ?>
<div class="chips">
  <?php foreach ($episodes as $i => $epRow): ?>
    <a class="chip<?php echo $i===$index ? ' active' : ''; ?>" href="<?php echo site_url('index.php?page=play&id='.(int)$item['vod_id'].'&ep='.$i); ?>"><?php echo e($epRow['name']); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>