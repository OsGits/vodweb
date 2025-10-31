<?php
$cfg = require __DIR__ . '/../../config.php';
$theme = $cfg['theme'] ?? 'default';
$file = __DIR__ . '/theme.json';
$vars = [
  'primary' => '#1f6feb',
  'bg' => '#0d1117',
  'surface' => '#161b22',
  'text' => '#c9d1d9',
  'muted' => '#8b949e',
  'accent' => '#58a6ff',
];
if (file_exists($file)) {
  $json = json_decode(file_get_contents($file), true);
  if (is_array($json)) {
    $vars = array_merge($vars, $json);
  }
}
?>
<style>
:root{
  --primary: <?php echo $vars['primary']; ?>;
  --bg: <?php echo $vars['bg']; ?>;
  --surface: <?php echo $vars['surface']; ?>;
  --text: <?php echo $vars['text']; ?>;
  --muted: <?php echo $vars['muted']; ?>;
  --accent: <?php echo $vars['accent']; ?>;
}
body{background:var(--bg);color:var(--text)}
.site-header{background:var(--surface)}
.brand{color:#fff}
.search button{background:var(--primary)}
.section-head .more{color:var(--accent)}
.btn{background:var(--primary)}
</style>