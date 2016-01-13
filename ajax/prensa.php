<?php
include('../inc_config.php');

$registro = $_POST['registro'];

// Id del usuario que publica los artículos
$uid = 1;
// Id de la categoría padre
$CagegoryID = 10;
// Nombre entidad de Drupal
$entidad = 'nota_de_prensa';

// Equivalencia de las opciones de la vieja web a la taxonomía de Drupal - valor categoría sqlsrv => id taxonomía drupal
$taxonomia = array();
$taxonomia = array(15 => 2, 11 => 3, 12 => 4, 16 => 7, 13 => 1, 14 => 6);

// Equivalencia de los campos de la vieja web con el tipo de datos de Drupal - campo sqlsrv => (nombre, tipo)
$eq_campos = array('Title' => array('title', ''), 'SubTitle' => array('field_subtitulo', ''), 'Article' => array('body', 'basic_html'), 'Summary' => array('field_resumen', 'summary'), 'PublishDate' => array('field_fecha_de_publicacion', 'datetime'), 'ExpireDate' => array('field_fecha_de_expiracion', 'datetime'),  'categoria' => array('field_categoriap', 'taxonomy'));

$db->query("SELECT TOP 1 * FROM EasyDNNNews WHERE ArticleID IN (SELECT ArticleID FROM EasyDNNNewsCategories AS c INNER JOIN EasyDNNNewsCategoryList AS l ON c.CategoryID = l.CategoryID 	WHERE ParentCategory = $CagegoryID OR c.CategoryID = $CagegoryID) AND ArticleID NOT IN (SELECT TOP $registro ArticleID FROM EasyDNNNews WHERE ArticleID IN (SELECT ArticleID FROM EasyDNNNewsCategories AS c INNER JOIN EasyDNNNewsCategoryList AS l ON c.CategoryID = l.CategoryID 	WHERE ParentCategory = $CagegoryID OR c.CategoryID = $CagegoryID) ORDER BY ArticleID) ORDER BY ArticleID");
if ($db->next_record()) {
  $contenido = array();
  foreach($eq_campos as $campo_bbdd => $campo_drupal) {
    if ($campo_drupal[1] == 'datetime') {
      $contenido[$campo_bbdd][] = substr($db->record[$campo_bbdd]->format('Y-m-d H:i:s'), 0, 10);
    } elseif($campo_bbdd == 'categoria') {
      $db2->query("SELECT * FROM EasyDNNNewsCategories WHERE ArticleID = '{$db->record['ArticleID']}'");
      while($db2->next_record()) {
        $contenido['categoria'][] = $db2->record['CategoryID'];
      }      
    } else {
      // Limpiamos todos los css, ids, class, etc. que pudiera haber en las etiquetas
      $texto = limpiar_estilos(html_entity_decode($db->record[$campo_bbdd]));
      $contenido[$campo_bbdd][] = $texto;
    }
  }
} else {
  exit('stop');
}


// Creamos un array, para mandar los datos con hal json
$json = array();
foreach($contenido as $campos => $campo) {
  foreach($campo as $valores => $valor) {
    $etiqueta = 'value';
    // Formateamos el valor, según su tipo de datos
    switch($eq_campos[$campos][1]) {
      // Si es un tipo de datos taxonomía
      case 'taxonomy':
        $valor = $taxonomia[$valor];
        // Cambiamos la etiqueta a target_id
        $etiqueta = 'target_id';
        break;
      // Si es un tipo de datos fecha
      case 'datetime':
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
        $elem['summary'] = utf8_encode(limpiar_estilos(html_entity_decode($contenido['Summary'][0])));
        $elem['format'] = 'basic_html';
      }	  
      // Añadimos el dato al array
      $json[$eq_campos[$campos][0]] = array($elem);
    }
  }
}
// Creamos el json
$json = crear_json($uid, $entidad, $json);

// Hacemos el POST a la API de Drupal
$result = insertar_contenido($entidad, $json);
echo $result['nid'][0]['value'] > 0 ? 'ok' : 'Error en registro: ArticleID = ' . $db->record['ArticleID'];

?>