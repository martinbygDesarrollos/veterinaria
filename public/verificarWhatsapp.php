<?php

require_once "../src/config.php";
require_once '../src/connection/open_connection.php';

echo "inicio: ".time();

$database = new DataBase();

var_dump("<pre>");
$query = 'SELECT idSocio, telefono, telefax FROM `socios` order by idSocio asc limit 10';
$datos = $database->sendQuery($query,array(), "LIST");

if ($datos->result == 2){

    //verificar si existe whatsapp en todos los numeros de la columna TELEFONO
    foreach ($datos->listResult as $key => $value) {

        $idsocio = $value["idSocio"];
        $tel = $value["telefono"];
        $fax = $value["telefax"];

        $tel = str_replace(' ', '', $tel);
        $fax = str_replace(' ', '', $fax);

        $passSimpleValidation = simpleValidation($tel);
        if ($passSimpleValidation){
            $exist = exist($tel);

            if(isset($exist)){
                if($exist->result = 2){
                    $query = 'UPDATE `socios` SET telefonoValido = 1 WHERE idSocio = '.$idsocio;
                    $database->sendQuery($query,array(), "BOOLE");
                }
            }else{
                echo "Falló la conexión al servidor.";
                exit;
            }
        }


        $passSimpleValidation = simpleValidation($fax);
        if ($passSimpleValidation){
            $exist = exist($fax);

            if(isset($exist)){
                if($exist->result = 2){
                    $query = 'UPDATE `socios` SET telefaxValido = 1 WHERE idSocio = '.$idsocio;
                    $database->sendQuery($query,array(), "BOOLE");
                }
            }else{
                echo "Falló la conexión al servidor.";
                exit;
            }
        }
    }
}

echo "fin: ".time();



function simpleValidation($number){
    // Expresiones regulares para los tres formatos
    $patron1 = '/^\d{8}$/';           // 8 dígitos
    $patron2 = '/^\d{9}$/';           // 9 dígitos
    $patron3 = '/^598\d{8}$/';        // comienza con 598 seguido de 8 dígitos

    // Verificar si el número coincide con alguno de los patrones
    if (preg_match($patron1, $number) || preg_match($patron2, $number) || preg_match($patron3, $number)) {
        return true;
    } else {
        return false;
    }


}

    function exist( $number ){
		$curl = curl_init();


		$options = array(
			CURLOPT_URL => URL_WHATSAPP_API."client/exist",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST"
		);

		curl_setopt_array($curl, $options);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'id='.WHATSAPP_EXIST_USER.'&num='.$number.'&token=45ek2wrhgr3rg33m');



		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response);
        return $response;

	}
?>