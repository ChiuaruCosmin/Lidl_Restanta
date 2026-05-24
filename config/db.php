<?php
$databasePath = __DIR__ . "/../database/und.sqlite";

$db = new PDO("sqlite:" . $databasePath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>