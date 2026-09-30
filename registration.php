<?php
include ('includes/db_conn.php');
$error = '';

 if(isset($_POST['register'])){
  $fname = trim($_POST['fname'] ?? '');
  $mname = trim($_POST['mname'] ?? '');
  $lname = trim($_POST['lname'] ?? '');

  if (
      !preg_match('/\A\p{L}+\z/u', $fname) ||
      !preg_match('/\A\p{L}+\z/u', $mname) ||
      !preg_match('/\A\p{L}+\z/u', $lname)
  ) {
      $error = 'First, middle, and last names must contain letters only.';
  } else {
      $gender = $_POST['gender'] ?? '';
      $bday = $_POST['bday'] ?? '';
      $uname = $_POST['uname'] ?? '';
      $pass = crc32($_POST['pass'] ?? '');
      $stmt = $dbconn->prepare(
          'INSERT INTO acc_info(FNAME, MNAME, LNAME, GENDER, BDAY, USERNAME, PASSWORD)
           VALUES (?, ?, ?, ?, ?, ?, ?)'
      );
      $stmt->bind_param('sssssss', $fname, $mname, $lname, $gender, $bday, $uname, $pass);
      $stmt->execute();
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
            <form method="POST" action="">
                <span>Enter your personal information</span> 
                <?php if ($error !== ''): ?>
                    <p role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <input type="text" name="fname" placeholder="First Name" pattern="[\p{L}]+" title="Use letters only" required>
                <input type="text" name="mname" placeholder="Middle Name" pattern="[\p{L}]+" title="Use letters only" required>
                <input type="text" name="lname" placeholder="Last Name" pattern="[\p{L}]+" title="Use letters only" required>
                <input type="date" name="bday" placeholder="mm/dd/yyyy"> 
                <select name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <input type="text" name="uname" placeholder="Username">
                <input type="password" name="pass" placeholder="Password">
                <input type="SUBMIT" name="register" value="REGISTER"> 
            </form>
            <form method="POST" action="index.php" class="back-button-form">
                <span>BACK TO LOG IN</span>
                <input type="submit" value="BACK">
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>