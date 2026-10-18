<?php
session_start();
require_once __DIR__ . '/../includes/no_cache.php';
include('../includes/db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$stmt = $dbconn->prepare(
    'SELECT FNAME, MNAME, LNAME, GENDER, BDAY, USERNAME, PASSWORD
     FROM acc_info
     WHERE ID = ?'
);

if (!$stmt) {
    http_response_code(500);
    exit('Unable to load your account settings.');
}

$stmt->bind_param('i', $userId);
if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    exit('Unable to load your account settings.');
}

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}

if (empty($_SESSION['settings_csrf_token'])) {
    $_SESSION['settings_csrf_token'] = bin2hex(random_bytes(32));
}

$message = $_SESSION['settings_message'] ?? '';
unset($_SESSION['settings_message']);
$messageType = $message !== '' ? 'success' : 'error';
$formValues = $user;

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'delete_account'
) {
    $messageType = 'error';
    $csrfToken = $_POST['csrf_token'] ?? null;
    $deleteConfirmation = $_POST['delete_confirmation'] ?? '';

    if (
        !is_string($csrfToken) ||
        !hash_equals($_SESSION['settings_csrf_token'], $csrfToken)
    ) {
        $message = 'Your session has expired. Please reload the page and try again.';
    } elseif (!is_string($deleteConfirmation) || $deleteConfirmation !== 'DELETE') {
        $message = 'Type DELETE exactly to confirm account deletion.';
    } else {
        $stmt = $dbconn->prepare('DELETE FROM acc_info WHERE ID = ?');
        if (!$stmt) {
            http_response_code(500);
            exit('Unable to delete your account.');
        }

        $stmt->bind_param('i', $userId);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            $stmt->close();
            http_response_code(500);
            exit('Unable to delete your account.');
        }
        $stmt->close();

        session_unset();
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') !== 'delete_account'
) {
    $postString = static function (string $key): string {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? $value : '';
    };
    $formValues = [
        'FNAME' => trim($postString('fname')),
        'MNAME' => trim($postString('mname')),
        'LNAME' => trim($postString('lname')),
        'GENDER' => trim($postString('gender')),
        'BDAY' => trim($postString('bday')),
        'USERNAME' => trim($postString('username')),
    ];
    $currentPassword = $postString('current_password');
    $newPassword = $postString('new_password');
    $confirmPassword = $postString('confirm_password');
    $csrfToken = $_POST['csrf_token'] ?? null;
    $messageType = 'error';

    if (
        !is_string($csrfToken) ||
        !hash_equals($_SESSION['settings_csrf_token'], $csrfToken)
    ) {
        $message = 'Your session has expired. Please reload the page and try again.';
    } elseif (
        !preg_match('/\A\p{L}+\z/u', $formValues['FNAME']) ||
        !preg_match('/\A\p{L}+\z/u', $formValues['MNAME']) ||
        !preg_match('/\A\p{L}+\z/u', $formValues['LNAME'])
    ) {
        $message = 'Names must contain letters only.';
    } elseif (
        strlen($formValues['USERNAME']) > 100 ||
        $formValues['USERNAME'] === ''
    ) {
        $message = 'Enter a username with no more than 100 characters.';
    } elseif (!in_array($formValues['GENDER'], ['Male', 'Female'], true)) {
        $message = 'Choose a valid gender.';
    } else {
        $birthday = DateTime::createFromFormat('!Y-m-d', $formValues['BDAY']);
        $birthdayErrors = DateTime::getLastErrors();

        if (
            !$birthday ||
            ($birthdayErrors !== false && ($birthdayErrors['warning_count'] || $birthdayErrors['error_count'])) ||
            $birthday->format('Y-m-d') !== $formValues['BDAY']
        ) {
            $message = 'Enter a valid date of birth.';
        } else {
            $stmt = $dbconn->prepare('SELECT ID FROM acc_info WHERE USERNAME = ? AND ID <> ?');
            if (!$stmt) {
                http_response_code(500);
                exit('Unable to update your account settings.');
            }

            $stmt->bind_param('si', $formValues['USERNAME'], $userId);
            if (!$stmt->execute()) {
                $stmt->close();
                http_response_code(500);
                exit('Unable to update your account settings.');
            }
            $usernameTaken = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($usernameTaken) {
                $message = 'That username is already in use.';
            } elseif (
                $currentPassword !== '' ||
                $newPassword !== '' ||
                $confirmPassword !== ''
            ) {
                if (
                    $currentPassword === '' ||
                    $newPassword === '' ||
                    $confirmPassword === ''
                ) {
                    $message = 'Enter your current password and both new password fields to change your password.';
                } elseif ((string) $user['PASSWORD'] !== (string) crc32($currentPassword)) {
                    $message = 'Your current password is incorrect.';
                } elseif (strlen($newPassword) < 8) {
                    $message = 'Your new password must be at least 8 characters.';
                } elseif ($newPassword !== $confirmPassword) {
                    $message = 'The new passwords do not match.';
                } else {
                    $passwordHash = (string) crc32($newPassword);
                    $stmt = $dbconn->prepare(
                        'UPDATE acc_info
                         SET FNAME = ?, MNAME = ?, LNAME = ?, GENDER = ?, BDAY = ?,
                             USERNAME = ?, PASSWORD = ?
                         WHERE ID = ?'
                    );
                    if (!$stmt) {
                        http_response_code(500);
                        exit('Unable to update your account settings.');
                    }
                    $stmt->bind_param(
                        'sssssssi',
                        $formValues['FNAME'],
                        $formValues['MNAME'],
                        $formValues['LNAME'],
                        $formValues['GENDER'],
                        $formValues['BDAY'],
                        $formValues['USERNAME'],
                        $passwordHash,
                        $userId
                    );

                    if (!$stmt->execute()) {
                        $stmt->close();
                        http_response_code(500);
                        exit('Unable to update your account settings.');
                    }
                    $stmt->close();
                    $_SESSION['settings_message'] = 'Your account settings have been updated.';
                    header('Location: settings.php');
                    exit;
                }
            } else {
                $stmt = $dbconn->prepare(
                    'UPDATE acc_info
                     SET FNAME = ?, MNAME = ?, LNAME = ?, GENDER = ?, BDAY = ?,
                         USERNAME = ?
                     WHERE ID = ?'
                );
                if (!$stmt) {
                    http_response_code(500);
                    exit('Unable to update your account settings.');
                }
                $stmt->bind_param(
                    'ssssssi',
                    $formValues['FNAME'],
                    $formValues['MNAME'],
                    $formValues['LNAME'],
                    $formValues['GENDER'],
                    $formValues['BDAY'],
                    $formValues['USERNAME'],
                    $userId
                );

                if (!$stmt->execute()) {
                    $stmt->close();
                    http_response_code(500);
                    exit('Unable to update your account settings.');
                }
                $stmt->close();
                $_SESSION['settings_message'] = 'Your account settings have been updated.';
                header('Location: settings.php');
                exit;
            }
        }
    }
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
            <p>Update your personal information, username, or password.</p>
            <?php if ($message !== ''): ?>
                <p role="<?php echo $messageType === 'success' ? 'status' : 'alert'; ?>" aria-live="polite"><?php echo e($message); ?></p>
            <?php endif; ?>
            <form method="POST" action="settings.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['settings_csrf_token']); ?>">

                <label for="fname">First name</label>
                <input type="text" id="fname" name="fname" maxlength="100" value="<?php echo e($formValues['FNAME']); ?>" required>

                <label for="mname">Middle name</label>
                <input type="text" id="mname" name="mname" maxlength="100" value="<?php echo e($formValues['MNAME']); ?>" required>

                <label for="lname">Last name</label>
                <input type="text" id="lname" name="lname" maxlength="100" value="<?php echo e($formValues['LNAME']); ?>" required>

                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php echo strtolower($formValues['GENDER']) === 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo strtolower($formValues['GENDER']) === 'female' ? 'selected' : ''; ?>>Female</option>
                </select>

                <label for="bday">Date of birth</label>
                <input type="date" id="bday" name="bday" value="<?php echo e($formValues['BDAY']); ?>" required>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="100" autocomplete="username" value="<?php echo e($formValues['USERNAME']); ?>" required>

                <h2>Change password</h2>
                <p>Leave these fields blank if you do not want to change your password.</p>
                <label for="current-password">Current password</label>
                <input type="password" id="current-password" name="current_password" autocomplete="current-password">

                <label for="new-password">New password</label>
                <input type="password" id="new-password" name="new_password" minlength="8" autocomplete="new-password">

                <label for="confirm-password">Confirm new password</label>
                <input type="password" id="confirm-password" name="confirm_password" minlength="8" autocomplete="new-password">

                <button type="submit">Save changes</button>
            </form>

            <section class="account-deletion" aria-labelledby="delete-account-heading">
                <h2 id="delete-account-heading">Delete account</h2>
                <p>This permanently removes your profile and login, but leaves posts you have shared. Type DELETE below to confirm; this cannot be undone.</p>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['settings_csrf_token']); ?>">
                    <input type="hidden" name="action" value="delete_account">
                    <label for="delete-confirmation">Type DELETE to confirm</label>
                    <input
                        type="text"
                        id="delete-confirmation"
                        name="delete_confirmation"
                        autocomplete="off"
                        required
                    >
                    <button type="submit">Permanently delete account</button>
                </form>
            </section>
        </section>
    </main>
    <script src="../includes/session-page.js"></script>
</body>
</html>
