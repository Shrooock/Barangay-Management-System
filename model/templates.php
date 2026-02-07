<?php
require "../bootstrap/index.php";

if (!isAdmin()) {
    $_SESSION["message"] = "Unauthorized access";
    $_SESSION["status"] = "danger";
    header("Location: ../dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];

    if ($action == "add") {
        // Self-healing: Add paper_size column if not exists
        $cols = $conn->query("SHOW COLUMNS FROM certificate_templates LIKE 'paper_size'");
        if ($cols->num_rows == 0) {
            $conn->query("ALTER TABLE certificate_templates ADD COLUMN paper_size VARCHAR(20) DEFAULT 'A4'");
        }

        $name = $_POST["name"];
        $certificate_id = $_POST["certificate_id"];
        $paper_size = $_POST["paper_size"] ?? 'A4';

        // Boilerplate JSON design
        $default_design = [
            ["id" => uniqid(), "type" => "text", "content" => "REPUBLIC OF THE PHILIPPINES", "x" => 0, "y" => 20, "width" => 100, "style" => "font-weight: bold; text-align: center; width: 100%;"],
            ["id" => uniqid(), "type" => "text", "content" => "BARANGAY CERTIFICATION", "x" => 0, "y" => 150, "width" => 100, "style" => "font-size: 24pt; font-weight: bold; text-align: center; width: 100%;"],
            ["id" => uniqid(), "type" => "text", "content" => "This is to certify that {{fullname}}...", "x" => 50, "y" => 250, "width" => 80, "style" => "font-size: 12pt; text-align: justify;"]
        ];

        $id = $db->insert("certificate_templates")->values([
            "certificate_id" => $certificate_id,
            "name" => $name,
            "content" => json_encode($default_design),
            "styles" => "",
            "paper_size" => $paper_size
        ])->exec();

        logActivity($conn, "ADD", "TEMPLATE", $name, "Created new visual certificate template");

        $_SESSION["message"] = "Template created! Open the designer to customize.";
        $_SESSION["status"] = "success";
        header("Location: ../certificate_templates.php");
        exit;
    }

    if ($action == "save_design") {
        $id = $_POST["id"];
        $design = $_POST["design"]; // JSON string from frontend

        $db->update("certificate_templates")
            ->where("id", $id)
            ->set([
                "content" => $design
            ])
            ->exec();

        logActivity($conn, "EDIT", "TEMPLATE", "ID: $id", "Updated visual template design");

        echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == "save_as_new") {
        $name = $_POST["name"];
        $certificate_id = $_POST["certificate_id"];
        $design = $_POST["design"];

        $id = $db->insert("certificate_templates")->values([
            "certificate_id" => $certificate_id,
            "name" => $name,
            "content" => $design,
            "styles" => ""
        ])->exec();

        logActivity($conn, "ADD", "TEMPLATE", $name, "Created new template from edited certificate");

        echo json_encode(["status" => "success", "id" => $id]);
        exit;
    }

    if ($action == "set_default") {
        $id = $_POST["id"];
        $certificate_id = $_POST["certificate_id"];

        // Reset all for this cert type
        $db->update("certificate_templates")
            ->where("certificate_id", $certificate_id)
            ->set(["is_default" => 0])
            ->exec();

        // Set the new default
        $db->update("certificate_templates")
            ->where("id", $id)
            ->set(["is_default" => 1])
            ->exec();

        logActivity($conn, "EDIT", "TEMPLATE", "ID: $id", "Set template as main/default");

        echo json_encode(["status" => "success"]);
        exit;
    }

    if ($action == "remove_default") {
        $certificate_id = $_POST["certificate_id"];

        // Reset all for this cert type
        $db->update("certificate_templates")
            ->where("certificate_id", $certificate_id)
            ->set(["is_default" => 0])
            ->exec();

        logActivity($conn, "EDIT", "TEMPLATE", "CERT_ID: $certificate_id", "Reverted to Standard Design");

        echo json_encode(["status" => "success"]);
        exit;
    }
}

if (isset($_GET["action"]) && $_GET["action"] == "delete") {
    $id = $_GET["id"];
    $template = $db->from("certificate_templates")->where("id", $id)->first()->exec();
    if ($template) {
        $db->delete("certificate_templates")->where("id", $id)->exec();
        logActivity($conn, "DELETE", "TEMPLATE", $template["name"], "Deleted certificate template");
        $_SESSION["message"] = "Template removed successfully";
        $_SESSION["status"] = "success";
    }
    header("Location: ../certificate_templates.php");
    exit;
}
