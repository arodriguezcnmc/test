<?php
$ch = curl_init("localhost:3880/drupal/novedad/node/22?_format=hal_json");
$options = array(
		CURLOPT_RETURNTRANSFER => true,    
		CURLOPT_HTTPHEADER => array('Content-Type: application/hal+json', 'Accept: application/hal+json', 'X-CSRF-Token: Ru5HF7wmt94mHAUOVNQ80bAL8rjmrBXIHBpR3y5bK6k'),
		CURLOPT_SSL_VERIFYPEER => false,    
		CURLOPT_CUSTOMREQUEST => 'GET',    
);
curl_setopt_array($ch, $options);    
$response = curl_exec($ch);
curl_close($ch);  

//$data = json_decode($response,true);    
print_r($response); 
?>