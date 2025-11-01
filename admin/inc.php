<?php
require_once __DIR__ . '/../config.php';

function admin_is_logged() {
    return !empty($_SESSION['admin_logged']);
}

function admin_require_login() {
    if (!admin_is_logged()) {
        header('Location: ' . url_for('/admin/login.php'));
        exit;
    }
}

function admin_head_html_start() {
    ?>
<!doctype html>
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
<?php }

function admin_head_html_end() { echo ""; }

function admin_container_start() {
    echo '<div class="admin-container">';
    echo '<div class="admin-title">后台管理</div>';
}

function admin_tabs($active) {
    ?>
    <div class="tabs">
      <a class="tab <?= ($active==='home')?'active':'' ?>" href="<?= h(url_for('/admin/home.php')) ?>">首页</a>
      <a class="tab <?= ($active==='settings')?'active':'' ?>" href="<?= h(url_for('/admin/settings.php')) ?>">设置</a>
      <a class="tab <?= ($active==='resources')?'active':'' ?>" href="<?= h(url_for('/admin/resources.php')) ?>">资源管理</a>
      <form method="post" action="<?= h(url_for('/admin/login.php')) ?>" style="margin-left:auto">
        <button class="btn secondary" type="submit" name="action" value="logout">退出登录</button>
      </form>
    </div>
    <?php
}

function admin_container_end() { echo '</div>'; }

function admin_page_start($activeTab) {
    admin_head_html_start();
    admin_container_start();
    admin_tabs($activeTab);
}

function admin_page_end() { echo "</body>\n</html>"; }