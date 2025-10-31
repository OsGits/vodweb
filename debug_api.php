<?php
require_once __DIR__ . '/lib/api.php';
header('Content-Type: text/plain; charset=utf-8');
list($data, $err) = get_vod_list(['pg' => 1]);
if ($err) {
    echo "Error: $err\n";
}
var_export($data);
?>