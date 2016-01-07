<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include('librerias/db_sqlsrv.php');

$db = new db_sqlsrv;
$db2 = new db_sqlsrv;

$module_id = 1085;
$entidad = 'novedad';
$taxonomia = array();
// Equivalencia de las opciones de la vieja web a la taxonomía de Drupal (tabla, id)
$taxonomia['novedad'][76] = array('CNMC' => 2, 'Competencia' => 3, 'Energía' => 4, 'Estadísticas' => 5, 'Promoción' => 7, 'Promoción Competencia' => 7, 'PromocionCompetencia' => 7, 'telecomunicacionesysaudiovisuales' => 1, 'transportesysectorpostal' => 6, 'Unidad de Mercado' => 9);
// Equivalencia de los campos de la vieja web con el tipo de datos de Drupal
$eq_campos['novedad'] = array(75 => array('title', ''), 76 => array('field_categoria', 'taxonomy'), 81 => array('field_texto_corto', ''), 83 => array('field_portada', 'boolean'), 84 => array('field_fecha', 'date'), 85 => array('body', 'basic_html'), 142 => array('field_nota_de_prensa_titulo', ''), 143 => array('field_nota_de_prensa_enlace', ''), 412 => array('field_fecha_publicacion', 'datetime'));
$registros = array();

// Obtenemos todos los registros
$db->query("SELECT TOP 1 * FROM UserDefinedRows WHERE ModuleId = '$module_id'");
while($db->next_record()) {
	// Por cada campo
    $db2->query("SELECT * FROM UserDefinedData AS d INNER JOIN UserDefinedFields AS f ON d.UserDefinedFieldId = f.UserDefinedFieldId WHERE UserDefinedRowId = '{$db->record['UserDefinedRowId']}'");
    while($db2->next_record()) {
	  // Si es un tipo de datos múltiple
	  if ($db2->record['MultipleValues']) {
		$valor = explode(';', $db2->record['FieldValue']);
	  } else {
	    $valor = array($db2->record['FieldValue']);
	  }
	  // Lo guardamos en un array
      foreach($valor as $valor) {
		// Sólo guardamos los campos que necesitamos
		if ($eq_campos[$entidad][$db2->record['UserDefinedFieldId']]) {
		  $registros[$db->record['UserDefinedRowId']][$db2->record['UserDefinedFieldId']][] = mysql_escape_string($valor);
		}
	  }	  
	}
}  

foreach($registros as $registro) {
  $json_aux = array("_links" => array("type" => array("href" => "http://localhost:3880/drupal/rest/type/node/novedad")));
  $json = '{"_links":{"type":{"href":"http://localhost:3880/drupal/rest/type/node/' . $entidad . '"}}';
  foreach($registro as $campos => $campo) {
	$json .=  ",\n  ";
	// Nombre del campo
	$json .= '"' . $eq_campos[$entidad][$campos][0] . '":[' . "\n";
	$j = 0;
    foreach($campo as $valores => $valor) {
	  $etiqueta = 'value';
	  // Formateamos el valor, según su tipo de datos
      switch($eq_campos[$entidad][$campos][1]) {
		// Si es un tipo de datos taxonomía
	    case 'taxonomy':
		  $valor = $taxonomia[$entidad][$campos][utf8_encode($valor)];
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
	  $json .= '    ' . ($j++ > 0 ? ',' : '');  
	  $json .= '{"' . $etiqueta . '":"' . $valor . '"';
	  $aa = array($etiqueta => utf8_encode($valor));
	  $json_aux[$eq_campos[$entidad][$campos][0]] = $aa;
	  // Si el tipo de campo es basic_html
	  if ($eq_campos[$entidad][$campos][1] == 'basic_html') {
	    $json .= ', "format":"basic_html"';
	  }
	  $json .= "}\n";
	 
	}
	$json .= '  ]';
  }
  $json .= "\n" . '}' . "\n";
  
  echo $json;
  echo json_encode($json_aux);
  
  // Hacemos el POST
  $ch = curl_init("localhost:3880/drupal/novedad/node?_format=hal_json");
  $options = array(
	CURLOPT_RETURNTRANSFER => true,    
	CURLOPT_HTTPHEADER => array('Content-Type: application/hal+json'),
	CURLOPT_HTTPHEADER => array('Accept: application/hal+json'),
	CURLOPT_HTTPHEADER => array('X-CSRF-Token: Ru5HF7wmt94mHAUOVNQ80bAL8rjmrBXIHBpR3y5bK6k'),
	CURLOPT_SSL_VERIFYPEER => false, 
	CURLOPT_CUSTOMREQUEST => "POST", 	
	CURLOPT_POST => true,  
	CURLOPT_POSTFIELDS => $json,	
  );
  curl_setopt_array($ch, $options);    
  $response = curl_exec($ch);
  curl_close($ch);  

  //$data = json_decode($response,true);    
  //print_r($response); 
}
?>