<?php

class whatsapp{

	public function apiConection($path, $data){
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => URL_WHATSAPP_API.$path,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 90,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $data.'&id='.WHATSAPP_API_USER.'&token='.TOKEN_API,
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/x-www-form-urlencoded'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return json_decode($response);
	}


	public function nuevoQr(){

		$response = $this->send_curl("GET",'getqr/'.WHATSAPP_API_USER, null);
		if ($response->result == 2)
			$response->message = "QR enviado.";


		return $response;
	}

	public function exist(){

		return $this->send_curl("GET",'getqr/'.WHATSAPP_API_USER, null);

	}


	public function enviarWhatsapp($url, $content, $phone){

		$whatsappClass = new whatsapp();

		if ( !isset($phone) || strcmp($phone,"")== 0 ){
			$response = new stdClass();
			$response->result = 1;
			return json_encode($response);
		}else{

			//$exist = $whatsappClass->apiConection("client/exist", "num=".$phone);
			//if ( $exist && $exist->result == 2 && strcmp($exist->message, "ok") == 0 ){

				$data = 'id='.WHATSAPP_API_USER.'&content='.$content.'&to='.$phone.'&token=45ek2wrhgr3rg33m';
				$response = $this->send_curl("POST",$url, $data);
				return $response;


			/*}else{
				$response = new stdClass();
				$response->result = 1;
				return json_encode($response);
			}*/


		} //cerrando el else
	}


	public function sessionStart(){
		$whatsapp = new whatsapp();

		$response = $this->send_curl("POST",'start-session/'.WHATSAPP_API_USER, null);

		if($response->result == 2 ){
			return $whatsapp->nuevoQr();
		}else return $response;

	}

	public function verifyStatus(){
		$whatsapp = new whatsapp();
		
		$response = $this->send_curl("GET",'session-state/'.WHATSAPP_API_USER, null);

		if($response->result == "2" && $response->message == "TIMEOUT"){
			return $whatsapp->sessionStart();
		}
		else if($response->result == "2" && ($response->message == "DISCONNECTED" || $response->message == "QR_GENERATED")){
			return $whatsapp->nuevoQr();
		}
		return $response;

	}



	public function send_curl( $method, $url, $data){
		$curl = curl_init();
		error_log("\n".date("ymdHis").": send_curl $method, $url", 3, "./../logs/whatsapp".date("ymd").".log");


		$options = array(
			CURLOPT_URL => URL_WHATSAPP_API.$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method
		);

		curl_setopt_array($curl, $options);

		if($method == "POST"){
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/x-www-form-urlencoded',
				'Authorization: Bearer '.TOKEN_API
			));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		}elseif ($method == "GET"){
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer '.TOKEN_API
			));
		}



		$response = curl_exec($curl);
		error_log("\n".date("ymdHis").": send_curl respuesta: $response", 3, "./../logs/whatsapp".date("ymd").".log");

		curl_close($curl);

		$response = json_decode($response);

		if($response){
			return $response;
		}
		else{
			$res = new stdClass();
			$res->result = 0;
			$res->message = "No se obtuvo respuesta del servidor.";
			return $res;
		}
	}


	

	/*public function exist($number){


		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => URL_WHATSAPP_EXIST.'client/exist',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => 'id='.WHATSAPP_EXIST_USER.'&num='.$number.'&token=45ek2wrhgr3rg33m',
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/x-www-form-urlencoded'
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		echo $response;

	}*/
}

