<?php
require "bootstrap/index.php";

$template_id = $_GET["template_id"] ?? null;
$resident_id = $_GET["resident_id"] ?? null;
$purpose = $_GET["purpose"] ?? "";

if (!$template_id) die("No template selected.");

$template = $db->from("certificate_templates")->where("id", $template_id)->first()->exec();
if (!$template) die("Template not found.");

$design = json_decode($template["content"], true) ?: [];

// Fetch all templates for switcher
$all_templates = $db->from("certificate_templates")
    ->where("certificate_id", $_GET['cert_id'])
    ->select(["id" => "id", "name" => "name", "is_default" => "is_default"])
    ->exec();

$resident = null;
if ($resident_id) {
    if ($_GET["cert_id"] == 5) {
        $resident = $db->from("tblpermit")->where("id", $resident_id)->first()->exec();
        $resident['fullname'] = $resident['name'];
        $resident['address'] = $resident['address'] ?? "";
    } else {
        $resident = $db->from("residents")->where("id", $resident_id)->first()->exec();
        $resident['fullname'] = trim(($resident['firstname'] ?? '') . ' ' . ($resident['middlename'] ?? '') . ' ' . ($resident['lastname'] ?? ''));
    }
}

// Fetch Officials for Placeholders
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
    $pos = strtolower($o['position']);
    if (strpos($pos, 'captain') !== false || strpos($pos, 'punong') !== false) {
        $captain = $o;
    } elseif (strpos($pos, 'kagawad') !== false) {
        if (count($kagawads) < 7) {
            $kagawads[] = $o;
        }
    } else {
        $other_officials[] = $o;
    }
}

// Combine for display
$others = array_merge($kagawads, $other_officials);

// Helpers to find specific officials (needed for images too)
$findOfficial = function($keyword) use ($other_officials) {
    foreach($other_officials as $o) {
        if(strpos(strtolower($o['position']), strtolower($keyword)) !== false) return $o;
    }
    return null;
};

$sk = $findOfficial('SK');
$sec = $findOfficial('Secretary');
$treas = $findOfficial('Treasurer');

// Build Image Map
$officialImagesMap = [];
$officialImagesMap['{{captain_image}}'] = 'assets/uploads/' . ($captain['image'] ?? 'person.png');
$officialImagesMap['{{sk_chairman_image}}'] = 'assets/uploads/' . ($sk['image'] ?? 'person.png');
$officialImagesMap['{{secretary_image}}'] = 'assets/uploads/' . ($sec['image'] ?? 'person.png');
$officialImagesMap['{{treasurer_image}}'] = 'assets/uploads/' . ($treas['image'] ?? 'person.png');

for($i=0; $i<7; $i++){
    $num = $i + 1;
    $officialImagesMap["{{kagawad_{$num}_image}}"] = 'assets/uploads/' . ($kagawads[$i]['image'] ?? 'person.png');
}

// Build Officials HTML Blocks
$officials_names_html = '<div style="display:flex; flex-direction:column; gap:8px; text-align:center;">';
foreach ($others as $o) {
    $officials_names_html .= '<div style="line-height:1.2; margin-bottom:5px;">
        <div style="font-weight:bold; font-size:11pt;">' . strip_tags($o['name']) . '</div>
        <div style="font-size:9pt; font-style:italic;">' . $o['position'] . '</div>
    </div>';
}
$officials_names_html .= '</div>';

$officials_grid_html = '<div style="display:flex; flex-wrap:wrap; justify-content:center; gap:15px; width:100%;">';
foreach ($others as $o) {
    $imgSrc = 'assets/uploads/' . ($o['image'] ?: 'person.png');
    $officials_grid_html .= '
    <div style="width:80px; text-align:center; font-size:9px; display:flex; flex-direction:column; align-items:center;">
        <img src="' . $imgSrc . '" style="width:60px; height:60px; object-fit:cover; border-radius:50%; border:1px solid #999; display:block; margin-bottom:4px;">
        <div style="font-weight:bold; line-height:1.1; font-size:8pt;">' . strip_tags($o['name']) . '</div>
        <div style="font-style:italic; line-height:1.1; font-size:7pt;">' . $o['position'] . '</div>
    </div>';
}
$officials_grid_html .= '</div>';

$brgy_info = $db->from("tblbrgy_info")->where("id", 1)->first()->exec();

