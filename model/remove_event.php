<?php
include "../bootstrap/index.php";

$id = getBody('id', $_POST);

if (!empty($id)) {
    $query = "DELETE FROM tblevents WHERE id='$id'";
    if ($conn->query($query) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Event Deleted Successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>
