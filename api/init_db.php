<?php

require_once(__DIR__ . "/../config/db.php");

$db->exec("DROP TABLE IF EXISTS unemployment");
$db->exec("DROP TABLE IF EXISTS somaj_medii");
$db->exec("DROP TABLE IF EXISTS somaj_varste");
$db->exec("DROP TABLE IF EXISTS somaj_educatie");

$db->exec("
    CREATE TABLE unemployment (
        id                  INTEGER PRIMARY KEY AUTOINCREMENT,
        judet               TEXT NOT NULL,
        luna                TEXT NOT NULL,
        numar_total         INTEGER DEFAULT 0,
        numar_femei         INTEGER DEFAULT 0,
        numar_barbati       INTEGER DEFAULT 0,
        numar_indemnizati   INTEGER DEFAULT 0,
        numar_neindemnizati INTEGER DEFAULT 0,
        rata                REAL DEFAULT 0,
        rata_feminina       REAL DEFAULT 0,
        rata_masculina      REAL DEFAULT 0,
        UNIQUE(judet, luna)
    )
");

$db->exec("
    CREATE TABLE somaj_medii (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        judet           TEXT NOT NULL,
        luna            TEXT NOT NULL,
        urban_total     INTEGER DEFAULT 0,
        urban_femei     INTEGER DEFAULT 0,
        urban_barbati   INTEGER DEFAULT 0,
        rural_total     INTEGER DEFAULT 0,
        rural_femei     INTEGER DEFAULT 0,
        rural_barbati   INTEGER DEFAULT 0,
        UNIQUE(judet, luna)
    )
");

$db->exec("
    CREATE TABLE somaj_varste (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        judet       TEXT NOT NULL,
        luna        TEXT NOT NULL,
        sub_25      INTEGER DEFAULT 0,
        v_25_29     INTEGER DEFAULT 0,
        v_30_39     INTEGER DEFAULT 0,
        v_40_49     INTEGER DEFAULT 0,
        v_50_55     INTEGER DEFAULT 0,
        peste_55    INTEGER DEFAULT 0,
        UNIQUE(judet, luna)
    )
");

$db->exec("
    CREATE TABLE somaj_educatie (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        judet           TEXT NOT NULL,
        luna            TEXT NOT NULL,
        fara_studii     INTEGER DEFAULT 0,
        primar          INTEGER DEFAULT 0,
        gimnazial       INTEGER DEFAULT 0,
        liceal          INTEGER DEFAULT 0,
        postliceal      INTEGER DEFAULT 0,
        profesional     INTEGER DEFAULT 0,
        universitar     INTEGER DEFAULT 0,
        UNIQUE(judet, luna)
    )
");

echo "Tabelele au fost create cu succes.";
?>
