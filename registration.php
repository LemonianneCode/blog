<?php
session_start();
include ('includes/db_conn.php');
require_once ('includes/security_questions.php');

$error = '';
$success = false;
$pendingRegistration = $_SESSION['pending_registration'] ?? null;

if (isset($_GET['cancel'])) {
    unset($_SESSION['pending_registration']);
    header('Location: index.php');
    exit;
}

if (isset($_POST['register'])) {
    $fname = trim($_POST['fname'] ?? '');
    $mname = trim($_POST['mname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $bday = $_POST['bday'] ?? '';
    $uname = trim($_POST['uname'] ?? '');
    $password = $_POST['pass'] ?? '';

    if (
        !preg_match('/\A\p{L}+\z/u', $fname) ||
        !preg_match('/\A\p{L}+\z/u', $mname) ||
        !preg_match('/\A\p{L}+\z/u', $lname)
    ) {
        $error = 'First, middle, and last names must contain letters only.';
    } elseif ($gender === '' || $bday === '' || $uname === '' || $password === '') {
        $error = 'Please complete every field.';
    } elseif (strlen($password) < 8) {
        $error = 'Your password must be at least 8 characters.';
    } else {
        $_SESSION['pending_registration'] = [
            'fname' => $fname,
            'mname' => $mname,
            'lname' => $lname,
            'gender' => $gender,
            'bday' => $bday,
            'uname' => $uname,
            'password' => (string) crc32($password),
        ];
        $pendingRegistration = $_SESSION['pending_registration'];
    }
}

if (isset($_POST['save_security_question'])) {
    $pendingRegistration = $_SESSION['pending_registration'] ?? null;
    $questions = [];
    $answers = [];
    for ($index = 1; $index <= 3; $index++) {
        $question = $_POST['security_question_' . $index] ?? '';
        $answer = $_POST['security_answer_' . $index] ?? '';
        $questions[] = is_string($question) ? $question : '';
        $answers[] = is_string($answer) ? trim($answer) : '';
    }

    if (!$pendingRegistration) {
        $error = 'Please enter your registration information first.';
    } elseif (
        count(array_unique($questions)) !== 3 ||
        count(array_filter($questions, static function ($question) use ($securityQuestions) {
            return !array_key_exists($question, $securityQuestions);
        })) > 0
    ) {
        $error = 'Please select three different valid security questions.';
    } elseif (count(array_filter($answers, static function ($answer) {
        return $answer === '';
    })) > 0) {
        $error = 'Please answer all three security questions.';
    } else {
        $passwordHash = $pendingRegistration['password'];
        $answerHashes = array_map(static function ($answer) {
            return password_hash($answer, PASSWORD_DEFAULT);
        }, $answers);
        [$question1, $question2, $question3] = $questions;
        [$answerHash1, $answerHash2, $answerHash3] = $answerHashes;
        $fname = $pendingRegistration['fname'];
        $mname = $pendingRegistration['mname'];
        $lname = $pendingRegistration['lname'];
        $gender = $pendingRegistration['gender'];
        $bday = $pendingRegistration['bday'];
        $username = $pendingRegistration['uname'];
        $stmt = $dbconn->prepare(
            'INSERT INTO acc_info
             (FNAME, MNAME, LNAME, GENDER, BDAY, USERNAME, PASSWORD,
              SECURITY_QUESTION, SECURITY_ANSWER, SECURITY_QUESTION_2, SECURITY_ANSWER_2,
              SECURITY_QUESTION_3, SECURITY_ANSWER_3)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'sssssssssssss',
            $fname,
            $mname,
            $lname,
            $gender,
            $bday,
            $username,
            $passwordHash,
            $question1,
            $answerHash1,
            $question2,
            $answerHash2,
            $question3,
            $answerHash3
        );

        if ($stmt->execute()) {
            unset($_SESSION['pending_registration']);
            $pendingRegistration = null;
            $success = true;
        } else {
            $error = 'Your account could not be created. Please check that the username is not already in use.';
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
    <link rel="stylesheet" href="regs.css">
    <title>Registration</title>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container sign-in">
            <?php if ($success): ?>
                <div class="registration-result">
                    <h1>Account Created</h1>
                    <p class="form-message success-message" role="status">Your account and recovery questions have been saved.</p>
                    <a href="index.php">Back to log in</a>
                </div>
            <?php elseif ($pendingRegistration): ?>
                <form method="POST" action="">
                    <h1>Recovery Questions</h1>
                    <span>Choose three different questions and provide an answer for each. You will need all three answers if you forget your password.</span>
                    <?php if ($error !== ''): ?>
                        <p class="form-message" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <?php for ($index = 1; $index <= 3; $index++): ?>
                        <label class="form-label" for="security-question-<?php echo $index; ?>">Question <?php echo $index; ?></label>
                        <select id="security-question-<?php echo $index; ?>" name="security_question_<?php echo $index; ?>" required>
                            <option value="" disabled selected>Select a question</option>
                            <?php foreach ($securityQuestions as $question => $questionText): ?>
                                <option value="<?php echo htmlspecialchars($question, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($questionText, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label class="form-label" for="security-answer-<?php echo $index; ?>">Answer <?php echo $index; ?></label>
                        <input type="text" id="security-answer-<?php echo $index; ?>" name="security_answer_<?php echo $index; ?>" autocomplete="off" required>
                    <?php endfor; ?>
                    <input type="submit" name="save_security_question" value="CREATE ACCOUNT">
                </form>
            <?php else: ?>
                <form method="POST" action="">
                    <h1>Create Account</h1>
                    <span>Enter your personal information</span>
                    <?php if ($error !== ''): ?>
                        <p class="form-message" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <input type="text" name="fname" placeholder="First Name" pattern="[\p{L}]+" title="Use letters only" required>
                    <input type="text" name="mname" placeholder="Middle Name" pattern="[\p{L}]+" title="Use letters only" required>
                    <input type="text" name="lname" placeholder="Last Name" pattern="[\p{L}]+" title="Use letters only" required>
                    <input type="date" name="bday" aria-label="Birth date" required>
                    <select name="gender" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" name="uname" placeholder="Username" autocomplete="username" required>
                    <input type="password" name="pass" placeholder="Password" minlength="8" autocomplete="new-password" required>
                    <input type="submit" name="register" value="CONTINUE">
                </form>
            <?php endif; ?>
            <form method="POST" action="?cancel=1" class="back-button-form">
                <span>BACK TO LOG IN</span>
                <input type="submit" value="BACK">
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
