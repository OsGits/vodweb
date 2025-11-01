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
  .btn { display:inline-block; padding:8px 14px; border-radius:6px; background:#2563eb; color:#fff; text-decoration:none; border:0; cursor:pointer; }
  .btn.secondary { background:#1f2937; color:#cbd5e1; }
  .msg { margin:8px 0; color:#22c55e; }
  .error { margin:8px 0; color:#ef4444; }
  .note { font-size: 12px; color:#94a3b8; }
  /* Tabs: collapsible & horizontal scroll */
  .tabs-wrapper { display:flex; align-items:center; gap:8px; margin-bottom: 12px; }
  .tabs-toggle { display:inline-flex; align-items:center; gap:6px; font-size:14px; }
  .tabs-collapsible { overflow:hidden; max-width:0; opacity:0; transition: max-width .25s ease, opacity .25s ease; flex: 1 1 auto; }
  .tabs-collapsible.expanded { max-width: 100%; opacity: 1; }
  .tabs-scroll { display:flex; gap:8px; border-bottom:1px solid #1f2937; padding-bottom:8px; overflow-x:auto; -webkit-overflow-scrolling: touch; white-space: nowrap; }
  .tabs-scroll.dragging { cursor: grabbing; }
  .tab { padding:8px 12px; border-radius:6px 6px 0 0; background:#1f2937; color:#cbd5e1; text-decoration:none; flex:0 0 auto; }
  .tab.active { background:#2563eb; color:#fff; }
  .tab:hover { background:#334155; color:#e5e7eb; }
  /* Make logout form not wrap and stick to right */
  .tabs-scroll form { margin-left:auto; flex:0 0 auto; }
  /* Scrollbar styling (webkit) */
  .tabs-scroll::-webkit-scrollbar { height: 8px; }
  .tabs-scroll::-webkit-scrollbar-thumb { background:#334155; border-radius:4px; }
  .tabs-scroll::-webkit-scrollbar-track { background:#0b1220; }
  @media (max-width: 640px) {
    .admin-container { margin: 12px; padding: 12px; }
    .tabs-collapsible.expanded { max-height: 80px; }
    .tab { padding:7px 10px; }
  }
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
    <div class="tabs-wrapper">
      <button class="tabs-toggle btn secondary" type="button" aria-expanded="false">展开设置</button>
      <div id="admin-tabs" class="tabs-collapsible collapsed">
        <div class="tabs-scroll">
          <a class="tab <?= ($active==='home')?'active':'' ?>" href="<?= h(url_for('/admin/home.php')) ?>">首页</a>
          <a class="tab <?= ($active==='settings')?'active':'' ?>" href="<?= h(url_for('/admin/settings.php')) ?>">设置</a>
          <a class="tab <?= ($active==='resources')?'active':'' ?>" href="<?= h(url_for('/admin/resources.php')) ?>">资源</a>
          <form method="post" action="<?= h(url_for('/admin/login.php')) ?>">
            <button class="btn secondary" type="submit" name="action" value="logout">退出登录</button>
          </form>
        </div>
      </div>
    </div>
    <script>
    (function(){
      var toggle = document.querySelector('.tabs-toggle');
      var collapsible = document.getElementById('admin-tabs');
      var scroll = collapsible ? collapsible.querySelector('.tabs-scroll') : null;
      var expanded = false;
      function setState(exp){
        expanded = !!exp;
        collapsible.classList.toggle('expanded', expanded);
        collapsible.classList.toggle('collapsed', !expanded);
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        toggle.textContent = expanded ? '收起设置' : '展开设置';
      }
      if (toggle && collapsible) {
        setState(false); // 默认折叠
        toggle.addEventListener('click', function(){ setState(!expanded); });
      }
      if (scroll) {
        // 鼠标滚轮横向滚动（提升PC端体验）
        scroll.addEventListener('wheel', function(e){
          if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
            scroll.scrollLeft += e.deltaY;
            e.preventDefault();
          }
        }, { passive:false });
        // 拖拽滑动（PC与移动端一致体验）
        var isDown = false, startX = 0, startLeft = 0;
        function pageX(ev){ return ev.touches ? ev.touches[0].pageX : ev.pageX; }
        function onDown(ev){ isDown = true; startX = pageX(ev); startLeft = scroll.scrollLeft; scroll.classList.add('dragging'); }
        function onMove(ev){ if(!isDown) return; var x = pageX(ev); var dx = startX - x; scroll.scrollLeft = startLeft + dx; }
        function onUp(){ isDown = false; scroll.classList.remove('dragging'); }
        scroll.addEventListener('mousedown', onDown);
        scroll.addEventListener('mousemove', onMove);
        scroll.addEventListener('mouseleave', onUp);
        scroll.addEventListener('mouseup', onUp);
        scroll.addEventListener('touchstart', onDown, { passive:true });
        scroll.addEventListener('touchmove', onMove, { passive:true });
        scroll.addEventListener('touchend', onUp, { passive:true });
      }
    })();
    </script>
    <?php
}

function admin_container_end() { echo '</div>'; }

function admin_page_start($activeTab) {
    admin_head_html_start();
    admin_container_start();
    admin_tabs($activeTab);
}

function admin_page_end() { echo "</body>\n</html>"; }