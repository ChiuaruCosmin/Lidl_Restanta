<?php
require_once(__DIR__ . "/../config/db.php");

$db->exec("
CREATE TABLE IF NOT EXISTS unemployment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    judet TEXT NOT NULL,
    luna TEXT NOT NULL,

    numar_total INTEGER DEFAULT 0,
    numar_femei INTEGER DEFAULT 0,
    numar_barbati INTEGER DEFAULT 0,
    numar_indemnizati INTEGER DEFAULT 0,
    numar_neindemnizati INTEGER DEFAULT 0,

    rata REAL DEFAULT 0,
    rata_feminina REAL DEFAULT 0,
    rata_masculina REAL DEFAULT 0,

    varsta TEXT DEFAULT '',
    educatie TEXT DEFAULT '',
    mediu TEXT DEFAULT ''
)
");

echo "Tabela unemployment a fost creata sau exista deja.";
?>