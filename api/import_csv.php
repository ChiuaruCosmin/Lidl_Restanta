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

function getLunaFromFileName($filePath) {
    $fileName = basename($filePath);
    if (preg_match('/somaj_(\d{4})_(\d{2})_/', $fileName, $matches)) {
        return $matches[1] . "-" . $matches[2];
    }
    return null;
}

function detectDelimiter($filePath) {
    $handle = fopen($filePath, "r");
    $firstLine = fgets($handle);
    fclose($handle);
    $semicolonCount = substr_count($firstLine, ";");
    $commaCount     = substr_count($firstLine, ",");
    return ($semicolonCount >= $commaCount) ? ";" : ",";
}

function cleanJudet($value) {
    $value = trim($value);
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);

    $diacritice = [
        'ă' => 'a', 'â' => 'a', 'î' => 'i',
        'ș' => 's', 'ş' => 's', 'Ș' => 'S', 'Ş' => 'S',
        'ț' => 't', 'ţ' => 't', 'Ț' => 'T', 'Ţ' => 'T',
        'Ă' => 'A', 'Â' => 'A', 'Î' => 'I',
        'Š' => 'S', 'š' => 's',
    ];
    $value = strtr($value, $diacritice);

    $value = str_replace('?', 'S', $value);

    $value = preg_replace('/[^\x00-\x7F]/', '', $value);

    $value = strtoupper($value);

    if (strpos($value, 'BUCURESTI') !== false || strpos($value, 'BUC.') !== false) {
        return 'MUNICIPIUL BUCURESTI';
    }

    $normalizari = [
        'BISTRITA-NASAUD' => 'BISTRITA NASAUD',
        'BISTRITA'        => 'BISTRITA NASAUD',
        'CARA-SEVERIN'    => 'CARAS-SEVERIN',
        'CARAS SEVERIN'   => 'CARAS-SEVERIN',
        'CARAS'           => 'CARAS-SEVERIN',
        'SATU-MARE'       => 'SATU MARE',
        'SATU M.'         => 'SATU MARE',
    ];

    if (isset($normalizari[$value])) {
        $value = $normalizari[$value];
    }

    return $value;
}

