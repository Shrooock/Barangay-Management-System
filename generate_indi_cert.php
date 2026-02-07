<?php
require "bootstrap/index.php";

$resident_id = $_GET['id'] ?? null;

// Check for MAIN visual template
$main_template = $db->from("certificate_templates")
    ->where("certificate_id", 4)
    ->where("is_default", 1)
    ->first()
    ->exec();

if ($main_template && !isset($_GET['mode'])) {
    header("Location: generate_visual.php?cert_id=4&template_id=" . $main_template['id'] . "&resident_id=" . $resident_id . "&purpose=" . urlencode($_GET['purpose'] ?? ''));
    exit;
}

// Fetch all templates for switcher
$all_templates = $db->from("certificate_templates")
    ->where("certificate_id", 4)
    ->select(["id" => "id", "name" => "name", "is_default" => "is_default"])
    ->exec();

$resident = (function () use ($db) {
    if (!isset($_GET["id"]))
        return [
            'firstname' => '________',
            'middlename' => '',
            'lastname' => '',
            'birthdate' => date('Y-m-d'),
            'address' =>
                '________',
            'voter_precinct_number' => '____________'
        ];
    $r = $db->from("residents")->where("id", $_GET["id"])->first()->exec();
    return $r ? $r : [
        'firstname' => '________',
        'middlename' => '',
        'lastname' => '',
        'birthdate' => date('Y-m-d'),
        'address' => '________',
        'voter_precinct_number' => '____________'
    ];
})();

$brgy_info = $db->from("tblbrgy_info")->where("id", 1)->first()->exec();
$city_logo = $brgy_info['city_logo'];
$brgy_logo = $brgy_info['brgy_logo'];

$all_officials = $db->from(["tblofficials" => "officials"])
    ->join(["tblposition" => "positions"], "positions.id", "officials.position")
    ->whereRaw("officials.status = 'Active'")
    ->orderBy("positions.order", "ASC")
    ->select(["name" => "officials.name", "position" => "positions.position", "image" => "officials.image"])
    ->exec();

$captain = null;
$kagawads = [];
$other_officials = [];

