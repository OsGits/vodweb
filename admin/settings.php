<?php
require_once __DIR__ . '/inc.php';
admin_require_login();

$save_msg = '';
$pwd_msg = '';
$action = $_POST['action'] ?? '';
if ($action === 'save_site') {
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

admin_page_start('settings');
?>
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
<?php admin_container_end(); admin_page_end(); ?>