<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include('librerias/db_sqlsrv.php');

$db = new db_sqlsrv;
$db2 = new db_sqlsrv;

$module_id = 1085;
$entidad = 'novedad';
$taxonomia = array();
// Equivalencia de las opciones de la vieja web a la taxonomía de Drupal (tabla, UserDefinedFieldId)
$taxonomia['novedad'][76] = array('CNMC' => 2, 'Competencia' => 3, 'Energía' => 4, 'Estadísticas' => 5, 'Promoción' => 7, 'Promoción Competencia' => 7, 'PromocionCompetencia' => 7, 'telecomunicacionesysaudiovisuales' => 1, 'transportesysectorpostal' => 6, 'Unidad de Mercado' => 9);
// Equivalencia de los campos de la vieja web con el tipo de datos de Drupal
$eq_campos['novedad'] = array(75 => 'title', 76 => 'field_categoria', 81 => 'field_texto_corto', 83 => 'field_portada', 84 => 'field_fecha', 85 => 'body', 142 => 'field_nota_de_prensa_titulo', 143 => 'field_nota_de_prensa_enlace', 412 => 'field_fecha_publicacion');
$registros = array();

// Obtenemos todos los registros
$db->query("SELECT TOP 1 * FROM UserDefinedRows WHERE ModuleId = '$module_id'");
while($db->next_record()) {
	// Por cada campo
    $db2->query("SELECT * FROM UserDefinedData AS d INNER JOIN UserDefinedFields AS f ON d.UserDefinedFieldId = f.UserDefinedFieldId WHERE UserDefinedRowId = '{$db->record['UserDefinedRowId']}'");
    while($db2->next_record()) {
	  // Si es un tipo de datos múltiple
	  if ($db2->record['MultipleValues']) {
		$valores = explode(';', $db2->record['FieldValue']);
	  } else {
	    $valores = array($db2->record['FieldValue']);
	  }
	  // Lo guardamos en un array
      foreach($valores as $valor) {
		// Si es un tipo de datos fecha
		if ($db2->record['UserDefinedFieldId'] == 84) {
		  $valor = substr($valor, 0, 10);	
		// Si es un tipo de datos booleano
		} elseif ($db2->record['UserDefinedFieldId'] == 83) {
		  $valor = $valor == 'True' ? 1 : 0;			  
	    // Si es un tipo de datos de taxonomía
		} elseif ($taxonomia[$entidad][$db2->record['UserDefinedFieldId']]) {
	      $valor = $taxonomia[$entidad][$db2->record['UserDefinedFieldId']][utf8_encode($valor)];
		  $indice = 'target_id';
		} else {
		  $indice = 'value';
		}
		// Sólo guardamos los campos que necesitamos
		if ($eq_campos[$entidad][$db2->record['UserDefinedFieldId']]) {
		  $registros[$db->record['UserDefinedRowId']][$eq_campos[$entidad][$db2->record['UserDefinedFieldId']]][$indice][] = mysql_escape_string($valor);
		}
	  }	  
	}
}  

foreach($registros as $registro) {
  $json = '{"_links":{"type":{"href":"http://localhost:3880/drupal/rest/type/node/' . $entidad . '"}}';
  foreach($registro as $campo => $datos) {
	$json .=  ",\n  ";
	$json .= '"' . $campo . '":[' . "\n";
    foreach($datos as $dato => $valores) {
	  $j = 0;	
	  foreach ($valores as $valor) {
		$json .= '    ' . ($j++ > 0 ? ',' : '');  
        $json .= '{"' . $dato . '":"' . $valor . '"';
		if ($campo == 'body') {
		  $json .= ', "format":"basic_html"';
		}
		$json .= "}\n";
	  }
	}
	$json .= '  ]';
  }
  $json .= "\n" . '}' . "\n";
}
echo $json;

/*
Hay que ver cómo escapar el HTML
Hay que pasar los booleanos de False o True a 1 ó 0
*/

?>