<?php
/**
 * 模板系统全局配置
 */

// 模板存储路径
$config['template_path'] = __DIR__ . '/..';

// 默认模板名称
$config['default_template'] = 'default';

// 是否启用模板缓存（true/false）
$config['cache_enabled'] = false;

// 模板缓存路径
$config['cache_path'] = __DIR__ . '/../cache';

// 缓存过期时间（秒）
$config['cache_lifetime'] = 3600;

// 调试模式
$config['debug'] = false;

// 组件默认存储路径
$config['components_dir'] = 'components';

// 布局默认存储路径
$config['layouts_dir'] = 'layouts';

// 部分模板默认存储路径
$config['partials_dir'] = 'partials';

// 模板文件扩展名
$config['template_extension'] = '.php';

// 错误处理模板
$config['error_template'] = 'error';

/**
 * 获取模板系统配置
 * 
 * @param string $key 配置项键名
 * @param mixed $default 默认值
 * @return mixed 配置值
 */
function template_config($key = null, $default = null) {
    global $config;
    
    if ($key === null) {
        return $config;
    }
    
    return $config[$key] ?? $default;
}

/**
 * 设置模板系统配置
 * 
 * @param string|array $key 配置项键名或配置数组
 * @param mixed $value 配置值（当key为字符串时）
 */
function set_template_config($key, $value = null) {
    global $config;
    
    if (is_array($key)) {
        $config = array_merge($config, $key);
    } else {
        $config[$key] = $value;
    }
}

/**
 * 确保模板缓存目录存在
 */
function ensure_template_cache_dir() {
    $cache_path = template_config('cache_path');
    if (!is_dir($cache_path)) {
        @mkdir($cache_path, 0755, true);
    }
    return $cache_path;
}
