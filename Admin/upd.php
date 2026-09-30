<?php
include('../includes/db_conn.php');

$sql = "SELECT * FROM acc_info ORDER BY LNAME ASC";
$query = $dbconn->query($sql);

$account = null;
$message = '';

// Load the account after the user enters an ID.
if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        $stmt = $dbconn->prepare('SELECT * FROM acc_info WHERE ID = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $account = $result->fetch_assoc();
        $stmt->close();

        if (!$account) {
            $message = 'No account found with that ID.';
        }
    } else {
        $message = 'Please enter a valid ID.';
    }
}

// Save the edited account.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $fname = trim($_POST['fname'] ?? '');
    $mname = trim($_POST['mname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bday = $_POST['bday'] ?? '';
    $uname = trim($_POST['uname'] ?? '');
    $pass = crc32($_POST['pass']) ?? '';

    $stmt = $dbconn->prepare(
        'UPDATE acc_info
         SET FNAME = ?, MNAME = ?, LNAME = ?, GENDER = ?, BDAY = ?,
             USERNAME = ?, PASSWORD = ?
         WHERE ID = ?'
    );
    $stmt->bind_param(
        'sssssssi',
        $fname,
        $mname,
        $lname,
        $gender,
        $bday,
        $uname,
        $pass,
        $id
    );

    if ($stmt->execute()) {
        $message = 'Account updated successfully.';
    } else {
        $message = 'Update failed: ' . $stmt->error;
    }

    $stmt->close();
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
  <link rel="stylesheet" href="admin.css">
  <title>Update account | Admin panel</title>
</head>
<body>
  <main class="admin-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1>Update account</h1>
        <p class="page-description">Find and edit a registered blog account.</p>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <p class="notice" role="status"><?php echo e($message); ?></p>
    <?php endif; ?>

    <section class="table-card" aria-label="Registered accounts">
      <div class="table-scroll">
        <table class="account-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Last Name</th>
              <th>First Name</th>
              <th>Middle Name</th>
              <th>Gender</th>
              <th>Birthday</th>
              <th>Username</th>
              <th>Password</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $query->fetch_assoc()): ?>
              <tr>
                <td><?php echo e($row['ID']); ?></td>
                <td><?php echo e($row['LNAME']); ?></td>
                <td><?php echo e($row['FNAME']); ?></td>
                <td><?php echo e($row['MNAME']); ?></td>
                <td><?php echo e($row['GENDER']); ?></td>
                <td><?php echo e($row['BDAY']); ?></td>
                <td><?php echo e($row['USERNAME']); ?></td>
                <td><?php echo e($row['PASSWORD']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="form-card" aria-labelledby="find-heading">
      <h2 id="find-heading">Find an account</h2>
      <p class="section-description">Enter the ID shown in the account list.</p>
      <form class="admin-form admin-form--lookup" method="GET" action="upd.php">
        <label for="id">Account ID</label>
        <input type="number" id="id" name="id" min="1" placeholder="Enter account ID" required>
        <input class="button" type="submit" value="Find account">
      </form>
    </section>

    <?php if ($account): ?>
      <section class="form-card" aria-labelledby="edit-heading">
        <h2 id="edit-heading">Edit account ID <?php echo e($account['ID']); ?></h2>
        <p class="section-description">Update the account details below.</p>
        <form class="admin-form" method="POST" action="upd.php">
          <input type="hidden" name="id" value="<?php echo e($account['ID']); ?>">

          <label for="fname">First Name
            <input type="text" id="fname" name="fname" value="<?php echo e($account['FNAME']); ?>" required>
          </label>

          <label for="mname">Middle Name
            <input type="text" id="mname" name="mname" value="<?php echo e($account['MNAME']); ?>" required>
          </label>

          <label for="lname">Last Name
            <input type="text" id="lname" name="lname" value="<?php echo e($account['LNAME']); ?>" required>
          </label>

          <label for="gender">Gender
            <select id="gender" name="gender" required>
              <option value="Male" <?php echo $account['GENDER'] === 'Male' ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo $account['GENDER'] === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
          </label>

          <label for="bday">Birthday
            <input type="date" id="bday" name="bday" value="<?php echo e($account['BDAY']); ?>" required>
          </label>

          <label for="uname">Username
            <input type="text" id="uname" name="uname" value="<?php echo e($account['USERNAME']); ?>" required>
          </label>

          <label for="pass">Password
            <input type="password" id="pass" name="pass" value="<?php echo e($account['PASSWORD']); ?>" required>
          </label>

          <div class="form-actions">
            <input class="button" type="submit" value="Update account">
          </div>
        </form>
      </section>
    <?php endif; ?>

    <nav class="admin-actions" aria-label="Admin actions">
      <form action="index.php">
        <input class="button button-quiet" type="submit" value="Back to admin panel">
      </form>
    </nav>
  </main>
</body>
</html>
