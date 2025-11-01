<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/categories.php';

$wd = trim($_GET['wd'] ?? '');
$pg = current_page();

include __DIR__ . '/partials/header.php';
?>
<h2 class="section-title">搜索：<?= h($wd) ?></h2>
<?php if ($wd === ''): ?>
  <div class="alert">请输入关键词进行搜索。</div>
<?php else: ?>
<?php
list($data, $err) = get_vod_list(['wd' => $wd, 'pg' => $pg]);
// 当列表为空或请求错误时，降级尝试较小页尺寸
if (($err) || (!isset($data['list']) || empty($data['list']))) {
    list($data2, $err2) = get_vod_list(['wd' => $wd, 'pg' => $pg, 'pagesize' => 20]);
    if (!$err2 && !empty($data2['list'])) { $data = $data2; $err = null; }
}
// Batch fetch details to enrich missing poster images
$items = $data['list'] ?? [];
$picMap = [];
$ids = [];
foreach ($items as $it) { if (!empty($it['vod_id'])) { $ids[] = $it['vod_id']; } }
if (!empty($ids)) {
    // 分批查询详情（每批20个ID），避免超过接口限制导致图片缺失
    $chunks = array_chunk($ids, 20);
    foreach ($chunks as $chunk) {
        list($detailData, $detailErr) = get_vod_detail(['ids' => implode(',', $chunk)]);
        if (!$detailErr && !empty($detailData['list'])) {
            foreach ($detailData['list'] as $d) {
                if (!empty($d['vod_id'])) {
                    $picMap[$d['vod_id']] = $d['vod_pic'] ?? '';
                }
            }
        }
    }
}
?>
<?php if ($err): ?>
  <div class="alert">接口请求错误：<?= h($err) ?></div>
<?php endif; ?>
<?php if (empty($data['list'])): ?>
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
      <div class="meta">更新时间：<?= h($item['vod_time'] ?? '') ?></div>
    </div>
  </a>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php
echo render_pagination(intval(($data['page'] ?? $pg)), intval(($data['pagecount'] ?? $pg)), '/search.php', ['wd' => $wd, '__pretty' => 'search']);
include __DIR__ . '/partials/footer.php';
?>