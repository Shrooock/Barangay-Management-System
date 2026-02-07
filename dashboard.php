<?php

require_once "bootstrap/index.php";

$residents_summary = $db
  ->from("residents")
  ->first()
  ->whereRaw("residents.deleted_at IS NULL")
  ->select([
    "total" => "COUNT(residents.id)",
    "male" => "SUM(residents.gender = 'Male')",
    "female" => "SUM(residents.gender = 'Female')",
    "total_4ps" => "SUM(residents.is_4ps)",
  ])
  ->exec();


$query9 = "SELECT * FROM tbldocuments";
$documents = $conn->query($query9)->num_rows;


$resident_details = (function () use ($db) {
  if (isUser()) {
    return $db
      ->from("residents")
      ->join("users", "users.id", "residents.account_id")
      ->where("users.id", $_SESSION["id"])
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
        "resident_type" => "residents.resident_type",
        "remarks" => "residents.remarks",
        "username" => "users.username",
        "user_type" => "users.user_type",
        "avatar" => "users.avatar",
      ])
      ->exec();
  }

  return [];
})();




function getRandomColor()
{
  $colors = [
    "#f97316",
    "#ef4444",
    "#f59e0b",
    "#eab308",
    "#84cc16",
    "#22c55e",
    "#10b981",
    "#14b8a6",
    "#06b6d4",
    "#0ea5e9",
    "#3b82f6",
    "#6366f1",
    "#8b5cf6",
    "#a855f7",
    "#d946ef",
    "#ec4899",
    "#f43f5e",
  ];

  $key = array_rand($colors, 1);

  return $colors[$key];
}

$admin_dashboard_cards = [
  [
    "icon" => "flaticon-users",
    "title" => "Population",
    "subtitle" => "Total Population",
    "value" => $residents_summary["total"] ? number_format($residents_summary["total"]) : 0,
    "href" => "resident.php",
    "color" => "#86d12f", // Green
  ],
  [
    "icon" => "flaticon-user",
    "title" => "Male",
    "subtitle" => "Total Male",
    "value" => $residents_summary["male"] ? number_format($residents_summary["male"]) : 0,
    "href" => "resident.php?gender=male",
    "color" => "#cb4be2", // Purple
  ],
  [
    "icon" => "icon-user-female",
    "title" => "Female",
    "subtitle" => "Total Female",
    "value" => $residents_summary["female"] ? number_format($residents_summary["female"]) : 0,
    "href" => "resident.php?gender=female",
    "color" => "#ff7f1d", // Orange
  ],

];

$certificate_colors = [
  "Barangay Certificate" => "#cb4be2", // Purple
  "Certificate of Indigency" => "#17b8a8", // Teal
  "Business Clearance" => "#f3545d", // Red/Pink
];

$certificate_counts = $db
  ->from(["certificates" => "c"])
  ->leftJoin(["certificate_requests" => "cr"], "c.id", "cr.certificate_id AND cr.status = 'resolved'")
  ->whereNotIn("c.id", [2, 3, 6])
  ->select([
    "name" => "c.name",
    "total" => "COUNT(cr.id)"
  ])
  ->groupBy("c.id")
  ->exec();

foreach ($certificate_counts as $cert) {
  $admin_dashboard_cards[] = [
    "icon" => "icon-docs",
    "title" => $cert["name"],
    "subtitle" => "Total Issued",
    "value" => number_format($cert["total"]),
    "href" => "resident_certification.php",
    "color" => $certificate_colors[$cert["name"]] ?? "#5cb85c",
  ];
}

