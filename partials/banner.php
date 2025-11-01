<?php
require_once __DIR__ . '/../config.php';
$banners = get_setting('banners', []);
if (!is_array($banners) || empty($banners)) { return; }
$first = $banners[0];
?>
<div class="banner-top" style="margin-top:8px;">
  <div class="banner-slider" id="banner-slider" style="position:relative; width:100%; overflow:hidden; border-radius:8px; background:#0b1220; box-shadow: 0 8px 24px rgba(0,0,0,.25);">
    <div class="banner-sizer" style="width:100%; padding-top: calc(100% * 7 / 16);"></div>
    <?php $list = array_slice($banners, 0, 6); $i = 0; foreach ($list as $b): $isFirst = ($i === 0); $i++; ?>
      <a class="banner-slide" href="<?= h(url_for('/search.php', ['wd' => $b['name']])) ?>" title="搜索：<?= h($b['name']) ?>" style="position:absolute; top:0; left:0; right:0; bottom:0; display:block; opacity:<?= $isFirst ? '1' : '0' ?>; pointer-events:<?= $isFirst ? 'auto' : 'none' ?>; transition:opacity .6s ease;">
        <img src="<?= h($b['image']) ?>" alt="<?= h($b['name']) ?>" loading="lazy" referrerpolicy="no-referrer" style="width:100%; height:100%; object-fit:cover; display:block;" />
      </a>
    <?php endforeach; ?>
  </div>
<!-- banner slider script moved to /assets/app.js -->