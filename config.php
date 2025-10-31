<?php
return [
    'site_name' => 'Lite Cinema',
    'base_url' => '/',
    'api_base' => 'https://cj.lziapi.com/api.php/provide/vod/',
    // 缓存目录与过期时间（秒）
    'cache_dir' => __DIR__ . '/storage/cache',
    'cache_ttl' => 300,
    // 主题名称，可切换例如 'default'
    'theme' => 'default',
    // 调试模式：输出错误信息
    'debug' => false,
];