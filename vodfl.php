<?php
/**
 * vodfl.php
 * 前端分类名称与接口分类名称映射（替换/隐藏）。
 *
 * 用法示例：
 * - 将接口中的“动作片”在前端显示为“动作电影”
 * - 将某些分类在前端隐藏（display=0）
 * - 可设置多个映射规则
 *
 * 规则结构：
 * [
 *   'api_names' => ['接口分类名1', '接口分类名2', ...], // 命中任一即应用本规则
 *   'display'   => '前端显示名称' | 0,                 // 字符串表示替换文本，0表示隐藏该分类
 * ]
 *
 * 可选：支持按ID匹配（若你知道接口分类ID），结构：
 * [ 'api_ids' => [1, 2, 3], 'display' => 'xxx' | 0 ]
 * 二者同时存在时，任一条件命中即可应用。
 */

function vodfl_rules() {
    // 在此配置你的映射规则
    return [
        // 示例：把“动作片”显示为“动作电影”，如果为0将因此不显示
       

        // 示例：隐藏“伦理片”分类（前端不展示）
       
        

        // 你可以继续添加多条规则...
        // [ 'api_names' => ['科幻片', '科幻'], 'display' => '科幻电影' ],
        // [ 'api_ids' => [123], 'display' => '自定义名称' ],
    ];
}

/**
 * 根据规则返回前端显示名称。
 * - 若命中隐藏规则（display=0），返回整数0。
 * - 若命中替换规则，返回替换后的字符串。
 * - 若未命中，返回原始名称。
 */
function vodfl_display_name($apiName, $apiId = null) {
    $rules = vodfl_rules();
    foreach ($rules as $r) {
        $matchName = false;
        $matchId = false;
        if (!empty($r['api_names']) && is_array($r['api_names'])) {
            $matchName = in_array($apiName, $r['api_names'], true);
        }
        if ($apiId !== null && !empty($r['api_ids']) && is_array($r['api_ids'])) {
            $matchId = in_array(intval($apiId), array_map('intval', $r['api_ids']), true);
        }
        if ($matchName || $matchId) {
            return $r['display'];
        }
    }
    return $apiName;
}

/**
 * 将分类列表应用映射规则：
 * - 对命中规则的分类重命名（替换显示文本）。
 * - 对display=0的分类进行过滤（隐藏）。
 *
 * @param array $cats 形如 [['id' => 1, 'name' => '动作片'], ...]
 * @return array 映射与过滤后的分类数组
 */
function vodfl_apply_to_categories(array $cats) {
    $out = [];
    foreach ($cats as $c) {
        $id = intval($c['id'] ?? 0);
        $name = (string)($c['name'] ?? '');
        $dsp = vodfl_display_name($name, $id);
        if ($dsp === 0) {
            // 隐藏该分类
            continue;
        }
        $out[] = ['id' => $id, 'name' => is_string($dsp) ? $dsp : $name];
    }
    return $out;
}