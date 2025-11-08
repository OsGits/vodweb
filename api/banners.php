<?php
/**
 * 横幅API接口
 * 提供前台获取横幅数据的功能
 */
require_once __DIR__ . '/../config.php';

// 确保返回JSON格式
header('Content-Type: application/json; charset=utf-8');

// 加载设置中的横幅数据
function get_banners_data() {
    $settings = load_settings();
    $banners = get_setting('banners', []);
    
    // 转换数据格式，添加必要的字段
    $result = [];
    foreach ($banners as $index => $banner) {
        $result[] = [
            'id' => $index,
            'image' => $banner['image'],
            'title' => $banner['name'],
            'link' => '/search.php?wd=' . urlencode($banner['name']),
            'active' => ($index === 0) // 第一个横幅默认为激活状态
        ];
    }
    
    return $result;
}

// 返回横幅数据
$banners = get_banners_data();
echo json_encode([
    'code' => 200,
    'message' => 'success',
    'data' => $banners
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
