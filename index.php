<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/categories.php';
/* 首页精选配置（按vod_name设置多个影片）与获取逻辑 */
function home_feature_names() {
    return [
        // 在此填入影片名，例如：'长津湖', '流浪地球', '斗罗大陆',
    ];
}
function home_search_by_name($name) {
    $name = trim((string)$name);
    if ($name === '') return null;
    list($data, $err) = get_vod_list(['wd' => $name, 'pg' => 1]);
    if ($err || empty($data) || empty($data['list'])) return null;
    $list = $data['list'];
    foreach ($list as $it) {
        if (isset($it['vod_name']) && (string)$it['vod_name'] === $name) {
            return $it;
        }
    }
    return $list[0] ?? null;
}
function home_fetch_featured() {
    $names = home_feature_names();
    $out = [];
    $seen = [];
    foreach ($names as $nm) {
        $it = home_search_by_name($nm);
        if (!$it) continue;
        $id = intval($it['vod_id'] ?? 0);
        if ($id > 0 && !isset($seen[$id])) {
            $seen[$id] = true;
            $out[] = $it;
        }
    }
    return $out;
}

$pg = current_page();
// Show latest updates; h=24 shows items updated within last 24 hours, fallback to regular list
list($data, $err) = get_vod_list(['pg' => $pg, 'h' => 24]);
if ($err || !$data || empty($data['list'])) {
    list($data, $err) = get_vod_list(['pg' => $pg]);
}

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

$featured = home_fetch_featured();
include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <?php if (!empty($featured)): ?>
    <h2 class="section-title">精选推荐</h2>
    <div class="masonry">
      <?php foreach ($featured as $item): ?>
        <?php $pic = $item['vod_pic'] ?? ''; ?>
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
  <?php endif; ?>

  <h2 class="section-title">最新更新</h2>
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
</div>
<?php
echo render_pagination(intval($data['page'] ?? $pg), intval($data['pagecount'] ?? $pg), '/index.php');
include __DIR__ . '/partials/footer.php';
?>