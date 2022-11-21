#PHP RestClient 
------------------------------------------------------------------------------------------------------
REST client for PHP, Allows a php server to make requests to other domains (Cross Site)   

Cliente REST para PHP, Permite desde un servidor php hacer peticiones a otros dominios (CrossSite)

Version: 3.0

Programmer: Eidy Estupiñan Varona <eidyev@gmail.com>

Requirements: PHP CURL Extension  php-curl

(English) How to use
---------------------------------------------------------------------------------------------------------------------------------------------------------
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




	other example
	
	<?php 
		include 'RestClient.php';

		$restclient = new RestClient('http://site.com/notificaciones', 'POST', array('type'=>3) );

		$restclient->setUseragent('RestClient3.0;')	
			   ->setReferer('https://sitex.com')
			   ->addRequest_custom_header('auth-pubkey','471215-987532-155461-a13w45-bc1812-edt9647')
			   ->addRequest_custom_header('info-plain','471215987532155461a13w45bc1812edt9647')
			   ->addRequest_custom_header('info-singned','4asd7asd1f2fas1f5sdf9s8fs75sf3ss21sd')
			   ->execute();
				   
		var_dump($restclient->getResponse_data());	




 (Español) ¿Cómo usarla?
---------------------------------------------------------------------------------------------------------------------------------------------------------
	
	Para usarla se debe incluir el archivo "RestClient.php" donde se desee usar
		
	//Definir listado de parámetros que se enviaran en la petición, es un arreglo, dejar vacio en caso de no tener parámetros
	$params = array('parametro1' => 'valorde parametro1', 'parametro2'=>'valor parametro2', ... 'parametro N'=>'valor parametro N');
	   
	//Creando el objeto 
	$clienterest = new RestClient('http://request_urldeprueba.com/rutax', 'POST', $params);
	   
	//Antes de llamar a la función de execute() se pueden especificar otras opciones y parametros   
	$restclient->set.... 
	   
	//Efectuando la petición
    	$clienterest->execute(); 
	   
	//Acediendo a la respuesta
	$respuesta = $clienterest->getResponse_data();	
      
	//En caso de la respuesta ser un json se puede objener el dato directamente llamando a la función
    	$respuesta = $clienterest->getResponse_data_json_decode();
	
	
	un ejemplo completo
	
	<?php 
		include 'RestClient.php';

		$clienterest = new RestClient('http://site.com/notificaciones', 'POST', array('tipo'=>3) );

		$clienterest->setUseragent('RestClient3.0;')	
			    ->setReferer('https://sitex.com')
			    ->addRequest_custom_header('auth-pubkey','471215-987532-155461-a13w45-bc1812-edt9647')
			    ->addRequest_custom_header('info-plain','471215987532155461a13w45bc1812edt9647')
			    ->addRequest_custom_header('info-singned','4asd7asd1f2fas1f5sdf9s8fs75sf3ss21sd')
			    ->execute();
				   
		$resultado = $clienterest->getResponse_data_json_decode();	
	
	
	
	
//Functions 	             
//Funciones disponibles
--------------------------------------------------------------------------------------------------------------------------------------------------------
        
    //Constructor
    public function __construct ($url = null, $method = 'GET', $parameters = null)

    
   //Function for build string of request parameters         
   //Función para conformar la cadena de consulta con los parametros de la petición
    buildRequestParametersString($data = null)
    
    
    //Execute request            
    //Función para ejecutar la petición   
    execute()
    	
	
    getResponse_status_code()    

    getResponse_type() 

    getResponse_info() 

    getResponse_data() 
	
    getResponse_data_json_decode() 
		
    //Function Execute and Explain, use only for test and diagnostic            
    //Funcion para ejecutar y ver salida detalla, usar para prueba y diagnóstico
    executeAndExplain() 
		
    
   //Getters and Setters functions         
   //Funciones para obtención y seteo de atributos
   //------------------------------------------------------------------------------------------------------------------------------------
	
    getUseragent()                             
    setUseragent($useragent) 
							                   
    getTimeout()                               
    setTimeout($timeout) 
							                   
    getCookieFileLocation()                    
    setCookieFileLocation($cookieFileLocation) 
							                   
    getReferer() 			                   
    setReferer($referer) 
							                   
    getRequest_url() 	                       
    setRequest_url($request_url) 
							                   
    getRequest_port()                          
    setRequest_port($request_port) 
							                   
    getRequest_method()                        
    setRequest_method($request_method) 
							                   
    getRequest_parameters()                    
    setRequest_parameters($request_parameters) 

    getRequest_length()      

    getRequest_accept_type()                   
    setRequest_accept_type($request_accept_type) 

    getRequest_custom_headers()                
    setRequest_custom_headers($array_custom_headers) 

    addRequest_custom_header($key,$value) 
    
    getRequest_authentication_enabled()        
    setRequest_authentication_enabled($request_authentication_enabled) 

    getRequest_auth_username()                 
    setRequest_auth_username($request_auth_username) 

    getRequest_auth_password()                 
    setRequest_auth_password($request_auth_password) 

