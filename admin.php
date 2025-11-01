<?php
require_once __DIR__ . '/config.php';

// Simple login
if (!isset($_SESSION['admin_logged'])) {
    $_SESSION['admin_logged'] = false;
}

$action = $_POST['action'] ?? '';
if ($action === 'login') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    $confUser = (string)get_setting('admin_user', 'admin');
    $confPass = (string)get_setting('admin_pass', 'admin');
    if ($u === $confUser && $p === $confPass) {
        $_SESSION['admin_logged'] = true;
        header('Location: ' . url_for('/admin.php'));
        exit;
    } else {
        $login_error = '账号或密码错误';
    }
} elseif ($action === 'logout') {
    $_SESSION['admin_logged'] = false;
    header('Location: ' . url_for('/admin.php'));
    exit;
}

// Save settings
$save_msg = '';
if ($_SESSION['admin_logged'] && $action === 'save_settings') {
    $api_base_in = trim($_POST['api_base'] ?? '');
    $m3u8_proxy_in = trim($_POST['m3u8_proxy'] ?? '');
    $category_aliases_in = trim($_POST['category_aliases'] ?? '');
    $category_hide_in = trim($_POST['category_hide'] ?? '');
    $source_aliases_in = trim($_POST['source_aliases'] ?? '');

    $settings = load_settings();
    if ($api_base_in !== '') $settings['api_base'] = $api_base_in; else unset($settings['api_base']);
    if ($m3u8_proxy_in !== '') $settings['m3u8_proxy'] = $m3u8_proxy_in; else unset($settings['m3u8_proxy']);

    $cat_alias = json_decode($category_aliases_in, true);
    if (is_array($cat_alias)) $settings['category_aliases'] = $cat_alias; else $settings['category_aliases'] = [];

    $cat_hide = array_filter(array_map(function($x){ return trim($x); }, preg_split('/[,\n]/', $category_hide_in)));
    $settings['category_hide'] = array_values($cat_hide);


    $src_alias = json_decode($source_aliases_in, true);
    if (is_array($src_alias)) $settings['source_aliases'] = $src_alias; else $settings['source_aliases'] = [];

    if (save_settings($settings)) {
        $save_msg = '保存成功';
    } else {
        $save_msg = '保存失败';
    }
}

$logged = $_SESSION['admin_logged'];

?><!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title>后台管理 - <?= h(SITE_NAME) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/assets/style.css">
<style>
  .admin-container { max-width: 960px; margin: 20px auto; background: #0b1220; border:1px solid #1f2937; border-radius:8px; padding:16px; color:#cbd5e1; }
  .admin-title { font-size: 22px; margin-bottom: 12px; }
  .admin-section { margin: 16px 0; }
  .admin-section h3 { margin: 8px 0; }
  .admin-input { width: 100%; padding: 8px; border-radius: 6px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; }
  .admin-textarea { width: 100%; min-height: 120px; padding: 8px; border-radius: 6px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; }
  .admin-actions { margin-top: 12px; }
  .btn { display:inline-block; padding:8px 14px; border-radius:6px; background:#2563eb; color:#fff; text-decoration:none; border:0; }
  .btn.secondary { background:#1f2937; color:#cbd5e1; }
  .msg { margin:8px 0; color:#22c55e; }
  .error { margin:8px 0; color:#ef4444; }
  .note { font-size: 12px; color:#94a3b8; }
</style>
</head>
<body>
<div class="admin-container">
  <div class="admin-title">后台管理</div>
  <?php if (!$logged): ?>
    <?php if (!empty($login_error)): ?><div class="error"><?= h($login_error) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="action" value="login">
      <div class="admin-section">
        <label>账号</label>
        <input class="admin-input" name="username" value="admin" />
      </div>
      <div class="admin-section">
        <label>密码</label>
        <input class="admin-input" name="password" type="password" value="admin" />
      </div>
      <div class="admin-actions">
        <button class="btn" type="submit">登录</button>
      </div>
    </form>
  <?php else: ?>
    <form method="post">
      <input type="hidden" name="action" value="save_settings">
      <?php if ($save_msg): ?><div class="msg"><?= h($save_msg) ?></div><?php endif; ?>
      <div class="admin-section">
        <h3>资源接口</h3>
        <input class="admin-input" name="api_base" value="<?= h(api_base()) ?>" />
        <div class="note">示例：<?= h(API_BASE) ?></div>
      </div>
      <div class="admin-section">
        <h3>m3u8 播放接口</h3>
        <input class="admin-input" name="m3u8_proxy" value="<?= h(m3u8_proxy_base()) ?>" />
        <div class="note">示例：http://anyn.cc/m3u8/?url=</div>
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
        <button class="btn secondary" type="submit" name="action" value="logout">退出登录</button>
        <a class="btn secondary" href=<?= h(url_for('/')) ?>>返回首页</a>
      </div>
    </form>
  <?php endif; ?>
</div>
</body>
</html>