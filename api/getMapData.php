<?php
require_once(__DIR__ . "/../config/db.php");

header('Content-Type: application/json; charset=utf-8');

$stmt = $db->query("SELECT MAX(luna) as max_luna FROM unemployment");
$row  = $stmt->fetch(PDO::FETCH_ASSOC);
$latestLuna = $row["max_luna"];

$stmt = $db->prepare("SELECT judet, rata FROM unemployment WHERE luna = ? ORDER BY judet ASC");
$stmt->execute([$latestLuna]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($rows as $r) {
    $result[$r["judet"]] = floatval($r["rata"]);
}

echo json_encode(["luna" => $latestLuna, "data" => $result], JSON_UNESCAPED_UNICODE);
?>
