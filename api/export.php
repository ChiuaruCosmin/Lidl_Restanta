<?php
require_once(__DIR__ . "/../config/db.php");

$format    = isset($_GET["format"])    ? trim($_GET["format"])    : "json";
$tab       = isset($_GET["tab"])       ? trim($_GET["tab"])       : "rata";
$judete    = isset($_GET["judete"])    ? $_GET["judete"]          : [];
$lunaStart = isset($_GET["luna_start"]) ? trim($_GET["luna_start"]) : "";
$lunaEnd   = isset($_GET["luna_end"])  ? trim($_GET["luna_end"])  : "";

if (!is_array($judete)) {
    $judete = [];
}
$judete = array_filter(array_map('trim', $judete));

if ($tab === "medii") {
    $tabel   = "somaj_medii";
    $cols    = "judet, luna, urban_total, urban_femei, urban_barbati, rural_total, rural_femei, rural_barbati";
    $headers = ["Judet", "Luna", "Urban Total", "Urban Femei", "Urban Barbati", "Rural Total", "Rural Femei", "Rural Barbati"];
    $keys    = ["judet", "luna", "urban_total", "urban_femei", "urban_barbati", "rural_total", "rural_femei", "rural_barbati"];
} elseif ($tab === "varste") {
    $tabel   = "somaj_varste";
    $cols    = "judet, luna, sub_25, v_25_29, v_30_39, v_40_49, v_50_55, peste_55";
    $headers = ["Judet", "Luna", "Sub 25", "25-29", "30-39", "40-49", "50-55", "Peste 55"];
    $keys    = ["judet", "luna", "sub_25", "v_25_29", "v_30_39", "v_40_49", "v_50_55", "peste_55"];
} elseif ($tab === "educatie") {
    $tabel   = "somaj_educatie";
    $cols    = "judet, luna, fara_studii, primar, gimnazial, liceal, postliceal, profesional, universitar";
    $headers = ["Judet", "Luna", "Fara studii", "Primar", "Gimnazial", "Liceal", "Postliceal", "Profesional", "Universitar"];
    $keys    = ["judet", "luna", "fara_studii", "primar", "gimnazial", "liceal", "postliceal", "profesional", "universitar"];
} else {
    $tabel   = "unemployment";
    $cols    = "judet, luna, numar_total, numar_femei, numar_barbati, numar_indemnizati, numar_neindemnizati, rata, rata_feminina, rata_masculina";
    $headers = ["Judet", "Luna", "Total someri", "Femei", "Barbati", "Indemnizati", "Neindemnizati", "Rata (%)", "Rata feminina (%)", "Rata masculina (%)"];
    $keys    = ["judet", "luna", "numar_total", "numar_femei", "numar_barbati", "numar_indemnizati", "numar_neindemnizati", "rata", "rata_feminina", "rata_masculina"];
}

$sql    = "SELECT $cols FROM $tabel WHERE 1=1";
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
    header("Content-Disposition: attachment; filename=\"somaj_" . $tab . "_export.csv\"");

    $out = fopen("php://output", "w");
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, $headers, ";");

    foreach ($data as $row) {
        $linie = [];
        foreach ($keys as $k) {
            $linie[] = $row[$k];
        }
        fputcsv($out, $linie, ";");
    }

    fclose($out);
} else {
    header("Content-Type: application/json; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"somaj_" . $tab . "_export.json\"");
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
?>
