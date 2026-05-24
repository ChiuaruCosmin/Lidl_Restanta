<?php
require_once(__DIR__ . "/../config/db.php");

header('Content-Type: application/json; charset=utf-8');

$judete = isset($_GET["judete"]) ? $_GET["judete"] : [];
$lunaStart = isset($_GET["luna_start"]) ? trim($_GET["luna_start"]) : "";
$lunaEnd = isset($_GET["luna_end"]) ? trim($_GET["luna_end"]) : "";

if (!is_array($judete)) {
    $judete = [];
}

$judete = array_map('trim', $judete);
$judete = array_filter($judete);

$sql = "SELECT * FROM unemployment WHERE 1 = 1";
$params = [];

if (count($judete) > 0) {
    $placeholders = implode(",", array_fill(0, count($judete), "?"));
    $sql .= " AND judet IN ($placeholders)";

    foreach ($judete as $judet) {
        $params[] = $judet;
    }
}

if ($lunaStart !== "") {
    $sql .= " AND luna >= ?";
    $params[] = $lunaStart;
}

if ($lunaEnd !== "") {
    $sql .= " AND luna <= ?";
    $params[] = $lunaEnd;
}

$sql .= " ORDER BY luna ASC, judet ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>