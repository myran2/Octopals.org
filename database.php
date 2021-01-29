<?php
require "config.php";
try {
    $dbConn = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPwd);
    // set the PDO error mode to exception
    $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully";
}

catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>