<?php
require_once __DIR__ . '/inc.php';
admin_require_login();

$save_msg = '';
$action = $_POST['action'] ?? '';
if ($action === 'save_resources') {
    $api_base_in = trim($_POST['api_base'] ?? '');
    $m3u8_proxy_in = trim($_POST['m3u8_proxy'] ?? '');
    $category_aliases_in = trim($_POST['category_aliases'] ?? '');
    $category_hide_in = trim($_POST['category_hide'] ?? '');
    $source_aliases_in = trim($_POST['source_aliases'] ?? '');
    $m3u8_enabled_in = isset($_POST['m3u8_enabled']);
    $api_enabled_in = isset($_POST['api_enabled']);
    $settings = load_settings();
    if ($api_base_in !== '') $settings['api_base'] = $api_base_in; else unset($settings['api_base']);
    if ($m3u8_proxy_in !== '') $settings['m3u8_proxy'] = $m3u8_proxy_in; else unset($settings['m3u8_proxy']);
    $settings['m3u8_enabled'] = $m3u8_enabled_in ? true : false;
    $settings['api_enabled'] = $api_enabled_in ? true : false;
    $cat_alias = json_decode($category_aliases_in, true);
    if (is_array($cat_alias)) $settings['category_aliases'] = $cat_alias; else $settings['category_aliases'] = [];
    $cat_hide = array_filter(array_map(function($x){ return trim($x); }, preg_split('/[,\n]/', $category_hide_in)));
    $settings['category_hide'] = array_values($cat_hide);
    $src_alias = json_decode($source_aliases_in, true);
    if (is_array($src_alias)) $settings['source_aliases'] = $src_alias; else $settings['source_aliases'] = [];
    $save_msg = save_settings($settings) ? '保存成功' : '保存失败';
}

admin_page_start('resources');
?>
<form method="post">
  <input type="hidden" name="action" value="save_resources">
  <?php if ($save_msg): ?><div class="msg"><?= h($save_msg) ?></div><?php endif; ?>
  <div class="admin-section">
    <h3>资源接口</h3>
    <input class="admin-input" name="api_base" value="<?= h(api_base()) ?>" />
    <label style="display:block; margin-top:8px;"><input type="checkbox" name="api_enabled" <?= api_enabled() ? 'checked' : '' ?> /> 启用资源接口</label>
    <div class="note">禁用后将不发起新请求，尝试回退到旧缓存。</div>
  </div>
  <div class="admin-section">
    <h3>m3u8 播放接口</h3>
    <input class="admin-input" name="m3u8_proxy" value="<?= h(m3u8_proxy_base()) ?>" />
    <label style="display:block; margin-top:8px;"><input type="checkbox" name="m3u8_enabled" <?= m3u8_enabled() ? 'checked' : '' ?> /> 启用m3u8代理</label>
    <div class="note">关闭后m3u8尝试直接播放（兼容性受浏览器限制）。</div>
  </div>
  <div class="admin-section">
    <h3>分类替换控制（JSON 对象）</h3>
    <textarea class="admin-textarea" name="category_aliases"><?php echo h(json_encode(get_setting('category_aliases', []), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); ?></textarea>
    <div class="note">示例：{"国产剧":"华语剧"}</div>
  </div>
  <div class="admin-section">
    <h3>分类隐藏列表（名称，逗号或换行分隔）</h3>
    <textarea class="admin-textarea" name="category_hide"><?php echo h(implode("\n", get_setting('category_hide', []))); ?></textarea>
    <div class="note">示例：综艺, 纪录片</div>
  </div>
  <div class="admin-section">
    <h3>播放源名称映射（JSON 对象）</h3>
    <textarea class="admin-textarea" name="source_aliases"><?php echo h(json_encode(get_setting('source_aliases', []), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); ?></textarea>
    <div class="note">示例：{"lzm3u8":"电信线路","liangzi":"联通线路"}</div>
  </div>
  <div class="admin-actions">
    <button class="btn" type="submit">保存设置</button>
    <a class="btn secondary" href=<?= h(url_for('/')) ?>>返回首页</a>
  </div>
</form>
<?php admin_container_end(); admin_page_end(); ?>