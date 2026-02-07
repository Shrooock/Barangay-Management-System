<?php
include('../server/server.php');

if (!isset($_SESSION['username'])) {
  if (isset($_SERVER["HTTP_REFERER"])) {
    header("Location: " . $_SERVER["HTTP_REFERER"]);
  }
}

$id = $conn->real_escape_string($_POST['id']);
$name = $conn->real_escape_string($_POST['name']);
$owner1 = $conn->real_escape_string($_POST['owner1']);
$owner2 = $conn->real_escape_string($_POST['owner2']);
$nature = $conn->real_escape_string($_POST['nature']);
$applied = $conn->real_escape_string($_POST['applied']);
$tin = $conn->real_escape_string($_POST['tin']);
$cert_no = $conn->real_escape_string($_POST['cert_no']);
$address = $conn->real_escape_string($_POST['address']);

if (!empty($name) && !empty($owner1) && !empty($nature) && !empty($applied)) {

  $update = "UPDATE tblpermit SET `name` = '$name', `owner1` = '$owner1', `owner2` = '$owner2', `nature` = '$nature', `applied` = '$applied', `tin` = '$tin', `business_address` = '$address', `cert_number` = '$cert_no' WHERE id = '$id'";
  $result = $conn->query($update);

  if ($result === true) {
    $_SESSION['message'] = 'Business Permit updated!';
    $_SESSION['status'] = 'success';
  } else {
    $_SESSION['message'] = 'Something went wrong!';
    $_SESSION['status'] = 'danger';
  }

} else {

  $_SESSION['message'] = 'Please fill up the form completely!';
  $_SESSION['status'] = 'danger';
}

header("Location: ../business_permit.php");

$conn->close();
