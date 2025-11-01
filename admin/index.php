<?php
require_once __DIR__ . '/inc.php';
if (admin_is_logged()) {
    header('Location: ' . url_for('/admin/home.php'));
} else {
    header('Location: ' . url_for('/admin/login.php'));
}
exit;