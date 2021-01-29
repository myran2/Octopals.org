<?php
require "database.php";

$playerName = $_GET['name'];
filter_input(INPUT_GET, $playerName, FILTER_SANITIZE_STRING);
if (strlen($playerName) < 2 || strlen($playerName) > 12)
{
    header('Location: http://www.octopals.org');
    exit;
}

$sql = "SELECT `name` FROM raider WHERE `name` = ?";
$stmt = $dbConn->prepare($sql);
$result = $stmt->execute([$playerName]);
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetch();
if ($data == false) {
    header('Location: http://www.octopals.org');
    exit;
}


// determine number of weeks stored in DB
$sql = "SELECT DATE_FORMAT(DATE_ADD(NOW(), INTERVAL '-1 -10' DAY_HOUR), '%u')
- min(DATE_FORMAT(DATE_ADD(`timestamp`, INTERVAL '-1 -10' DAY_HOUR), '%u')) + 1 AS totalWeeks FROM raider_key_history";
$stmt = $dbConn->prepare($sql);
$stmt->execute();
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetch();
$totalWeeks = $data["totalWeeks"];

$sql = "SELECT DATE_FORMAT(DATE_ADD(`timestamp`, INTERVAL '-1 -10' DAY_HOUR), '%u') week, raider_id, max(key_level) AS key_level FROM raider_key_history
WHERE raider_id = (SELECT blizz_id FROM raider WHERE `name`=?)
GROUP BY raider_id, WEEK";
$stmt = $dbConn->prepare($sql);
$result = $stmt->execute([$playerName]);
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetchAll();

$weeklyKeyAbove15Count = 0;
$weeklyKeyBelow15Count = 0;
$weeklyKeySkippedCount = 0;
foreach($data as $row) {
    if ($row["key_level"] >= 15) {
        $weeklyKeyAbove15Count += 1;
    }
    else {
        $weeklyKeyBelow15Count += 1;
    }
}
$weeklyKeySkippedCount = $totalWeeks - ($weeklyKeyAbove15Count + $weeklyKeyBelow15Count);

$sql = "SELECT neck_level, DATE_FORMAT(`timestamp`, \"%m-%d\") AS dt FROM raider_history WHERE raider_id = (SELECT blizz_id FROM raider WHERE `name` = ?) GROUP BY dt";
$stmt = $dbConn->prepare($sql);
$result = $stmt->execute([$playerName]);
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetchAll();

$labels = "";
$neckData = "";
foreach($data as $row) {
    $labels = $labels.", '". $row['dt']."'";
    $neckData = $neckData.", ".$row['neck_level'];
}
?>

<!doctype html>
<html lang="en">
    <head>
        <title>Octopals Roster</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
    </head>

    <body class="bg-dark">
        <nav class="navbar navbar-dark navbar-expand-lg">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="#">
                <img src="img/pal.png" width="30" height="30" class="d-inline-block align-top" alt="">
                Octopals
            </a>
            <div class="collapse navbar-collapse text-dark" id="navbarNavAltMarkup">
              <div class="navbar-nav">
                <a class="nav-item nav-link" href="http://www.octopals.org">Home</a>
                <a class="nav-item nav-link" href="http://www.octopals.org">Roster</a>
              </div>
            </div>
        </nav>
        <div class="bg-dark">
            <div class="container">
                <div class="row">
                    <h1 class="text-light"><?php echo $playerName ?></h1>
                </div>
                <div class="row">
                    <div class="col-sm">
                        <h3 class="text-light">Weekly Keys</h3>
                        <div class="chart-container" style="position: relative; height:400px; width: 400px;">
                            <canvas id="weeklyKeyChart"></canvas>
                        </div>
                    </div>
                    <div class="col-sm">
                        <h3 class="text-light">Neck Level</h3>
                        <div class="chart-container" style="position: relative; height:400px; width: 400px;">
                            <canvas id="neckLevelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script>
        var donutChartDiv = document.getElementById("weeklyKeyChart").getContext('2d');
        var donutChart = new Chart(donutChartDiv, {
            type: 'doughnut',
            data: {
                labels: ["15 or Higher", "Below 15", "No Key"],
                datasets: [{
                    data: [<?php echo $weeklyKeyAbove15Count; ?>, <?php echo $weeklyKeyBelow15Count; ?>, <?php echo $weeklyKeySkippedCount; ?>],
                    backgroundColor: ["#28a745", "#ffc107", "#dc3545"],
                }],
                borderWidth: 0
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom',
                    labels: {
                        fontColor: '#f8f9fa',
                        fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
                    }
                }
            }
        });
        var lineChartDiv = document.getElementById("neckLevelChart").getContext('2d');
        var lineChart = new Chart(lineChartDiv, {
            type: 'line',
            data: {
                labels: [<?php echo substr($labels, 2); ?>],
                datasets: [{
                    yAxisID: 'y-axis',
                    xAxisID: 'x-axis',
                    data: [<?php echo substr($neckData, 2); ?>],
                    fill: false,
                    borderColor: '#007bff',
                    lineTension: 0.1,

                }],
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom',
                    display: false,
                    labels: {
                        fontColor: '#f8f9fa',
                        fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            fontColor: '#f8f9fa',
                            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
                        },
                        id: 'y-axis',
                        fontColor: '#f8f9fa',
                        fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
                    }],
                    xAxes: [{
                        ticks: {
                            fontColor: '#f8f9fa',
                            fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"'
                        },
                        id: 'x-axis',
                    }]
                }
            }
        });
    </script>
</html>

<?php
// close mysql connection
$dbConn = null;
?>