<?php
session_start();
include ('includes/db_conn.php');

if (isset($_GET['back_to_login'])) {
    unset($_SESSION['login_attempts'], $_SESSION['reset_user_id']);
    session_regenerate_id(true);
    header('Location: index.php');
    exit;
}

$attemptLimit = 5;
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= $attemptLimit) {
    header('Location: forgot_password.php');
    exit;
}

$attemptsLeft = $attemptLimit - $_SESSION['login_attempts'];
$errorMessage = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errorMessage = 'Please enter your username and password.';
    } elseif ($username === 'root' && $password === 'admin123') {
        $_SESSION['login_attempts'] = 0;
        header('Location: Admin/index.php');
        exit;
    } else {
        $stmt = $dbconn->prepare('SELECT ID, PASSWORD FROM acc_info WHERE USERNAME = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && (string) $user['PASSWORD'] === (string) crc32($password)) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['login_attempts'] = 0;
            header('Location: mainpage/index.php');
            exit;
        }

        $_SESSION['login_attempts']++;
        $attemptsLeft = $attemptLimit - $_SESSION['login_attempts'];

        if ($attemptsLeft === 0) {
            header('Location: forgot_password.php');
            exit;
        }

        $errorMessage = 'Invalid username or password. You have ' . $attemptsLeft . ' attempts left.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>LOG IN</title>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container sign-in">
            <form method="POST" action="">
                <h1>Sign In</h1>
                <span>Enter your email and password</span>
                <?php if ($errorMessage !== ''): ?>
                    <p class="form-message" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <input type="text" name="username" placeholder="Username" id="username" required>
                <div class="password-field">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <button class="toggle-password" type="button" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                            <path class="eye-slash" d="m4 4 16 16"></path>
                        </svg>
                    </button>
                </div>
                <input type="SUBMIT" name="login" value="LOG IN" id="login-button">
                <p class="attempts-message">Login attempts remaining: <?php echo $attemptsLeft; ?></p>
                <a class="forgot-link" href="forgot_password.php">Forgot password?</a>
                <span>Don't have account yet?</span>
                <a href="registration.php"><i>Sign Up</i></a>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>