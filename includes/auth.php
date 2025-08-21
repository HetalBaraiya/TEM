<?php
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
function require_role($roles) {
    if (!in_array($_SESSION['user']['role'], (array)$roles)) {
        header("HTTP/1.1 403 Forbidden");
        echo "Access denied.";
        exit;
    }
}
?>
