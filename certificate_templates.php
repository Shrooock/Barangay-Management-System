<?php
require "./bootstrap/index.php";

if (!isAdmin()) {
    header("Location: dashboard.php");
    exit;
}

$templates = $db
    ->from(["certificate_templates" => "ct"])
    ->join(["certificates" => "c"], "c.id", "ct.certificate_id")
    ->whereNotIn("ct.certificate_id", [2, 3, 6])
    ->select([
        "id" => "ct.id",
        "name" => "ct.name",
        "cert_id" => "ct.certificate_id",
        "cert_name" => "c.name",
        "is_default" => "ct.is_default",
        "created_at" => "ct.created_at"
    ])
    ->exec();

$certificates = $db->from("certificates")->whereNotIn("id", [2, 3, 6])->exec();
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php include "templates/header.php"; ?>
        <title>Certificate Visual Designer - Barangay Services Management System</title>
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
                                    <h2 class="text-white fw-bold">Certificate Visual Designer</h2>
                                    <h5 class="text-white op-7">Drag, resize, and edit elements to create stunning
                                        certificates</h5>
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
                                            <div class="card-title">All Designs</div>
                                            <div class="card-tools">
                                                <button class="btn btn-info btn-border btn-round btn-sm"
                                                    data-toggle="modal" data-target="#addTemplateModal">
                                                    <i class="fa fa-plus"></i> Create New Design
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php include "templates/alert.php"; ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Design Name</th>
                                                        <th>Category</th>
                                                        <th>Status</th>
                                                        <th>Last Modified</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($templates)): ?>
                                                        <?php foreach ($templates as $row): ?>
                                                            <tr>
                                                                <td>
                                                                    <?= ucwords($row["name"]) ?>
                                                                    <?php if ($row['is_default']): ?>
                                                                        <span class="badge badge-success ml-2">Main</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= $row["cert_name"] ?></td>
                                                                <td>
                                                                    <?php if ($row['is_default']): ?>
                                                                        <span class="text-success font-weight-bold">Active
                                                                            Main</span>
                                                                    <?php else: ?>
                                                                        <button type="button" class="btn btn-outline-primary btn-xs"
                                                                            onclick="setAsMain(<?= $row['id'] ?>, <?= $row['cert_id'] ?>)">
                                                                            Set as Main
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= date('M d, Y', strtotime($row["created_at"])) ?></td>
                                                                <td>
                                                                    <div class="form-button-action">
                                                                        <a href="designer.php?id=<?= $row['id'] ?>"
                                                                            class="btn btn-link btn-primary"
                                                                            title="Open Designer">
                                                                            <i class="fas fa-magic"></i> Designer
                                                                        </a>
                                                                        <button type="button"
                                                                            onclick="deleteTemplate(<?= $row['id'] ?>)"
                                                                            class="btn btn-link btn-danger" title="Remove">
                                                                            <i class="fa fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center">No designs found</td>
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
                <?php include "templates/main-footer.php"; ?>
            </div>
        </div>

        <!-- Add Template Modal -->
        <div class="modal fade" id="addTemplateModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="model/templates.php">
                        <div class="modal-header border-0">
                            <h5 class="modal-title"><span class="fw-mediumbold">New</span> <span
                                    class="fw-light">Design</span></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Design/Template Name</label>
                                <input type="text" class="form-control" name="name"
                                    placeholder="e.g. Modern Brgy Clearance" required>
                            </div>
                            <div class="form-group">
                                <label>Certificate Type</label>
                                <select class="form-control" name="certificate_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($certificates as $cert): ?>
                                        <option value="<?= $cert['id'] ?>"><?= $cert['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Paper Size</label>
                                <select class="form-control" name="paper_size" required>
                                    <option value="A4">A4 (210mm x 297mm)</option>
                                    <option value="Letter">Letter (8.5in x 11in)</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="btn btn-primary">Create</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include "templates/footer.php"; ?>
        <script>
            function deleteTemplate(id) {
                if (confirm('Are you sure you want to delete this design?')) {
                    window.location.href = 'model/templates.php?action=delete&id=' + id;
                }
            }

            function setAsMain(id, cert_id) {
                $.ajax({
                    type: 'POST',
                    url: 'model/templates.php',
                    data: {
                        action: 'set_default',
                        id: id,
                        certificate_id: cert_id
                    },
                    success: function (response) {
                        let res = JSON.parse(response);
                        if (res.status == 'success') {
                            location.reload();
                        } else {
                            alert('Error setting main template');
                        }
                    }
                });
            }
        </script>
    </body>

</html>
