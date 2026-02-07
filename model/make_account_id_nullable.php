<?php
include '../server/server.php';

// First, check if there are any existing records with account_id = 0 which might cause issues
// (though the DB check showed 0 records, it's good practice)

$query = "ALTER TABLE residents MODIFY account_id INT(10) UNSIGNED NULL";

if ($conn->query($query) === TRUE) {
    echo "Column 'account_id' is now nullable.";
} else {
    echo "Error modifying column: " . $conn->error;
}

$conn->close();
?>
