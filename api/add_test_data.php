<?php
require_once("../config/db.php");

$db->exec("DELETE FROM unemployment");

$sql = "INSERT INTO unemployment (judet, luna, rata, varsta, educatie, mediu)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $db->prepare($sql);

$stmt->execute(["Iasi", "2025-01", 3.2, "25-34", "superior", "urban"]);
$stmt->execute(["Iasi", "2025-02", 3.4, "25-34", "superior", "urban"]);
$stmt->execute(["Bucuresti", "2025-01", 1.1, "25-34", "superior", "urban"]);
$stmt->execute(["Cluj", "2025-01", 2.0, "35-44", "mediu", "urban"]);
$stmt->execute(["Vaslui", "2025-01", 7.5, "45-54", "gimnazial", "rural"]);

echo "Date de test adaugate!";
?>