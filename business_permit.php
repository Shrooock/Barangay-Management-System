<?php
require "./bootstrap/index.php";

$permit = $db->from("tblpermit")->exec();

// Fetch default templates for visual designer
$default_templates = $db->from("certificate_templates")->where("is_default", 1)->exec();
$main_templates = [];
foreach ($default_templates as $t) {
  $main_templates[$t['certificate_id']] = $t['id'];
}
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <?php include 'templates/header.php' ?>
    <title>Resident Certificate Issuance - Barangay Services Management System</title>
  </head>

  <body>
    <?php include 'templates/loading_screen.php' ?>
    <div class="wrapper">
      <!-- Main Header -->
      <?php include 'templates/main-header.php' ?>
      <!-- End Main Header -->

      <!-- Sidebar -->
      <?php include 'templates/sidebar.php' ?>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="content">
          <div class="panel-header bg-primary-gradient">
            <div class="page-inner">
              <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                <div>
                  <h2 class="text-white fw-bold">Resident Certificate</h2>
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
                      <div class="card-title">Resident Certificate Issuance</div>
                      <?php if (isset($_SESSION['username'])): ?>
                        <div class="card-tools">
                          <a href="#add" data-toggle="modal" class="btn btn-info btn-border btn-round btn-sm">
                            <i class="fa fa-plus"></i>
                            Business Permit
                          </a>
                        </div>
                      <?php endif ?>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table id="residenttable" class="display table table-striped">
                        <thead>
                          <tr>
                            <th scope="col">Name of Business</th>
                            <th scope="col">Business Owner</th>
                            <th scope="col">Nature</th>
                            <th scope="col">Date Applied</th>
                            <?php if (isset($_SESSION['username'])): ?>
                              <th scope="col">Action</th>
                            <?php endif ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!empty($permit)): ?>
                            <?php foreach ($permit as $row): ?>
                              <tr>
                                <td><?= ucwords($row['name']) ?></td>
                                <td>
                                  <?= !empty($row['owner2']) ? ucwords($row['owner1'] . ' & ' . $row['owner2']) : $row['owner1'] ?>
                                </td>
                                <td><?= $row['nature'] ?></td>
                                <td><?= $row['applied'] ?></td>
                                <?php if (isset($_SESSION['username'])): ?>
                                  <td>
                                    <div class="form-button-action">
                                      <?php if ($template_preference == 'standard' || $template_preference == 'both'): ?>
                                        <a type="button" data-toggle="tooltip"
                                          href="generate_business_permit.php?id=<?= $row['id'] ?>"
                                          class="btn btn-link btn-primary" data-original-title="Generate Standard Permit">
                                          <i class="fas fa-file-alt"></i>
                                        </a>
                                      <?php endif; ?>
                                      <?php if (isset($_SESSION['username']) && $_SESSION['role'] == 'administrator'): ?>
                                        <a type="button" href="#edit" data-toggle="modal" class="btn btn-link btn-secondary"
                                          title="Edit Business Permit" onclick="editPermit(this)" data-id="<?= $row['id'] ?>"
                                          data-name="<?= $row['name'] ?>" data-owner1="<?= $row['owner1'] ?>"
                                          data-owner2="<?= $row['owner2'] ?>" data-nature="<?= $row['nature'] ?>"
                                          data-applied="<?= $row['applied'] ?>" data-tin="<?= $row['tin'] ?>"
                                          data-cert_no="<?= $row['cert_number'] ?>"
                                          data-address="<?= $row['business_address'] ?>">
                                          <i class="fa fa-edit"></i>
                                        </a>
                                        <a type="button" data-toggle="tooltip"
                                          href="model/remove_permit.php?id=<?= $row['id'] ?>"
                                          onclick="return confirm('Are you sure you want to delete this business permit?');"
                                          class="btn btn-link btn-danger" data-original-title="Remove">
                                          <i class="fa fa-times"></i>
                                        </a>
                                      <?php endif ?>
                                    </div>
                                  </td>
                                <?php endif ?>

                              </tr>
                            <?php endforeach ?>
                          <?php endif ?>
                        </tbody>
                        <tfoot>
                          <tr>
                            <th scope="col">Name of Business</th>
                            <th scope="col">Business Owner</th>
                            <th scope="col">Nature</th>
                            <th scope="col">Date Applied</th>
                            <?php if (isset($_SESSION['username'])): ?>
                              <th scope="col">Action</th>
                            <?php endif ?>
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

        <!-- Main Footer -->
        <?php include 'templates/main-footer.php' ?>
        <!-- End Main Footer -->

        <!-- Modal -->
        <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Create Business Permit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="model/save_permit.php">
                  <div class="form-group">
                    <label>Business Name</label>
                    <input type="text" class="form-control" placeholder="Enter Business Name" name="name" required>
                  </div>
                  <div class="form-group">
                    <label>Business Owner</label>
                    <input type="text" class="form-control mb-2" placeholder="Enter Owner Name" name="owner1" required>
                    <input type="text" class="form-control" placeholder="Enter Owner Name" name="owner2">
                  </div>
                  <div class="form-group">
                    <label>Business Address</label>
                    <textarea class="form-control" placeholder="Enter Business Address" name="address"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Business Nature</label>
                    <input type="text" class="form-control" placeholder="Sari-Sari Store/Warter Refill Station"
                      name="nature" required>
                  </div>
                  <div class="form-group">
                    <label>Date Applied</label>
                    <input type="date" class="form-control" name="applied" value="<?= date('Y-m-d'); ?>" required>
                  </div>
                  <div class="form-group">
                    <label>TIN</label>
                    <input type="text" class="form-control" placeholder="Enter TIN" name="tin">
                  </div>
                  <div class="form-group">
                    <label>Certificate No.</label>
                    <input type="text" class="form-control" placeholder="Enter Certificate Number" name="cert_no">
                  </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Create</button>
              </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Business Permit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="model/edit_permit.php">
                  <div class="form-group">
                    <label>Business Name</label>
                    <input type="text" class="form-control" placeholder="Enter Business Name" id="name" name="name"
                      required>
                  </div>
                  <div class="form-group">
                    <label>Business Owner</label>
                    <input type="text" class="form-control mb-2" placeholder="Enter Owner Name" id="owner1"
                      name="owner1" required>
                    <input type="text" class="form-control" placeholder="Enter Owner Name" id="owner2" name="owner2">
                  </div>
                  <div class="form-group">
                    <label>Business Address</label>
                    <textarea class="form-control" placeholder="Enter Business Address" id="address"
                      name="address"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Business Nature</label>
                    <input type="text" class="form-control" placeholder="Sari-Sari Store/Warter Refill Station"
                      id="nature" name="nature" required>
                  </div>
                  <div class="form-group">
                    <label>Date Applied</label>
                    <input type="date" class="form-control" id="applied" name="applied" value="<?= date('Y-m-d'); ?>"
                      required>
                  </div>
                  <div class="form-group">
                    <label>TIN</label>
                    <input type="text" class="form-control" placeholder="Enter TIN" id="tin" name="tin">
                  </div>
                  <div class="form-group">
                    <label>Certificate No.</label>
                    <input type="text" class="form-control" placeholder="Enter Certificate Number" id="cert_no"
                      name="cert_no">
                  </div>

              </div>
              <div class="modal-footer">
                <input type="hidden" id="p_id" name="id">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
              </div>
              </form>
            </div>
          </div>
        </div>

      </div>
    </div>
    <?php include 'templates/footer.php' ?>
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    <script>
      $(document).ready(function () {
        $('#residenttable').DataTable();
      });

      function editPermit(that) {
        id = $(that).attr('data-id');
        name = $(that).attr('data-name');
        owner1 = $(that).attr('data-owner1');
        owner2 = $(that).attr('data-owner2');
        nature = $(that).attr('data-nature');
        applied = $(that).attr('data-applied');
        tin = $(that).attr('data-tin');
        cert_no = $(that).attr('data-cert_no');
        address = $(that).attr('data-address');

        $('#p_id').val(id);
        $('#name').val(name);
        $('#owner1').val(owner1);
        $('#owner2').val(owner2);
        $('#nature').val(nature);
        $('#applied').val(applied);
        $('#tin').val(tin);
        $('#cert_no').val(cert_no);
        $('#address').val(address);

      }
    </script>
  </body>

</html>
