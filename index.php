<?php
 session_start();
 include ('includes/db_conn.php');

 if(isset($_POST['login'])){
  if($_POST['username'] != "" && $_POST['password'] != ""){
    $Uname = $_POST['username'];
    $Pass = crc32($_POST['password']);

    $sql = "SELECT ID FROM acc_info WHERE USERNAME = '$Uname' AND PASSWORD = '$Pass'";
    $query = $dbconn->query($sql);

     if ($_POST['username'] == "root" and $_POST['password'] == "admin123"){
      header('location: Admin/index.php');
     } else if(!empty($row=$query->fetch_assoc())){
      $_SESSION['user_id'] = $row['ID'];
      header('location:mainpage/index.php');
      exit;
     }else{
      header('location:error.php');
      exit;
     }
  }else{
    echo "<script>alert('No input. Please enter your username and password.');</script>";
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
                <span>Don't have account yet?</span>
                <a href="registration.php"><i>Sign Up</i></a>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>