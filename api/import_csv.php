<?php
require_once(__DIR__ . "/../config/db.php");

function cleanNumber($value) {
    $value = trim($value);
    $value = str_replace(".", "", $value);
    $value = str_replace(",", ".", $value);
    $value = preg_replace('/[^0-9.]/', '', $value);

    if ($value === "") {
        return 0;
    }

    return floatval($value);
}

$file = fopen(__DIR__ . "/../data/somaj.csv", "r");

if (!$file) {
    die("Nu pot deschide fisierul CSV.");
}

$db->exec("DELETE FROM unemployment");

// sarim peste header
fgets($file);

$insert = $db->prepare("
    INSERT INTO unemployment (
        judet,
        luna,
        numar_total,
        numar_femei,
        numar_barbati,
        numar_indemnizati,
        numar_neindemnizati,
        rata,
        rata_feminina,
        rata_masculina,
        varsta,
        educatie,
        mediu
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', '', '')
");

while (($line = fgets($file)) !== false) {
    $data = explode(";", $line);

    if (count($data) < 10) {
        continue;
    }

    $judet = trim($data[0]);

    if ($judet === "" || strtolower($judet) === "total") {
        continue;
    }

    // momentan avem un singur CSV, deci luna ramane fixa
    // cand adaugam mai multe luni, o sa citim luna din numele fisierului
    $luna = "2025-05";

    $numarTotal = intval(cleanNumber($data[1]));
    $numarFemei = intval(cleanNumber($data[2]));
    $numarBarbati = intval(cleanNumber($data[3]));
    $numarIndemnizati = intval(cleanNumber($data[4]));
    $numarNeindemnizati = intval(cleanNumber($data[5]));

    $rata = cleanNumber($data[6]);
    $rataFeminina = cleanNumber($data[7]);
    $rataMasculina = cleanNumber($data[8]);

    $insert->execute([
        $judet,
        $luna,
        $numarTotal,
        $numarFemei,
        $numarBarbati,
        $numarIndemnizati,
        $numarNeindemnizati,
        $rata,
        $rataFeminina,
        $rataMasculina
    ]);
}

fclose($file);

echo "Import terminat.";
?>