function replace_placeholders($content, $resident, $brgy, $purpose, $captain, $officials_names_html, $officials_grid_html, $kagawads, $other_officials) {
    // Helpers to find specific officials
    $findOfficial = function($keyword) use ($other_officials) {
        foreach($other_officials as $o) {
            if(strpos(strtolower($o['position']), strtolower($keyword)) !== false) return $o;
        }
        return null;
    };

    $sk = $findOfficial('SK');
    $sec = $findOfficial('Secretary');
    $treas = $findOfficial('Treasurer');

    $replaces = [
        "{{fullname}}" => $resident['fullname'] ?? "____________________",
        "{{firstname}}" => $resident['firstname'] ?? "________",
        "{{middlename}}" => $resident['middlename'] ?? "",
        "{{lastname}}" => $resident['lastname'] ?? "________",
        "{{age}}" => $resident['age'] ?? "____",
        "{{birthdate}}" => !empty($resident['birthdate']) ? date('F d, Y', strtotime($resident['birthdate'])) : "____________________",
        "{{address}}" => $resident['address'] ?? "____________________",
        "{{precinct}}" => $resident['voter_precinct_number'] ?? "____________",
        "{{today_day}}" => date('jS'),
        "{{today_month}}" => date('F'),
        "{{today_year}}" => date('Y'),
        "{{valid_until}}" => strtoupper(date('F d, Y', strtotime('+1 year'))),
        "{{purpose}}" => strtoupper($purpose),
        "{{brgy_name}}" => strtoupper($brgy['brgy_name'] ?? "Barangay 116"),
        "{{city_name}}" => strtoupper($brgy['town'] ?? "City of Manila"),
        "{{captain_name}}" => $captain ? strip_tags($captain['name']) : "EDUARDO M. SOLIS",
        "{{contact_number}}" => $brgy['number'] ?? "",
        "{{brgy_address}}" => nl2br($brgy['address'] ?? ""),
        "{{officials_names_list}}" => $officials_names_html,
        "{{officials_images_grid}}" => $officials_grid_html,
        
        // Individual Official Placeholders
        "{{sk_chairman_name}}" => $sk ? strip_tags($sk['name']) : "",
        "{{secretary_name}}" => $sec ? strip_tags($sec['name']) : "",
        "{{treasurer_name}}" => $treas ? strip_tags($treas['name']) : "",
    ];

    // Add Kagawads 1-7
    for($i=0; $i<7; $i++){
        $num = $i + 1;
        $replaces["{{kagawad_{$num}_name}}"] = isset($kagawads[$i]) ? strip_tags($kagawads[$i]['name']) : "";
        $replaces["{{kagawad_{$num}_pos}}"] = isset($kagawads[$i]) ? $kagawads[$i]['position'] : "";
    }

    return str_replace(array_keys($replaces), array_values($replaces), $content);
}
?>
</head>
<head>
    <?php include "templates/header.php"; ?>
    <title>Print - <?= $template['name'] ?></title>
    <style>
        .print-canvas {
            background: white;
            width: <?= isset($template['paper_size']) && $template['paper_size'] == 'Letter' ? '215.9mm' : '210mm' ?>;
            height: <?= isset($template['paper_size']) && $template['paper_size'] == 'Letter' ? '279.4mm' : '297mm' ?>;
            position: relative;
            margin: 10mm auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
            box-sizing: border-box;
        }
        .design-element {
            position: absolute;
            line-height: normal;
        }
        .design-element img {
            width: 100%;
            height: 100%;
        }
        @media print {
        @media print {
            .no-print, .sidebar, .main-header, .main-panel > .content > .page-inner > .card > .card-header { display: none !important; }
            body { background: white !important; margin: 0 !important; }
            .wrapper { display: block !important; }
            .main-panel { margin: 0 !important; width: 100% !important; padding: 0 !important; }
            .content { margin: 0 !important; padding: 0 !important; }
            .print-canvas { margin: 0 !important; box-shadow: none !important; border: none !important; position: absolute; top: 0; left: 0; }
        }
        }
        @page {
            size: <?= isset($template['paper_size']) && $template['paper_size'] == 'Letter' ? 'Letter' : 'A4' ?>;
            margin: 0;
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
                        <div class="card-header no-print">
                            <div class="card-head-row">
                                <div class="card-title">Preview: <?= $template['name'] ?></div>
                                <div class="card-tools d-flex align-items-center">
                                    <div class="form-group mb-0 mr-3 d-flex align-items-center">
                                        <label class="mr-2 mb-0">Template:</label>
                                        <select id="templateSwitcher" class="form-control form-control-sm" style="width: 200px;">
                                            <option value="standard">Standard Design</option>
                                            <?php foreach ($all_templates as $t): ?>
                                                <option value="<?= $t['id'] ?>" <?= $t['id'] == $template_id ? 'selected' : '' ?>>
                                                    <?= ucwords($t['name']) ?> <?= $t['is_default'] ? '(Active)' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php if ($_GET['cert_id'] != 5): ?>
                                        <div class="form-group mb-0 mr-3 d-flex align-items-center">
                                            <label class="mr-2 mb-0">Purpose:</label>
                                            <select id="purposeSelect" class="form-control form-control-sm mr-2" style="width: 200px;">
                                                <option value="">-- Select Purpose --</option>
                                                <?php if ($_GET['cert_id'] == 1): // Barangay ?>
                                                    <option value="EMPLOYMENT">Employment</option>
                                                    <option value="SCHOLARSHIP">Scholarship</option>
                                                    <option value="BANK TRANSACTION">Bank Transaction</option>
                                                    <option value="BUSINESS PERMIT">Business Permit</option>
                                                    <option value="LEGAL PURPOSES">Legal Purposes</option>
                                                    <option value="IDENTIFICATION">Identification</option>
                                                    <option value="TRAVEL">Travel</option>
                                                    <option value="GOVERNMENT TRANSACTION">Government Transaction</option>
                                                <?php elseif ($_GET['cert_id'] == 4): // Indigency ?>
                                                    <option value="PHILHEALTH RENEWAL">PhilHealth Renewal</option>
                                                    <option value="MEDICAL ASSISTANCE">Medical Assistance</option>
                                                    <option value="BURIAL ASSISTANCE">Burial Assistance</option>
                                                    <option value="FINANCIAL ASSISTANCE">Financial Assistance</option>
                                                    <option value="SCHOLARSHIP">Scholarship</option>
                                                    <option value="DSWD ASSISTANCE">DSWD Assistance</option>
                                                    <option value="HOSPITAL BILL">Hospital Bill</option>
                                                    <option value="SENIOR CITIZEN ID">Senior Citizen ID</option>
                                                <?php endif; ?>
                                            </select>
                                            <input type="text" id="purposeInput" class="form-control form-control-sm" placeholder="Or type here..." style="width: 150px;">
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-tools d-flex align-items-center">
                                        <button class="btn btn-success btn-border btn-round btn-sm mr-2" onclick="saveAsNewTemplate()">
                                            <i class="fa fa-save"></i> Save as Template
                                        </button>
                                        <a href="designer.php?id=<?= $template_id ?>&resident_id=<?= $resident_id ?>&cert_id=<?= $_GET['cert_id'] ?>" class="btn btn-warning btn-border btn-round btn-sm mr-2 no-print">
                                            <i class="fa fa-edit"></i> Edit Design
                                        </a>
                                        <button class="btn btn-info btn-border btn-round btn-sm mr-2 no-print" onclick="handlePrint()">
                                            <i class="fa fa-print"></i> Print
                                        </button>
                                        <button class="btn btn-danger btn-border btn-round btn-sm no-print" onclick="window.history.back()">
                                            <i class="fa fa-times"></i> Close
                                        </button>
                                    </div>
                            </div>
                        </div>
                        <div class="card-body p-0" style="background: #f4f4f4; overflow-x: auto;">
                            <div class="print-canvas">
                                <?php foreach ($design as $el): ?>
                                    <div class="design-element" 
                                         data-type="<?= $el['type'] ?>"
                                         <?= $el['type'] === 'text' ? 'data-original-content="'.htmlspecialchars($el['content']).'"' : '' ?>
                                         style="left: <?= $el['x'] ?>px; top: <?= $el['y'] ?>px; width: <?= $el['width'] ?>px; <?= isset($el['height']) ? 'height: '.$el['height'].'px;' : '' ?> <?= $el['style'] ?>">
                                        <?php if ($el['type'] === 'text'): ?>
                                            <?= replace_placeholders($el['content'], $resident, $brgy_info, $purpose, $captain, $officials_names_html, $officials_grid_html, $kagawads, $other_officials) ?>
                                        <?php elseif($el['type'] === 'image'): ?>
                                            <?php $imgSrc = $officialImagesMap[$el['src']] ?? $el['src']; ?>
                                            <img src="<?= $imgSrc ?>" style="width:100%; height:100%; object-fit: cover;">
                                        <?php elseif($el['type'] === 'box'): ?>
                                            <div style="width:100%; height:100%; <?= !strpos($el['style'], 'border') ? 'border:1px solid black;' : '' ?>"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
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
        const resident = <?= json_encode($resident) ?>;
        const brgy = <?= json_encode($brgy_info) ?>;
        
        function updatePlaceholders() {
            const purposeInput = document.getElementById('purposeInput');
            const purposeSelect = document.getElementById('purposeSelect');
            const purpose = ((purposeInput ? purposeInput.value : "") || (purposeSelect ? purposeSelect.value : "") || "____________________").toUpperCase();
            
            document.querySelectorAll('.design-element[data-type="text"]').forEach(el => {
                let content = el.getAttribute('data-original-content');
                
                const replaces = {
                    "{{fullname}}": resident.fullname || "____________________",
                    "{{firstname}}": resident.firstname || "________",
                    "{{middlename}}": resident.middlename || "",
                    "{{lastname}}": resident.lastname || "________",
                    "{{age}}": resident.age || "____",
                    "{{birthdate}}": resident.birthdate ? formatDate(resident.birthdate) : "____________________",
                    "{{address}}": resident.address || "____________________",
                    "{{precinct}}": resident.voter_precinct_number || "____________",
                    "{{today_day}}": "<?= date('jS') ?>",
                    "{{today_month}}": "<?= date('F') ?>",
                    "{{today_year}}": "<?= date('Y') ?>",
                    "{{valid_until}}": "<?= strtoupper(date('F d, Y', strtotime('+1 month'))) ?>",
                    "{{purpose}}": purpose,
                    "{{brgy_name}}": (brgy.brgy_name || "Barangay 116").toUpperCase(),
                    "{{city_name}}": (brgy.town || "City of Manila").toUpperCase(),
                    "{{captain_name}}": <?= json_encode($captain ? strip_tags($captain['name']) : "EDUARDO M. SOLIS") ?>,
                    "{{officials_names_list}}": <?= json_encode($officials_names_html) ?>,
                    "{{officials_images_grid}}": <?= json_encode($officials_grid_html) ?>,
                };

                for (const [key, val] of Object.entries(replaces)) {
                    content = content.split(key).join(val);
                }
                el.innerHTML = content;
            });
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }

        if (document.getElementById('purposeSelect')) {
            document.getElementById('purposeSelect').addEventListener('change', (e) => {
                if(e.target.value) document.getElementById('purposeInput').value = '';
                updatePlaceholders();
            });
            document.getElementById('purposeInput').addEventListener('input', (e) => {
                if(e.target.value) document.getElementById('purposeSelect').value = '';
                updatePlaceholders();
            });
        }

        function saveAsNewTemplate() {
            swal({
                title: "Save as New Template?",
                text: "Enter a name for this custom design:",
                content: "input",
                buttons: true,
            }).then((name) => {
                if (!name) return;
                
                fetch('model/templates.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=save_as_new&name=${encodeURIComponent(name)}&certificate_id=<?= $_GET['cert_id'] ?>&design=${encodeURIComponent(JSON.stringify(design))}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        swal("Saved!", "This edited certificate is now saved as a reusable design!", "success");
                    }
                });
            });
        }

        function handlePrint() {
            const resident_id = "<?= $resident_id ?>";
            const cert_id = "<?= $_GET['cert_id'] ?>";
            
            if (resident_id && cert_id) {
                fetch('model/track_print.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `resident_id=${resident_id}&cert_id=${cert_id}`
                });
            }

            // Nuclear option: Replace body with certificate only
            const cert = document.querySelector('.print-canvas').outerHTML;
            document.body.innerHTML = cert;
            
            window.print();
            location.reload();
        }

        // Template Switcher Logic
        document.getElementById('templateSwitcher').addEventListener('change', function() {
            const val = this.value;
            const certId = "<?= $_GET['cert_id'] ?>";
            const residentId = "<?= $resident_id ?>";
            
            if (val === 'standard') {
                $.post('model/templates.php', {
                    action: 'remove_default',
                    certificate_id: certId
                }, function(res) {
                    let page = 'generate_brgy_cert.php';
                    if (certId == '4') page = 'generate_indi_cert.php';
                    if (certId == '5') page = 'generate_business_permit.php';
                    window.location.href = page + '?id=' + residentId + '&mode=standard';
                });
            } else {
                $.post('model/templates.php', {
                    action: 'set_default',
                    id: val,
                    certificate_id: certId
                }, function(res) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('template_id', val);
                    window.location.href = url.toString();
                });
            }
        });
    </script>
</body>
</html>
