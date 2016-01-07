<?php
// PHP para eliminar todos los enlaces del texto y almacenarlos en un array
/*set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include('librerias/db_sqlsrv.php');

$db = new db_sqlsrv;
$db2 = new db_sqlsrv;

$db->query("SELECT * FROM EasyDNNNews WHERE ArticleID = '1441'");
$db->next_record();
$texto = html_entity_decode($db->record['Article']);

// Guardamos todos los enlaces en un array
preg_match_all("/\<a([^>]*)\>([^<]*)\<\/a\>/i", $texto, $enlaces);
// Limpiamos los enlaces
$texto = preg_replace("/\<a([^>]*)\>([^<]*)\<\/a\>/i", "$2", $texto);
// Quitamos los últimos enlaces si están solos
foreach($enlaces[2] as $enlace) {
  $enlace = str_ireplace('/', '\/', $enlace);
  //$texto = preg_replace("/\<p([^>]*)\>(" . $enlace . ")\<\/p\>/i", "", $texto);
}

echo $texto;
echo "<pre>";
print_r($enlaces);
echo "</pre>";
*/

$texto = '<p   jquery1372850096533="1">Textoooo</p>';
$texto = preg_replace('/[ ]+[style|class|jquery{0-9}*]+="(.*)"/i', '', $texto);
echo $texto;
?>