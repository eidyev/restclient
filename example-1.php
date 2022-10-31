<?php

//include file "RestClient.php"

//Define array of request parameters, empty is no parameters exist
$params = array('parametro1' => 'valorde parametro1', 'parametro2'=>'valor parametro2', ... 'parametro N'=>'valor parametro N');
   
//Instance a RestClient an set info and parameters 
$restclient = new RestClient('http://api_or_site_url.com/pathx', 'POST', $params);

//heare before execute yo can set other options tu restclient	
$restclient->set....   
   
//Do request
	$restclient->execute(); 
   
//getting response data
$result = $restclient->getResponse_data();	
  
if data is a json you use 
$result = $restclient->getResponse_data_json_decode();

?>
