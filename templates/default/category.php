<?php
// 分类页模板
?>
<?php component('banner') ?>
<h2 class="section-title">分类：<?= h($cateName) ?></h2>
<?php if (isset($error)): ?>
  <div class="alert">接口请求错误：<?= h($error) ?></div>
<?php endif; ?>
<?php if (empty($items)): ?>
  <div class="alert">暂无数据或接口无返回。</div>
<?php endif; ?>
<div class="masonry">
<?php foreach ($items as $item): ?>
  <?php $pic = $item['vod_pic'] ?? ($picMap[$item['vod_id']] ?? ''); ?>
  <a class="card" href="/detail/<?= h($item['vod_id']) ?>">
    <img src="<?= h($pic) ?>" alt="<?= h($item['vod_name'] ?? '') ?>" loading="lazy" referrerpolicy="no-referrer" onerror="this.src='/assets/placeholder.svg'" />
    <div class="content">
      <div class="title"><?= h($item['vod_name'] ?? '') ?></div>
      <div class="meta"><?= h(($item['type_name'] ?? '')) ?> · <?= h($item['vod_remarks'] ?? '') ?></div>
      <div class="meta"><?= h($item['vod_time'] ?? '') ?></div>
    </div>
  </a>
<?php endforeach; ?>
</div>
<?php if (isset($pagination)): ?>
  <?= $pagination ?>
<?php endif; ?>