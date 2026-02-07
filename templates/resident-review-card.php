<?php

require_once "bootstrap/index.php";

$resident = (function () use ($db) {
	$query = $db
    ->from("residents")
		->first()
    ->select([
      "id" => "residents.id",
      "national_id" => "residents.national_id",
      "account_id" => "residents.account_id",
      "citizenship" => "residents.citizenship",
      "firstname" => "residents.firstname",
      "middlename" => "residents.middlename",
      "lastname" => "residents.lastname",
      "alias" => "residents.alias",
      "birthplace" => "residents.birthplace",
      "birthdate" => "residents.birthdate",
      "age" => "residents.age",
      "civilstatus" => "residents.civilstatus",
      "gender" => "residents.gender",
      "voterstatus" => "residents.voterstatus",
      "identified_as" => "residents.identified_as",
      "phone" => "residents.phone",
      "email" => "residents.email",
      "occupation" => "residents.occupation",
      "address" => "residents.address",
      "is_4ps" => "residents.is_4ps",
      "is_senior" => "residents.is_senior",
      "is_pwd" => "residents.is_pwd",
      "is_solo_parent" => "residents.is_solo_parent",
      "is_head_of_family" => "residents.is_head_of_family",
      "resident_type" => "residents.resident_type",
      "resident_type" => "residents.resident_type",
      "remarks" => "residents.remarks",
      "voter_precinct_number" => "residents.voter_precinct_number",
      "avatar" => "residents.picture",
    ]);

	if (isAdmin() && isset($_GET["resident_id"])) {
		$query->where("residents.id", $_GET["resident_id"]);
	}

	return $query->exec();
})();
?>

