<?php
// 主布局文件
$assets_path = template_config('assets_path', '/assets');
$site_name = site_name();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= h($title ?? $site_name) ?></title>
<link rel="stylesheet" href="<?= $assets_path ?>/style.css" />
<?php if (!empty($custom_css)): ?>
    <?php foreach ($custom_css as $css): ?>
        <link rel="stylesheet" href="<?= $css ?>" />
    <?php endforeach; ?>
<?php endif; ?>
<?php if (isset($styles)): ?>
    <style>
        <?= $styles ?>
    </style>
<?php endif; ?>
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <?php if (!isset($is_play) || !$is_play): ?>
    <div class="header-actions">
      <button class="nav-toggle" type="button" aria-controls="category-modal" aria-expanded="false">☰ 分类</button>
      <form class="search-form" action="/search" method="get">
        <input type="text" name="wd" placeholder="搜索影片..." value="<?= h($_GET['wd'] ?? '') ?>" />
        <button type="submit">搜索</button>
      </form>
    </div>
    <div id="category-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="category-title">
      <div class="modal-dialog">
        <button class="modal-close" type="button" aria-label="关闭">✕</button>
        <h2 id="category-title" class="modal-title">分类</h2>
        <nav id="side-nav" class="nav nav-vertical" tabindex="0">
          <a href="/" class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : '' ?>">首页</a>
          <?php if (isset($categories)): ?>
              <?php foreach ($categories as $c): ?>
                <a href="/category/<?= h($c['id']) ?>" class="<?= (basename($_SERVER['PHP_SELF']) === 'category.php' && intval($_GET['t'] ?? 0) === intval($c['id'])) ? 'active' : '' ?>"><?= h($c['name']) ?></a>
              <?php endforeach; ?>
          <?php endif; ?>
          <a href="/latest" class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php' && isset($_GET['h'])) ? 'active' : '' ?>">最新</a>
        </nav>
      </div>
    </div>
    <?php endif; ?>
  </div>
</header>
<main class="container">
  <?= $content ?? '' ?>
</main>
<footer class="site-footer">
  <div class="container">
    <p>数据来源于接口，本站不存储任何视频资源。</p>
  </div>
</footer>
<script src="<?= $assets_path ?>/app.js"></script>
<?php if (!empty($custom_js)): ?>
    <?php foreach ($custom_js as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (isset($scripts)): ?>
    <script>
        <?= $scripts ?>
    </script>
<?php endif; ?>
</body>
</html>
