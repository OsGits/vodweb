<?php
// Bridge to new modular admin
require_once __DIR__ . '/admin/index.php';

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
$pwd_msg = '';
if ($_SESSION['admin_logged']) {
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
    } elseif ($action === 'save_site') {
        $site_name_in = trim($_POST['site_name'] ?? '');
        if ($site_name_in !== '') {
            $settings = load_settings();
            $settings['site_name'] = $site_name_in;
            $save_msg = save_settings($settings) ? '站点名称已更新' : '保存失败';
        } else {
            $save_msg = '站点名称不能为空';
        }
    } elseif ($action === 'change_password') {
        $current = trim($_POST['current_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');
        $new_user = trim($_POST['new_username'] ?? '');
        $confUser = (string)get_setting('admin_user', 'admin');
        $confPass = (string)get_setting('admin_pass', 'admin');
        if ($current !== $confPass) {
            $pwd_msg = '当前密码不正确';
        } elseif ($new === '' || strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
            $pwd_msg = '新密码不符合安全规范（至少8位，包含字母和数字）';
        } elseif ($new !== $confirm) {
            $pwd_msg = '两次输入的新密码不一致';
        } else {
            $settings = load_settings();
            if ($new_user !== '') { $settings['admin_user'] = $new_user; }
            $settings['admin_pass'] = $new;
            $pwd_msg = save_settings($settings) ? '账号/密码已更新' : '保存失败';
        }
    }
}

$logged = $_SESSION['admin_logged'];
$tab = $_GET['tab'] ?? 'home';

?><!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title>后台管理 - <?= h(site_name()) ?></title>
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
  .tabs { display:flex; gap:8px; border-bottom:1px solid #1f2937; margin-bottom:12px; }
  .tab { padding:8px 12px; border-radius:6px 6px 0 0; background:#1f2937; color:#cbd5e1; text-decoration:none; }
  .tab.active { background:#2563eb; color:#fff; }
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
    <div class="tabs">
      <a class="tab <?= ($tab==='home')?'active':'' ?>" href="<?= h(url_for('/admin.php', ['tab'=>'home'])) ?>">首页</a>
      <a class="tab <?= ($tab==='settings')?'active':'' ?>" href="<?= h(url_for('/admin.php', ['tab'=>'settings'])) ?>">设置</a>
      <a class="tab <?= ($tab==='resources')?'active':'' ?>" href="<?= h(url_for('/admin.php', ['tab'=>'resources'])) ?>">资源管理</a>
      <form method="post" style="margin-left:auto">
        <button class="btn secondary" type="submit" name="action" value="logout">退出登录</button>
      </form>
    </div>

    <?php if ($tab === 'home'): ?>
      <div class="admin-section">
        <h3>系统时间</h3>
        <div id="clock" style="font-size:28px; font-weight:600;">--</div>
        <script>
          function pad(n){ return n<10 ? '0'+n : ''+n; }
          function tick(){
            var d=new Date();
            var s=d.getFullYear()+"-"+pad(d.getMonth()+1)+"-"+pad(d.getDate())+" "+pad(d.getHours())+":"+pad(d.getMinutes())+":"+pad(d.getSeconds());
            document.getElementById('clock').textContent=s;
          }
          tick(); setInterval(tick, 1000);
        </script>
      </div>
    <?php elseif ($tab === 'settings'): ?>
      <?php if ($save_msg): ?><div class="msg"><?= h($save_msg) ?></div><?php endif; ?>
      <?php if ($pwd_msg): ?><div class="msg"><?= h($pwd_msg) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="save_site">
        <div class="admin-section">
          <h3>网站名称</h3>
          <input class="admin-input" name="site_name" value="<?= h(site_name()) ?>" />
          <div class="note">前台标题与Logo将显示此名称。</div>
        </div>
        <div class="admin-actions">
          <button class="btn" type="submit">保存</button>
        </div>
      </form>

      <form method="post" style="margin-top:16px;">
        <input type="hidden" name="action" value="change_password">
        <div class="admin-section">
          <h3>账号与密码修改</h3>
          <div class="note">当前账号：<?= h(get_setting('admin_user','admin')) ?></div>
          <label>当前密码</label>
          <input class="admin-input" name="current_password" type="password" />
          <label style="margin-top:8px;">新账号（可选）</label>
          <input class="admin-input" name="new_username" />
          <label style="margin-top:8px;">新密码</label>
          <input class="admin-input" name="new_password" type="password" />
          <label style="margin-top:8px;">确认新密码</label>
          <input class="admin-input" name="confirm_password" type="password" />
          <div class="note">密码需至少8位，包含字母与数字。</div>
        </div>
        <div class="admin-actions">
          <button class="btn" type="submit">修改密码</button>
        </div>
      </form>
    <?php else: ?>
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
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>