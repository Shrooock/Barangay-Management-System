<?php include 'bootstrap/index.php' ?>
<?php
// Query to fetch officials - ensure Barangay Captain is always visible
if (isset($_SESSION['role'])) {
	if ($_SESSION['role'] == 'staff') {
		// For staff: show active officials, but always include Barangay Captain
		$off_q = "SELECT *,tblofficials.id as id, tblposition.id as pos_id FROM tblofficials JOIN tblposition ON tblposition.id=tblofficials.position WHERE (`status`='Active' OR tblposition.id=4) ORDER BY CASE WHEN tblposition.id=4 THEN 0 ELSE tblposition.order END ASC, tblposition.order ASC ";
	} else {
		// For admin: show all officials, Barangay Captain first
		$off_q = "SELECT *,tblofficials.id as id, tblposition.id as pos_id FROM tblofficials JOIN tblposition ON tblposition.id=tblofficials.position ORDER BY CASE WHEN tblposition.id=4 THEN 0 ELSE tblposition.order END ASC, tblposition.order ASC, `status` ASC ";
	}
} else {
	// For public: show active officials, but always include Barangay Captain
	$off_q = "SELECT *,tblofficials.id as id, tblposition.id as pos_id FROM tblofficials JOIN tblposition ON tblposition.id=tblofficials.position WHERE (`status`='Active' OR tblposition.id=4) ORDER BY CASE WHEN tblposition.id=4 THEN 0 ELSE tblposition.order END ASC, tblposition.order ASC ";
}

$res_o = $conn->query($off_q);

