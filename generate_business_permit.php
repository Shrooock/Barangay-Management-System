<?php
require "bootstrap/index.php";

$resident_id = $_GET['id'] ?? null;

// Check for MAIN visual template (Business Permit = 5)
$main_template = $db->from("certificate_templates")
    ->where("certificate_id", 5)
    ->where("is_default", 1)
    ->first()
    ->exec();

if ($main_template && !isset($_GET['mode'])) {
    header("Location: generate_visual.php?cert_id=5&template_id=" . $main_template['id'] . "&resident_id=" . $resident_id);
    exit;
}

// Fetch all templates for switcher
$all_templates = $db->from("certificate_templates")
    ->where("certificate_id", 5)
    ->select(["id" => "id", "name" => "name", "is_default" => "is_default"])
    ->exec();

$permit = (function () use ($db) {
    if (!isset($_GET["id"]))
        return [
            'name' => '________',
            'owner1' => '________',
            'owner2' => '',
            'nature' => '________',
            'tin' => '________',
            'address' => '________'
        ];
    $r = $db->from("tblpermit")->where("id", $_GET["id"])->first()->exec();
    return $r ? $r : [
        'name' => '________',
        'owner1' => '________',
        'owner2' => '',
        'nature' => '________',
        'tin' =>
            '________',
        'address' => '________'
    ];
})();


$brgy_info = $db->from("tblbrgy_info")->where("id", 1)->first()->exec();
$city_logo = $brgy_info['city_logo'];
$brgy_logo = $brgy_info['brgy_logo'];

