<?php
include('../inc_config.php');

$registro = $_POST['registro'];

// Id del usuario que publica los artículos
$uid = 1;
// Id de la categoría padre de los contenidos de "Agenda del Presidente"
$CagegoryID = 17;
// Nombre entidad de Drupal
$entidad = 'agenda_presidente';

// Equivalencia de los campos de la vieja web con el tipo de datos de Drupal - campo sqlsrv => (nombre, tipo)
$eq_campos = array('Title' => array('title', ''), 'SubTitle' => array('field_subtituloa', ''), 'Article' => array('body', 'basic_html'), 'StartDate' => array('field_fecha_inicio', 'datetime'), 'EndDate' => array('field_fecha_fin', 'datetime'), 'PublishDate' => array('field_fecha_de_publicaciona', 'datetime_object'));

$db->query("SELECT TOP 1 * FROM EasyDNNNews AS n INNER JOIN EasyDNNNewsEventsData AS e ON n.ArticleID = e.ArticleID  WHERE n.ArticleID IN (SELECT ArticleID FROM EasyDNNNewsCategories AS c INNER JOIN EasyDNNNewsCategoryList AS l ON c.CategoryID = l.CategoryID 	WHERE ParentCategory = $CagegoryID OR c.CategoryID = $CagegoryID) AND n.ArticleID NOT IN (SELECT TOP $registro n.ArticleID FROM EasyDNNNews AS n INNER JOIN EasyDNNNewsEventsData AS e ON n.ArticleID = e.ArticleID  WHERE e.ArticleID IN (SELECT ArticleID FROM EasyDNNNewsCategories AS c INNER JOIN EasyDNNNewsCategoryList AS l ON c.CategoryID = l.CategoryID 	WHERE ParentCategory = $CagegoryID OR c.CategoryID = $CagegoryID) ORDER BY n.ArticleID) ORDER BY n.ArticleID");
if ($db->next_record()) {
  $contenido = array();
  foreach($eq_campos as $campo_bbdd => $campo_drupal) {
    // Formateamos según el tipo de datos
    $valor = formatear($campo_drupal[1], $db->record[$campo_bbdd]);
    // Lo guardamos en el array
    $contenido[$campo_bbdd][] = $valor;
  }
} else {
  exit('stop');
}


// Creamos un array, para mandar los datos con hal json
$json = array();
foreach($contenido as $campos => $campo) {
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
// Creamos el json
$json = crear_json($uid, $entidad, $json);

// Hacemos el POST a la API de Drupal
$result = insertar_contenido($entidad, $json);
echo $result['nid'][0]['value'] > 0 ? 'ok' : 'Error en registro: ArticleID = ' . $db->record['ArticleID'];

?>