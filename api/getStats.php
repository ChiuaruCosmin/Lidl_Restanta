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

$where = " WHERE 1 = 1 ";
$params = [];

if (count($judete) > 0) {
    $placeholders = implode(",", array_fill(0, count($judete), "?"));
    $where .= " AND judet IN ($placeholders) ";

    foreach ($judete as $judet) {
        $params[] = $judet;
    }
}

if ($lunaStart !== "") {
    $where .= " AND luna >= ? ";
    $params[] = $lunaStart;
}

if ($lunaEnd !== "") {
    $where .= " AND luna <= ? ";
    $params[] = $lunaEnd;
}

$sql = "
    SELECT
        COUNT(*) AS total_randuri,
        SUM(numar_total) AS total_someri,
        AVG(rata) AS rata_medie
    FROM unemployment
" . $where;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$generalStats = $stmt->fetch(PDO::FETCH_ASSOC);

$sqlMax = "
    SELECT judet, luna, rata
    FROM unemployment
" . $where . "
    ORDER BY rata DESC
    LIMIT 1
";

$stmtMax = $db->prepare($sqlMax);
$stmtMax->execute($params);
$maxRow = $stmtMax->fetch(PDO::FETCH_ASSOC);

$sqlMin = "
    SELECT judet, luna, rata
    FROM unemployment
" . $where . "
    ORDER BY rata ASC
    LIMIT 1
";

$stmtMin = $db->prepare($sqlMin);
$stmtMin->execute($params);
$minRow = $stmtMin->fetch(PDO::FETCH_ASSOC);

$response = [
    "total_randuri" => intval($generalStats["total_randuri"] ?? 0),
    "total_someri" => intval($generalStats["total_someri"] ?? 0),
    "rata_medie" => round(floatval($generalStats["rata_medie"] ?? 0), 2),

    "max_judet" => $maxRow ? $maxRow["judet"] : "-",
    "max_luna" => $maxRow ? $maxRow["luna"] : "-",
    "max_rata" => $maxRow ? floatval($maxRow["rata"]) : 0,

    "min_judet" => $minRow ? $minRow["judet"] : "-",
    "min_luna" => $minRow ? $minRow["luna"] : "-",
    "min_rata" => $minRow ? floatval($minRow["rata"]) : 0
];

echo json_encode($response);
?>