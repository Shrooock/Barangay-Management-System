<?php
include "../bootstrap/index.php";

$user_id = getBody("id", $_GET);

if ($user_id) {
	try {
		$conn->query("SET FOREIGN_KEY_CHECKS=0;");

		$user_res = $conn->query("SELECT username FROM users WHERE id=$user_id");
		$user_data = $user_res->fetch_assoc();
		$target_user = $user_data['username'] ?? "ID: $user_id";

		$db
			->delete("residents")
			->where("account_id", $user_id)
			->exec();

		$db
			->delete("users")
			->where("id", $user_id)
			->exec();

		logActivity($conn, "DELETE", "USER", $target_user, "Deleted user account and linked resident data");

		$conn->query("SET FOREIGN_KEY_CHECKS=1;");
	} catch (\Throwable $th) {
		echo "<pre>";
		var_dump($th);
		echo "</pre>";
	}

	header("Location: ../users.php");
	$conn->close();
}
