<?php
// POST a la API Rest de Drupal
function insertar_contenido($entidad, $json) {
  $ch = curl_init("localhost:3880/drupal/$entidad/node?_format=hal+json");
  $options = array(
    CURLOPT_RETURNTRANSFER => true,    
    CURLOPT_HTTPHEADER => array('Content-Type: application/hal+json', 'Accept: application/hal+json'),
    CURLOPT_SSL_VERIFYPEER => false, 
    CURLOPT_CUSTOMREQUEST => "POST", 	
    CURLOPT_POST => true,  
    CURLOPT_POSTFIELDS => $json,	
  );
  curl_setopt_array($ch, $options);    
  $response = curl_exec($ch);
  curl_close($ch);  
  return json_decode($response,true);
}

// GET a la API Rest de Drupal
function obtener_contenido($entidad, $id) {
  $ch = curl_init("localhost:3880/drupal/$entidad/node/$id?_format=hal+json");
  $options = array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => array('Content-Type: application/hal+json', 'Accept: application/hal+json'),
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_CUSTOMREQUEST => "GET"
  );
  curl_setopt_array($ch, $options);
  $response = curl_exec($ch);
  curl_close($ch);
  return json_decode($response,true);  
}

function limpiar_estilos($texto) {
  // Transformamos el font-weight:bold de los span, en strong
  $texto = preg_replace('/\<span[^>]*style="[^"]*font-weight:[ ]*bold[^"]*"[^>]*\>([^<]*)\<\/span\>/i', '<strong>$1</strong>', $texto);
  // Limpiamos todos los ids, las clases, names, estilos y todo el javascript que pudiera haber en el contenido
  $texto = preg_replace('/[ ]+[abp|id|name|style|class|jquery{0-9}*]+="[^"]*"|\<script[^<]*\<\/script>/i', '', $texto);  
  return $texto;
}

function comillas_tipografias($texto) {
  // Cambiamos las comillas tipográficas
  $texto = str_ireplace(array('\u0093', '\u0094'), array('\u201c', '\u201d'), $texto);
  return $texto;
}

// Creamos el hal json necesario para mandar a la API de Drupal
function crear_json($uid, $entidad, $json) {
  // Metemos esta instrucción para indicar el autor del post (root)
  $elem = array('target_id' => $uid);
  $json['uid'] = array($elem);
  // Creamos el hal json
  $json = array_merge(array("_links" => array("type" => array("href" => "http://localhost:3880/drupal/rest/type/node/$entidad"))), $json);
  $json = json_encode($json);
  $json = comillas_tipografias($json);
  return $json;
}

// Función para formatear de tipo de datos SQLServer a Drupal
function formatear($tipo, $valor) {
  switch ($tipo) {
    case 'datetime_object':
      $valor = substr($valor->format('Y-m-d H:i:s'), 0, 10);
      break;
    case 'boolean':
      $valor = $valor == 'True' ? 1 : 0;
      break;
    case 'datetime':
      $valor = $valor->format('Y-m-d') . 'T' . $valor->format('H:i:s');
      break;
    default:
      // Limpiamos todos los css, ids, class, etc. que pudiera haber en las etiquetas
      $valor = limpiar_estilos(html_entity_decode($valor));
  }  
  return $valor;
}
?>