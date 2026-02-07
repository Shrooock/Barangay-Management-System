<?php
// Local connection for migration
$database = "bsms";
$username = "root";
$host = "localhost";
$password = "";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Add columns if they don't exist
$check_query = "SHOW COLUMNS FROM `tblpermit` LIKE 'tin'";
$result = $conn->query($check_query);

if ($result->num_rows == 0) {
    echo "Adding 'tin' and 'cert_number' columns...<br>";
    $query = "ALTER TABLE `tblpermit` 
        ADD COLUMN `tin` VARCHAR(50) DEFAULT NULL AFTER `nature`,
        ADD COLUMN `cert_number` VARCHAR(50) DEFAULT NULL AFTER `tin`";

    if ($conn->query($query)) {
        echo "Migration successful: Columns added to tblpermit.";
    } else {
        echo "Migration failed: " . $conn->error;
    }
} else {
    echo "Columns already exist. No action needed.";
}

$conn->close();
?>
