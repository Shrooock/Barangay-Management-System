<?php
include '../bootstrap/index.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrator'){
    header('Location: ../dashboard.php');
    exit;
}

// Clear the certificate_requests table
$query = "TRUNCATE TABLE certificate_requests";

if($conn->query($query) === TRUE){
    // Optional: Log this action too
    logActivity($conn, "DELETE", "HISTORY", "Issued Certificates", "Cleared all issued certificate history");
    
    $_SESSION['message'] = 'Issued Certificate History has been cleared!';
    $_SESSION['success'] = 'success';
}else{
    $_SESSION['message'] = 'Something went wrong: ' . $conn->error;
    $_SESSION['success'] = 'danger';
}

header("Location: ../activity_logs.php");
?>
