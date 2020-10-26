<?php
/*
* RestClient 
* REST client for PHP, Allows a php server to make requests to other domains (Cross Site)   
* Cliente REST para PHP, Permite desde un servidor php hacer peticiones a otros dominios (Cross Site)
* Version: 3.0
* Programmer: Eidy Estupiñan Varona <eidyev@gmail.com>
* Requirements: PHP CURL Extension  php-curl
*/

class RestClient
{
   //Enviroment atributes          //Atributos Generales
   //-------------------------------------------------------------------------------------------------------- 
        private $useragent;                                   //User agent   //User agent simula al de un navegador Firefox 75 en Windows 10 
        private $timeout = 10;                                //Time Out     //Tiempo de espera para la conexión
        private $cookieFileLocation;                          //Cookie FIle  //Fichero de Cookies para en caso del que el sitio las cree 
        private $referer = null;                              //Referrer     //Indica quien refirió para hacer la petición
	private $verificSSL = false;
        private $validSSL = null;
        private $error = null;
        
    //Request atributes            //Atributos de la petición 
    //-------------------------------------------------------------------------------------------------------
        private $request_url = '';                            //Request URL            //URL a la cual se hará la petiión
        private $request_port = null;                         //Request Port           //Puerto detino en casod e que se use otro
        private $request_method = 'GET';                      //Request Method         //Método a usar en las peticiones GET,POST,PUT,DELETE
        private $request_parameters = null;                   //Request parameters     //listado de parametros en la peticion
        private $request_length = 0;                          //Request length         //longitud de la lista de parametros de la petición
        private $request_accept_type = 'application/json';    //Request Accept Type    //Typo de contenido que se esperará en el resultado
        private $request_custom_headers = null;               //Request custom headers //Cabeceras personalizadas, agregar keys para APIs etc 
        private $request_authentication_enabled = false;      //Request Auth           //Habilitar Autenticación
        private $request_auth_username = null;                //Request Username       //Usuario
        private $request_auth_password = null;                //Request Password       //Clave 
    

    //Response atributes            //Atributos de la respuesta
    //-------------------------------------------------------------------------------------------------------
        private $response_status_code= null;                  //Response Sttus Code     //Código de estado de la respuesta
        private $response_type= null;                         //Response Type           //Tipo de contenido de la respuesta  
        private $response_content_length = 0;                 //Response Content Length //Longitud del contenidod e la respuesta 
        private $response_info= null;                         //Response Info           //Información completa de la respuesta 
        private $response_data= null;                         //Response Data           //Datos de la respuesta 
        
        
    //Constructor
    public function __construct ($url = null, $method = 'GET', $parameters = null)
    {
        $this->request_url = $url;
        $this->request_method = $method;
        $this->request_parameters = $parameters;
        if ($this->request_parameters !== null)
           $this->buildRequestParametersString($parameters);
        
        $file = getcwd().DIRECTORY_SEPARATOR.'cookie.txt';
        $file2= getcwd().DIRECTORY_SEPARATOR.'ca.pem';
        if (file_exists($file))
           unlink($file);
        if (file_exists($file2))
           unlink($file2);        
        $this->cookieFileLocation = $file;
        $this->useragent = 'REST-Client-3.0';
    }
    
    //Function for build string of request parameters         //Función para conformar la cadena de consulta con los parametros de la petición
    public function buildRequestParametersString($data = null)
    {
        $data = ($data !== null) ? $data : $this->request_parameters;
        if (!is_array($data))		
            throw new InvalidArgumentException('Invalid data input for parameters.  Array expected');
        $data = http_build_query($data, '', '&');
        $this->request_parameters = $data;
        $this->request_length = strlen($this->request_parameters);
    }
    
    //Execute request            //Función para ejecutar la petición   
    public function execute()
    {
	//curl sesion init
	$curlhandle = curl_init();
        
	try
        {
            switch (strtoupper($this->request_method))
            {
                case 'GET':
                        $this->executeGet($curlhandle);
                        break;
                case 'POST':
                        $this->executePost($curlhandle);
                        break;
                case 'PUT':
                        $this->executePut($curlhandle);
                        break;
                case 'DELETE':
                        $this->executeDelete($curlhandle);
                        break;
                default:
                        throw new InvalidArgumentException('"'.$this->request_method.'" is an invalid REST request method.');
            }
        }
        catch (InvalidArgumentException $e)
        {
            curl_close($curlhandle);
            throw $e;
        }
        catch (Exception $e)
        {
            curl_close($curlhandle);
            throw $e;
        }
    }
    	
