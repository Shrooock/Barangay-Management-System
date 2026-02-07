<?php
require_once __DIR__ . "/../bootstrap/index.php";

$sql = "ALTER TABLE tblbrgy_info ADD COLUMN template_preference ENUM('standard', 'visual', 'both') DEFAULT 'both'";

if ($conn->query($sql)) {
    echo "Column 'template_preference' added successfully.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
