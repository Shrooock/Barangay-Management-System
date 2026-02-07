<?php
include('../server/server.php');

if (!isset($_SESSION['username'])) {
    if (isset($_SERVER["HTTP_REFERER"])) {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    }
}

$name         = $conn->real_escape_string($_POST['name']);
$owner1     = $conn->real_escape_string($_POST['owner1']);
$owner2     = $conn->real_escape_string($_POST['owner2']);
$nature     = $conn->real_escape_string($_POST['nature']);
$applied    = $conn->real_escape_string($_POST['applied']);
$tin        = $conn->real_escape_string($_POST['tin']);
$cert_no    = $conn->real_escape_string($_POST['cert_no']);
$address    = $conn->real_escape_string($_POST['address'] ?? '');

// Self-healing: Ensure columns exist
$check_cols = $conn->query("SHOW COLUMNS FROM `tblpermit` LIKE 'tin'");
if ($check_cols->num_rows == 0) {
    $conn->query("ALTER TABLE `tblpermit` ADD COLUMN `tin` VARCHAR(50) DEFAULT NULL AFTER `nature` ");
    $conn->query("ALTER TABLE `tblpermit` ADD COLUMN `cert_number` VARCHAR(50) DEFAULT NULL AFTER `tin` ");
}
$check_address = $conn->query("SHOW COLUMNS FROM `tblpermit` LIKE 'business_address'");
if ($check_address->num_rows == 0) {
    $conn->query("ALTER TABLE `tblpermit` ADD COLUMN `business_address` TEXT DEFAULT NULL AFTER `owner2` ");
}

if (!empty($name) && !empty($owner1) && !empty($nature) && !empty($applied)) {

    $insert  = "INSERT INTO tblpermit (`name`, `owner1`, `owner2`, `business_address`, nature, applied, tin, cert_number) VALUES ('$name', '$owner1','$owner2', '$address', '$nature','$applied', '$tin', '$cert_no')";
    $result  = $conn->query($insert);

    if ($result === true) {
        $_SESSION['message'] = 'Business Permit added!';
        $_SESSION['status'] = 'success';
    } else {
        $_SESSION['message'] = 'Something went wrong! ' . $conn->error;
        $_SESSION['status'] = 'danger';
    }
} else {

    $_SESSION['message'] = 'Please fill up the form completely!';
    $_SESSION['status'] = 'danger';
}

header("Location: ../business_permit.php");

$conn->close();
