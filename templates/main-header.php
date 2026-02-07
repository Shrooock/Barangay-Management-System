<?php 
include "model/fetch_brgy_info.php"; 

$upcoming_events = [];
if(isset($conn)){
    // Fetch Events (From Today onwards)
    $res = $conn->query("SELECT * FROM tblevents WHERE start >= CURDATE() AND status = 'Active'");
    while($row = $res->fetch_assoc()){
        $upcoming_events[] = [
            'title' => $row['title'],
            'start' => $row['start'], 
            'end' => $row['end'],
            'color' => $row['color']
        ];
    }

    // Fetch Blotter/Incidents (From Today onwards)
    $res = $conn->query("SELECT * FROM tblblotter WHERE date >= CURDATE() AND status IN ('Active', 'Scheduled')");
    while($row = $res->fetch_assoc()){
        $upcoming_events[] = [
            'title' => "Blotter: " . $row['type'] . " - " . $row['complainant'],
            'start' => $row['date'] . ' ' . $row['time'],
            'end' => null,
            'color' => ($row['status'] == 'Scheduled') ? '#ff9e27' : '#f3545d'
        ];
    }

    // Sort by Date ASC
    usort($upcoming_events, function($a, $b) {
        return strtotime($a['start']) - strtotime($b['start']);
    });

    // Limit to 5
    $upcoming_events = array_slice($upcoming_events, 0, 5);
}
?>

<div class="main-header">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="blue">

        <a href="dashboard.php" class="logo">
            <span class="text-light ml-2 fw-bold" style="font-size:20px">BARANGAY</span>
        </a>
        <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse" data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">
                <i class="icon-menu"></i>
            </span>
        </button>
        <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
        <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
                <i class="icon-menu"></i>
            </button>
        </div>
    </div>
    <!-- End Logo Header -->

    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-expand-lg" data-background-color="blue2">
        <div class="container-fluid d-flex justify-content-end ">
            <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                <li class="nav-item dropdown hidden-caret">
                    <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bell"></i>
                        <?php if(count($upcoming_events) > 0): ?>
                            <span class="notification"><?= count($upcoming_events) ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                        <li>
                            <div class="dropdown-title">You have <?= count($upcoming_events) ?> upcoming events</div>
                        </li>
                        <li>
                            <div class="notif-scroll scrollbar-outer">
                                <div class="notif-center">
                                    <?php foreach($upcoming_events as $event): ?>
                                    <a href="calendar.php">
                                        <div class="notif-icon notif-primary"> <i class="fa fa-calendar-check"></i> </div>
                                        <div class="notif-content">
                                            <span class="block">
                                                <?= $event['title'] ?>
                                                <?php 
                                                    if (isset($event['end']) && !empty($event['end']) && strtotime($event['end']) < time()) {
                                                        echo '<span class="text-danger ml-1" style="font-size: 0.8em; font-weight: bold;">(Event Ended)</span>';
                                                    }
                                                ?>
                                            </span>
                                            <span class="time"><?= date('M d, h:i A', strtotime($event['start'])) ?></span> 
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a class="see-all" href="calendar.php">See all events<i class="fa fa-angle-right"></i> </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown hidden-caret">
                    <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-settings"></i>
                    </a>
                    <ul class="dropdown-menu messages-notif-box animated fadeIn" aria-labelledby="messageDropdown">
                        <li>
                            <?php if (isset($_SESSION["role"])): ?>
                                <a class="see-all" href="model/logout.php">Sign Out<i class="icon-logout"></i> </a>
                            <?php else: ?>
                                <a class="see-all" href="login.php">Sign In<i class="icon-login"></i> </a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <!-- End Navbar -->
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var notifDropdown = document.getElementById('notifDropdown');
        var badge = document.querySelector('.notification');
        
        if (badge) {
            // Check LocalStorage for persistence
            var currentCount = badge.innerText.trim();
            var seenCount = localStorage.getItem('notif_seen_count');

            // Hide if the user has already seen this specific count
            if (currentCount === seenCount) {
                badge.style.display = 'none';
            }
        }

        if (notifDropdown) {
            notifDropdown.addEventListener('click', function() {
                var badge = this.querySelector('.notification');
                if (badge) {
                    badge.style.display = 'none';
                    // Persist that we have seen this count
                    localStorage.setItem('notif_seen_count', badge.innerText.trim());
                }
            });
        }
    });
</script>