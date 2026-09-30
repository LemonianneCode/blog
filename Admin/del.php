<?php
include ('../includes/db_conn.php');

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM acc_info WHERE ID = $id";
    $query = $dbconn->query($sql);
    if($query) {
        echo "Account deleted successfully.";
    } else {
        echo "Error deleting account.";
    }
} else {
    
    echo "Invalid request.";
}

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
echo "<th>Password</th>"; // This is the column for $pass
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
    echo "<td>" . $pass . "</td>";
    echo "</tr>";
}

// 3. Close the table
echo "</tbody>";
echo "</table>";
?>
<form method="GET" action="">
  <input type="number" name="id" placeholder="Enter ID to delete" required>
  <input type="submit" value="Delete Account">  
</form>

<form action="index.php">
    <input type="SUBMIT" value="Back to Admin Panel">
</form>