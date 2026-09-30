<?php
session_start();
include ('includes/db_conn.php');

$message = '';
$passwordReset = false;
$accountVerified = isset($_SESSION['reset_user_id']);

if (isset($_POST['verify_account'])) {
    unset($_SESSION['reset_user_id']);
    $accountVerified = false;

    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['fname'] ?? '');
    $middleName = trim($_POST['mname'] ?? '');
    $lastName = trim($_POST['lname'] ?? '');
    $birthday = $_POST['birthday'] ?? '';

    if ($username === '' || $firstName === '' || $middleName === '' || $lastName === '' || $birthday === '') {
        $message = 'Please complete every field.';
    } else {
        $stmt = $dbconn->prepare(
            'SELECT ID FROM acc_info
             WHERE USERNAME = ? AND FNAME = ? AND MNAME = ? AND LNAME = ? AND BDAY = ?'
        );
        $stmt->bind_param('sssss', $username, $firstName, $middleName, $lastName, $birthday);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $_SESSION['reset_user_id'] = $user['ID'];
            $accountVerified = true;
        } else {
            $message = 'Those account details do not match.';
        }
    }
}

if (isset($_POST['update_password'])) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!isset($_SESSION['reset_user_id'])) {
        $message = 'Please verify your account details first.';
        $accountVerified = false;
    } elseif ($newPassword === '' || $confirmPassword === '') {
        $message = 'Please enter and confirm your new password.';
    } elseif (strlen($newPassword) < 8) {
        $message = 'Your new password must be at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'The new passwords do not match.';
    } else {
        $userId = $_SESSION['reset_user_id'];
        $newPasswordHash = (string) crc32($newPassword);
        $stmt = $dbconn->prepare('UPDATE acc_info SET PASSWORD = ? WHERE ID = ?');
        $stmt->bind_param('si', $newPasswordHash, $userId);

        if ($stmt->execute()) {
            unset($_SESSION['reset_user_id']);
            $_SESSION['login_attempts'] = 0;
            $passwordReset = true;
            $accountVerified = false;
        } else {
            $message = 'The password could not be changed. Please try again.';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Forgot Password</title>
</head>
<body>
    <div class="container forgot-container">
        <div class="form-container sign-in">
            <form method="POST" action="">
                <h1>Forgot Password</h1>
                <?php if ($passwordReset): ?>
                    <p class="form-message" role="status">Your password has been changed.</p>
                    <a href="index.php?back_to_login=1">Back to log in</a>
                <?php elseif ($accountVerified): ?>
                    <span>Account verified. Choose a new password.</span>
                    <?php if ($message !== ''): ?>
                        <p class="form-message" role="alert"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <input type="password" name="new_password" placeholder="New password" minlength="8" autocomplete="new-password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" minlength="8" autocomplete="new-password" required>
                    <input type="submit" name="update_password" value="CHANGE PASSWORD">
                    <a href="index.php?back_to_login=1">Back to log in</a>
                <?php else: ?>
                    <span>Verify your account details to continue.</span>
                    <?php if ($message !== ''): ?>
                        <p class="form-message" role="alert"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <input type="text" name="username" placeholder="Username" autocomplete="username" required>
                    <input type="text" name="fname" placeholder="First name" autocomplete="given-name" required>
                    <input type="text" name="mname" placeholder="Middle name" required>
                    <input type="text" name="lname" placeholder="Last name" autocomplete="family-name" required>
                    <input type="date" name="birthday" aria-label="Birth date" required>
                    <input type="submit" name="verify_account" value="VERIFY ACCOUNT">
                    <a href="index.php?back_to_login=1">Back to log in</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>