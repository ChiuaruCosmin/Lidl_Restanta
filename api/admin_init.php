<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Neautorizat']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

ob_start();
include __DIR__ . "/init_db.php";
echo "\n\n";
include __DIR__ . "/import_csv.php";
$output = ob_get_clean();

echo json_encode(['output' => $output]);
