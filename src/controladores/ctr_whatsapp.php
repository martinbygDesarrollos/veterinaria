<?php

require_once '../src/utils/whatsapp.php';

class ctr_whatsapp {

    private $diccionario = [
		"QR code is not available in the current session state" => "Conectado",
		"CONNECTED" => "Conectado",
	];

    public function verifyStatus(){
        $whatsappClass = new whatsapp();
        $response = $whatsappClass->verifyStatus();

        if (array_key_exists($response->message, $this->diccionario) ){
            $_SESSION["w_status_m"] = $this->diccionario[$response->message];
            $response->message = $this->diccionario[$response->message];
        }
        else
            $_SESSION["w_status_m"] = $response->message;


        return $response;
    }
}
?>