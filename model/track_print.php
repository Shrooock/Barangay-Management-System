<?php
include "../bootstrap/index.php";

if (isset($_POST['resident_id']) && isset($_POST['cert_id'])) {
    $resident_id = $_POST['resident_id'];
    $cert_id = $_POST['cert_id'];

    // Fetch resident and certificate names for logging
    if ($cert_id == 5) {
        // Business Permit - Fetch from tblpermit
        $res_data = $db->from("tblpermit")->where("id", $resident_id)->first()->select(["name" => "name", "owner" => "owner1"])->exec();
        $resident_name = $res_data ? $res_data['name'] . " (" . $res_data['owner'] . ")" : "Unknown Business";
    } else {
        // Standard Certificates - Fetch from residents
        $res_data = $db->from("residents")->where("id", $resident_id)->first()->select(["name" => "CONCAT(firstname, ' ', lastname)"])->exec();
        $resident_name = $res_data ? $res_data['name'] : "Unknown Resident";
    }

    $cert_data = $db->from("certificate_templates")->where("certificate_id", $cert_id)->first()->select(["name" => "name"])->exec();
    
    // Fallback if template name isn't found (likely uses default names)
    if (!$cert_data) {
        if ($cert_id == 1) $cert_name = "Barangay Certificate";
        elseif ($cert_id == 4) $cert_name = "Certificate of Indigency";
        elseif ($cert_id == 5) $cert_name = "Business Clearance";
        else $cert_name = "Certificate";
    } else {
        $cert_name = $cert_data['name'];
    }

    // Insert as a resolved request to update dashboard counts
    $db->insert("certificate_requests")
        ->values([
            "resident_id" => $resident_id,
            "certificate_id" => $cert_id,
            "status" => "resolved",
            "memo" => "Printed from generation page"
        ])
        ->exec();

    // Log the activity
    logActivity($conn, "PRINT", "CERTIFICATE", "$resident_name", "Printed $cert_name");

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
}
