<?php
session_start();
require_once __DIR__ . '/../includes/no_cache.php';
require_once __DIR__ . '/../includes/db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$stmt = $dbconn->prepare(
    'SELECT ID, CONTENT, IMAGE, CREATED_AT
     FROM announcement_posts
     ORDER BY CREATED_AT DESC, ID DESC'
);
if (!$stmt || !$stmt->execute()) {
    http_response_code(500);
    exit('Unable to load announcements.');
}
$announcements = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="main.css">
    <title>Announcements</title>
</head>
<body>
    <main class="mainpage-container">
        <nav class="profile-nav" aria-label="Main navigation">
            <a href="index.php">Home</a>
            <a href="announcements.php" aria-current="page">Announcements</a>
            <a href="profile.php">Profile</a>
            <a href="settings.php">Settings</a>
            <a class="profile-logout" href="index.php?logout=1">Log out</a>
        </nav>

        <section class="container announcements-page" aria-labelledby="announcements-heading">
            <h1 id="announcements-heading">Announcements</h1>
            <?php if ($announcements->num_rows > 0): ?>
                <?php while ($announcement = $announcements->fetch_assoc()): ?>
                    <article class="announcement-feed-card">
                        <time datetime="<?php echo e(date('c', strtotime($announcement['CREATED_AT']))); ?>">
                            <?php echo e(date('F j, Y \a\t g:i a', strtotime($announcement['CREATED_AT']))); ?>
                        </time>
                        <p><?php echo nl2br(e($announcement['CONTENT'])); ?></p>
                        <?php if (!empty($announcement['IMAGE'])): ?>
                            <img src="../imgstorage/<?php echo e(basename($announcement['IMAGE'])); ?>" alt="Announcement image">
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <article class="announcement-feed-card">
                    <p>No announcements yet. Check back later for updates.</p>
                </article>
            <?php endif; ?>
        </section>
    </main>
    <?php $stmt->close(); ?>
    <script src="../includes/session-page.js"></script>
</body>
</html>
