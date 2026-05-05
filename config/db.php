<?php
$db = new PDO("sqlite:../database/und.sqlite");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>