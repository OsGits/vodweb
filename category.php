<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/categories.php';

$t = intval($_GET['t'] ?? 0);
$pg = current_page();

list($data, $err) = get_vod_list(['t' => $t, 'pg' => $pg]);
// Batch fetch details to enrich missing poster images
$items = $data['list'] ?? [];
$picMap = [];
$ids = [];
foreach ($items as $it) { if (!empty($it['vod_id'])) { $ids[] = $it['vod_id']; } }
if (!empty($ids)) {
    list($detailData, $detailErr) = get_vod_detail(['ids' => implode(',', $ids)]);
    if (!$detailErr && !empty($detailData['list'])) {
        foreach ($detailData['list'] as $d) {
            if (!empty($d['vod_id'])) {
                $picMap[$d['vod_id']] = $d['vod_pic'] ?? '';
            }
        }
    }
}

$cates = get_categories();
$cateName = ($cates[$t] ?? ['name' => '分类'])['name'];
include __DIR__ . '/partials/header.php';
?>
<h2 class="section-title">分类：<?= h($cateName) ?></h2>
<?php if ($err): ?>
  <div class="alert">接口请求错误：<?= h($err) ?></div>
<?php endif; ?>
<?php if (empty($data['list'])): ?>
  <div class="alert">暂无数据或接口无返回。</div>
<?php endif; ?>
<div class="masonry">
<?php foreach ($items as $item): ?>
  <?php $pic = $item['vod_pic'] ?? ($picMap[$item['vod_id']] ?? ''); ?>
  <a class="card" href="<?= h(url_for('/detail.php', ['id' => $item['vod_id']])) ?>">
    <img src="<?= h($pic) ?>" alt="<?= h($item['vod_name'] ?? '') ?>" loading="lazy" referrerpolicy="no-referrer" onerror="this.src='/assets/placeholder.svg'" />
    <div class="content">
      <div class="title"><?= h($item['vod_name'] ?? '') ?></div>
      <div class="meta"><?= h(($item['type_name'] ?? '')) ?> · <?= h($item['vod_remarks'] ?? '') ?></div>
      <div class="meta">更新时间：<?= h($item['vod_time'] ?? '') ?></div>
    </div>
  </a>
<?php endforeach; ?>
</div>
<?php
echo render_pagination(intval($data['page'] ?? $pg), intval($data['pagecount'] ?? $pg), '/category.php', ['t' => $t]);
include __DIR__ . '/partials/footer.php';
?>