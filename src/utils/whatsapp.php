<?php

class whatsapp{

	public function apiConection($path, $data){
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => URL_WHATSAPP_API.$path,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $data,//'id=1&content=mensaje%200919&to=92459188&token=45ek2wrhgr3rg33m',
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/x-www-form-urlencoded'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return json_decode($response);
	}


	public function nuevoQr($path, $data){
		$curl = curl_init();

		//$data = 'id=2&token=45ek2wrhgr3rg33m';


		curl_setopt_array($curl, array(
		  CURLOPT_URL => URL_WHATSAPP_API.$path,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_POSTFIELDS => $data,
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/x-www-form-urlencoded'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;
		//return json_decode($response);
	}
}

?>