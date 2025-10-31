<?php
$cfg = require __DIR__ . '/../../../config.php';
$classes = api_classes();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo e($cfg['site_name']); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo site_url('themes/'.$cfg['theme'].'/assets/style.css'); ?>" />
  <?php include __DIR__ . '/../theme_vars.php'; ?>
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?php echo site_url('index.php'); ?>"><?php echo e($cfg['site_name']); ?></a>
    <nav class="nav">
      <a href="<?php echo site_url('index.php?page=home'); ?>">首页</a>
      <?php foreach(($classes ?? []) as $c): ?>
        <a href="<?php echo site_url('index.php?page=category&t='.(int)$c['type_id']); ?>"><?php echo e($c['type_name']); ?></a>
      <?php endforeach; ?>
    </nav>
    <form class="search" action="<?php echo site_url('index.php'); ?>" method="get">
      <input type="hidden" name="page" value="search">
      <input type="text" name="wd" placeholder="搜索影视，演员..." value="<?php echo e($_GET['wd'] ?? ''); ?>">
      <button type="submit">搜索</button>
    </form>
  </div>
</header>
<main class="container">