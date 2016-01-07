<?php
set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include('librerias/db_sqlsrv.php');
include('librerias/funciones.php');
$db = new db_sqlsrv;
$db2 = new db_sqlsrv;

// Id del usuario que publica los artículos
$uid = 1;
// Id del módulo en bbdd SQLServer
$module_id = 1085;
// Nombre entidad de Drupal
$entidad = 'novedad';

// Equivalencia de las opciones de la vieja web a la taxonomía de Drupal - valor categoría sqlsrv => id taxonomía drupal
$taxonomia = array();
$taxonomia[76] = array('CNMC' => 2, 'Competencia' => 3, 'Energía' => 4, 'Estadísticas' => 5, 'Promoción' => 7, 'Promoción Competencia' => 7, 'PromocionCompetencia' => 7, 'telecomunicacionesysaudiovisuales' => 1, 'transportesysectorpostal' => 6, 'Unidad de Mercado' => 9);

// Equivalencia de los campos de la vieja web con el tipo de datos de Drupal - campo sqlsrv => (nombre, tipo)
$eq_campos = array(75 => array('title', ''), 76 => array('field_categoria', 'taxonomy'), 81 => array('field_texto_corto', ''), 83 => array('field_portada', 'boolean'), 84 => array('field_fecha', 'date'), 85 => array('body', 'basic_html'), 142 => array('field_nota_de_prensa_titulo', ''), 143 => array('field_nota_de_prensa_enlace', ''), 412 => array('field_fecha_publicacion', 'datetime'));

// Obtenemos todos los registros
$registros = array();
$total_registros = 0;
$db->query("SELECT * FROM UserDefinedRows WHERE ModuleId = '$module_id' ORDER BY UserDefinedRowId");
while($db->next_record()) {
	// Por cada campo
  $db2->query("SELECT * FROM UserDefinedData AS d INNER JOIN UserDefinedFields AS f ON d.UserDefinedFieldId = f.UserDefinedFieldId WHERE UserDefinedRowId = '{$db->record['UserDefinedRowId']}'");
  while($db2->next_record()) {
	  // Si es un tipo de datos múltiple
	  if ($db2->record['MultipleValues']) {
  		$valor = explode(';', $db2->record['FieldValue']);
	  } else {
      // Limpiamos todos los css, ids, class, etc. que pudiera haber en las etiquetas
      $texto = preg_replace('/[ ]+[id|name|style|class|jquery{0-9}*]+="[^"]*"/i', '', $db2->record['FieldValue']);
      //$texto = $db2->record['FieldValue'];
	    $valor = array($texto);
	  }
	  // Lo guardamos en un array
    foreach($valor as $valor) {
		  // Sólo guardamos los campos que necesitamos
		  if ($eq_campos[$db2->record['UserDefinedFieldId']]) {
        $registros[$total_registros][$db2->record['UserDefinedFieldId']][] = $valor;
      }
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
          $valor = $taxonomia[$campos][utf8_encode($valor)];
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
  // Metemos esta instrucción para indicar el autor del post (root)
  $elem = array('target_id' => $uid);
  $json['uid'] = array($elem);  
  //print_r($json);
  // Creamos el hal json
  $json = array_merge(array("_links" => array("type" => array("href" => "http://localhost:3880/drupal/rest/type/node/$entidad"))), $json);
  $json = json_encode($json);
  //echo $json;
    
  // Hacemos el POST a la API de Drupal
  insert_drupal($entidad, $json);
}

echo count($registros) . ' registros importados';
?>