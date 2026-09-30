<?php
include('../includes/db_conn.php');

$sql = "SELECT * FROM acc_info ORDER BY LNAME ASC";
$query = $dbconn->query($sql);

// 1. Start the table and add headers
echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
echo "<thead>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>Last Name</th>";
echo "<th>First Name</th>";
echo "<th>Middle Name</th>";
echo "<th>Gender</th>";
echo "<th>Birthday</th>";
echo "<th>Username</th>";
echo "<th>Password</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

// 2. Loop through the results and output a table row (<tr>) for each record
while($row = $query->fetch_assoc()){
    $uid = $row['ID'];
    $fname = $row['FNAME'];
    $mname = $row['MNAME'];
    $lname = $row['LNAME'];
    $gender = $row['GENDER'];
    $bday = $row['BDAY'];
    $uname = $row['USERNAME'];
    $pass = $row['PASSWORD'];

    // Output the table row using the variables
    echo "<tr>";
    echo "<td>" . $uid . "</td>";
    echo "<td>" . $lname . "</td>";
    echo "<td>" . $fname . "</td>";
    echo "<td>" . $mname . "</td>";
    echo "<td>" . $gender . "</td>";
    echo "<td>" . $bday . "</td>";
    echo "<td>" . $uname . "</td>";
    echo "<td>" . $pass . "</td>"; // This uses the $pass variable
    echo "</tr>";
}

// 3. Close the table
echo "</tbody>";
echo "</table>";

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

<h2>Update Account</h2>

<?php if ($message !== ''): ?>
    <p><?php echo e($message); ?></p>
<?php endif; ?>

<!-- Enter the ID of the account to update -->
<form method="GET" action="upd.php">
    <label for="id">Account ID:</label>
    <input type="number" id="id" name="id" min="1" required>
    <input type="submit" value="Find Account">
</form>

<?php if ($account): ?>
    <hr>
    <h3>Edit account ID <?php echo e($account['ID']); ?></h3>

    <form method="POST" action="upd.php">
        <input type="hidden" name="id" value="<?php echo e($account['ID']); ?>">

        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" value="<?php echo e($account['FNAME']); ?>" required><br><br>

        <label for="mname">Middle Name:</label>
        <input type="text" id="mname" name="mname" value="<?php echo e($account['MNAME']); ?>" required><br><br>

        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" value="<?php echo e($account['LNAME']); ?>" required><br><br>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="Male" <?php echo $account['GENDER'] === 'Male' ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo $account['GENDER'] === 'Female' ? 'selected' : ''; ?>>Female</option>
        </select><br><br>

        <label for="bday">Birthday:</label>
        <input type="date" id="bday" name="bday" value="<?php echo e($account['BDAY']); ?>" required><br><br>

        <label for="uname">Username:</label>
        <input type="text" id="uname" name="uname" value="<?php echo e($account['USERNAME']); ?>" required><br><br>

        <label for="pass">Password:</label>
        <input type="password" id="pass" name="pass" value="<?php echo e($account['PASSWORD']); ?>" required><br><br>

        <input type="submit" value="Update Account">
    </form>
<?php endif; ?>

<br>
<form action="index.php">
    <input type="submit" value="Back to Admin Panel">
</form>

