<?php
require "bootstrap/index.php";

$resident = (function () use ($db) {
    if (!isset($_GET["id"]))
        return ['firstname' => '________', 'middlename' => '', 'lastname' => '', 'birthdate' => date('Y-m-d'), 'address' => '________', 'voter_precinct_number' => '____________'];
    $r = $db->from("residents")->where("id", $_GET["id"])->first()->exec();
    return $r ? $r : ['firstname' => '________', 'middlename' => '', 'lastname' => '', 'birthdate' => date('Y-m-d'), 'address' => '________', 'voter_precinct_number' => '____________'];
})();

// Check if a Main Visual Template is active
if (!isset($_GET['mode']) || $_GET['mode'] != 'standard') {
    $main_template = $db->from("certificate_templates")
        ->where("certificate_id", 1) // 1 = Barangay Certificate
        ->where("is_default", 1)
        ->first()
        ->exec();

    if ($main_template) {
        $query_str = $_SERVER['QUERY_STRING'];
        header("Location: generate_visual.php?template_id=" . $main_template['id'] . "&cert_id=1&resident_id=" . ($_GET['id'] ?? '') . "&" . $query_str);
        exit;
    }
}


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

// Fetch all templates for switcher (Barangay Cert = 1)
$all_templates = $db->from("certificate_templates")
    ->where("certificate_id", 1)
    ->select(["id" => "id", "name" => "name", "is_default" => "is_default"])
    ->exec();
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php include "templates/header.php"; ?>
        <title>Barangay Certification - Barangay Services Management System</title>
        <link rel="stylesheet" href="assets/css/certificate-print.css">
        <style>
            /* Specific tweaks for this document to match EXACTLY */
            .cert-container {
                border: 1px solid black !important;
                padding: 2mm !important;
                height: 290mm !important;
            }

            .cert-main-box {
                flex-grow: 0 !important;
                padding: 9mm !important;
                margin-top: 2mm !important;
                margin-bottom: 2mm !important;
            }

            .outer-border {
                border: 4px double black;
                height: 100%;
                padding: 5mm 5mm 8mm 5mm;
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
            }

            .cert-header-center h4 {
                font-weight: normal !important;
            }

            .cert-header-center h3 {
                font-weight: normal !important;
            }

            .office-title {
                font-weight: bold !important;
                border-bottom: none !important;
            }

            .brgy-title {
                font-weight: normal !important;
                margin-top: 1mm;
            }

            .top-boxes {
                display: flex;
                justify-content: space-between;
                margin-bottom: mm;
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

            /* PREVIEW SIZES */
            .sheet-a4 {
                width: 210mm !important;
                height: 297mm !important;
            }

            .sheet-letter {
                width: 215.9mm !important;
                height: 279.4mm !important;
            }

            @media print {
                .sheet-a4 {
                    width: 210mm !important;
                    height: 297mm !important;
                    max-width: 210mm !important;
                }

                .sheet-letter {
                    width: 215.9mm !important;
                    height: 279.4mm !important;
                    max-width: 215.9mm !important;
                }
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
                                    <div class="card-title">Barangay Certificate 2026</div>
                                    <div class="card-tools d-flex align-items-center gap-2">
                                        <div class="form-group mb-0 mr-2 d-flex align-items-center gap-1">
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
                                                <option value="EMPLOYMENT">Employment</option>
                                                <option value="SCHOLARSHIP">Scholarship</option>
                                                <option value="BANK TRANSACTION">Bank Transaction</option>
                                                <option value="BUSINESS PERMIT">Business Permit</option>
                                                <option value="LEGAL PURPOSES">Legal Purposes</option>
                                                <option value="IDENTIFICATION">Identification</option>
                                                <option value="TRAVEL">Travel</option>
                                                <option value="GOVERNMENT TRANSACTION">Government Transaction</option>
                                            </select>
                                            <span class="mx-1">or</span>
                                            <input type="text" id="purposeInput" class="form-control form-control-sm"
                                                placeholder="Type new purpose" style="width: 150px;">
                                            <button class="btn btn-success btn-sm" onclick="savePurpose()"
                                                title="Save to list">
                                                <i class="fa fa-save"></i>
                                            </button>
                                        </div>
                                        <div class="form-group mb-0 mr-2 d-flex align-items-center gap-1">
                                            <select id="paperSizeSelect" class="form-control form-control-sm" style="width: 120px;" title="Select Paper Size">
                                                <option value="a4" selected>A4 (210x297)</option>
                                                <option value="letter">Letter (8.5x11)</option>
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
                                <div class="cert-container sheet-a4" id="printThis">
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
                                                <h1>BARANGAY CERTIFICATION</h1>
                                            </div>
                                            <div class="cert-body">
                                                <?php
                                                $fullname = trim(($resident['firstname'] ?? '') . ' ' . ($resident['middlename'] ?? '') . ' ' . ($resident['lastname'] ?? ''));
                                                $age = floor((time() - strtotime($resident['birthdate'] ?? date('Y-m-d'))) / 31556926);
                                                $birthdate_formatted = date('F d, Y', strtotime($resident['birthdate'] ?? date('Y-m-d')));
                                                $address = $resident['address'] ?? '';
                                                $today = date('jS');
                                                $month = date('F');
                                                $year = date('Y');
                                                ?>
                                                <p>This is to certify that <b><?= $fullname ?></b>, age
                                                    <b><?= $age ?></b>, born on <b><?= $birthdate_formatted ?></b>.
                                                    He/she is a <b>RESIDENT</b> at
                                                    <b><?= !empty($address) ? $address : '<span class="fill-blank">________</span>' ?></b>,
                                                    within the jurisdiction of the barangay as of this
                                                </p>
                                                <p style="text-indent: 0;">date.</p>

                                                <p>This certification is being issued upon the request of the
                                                    above-mentioned person for <b><span
                                                            id="purposeDisplay">____________________</span></b>
                                                    requirement purposes.</p>
                                                <p style="text-indent: 0; text-align: center; margin-top: 10mm;">This
                                                    CERTIFICATION is issued in the City of Manila on the
                                                    <b><?= $today ?> DAY</b> of <b><?= $month ?> <?= $year ?></b>.
                                                </p>
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
                                                <?php
                                                // Replicating the EXACT order from PDF
                                                $official_rows = array_chunk($others, 4);
                                                foreach ($official_rows as $row):
                                                    ?>
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
                const saved = JSON.parse(localStorage.getItem('savedPurposes') || '[]');
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

            // Handle Paper Size Class Toggling
            const paperSelect = document.getElementById('paperSizeSelect');
            const printContainer = document.getElementById('printThis');

            paperSelect.addEventListener('change', function() {
                const size = this.value;
                // Remove existing size classes
                printContainer.classList.remove('sheet-a4', 'sheet-letter');
                
                // Add new size class
                if (size === 'letter') {
                    printContainer.classList.add('sheet-letter');
                } else {
                    printContainer.classList.add('sheet-a4');
                }
            });

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
                const saved = JSON.parse(localStorage.getItem('savedPurposes') || '[]');
                saved.push(purpose);
                localStorage.setItem('savedPurposes', JSON.stringify(saved));

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
                        body: `resident_id=${residentId}&cert_id=1`
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
                    // Scale X: 215.9 / 210 = ~1.028
                    // Scale Y: 279.4 / 296 = ~0.944 (We use 0.945 to be safe/tight).
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

                // Replace body content
                document.body.innerHTML = cert;

                // Print
                setTimeout(() => {
                    window.print();
                    location.reload();
                }, 500);
            }

            // Template Switcher Logic
            document.getElementById('templateSwitcher').addEventListener('change', function () {
                const val = this.value;
                const certId = 1;
                const residentId = "<?= $resident['id'] ?? '' ?>";

                if (val === 'standard') {
                    // Already here, maybe just reload or nothing
                    location.reload();
                } else {
                    $.post('model/templates.php', {
                        action: 'set_default',
                        id: val,
                        certificate_id: certId
                    }, function (res) {
                        window.location.href = 'generate_visual.php?template_id=' + val + '&cert_id=1&resident_id=' + residentId;
                    });
                }
            });
        </script>


    </body>

</html>
