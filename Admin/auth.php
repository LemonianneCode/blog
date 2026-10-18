<?php
session_start();
require_once __DIR__ . '/../includes/no_cache.php';

if (($_SESSION['is_admin'] ?? false) !== true) {
    header('Location: ../index.php');
    exit;
}
