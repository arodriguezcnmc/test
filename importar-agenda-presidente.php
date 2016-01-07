<?php
set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include('librerias/db_sqlsrv.php');
include('librerias/funciones.php');

$db = new db_sqlsrv;
$db2 = new db_sqlsrv;

// Id del usuario que publica los artículos
$uid = 1;
// Nombre de la tabla en SQLServer
$tabla = 'EasyDNNNews';
// Id de la categoría padre
$CageforyID = 17;
// Nombre entidad de Drupal
$entidad = 'agenda_presidente';

// Equivalencia de las opciones de la vieja web a la taxonomía de Drupal (tabla, id)
$taxonomia = array();
$taxonomia['categoria'] = array(15 => 2, 11 => 3, 12 => 4, 16 => 7, 13 => 1, 14 => 6);

// Equivalencia de los campos de la vieja web con el tipo de datos de Drupal (nombre, tipo)
$eq_campos = array('Title' => array('title', ''), 'SubTitle' => array('field_subtituloa', ''), 'Article' => array('body', 'basic_html'), 'PublishDate' => array('field_fecha_de_publicaciona', 'datetime'));

// Obtenemos todos los registros
$registros = array();
$total_registros = 0;
$db->query("SELECT TOP 1 * FROM $tabla WHERE ArticleID IN (SELECT ArticleID FROM EasyDNNNewsCategories AS c INNER JOIN EasyDNNNewsCategoryList AS l ON c.CategoryID = l.CategoryID 	WHERE ParentCategory = $CageforyID OR c.CategoryID = $CageforyID) ORDER BY ArticleID");
while ($db->next_record()) {
  $registros[$total_registros] = array();
  foreach($eq_campos as $campo_bbdd => $campo_drupal) {
    // Formateamos según el tipo de datos
    if ($campo_drupal[1] == 'datetime') {
      $valor = substr($db->record[$campo_bbdd]->format('Y-m-d H:i:s'), 0, 10);  
    } else if ($campo_drupal[1] == 'boolean') {
      $valor = $valor == 'True' ? 1 : 0;
    } else {
      // Limpiamos todos los css, ids, class, etc. que pudiera haber en las etiquetas
      $valor = preg_replace('/[ ]+[id|name|style|class|jquery{0-9}*]+="[^"]*"/i', '', html_entity_decode($db->record[$campo_bbdd]));
    }
    $registros[$total_registros][$campo_bbdd][] = $valor;
  }
  $total_registros++;
}

// Importamos a Drupal los registros en orden inverso al que se insertaron en SQLServer
for($total_registros = count($registros) - 1; $total_registros >= 0; $total_registros = $total_registros - 1) {
  $registro = $registros[$total_registros];
  // Creamos un array, para mandar los datos con hal json
  $json = array();
  foreach($registro as $campos => $campo) {
    foreach($campo as $valores => $valor) {
      // Creamos un elemento para añadir al array
      $elem = array('value' => utf8_encode($valor));
      // Si el tipo de campo es basic_html
      if ($eq_campos[$campos][1] == 'basic_html') {
        $elem['format'] = 'basic_html';
      }	  
      // Añadimos el dato al array
      $json[$eq_campos[$campos][0]] = array($elem);
    }
  }
  // Metemos esta instrucción para indicar el autor del post (root)
  $elem = array('target_id' => $uid);
  $json['uid'] = array($elem);
  // Creamos el hal json
  $json = array_merge(array("_links" => array("type" => array("href" => "http://localhost:3880/drupal/rest/type/node/$entidad"))), $json);
  //print_r($json);
  $json = json_encode($json);
  //print_r($json);
  
  // Hacemos el POST a la API de Drupal
  insert_drupal($entidad, $json);
} 

echo count($registros) . ' registros importados';
?>