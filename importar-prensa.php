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
$CageforyID = 10;
// Nombre entidad de Drupal
$entidad = 'nota_de_prensa';

// Equivalencia de las opciones de la vieja web a la taxonomía de Drupal - valor categoría sqlsrv => id taxonomía drupal
$taxonomia = array();
$taxonomia['categoria'] = array(15 => 2, 11 => 3, 12 => 4, 16 => 7, 13 => 1, 14 => 6);

// Equivalencia de los campos de la vieja web con el tipo de datos de Drupal - campo sqlsrv => (nombre, tipo)
$eq_campos = array('Title' => array('title', ''), 'SubTitle' => array('field_subtitulo', ''), 'Article' => array('body', 'basic_html'), 'Summary' => array('field_resumen', 'summary'), 'PublishDate' => array('field_fecha_de_publicacion', 'datetime'), 'ExpireDate' => array('field_fecha_de_expiracion', 'datetime'),  'categoria' => array('field_categoriap', 'taxonomy'));

// Obtenemos todos los registros
$registros = array();
$total_registros = 0;
$db->query("SELECT * FROM $tabla WHERE ArticleID IN (SELECT ArticleID FROM EasyDNNNewsCategories AS c INNER JOIN EasyDNNNewsCategoryList AS l ON c.CategoryID = l.CategoryID 	WHERE ParentCategory = $CageforyID OR c.CategoryID = $CageforyID) ORDER BY ArticleID");
while ($db->next_record()) {
  $registros[$total_registros] = array();
  foreach($eq_campos as $campo_bbdd => $campo_drupal) {
    if ($campo_drupal[1] == 'datetime') {
      $registros[$total_registros][$campo_bbdd][] = substr($db->record[$campo_bbdd]->format('Y-m-d H:i:s'), 0, 10);
    } elseif($campo_bbdd == 'categoria') {
      $db2->query("SELECT * FROM EasyDNNNewsCategories WHERE ArticleID = '{$db->record['ArticleID']}'");
      while($db2->next_record()) {
        $registros[$total_registros]['categoria'][] = $db2->record['CategoryID'];
      }      
    } else {
      // Limpiamos todos los css, ids, class, etc. que pudiera haber en las etiquetas
      $texto = preg_replace('/[ ]+[id|name|style|class|jquery{0-9}*]+="[^"]*"/i', '', html_entity_decode($db->record[$campo_bbdd]));
      $registros[$total_registros][$campo_bbdd][] = $texto;
    }
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
      $etiqueta = 'value';
      // Formateamos el valor, según su tipo de datos
      switch($eq_campos[$campos][1]) {
        // Si es un tipo de datos taxonomía
        case 'taxonomy':
          $valor = $taxonomia[$campos][$valor];
          // Cambiamos la etiqueta a target_id
          $etiqueta = 'target_id';
          break;
        // Si es un tipo de datos fecha
        case 'date':
          $valor = substr($valor, 0, 10);	
          break;
        // Si es un tipo de datos booleano
        case 'boolean':
          $valor = $valor == 'True' ? 1 : 0;	
          break;
        // Si es summary, no lo insertamos, lo insertaremos en el body
        case 'summary':
          $etiqueta = '';	
          break;        
        default:
      }	
      if ($etiqueta) {
        // Creamos un elemento para añadir al array
        $elem = array($etiqueta => utf8_encode($valor));
        // Si el tipo de campo es basic_html
        if ($eq_campos[$campos][1] == 'basic_html') {
          $elem['summary'] = strip_tags($registro['Summary'][0]);
          $elem['format'] = 'basic_html';
        }	  
        // Añadimos el dato al array
        $json[$eq_campos[$campos][0]] = array($elem);
      }
    }
  }
  // Metemos esta instrucción para indicar el autor del post (root)
  $elem = array('target_id' => $uid);
  $json['uid'] = array($elem);
  // Creamos el hal json
  $json = array_merge(array("_links" => array("type" => array("href" => "http://localhost:3880/drupal/rest/type/node/$entidad"))), $json);
  $json = json_encode($json);
  //print_r($json);
  
  // Hacemos el POST a la API de Drupal
  insert_drupal($entidad, $json);
} 

echo count($registros) . ' registros importados';
 
?>