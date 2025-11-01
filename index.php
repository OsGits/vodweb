<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/categories.php';
/* 首页精选配置与获取逻辑已移除以优化性能 */
function home_feature_names() { return []; }
function home_search_by_name($name) { return null; }
function home_fetch_featured() { return []; }

$pg = current_page();
// Show latest updates; h=24 shows items updated within last 24 hours, fallback to regular list
list($data, $err) = get_vod_list(['pg' => $pg, 'h' => 24]);
if ($err || !$data || empty($data['list'])) {
    list($data, $err) = get_vod_list(['pg' => $pg]);
}

// Batch fetch details to enrich missing poster images（仅在缺图时请求）
$items = $data['list'] ?? [];
$picMap = [];
$needIds = [];
foreach ($items as $it) {
    $id = !empty($it['vod_id']) ? $it['vod_id'] : null;
    $pic = $it['vod_pic'] ?? '';
    if ($id && (empty($pic) || $pic === '')) { $needIds[] = $id; }
}
if (!empty($needIds)) {
    list($detailData, $detailErr) = get_vod_detail(['ids' => implode(',', $needIds)]);
    if (!$detailErr && !empty($detailData['list'])) {
        foreach ($detailData['list'] as $d) {
            if (!empty($d['vod_id'])) {
                $picMap[$d['vod_id']] = $d['vod_pic'] ?? '';
            }
        }
    }
}

$featured = [];
$settings = load_settings();
include __DIR__ . '/partials/banner.php';
include __DIR__ . '/partials/header.php';
?>

<div class="container">
  <h2>最新更新</h2>
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
</div>
<?php
echo render_pagination(intval($data['page'] ?? $pg), intval($data['pagecount'] ?? $pg), '/index.php');
include __DIR__ . '/partials/footer.php';
?>