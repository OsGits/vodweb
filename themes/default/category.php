<?php
$data = $data ?? [];
$list = $data['list'] ?? [];
$pg = (int)($pg ?? 1);
$t = (int)($t ?? 0);
$pagecount = (int)($data['pagecount'] ?? $pg);
?>
<section class="hero small">
  <h1>分类浏览</h1>
  <p class="muted">按分类快速查找影片与剧集</p>
</section>

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

<div class="pagination">
  <?php if ($pg > 1): ?>
    <a href="<?php echo site_url('index.php?page=category&t='.$t.'&pg='.($pg-1)); ?>">上一页</a>
  <?php endif; ?>
  <span>第 <?php echo $pg; ?> / <?php echo $pagecount; ?> 页</span>
  <?php if ($pg < $pagecount): ?>
    <a href="<?php echo site_url('index.php?page=category&t='.$t.'&pg='.($pg+1)); ?>">下一页</a>
  <?php endif; ?>
</div>