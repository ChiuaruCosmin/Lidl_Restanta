<?php
require_once("../config/db.php");

$db->exec("
CREATE TABLE IF NOT EXISTS unemployment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    judet TEXT,
    luna TEXT,
    rata REAL,
    varsta TEXT,
    educatie TEXT,
    mediu TEXT
)
");

echo "Tabela creata!";