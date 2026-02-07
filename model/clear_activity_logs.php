<?php
include "../bootstrap/index.php";

if ($_SESSION["role"] !== "administrator") {
    header("Location: ../dashboard.php");
    exit;
}

$query = "TRUNCATE TABLE activity_log";
$result = $conn->query($query);

if ($result === true) {
    $_SESSION["message"] = "Activity logs have been cleared!";
    $_SESSION["status"] = "success";
} else {
    $_SESSION["message"] = "Something went wrong!";
    $_SESSION["status"] = "danger";
}

header("Location: ../activity_logs.php");
$conn->close();
?>
