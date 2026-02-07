<?php

require_once "./bootstrap/index.php";

$users = $db
	->from("users")
	->orderBy("created_at", "desc")
	->orderBy("created_at", "desc")
	->select([
		"id" => "users.id",
		"username" => "users.username",
		"password" => "users.password",
		"user_type" => "users.user_type",
		"avatar" => "users.avatar",
		"email" => "users.email",
		"created_at" => "users.created_at",
		"is_verified" => "users.is_verified",
	])
	->exec();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<?php include "templates/header.php"; ?>
	<title>User Management - Barangay Services Management System</title>
</head>

<body>
	<?php include "templates/loading_screen.php"; ?>
	<div class="wrapper">
		<!-- Main Header -->
		<?php include "templates/main-header.php"; ?>
		<!-- End Main Header -->

		<!-- Sidebar -->
		<?php include "templates/sidebar.php"; ?>
		<!-- End Sidebar -->

		<div class="main-panel">
			<div class="content">
				<div class="panel-header bg-primary-gradient">
					<div class="page-inner">
						<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
							<div>
								<h2 class="text-white fw-bold">Settings</h2>
							</div>
						</div>
					</div>
				</div>
				<div class="page-inner">
					<div class="row mt--2">
						<div class="col-md-12">

							<?php if (isset($_SESSION["message"])): ?>
								<div class="alert alert-<?php echo $_SESSION["status"]; ?> <?= $_SESSION["status"] == "danger"
 	? "bg-danger text-light"
 	: null ?>" role="alert">
									<?php echo $_SESSION["message"]; ?>
								</div>
								<?php unset($_SESSION["message"]); ?>
							<?php endif; ?>

							<div class="card">
								<div class="card-header">
									<div class="card-head-row">
										<div class="card-title">User Management</div>
										<div class="card-tools">
											<a href="#add" data-toggle="modal" class="btn btn-info btn-border btn-round btn-sm">
												<i class="fa fa-plus"></i>
												User
											</a>
										</div>
									</div>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="table table-striped ">
											<thead>
												<tr>
													<th scope="col">No.</th>
													<th scope="col">Username</th>
													<th scope="col">Email</th>
													<th scope="col">Verification</th>
													<th scope="col">Action</th>
												</tr>
											</thead>
											<tbody>
												<?php if (!empty($users)): ?>
													<?php $no = 1; foreach ($users as $row): ?>
														<tr>
															<td><?= $no ?></td>
															<td>
																<div class="avatar-sm float-left mr-2">
																	<img src="<?= imgSrc($row["avatar"], 'img/person.png') ?>" alt="..." class="avatar-img rounded-circle">
																</div>
																<?= ucwords($row["username"]) ?>
															</td>
															<td><?= $row["email"] ?></td>
															<td>
																<?php if ($row["is_verified"]): ?>
																	<span class="badge badge-success">Verified</span>
																<?php else: ?>
																	<span class="badge badge-danger">Pending</span>
																<?php endif; ?>
															</td>
															<td>
																<div class="form-button-action">
																	<?php if ($row["username"] !== $_SESSION["username"]): ?>
																		<a
																			type="button"
																			data-toggle="modal"
																			href="#edit_user_modal"
																			class="btn btn-link btn-info"
																			onclick="editUser(this)"
																			data-id="<?= $row["id"] ?>"
																			data-username="<?= $row["username"] ?>"
																			data-email="<?= $row["email"] ?>"
																			data-original-title="Edit User"
																		>
																			<i class="fa fa-edit"></i>
																		</a>
																		<a
																			type="button"
																			data-toggle="tooltip"
																			href="model/verify_user.php?id=<?= $row["id"] ?>&status=<?= $row["is_verified"] ? 0 : 1 ?>"
																			class="btn btn-link btn-<?= $row["is_verified"] ? "warning" : "success" ?>"
																			data-original-title="<?= $row["is_verified"] ? "Deactivate" : "Verify" ?>"
																		>
																			<i class="fa fa-<?= $row["is_verified"] ? "times" : "check" ?>"></i>
																		</a>
																		<a
																			type="button"
																			data-toggle="tooltip"
																			href="model/remove_user.php?id=<?= $row["id"] ?>"
																			onclick="return confirm('Are you sure you want to delete this user?');"
																			class="btn btn-link btn-danger"
																			data-original-title="Remove"
																		>
																			<i class="fa fa-times"></i>
																		</a>
																	<?php else: ?>
																		<span class="text-muted small">Current User</span>
																	<?php endif; ?>
																</div>
															</td>
														</tr>
													<?php $no++; endforeach; ?>
												<?php else: ?>
													<tr>
														<td colspan="5" class="text-center">No Available Data</td>
													</tr>
												<?php endif; ?>
											</tbody>
											<tfoot>
												<tr>
													<th scope="col">No.</th>
													<th scope="col">Username</th>
													<th scope="col">Email</th>
													<th scope="col">Verification</th>
													<th scope="col">Action</th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Modal -->
			<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Create System User</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form method="POST" action="model/save_user.php" enctype="multipart/form-data">
								<input type="hidden" name="size" value="1000000">
								<div class="text-center">
									<div id="my_camera" style="height: 250;" class="text-center">
										<img src="assets/img/person.png" alt="..." class="img img-fluid" width="250">
									</div>
									<div class="form-group d-flex justify-content-center">
										<button type="button" class="btn btn-danger btn-sm mr-2" id="open_cam">Open Camera</button>
										<button type="button" class="btn btn-secondary btn-sm ml-2" onclick="save_photo()">Capture</button>
									</div>
									<div id="profileImage">
										<input type="hidden" name="profileimg">
									</div>
									<div class="form-group">
										<input type="file" class="form-control" name="img" accept="image/*">
									</div>
								</div>
								<div class="form-group">
									<label>Username</label>
									<input type="text" class="form-control" placeholder="Enter Username" name="username" required>
								</div>
								<div class="form-group pb-0">
									<label>Gmail</label>
									<div class="input-group">
										<input type="email" class="form-control" placeholder="Enter Gmail Address" id="reg_email" name="email" required>
										<div class="input-group-append">
											<button class="btn btn-outline-primary" type="button" id="send_code_btn">Send Code</button>
										</div>
									</div>
									<small id="send_status" class="form-text"></small>
								</div>
								<div class="form-group pt-0" id="otp_container" style="display:none;">
									<label>Verification Code</label>
									<div class="input-group">
										<input type="text" class="form-control" placeholder="Enter 6-digit code" id="reg_otp">
										<div class="input-group-append">
											<button class="btn btn-success" type="button" id="verify_code_btn">Verify</button>
										</div>
									</div>
									<small id="verify_status" class="form-text"></small>
								</div>
								<div class="form-group">
									<label>Password</label>
									<input type="password" class="form-control" placeholder="Enter Password" name="pass" required>
								</div>

						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary" id="create_user_btn" disabled>Create</button>
						</div>
						</form>
					</div>
				</div>
			</div>

			<div class="modal fade" id="edit_user_modal" tabindex="-1" role="dialog">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Edit System User</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form method="POST" action="model/edit_user.php">
								<input type="hidden" name="id" id="edit_user_id">
								<div class="form-group">
									<label>Username</label>
									<input type="text" class="form-control" name="username" id="edit_username" required>
								</div>
								<div class="form-group">
									<label>Gmail</label>
									<input type="email" class="form-control" name="email" id="edit_email" required>
									<small class="form-text text-muted">Note: Changing the email will require the user to re-verify.</small>
								</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Update User</button>
						</div>
						</form>
					</div>
				</div>
			</div>

			<script>
				function editUser(btn) {
					const id = btn.getAttribute('data-id');
					const username = btn.getAttribute('data-username');
					const email = btn.getAttribute('data-email');
					const type = btn.getAttribute('data-type');

					document.getElementById('edit_user_id').value = id;
					document.getElementById('edit_username').value = username;
					document.getElementById('edit_email').value = email;
				}

				document.getElementById('send_code_btn').addEventListener('click', function() {
					const email = document.getElementById('reg_email').value;
					const btn = this;
					const status = document.getElementById('send_status');
					
					if(!email) {
						alert("Please enter a Gmail address first.");
						return;
					}

					btn.disabled = true;
					btn.innerText = "Sending...";
					status.innerHTML = "Sending code to " + email + "...";
					status.className = "form-text text-muted";

					fetch('model/send_reg_otp.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'email=' + encodeURIComponent(email)
					})
					.then(response => response.json())
					.then(data => {
						if(data.status === 'success') {
							status.innerHTML = data.message;
							status.className = "form-text text-success";
							document.getElementById('otp_container').style.display = 'block';
							btn.innerText = "Resend Code";
							setTimeout(() => { btn.disabled = false; }, 30000); // 30s cooldown
						} else {
							status.innerHTML = data.message;
							status.className = "form-text text-danger";
							btn.disabled = false;
							btn.innerText = "Send Code";
						}
					})
					.catch(error => {
						console.error('Error:', error);
						status.innerHTML = "Error sending code. Check console.";
						status.className = "form-text text-danger";
						btn.disabled = false;
					});
				});

				document.getElementById('verify_code_btn').addEventListener('click', function() {
					const otp = document.getElementById('reg_otp').value;
					const status = document.getElementById('verify_status');
					const createBtn = document.getElementById('create_user_btn');
					
					if(!otp) {
						alert("Please enter the code sent to Gmail.");
						return;
					}

					// Verification check (this could be AJAX too, but we can also just compare against a session-stored value in save_user.php)
					// To be robust, let's just mark it as "Ready" in the frontend and let save_user.php do the real check.
					// However, for UX, let's add a small script to verify the code without saving yet.
					this.innerText = "Checking...";
					
					fetch('model/verify_reg_otp.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'otp=' + encodeURIComponent(otp)
					})
					.then(response => response.json())
					.then(data => {
						if(data.status === 'success') {
							status.innerHTML = "Email verified successfully!";
							status.className = "form-text text-success";
							this.disabled = true;
							this.innerText = "Verified";
							document.getElementById('reg_email').readOnly = true;
							document.getElementById('reg_otp').readOnly = true;
							createBtn.disabled = false;
						} else {
							status.innerHTML = "Invalid code. Please try again.";
							status.className = "form-text text-danger";
							this.innerText = "Verify";
						}
					});
				});
			</script>

			<!-- Main Footer -->
			<?php include "templates/main-footer.php"; ?>
			<!-- End Main Footer -->

		</div>

	</div>
	<?php include "templates/footer.php"; ?>
</body>

</html>