$captain = $db->from("tblofficials")
    ->join("tblposition", "tblposition.id", "tblofficials.position")
    ->whereRaw("tblposition.position LIKE '%Captain%' OR tblposition.position LIKE '%Punong%'")
    ->where("tblofficials.status", "Active")
    ->select(["name" => "tblofficials.name"])
    ->first()
    ->exec();
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php include "templates/header.php"; ?>
        <title>Barangay Business Clearance - Barangay Services Management System</title>
        <link rel="stylesheet" href="assets/css/certificate-print.css">
        <style>
            .cert-container,
            .cert-main-box,
            .officials-section-boxed {
                border: none !important;
            }

            .cert-container {
                padding: 10mm !important;
                height: 290mm !important;
                font-family: serif !important;
            }

            .cert-header {
                border-bottom: none !important;
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
                font-size: 13pt !important;
            }

            .brgy-title {
                font-weight: normal !important;
                margin-top: 1mm;
                font-size: 11pt !important;
            }

            .valid-until {
                font-weight: bold;
                font-size: 11pt;
                margin-bottom: 5mm;
            }

            .cert-body p {
                text-indent: 0 !important;
                margin-bottom: 5mm;
                line-height: 1.5;
            }

            .cert-title h1 {
                text-decoration: none !important;
                font-size: 20pt !important;
            }

            .cert-title p {
                font-size: 12pt !important;
                margin-top: 0 !important;
            }

            .business-details {
                margin-top: 10mm;
                font-weight: bold;
            }

            .business-details p {
                margin-bottom: 2mm !important;
            }

            .business-footer-note {
                margin-top: 15mm;
                font-weight: bold;
                font-size: 10pt;
            }

            .sig-name,
            .captain-name,
            .official-card .name {
                text-decoration: none !important;
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
                                    <div class="card-title">Business Clearance</div>
                                    <div class="card-tools d-flex align-items-center">
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
                                        <div class="form-group mb-0 mr-3 d-flex align-items-center">
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
                                    <div class="cert-header" style="position: relative; margin-bottom: 8mm;">
                                        <img src="assets/uploads/<?= $city_logo ?>" class="cert-header-logo">
                                        <div class="cert-header-center">
                                            <h4>REPUBLIC OF THE PHILIPPINES</h4>
                                            <h3>City of Manila</h3>
                                            <div class="office-title">OFFICE OF THE CHAIRMAN</div>
                                            <div class="brgy-title">BARANGAY 116 ZONE 9 DISTRICT I</div>
                                        </div>
                                        <img src="assets/uploads/<?= $brgy_logo ?>" class="cert-header-logo">
                                    </div>

                                    <?php if (!empty($brgy_info['bg_logo'])): ?>
                                        <img src="assets/uploads/<?= $brgy_info['bg_logo'] ?>" class="cert-watermark">
                                    <?php endif; ?>

                                    <div class="cert-main-box" style="margin-top: 30mm;">
                                        <div class="cert-title"
                                            style="margin-top: 10mm; margin-bottom: 15mm; text-align: center; position: relative;">
                                            <div
                                                style="text-align: left; font-weight: bold; font-size: 12pt; margin-left: 2mm; margin-bottom: 2mm;">
                                                Valid Until: <?= date('m/d/Y', strtotime('+1 year')) ?></div>
                                            <h1 style="font-weight: bold; color: #444;">BARANGAY CLEARANCE</h1>
                                            <p style="font-weight: bold; margin-top: 2mm; font-size: 14pt;">(TO OPERATE
                                                BUSINESS)</p>
                                        </div>
                                        <div class="cert-body" style="font-size: 12pt;">
                                            <?php
                                            $fullname = trim($permit['owner1']);
                                            if (!empty($permit['owner2'])) {
                                                $fullname .= ' & ' . trim($permit['owner2']);
                                            }
                                            // Logic: If business_address exists, use it. Else if address exists, use it. Else empty.
                                            $display_address = !empty($permit['business_address']) ? $permit['business_address'] : (!empty($permit['address']) ? $permit['address'] : '');
                                            $today = date('jS');
                                            $month = date('F');
                                            $year = date('Y');
                                            ?>
                                            <div class="cert-content" style="text-align: justify; margin-top: 15mm;">
                                                <p style="margin-bottom: 0 !important; line-height: 2.5em !important;">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This
                                                    is to certify that
                                                    <b><?= !empty($fullname) ? $fullname : '__________' ?></b>, with
                                                    business address at
                                                    <b><?= !empty($display_address) ? $display_address : '__________' ?></b>
                                                    is hereby granted clearance to operate business within the
                                                    territorial jurisdiction of <b>BARANGAY 116 ZONE 9</b>, pursuant to
                                                    the provisions of Section 1520, Republic Act 7160, otherwise known
                                                    as the Local Government Code of 1991.
                                                </p>

                                                <p
                                                    style="margin-bottom: 10mm !important; line-height: 2.5em !important;">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This
                                                    Clearance is being issued upon the request of its owner whose
                                                    signature Community Tax/Residence Certificate No.
                                                    <b><?= !empty($permit['cert_number']) ? $permit['cert_number'] : '________________' ?></b>
                                                    appears here in License Purposes.
                                                </p>

                                                <p style="text-align: center;">This Clearance is issued this
                                                    <b><?= $today ?></b> Day of <b><?= $month ?></b> <b><?= $year ?></b>
                                                </p>
                                            </div>

                                            <div class="business-details"
                                                style="margin-top: 10mm; text-align: left; margin-left: 15mm;">
                                                <p><b>Business Name:</b> <?= $permit['name'] ?></p>
                                                <p><b>Type of Business:</b> <?= $permit['nature'] ?></p>
                                                <p><b>TIN:</b>
                                                    <?= !empty($permit['tin']) ? $permit['tin'] : '____________________' ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="signature-block">
                                            <div class="sig-name">
                                                <?= $captain ? $captain['name'] : 'EDUARDO M. SOLIS' ?>
                                            </div>
                                            <div class="sig-title">Punong Barangay</div>
                                        </div>

                                        <div class="business-footer-note">
                                            <p>NOTE:</p>
                                            <p>NO COLLECTION FEE</p>
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
            function printCertificate() {
                const residentId = "<?= $_GET['id'] ?? '' ?>";
                const paperSize = document.getElementById('paperSizeSelect').value;

                if (residentId) {
                    fetch('model/track_print.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `resident_id=${residentId}&cert_id=5`
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

                if (val === 'standard') {
                    // Remove default for this cert type
                    await fetch('model/templates.php?action=remove_default&cert_id=5');
                    location.reload();
                } else {
                    // Set as default and redirect
                    await fetch(`model/templates.php?action=set_default&id=${val}&cert_id=5`);
                    location.replace(`generate_visual.php?cert_id=5&template_id=${val}&resident_id=${resident_id}`);
                }
            });
        </script>
    </body>

</html>
