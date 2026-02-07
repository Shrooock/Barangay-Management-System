<?php
require_once "bootstrap/index.php";

if (!isAdmin()) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET["id"];
$template = $db->from("certificate_templates")->where("id", $id)->first()->exec();

if (!$template) {
    header("Location: certificate_templates.php");
    exit;
}

// Fetch Officials for Dynamic Images
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
// Note: We use relative paths for the JS preview.
$officialImagesMap = [];
$officialImagesMap['{{captain_image}}'] = 'assets/uploads/' . ($captain['image'] ?? 'person.png');
$officialImagesMap['{{sk_chairman_image}}'] = 'assets/uploads/' . ($sk['image'] ?? 'person.png');
$officialImagesMap['{{secretary_image}}'] = 'assets/uploads/' . ($sec['image'] ?? 'person.png');
$officialImagesMap['{{treasurer_image}}'] = 'assets/uploads/' . ($treas['image'] ?? 'person.png');

for($i=0; $i<7; $i++){
    $num = $i + 1;
    $officialImagesMap["{{kagawad_{$num}_image}}"] = 'assets/uploads/' . ($kagawads[$i]['image'] ?? 'person.png');
}

$design = json_decode($template["content"], true) ?: [];
$brgy_info = $db->from("tblbrgy_info")->where("id", 1)->first()->exec();
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php include "templates/header.php"; ?>
        <title>Designer - <?= $template['name'] ?></title>
        <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
        <style>
            #canvas-wrapper {
                background: #525659;
                padding: 50px;
                display: flex;
                justify-content: center;
                overflow: auto;
                height: calc(100vh - 200px);
            }

            #certificate-canvas {
                background: white;
                width:
                    <?= isset($template['paper_size']) && $template['paper_size'] == 'Letter' ? '215.9mm' : '210mm' ?>
                ;
                height:
                    <?= isset($template['paper_size']) && $template['paper_size'] == 'Letter' ? '279.4mm' : '297mm' ?>
                ;
                position: relative;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
                overflow: hidden;
            }

            .design-element {
                position: absolute;
                cursor: move;
                user-select: none;
                box-sizing: border-box;
                border: 1px transparent dashed;
            }

            .design-element:hover {
                border: 1px #007bff dashed;
            }

            .design-element.selected {
                border: 1px #007bff solid;
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            }

            .design-element img {
                width: 100%;
                height: 100%;
                pointer-events: none;
            }

            .toolbar {
                background: #f8f9fa;
                border-bottom: 1px solid #ddd;
                padding: 10px;
                position: sticky;
                top: 0;
                z-index: 1000;
            }

            .sidebar-props {
                height: calc(100vh - 200px);
                overflow-y: auto;
                border-left: 1px solid #ddd;
                padding: 15px;
                background: #fff;
            }

            .placeholder-badge {
                cursor: pointer;
                margin: 2px;
                font-size: 10px;
                display: inline-block;
            }
            
            .placeholder-image-badge {
                cursor: pointer;
                margin: 2px;
                font-size: 10px;
                background-color: #6610f2;
                color: white;
                display: inline-block;
                padding: 3px 6px;
                border-radius: 4px;
            }
            .placeholder-image-badge:hover {
                background-color: #520dc2;
                color: white;
            }

            /* Color Picker Styling */
            input[type="color"] {
                -webkit-appearance: none;
                border: none;
                width: 30px; 
                height: 30px;
                padding: 0;
                overflow: hidden;
                border-radius: 50%;
                cursor: pointer;
                background: none;
            }
            input[type="color"]::-webkit-color-swatch-wrapper {
                padding: 0;
            }
            input[type="color"]::-webkit-color-swatch {
                border: 1px solid #ddd; 
                border-radius: 50%;
                padding: 0;
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
                    <div class="toolbar d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2 align-items-center">
                            <button class="btn btn-secondary btn-sm"
                                onclick="window.location.href='certificate_templates.php'"><i
                                    class="fa fa-arrow-left"></i></button>
                            <h4 class="mb-0 ml-2">Designer: <?= $template['name'] ?></h4>
                            <div class="ml-4">
                                <button class="btn btn-primary btn-sm" onclick="addElement('text')"><i
                                        class="fas fa-plus"></i> Text</button>
                                <button class="btn btn-info btn-sm" onclick="showLogoSelector()"><i
                                        class="far fa-image"></i> Logo</button>
                                <button class="btn btn-secondary btn-sm" onclick="addElement('box')"><i
                                        class="far fa-square"></i> Rectangle</button>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-success" onclick="saveDesign()"><i class="fa fa-save"></i> Save
                                Design</button>
                        </div>
                    </div>

                    <div class="row no-gutters">
                        <div class="col-md-9">
                            <div id="canvas-wrapper">
                                <div id="certificate-canvas">
                                    <!-- Dynamic Elements will be here -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="sidebar-props">
                                <h5>Properties</h5>
                                <div id="props-panel" class="text-center text-muted">
                                    Select an element to edit properties
                                </div>
                                <hr>
                                <h5>Placeholders</h5>
                                <p class="small text-muted mb-1">Text Variables:</p>
                                <div id="placeholders">
                                    <?php
                                    $placeholders = [
                                        "fullname", "firstname", "middlename", "lastname", "age", "birthdate", "address", "precinct", 
                                        "today_day", "today_month", "today_year", "valid_until", "purpose", "captain_name",
                                        "sk_chairman_name", "secretary_name", "treasurer_name",
                                        "kagawad_1_name", "kagawad_2_name", "kagawad_3_name", "kagawad_4_name", "kagawad_5_name", "kagawad_6_name", "kagawad_7_name"
                                    ];
                                    foreach ($placeholders as $p): ?>
                                        <span class="badge badge-info placeholder-badge"
                                            onclick="insertPlaceholder('{{<?= $p ?>}}')">{{<?= $p ?>}}</span>
                                    <?php endforeach; ?>
                                </div>
                                <hr>
                                <h5>Official Photos</h5>
                                <p class="small text-muted mb-1">Click to add photo:</p>
                                <div id="image-placeholders">
                                     <span class="placeholder-image-badge" onclick="addElement('image', '{{captain_image}}')">Captain Photo</span>
                                     <span class="placeholder-image-badge" onclick="addElement('image', '{{sk_chairman_image}}')">SK Chair Photo</span>
                                     <span class="placeholder-image-badge" onclick="addElement('image', '{{secretary_image}}')">Sec Photo</span>
                                     <span class="placeholder-image-badge" onclick="addElement('image', '{{treasurer_image}}')">Treas Photo</span>
                                     <?php for($i=1; $i<=7; $i++): ?>
                                         <span class="placeholder-image-badge" onclick="addElement('image', '{{kagawad_<?= $i ?>_image}}')">Kagawad <?= $i ?> Photo</span>
                                     <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo Selector Modal -->
        <div class="modal fade" id="logoModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Select Logo</h5>
                    </div>
                    <div class="modal-body d-flex flex-wrap gap-2 justify-content-center">
                        <img src="assets/uploads/<?= $brgy_info['city_logo'] ?>" class="img-thumbnail"
                            style="width: 100px; cursor: pointer;"
                            onclick="addElement('image', 'assets/uploads/<?= $brgy_info['city_logo'] ?>')">
                        <img src="assets/uploads/<?= $brgy_info['brgy_logo'] ?>" class="img-thumbnail"
                            style="width: 100px; cursor: pointer;"
                            onclick="addElement('image', 'assets/uploads/<?= $brgy_info['brgy_logo'] ?>')">
                        <?php if (!empty($brgy_info['bg_logo'])): ?>
                            <img src="assets/uploads/<?= $brgy_info['bg_logo'] ?>" class="img-thumbnail"
                                style="width: 100px; cursor: pointer;"
                                onclick="addElement('image', 'assets/uploads/<?= $brgy_info['bg_logo'] ?>')">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include "templates/footer.php"; ?>
        <script>
            let design = <?= json_encode($design) ?>;
            const placeholderImages = <?= json_encode($officialImagesMap) ?>;
            let selectedId = null;

            function init() {
                render();
                setupInteract();
            }

            function render() {
                const canvas = document.getElementById('certificate-canvas');
                canvas.innerHTML = '';
                design.forEach(el => {
                    const div = document.createElement('div');
                    div.id = el.id;
                    div.className = 'design-element' + (selectedId === el.id ? ' selected' : '');
                    div.style.left = el.x + 'px';
                    div.style.top = el.y + 'px';
                    if (el.width) div.style.width = el.width + 'px';
                    if (el.height) div.style.height = el.height + 'px';
                    div.style.cssText += el.style;

                    if (el.type === 'text') {
                        div.innerHTML = el.content;
                        div.contentEditable = true;
                        div.oninput = (e) => {
                            el.content = e.target.innerHTML;
                        };
                    } else if (el.type === 'image') {
                        const img = document.createElement('img');
                        // Resolve Dynamic Image for Preview
                        if (el.src.startsWith('{{')) {
                             img.src = placeholderImages[el.src] || 'assets/img/person.png';
                        } else {
                             img.src = el.src;
                        }
                        div.appendChild(img);
                    } else if (el.type === 'box') {
                        if (!el.style.includes('border')) {
                            div.style.border = '1px solid black';
                        }
                    }

                    div.onclick = (e) => {
                        e.stopPropagation();
                        selectElement(el.id);
                    };

                    canvas.appendChild(div);
                });
            }

            function selectElement(id) {
                selectedId = id;
                const el = design.find(e => e.id === id);
                const props = document.getElementById('props-panel');

                document.querySelectorAll('.design-element').forEach(dom => {
                    dom.classList.toggle('selected', dom.id === id);
                });

                if (!el) {
                    props.innerHTML = 'Select an element to edit properties';
                    return;
                }

                let html = `
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Width</label>
                    <div class="col-sm-8"><input type="number" class="form-control form-control-sm" name="el_width" oninput="updateDimension('width', this.value)" value="${Math.round(el.width) || 200}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Height</label>
                    <div class="col-sm-8"><input type="number" class="form-control form-control-sm" name="el_height" oninput="updateDimension('height', this.value)" value="${Math.round(el.height) || ''}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Border</label>
                    <div class="col-sm-4"><input type="number" class="form-control form-control-sm" oninput="updateStyle('borderWidth', this.value + 'px'); updateStyle('borderStyle', 'solid')" value="${parseInt(window.getComputedStyle(document.getElementById(id)).borderWidth) || 0}"></div>
                    <div class="col-sm-4"><input type="color" oninput="updateStyle('borderColor', this.value)" value="${rgbToHex(window.getComputedStyle(document.getElementById(id)).borderColor) || '#000000'}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Background</label>
                    <div class="col-sm-5 pt-1">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="bg-trans" onchange="toggleBackground(this.checked)" ${window.getComputedStyle(document.getElementById(id)).backgroundColor === 'rgba(0, 0, 0, 0)' || window.getComputedStyle(document.getElementById(id)).backgroundColor === 'transparent' ? 'checked' : ''}>
                            <label class="custom-control-label" for="bg-trans">Transparent</label>
                        </div>
                    </div>
                    <div class="col-sm-3"><input type="color" oninput="updateStyle('backgroundColor', this.value); document.getElementById('bg-trans').checked = false;" value="${rgbToHex(window.getComputedStyle(document.getElementById(id)).backgroundColor) || '#ffffff'}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Opacity</label>
                    <div class="col-sm-8 d-flex align-items-center">
                        <input type="range" class="custom-range" min="0" max="1" step="0.1" 
                            oninput="updateStyle('opacity', this.value)" 
                            value="${window.getComputedStyle(document.getElementById(id)).opacity || 1}">
                    </div>
                </div>
                ${el.type === 'text' ? `
                <div class="form-group">
                    <label>Text Content</label>
                    <textarea class="form-control form-control-sm" rows="3" oninput="updateContent(this.value)">${el.content}</textarea>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Text Color</label>
                    <div class="col-sm-8"><input type="color" oninput="updateStyle('color', this.value)" value="${rgbToHex(window.getComputedStyle(document.getElementById(id)).color) || '#000000'}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Font Family</label>
                    <div class="col-sm-8">
                        <select class="form-control form-control-sm" onchange="updateStyle('fontFamily', this.value)">
                            <option value="inherit" ${el.style.indexOf('font-family') === -1 ? 'selected' : ''}>Default</option>
                            <option value="'Roboto', sans-serif" ${el.style.indexOf('Roboto') !== -1 ? 'selected' : ''}>Roboto</option>
                            <option value="'Playfair Display', serif" ${el.style.indexOf('Playfair') !== -1 ? 'selected' : ''}>Playfair (Serif)</option>
                            <option value="'Montserrat', sans-serif" ${el.style.indexOf('Montserrat') !== -1 ? 'selected' : ''}>Montserrat (Modern)</option>
                            <option value="'Dancing Script', cursive" ${el.style.indexOf('Dancing') !== -1 ? 'selected' : ''}>Dancing Script (Cursive)</option>
                            <option value="'Times New Roman', serif" ${el.style.indexOf('Times') !== -1 ? 'selected' : ''}>Times New Roman</option>
                            <option value="Arial, sans-serif" ${el.style.indexOf('Arial') !== -1 ? 'selected' : ''}>Arial</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Font Size</label>
                    <div class="col-sm-8"><input type="number" class="form-control form-control-sm" onchange="updateStyle('fontSize', this.value + 'pt')" value="${parseInt(window.getComputedStyle(document.getElementById(id)).fontSize) || 12}"></div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Weight</label>
                    <div class="col-sm-8">
                        <select class="form-control form-control-sm" onchange="updateStyle('fontWeight', this.value)">
                            <option value="normal" ${el.style.indexOf('bold') === -1 ? 'selected' : ''}>Normal</option>
                            <option value="bold" ${el.style.indexOf('bold') !== -1 ? 'selected' : ''}>Bold</option>
                        </select>
                    </div>
                </div>
                ` : ''}
                ${el.type === 'image' ? `
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Shape</label>
                    <div class="col-sm-8">
                        <select class="form-control form-control-sm" onchange="updateStyle('borderRadius', this.value); updateStyle('overflow', 'hidden');">
                            <option value="0" ${!el.style.includes('border-radius') || el.style.includes('0px') ? 'selected' : ''}>Square</option>
                            <option value="10px" ${el.style.includes('10px') ? 'selected' : ''}>Rounded</option>
                            <option value="50%" ${el.style.includes('50%') ? 'selected' : ''}>Circle</option>
                        </select>
                    </div>
                </div>
                ` : ''}
                <div class="text-right">
                    <button class="btn btn-danger btn-sm" onclick="removeElement('${id}')"><i class="fa fa-trash"></i> Delete</button>
                </div>
            `;
                props.innerHTML = html;
            }

            function toggleBackground(isTransparent) {
                updateStyle('backgroundColor', isTransparent ? 'transparent' : '#ffffff');
            }

            function updateDimension(prop, val) {
                const el = design.find(e => e.id === selectedId);
                const dom = document.getElementById(selectedId);
                el[prop] = parseFloat(val);
                if (val === '') {
                    dom.style[prop] = 'auto';
                    el[prop] = null;
                } else {
                    dom.style[prop] = val + 'px';
                }
            }

            function updateContent(val) {
                const el = design.find(e => e.id === selectedId);
                el.content = val;
                document.getElementById(selectedId).innerHTML = val;
            }

            function updateStyle(prop, value) {
                const el = design.find(e => e.id === selectedId);
                const dom = document.getElementById(selectedId);
                dom.style[prop] = value;
                // Update the style string (naive way)
                el.style = dom.style.cssText;
            }

            function addElement(type, src = '') {
                // Prevent duplicate unique images (Placeholders)
                if (type === 'image' && src.startsWith('{{')) {
                    const exists = design.find(e => e.src === src);
                    if (exists) {
                        swal("Duplicate Photo", "This official's photo is already in the design.", "warning");
                        return;
                    }
                }

                const el = {
                    id: 'el_' + Date.now(),
                    type: type,
                    content: type === 'text' ? 'New Text' : '',
                    src: src,
                    x: 100,
                    y: 100,
                    width: type === 'text' ? 200 : 100,
                    height: type === 'text' ? null : 100,
                    style: type === 'box' ? 'font-size: 12pt; border: 1px solid black; background-color: transparent;' : 'font-size: 12pt;'
                };
                design.push(el);
                $('#logoModal').modal('hide');
                render();
                selectElement(el.id);
            }

            function removeElement(id) {
                design = design.filter(e => e.id !== id);
                selectedId = null;
                render();
                selectElement(null);
            }

            function setupInteract() {
                interact('.design-element')
                    .draggable({
                        listeners: {
                            move(event) {
                                const target = event.target;
                                const el = design.find(e => e.id === target.id);
                                el.x += event.dx;
                                el.y += event.dy;
                                target.style.left = el.x + 'px';
                                target.style.top = el.y + 'px';
                            }
                        }
                    })
                    .resizable({
                        edges: { left: true, right: true, bottom: true, top: true },
                        listeners: {
                            move(event) {
                                const target = event.target;
                                const el = design.find(e => e.id === target.id);
                                el.width = event.rect.width;
                                el.height = event.rect.height;
                                el.x += event.deltaRect.left;
                                el.y += event.deltaRect.top;

                                target.style.width = el.width + 'px';
                                target.style.height = el.height + 'px';
                                target.style.left = el.x + 'px';
                                target.style.top = el.y + 'px';

                                // Sync props panel if selected
                                if (selectedId === el.id) {
                                    const wInput = document.querySelector('input[name="el_width"]');
                                    const hInput = document.querySelector('input[name="el_height"]');
                                    if (wInput) wInput.value = Math.round(el.width);
                                    if (hInput) hInput.value = Math.round(el.height);
                                }
                            }
                        }
                    });
            }

            function showLogoSelector() {
                $('#logoModal').modal('show');
            }

            function insertPlaceholder(p) {
                if (!selectedId) {
                    // Start a new text element with this placeholder if none selected
                    addElement('text'); 
                    // Need to find the element we just added
                    // Since addElement pushes to end, use strict timing
                    const newEl = design[design.length - 1];
                    // selectElement is called by addElement, so selectedId is set.
                    // But we returned? No addElement is sync.
                    
                    // We must wait? No.
                    // Let's just set the content directly
                    newEl.content = p;
                    document.getElementById(newEl.id).innerHTML = p;
                    selectElement(newEl.id);
                    return;
                }
                const el = design.find(e => e.id === selectedId);
                if (el.type !== 'text') return;

                // Prevent duplicates (Limit 1)
                if (el.content.includes(p)) {
                    swal("Duplicate!", "This placeholder is already in this text box.", "warning");
                    return;
                }

                if (el.content === 'New Text') {
                    el.content = p;
                } else {
                    el.content += ' ' + p;
                }

                // Update canvas immediately
                document.getElementById(selectedId).innerHTML = el.content;

                // Refresh properties panel
                selectElement(selectedId);
            }

            function rgbToHex(rgb) {
                if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') return '#ffffff';
                const res = rgb.match(/\d+/g);
                if (!res) return '#ffffff';
                return "#" + res.slice(0, 3).map(x => {
                    const hex = parseInt(x).toString(16);
                    return hex.length === 1 ? "0" + hex : hex;
                }).join("");
            }

            function saveDesign() {
                $.post('model/templates.php', {
                    action: 'save_design',
                    id: '<?= $id ?>',
                    design: JSON.stringify(design)
                }, function (res) {
                    const data = JSON.parse(res);
                    if (data.status === 'success') {
                        swal("Saved!", "Design updated successfully", "success");
                    }
                });
            }

            document.getElementById('certificate-canvas').onclick = () => selectElement(null);
            init();
        </script>
    </body>

</html>
