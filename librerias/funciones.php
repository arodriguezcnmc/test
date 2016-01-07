<?php
function insert_drupal($entidad, $json) {
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
?>