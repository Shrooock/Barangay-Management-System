<?php
include "../bootstrap/index.php";

if (!isset($_SESSION["username"])) {
	if (isset($_SERVER["HTTP_REFERER"])) {
		header("Location: " . $_SERVER["HTTP_REFERER"]);
	}
}

/**
 * Handle profile image
 */
$id = getBody("id", $_POST); // base 64 image
$profileCamera = getBody("profileimg", $_POST); // base 64 image
$profileFile = $_FILES["img"];

$imgFilename = empty($profileCamera) ? null : $profileCamera;

if ($profileFile["name"]) {
	$uniqId = uniqid(date("YmdhisU"));
	$ext = pathinfo($profileFile["name"], PATHINFO_EXTENSION);
	$imgFilename = "$uniqId.$ext";
	$imgDir = "../assets/uploads/$imgFilename";

	move_uploaded_file($profileFile["tmp_name"], $imgDir);
}

echo "<pre>";
var_dump($imgFilename);
echo "</pre>";

$email = getBody("email", $_POST);
$cur_pass = getBody("cur_pass", $_POST);
$new_pass = getBody("new_pass", $_POST);
$con_pass = getBody("con_pass", $_POST);

$updateData = [
	"avatar" => $imgFilename,
	"email" => $email,
];

if (!empty($cur_pass) && !empty($new_pass) && !empty($con_pass)) {
    // Check current password
    $hash = sha1($cur_pass);
    $check_q = $conn->query("SELECT * FROM users WHERE id = $id AND password = '$hash'");
    
    if ($check_q->num_rows > 0) {
        if ($new_pass === $con_pass) {
            $updateData["password"] = sha1($new_pass);
            $pw_msg = " and password";
        } else {
            $_SESSION["message"] = "New passwords do not match!";
            $_SESSION["status"] = "danger";
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    } else {
        $_SESSION["message"] = "Current password is incorrect!";
        $_SESSION["status"] = "danger";
        header("Location: " . $_SERVER["HTTP_REFERER"]);
        exit;
    }
}

$result = $db
	->update("users")
	->where("id", $id)
	->set($updateData)
	->exec();

$_SESSION["email"] = $email;

$user_res = $conn->query("SELECT username FROM users WHERE id=$id");
$user_data = $user_res->fetch_assoc();
$target_user = $user_data['username'] ?? "ID: $id";

logActivity($conn, "EDIT", "USER", $target_user, "Updated account settings" . ($pw_msg ?? ""));
$_SESSION["avatar"] = $imgFilename;
$_SESSION["message"] = "Account settings have been updated successfully!";
$_SESSION["status"] = "success";

if (isset($_SERVER["HTTP_REFERER"])) {
	header("Location: " . $_SERVER["HTTP_REFERER"]);
}

$conn->close();
