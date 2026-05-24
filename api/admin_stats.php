<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Neautorizat']);
    exit;
}

require_once(__DIR__ . "/../config/db.php");

header('Content-Type: application/json; charset=utf-8');

$tabele = [
    'unemployment'   => 'Rata somajului',
    'somaj_medii'    => 'Urban / Rural',
    'somaj_varste'   => 'Grupe de varsta',
    'somaj_educatie' => 'Nivel educatie'
];

$result = [];
foreach ($tabele as $tabel => $label) {
    $stmt = $db->query("SELECT COUNT(*) as nr, MIN(luna) as min_luna, MAX(luna) as max_luna FROM $tabel");
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    $result[] = [
        'tabel'      => $tabel,
        'label'      => $label,
        'nr_randuri' => (int)$row['nr'],
        'luna_start' => $row['min_luna'],
        'luna_end'   => $row['max_luna']
    ];
}

echo json_encode($result);
