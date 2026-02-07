<?php
include "../bootstrap/index.php";

$title = getBody('title', $_POST);
$start = getBody('start', $_POST);
$end = getBody('end', $_POST);
$description = getBody('description', $_POST);
$color = getBody('color', $_POST);
$id = getBody('id', $_POST);

if (!empty($id)) {
    // Update
    $query = "UPDATE tblevents SET title='$title', start='$start', end='$end', description='$description', color='$color' WHERE id='$id'";
    $action = "Updated";
} else {
    // Insert
    $query = "INSERT INTO tblevents (title, start, end, description, color, status) VALUES ('$title', '$start', '$end', '$description', '$color', 'Active')";
    $action = "Added";
}

if ($conn->query($query) === TRUE) {
    // Log Activity
    if (isset($_SESSION['id'])) {
        $log_user_id = $_SESSION['id'];
        $log_action = ($action == "Added") ? "ADD" : "EDIT"; // Standardize action verbs
        $log_target_type = "Event";
        $log_target_name = $conn->real_escape_string($title);
        $log_details = $conn->real_escape_string("Event '$title' was $action for date $start.");
        
        $conn->query("INSERT INTO activity_log (user_id, action, target_type, target_name, details) VALUES ('$log_user_id', '$log_action', '$log_target_type', '$log_target_name', '$log_details')");
    }

    echo json_encode(['status' => 'success', 'message' => "Event $action Successfully"]);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}
?>
