<?php
require "database.php";
require "constants.php";

$sql = "SELECT blizz_id, `name`, playerClass, playerRoles FROM raider";
$stmt = $dbConn->prepare($sql);
$stmt->execute();
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetchAll();
?>

<html lang="en">
    <head>
        <title>Octopals Loot Needs</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css">
        <script src="https://code.jquery.com/jquery-3.5.0.min.js" integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js"></script>
        <script src="/js/bootstrap-table-sticky-header.js"></script>
        <style>
            .bootstrap-table .fixed-table-container .fixed-table-body {
                height: max-content;
            }

            .toast {
                left: 50%;
                position: fixed;
                transform: translate(-50%, 0px);
                z-index: 9999;
            }

            .fix-sticky {
                position: fixed !important;
                overflow: hidden;
                z-index: 100;
            }
        </style>
    </head>

    <body class="bg-dark">
        <div id="successToast" class="toast sticky-top" data-delay=2000>
            <div class="toast-header">
            <strong class="mr-auto">Loot Preferences Saved</strong>
            </div>
        </div>

        <nav class="navbar navbar-dark navbar-expand-lg navbar-light">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="#">
                <img src="img/pal.png" width="30" height="30" class="d-inline-block align-top" alt="">
                Octopals
            </a>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
              <div class="navbar-nav">
                <a class="nav-item nav-link" href="/index.php">Home</a>
                <a class="nav-item nav-link" href="/index.php">Roster</a>
                <a class="nav-item nav-link active" href="#">Loot</a>
              </div>
            </div>
        </nav>

        <h1 class="text-light">Loot Needs</h1>
        <div class="bg-dark">
        <table data-toggle="table" class="table table-dark table-striped table-bordered" data-custom-sort="customSort" data-sticky-header="true">
            <caption>:)</caption>
            <thead class="thead-dark">
                <tr>
                    <th data-sortable="true" data-field="name">Name</th>
                    <th data-sortable="true" data-field="boss1">Wrathion</th>
                    <th data-sortable="true" data-field="boss2">Skitra</th>
                    <th data-sortable="true" data-field="boss3">Maut</th>
                    <th data-sortable="true" data-field="boss4">Xanesh</th>
                    <th data-sortable="true" data-field="boss5">Hivemind</th>
                    <th data-sortable="true" data-field="boss6">Shad'har</th>
                    <th data-sortable="true" data-field="boss7">Drest'agath</th>
                    <th data-sortable="true" data-field="boss8">Il'gynoth</th>
                    <th data-sortable="true" data-field="boss9">Vexiona</th>
                    <th data-sortable="true" data-field="boss10">Ra-den</th>
                    <th data-sortable="true" data-field="boss11">Carapace</th>
                    <th data-sortable="true" data-field="boss12">N'Zoth</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data as $row) {
                        echo "<tr id='". $row["name"] ."'>\n";
                        echo "<td><span class=\"text-light\"><img src='img/".$row["playerClass"].".png'> ".$row["name"]."</span></td>\n";
                        $sql = "SELECT bl.boss_id, min(response) as response FROM (SELECT boss_id FROM boss_loot GROUP BY boss_id ) as bl LEFT JOIN (SELECT raider_id, boss_id, response FROM raider_loot WHERE raider_id = ? AND response < ?) as rl ON bl.boss_id = rl.boss_id GROUP BY boss_id";
                        $stmt = $dbConn->prepare($sql);
                        $stmt->execute([$row["blizz_id"], LootResponse::DontNeed]);
                        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        $bossResponses = $stmt->fetchAll();
                        foreach($bossResponses as $r) {
                            $symbol = "✔️";
                            if (!is_null($r["response"])) {
                                if ($r["response"] == 1 || $r["response"] == 2)
                                    $symbol = "❌";
                                else
                                    $symbol = "⚠️";
                            }
                            echo "<td><a href='boss.php?blizzId=". $row["blizz_id"] ."&bossId=". $r["boss_id"] ."' data-toggle='lightbox' data-gallery='remoteload' data-disable-external-check='true' data-width='800'>". $symbol ."</a></td>\n";
                        }
                        echo "</tr>\n";
                    }
                ?>
            </tbody>
        </table>
        </div>
        <script>
            $(document).on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox();
            });

            $(document).ready(function() {
                $('.toast').toast();
            });

            function customSort(sortName, sortOrder, data) {
                var order = sortOrder === 'desc' ? -1 : 1
                data.sort(function (a, b) {
                    var aa = a[sortName];
                    var bb = b[sortName];
                    if (sortName === 'name') {
                        aa = $("<img>").html(aa).text();
                        bb = $("<img>").html(bb).text();
                    }
                    if (sortName.startsWith("boss")) {
                        aa = $("<a>").html(aa).text();
                        switch (aa) {
                            case '✔️':
                                aa = 3;
                                break;
                            case '⚠️':
                                aa = 2;
                                break;
                            case '❌':
                                aa = 1;
                                break;
                        }
                        bb = $("<a>").html(bb).text();
                        switch (bb) {
                            case '✔️':
                                bb = 3;
                                break;
                            case '⚠️':
                                bb = 2;
                                break;
                            case '❌':
                                bb = 1;
                                break;
                        }
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

            var closeBossWindow = function(name, bossNum, weight) {
                let symbol = "✔️";
                if (weight < 5 && weight > 2)
                {
                    symbol = "⚠️";
                }
                if (weight <= 2)
                {
                    symbol = "❌";
                }
                bossNum += 1;
                $("tr#" + name + " td:nth-child(" + bossNum + ") > a")[0].innerText = symbol;
                $(".ekko-lightbox").modal('hide');
            }

            var successMsg = function(msg) {
                $('.toast').toast('show')
            }
        </script>
    </body>
</html>