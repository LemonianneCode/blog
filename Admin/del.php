<?php
include('../includes/db_conn.php');

$message = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM acc_info WHERE ID = $id";
    $query = $dbconn->query($sql);
    if ($query) {
        $message = 'Account deleted successfully.';
    } else {
        $message = 'Error deleting account.';
    }
} else {
    $message = 'Enter an account ID to delete.';
}

$sql = "SELECT * FROM acc_info ORDER BY LNAME ASC";
$query = $dbconn->query($sql);

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
  <title>Delete account | Admin panel</title>
</head>
<body>
  <main class="admin-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1>Delete account</h1>
        <p class="page-description">Choose an account ID to remove it.</p>
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

    <section class="form-card" aria-labelledby="delete-heading">
      <h2 id="delete-heading">Remove an account</h2>
      <p class="section-description">This action permanently deletes the selected account.</p>
      <form class="admin-form admin-form--lookup" method="GET" action="">
        <label for="delete-id">Account ID</label>
        <input type="number" id="delete-id" name="id" min="1" placeholder="Enter account ID" required>
        <input class="button button-danger" type="submit" value="Delete account">
      </form>
    </section>

    <nav class="admin-actions" aria-label="Admin actions">
      <form action="index.php">
        <input class="button button-quiet" type="submit" value="Back to admin panel">
      </form>
    </nav>
  </main>
</body>
</html>
