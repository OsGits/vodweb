<?php
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/../vodfl.php'; // 映射配置与工具

function get_categories() {
    static $cache = null;
    if ($cache !== null) return $cache;
    list($xml, $err) = api_xml('list', ['pg' => 1]);
    $cats = [];
    if ($xml && isset($xml->class)) {
        foreach ($xml->class->ty as $ty) {
            $attrs = $ty->attributes();
            $id = intval($attrs['id'] ?? 0);
            $name = (string)$ty;
            if ($id > 0) {
                $cats[] = ['id' => $id, 'name' => $name];
            }
        }
    }
    // 应用前端映射与过滤（如隐藏某些分类）
    $cats = vodfl_apply_to_categories($cats);
    $cache = $cats;
    return $cache;
}

function find_category_name($id) {
    foreach (get_categories() as $c) {
        if ($c['id'] == $id) return $c['name'];
    }
    return '';
}

?>