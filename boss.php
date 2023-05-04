<?php
require "database.php";
include "constants.php";

$blizzId = filter_input(INPUT_GET, 'blizzId', FILTER_SANITIZE_NUMBER_INT);
$bossId = filter_input(INPUT_GET, 'bossId', FILTER_SANITIZE_NUMBER_INT);
if ($bossId == false || $bossId == NULL) {
    die ("No boss Id specified.");
}

$sql = "SELECT `name`, `order` FROM encounter WHERE encounter_id = ?";
$stmt = $dbConn->prepare($sql);
$stmt->execute([$bossId]);
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetch();
if ($data == false)
    die("Invalid boss Id.");
$bossName = $data["name"];
$bossOrder = $data["order"];

$sql = "SELECT `name`, playerClass, playerRoles FROM raider WHERE blizz_id = ?";
$stmt = $dbConn->prepare($sql);
$stmt->execute([$blizzId]);
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetch();
if ($data == false) {
    die("Blizz Id ". $blizzId ." not found");
}
$playerName = $data["name"];
$classMask = $classIdToClassMask[$data["playerClass"]];

// previous loot needs
$sql = "SELECT bl.item_id, bonus_id, raider_id, response FROM (SELECT * from encounter_loot WHERE encounter_id = ? AND (class_mask & ? > 0)) AS bl left JOIN (SELECT * from raider_loot where raider_id = ?) as rl on rl.item_id = bl.item_id";
$stmt = $dbConn->prepare($sql);
$params = [$bossId, $classMask, $blizzId];
$stmt->execute($params);
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$data = $stmt->fetchAll();

//echo interpolateQuery($sql, $params);

?>
<html lang="en">
    <head>
        <title><?php echo $playerName . " - ". $bossName; ?></title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.5.0.min.js" integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script>var whTooltips = {colorLinks: true, iconizeLinks: true, renameLinks: true, iconSize: 'medium'};</script>
        <script src="https://wow.zamimg.com/widgets/power.js"></script>
        <script type="text/javascript" src="/js/bossForm.js"></script>
    </head>

    <body class="bg-dark">
        <div class="container">
            <h1 class="text-light"><?php echo $playerName . "'s loot needs for ". $bossName; ?></h1>
            <form id="lootNeeds" method="post">
                <div class="row">
                    <div class="col">
                        <ul class="list-group">
                            <?php
                            foreach($data as $row) {
                                $bonus_id = "";
                                if ($row["bonus_id"] != '') {
                                    $bonus_id = $row["bonus_id"];
                                }
                                $selected = array("", "", "", "", "", "selected");
                                if ($row["response"]) {
                                    $selected[$row["response"]] = "selected";
                                    $selected[LootResponse::DontNeed] = "";
                                }
                                
                            ?>
                            <li class="list-group-item list-group-item-dark">
                                <div class="row">
                                    <div class="col-sm">
                                        <a href="#" data-wowhead="item=<?php echo $row["item_id"]; ?>&bonus=<?php echo MythicItemBonusIds::VaultOfTheIncarnates; ?>">
                                        </a>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                        <select class="form-control position-static text-light bg-dark" name = "<?php echo $row["item_id"]; ?>response" id="<?php echo $row["item_id"]; ?>response">
                                            <option <?php echo $selected[LootResponse::DontNeed] ?> value="dont-need">Don't Need</option>
                                            <option <?php echo $selected[LootResponse::Major]; ?> value="major">Major Upgrade</option>
                                            <option <?php echo $selected[LootResponse::Minor]; ?> value="minor">Minor Upgrade</option>
                                            <option <?php echo $selected[LootResponse::Offspec]; ?> value="offspec">Offspec/M+/PvP/Transmog</option>
                                        </select>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="row" style="margin-top: 20px">
                    <div class="col">
                        <div class="float-right">
                            <input type="submit" class="btn btn-success" value="Save">
                        </div>
                    </div>
                </div>
                <input type="hidden" id="blizz_id" name="blizz_id" value="<?php echo $blizzId; ?>">
                <input type="hidden" id="boss_id" name="boss_id" value="<?php echo $bossId; ?>">
            </form>
        </div>
        <script>
            $(document).ready(function(){	
                $("#lootNeeds").submit(function(event){
                    submitForm();
                    let weight = 5;
                    $("option:selected").each(function() {
                        let curWeight = <?php echo LootResponse::DontNeed; ?>;
                        switch($(this).val()) {
                            case 'major':
                                curWeight = <?php echo LootResponse::Major; ?>;
                                break;
                            case 'minor':
                                curWeight = <?php echo LootResponse::Minor; ?>;
                                break;
                            case 'offspec':
                                curWeight = <?php echo LootResponse::Offspec; ?>;
                                break;
                        }
                        if (curWeight < weight)
                            weight = curWeight;
                    });
                    window.parent.closeBossWindow("<?php echo $playerName; ?>", <?php echo $bossOrder; ?>, weight);
                    return false;
                });
            });
        </script>
    </body>
</html>