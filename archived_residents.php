<?php
include "bootstrap/index.php";

if (!isAdmin()) {
	header("Location: dashboard.php");
}


$residentList = (function () use ($db) {
	return $db
    ->from("residents")
    ->whereRaw("residents.deleted_at IS NOT NULL")
    ->orderBy("residents.id", "desc")
    ->select([
      "id" => "residents.id",
      "national_id" => "residents.national_id",
      "account_id" => "residents.account_id",
      "citizenship" => "residents.citizenship",
      "avatar" => "residents.picture",
      "firstname" => "residents.firstname",
      "middlename" => "residents.middlename",
      "lastname" => "residents.lastname",
      "alias" => "residents.alias",
      "birthdate" => "residents.birthdate",
      "age" => "residents.age",
      "gender" => "residents.gender",
      "deleted_at" => "residents.deleted_at",
    ])
    ->exec();
})();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include "templates/header.php"; ?>
  <title>Archived Residents - Barangay Services Management System</title>
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
                <h2 class="text-white fw-bold">Archived Residents</h2>
              </div>
            </div>
          </div>
        </div>
        <div class="page-inner">
          <div class="row mt--2">
            <div class="col-md-12">

              <?php include "templates/alert.php"; ?>

              <div class="card">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">Archived Resident Records</div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="residenttable" class="display table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">Fullname</th>
                          <th scope="col">National ID</th>
                          <th scope="col">Age</th>
                          <th scope="col">Gender</th>
                          <th scope="col">Archived At</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (!empty($residentList)): ?>
                          <?php foreach ($residentList as $row): ?>
                            <tr>
                              <td>
                                <?= ucwords($row["lastname"] . ", " . $row["firstname"]) ?>
                              </td>
                              <td><?= $row["national_id"] ?></td>
                              <td><?= $row["age"] ?></td>
                              <td><?= $row["gender"] ?></td>
                              <td><?= date('M d, Y h:i A', strtotime($row["deleted_at"])) ?></td>
                              <td>
                                <div class="form-button-action">
                                  <a
                                    type="button"
                                    href="model/restore_resident.php?id=<?= $row["id"] ?>"
                                    class="btn btn-link btn-success"
                                    title="Restore Resident"
                                    onclick="return confirm('Are you sure you want to restore this resident?');"
                                  >
                                    <i class="fa fa-undo"></i> Restore
                                  </a>
                                  <a
                                    type="button"
                                    href="model/residents.php?id=<?= $row["id"] ?>&remove-resident=1"
                                    class="btn btn-link btn-danger"
                                    title="Remove Resident"
                                    onclick="return confirm('Are you sure you want to permanently remove this resident?');"
                                  >
                                    <i class="fa fa-times"></i> Remove
                                  </a>
                                </div>
                              </td>
                            </tr>
                          <?php endforeach; ?>
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

      <!-- Main Footer -->
      <?php include "templates/main-footer.php"; ?>
    </div>
  </div>

  <?php include "templates/footer.php"; ?>

  <script>
    $(document).ready(function() {
      $('#residenttable').DataTable({
        order: []
      });
    });
  </script>
</body>

</html>
