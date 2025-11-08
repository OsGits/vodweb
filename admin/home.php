<?php
require_once __DIR__ . '/inc.php';
admin_require_login();

// 新增：缓存目录路径（供清理与统计共用）
$cacheDir = realpath(__DIR__ . '/../cache');

$home_msg = '';
$action = $_POST['action'] ?? '';
if ($action === 'clear_cache') {
  // 移除原先局部的 $cacheDir 定义，改用上方共享变量
  $deleted = 0; $failed = 0;
  if ($cacheDir && is_dir($cacheDir)) {
    $pattern = $cacheDir . DIRECTORY_SEPARATOR . '*.cache';
    foreach (glob($pattern) as $f) {
      if (is_file($f)) {
        if (@unlink($f)) { $deleted++; } else { $failed++; }
      }
    }
    $home_msg = '缓存清理完成：删除 ' . $deleted . ' 个，失败 ' . $failed . ' 个';
  } else {
    $home_msg = '缓存目录不可用';
  }
}

// 新增：统计当前缓存文件数量
$cache_count = 0;
if ($cacheDir && is_dir($cacheDir)) {
  $files = glob($cacheDir . DIRECTORY_SEPARATOR . '*.cache');
  if ($files !== false) { $cache_count = count($files); }
}

admin_page_start('home');
?>
<?php if ($home_msg): ?><div class="msg"><?= h($home_msg) ?></div><?php endif; ?>
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

<!-- 新增：缓存状态展示 -->
<div class="admin-section">
  <h3>缓存状态</h3>
  <div>当前缓存文件数量：<?= h((string)$cache_count) ?> 个</div>
</div>

<div class="admin-section">
  <h3>快捷操作</h3>
  <div class="admin-actions">
    <a class="btn" href="<?= h(url_for('/')) ?>" target="_blank">前往前台首页</a>
    <form method="post" style="display:inline-block; margin-left:8px;">
      <input type="hidden" name="action" value="clear_cache">
      <button class="btn secondary" type="submit">清空缓存</button>
    </form>
  </div>
</div>
<div class="admin-section">
  <h3>项目介绍与版权</h3>
  <div class="note">轻量级的聚合影视站，开源地址：</div>
  <a class="btn secondary" href="https://github.com/OsGits/vodweb" target="_blank" rel="noopener">https://github.com/OsGits/vodweb</a>
</div>
<?php admin_container_end(); admin_page_end(); ?>