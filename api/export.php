<?php
require_once(__DIR__ . "/../config/db.php");

$format    = isset($_GET["format"]) ? trim($_GET["format"]) : "json";
$judete    = isset($_GET["judete"]) ? $_GET["judete"] : [];
$lunaStart = isset($_GET["luna_start"]) ? trim($_GET["luna_start"]) : "";
$lunaEnd   = isset($_GET["luna_end"]) ? trim($_GET["luna_end"]) : "";

if (!is_array($judete)) {
    $judete = [];
}
$judete = array_filter(array_map('trim', $judete));

$sql    = "SELECT judet, luna, numar_total, numar_femei, numar_barbati, numar_indemnizati, numar_neindemnizati, rata, rata_feminina, rata_masculina FROM unemployment WHERE 1=1";
$params = [];

if (count($judete) > 0) {
    $placeholders = implode(",", array_fill(0, count($judete), "?"));
    $sql .= " AND judet IN ($placeholders)";
    foreach ($judete as $j) {
        $params[] = $j;
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

if ($format === "csv") {
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"somaj_export.csv\"");

    $out = fopen("php://output", "w");
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ["Judet", "Luna", "Total someri", "Femei", "Barbati", "Indemnizati", "Neindemnizati", "Rata (%)", "Rata feminina (%)", "Rata masculina (%)"], ";");

    foreach ($data as $row) {
        fputcsv($out, [
            $row["judet"],
            $row["luna"],
            $row["numar_total"],
            $row["numar_femei"],
            $row["numar_barbati"],
            $row["numar_indemnizati"],
            $row["numar_neindemnizati"],
            $row["rata"],
            $row["rata_feminina"],
            $row["rata_masculina"]
        ], ";");
    }

    fclose($out);
} else {
    header("Content-Type: application/json; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"somaj_export.json\"");
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
?>
