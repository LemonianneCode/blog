<?php
session_start();
include('../includes/db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$stmt = $dbconn->prepare(
    'SELECT FNAME, MNAME, LNAME, GENDER, BDAY, USERNAME
     FROM acc_info
     WHERE ID = ?'
);

if (!$stmt) {
    http_response_code(500);
    exit('Unable to load your profile.');
}

$stmt->bind_param('i', $userId);
if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    exit('Unable to load your profile.');
}

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Your Profile</title>
</head>
<body>
    <main class="profile-container">
        <nav class="profile-nav" aria-label="Main navigation">
            <a href="index.php">Home</a>
            <a href="profile.php" aria-current="page">Profile</a>
            <a href="settings.php">Settings</a>
            <a class="profile-logout" href="index.php?logout=1">Log out</a>
        </nav>

        <section class="profile-card" aria-labelledby="profile-heading">
            <h1 id="profile-heading">Your Profile</h1>
            <dl class="profile-details">
                <div>
                    <dt>First name</dt>
                    <dd><?php echo e($user['FNAME']); ?></dd>
                </div>
                <div>
                    <dt>Middle name</dt>
                    <dd><?php echo e($user['MNAME']); ?></dd>
                </div>
                <div>
                    <dt>Last name</dt>
                    <dd><?php echo e($user['LNAME']); ?></dd>
                </div>
                <div>
                    <dt>Username</dt>
                    <dd><?php echo e($user['USERNAME']); ?></dd>
                </div>
                <div>
                    <dt>Gender</dt>
                    <dd><?php echo e($user['GENDER']); ?></dd>
                </div>
                <div>
                    <dt>Date of birth</dt>
                    <dd><?php echo e($user['BDAY'] ?: 'Not provided'); ?></dd>
                </div>
            </dl>
        </section>
    </main>
</body>
</html>