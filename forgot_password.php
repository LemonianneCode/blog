<?php
session_start();
include ('includes/db_conn.php');
require_once ('includes/security_questions.php');

$message = '';
$passwordReset = false;
$accountVerified = isset($_SESSION['reset_user_id']);
$securityQuestionsForReset = $_SESSION['reset_security_questions'] ?? [];
$answerRequired = isset($_SESSION['reset_pending_user_id']);

if (isset($_POST['verify_account'])) {
    unset($_SESSION['reset_user_id'], $_SESSION['reset_pending_user_id'], $_SESSION['reset_security_questions']);
    $accountVerified = false;
    $answerRequired = false;
    $securityQuestionsForReset = [];

    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['fname'] ?? '');
    $middleName = trim($_POST['mname'] ?? '');
    $lastName = trim($_POST['lname'] ?? '');
    $birthday = $_POST['birthday'] ?? '';

    if ($username === '' || $firstName === '' || $middleName === '' || $lastName === '' || $birthday === '') {
        $message = 'Please complete every field.';
    } else {
        $stmt = $dbconn->prepare(
            'SELECT ID, SECURITY_QUESTION, SECURITY_ANSWER, SECURITY_QUESTION_2, SECURITY_ANSWER_2,
                    SECURITY_QUESTION_3, SECURITY_ANSWER_3
             FROM acc_info
             WHERE USERNAME = ? AND FNAME = ? AND MNAME = ? AND LNAME = ? AND BDAY = ?'
        );
        $stmt->bind_param('sssss', $username, $firstName, $middleName, $lastName, $birthday);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $storedQuestions = [
                $user['SECURITY_QUESTION'],
                $user['SECURITY_QUESTION_2'],
                $user['SECURITY_QUESTION_3'],
            ];
            $storedAnswers = [
                $user['SECURITY_ANSWER'],
                $user['SECURITY_ANSWER_2'],
                $user['SECURITY_ANSWER_3'],
            ];
            $questionsConfigured = count(array_unique($storedQuestions)) === 3;
            foreach ($storedQuestions as $index => $question) {
                if (empty($question) || !array_key_exists($question, $securityQuestions) || empty($storedAnswers[$index])) {
                    $questionsConfigured = false;
                    break;
                }
            }

            if (!$questionsConfigured) {
                $message = 'This account does not have three recovery questions configured. Please contact the administrator to recover access.';
            } else {
                $_SESSION['reset_pending_user_id'] = $user['ID'];
                $_SESSION['reset_security_questions'] = $storedQuestions;
                $securityQuestionsForReset = $storedQuestions;
                $answerRequired = true;
            }
        } else {
            $message = 'Those account details do not match.';
        }
    }
}

if (isset($_POST['verify_security_answer'])) {
    $answers = [];
    for ($index = 1; $index <= 3; $index++) {
        $answer = $_POST['security_answer_' . $index] ?? '';
        $answers[] = is_string($answer) ? trim($answer) : '';
    }
    $pendingUserId = $_SESSION['reset_pending_user_id'] ?? null;
    $securityQuestionsForReset = $_SESSION['reset_security_questions'] ?? [];

    if (!$pendingUserId || count($securityQuestionsForReset) !== 3) {
        $message = 'Please verify your account details first.';
        $answerRequired = false;
    } elseif (count(array_filter($answers, static function ($answer) {
        return $answer === '';
    })) > 0) {
        $message = 'Please answer all three security questions.';
        $answerRequired = true;
    } else {
        $stmt = $dbconn->prepare(
            'SELECT SECURITY_ANSWER, SECURITY_ANSWER_2, SECURITY_ANSWER_3 FROM acc_info WHERE ID = ?'
        );
        $stmt->bind_param('i', $pendingUserId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $answerFields = ['SECURITY_ANSWER', 'SECURITY_ANSWER_2', 'SECURITY_ANSWER_3'];
        $allAnswersMatch = $user !== null;
        foreach ($answerFields as $index => $field) {
            if (
                !$user ||
                empty($user[$field]) ||
                !password_verify($answers[$index], $user[$field])
            ) {
                $allAnswersMatch = false;
            }
        }

        if ($allAnswersMatch) {
            unset($_SESSION['reset_pending_user_id'], $_SESSION['reset_security_questions']);
            $_SESSION['reset_user_id'] = $pendingUserId;
            session_regenerate_id(true);
            $accountVerified = true;
            $answerRequired = false;
            $securityQuestionsForReset = [];
        } else {
            $message = 'One or more security answers are incorrect.';
            $answerRequired = true;
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
                <?php elseif ($answerRequired): ?>
                    <span>Answer all three recovery questions to continue.</span>
                    <?php if ($message !== ''): ?>
                        <p class="form-message" role="alert"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <?php foreach ($securityQuestionsForReset as $index => $question): ?>
                        <label class="recovery-question" for="security-answer-<?php echo $index + 1; ?>">
                            <?php echo htmlspecialchars($securityQuestions[$question] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </label>
                        <input type="text" id="security-answer-<?php echo $index + 1; ?>" name="security_answer_<?php echo $index + 1; ?>" autocomplete="off" required>
                    <?php endforeach; ?>
                    <input type="submit" name="verify_security_answer" value="VERIFY ANSWER">
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