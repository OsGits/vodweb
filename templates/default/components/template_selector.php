<?php
// 模板选择器组件
?>
<div class="template-selector">
  <h3>选择模板</h3>
  <?php
  // 获取所有可用的模板
  $templates = [];
  $template_dir = __DIR__ . '/../../..';
  
  if (is_dir($template_dir)) {
    $dirs = scandir($template_dir);
    foreach ($dirs as $dir) {
      if ($dir != '.' && $dir != '..' && $dir != '.config' && is_dir($template_dir . '/' . $dir)) {
        // 检查是否有配置文件
        $config_file = $template_dir . '/' . $dir . '/config.php';
        if (file_exists($config_file)) {
          $config = [];
          include $config_file;
          $templates[] = [
            'name' => $dir,
            'title' => $config['title'] ?? $dir,
            'version' => $config['version'] ?? '1.0.0',
            'description' => $config['description'] ?? '',
            'author' => $config['author'] ?? ''
          ];
        }
      }
    }
  }
  >
  <?php if (!empty($templates)): ?>
    <form action="" method="post">
      <input type="hidden" name="action" value="set_template">
      <div class="template-list">
        <?php foreach ($templates as $template): 
          $is_active = ($template['name'] == ($current_template ?? template_name()));
        ?>
          <div class="template-item " . ($is_active ? 'active' : '') . "">
            <label>
              <input type="radio" name="template" value="<?= h($template['name']) ?>" <?= $is_active ? 'checked' : '' ?>>
              <div class="template-info">
                <strong><?= h($template['title']) ?></strong>
                <span class="template-version"><?= h($template['version']) ?></span>
                <p class="template-description"><?= h($template['description']) ?></p>
                <span class="template-author">作者：<?= h($template['author']) ?></span>
              </div>
            </label>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">保存设置</button>
      </div>
    </form>
  <?php else: ?>
    <p>未找到可用的模板。</p>
  <?php endif; ?>
  <style>
    .template-selector h3 { margin-bottom: 15px; }
    .template-list { display: flex; flex-direction: column; gap: 10px; }
    .template-item { border: 2px solid #1f2937; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.3s ease; }
    .template-item.active { border-color: #2563eb; background-color: rgba(37, 99, 235, 0.1); }
    .template-item:hover { border-color: #4f46e5; }
    .template-item input[type="radio"] { margin-right: 10px; }
    .template-info { display: inline-block; }
    .template-info strong { font-size: 16px; display: block; }
    .template-version { font-size: 12px; color: #9ca3af; margin-left: 5px; }
    .template-description { margin: 8px 0; color: #9ca3af; font-size: 14px; }
    .template-author { font-size: 12px; color: #6b7280; }
    .form-actions { margin-top: 20px; }
    .btn { padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 500; }
    .btn-primary { background-color: #2563eb; color: white; border: none; }
    .btn-primary:hover { background-color: #1d4ed8; }
  </style>
