<?php
include "../bootstrap/index.php";

$result = $conn->query("SELECT * FROM tblevents WHERE status = 'Active'");
$events = [];

while ($row = $result->fetch_assoc()) {
    $title = $row['title'];
    if (!empty($row['end']) && strtotime($row['end']) < time()) {
        $title .= " (Ended)";
    }

    $events[] = [
        'id' => $row['id'],
        'title' => $title,
        'start' => $row['start'],
        'end' => $row['end'],
        'description' => $row['description'],
        'color' => $row['color'],
        'textColor' => '#ffffff'
    ];
}

// Fetch Blotter/Incidents
$blotter_query = $conn->query("SELECT * FROM tblblotter WHERE status != 'Settled'");
while ($row = $blotter_query->fetch_assoc()) {
    $color = '#f3545d'; // Default Active (Red)
    if ($row['status'] == 'Scheduled') {
        $color = '#ff9e27'; // Orange
    } elseif ($row['status'] == 'Settled') {
        $color = '#28a745'; // Green
    }

    $events[] = [
        'id' => 'blotter-' . $row['id'],
        'title' => 'Blotter: ' . $row['type'] . ' - ' . $row['complainant'],
        'start' => $row['date'] . 'T' . $row['time'],
        'description' => $row['details'],
        'color' => $color,
        'textColor' => '#ffffff',
        'isBlotter' => true // Flag to identify in frontend
    ];
}

echo json_encode($events);
?>