?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <?php include "templates/header.php"; ?>
    <title>Dashboard - Barangay Management System</title>


    <style>
      .hidden {
        display: none !important;
      }

      label.btn.active {
        color: white !important;
        background-color: #337BB6;
      }

      .form-check>.btn-group {
        width: 100%;
      }

      .badge.badge-resolved {
        background-color: #22c55e;
      }

      .badge.badge-pending {
        background-color: #525252;
      }

      .badge.badge-rejected {
        background-color: #ef4444;
      }

      .list-group .list-group-item {
        border-width: 1px;
      }

      .request-list>.request-list__item {
        justify-content: space-between;
        align-items: center;
      }

      .request-list>.request-list__item>div>p {
        margin-bottom: 0;
      }

      .request-list>.request-list__item>div>.subtitle {
        font-size: 10px;
        opacity: 0.5;
      }

      .card-certificate-requests>.card-header>.card-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .card-certificate-requests__summary {
        font-size: 10px;
      }

    </style>
  </head>

  <body>
    <?php include "templates/loading_screen.php"; ?>

    <div class="wrapper">
      <!-- Main Header -->
      <?php include "templates/main-header.php"; ?>
      <!-- End Main Header -->

      <!-- Sidebar -->
      <?php include "templates/sidebar.php"; ?>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="content">
          <div class="panel-header bg-primary-gradient">
            <div class="page-inner">
              <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                <div>
                  <h2 class="text-white fw-bold">
                    Dashboard</h2>
                </div>
              </div>
            </div>
          </div>
          <div class="page-inner mt--2">

            <?php include "templates/alert.php"; ?>

            <?php if (isUser()): ?>
              <div class="row">
                <div class="col-md-12">
                  <?php include "templates/resident-review-card.php"; ?>
                </div>

              </div>
            <?php endif; ?>

            <?php if (role(["administrator", "staff"])): ?>
              <div class="row">
                <?php foreach ($admin_dashboard_cards as $row): ?>
                  <div class="col-md-4">
                    <div class="card card-stats card-round" style="background-color: <?= $row['color'] ?>; color: #fff">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-2">
                            <div class="icon-big text-center">
                              <?php if (isset($row["icon"])): ?>
                                <i class="<?= $row["icon"] ?>"></i>
                              <?php elseif (isset($row["icon-text"])): ?>
                                <i><?= $row["icon-text"] ?></i>
                              <?php endif; ?>
                            </div>
                          </div>
                          <div class="col-3 col-stats">
                          </div>
                          <div class="col-6 col-stats">
                            <div class="numbers mt-4">
                              <h7 class="fw-bold text-uppercase">
                                <?= $row["title"] ?>
                              </h7>
                              <h3 class="fw-bold text-uppercase">
                                <?= $row["value"] ?>
                              </h3>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="card-body">
                        <a href="<?= $row["href"] ?>" class="card-link text-light">
                          <?= $row["subtitle"] ?>
                        </a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <div class="card-title fw-bold">Gender Statistics</div>
                    </div>
                    <div class="card-body">
                      <div class="chart-container" style="min-height: 300px">
                        <canvas id="genderChart"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <div class="card-title fw-bold">Document Request Statistics</div>
                    </div>
                    <div class="card-body">
                      <div class="chart-container" style="min-height: 300px">
                        <canvas id="docChart"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </div>
        <!-- Main Footer -->
        <?php include "templates/main-footer.php"; ?>
        <!-- End Main Footer -->

      </div>

    </div>
    <?php include "templates/footer.php"; ?>

    <?php
    $cert_labels = [];
    $cert_values = [];
    foreach ($certificate_counts as $cert) {
      $cert_labels[] = $cert["name"];
      $cert_values[] = $cert["total"];
    }
    ?>

    <script>
      // Gender Chart
      var ctxGender = document.getElementById('genderChart').getContext('2d');
      var genderChart = new Chart(ctxGender, {
        type: 'pie',
        data: {
          datasets: [{
            data: [<?= $residents_summary["male"] ?>, <?= $residents_summary["female"] ?>],
            backgroundColor: ["#5cb85c", "#ffa534"],
            borderWidth: 0
          }],
          labels: ['Male', 'Female']
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            position: 'bottom',
            labels: {
              fontColor: 'rgb(154, 154, 154)',
              fontSize: 11,
              usePointStyle: true,
              padding: 20
            }
          },
          pieceLabel: {
            render: 'percentage',
            fontColor: 'white',
            fontSize: 14,
          },
          tooltips: false,
          layout: {
            padding: {
              left: 20,
              right: 20,
              top: 20,
              bottom: 20
            }
          }
        }
      });

      // Document Request Chart
      var ctxDocs = document.getElementById('docChart').getContext('2d');
      var docChart = new Chart(ctxDocs, {
        type: 'bar',
        data: {
          labels: <?= json_encode($cert_labels) ?>,
          datasets: [{
            label: "Requests",
            backgroundColor: ["#cb4be2", "#17b8a8", "#f3545d"],
            borderColor: ["#cb4be2", "#17b8a8", "#f3545d"],
            data: <?= json_encode($cert_values) ?>,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            display: false
          },
          scales: {
            yAxes: [{
              ticks: {
                display: true,
                beginAtZero: true,
              },
              gridLines: {
                drawBorder: false,
                display: true
              }
            }],
            xAxes: [{
              gridLines: {
                drawBorder: false,
                display: false
              }
            }]
          },
        }
      });
    </script>
  </body>

</html>
