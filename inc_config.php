<?php
set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include('librerias/db_sqlsrv.php');
include('librerias/funciones.php');
$db = new db_sqlsrv;
$db2 = new db_sqlsrv;
?>