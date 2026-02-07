<footer class="footer">
  <div class="container-fluid">
    <div class="copyright ml-auto">
      2025 </div>
  </div>
</footer>
<!-- Modal -->
<div class="modal fade" id="barangay" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST" action="model/edit_brgy_info.php" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Update Barangay Info</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="size" value="1000000">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Province Name</label>
                <input type="text" class="form-control" placeholder="Enter Province Name" name="province" required
                  value="<?= $province ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Town Name</label>
                <input type="text" class="form-control" placeholder="Enter Town Name" name="town" required
                  value="<?= $town ?>">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Barangay Address</label>
                <textarea class="form-control" name="address" placeholder="Enter Barangay Address"
                  required><?= $address ?></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Barangay Name</label>
                <input type="text" class="form-control" placeholder="Enter Barangay Name" name="brgy" required
                  value="<?= $brgy ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Contact Number</label>
                <input type="text" class="form-control" placeholder="Enter Contact Number" name="number" required
                  value="<?= $number ?>">
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label class="fw-bold">Certificate Issuance Settings</label>
                <select class="form-control" name="template_preference">
                  <option value="both" <?= (isset($template_preference) && $template_preference == 'both') ? 'selected' : '' ?>>Show Both (Standard & Visual Designer)</option>
                  <option value="standard" <?= (isset($template_preference) && $template_preference == 'standard') ? 'selected' : '' ?>>Standard Only (Old Templates)</option>
                  <option value="visual" <?= (isset($template_preference) && $template_preference == 'visual') ? 'selected' : '' ?>>Visual Designer Only</option>
                </select>
                <small class="form-text text-muted">Choose which print buttons appear on resident lists.</small>
              </div>
            </div>
          </div>
          <hr>
          <h5 class="fw-bold mb-2">SENDER Gmail Settings (Required for OTP)</h5>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Sender Gmail Address</label>
                <input type="email" class="form-control" name="smtp_username" value="<?= $smtp_username ?>"
                  placeholder="official-barangay@gmail.com" required>
                <small class="form-text text-muted">The Gmail account that sends the codes.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Gmail App Password</label>
                <input type="password" class="form-control" name="smtp_password" value="<?= $smtp_password ?>"
                  placeholder="16-character code" required>
                <small class="form-text text-muted">NOT your regular password. Use a <a
                    href="https://myaccount.google.com/apppasswords" target="_blank">Google App Password</a>.</small>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Municipality/City Logo</label><br>
                <img src="assets/uploads/<?= $city_logo ?>" id="prev_city_logo" class="img-fluid" width="120">
                <input type="file" class='form-control' name="city_logo" accept="image/*"
                  onchange="previewImage(this, 'prev_city_logo')">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Barangay Logo</label><br>
                <img src="assets/uploads/<?= $brgy_logo ?>" id="prev_brgy_logo" class="img-fluid" width="120">
                <input type="file" class='form-control' name="brgy_logo" accept="image/*"
                  onchange="previewImage(this, 'prev_brgy_logo')">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Background Logo (Watermark)</label><br>
                <?php if (!empty($bg_logo)): ?>
                  <img src="assets/uploads/<?= $bg_logo ?>" id="prev_bg_logo" class="img-fluid" width="120">
                <?php else: ?>
                  <img src="assets/img/person.png" id="prev_bg_logo" class="img-fluid" width="120" style="opacity:0.3">
                <?php endif; ?>
                <input type="file" class='form-control' name="bg_logo" accept="image/*"
                  onchange="previewImage(this, 'prev_bg_logo')">
              </div>
            </div>
          </div>
        </div>
        <small class="form-text text-muted mb-3 mx-4">Note: pls upload only image and not more than 20MB.</small>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="restore" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Restore Database</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="model/restore.php" enctype="multipart/form-data">
          <input type="hidden" name="size" value="1000000">
          <div class="form-group form-floating-label">
            <label>Upload Sql file</label>
            <input type="file" class="form-control" accept=".sql" name="backup_file" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Restore</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
  function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        document.getElementById(previewId).src = e.target.result;
        document.getElementById(previewId).style.opacity = "1";
      }
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>