<div class="fadeIn card">
  <div class="login-form card-body">

    <?php include "templates/alert.php"; ?>

    <form method="POST" action="model/residents.php" autocomplete="off" enctype="multipart/form-data">
      <div class="row g-5">
        <div class="col-md-4">
          <div style="height: 250;" class="text-center" id="my_camera">
            <img src="<?= imgSrc($resident["avatar"]) ??
            	"assets/img/person.png" ?>" alt="..." class="img "
              width="250" height="250" style="max-height: 250; object-fit: cover;">
          </div>

          <div class="form-group d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-danger btn-sm mr-2" id="open_cam">
              Open Camera
            </button>
            <button type="button" class="btn btn-secondary btn-sm ml-2" onclick="save_photo()">
              Capture
            </button>
          </div>

          <div id="profileImage">
            <input type="hidden" name="profileimg">
          </div>

          <div class="form-group">
            <input type="file" class="form-control" name="img" accept="image/*">
          </div>

          <div class="form-group">
            <label>National ID No.</label>
            <input type="text" class="form-control" name="national_id"
              value="<?= $resident["national_id"] ?>" placeholder="Enter National ID No." required>
          </div>

          <div class="form-group">
            <label>Citizenship</label>
            <input type="text" class="form-control" name="citizenship"
              value="<?= $resident["citizenship"] ?>" placeholder="Enter citizenship" required>
          </div>

          <div class="form-group">
            <label>Address</label>
            <textarea class="form-control" name="address" required placeholder="Enter Address">
            <?= $resident["address"] ?>
          </textarea>
          </div>
        </div>

        <div class="col-md-8">
          <div class="row g-0">
            <div class="col-sm-4">
              <div class="form-group">
                <label>First name</label>
                <input class="form-control" placeholder="Enter First name" name="fname"
                  value="<?= $resident["firstname"] ?>" required>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label>Middle name</label>
                <input class="form-control" placeholder="Enter Middle name" name="mname"
                  value="<?= $resident["middlename"] ?>" required>
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label>Last name</label>
                <input class="form-control" placeholder="Enter Last name" name="lname" required
                  value="<?= $resident["lastname"] ?>">
              </div>
            </div>
          </div>

          <div class="row g-0">
            <div class="col-sm-4">
              <div class="form-group">
                <label>Alias</label>
                <input class="form-control" placeholder="Enter Alias" name="alias"
                  value="<?= $resident["alias"] ?>">
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label>Place of Birth</label>
                <input class="form-control" placeholder="Enter Birthplace" name="birthplace" required
                  value="<?= $resident["birthplace"] ?>">
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label>Birthdate</label>
                <input type="date" class="form-control" placeholder="Enter Birthdate" name="birthdate"
                  required value="<?= $resident["birthdate"] ?>">
              </div>
            </div>
          </div>

          <div class="row g-0">
            <div class="col-sm-3">
              <div class="form-group">
                <label>Age</label>
                <input type="number" class="form-control" placeholder="Enter Age" min="1" name="age" id="age"
                  required value="<?= $resident["age"] ?>" readonly>
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                <label>Class</label>
                <input type="text" class="form-control" placeholder="Class" id="resident_class" readonly 
                  value="<?= $resident['age'] <= 18 ? 'Minor' : 'Adult' ?>">
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                <label>Civil Status</label>

                <select class="form-control" name="civil_status" value="<?= $resident[
                	"civilstatus"
                ] ?>">
                  <option disabled selected>Select Civil Status</option>

                  <option value="Single"
                    <?= ifThen($resident["civilstatus"] == "Single", 'selected="true"') ?>>
                    Single
                  </option>

                  <option value="Married"
                    <?= ifThen($resident["civilstatus"] == "Married", 'selected="true"') ?>>
                    Married
                  </option>

                  <option value="Widow"
                    <?= ifThen($resident["civilstatus"] == "Widow", 'selected="true"') ?>>
                    Widow
                  </option>
                </select>
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                <label>Gender</label>
                <select class="form-control" name="gender" required value="<?= $resident[
                	"gender"
                ] ?>">
                  <option disabled selected>Select Gender</option>

                  <option <?= ifThen(
                  	$resident["gender"] == "Male",
                  	'selected="true"'
                  ) ?> value="Male">
                    Male
                  </option>

                  <option <?= ifThen(
                  	$resident["gender"] == "Female",
                  	'selected="true"'
                  ) ?> value="Female">
                    Female
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="row g-0">
            <div class="col-sm-6">
              <div class="form-group">
                <label>Voters Status</label>
                <select class="form-control vstatus" required name="voter_status"
                  value="<?= $resident["voterstatus"] ?>">
                  <option disabled selected>Select Voters Status</option>

                  <option value="Yes" <?= ifThen(
                  	$resident["voterstatus"] == "Yes",
                  	'selected="true"'
                  ) ?>>
                    Yes
                  </option>

                  <option value="No" <?= ifThen(
                  	$resident["voterstatus"] == "No",
                  	'selected="true"'
                  ) ?>>
                    No
                  </option>
                </select>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label>Voter's Precinct Number</label>
                <input class="form-control" name="voter_precinct_number"
                  value="<?= $resident["voter_precinct_number"] ?>" placeholder="Enter Precinct Number">
              </div>
            </div>

          </div>

          <div class="row g-0">
            <div class="col-sm-4">
              <div class="form-group">
                <label>Email Address</label>
                <input type="email" class="form-control" placeholder="Enter Email" name="email"
                  value="<?= $resident["email"] ?>">
              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label>Contact Number</label>
                <input class="form-control" placeholder="Enter Contact Number" name="number"
                  value="<?= $resident["phone"] ?>">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Occupation</label>
                <input class="form-control" placeholder="Enter Occupation" name="occupation"
                  value="<?= $resident["occupation"] ?>">
              </div>
            </div>
          </div>

          <div class="row g-0">
            <div class="col-sm-3">
              <div class="form-group">
                <label>Are you a 4Ps Beneficiary?</label>

                <div class="form-check">
                  <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn <?= $resident["is_4ps"] == 1 ? "active" : "" ?>">
                      <input type="radio" name="is_4ps" class="hidden"
                        <?= $resident["is_4ps"] == 1 ? "checked" : null ?> value="1"> Yes
                    </label>
                    <label class="btn <?= $resident["is_4ps"] == 0 ? "active" : "" ?>">
                      <input type="radio" name="is_4ps" class="hidden" value="0"
                        <?= $resident["is_4ps"] == 0 ? "checked" : null ?>> No
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-3">
              <div class="form-group">
                <label>Are you a PWD?</label>

                <div class="form-check">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn <?= $resident["is_pwd"] == 1 ? "active" : "" ?>">
                      <input type="radio" name="is_pwd" class="hidden"
                        <?= $resident["is_pwd"] == 1 ? "checked" : null ?> value="1"> Yes
                    </label>
                    <label class="btn <?= $resident["is_pwd"] == 0 ? "active" : "" ?>">
                      <input type="radio" name="is_pwd" class="hidden" value="0"
                        <?= $resident["is_pwd"] == 0 ? "checked" : null ?>> No
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-3">
                <div class="form-group">
                  <label>Solo Parent?</label>

                  <div class="form-check">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn <?= $resident["is_solo_parent"] == 1 ? "active" : "" ?>">
                        <input type="radio" name="is_solo_parent" class="hidden"
                          <?= $resident["is_solo_parent"] == 1 ? "checked" : null ?> value="1"> Yes
                      </label>
                      <label class="btn <?= $resident["is_solo_parent"] == 0 ? "active" : "" ?>">
                        <input type="radio" name="is_solo_parent" class="hidden" value="0"
                          <?= $resident["is_solo_parent"] == 0 ? "checked" : null ?>> No
                      </label>
                    </div>
                  </div>
                </div>
              </div>

            <div class="col-sm-3">
              <div class="form-group">
                <label>Is senior?</label>

                <div class="form-check">
                <input class="form-control" readonly placeholder="Is senior" name="is_senior"
                  value="<?= $resident["is_senior"] ? "Yes" : "No" ?>">
                </div>
              </div>
            </div>
          </div>
          
           <div class="row g-0">
             <div class="col-sm-6">
                <div class="form-group">
                  <label>Head of the Family?</label>

                  <div class="form-check">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn <?= $resident["is_head_of_family"] == 1 ? "active" : "" ?>">
                        <input type="radio" name="is_head_of_family" class="hidden"
                          <?= $resident["is_head_of_family"] == 1 ? "checked" : null ?> value="1"> Yes
                      </label>
                      <label class="btn <?= $resident["is_head_of_family"] == 0 ? "active" : "" ?>">
                        <input type="radio" name="is_head_of_family" class="hidden" value="0"
                          <?= $resident["is_head_of_family"] == 0 ? "checked" : null ?>> No
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-sm-6">
                <div class="form-group">
                  <label>Resident Type</label>

                  <div class="form-check">
                    <div class="btn-group" data-toggle="buttons">
                      <label class="btn <?= $resident["resident_type"] == 1 ? "active" : "" ?>">
                        <input type="radio" name="resident_type" class="hidden"
                          <?= $resident["resident_type"] == 1 ? "checked" : null ?> value="1"> Resident
                      </label>
                      <label class="btn <?= $resident["resident_type"] == 2 ? "active" : "" ?>">
                        <input type="radio" name="resident_type" class="hidden" value="2"
                          <?= $resident["resident_type"] == 2 ? "checked" : null ?>> Tenant
                      </label>
                    </div>
                  </div>
                </div>
              </div>
          </div>

          </div>
        </div>
      </div>

      <div class="form-action mb-3 d-flex justify-content-end gap-3">
        <input type="hidden" name="update-resident" value="1">

        <input type="hidden" name="resident_id" value="<?= $resident["id"] ?>">

        <?php if (isAdmin()): ?>
          <a type="submit" href="resident.php" class="btn btn-dark btn-block text-white fw-bold">
            Back
          </a>
        <?php endif; ?>

        <button type="submit" class="btn btn-success btn-block text-white fw-bold">
          Update
        </button>
      </div>
    </form>
  </div>
</div>