function importRata($db, $files) {
    $db->exec("DELETE FROM unemployment");

    $stmt = $db->prepare("
        INSERT OR REPLACE INTO unemployment (
            judet, luna,
            numar_total, numar_femei, numar_barbati,
            numar_indemnizati, numar_neindemnizati,
            rata, rata_feminina, rata_masculina
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $total = 0;

    foreach ($files as $file) {
        $luna = getLunaFromFileName($file);
        if ($luna === null) {
            echo "Ignorat (luna negasita): " . basename($file) . "\n";
            continue;
        }

        $delim  = detectDelimiter($file);
        $handle = fopen($file, "r");
        if (!$handle) {
            echo "Eroare la deschidere: " . basename($file) . "\n";
            continue;
        }

        fgetcsv($handle, 0, $delim);

        $count = 0;
        while (($row = fgetcsv($handle, 0, $delim)) !== false) {
            if (count($row) < 9) {
                continue;
            }
            $judet = cleanJudet($row[0]);
            if ($judet === "" || strpos($judet, 'TOTAL') === 0) {
                continue;
            }

            $stmt->execute([
                $judet, $luna,
                cleanInteger($row[1]),
                cleanInteger($row[2]),
                cleanInteger($row[3]),
                cleanInteger($row[4]),
                cleanInteger($row[5]),
                cleanRate($row[6]),
                cleanRate($row[7]),
                cleanRate($row[8])
            ]);

            $count++;
            $total++;
        }

        fclose($handle);
        echo "Importat " . basename($file) . " - " . $count . " randuri\n";
    }

    return $total;
}

function importMedii($db, $files) {
    $db->exec("DELETE FROM somaj_medii");

    $stmt = $db->prepare("
        INSERT OR REPLACE INTO somaj_medii (
            judet, luna,
            urban_total, urban_femei, urban_barbati,
            rural_total, rural_femei, rural_barbati
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $total = 0;

    foreach ($files as $file) {
        $luna = getLunaFromFileName($file);
        if ($luna === null) {
            echo "Ignorat (luna negasita): " . basename($file) . "\n";
            continue;
        }

        $delim  = detectDelimiter($file);
        $handle = fopen($file, "r");
        if (!$handle) {
            echo "Eroare la deschidere: " . basename($file) . "\n";
            continue;
        }

        fgetcsv($handle, 0, $delim);

        $count = 0;
        while (($row = fgetcsv($handle, 0, $delim)) !== false) {
            if (count($row) < 10) {
                continue;
            }
            $judet = cleanJudet($row[0]);
            if ($judet === "" || strpos($judet, 'TOTAL') === 0) {
                continue;
            }

            $stmt->execute([
                $judet, $luna,
                cleanInteger($row[4]),
                cleanInteger($row[5]),
                cleanInteger($row[6]),
                cleanInteger($row[7]),
                cleanInteger($row[8]),
                cleanInteger($row[9])
            ]);

            $count++;
            $total++;
        }

        fclose($handle);
        echo "Importat " . basename($file) . " - " . $count . " randuri\n";
    }

    return $total;
}

/* -----------------------------------------------------------------------
 * Import fisiere _varste -> tabela somaj_varste
 * Coloane: judet, total, sub25, 25-29, 30-39, 40-49, 50-55, peste55
 * -------------------------------------------------------------------- */
function importVarste($db, $files) {
    $db->exec("DELETE FROM somaj_varste");

    $stmt = $db->prepare("
        INSERT OR REPLACE INTO somaj_varste (
            judet, luna,
            sub_25, v_25_29, v_30_39, v_40_49, v_50_55, peste_55
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $total = 0;

    foreach ($files as $file) {
        $luna = getLunaFromFileName($file);
        if ($luna === null) {
            echo "Ignorat (luna negasita): " . basename($file) . "\n";
            continue;
        }

        $delim  = detectDelimiter($file);
        $handle = fopen($file, "r");
        if (!$handle) {
            echo "Eroare la deschidere: " . basename($file) . "\n";
            continue;
        }

        fgetcsv($handle, 0, $delim);

        $count = 0;
        while (($row = fgetcsv($handle, 0, $delim)) !== false) {
            if (count($row) < 8) {
                continue;
            }
            $judet = cleanJudet($row[0]);
            if ($judet === "" || strpos($judet, 'TOTAL') === 0) {
                continue;
            }

            $stmt->execute([
                $judet, $luna,
                cleanInteger($row[2]),
                cleanInteger($row[3]),
                cleanInteger($row[4]),
                cleanInteger($row[5]),
                cleanInteger($row[6]),
                cleanInteger($row[7])
            ]);

            $count++;
            $total++;
        }

        fclose($handle);
        echo "Importat " . basename($file) . " - " . $count . " randuri\n";
    }

    return $total;
}

/* -----------------------------------------------------------------------
 * Import fisiere _nivel-educatie -> tabela somaj_educatie
 * Coloane: judet, total, fara_studii, primar, gimnazial,
 *          liceal, postliceal, profesional, universitar
 * -------------------------------------------------------------------- */
function importEducatie($db, $files) {
    $db->exec("DELETE FROM somaj_educatie");

    $stmt = $db->prepare("
        INSERT OR REPLACE INTO somaj_educatie (
            judet, luna,
            fara_studii, primar, gimnazial,
            liceal, postliceal, profesional, universitar
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $total = 0;

    foreach ($files as $file) {
        $luna = getLunaFromFileName($file);
        if ($luna === null) {
            echo "Ignorat (luna negasita): " . basename($file) . "\n";
            continue;
        }

        $delim  = detectDelimiter($file);
        $handle = fopen($file, "r");
        if (!$handle) {
            echo "Eroare la deschidere: " . basename($file) . "\n";
            continue;
        }

        fgetcsv($handle, 0, $delim);

        $count = 0;
        while (($row = fgetcsv($handle, 0, $delim)) !== false) {
            if (count($row) < 9) {
                continue;
            }
            $judet = cleanJudet($row[0]);
            if ($judet === "" || strpos($judet, 'TOTAL') === 0) {
                continue;
            }

            $stmt->execute([
                $judet, $luna,
                cleanInteger($row[2]),
                cleanInteger($row[3]),
                cleanInteger($row[4]),
                cleanInteger($row[5]),
                cleanInteger($row[6]),
                cleanInteger($row[7]),
                cleanInteger($row[8])
            ]);

            $count++;
            $total++;
        }

        fclose($handle);
        echo "Importat " . basename($file) . " - " . $count . " randuri\n";
    }

    return $total;
}

$dataDir = __DIR__ . "/../data/";

$rataFiles      = glob($dataDir . "somaj_*_rata.csv");
$mediiFiles     = glob($dataDir . "somaj_*_medii.csv");
$varsteFiles    = glob($dataDir . "somaj_*_varste.csv");
$educatieFiles  = glob($dataDir . "somaj_*_nivel-educatie.csv");

sort($rataFiles);
sort($mediiFiles);
sort($varsteFiles);
sort($educatieFiles);

echo "=== Import rata (" . count($rataFiles) . " fisiere) ===\n";
$n = importRata($db, $rataFiles);
echo "Total: $n randuri\n\n";

echo "=== Import medii (" . count($mediiFiles) . " fisiere) ===\n";
$n = importMedii($db, $mediiFiles);
echo "Total: $n randuri\n\n";

echo "=== Import varste (" . count($varsteFiles) . " fisiere) ===\n";
$n = importVarste($db, $varsteFiles);
echo "Total: $n randuri\n\n";

echo "=== Import educatie (" . count($educatieFiles) . " fisiere) ===\n";
$n = importEducatie($db, $educatieFiles);
echo "Total: $n randuri\n\n";

echo "Import complet!\n";
?>
