<?php
require "database.php";
require "gameData.php";

$blizzId = filter_input(INPUT_POST, 'blizz_id', FILTER_SANITIZE_NUMBER_INT);
$bossId = filter_input(INPUT_POST, 'boss_id', FILTER_SANITIZE_NUMBER_INT);

foreach($_POST as $key => $value)
{
    if (strstr($key, 'item'))
    {
        $x = str_replace('item','',$key);
        inserttag($value, $x);
    }

    if (!preg_match("/response/", $key)) {
        continue;
    }
    $item_id = str_replace("response", "", $key);
    $response = 0;
    switch ($value) {
        case "major":
            $response = LootResponse::Major;
            break;
        case "minor":
            $response = LootResponse::Minor;
            break;
        case "offspec":
            $response = LootResponse::Offspec;
            break;
        case "dont-need":
            $response = LootResponse::DontNeed;
            break;
    }

    try {
        $sql = "REPLACE INTO raider_loot (raider_id, encounter_id, item_id, response) VALUES (?, ?, ?, ?)";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute([$blizzId, $bossId, $item_id, $response]);
    }
    catch (PDOException $e) {
        echo $e->getMessage() . "\n";
    }
}
echo ":)\n";
?>