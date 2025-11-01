<?php
require_once __DIR__ . '/inc.php';
$active = 'banners';
admin_require_login();

$settings = load_settings();
$banners = get_setting('banners', []);

$uploadDir = realpath(__DIR__ . '/..');
$uploadRel = '/uploads/banners';
$uploadPath = $uploadDir . $uploadRel;
if (!is_dir($uploadPath)) {
    @mkdir($uploadDir . '/uploads', 0777, true);
    @mkdir($uploadPath, 0777, true);
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add_banner') {
        $filmName = trim($_POST['film_name'] ?? '');
        if ($filmName === '') {
            $error = '影片名不能为空';
        } elseif (!isset($_FILES['banner_image']) || $_FILES['banner_image']['error'] !== UPLOAD_ERR_OK) {
            $error = '请选择要上传的图片';
        } else {
            $file = $_FILES['banner_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp','svg'];
            if (!in_array($ext, $allowed)) {
                $error = '不支持的图片格式（仅支持: jpg、jpeg、png、gif、webp、svg）';
            } else {
                $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                $uniq = date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6);
                $filename = $safeBase . '_' . $uniq . '.' . $ext;
                $target = $uploadPath . DIRECTORY_SEPARATOR . $filename;
                if (!move_uploaded_file($file['tmp_name'], $target)) {
                    $error = '文件保存失败，请检查目录权限';
                } else {
                    $publicUrl = $uploadRel . '/' . $filename; // e.g. /uploads/banners/xxx.png
                    $banners[] = [
                        'image' => $publicUrl,
                        'name'  => $filmName,
                    ];
                    $settings['banners'] = $banners;
                    save_settings($settings);
                    $message = '横幅已上传并保存';
                }
            }
        }
    } elseif ($action === 'delete_banner') {
        $idx = intval($_POST['index'] ?? -1);
        if ($idx < 0 || $idx >= count($banners)) {
            $error = '非法索引';
        } else {
            $toDelete = $banners[$idx];
            $imgPath = $uploadDir . $toDelete['image'];
            if (is_file($imgPath)) {
                @unlink($imgPath);
            }
            array_splice($banners, $idx, 1);
            $settings['banners'] = $banners;
            save_settings($settings);
            $message = '横幅已删除';
        }
    }
}

?>
<?php admin_page_start('banners'); ?>
<?php if ($message): ?><div class="msg"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

<div class="admin-section">
  <h3>上传横幅</h3>
  <form method="post" enctype="multipart/form-data">
    <div class="field">
      <label>图片文件</label>
      <input class="admin-input" type="file" name="banner_image" accept="image/*" required />
    </div>
    <div class="field">
      <label>影片名</label>
      <input class="admin-input" type="text" name="film_name" placeholder="用于跳转搜索，例如：流浪地球" required />
    </div>
    <div class="admin-actions">
      <button class="btn" type="submit" name="action" value="add_banner">上传并保存</button>
    </div>
  </form>
</div>

<div class="admin-section">
  <h3>横幅列表</h3>
  <?php if (empty($banners)): ?>
    <div class="note">暂无横幅</div>
  <?php else: ?>
    <div class="masonry banners">
      <?php foreach ($banners as $i => $b): ?>
        <div class="banner-item">
          <a class="thumb" href="<?= h(url_for('/search.php') . '?wd=' . rawurlencode($b['name'])) ?>" target="_blank" title="跳转搜索：<?= h($b['name']) ?>">
            <img src="<?= h($b['image']) ?>" alt="<?= h($b['name']) ?>" />
          </a>
          <div class="caption">
            <div class="name">影片名：<?= h($b['name']) ?></div>
            <div class="admin-actions">
              <form method="post" onsubmit="return confirm('确认删除该横幅？')" style="display:inline-block;">
                <input type="hidden" name="index" value="<?= h($i) ?>" />
                <button class="btn secondary" type="submit" name="action" value="delete_banner">删除</button>
              </form>
              <a class="btn" href="<?= h(url_for('/search.php') . '?wd=' . rawurlencode($b['name'])) ?>" target="_blank">查看</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<style>
.admin-section .field { margin-bottom: 10px; }
.admin-section .field label { display:block; margin-bottom: 6px; font-weight: 600; }
.masonry.banners { display:flex; flex-wrap: wrap; gap: 12px; }
.banner-item .thumb { display:block; width: 320px; height: 120px; overflow:hidden; border-radius: 6px; }
.banner-item img { width: 100%; height: 100%; object-fit: cover; display:block; }
.banner-item .caption { margin-top: 8px; display:flex; align-items:center; justify-content: space-between; }
.banner-item .name { font-size: 14px; }
.banner-item .admin-actions .btn { margin-right: 8px; }
</style>

<?php admin_container_end(); admin_page_end(); ?>