<?php
$detail = $detail ?? [];
$item = $detail['list'][0] ?? [];
$episodes = parse_episodes($item['vod_play_url'] ?? '');
?>
<section class="detail">
  <div class="poster">
    <div class="thumb" style="background-image:url('<?php echo e($item['vod_pic'] ?? ''); ?>')"></div>
  </div>
  <div class="summary">
    <h1><?php echo e($item['vod_name'] ?? ''); ?></h1>
    <div class="tags">
      <span><?php echo e($item['type_name'] ?? ''); ?></span>
      <span><?php echo e($item['vod_area'] ?? ''); ?></span>
      <span><?php echo e($item['vod_lang'] ?? ''); ?></span>
      <span><?php echo e($item['vod_year'] ?? ''); ?></span>
      <span><?php echo e($item['vod_remarks'] ?? ''); ?></span>
    </div>
    <p class="muted">主演：<?php echo e($item['vod_actor'] ?? ''); ?> | 导演：<?php echo e($item['vod_director'] ?? ''); ?></p>
    <p class="desc"><?php echo e($item['vod_content'] ?? '暂无简介'); ?></p>
    <?php if (!empty($episodes)): ?>
      <div class="actions">
        <a class="btn" href="<?php echo site_url('index.php?page=play&id='.(int)$item['vod_id'].'&ep=0'); ?>">立即播放</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if (!empty($episodes)): ?>
<section>
  <div class="section-head">
    <h2>选集</h2>
  </div>
  <div class="chips">
    <?php foreach ($episodes as $i => $ep): ?>
      <a class="chip" href="<?php echo site_url('index.php?page=play&id='.(int)$item['vod_id'].'&ep='.$i); ?>"><?php echo e($ep['name']); ?></a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>