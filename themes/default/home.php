<?php
$list = $latest['list'] ?? [];
$classes = $classes ?? [];
?>
<section class="hero">
  <h1>发现好电影 / 好剧集</h1>
  <p class="muted">实时收录与更新，轻松找到你想看的内容</p>
</section>

<section>
  <div class="section-head">
    <h2>最新更新</h2>
    <a class="more" href="<?php echo site_url('index.php?page=category'); ?>">全部</a>
  </div>
  <div class="grid">
    <?php foreach ($list as $item): ?>
      <a class="card" href="<?php echo site_url('index.php?page=detail&id='.(int)$item['vod_id']); ?>">
        <div class="thumb" style="background-image:url('<?php echo e($item['vod_pic'] ?? ''); ?>')"></div>
        <div class="info">
          <div class="title"><?php echo e($item['vod_name'] ?? ''); ?></div>
          <div class="meta"><?php echo e($item['type_name'] ?? ''); ?> · <?php echo e($item['vod_remarks'] ?? ''); ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <div class="section-head">
    <h2>分类导航</h2>
  </div>
  <div class="chips">
    <?php foreach ($classes as $c): ?>
      <a class="chip" href="<?php echo site_url('index.php?page=category&t='.(int)$c['type_id']); ?>"><?php echo e($c['type_name']); ?></a>
    <?php endforeach; ?>
  </div>
</section>