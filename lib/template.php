<?php
require_once __DIR__ . '/utils.php';

function render(string $tpl, array $data = []): void {
    $cfg = require __DIR__ . '/../config.php';
    $theme = $cfg['theme'] ?? 'default';
    $base = __DIR__ . '/../themes/' . $theme;

    $header = $base . '/layout/header.php';
    $footer = $base . '/layout/footer.php';
    $file = $base . '/' . trim($tpl, '/');

    if (!file_exists($file)) {
        http_response_code(404);
        echo 'Template not found: ' . e($tpl);
        return;
    }

    extract($data);
    include $header;
    include $file;
    include $footer;
}