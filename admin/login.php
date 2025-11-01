<?php
require_once __DIR__ . '/inc.php';

$action = $_POST['action'] ?? '';
$login_error = '';
if ($action === 'login') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    $confUser = (string)get_setting('admin_user', 'admin');
    $confPass = (string)get_setting('admin_pass', 'admin');
    if ($u === $confUser && $p === $confPass) {
        $_SESSION['admin_logged'] = true;
        header('Location: ' . url_for('/admin/home.php'));
        exit;
    } else {
        $login_error = '账号或密码错误';
    }
} elseif ($action === 'logout') {
    $_SESSION['admin_logged'] = false;
    header('Location: ' . url_for('/admin/login.php'));
    exit;
}

if (admin_is_logged()) {
    header('Location: ' . url_for('/admin/home.php'));
    exit;
}

admin_head_html_start();
?>
<div class="admin-container">
  <div class="admin-title">后台管理</div>
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
</div>
<?php admin_page_end(); ?>