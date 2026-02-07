<?php
include '../server/server.php';

if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

if (isset($_POST['update_bg'])) {
    $cert_type = $_POST['cert_type'];
    $bg_file = $_FILES['bg_image']['name'];
    $redirect = $_POST['redirect'];

    if (!empty($bg_file)) {
        $newName = date('dmYHis') . "_" . $cert_type . "_" . str_replace(" ", "", $bg_file);
        $target = "../assets/uploads/" . basename($newName);

        if (move_uploaded_file($_FILES['bg_image']['tmp_name'], $target)) {
            $column = "";
            switch ($cert_type) {
                case 'brgy': $column = 'bg_brgy_cert'; break;
                case 'indi': $column = 'bg_indi_cert'; break;
                case 'business': $column = 'bg_business_permit'; break;
                case 'elect': $column = 'bg_elect_cert'; break;
            }

            if (!empty($column)) {
                $query = "UPDATE tblbrgy_info SET `$column`='" . $conn->real_escape_string($newName) . "' WHERE id=1";
                if ($conn->query($query)) {
                    $_SESSION['message'] = 'Background updated successfully!';
                    $_SESSION['status'] = 'success';
                } else {
                    $_SESSION['message'] = 'Database error: ' . $conn->error;
                    $_SESSION['status'] = 'danger';
                }
            }
        } else {
            $_SESSION['message'] = 'Failed to upload image.';
            $_SESSION['status'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Please select an image.';
        $_SESSION['status'] = 'danger';
    }
}

header("Location: ../" . $redirect);
$conn->close();
?>