foreach ($all_officials as $o) {
    if (strpos(strtolower($o['position']), 'captain') !== false || strpos(strtolower($o['position']), 'punong') !== false) {
        $captain = $o;
    } elseif (strpos(strtolower($o['position']), 'kagawad') !== false) {
        if (count($kagawads) < 7) {
            $kagawads[] = $o;
        }
    } else {
        $other_officials[] = $o;
    }
}
$others = array_merge($kagawads, $other_officials);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php include "templates/header.php"; ?>
        <title>Barangay Cert of Indigency - Barangay Services Management System</title>
        <link rel="stylesheet" href="assets/css/certificate-print.css">
        <style>
            .cert-container {
                border: 1px solid black !important;
                padding: 2mm !important;
                height: 290mm !important;
            }

            .cert-main-box {
                flex-grow: 0 !important;
                padding: 1mm !important;
                margin-top: 3mm !important;
                margin-bottom: 3mm !important;
            }

            .outer-border {
                border: 4px double black;
                height: 100%;
                padding: 2mm 5mm 8mm 5mm;
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
            }

            .top-boxes {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.1mm;
            }

            .voter-box,
            .valid-box {
                border: 1px solid black;
                padding: 1mm 3mm;
                font-size: 10pt;
                font-weight: bold;
            }

            /* Fallback to ensure NO underlines on names and roles are bigger */
            .captain-name {
                margin: 0 !important;
                font-size: 12pt;
                font-weight: bold;
                text-transform: uppercase;
                text-decoration: none !important;
                line-height: 1.1;
            }

            .captain-title {
                font-size: 11pt !important;
                margin-top: -1mm !important;
                line-height: 1.1;
            }

            .official-card .name {
                font-size: 7pt !important;
                font-weight: bold !important;
                text-decoration: none !important;
            }

            .official-card .pos {
                font-size: 7pt !important;
                font-style: normal !important;
                font-weight: bold !important;
            }

            .captain-section,
            .captain-section p,
            .captain-section span,
            .captain-section .label,
            .captain-section .value {
                text-align: center !important;
                display: block !important;
                margin: 0 !important;
            }

            .captain-photo {
                width: 30mm !important;
                height: 30mm !important;
                margin: 5mm auto 4mm auto !important;
                display: block !important;
            }

            .officials-section-boxed {
                margin-top: auto !important;
            }

            .cert-header-center h4 {
                font-weight: normal !important;
                text-transform: uppercase;
            }

            .cert-header-center h3 {
                font-weight: normal !important;
            }

            .office-title {
                font-weight: bold !important;
                border-bottom: none !important;
                margin-top: 1mm;
            }

            .header-brgy-line {
                font-weight: normal !important;
                margin-top: 1mm;
                font-size: 11pt;
            }

        </style>
    </head>

    <body>
        <?php include "templates/loading_screen.php"; ?>
        <div class="wrapper">
            <?php include "templates/main-header.php"; ?>
            <?php include "templates/sidebar.php"; ?>
            <div class="main-panel">
                <div class="content">
                    <div class="page-inner">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-head-row">
                                    <div class="card-title">Indigency Certificate</div>
                                    <div class="card-tools d-flex align-items-center gap-2">
                                        <div class="form-group mb-0 mr-3 d-flex align-items-center">
                                            <label class="mr-2 mb-0">Template:</label>
                                            <select id="templateSwitcher" class="form-control form-control-sm"
                                                style="width: 200px;">
                                                <option value="standard" selected>Standard Design (Active)</option>
                                                <?php foreach ($all_templates as $t): ?>
                                                    <option value="<?= $t['id'] ?>">
                                                        <?= ucwords($t['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0 mr-2 d-flex align-items-center gap-1">
                                            <select id="purposeSelect" class="form-control form-control-sm"
                                                style="width: 200px;">
                                                <option value="">-- Select Purpose --</option>
                                                <option value="PHILHEALTH RENEWAL">PhilHealth Renewal</option>
                                                <option value="MEDICAL ASSISTANCE">Medical Assistance</option>
                                                <option value="BURIAL ASSISTANCE">Burial Assistance</option>
                                                <option value="FINANCIAL ASSISTANCE">Financial Assistance</option>
                                                <option value="SCHOLARSHIP">Scholarship</option>
                                                <option value="DSWD ASSISTANCE">DSWD Assistance</option>
                                                <option value="HOSPITAL BILL">Hospital Bill</option>
                                                <option value="SENIOR CITIZEN ID">Senior Citizen ID</option>
                                            </select>
                                            <span class="mx-1">or</span>
                                            <input type="text" id="purposeInput" class="form-control form-control-sm"
                                                placeholder="Type new purpose" style="width: 150px;">
                                            <button class="btn btn-success btn-sm" onclick="savePurpose()"
                                                title="Save to list">
                                                <i class="fa fa-save"></i>
                                            </button>
                                        </div>
                                        <div class="form-group mb-0 mr-2 d-flex align-items-center">
                                            <select id="paperSizeSelect" class="form-control form-control-sm"
                                                style="width: 100px;">
                                                <option value="a4">A4</option>
                                                <option value="letter">Letter</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-info btn-border btn-round btn-sm"
                                            onclick="printCertificate()">
                                            <i class="fa fa-print"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="cert-container" id="printThis">
                                    <div class="outer-border">
                                        <div class="cert-header">
                                            <img src="assets/uploads/<?= $city_logo ?>" class="cert-header-logo">
                                            <div class="cert-header-center">
                                                <h4>REPUBLIC OF THE PHILIPPINES</h4>
                                                <h3>City of Manila</h3>
                                                <div class="office-title">OFFICE OF THE CHAIRMAN</div>
                                                <h4 class="brgy-title">BARANGAY 116 ZONE 9 DISTRICT I</h4>
                                            </div>
                                            <img src="assets/uploads/<?= $brgy_logo ?>" class="cert-header-logo">
                                        </div>

                                        <?php if (!empty($brgy_info['bg_logo'])): ?>
                                            <img src="assets/uploads/<?= $brgy_info['bg_logo'] ?>" class="cert-watermark">
                                        <?php endif; ?>

                                        <div class="top-boxes">
                                            <div class="voter-box">Voter's Precinct No.:
                                                <?= !empty($resident['voter_precinct_number']) ? $resident['voter_precinct_number'] : '<span class="fill-blank">____________</span>' ?>
                                            </div>
                                            <div class="valid-box">Valid Until:
                                                <?= strtoupper(date('F d, Y', strtotime('+1 year'))) ?>
                                            </div>
                                        </div>

                                        <div class="cert-main-box">
                                            <div class="cert-title">
                                                <h1>BARANGAY CERTIFICATE OF INDIGENCY</h1>
                                            </div>
                                            <div class="cert-body">
                                                <b class="to-whom">TO WHOM IT MAY CONCERN:</b>
                                                <?php
                                                $fullname = trim(($resident['firstname'] ?? '') . ' ' . ($resident['middlename'] ?? '') . ' ' . ($resident['lastname'] ?? ''));
                                                $age = floor((time() - strtotime($resident['birthdate'] ?? date('Y-m-d'))) / 31556926);
                                                $birthdate_formatted = date('F d, Y', strtotime($resident['birthdate'] ?? date('Y-m-d')));
                                                $address = $resident['address'] ?? '';
                                                $today = date('jS');
                                                $month = strtoupper(date('F'));
                                                $year = date('Y');
                                                ?>
                                                <p>This is to certify that <b><?= $fullname ?></b>, age
                                                    <b><?= $age ?></b>, born <b><?= $birthdate_formatted ?></b>. He/she
                                                    is a <b>RESIDENT</b> at
                                                    <b><?= !empty($address) ? $address : '<span class="fill-blank">________</span>' ?></b>,
                                                    which is within the jurisdiction of Barangay 116 Zone 9, District 1
                                                    as of this date.
                                                </p>

                                                <p>This is to further certify that the subject person concerned is known
                                                    belong to the family living below poverty line that can avail
                                                    support from any government or private institutions.</p>
                                                <p>This Certification is being issued upon the request of the
                                                    above-mentioned person for <b><span
                                                            id="purposeDisplay">____________________</span></b>
                                                    requirement purpose.</p>
                                                <p style="text-indent: 0; text-align: center;">Issued in the Barangay
                                                    Hall this <b><?= $today ?> DAY</b> of <b><?= $month ?>
                                                        <?= $year ?></b> in the City of Manila.</p>
                                            </div>

                                            <div class="signature-block">
                                                <div class="sig-name">
                                                    <?= $captain ? strip_tags($captain['name']) : 'EDUARDO M. SOLIS' ?>
                                                </div>
                                                <div class="sig-title">Punong Barangay</div>
                                                <div class="seal-warning">Not Valid Without Official Barangay Seal</div>
                                            </div>
                                        </div>

                                        <div class="officials-section-boxed">
                                            <div class="captain-section"
                                                style="display: flex !important; flex-direction: column !important; align-items: center !important; text-align: center !important;">
                                                <div class="captain-photo">
                                                    <img
                                                        src="assets/uploads/<?= $captain ? $captain['image'] : 'person.png' ?>">
                                                </div>
                                                <p class="captain-name">
                                                    <?= $captain ? strip_tags($captain['name']) : 'EDUARDO M. SOLIS' ?>
                                                </p>
                                                <p class="captain-title">PUNONG BARANGAY</p>

                                                <p class="label">Address:</p>
                                                <p class="value"><?= nl2br($brgy_info['address']) ?>
                                                </p>

                                                <p class="label">Tel. No.: <span
                                                        class="value-inline"><?= $brgy_info['number'] ?></span></p>

                                                <p class="label">E-mail:</p>
                                                <p class="value">barangay116zone9district1@gmail.com</p>

                                                <p class="label">Barangay Secretary Office Hours:</p>
                                                <p class="value">8:00am-12:00nn & 1:00pm-5:00pm<br>&lt; Mondays to
                                                    Saturdays &gt;</p>
                                            </div>
                                            <div class="officials-grid">
                                                <?php $rows = array_chunk($others, 4);
                                                foreach ($rows as $row): ?>
                                                    <div class="officials-row">
                                                        <?php foreach ($row as $o): ?>
                                                            <div class="official-card">
                                                                <img src="assets/uploads/<?= $o['image'] ?>">
                                                                <div class="name"><?= strip_tags($o['name']) ?></div>
                                                                <div class="pos"><?= $o['position'] ?></div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endforeach; ?>
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
            // Load saved purposes from localStorage
            function loadSavedPurposes() {
                const saved = JSON.parse(localStorage.getItem('savedPurposesIndigency') || '[]');
                const select = document.getElementById('purposeSelect');
                saved.forEach(purpose => {
                    if (!Array.from(select.options).some(opt => opt.value === purpose)) {
                        const option = document.createElement('option');
                        option.value = purpose;
                        option.textContent = purpose;
                        select.appendChild(option);
                    }
                });
            }
            loadSavedPurposes();

            // Update purpose display from dropdown
            document.getElementById('purposeSelect').addEventListener('change', function () {
                const purposeDisplay = document.getElementById('purposeDisplay');
                document.getElementById('purposeInput').value = ''; // Clear text input
                if (this.value) {
                    purposeDisplay.textContent = this.value;
                    purposeDisplay.classList.remove('fill-blank');
                } else {
                    purposeDisplay.textContent = '____________________';
                    purposeDisplay.classList.add('fill-blank');
                }
            });

            // Update purpose display from text input
            document.getElementById('purposeInput').addEventListener('input', function () {
                const purposeDisplay = document.getElementById('purposeDisplay');
                document.getElementById('purposeSelect').value = ''; // Clear dropdown
                if (this.value.trim()) {
                    purposeDisplay.textContent = this.value.toUpperCase();
                    purposeDisplay.classList.remove('fill-blank');
                } else {
                    purposeDisplay.textContent = '____________________';
                    purposeDisplay.classList.add('fill-blank');
                }
            });

            // Save new purpose to list
            function savePurpose() {
                const input = document.getElementById('purposeInput');
                const select = document.getElementById('purposeSelect');
                const purpose = input.value.trim().toUpperCase();

                if (!purpose) {
                    alert('Please enter a purpose to save.');
                    return;
                }

                // Check if already exists
                if (Array.from(select.options).some(opt => opt.value === purpose)) {
                    alert('This purpose already exists in the list.');
                    return;
                }

                // Add to dropdown
                const option = document.createElement('option');
                option.value = purpose;
                option.textContent = purpose;
                select.appendChild(option);
                select.value = purpose;

                // Save to localStorage
                const saved = JSON.parse(localStorage.getItem('savedPurposesIndigency') || '[]');
                saved.push(purpose);
                localStorage.setItem('savedPurposesIndigency', JSON.stringify(saved));

                // Update display
                document.getElementById('purposeDisplay').textContent = purpose;
                document.getElementById('purposeDisplay').classList.remove('fill-blank');
                input.value = '';

                alert('Purpose "' + purpose + '" saved to list!');
            }

            function printCertificate() {
                const residentId = "<?= $resident['id'] ?? '' ?>";
                const paperSize = document.getElementById('paperSizeSelect').value;

                if (residentId) {
                    fetch('model/track_print.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `resident_id=${residentId}&cert_id=4`
                    });
                }

                const cert = document.querySelector('.cert-container').outerHTML;

                // CSS Config based on Paper Size
                // RESTORE A4: Strict dimensions, Margin 0
                let sizeRule = '@page { size: A4; margin: 0; }';
                let dimRule = 'width: 210mm; height: 296mm;';
                let scaleRule = '';
                let bodyFlex = 'display: block;';

                if (paperSize === 'letter') {
                    // LETTER FIX: ABSOLUTE FILL (NO MARGINS)
                    // The user wants Full Bleed (Left, Right, Top, Bottom).
                    // 1. Absolute Position (0,0) to ensure no Top/Left whitespace.
                    // 2. Exact Transform Scaling to map A4 dimensions (210x296) to Letter (215.9x279.4).
                    sizeRule = '@page { size: Letter; margin: 0; }';
                    dimRule = 'width: 215.9mm; height: 279.4mm;';
                    scaleRule = 'position: absolute !important; top: 0 !important; left: 0 !important; width: 210mm !important; height: 296mm !important; transform: scale(1.0285, 0.945); transform-origin: 0 0; margin: 0 !important;';
                    bodyFlex = 'display: block;';
                }

                // Create style element for dynamic page size
                const style = document.createElement('style');
                style.id = 'print-dynamic-style';
                style.innerHTML = `
                    ${sizeRule}
                    @media print {
                        html, body {
                            ${dimRule}
                            margin: 0 !important;
                            padding: 0 !important;
                            overflow: hidden !important;
                            background: white !important;
                            ${bodyFlex}
                        }
                        .cert-container {
                            width: 100% !important;
                            height: 100% !important;
                            margin: 0 !important;
                            box-shadow: none !important;
                            border: 4px double black !important;
                            page-break-after: avoid !important;
                            page-break-inside: avoid !important;
                            ${scaleRule} 
                        }
                        /* Ensure no other content interferes */
                        body > *:not(.cert-container) { display: none !important; }
                    }
                `;

                // IMPORTANT: Append style to HEAD for @page rules to work reliably
                document.head.appendChild(style);

                document.body.innerHTML = cert;

                setTimeout(() => {
                    window.print();
                    location.reload();
                }, 500);
            }

            document.getElementById('templateSwitcher').addEventListener('change', async function () {
                const val = this.value;
                const resident_id = "<?= $resident_id ?>";
                const purpose = document.getElementById('purposeSelect').value || document.getElementById('purposeInput').value;

                if (val === 'standard') {
                    // Remove default for this cert type
                    await fetch('model/templates.php?action=remove_default&cert_id=4');
                    location.reload();
                } else {
                    // Set as default and redirect
                    await fetch(`model/templates.php?action=set_default&id=${val}&cert_id=4`);
                    location.replace(`generate_visual.php?cert_id=4&template_id=${val}&resident_id=${resident_id}&purpose=${encodeURIComponent(purpose)}`);
                }
            });
        </script>

    </body>

</html>
