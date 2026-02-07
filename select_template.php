<?php
require "./bootstrap/index.php";

$resident_id = $_GET["resident_id"];
$cert_id = $_GET["cert_id"];

if (in_array($cert_id, [2, 3, 6])) {
    header("Location: dashboard.php");
    exit;
}

if ($cert_id == 5) {
    // Business Permit uses tblpermit
    $resident = $db->from("tblpermit")->where("id", $resident_id)->first()->exec();
    // Map business name to firstname for UI display
    $resident['firstname'] = $resident['name'];
    $resident['lastname'] = "";
} else {
    $resident = $db->from("residents")->where("id", $resident_id)->first()->exec();
}

$certificate = $db->from("certificates")->where("id", $cert_id)->first()->exec();
$templates = $db->from("certificate_templates")->where("certificate_id", $cert_id)->exec();

if (!$resident || !$certificate) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "templates/header.php"; ?>
    <title>Select Design - <?= $certificate['name'] ?></title>
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
                                <h2 class="text-white fw-bold">Select Certificate Design</h2>
                                <h5 class="text-white opacity-7 ml-2">For: <?= $resident['firstname'] ?> <?= $resident['lastname'] ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="page-inner mt--5">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-body">
                                    <div class="row justify-content-center">
                                        <?php if (!empty($templates)): ?>
                                            <div class="col-md-12 mb-4">
                                                <h4 class="fw-bold">Available Visual Designs for <?= $certificate['name'] ?></h4>
                                            </div>
                                            <?php foreach ($templates as $tmpl): ?>
                                                <div class="col-md-4">
                                                    <div class="card card-post card-round border shadow-sm h-100">
                                                        <div class="card-body text-center p-4">
                                                            <div class="mb-4">
                                                                <div class="avatar avatar-xl">
                                                                    <div class="avatar-title rounded-circle border border-white bg-primary">
                                                                        <i class="fas fa-certificate fa-lg"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <h4 class="fw-bold mb-1"><?= $tmpl['name'] ?></h4>
                                                            <p class="text-muted small mb-4">Visual Template</p>
                                                            <a href="generate_visual.php?template_id=<?= $tmpl['id'] ?>&resident_id=<?= $resident_id ?>&cert_id=<?= $cert_id ?>" class="btn btn-primary btn-round">
                                                                Select Design
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-md-12 text-center py-5">
                                                <div class="mb-4">
                                                    <i class="fas fa-magic fa-5x text-muted opacity-5"></i>
                                                </div>
                                                <h2 class="fw-bold">No visual designs found</h2>
                                                <p class="text-muted">Design a stunning certificate first in the Certificate Designs section.</p>
                                                <a href="certificate_templates.php" class="btn btn-info btn-round px-5">Go to Designer</a>
                                            </div>
                                        <?php endif; ?>
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
</body>
</html>
