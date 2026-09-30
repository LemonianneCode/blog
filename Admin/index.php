<?php
include ('../includes/db_conn.php');

$sql = "SELECT * FROM acc_info ORDER BY LNAME ASC";
$query = $dbconn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="admin.css">
  <title>Admin panel</title>
</head>
<body>
  <main class="admin-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1>Accounts</h1>
        <p class="page-description">Manage registered blog accounts.</p>
      </div>
    </header>
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

<?php
while($row = $query->fetch_assoc()){
    $uid = $row['ID'];
    $fname = $row['FNAME'];
    $mname = $row['MNAME'];
    $lname = $row['LNAME'];
    $gender = $row['GENDER'];
    $bday = $row['BDAY'];
    $uname = $row['USERNAME'];
    $pass = $row['PASSWORD'];

    echo "<tr>";
    echo "<td>" . $uid . "</td>";
    echo "<td>" . $lname . "</td>";
    echo "<td>" . $fname . "</td>";
    echo "<td>" . $mname . "</td>";
    echo "<td>" . $gender . "</td>";
    echo "<td>" . $bday . "</td>";
    echo "<td>" . $uname . "</td>";
    echo "<td>" . $pass . "</td>";
    echo "</tr>";
}
?>
          </tbody>
        </table>
      </div>
    </section>
    <nav class="admin-actions" aria-label="Admin actions">
      <form method="GET" action="del.php">
        <input class="button button-danger" type="submit" value="Delete account">
      </form>
      <form method="GET" action="upd.php">
        <input class="button" type="submit" value="Update account">
      </form>
      <form method="GET" action="../index.php">
        <input class="button button-quiet" type="submit" value="Log out">
      </form>
    </nav>
  </main>
</body>
</html>