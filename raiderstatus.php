<?php
require "database.php";
include "constants.php";

// make date/time string for most recent server reset (tuesday at 11am)
date_default_timezone_set('America/Chicago');
$date = new DateTime();
$date->modify('this week +1 days');
$date->setTime(11, 0);
if ($date > new DateTime())
{
    $date->modify('last week +1 days');
    $date->setTime(11, 0);
}
$mostRecentServerReset = $date->format('Y-m-d H:i:s');

$sql = "SELECT r.name, r.playerclass, rh.neck_level, rh.cape_level, rkh.key_level, rkh.dungeon, rh.timestamp
FROM raider_history AS rh
JOIN(
	SELECT raider_id, MAX(`timestamp`) AS ts
	FROM raider_history
	GROUP BY raider_id) t ON t.raider_id = rh.raider_id AND t.ts = rh.`timestamp`
JOIN (
	SELECT blizz_id, `name`, playerClass
	FROM raider ) r ON r.blizz_id = rh.raider_id
left JOIN (
	SELECT rkht.raider_id, rkht.key_level, rkht.dungeon
	FROM raider_key_history as rkht
	JOIN (
		SELECT raider_id, MAX(`timestamp`) AS ts2
		FROM raider_key_history WHERE `timestamp` > '". $mostRecentServerReset ."'
		GROUP BY raider_id) t2 ON t2.raider_id = rkht.raider_id AND t2.ts2 = rkht.`timestamp`
	) rkh ON rkh.raider_id = r.blizz_id
	";
$stmt = $dbConn->prepare($sql);
$stmt->execute();
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetchAll();
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
                <a class="nav-item nav-link" href="#">Home</a>
                <a class="nav-item nav-link active" href="#">Roster</a>
                <a class="nav-item nav-link" href="loot.php">Loot</a>
              </div>
            </div>
        </nav>
        <div class="bg-dark">
            <h1 class="text-light">Roster Audit</h1>
            <table data-toggle="table" class="table table-dark table-striped table-bordered" data-custom-sort="customSort">
                <caption>Data is updated every day at 2am EST.</caption>
                <thead class="thead-dark">
                    <tr>
                        <th data-field="name" data-sortable="true">Name</th>
                        <th data-field="neck-level" data-sortable="true" data-order="desc">Neck Level</th>
                        <th data-field="cape-level" data-sortable="true" data-order="desc">Cape Level</th>
                        <th data-field="weekly-key" data-sortable="true" data-order="desc">Highest Weekly Key</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($data as $row) {
                            $weeklyKey = ":(";
                            if (!is_null($row["key_level"]))
                            {
                                if (!array_key_exists($row["dungeon"], $dungeonIdToName))
                                {
                                    $weeklyKey = $row["key_level"]." ?(".$row["dungeon"].")";
                                }
                                else 
                                {
                                    $weeklyKey = $row["key_level"]." ".$dungeonIdToName[$row["dungeon"]];
                                }
                            }
                            echo "<tr>\n";
                            echo "<td><a class=\"text-light\" href=\"/profile.php?name=".$row["name"]."\"><img src='img/".$row["playerClass"].".png'> ".$row["name"]."</a></td>\n";
                            echo "<td>".$row["neck_level"]."</td>\n";
                            echo "<td>".$row["cape_level"]."</td>\n";
                            echo "<td>".$weeklyKey."</td>\n";
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <script>
        function customSort(sortName, sortOrder, data) {
            var order = sortOrder === 'desc' ? -1 : 1
            data.sort(function (a, b) {
                var aa = a[sortName];
                var bb = b[sortName];
                if (sortName === 'name') {
                    aa = $("<img>").html(aa).text();
                    bb = $("<img>").html(bb).text();
                }
                else if (sortName === 'weekly-key') {
                    aa = aa.replace(":(", "0 ");
                    bb = bb.replace(":(", "0 ");
                    aa = parseInt(aa.substring(0, aa.indexOf(" ")));
                    bb = parseInt(bb.substring(0, bb.indexOf(" ")));
                    //console.log(aa + " " + bb);
                }
                else if (sortName === 'cape-level') {
                    aa = parseInt(aa);
                    bb = parseInt(bb);
                    //console.log(aa.toString() + " " + bb.toString());
                }
                if (aa < bb) {
                    return order * -1
                }
                if (aa > bb) {
                    return order
                }
                return 0
            });
        }
        </script>
    </body>
</html>

<?php
// close mysql connection
$dbConn = null;
?>