    //Execute request by GET Method           //Función para ejecutar la petición por el método GET
    private function executeGet ($curlhandle)
    {
        if(!empty($this->request_parameters))
           $this->request_url .= '?'.$this->request_parameters;
        $this->doExecute($curlhandle);			
    }
	
    //Execute request by POST Method           //Función para ejecutar la petición por el método POST
    private function executePost ($curlhandle)
    {
        if (!is_string($this->request_parameters))
           $this->buildRequestParametersString();
        curl_setopt($curlhandle, CURLOPT_POSTFIELDS, $this->request_parameters);
        curl_setopt($curlhandle, CURLOPT_POST, TRUE);
        curl_setopt($curlhandle, CURLOPT_BINARYTRANSFER, TRUE);
        $this->doExecute($curlhandle);	
    }
	
    //Execute request by PUT Method           //Función para ejecutar la petición por el método PUT
    private function executePut ($curlhandle)
    {
        if (!is_string($this->request_parameters))
            $this->buildRequestParametersString();

        $fh = fopen('php://memory', 'rw');
        fwrite($fh, $this->request_parameters);
        rewind($fh);
        curl_setopt($curlhandle, CURLOPT_INFILE, $fh);
        curl_setopt($curlhandle, CURLOPT_INFILESIZE, $this->request_length);
        curl_setopt($curlhandle, CURLOPT_PUT, TRUE);
        $this->doExecute($curlhandle);
        fclose($fh);
    }
	
