<?php
session_start();

if (($_SESSION['is_admin'] ?? false) !== true) {
    header('Location: ../index.php');
    exit;
}
