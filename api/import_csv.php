<?php
require_once("../config/db.php");

$file = fopen("../data/somaj.csv", "r");

if (!$file) {
    die("Nu pot deschide fisierul!");
}

$db->exec("DELETE FROM unemployment");

fgets($file);

while (($line = fgets($file)) !== false) {

    $data = explode(";", $line);

    // verificare linie valida
    if (count($data) < 7) continue;

    $judet = trim($data[0]);

    // ignoram total si linii goale
    if ($judet == "" || $judet == "Total") continue;

    $rata = trim($data[6]);

    // transformam 3,60 -> 3.60
    $rata = str_replace(",", ".", $rata);
    $rata = floatval($rata);

    // luna fixa (din titlu dataset)
    $luna = "2025-05";

    $stmt = $db->prepare("INSERT INTO unemployment (judet, luna, rata, varsta, educatie, mediu)
                          VALUES (?, ?, ?, '', '', '')");

    $stmt->execute([$judet, $luna, $rata]);
}

fclose($file);

echo "Import terminat!";
?>