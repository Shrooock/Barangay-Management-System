<?php
require_once "./bootstrap/index.php";

// Only administrators can view logs
if ($_SESSION["role"] !== "administrator") {
    header("Location: dashboard.php");
    exit;
}

// Self-healing: Ensure activity_log table exists
$table_check = $conn->query("SHOW TABLES LIKE 'activity_log'");
if ($table_check->num_rows == 0) {
    $create_table = "CREATE TABLE `activity_log` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT UNSIGNED DEFAULT NULL, 
        `action` VARCHAR(50) NOT NULL,    
        `target_type` VARCHAR(50) NOT NULL, 
        `target_name` VARCHAR(100) DEFAULT NULL, 
        `details` TEXT DEFAULT NULL,      
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($create_table);
}

$logs = $db
    ->from(["activity_log" => "logs"])
    ->leftJoin(["users" => "u"], "u.id", "logs.user_id")
    ->orderBy("logs.created_at", "desc")
    ->select([
        "id" => "logs.id",
        "username" => "u.username",
        "action" => "logs.action",
        "target_type" => "logs.target_type",
        "target_name" => "logs.target_name",
        "details" => "logs.details",
        "created_at" => "logs.created_at",
    ])
    ->exec();
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php include "templates/header.php"; ?>
        <title>Activity Logs - Barangay Services Management System</title>
    </head>

    <body>
        <?php include "templates/loading_screen.php"; ?>
        <div class="wrapper">
            <?php include "templates/main-header.php"; ?>
            <?php include "templates/sidebar.php"; ?>

            <div class="main-panel">
                <div class="content">
                    <div class="panel-header bg-primary-gradient">
                        <div class="page-inner">
                            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                                <div>
                                    <h2 class="text-white fw-bold">Audit Trail</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="page-inner">
                        <div class="row mt--2">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-head-row">
                                            <div class="card-title">Activity Logs</div>
                                            <div class="card-tools">
                                                <a href="model/clear_issued_certificates.php"
                                                    onclick="return confirm('Are you sure you want to clear the issued certificate history? This will reset the dashboard counters.');"
                                                    class="btn btn-warning btn-border btn-round btn-sm mr-2">
                                                    <i class="fa fa-trash"></i>
                                                    Clear Issued History
                                                </a>
                                                <a href="model/clear_activity_logs.php"
                                                    onclick="return confirm('Are you sure you want to clear all activity logs? This action cannot be undone.');"
                                                    class="btn btn-danger btn-border btn-round btn-sm">
                                                    <i class="fa fa-trash"></i>
                                                    Clear Logs
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="logTable" class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date & Time</th>
                                                        <th>User</th>
                                                        <th>Action</th>
                                                        <th>Target</th>
                                                        <th>Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($logs)): ?>
                                                        <?php foreach ($logs as $row): ?>
                                                            <tr>
                                                                <td><?= date('M d, Y h:i A', strtotime($row["created_at"])) ?>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-info">
                                                                        <?= $row["username"] ? ucwords($row["username"]) : 'System/Deleted User' ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    $badge = 'secondary';
                                                                    if ($row['action'] == 'ADD')
                                                                        $badge = 'success';
                                                                    if ($row['action'] == 'EDIT')
                                                                        $badge = 'warning';
                                                                    if ($row['action'] == 'DELETE')
                                                                        $badge = 'danger';
                                                                    if ($row['action'] == 'PRINT')
                                                                        $badge = 'primary';
                                                                    ?>
                                                                    <span
                                                                        class="badge badge-<?= $badge ?>"><?= $row["action"] ?></span>
                                                                </td>
                                                                <td>
                                                                    <strong><?= $row["target_type"] ?></strong>:
                                                                    <?= $row["target_name"] ?>
                                                                </td>
                                                                <td><small><?= $row["details"] ?></small></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center">No logs found</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <?php include "templates/main-footer.php"; ?>
        </div>
        </div>
        <?php include "templates/footer.php"; ?>
        <script>
            $(document).ready(function () {
                $('#logTable').DataTable({
                    "order": [[0, "desc"]]
                });
                $('#issuedTable').DataTable({
                    "order": [[0, "desc"]]
                });
            });
        </script>
    </body>

</html>
