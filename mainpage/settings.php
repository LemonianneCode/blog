<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Settings</title>
</head>
<body>
    <main class="profile-container">
        <nav class="profile-nav" aria-label="Main navigation">
            <a href="index.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="settings.php" aria-current="page">Settings</a>
            <a class="profile-logout" href="index.php?logout=1">Log out</a>
        </nav>

        <section class="profile-card" aria-labelledby="settings-heading">
            <h1 id="settings-heading">Settings</h1>
            <p>There are no settings to configure yet.</p>
        </section>
    </main>
</body>
</html>
