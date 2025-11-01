<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/categories.php';
$cats = get_categories();
$isPlay = (basename($_SERVER['PHP_SELF']) === 'play.php');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= h(site_name()) ?></title>
<link rel="stylesheet" href="/assets/style.css" />
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="/"><?= h(site_name()) ?></a>
    <?php if (!$isPlay): ?>
    <button class="nav-toggle" type="button" aria-controls="category-modal" aria-expanded="false">☰ 分类</button>
    <div id="category-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="category-title">
      <div class="modal-dialog">
        <button class="modal-close" type="button" aria-label="关闭">✕</button>
        <h2 id="category-title" class="modal-title">分类</h2>
        <nav id="side-nav" class="nav nav-vertical" tabindex="0">
          <a href="/" class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : '' ?>">首页</a>
          <?php foreach ($cats as $c): ?>
            <a href="/category/<?= h($c['id']) ?>" class="<?= (basename($_SERVER['PHP_SELF']) === 'category.php' && intval($_GET['t'] ?? 0) === intval($c['id'])) ? 'active' : '' ?>"><?= h($c['name']) ?></a>
          <?php endforeach; ?>
          <a href="/latest" class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php' && isset($_GET['h'])) ? 'active' : '' ?>">最新</a>
        </nav>
      </div>
    </div>
    <form class="search-form" action="/search" method="get">
      <input type="text" name="wd" placeholder="搜索影片..." value="<?= h($_GET['wd'] ?? '') ?>" />
      <button type="submit">搜索</button>
    </form>
    <?php endif; ?>
  </div>
</header>
<main class="container">