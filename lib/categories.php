<?php
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/../vodfl.php'; // 映射配置与工具

function categories_apply_admin_overrides($cats) {
    // 名称别名
    $aliases = get_setting('category_aliases', []);
    if (is_array($aliases) && !empty($aliases)) {
        foreach ($cats as &$c) {
            $name = (string)($c['name'] ?? '');
            foreach ($aliases as $k => $v) {
                $kk = strtolower(trim((string)$k));
                if ($kk !== '' && strpos(strtolower($name), $kk) !== false) {
                    $c['name'] = (string)$v;
                    break;
                }
            }
        }
        unset($c);
    }
    // 名称隐藏列表（仅按名称关键词隐藏，不按ID隐藏）
    $hide = get_setting('category_hide', []);
    if (is_array($hide) && !empty($hide)) {
        $hideNorm = array_map(function($x){ return strtolower(trim((string)$x)); }, $hide);
        $cats = array_values(array_filter($cats, function($c) use ($hideNorm){
            $name = strtolower(trim((string)($c['name'] ?? '')));
            foreach ($hideNorm as $h) {
                if ($h !== '' && (strpos($name, $h) !== false)) return false;
            }
            return true;
        }));
    }
    return $cats;
}

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
    // 应用后台配置的别名与隐藏（仅名称）
    $cats = categories_apply_admin_overrides($cats);
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