    //Execute request by DELETE Method           //Función para ejecutar la petición por el método DELETE
    private function executeDelete ($curlhandle)
    {
        curl_setopt($curlhandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if(!empty($this->request_parameters))
            $this->request_url .= '?'.$this->request_parameters;
        $this->doExecute($curlhandle);
    }
	
    //Execute request            //Función para ejecutar la petición
    private function doExecute (&$curlHandle)
    {
        $this->setCurlOpts($curlHandle);
        $this->response_data = curl_exec($curlHandle);
        $this->response_info = curl_getinfo($curlHandle); 
        $this->response_status_code = curl_getinfo($curlHandle,CURLINFO_HTTP_CODE);
        $this->response_type = curl_getinfo($curlHandle,CURLINFO_CONTENT_TYPE);
        $this->response_content_length = strlen($this->response_data);
        if ($this->verificSSL)
            $this->validSSL = curl_getinfo($curlHandle,CURLINFO_SSL_VERIFYRESULT);
        
        if(curl_errno($curlHandle)) 
            $this->error = 'Error: '.curl_error($curlHandle);
         curl_close($curlHandle);
    }
	
    //Set CURL Options           //Establecer parámetros al CURL
    private function setCurlOpts(&$curlHandle)
    {
        curl_setopt($curlHandle, CURLOPT_USERAGENT,$this->useragent);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curlHandle, CURLOPT_URL, $this->request_url);
        
        if($this->request_port !== null)
            curl_setopt($curlHandle, CURLOPT_PORT , $this->request_port);
        
        if($this->referer !== null) 
            curl_setopt($curlHandle, CURLOPT_REFERER,$this->referer); 
        
        if($this->request_custom_headers !== null)
        {    
           $custom_headers = $this->request_custom_headers;
           $custom_headers_keys = array_keys($custom_headers);
           $custom_headers_strs = array('Accept: '. $this->request_accept_type);

           for($i=0;$i<count($custom_headers_keys);$i++)
           {
               $key=$custom_headers_keys[$i];
               $valor=$custom_headers[$key];
               $custom_headers_strs[]= $key.': '.$valor;                            
           }
           curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $custom_headers_strs);
        }    
        else    
           curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Accept: '. $this->request_accept_type));
                
        
        if ($this->request_authentication_enabled && ($this->request_username !== null && $this->request_password !== null))
        {
            curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);         
            curl_setopt($curlHandle, CURLOPT_USERPWD, $this->request_username.':'.$this->request_password);
        }
        
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
        
        curl_setopt($curlHandle, CURLOPT_COOKIEFILE,$this->cookieFileLocation); 
        
        if ($this->verificSSL)
        {
            curl_setopt($curlHandle, CURLOPT_CAINFO, getcwd().DIRECTORY_SEPARATOR.'ca.pem');
            curl_setopt($curlHandle, CURLOPT_CAPATH, getcwd().DIRECTORY_SEPARATOR.'ca.pem');
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        }
        else
        {    
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
    }
    
   //Getters and Setters functions               //Funciones para obtención y seteo de atributos
   //------------------------------------------------------------------------------------------------------------------------------------
    public function getUseragent() {
        return $this->useragent;
    }

    public function setUseragent($useragent) {
        $this->useragent = $useragent;
        return $this;
    }

    public function getTimeout() {
        return $this->timeout;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
        return $this;
    }

    public function getCookieFileLocation() {
        return $this->cookieFileLocation;
    }

    public function setCookieFileLocation($cookieFileLocation) {
        $this->cookieFileLocation = $cookieFileLocation;
        return $this;
    }

    public function getReferer() {
        return $this->referer;
    }

    public function setReferer($referer) {
        $this->referer = $referer;
        return $this;
    }

    public function getVerificSSL() {
        return $this->verificSSL;
    }

    public function setVerificSSL($verificSSL) {
        $this->verificSSL = $verificSSL;
        return $this;
    }
    
    public function getValidSSL() {
        return $this->validSSL;
    }
            
    public function getRequest_url() {
        return $this->request_url;
    }

    public function setRequest_url($request_url) {
        $this->request_url = $request_url;
        return $this;
    }

    public function getRequest_port() {
        return $this->request_port;
    }

    public function setRequest_port($request_port) {
        $this->request_port = $request_port;
        return $this;
    }

    public function getRequest_method() {
        return $this->request_method;
    }

    public function setRequest_method($request_method) {
        $this->request_method = $request_method;
        return $this;
    }

    public function getRequest_parameters() {
        return $this->request_parameters;
    }

    public function setRequest_parameters($request_parameters) {
        $this->request_parameters = $request_parameters;
        return $this;
    }

    public function getRequest_length() {
        return $this->request_length;
    }

    public function getRequest_accept_type() {
        return $this->request_accept_type;
    }

    public function setRequest_accept_type($request_accept_type) {
        $this->request_accept_type = $request_accept_type;
        return $this;
    }

    public function getRequest_custom_headers() {
        return $this->request_custom_headers;
    }

    public function setRequest_custom_headers($array_custom_headers) {
        if (!is_array($array_custom_headers))		
            throw new InvalidArgumentException('Invalid data input for $request_custom_headers.  Array expected');
        $this->request_custom_headers = $array_custom_headers;
        return $this;
    }

    public function addRequest_custom_header($key,$value) {
       $this->request_custom_headers[]= $key.': '.$value;
       return $this;
    }
    
    public function getRequest_authentication_enabled() {
        return $this->request_authentication_enabled;
    }

    public function setRequest_authentication_enabled($request_authentication_enabled) {
        $this->request_authentication_enabled = $request_authentication_enabled;
        return $this;
    }

    public function getRequest_auth_username() {
        return $this->request_auth_username;
    }

    public function setRequest_auth_username($request_auth_username) {
        $this->request_auth_username = $request_auth_username;
        return $this;
    }

    public function getRequest_auth_password() {
        return $this->request_auth_password;
    }

    public function setRequest_auth_password($request_auth_password) {
        $this->request_auth_password = $request_auth_password;
        return $this;
    }

    public function getResponse_status_code() {
        return $this->response_status_code;
    }
 
    public function getResponse_type() {
        return $this->response_type;
    }
  
    public function getResponse_content_length() {
        return $this->response_content_length;
    }
        
    public function getResponse_info() {
        return $this->response_info;
    }

    public function getResponse_data() {
        return $this->response_data;
    }
	
	public function getResponse_data_json_decode() 
	{
        return json_decode($this->response_data);
    }
	
    //Function Execute and Explain, use only for test and diagnostic            //Funcion para ejecutar y ver salida detalla, usar para prueba y diagnóstico
    public function executeAndExplain() 
    {
       $this->execute();
       echo '<!DOCTYPE html><html><head><title>RestClient | Execute and Explain </title>';
       echo '<style type="text/css"> body {background-color: #fff; color: #222; font-family: sans-serif;} pre {margin: 0; font-family: monospace;} table {border-collapse: collapse; border: 0; width: 900px; box-shadow: 1px 2px 3px #ccc;} .center {text-align: center;} .center table {margin: 1em auto; text-align: left;} .center th {text-align: center !important;} td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;} h1 {font-size: 150%;} h2 {font-size: 125%;} .p {text-align: left;} .e {background-color: #ccf; width: 150px; font-weight: bold;} .h {background-color: #99c; font-weight: bold;} .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;} .v i {color: #999;} img {float: right; border: 0;height: 45px;} hr {width: 934px; background-color: #ccc; border: 0; height: 1px;} </style>';
       echo '</head> <body> <div class="center"> <table> <tr class="h"><td> <a href="#"><img border="0" src="data:image/png;base64,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" alt="PHP logo" /></a><h1 class="p">PHP RestClient Version 3</h1></td></tr>';
       echo '</table> <table><tr> <td class="e" colspan="2" >General Options / Opciones Generales</td> </tr>
                <tr> <td class="e">User Agent</td>  <td class="v">'.$this->useragent.'</td></tr>
                <tr> <td class="e">Time Out</td>  <td class="v">'.$this->timeout.' secunds</td></tr>
                <tr> <td class="e">Cookie File</td>  <td class="v">'.$this->cookieFileLocation.'</td></tr>
                <tr> <td class="e">Referer Used</td>  <td class="v">'.$this->referer.'</td></tr>    
                <tr> <td class="e">Verific SSL</td>  <td class="v">'.$this->verificSSL.'</td></tr>
                <tr> <td class="e">Valid SSL</td>  <td class="v">'.(($this->validSSL == 0)?'Ok':'Fail').'</td></tr>
                <tr> <td class="e">Error</td>  <td class="v">'.$this->error.'</td></tr>    
             </table> <table><tr> <td class="e" colspan="2" >Request Options / Opciones de Petición</td> </tr>
                <tr> <td class="e">Request URL</td>  <td class="v">'.$this->request_url.'</td></tr> 
                <tr> <td class="e">Request Port</td>  <td class="v">'.$this->request_port.'</td></tr> 
                <tr> <td class="e">Request Method</td>  <td class="v">'.$this->request_method.'</td></tr> 
                <tr> <td class="e">Request Parameters</td>  <td class="v">';print_r($this->request_parameters);echo '</td></tr> 
                <tr> <td class="e">Request Length</td>  <td class="v">'.$this->request_length.'</td></tr> 
                <tr> <td class="e">Request Accept Type</td>  <td class="v">'.$this->request_accept_type.'</td></tr>
                <tr> <td class="e">Request Custom Headers</td>  <td class="v">';print_r($this->request_custom_headers); echo'</td></tr>
                <tr> <td class="e">Request Authentication</td>  <td class="v">'.$this->request_authentication_enabled.'</td></tr>
                <tr> <td class="e">Request Auth. User </td>  <td class="v">'.$this->request_auth_username.'</td></tr> 
                <tr> <td class="e">Request Auth. Password</td>  <td class="v">'.$this->request_auth_password.'</td></tr>    
             </table> <table><tr> <td class="e" colspan="2" >Response Options / Opciones de Respuesta</td> </tr>
                <tr> <td class="e">Response Status Code</td>  <td class="v">'.$this->response_status_code.'</td></tr> 
                <tr> <td class="e">Response Content Type</td>  <td class="v">'.$this->response_type.'</td></tr> 
                <tr> <td class="e">Response Content Length</td>  <td class="v">'.$this->response_content_length.'</td></tr> 
                <tr> <td class="e">Response Info</td>  <td class="v">';print_r($this->response_info); echo'</td></tr> 
                <tr> <td class="e">Response Data</td>  <td class="v">';var_dump($this->response_data); 
       echo '</td></tr></table> </body></html>';
    }
}
