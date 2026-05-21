<?php
require_once(__DIR__ . "/../config/db.php");

function cleanInteger($value) {
    $value = trim($value);
    $value = str_replace([".", ",", " "], "", $value);
    $value = preg_replace('/[^0-9]/', '', $value);

    if ($value === "") {
        return 0;
    }

    return intval($value);
}

function cleanRate($value) {
    $value = trim($value);
    $value = str_replace(" ", "", $value);

    if (strpos($value, ",") !== false && strpos($value, ".") === false) {
        $value = str_replace(",", ".", $value);
    }

    $value = preg_replace('/[^0-9.]/', '', $value);

    if ($value === "") {
        return 0;
    }

    return floatval($value);
}

function getMonthFromFileName($filePath) {
    $fileName = basename($filePath);

    if (preg_match('/somaj_(\d{4})_(\d{2})\.csv/', $fileName, $matches)) {
        return $matches[1] . "-" . $matches[2];
    }

    return null;
}

function detectDelimiter($filePath) {
    $firstLine = fgets(fopen($filePath, "r"));

    $semicolonCount = substr_count($firstLine, ";");
    $commaCount = substr_count($firstLine, ",");

    if ($semicolonCount >= $commaCount) {
        return ";";
    }

    return ",";
}

$csvFiles = glob(__DIR__ . "/../data/somaj_*.csv");

if (!$csvFiles || count($csvFiles) === 0) {
    die("Nu exista fisiere CSV in folderul data.\n");
}

sort($csvFiles);

$db->exec("DELETE FROM unemployment");

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

$totalImported = 0;

foreach ($csvFiles as $csvFile) {
    $luna = getMonthFromFileName($csvFile);

    if ($luna === null) {
        echo "Fisier ignorat: " . basename($csvFile) . "\n";
        continue;
    }

    $delimiter = detectDelimiter($csvFile);

    $file = fopen($csvFile, "r");

    if (!$file) {
        echo "Nu pot deschide fisierul: " . basename($csvFile) . "\n";
        continue;
    }

    fgetcsv($file, 0, $delimiter);

    $rowsForFile = 0;

    while (($data = fgetcsv($file, 0, $delimiter)) !== false) {
        if (count($data) < 9) {
            continue;
        }

        $judet = trim($data[0]);
        $judet = preg_replace('/^\xEF\xBB\xBF/', '', $judet);

        if ($judet === "" || strtolower($judet) === "total") {
            continue;
        }

        $numarTotal = cleanInteger($data[1]);
        $numarFemei = cleanInteger($data[2]);
        $numarBarbati = cleanInteger($data[3]);
        $numarIndemnizati = cleanInteger($data[4]);
        $numarNeindemnizati = cleanInteger($data[5]);

        $rata = cleanRate($data[6]);
        $rataFeminina = cleanRate($data[7]);
        $rataMasculina = cleanRate($data[8]);

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

        $rowsForFile++;
        $totalImported++;
    }

    fclose($file);

    echo "Importat " . basename($csvFile) . " - randuri: " . $rowsForFile . "\n";
}

echo "Import terminat. Total randuri importate: " . $totalImported . "\n";
?>