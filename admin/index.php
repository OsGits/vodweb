<?php
require_once __DIR__ . '/inc.php';

// 统一路由入口：登录后根据 p 参数加载对应模块
if (!admin_is_logged()) {
    header('Location: ' . url_for('/admin/login.php'));
    exit;
}

$routes = [
    'home' => __DIR__ . '/home.php',
    'settings' => __DIR__ . '/settings.php',
    'resources' => __DIR__ . '/resources.php',
    'banners' => __DIR__ . '/banners.php',
];

$p = isset($_GET['p']) ? (string)$_GET['p'] : 'home';
if (!array_key_exists($p, $routes)) {
    $p = 'home';
}

// 加载对应模块页面（页面自身包含布局输出）
require $routes[$p];