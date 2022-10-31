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