$official = array();
while ($row = $res_o->fetch_assoc()) {
	$official[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <?php include 'templates/header.php' ?>
    <title>Brg Officials and Staff - Barangay Services Management System</title>
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
                  <h2 class="text-white fw-bold">Barangay Officials</h2>
                </div>
              </div>
            </div>
          </div>
          <div class="page-inner">

            <?php include "templates/alert.php"; ?>

            <div class="row mt--2">

              <div class="col-md-12">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex flex-wrap pb-2 justify-content-between">
                      <div class="px-2 pb-2 pb-md-0 text-center">
                        <img src="assets/uploads/<?= $city_logo ?>" class="img-fluid" width="100">
                      </div>
                      <div class="px-2 pb-2 pb-md-0 text-center">
                        <h2 class="fw-bold mt-3"><?= ucwords($brgy) ?></h2>
                        <h4 class="fw-bold mt-3"><i><?= ucwords($town) ?></i></h4>
                      </div>
                      <div class="px-2 pb-2 pb-md-0 text-center">
                        <img src="assets/uploads/<?= $brgy_logo ?>" class="img-fluid" width="100">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card">
                  <div class="card-header">
                    <div class="card-head-row">
                      <div class="card-title">Current Barangay Officials</div>
                      <?php if (isset($_SESSION['username'])) : ?>
                      <div class="card-tools">
                        <a href="#add" data-toggle="modal" class="btn btn-info btn-border btn-round btn-sm">
                          <i class="fa fa-plus"></i>
                          Official
                        </a>
                      </div>
                      <?php endif ?>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th scope="col">Photo</th>
                            <th scope="col">Fullname</th>
                            <th scope="col">Position</th>
                            <th scope="col">Start of Term</th>
                            <th scope="col">End of Term</th>
                            <?php if (isset($_SESSION['username'])) : ?>
                            <?php if ($_SESSION['role'] == 'administrator') : ?>
                            <th>Status</th>
                            <?php endif ?>

                            <th>Action</th>
                            <?php endif ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!empty($official)) : ?>
                          <?php foreach ($official as $row) : ?>
                          <?php 
                            $is_chairman = ($row['pos_id'] == 4 || stripos($row['position'], 'Captain') !== false || stripos($row['position'], 'Chairman') !== false);
                            $row_class = $is_chairman ? 'table-primary' : '';
                            $row_style = $is_chairman ? 'font-weight: bold;' : '';
                          ?>
                          <tr class="<?= $row_class ?>" style="<?= $row_style ?>">
                            <td>
                              <?php if (!empty($row['image'])): ?>
                                <img src="assets/uploads/<?= $row['image'] ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                              <?php else: ?>
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: #e0e0e0; display: flex; align-items: center; justify-content: center;">
                                  <i class="fa fa-user" style="color: #999;"></i>
                                </div>
                              <?php endif; ?>
                            </td>
                            <td class="text-uppercase"><?= $row['name'] ?></td>
                            <td>
                              <?= $row['position'] ?>
                              <?php if ($is_chairman): ?>
                                <span class="badge badge-info ml-1">Chairman</span>
                              <?php endif; ?>
                            </td>
                            <td><?= $row['termstart'] ?></td>
                            <td><?= $row['termend'] ?></td>



                            <?php if (isset($_SESSION['username'])) : ?>
                            <?php if ($_SESSION['role'] == 'administrator') : ?>
                            <td>
                              <?= $row['status'] == 'Active' ? '<span class="badge badge-primary">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?>
                            </td>
                            <?php endif ?>
                            <td>
                              <a type="button" href="#edit" data-toggle="modal" class="btn btn-link btn-primary"
                                title="Edit Position" onclick="editOfficial(this)" data-id="<?= $row['id'] ?>"
                                data-name="<?= $row['name'] ?>"
                                data-pos="<?= $row['pos_id'] ?>" data-start="<?= $row['termstart'] ?>"
                                data-end="<?= $row['termend'] ?>" data-status="<?= $row['status'] ?>"
                                data-image="<?= $row['image'] ?? '' ?>">
                                <i class="fa fa-edit"></i>
                              </a>
                              <?php if ($_SESSION['role'] == 'administrator') : ?>
                              <a type="button" data-toggle="tooltip"
                                href="model/remove_official.php?id=<?= $row['id'] ?>"
                                onclick="return confirm('Are you sure you want to delete this official?');"
                                class="btn btn-link btn-danger" data-original-title="Remove">
                                <i class="fa fa-times"></i>
                              </a>
                              <?php endif ?>
                            </td>
                            <?php endif ?>
                          </tr>
                          <?php endforeach ?>
                          <?php 
                          // Check if Barangay Captain exists
                          $has_captain = false;
                          foreach ($official as $row) {
                            if ($row['pos_id'] == 4 || stripos($row['position'], 'Captain') !== false || stripos($row['position'], 'Chairman') !== false) {
                              $has_captain = true;
                              break;
                            }
                          }
                          if (!$has_captain && isset($_SESSION['username'])): ?>
                          <tr class="table-warning">
                            <td colspan="<?= isset($_SESSION['username']) && $_SESSION['role'] == 'administrator' ? '7' : '5' ?>" class="text-center">
                              <i class="fa fa-info-circle"></i> <strong>No Barangay Captain found.</strong> Please add a Barangay Captain using the "+ Official" button above.
                            </td>
                          </tr>
                          <?php endif; ?>
                          <?php endif; ?>
                          <?php if (empty($official)) : ?>
                          <tr>
                            <td colspan="<?= isset($_SESSION['username']) && $_SESSION['role'] == 'administrator' ? '7' : '5' ?>" class="text-center">No Available Data</td>
                          </tr>
                          <?php endif ?>
                        </tbody>
                        <tfoot>
                          <tr>
                            <th scope="col">Photo</th>
                            <th scope="col">Fullname</th>
                            <th scope="col">Position</th>
                            <?php if (isset($_SESSION['username'])) : ?>
                            <?php if ($_SESSION['role'] == 'administrator') : ?>
                            <th>Status</th>
                            <?php endif ?>
                            <th scope="col">Start of Term</th>
                            <th scope="col">End of Term</th>
                            <th>Action</th>
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

        <!-- Modal -->
        <div class="modal fade" id="add">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Create Official</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="model/officials.php" enctype="multipart/form-data">
                  <div class="form-group text-center">
                    <label class="fw-bold">Official Photo</label>
                    <div class="mt-2 mb-2">
                      <img id="add_photo_preview" src="assets/img/person.png" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 2px solid #ddd;">
                    </div>
                    <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'add_photo_preview')">
                  </div>
                  <div class="form-group">
                    <label>Fullname</label>
                    <input type="text" class="form-control" placeholder="Enter Fullname" name="name" required>
                  </div>
                  <div class="form-group">
                    <label>Position <span class="text-danger">*</span></label>
                    <select class="form-control" required name="position" id="add_position">
                      <option value="" disabled selected>Select Official Position</option>
                      <?php if (!empty($position)): ?>
                        <?php 
                        // Separate Barangay Captain and other positions
                        $captain = null;
                        $other_positions = [];
                        foreach ($position as $row) {
                          if ($row['id'] == 4 || stripos($row['position'], 'Captain') !== false) {
                            $captain = $row;
                          } else {
                            $other_positions[] = $row;
                          }
                        }
                        // Show Barangay Captain first
                        if ($captain): ?>
                        <option value="<?= $captain['id'] ?>" style="font-weight: bold;">⭐ Brgy. <?= $captain['position'] ?> (Chairman)</option>
                        <optgroup label="────────────"></optgroup>
                        <?php endif; ?>
                        <?php foreach ($other_positions as $row) : ?>
                        <option value="<?= $row['id'] ?>">Brgy. <?= $row['position'] ?></option>
                        <?php endforeach ?>
                      <?php else: ?>
                        <option value="" disabled>No position available</option>
                      <?php endif ?>
                    </select>
                    <?php if (empty($position)): ?>
                    <small class="text-danger">Please add position options first in the Position page.</small>
                    <?php endif ?>
                  </div>
                  <div class="form-group">
                    <label>Term Start</label>
                    <input type="date" class="form-control" name="start" required>
                  </div>
                  <div class="form-group">
                    <label>Term End</label>
                    <input type="date" class="form-control" name="end" required>
                  </div>
                  <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" required name="status">
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                    </select>
                  </div>

              </div>
              <div class="modal-footer">
                <input type="hidden" id="pos_id" name="id">
                <input type="hidden" name="register-official" value="1">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Create</button>
              </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Official</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="model/edit_official.php" enctype="multipart/form-data">
                  <div class="form-group text-center">
                    <label class="fw-bold">Official Photo</label>
                    <div class="mt-2 mb-2">
                      <img id="edit_photo_preview" src="assets/img/person.png" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 2px solid #ddd;">
                    </div>
                    <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'edit_photo_preview')">
                    <input type="hidden" id="current_image" name="current_image">
                    <small class="text-muted">Leave empty to keep current photo</small>
                  </div>
                  <div class="form-group">
                    <label>Fullname</label>
                    <input type="text" class="form-control" id="name" placeholder="Enter Fullname" name="name" required>
                  </div>
                  <div class="form-group">
                    <label>Position</label>
                    <select class="form-control" id="position" required name="position">
                      <option disabled selected>Select Official Position</option>
                      <?php foreach ($position as $row) : ?>
                      <option value="<?= $row['id'] ?>">Brgy. <?= $row['position'] ?></option>
                      <?php endforeach ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Term Start</label>
                    <input type="date" class="form-control" id="start" name="start" required>
                  </div>
                  <div class="form-group">
                    <label>Term End</label>
                    <input type="date" class="form-control" id="end" name="end" required>
                  </div>
                  <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" id="status" required name="status">
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                    </select>
                  </div>

              </div>
              <div class="modal-footer">
                <input type="hidden" id="off_id" name="id">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
              </div>
              </form>
            </div>
          </div>
        </div>
        <!-- Main Footer -->
        <?php include 'templates/main-footer.php' ?>
        <!-- End Main Footer -->

      </div>

    </div>
    <?php include 'templates/footer.php' ?>
    <script>
    // Image preview function
    function previewImage(input, previewId) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Form validation before submission
    document.addEventListener('DOMContentLoaded', function() {
      var addForm = document.querySelector('form[action="model/officials.php"]');
      if (addForm) {
        addForm.addEventListener('submit', function(e) {
          var position = document.getElementById('add_position');
          
          if (!position || !position.value || position.value === '') {
            e.preventDefault();
            alert('Please select a Position');
            position.focus();
            return false;
          }
        });
      }
    });

    // Edit official - populate form including image
    function editOfficial(element) {
      var id = element.getAttribute('data-id');
      var name = element.getAttribute('data-name');
      var pos = element.getAttribute('data-pos');
      var start = element.getAttribute('data-start');
      var end = element.getAttribute('data-end');
      var status = element.getAttribute('data-status');
      var image = element.getAttribute('data-image');

      document.getElementById('off_id').value = id;
      document.getElementById('name').value = name;
      document.getElementById('position').value = pos;
      document.getElementById('start').value = start;
      document.getElementById('end').value = end;
      document.getElementById('status').value = status;
      document.getElementById('current_image').value = image;

      // Set preview image
      if (image) {
        document.getElementById('edit_photo_preview').src = 'assets/uploads/' + image;
      } else {
        document.getElementById('edit_photo_preview').src = 'assets/img/person.png';
      }
    }
    </script>
  </body>

</html>
