<?php
include "../bootstrap/index.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'administrator') {
    if (isset($_SERVER["HTTP_REFERER"])) {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    }
    exit;
}

$id = $_POST['id'];
$user = $conn->real_escape_string($_POST['username']);
$email = $conn->real_escape_string($_POST['email']);
$type = 'administrator';

if (!empty($id) && !empty($user) && !empty($email)) {
    // Check if email is changing to reset verification status
    $check_q = "SELECT email FROM users WHERE id = $id";
    $old_email_res = $conn->query($check_q)->fetch_assoc();
    $verification_reset = "";
    if ($old_email_res['email'] !== $email) {
        $verification_reset = ", is_verified = 0";
    }

    $query = "UPDATE users SET username = '$user', email = '$email', user_type = '$type' $verification_reset WHERE id = $id";

    if ($conn->query($query) === true) {
        $_SESSION['message'] = 'User has been updated!';
        $_SESSION['status'] = 'success';
        logActivity($conn, "EDIT", "USER", $user, "Updated user details (Email: $email)");
    } else {
        $_SESSION['message'] = 'Something went wrong! ' . $conn->error;
        $_SESSION['status'] = 'danger';
    }
} else {
    $_SESSION['message'] = 'Please complete the form!';
    $_SESSION['status'] = 'danger';
}

if (isset($_SERVER["HTTP_REFERER"])) {
    header("Location: " . $_SERVER["HTTP_REFERER"]);
} else {
    header("Location: ../users.php");
}

$conn->close();
?>
