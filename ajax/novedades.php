<?php
include('../inc_config.php');

$registro = $_POST['registro'];

// Id del usuario que publica los artículos
$uid = 1;
// Id del módulo en bbdd SQLServer
$module_id = 1085;
// Nombre entidad de Drupal
$entidad = 'novedad';

// Equivalencia de las opciones de la vieja web a la taxonomía de Drupal - valor categoría sqlsrv => id taxonomía drupal
$taxonomia = array();
$taxonomia = array('CNMC' => 2, 'Competencia' => 3, 'Energía' => 4, 'Estadísticas' => 5, 'Promoción' => 7, 'Promoción Competencia' => 7, 'PromocionCompetencia' => 7, 'telecomunicacionesysaudiovisuales' => 1, 'transportesysectorpostal' => 6, 'Unidad de Mercado' => 9);

// Equivalencia de los campos de la vieja web con el tipo de datos de Drupal - campo sqlsrv => (nombre, tipo)
$eq_campos = array(75 => array('title', ''), 76 => array('field_categoria', 'taxonomy'), 81 => array('field_texto_corto', ''), 83 => array('field_portada', 'boolean'), 84 => array('field_fecha', 'date'), 85 => array('body', 'basic_html'), 142 => array('field_nota_de_prensa_titulo', ''), 143 => array('field_nota_de_prensa_enlace', ''), 412 => array('field_fecha_publicacion', 'datetime'));

// Obtenemos todos los registros
$contenido = array();
$db->query("SELECT TOP 1 * FROM UserDefinedRows WHERE ModuleId = '$module_id' AND UserDefinedRowId NOT IN (SELECT TOP $registro UserDefinedRowId FROM UserDefinedRows WHERE ModuleId = '$module_id' ORDER BY UserDefinedRowId) ORDER BY UserDefinedRowId");
if (!$db->next_record()) {
  // Buscamos los campos de ese registro
  $db2->query("SELECT * FROM UserDefinedData AS d INNER JOIN UserDefinedFields AS f ON d.UserDefinedFieldId = f.UserDefinedFieldId WHERE UserDefinedRowId = '{$db->record['UserDefinedRowId']}'");
  while($db2->next_record()) {
    // Si es un tipo de datos múltiple
    if ($db2->record['MultipleValues']) {
      $valor = explode(';', $db2->record['FieldValue']);
    } else {
      // Limpiamos todos los css, ids, class, etc. que pudiera haber en las etiquetas
      $texto = limpiar_estilos($db2->record['FieldValue']);
      //$texto = $db2->record['FieldValue'];
      $valor = array($texto);
    }
    // Lo guardamos en un array
    foreach($valor as $valor) {
      // Sólo guardamos los campos que necesitamos
      if ($eq_campos[$db2->record['UserDefinedFieldId']]) {
        $contenido[$db2->record['UserDefinedFieldId']][] = $valor;
      }
    }
  }
} else {
  exit('stop');
}

$json = array();
foreach($contenido as $campos => $campo) {
  foreach($campo as $valores => $valor) {
    $etiqueta = 'value';
    // Formateamos el valor, según su tipo de datos
    switch($eq_campos[$campos][1]) {
      // Si es un tipo de datos taxonomía
      case 'taxonomy':
        $valor = $taxonomia[utf8_encode($valor)];
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
      default:
    }	
    // Creamos un elemento para añadir al array
    $elem = array($etiqueta => utf8_encode($valor));
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
echo $result['nid'][0]['value'] > 0 ? 'ok' : 'Error en registro: UserDefinedRowId = ' . $db->record['UserDefinedRowId'];

?>
