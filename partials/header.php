<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/categories.php';
$cats = get_categories();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= h(SITE_NAME) ?></title>
<link rel="stylesheet" href="/assets/style.css" />
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="<?= h(url_for('/index.php')) ?>"><?= h(SITE_NAME) ?></a>
    <nav class="nav" tabindex="0">
      <a href="<?= h(url_for('/index.php')) ?>" class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : '' ?>">首页</a>
      <?php foreach ($cats as $c): ?>
        <a href="<?= h(url_for('/category.php', ['t' => $c['id']])) ?>" class="<?= (basename($_SERVER['PHP_SELF']) === 'category.php' && intval($_GET['t'] ?? 0) === intval($c['id'])) ? 'active' : '' ?>"><?= h($c['name']) ?></a>
      <?php endforeach; ?>
      <a href="<?= h(url_for('/index.php', ['h' => 24])) ?>" class="<?= (basename($_SERVER['PHP_SELF']) === 'index.php' && isset($_GET['h'])) ? 'active' : '' ?>">最新</a>
    </nav>
    <form class="search-form" action="/search.php" method="get">
      <input type="text" name="wd" placeholder="搜索影片..." value="<?= h($_GET['wd'] ?? '') ?>" />
      <button type="submit">搜索</button>
    </form>
  </div>
</header>
<main class="container">