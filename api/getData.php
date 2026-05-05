<?php
require_once("../config/db.php");

$stmt = $db->query("SELECT * FROM unemployment LIMIT 50");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);