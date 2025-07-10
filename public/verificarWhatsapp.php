<?php

require_once "../src/config.php";
require_once '../src/connection/open_connection.php';

$inicio = microtime(true);

$errores = 0; // se enviaron al serv pero no respondio u otro error
$verificados = 0; //todos los que se enviaron al serv y puede o no tener wp
$total_ver = 0; //todo lo que se envia al servidor para saber si tiene wp
$total = 0; //todos los registros que llegan de la base de datos

$database = new DataBase();

$query = 'SELECT idSocio, telefono, telefax FROM `socios` WHERE telefonoValido = 0 OR telefaxValido = 0';
$datos = $database->sendQuery($query,array(), "LIST");

if ($datos->result == 2){
    $total = count($datos->listResult);

    //verificar si existe whatsapp en todos los numeros de la columna TELEFONO
    foreach ($datos->listResult as $key => $value) {

        $idsocio = $value["idSocio"];
        $tel = $value["telefono"];
        $fax = $value["telefax"];

        $tel = str_replace(' ', '', $tel);
        $fax = str_replace(' ', '', $fax);

        $telSimpleValidation = simpleValidation($tel);
        if ($telSimpleValidation){
            $telFormated = transform($tel);
            $exist = exist($telFormated);
            $total_ver ++;

            if(!$exist){
                $errores ++;
            }

            if(isset($exist)){
                $verificados ++;
                if($exist->result == 2){
                    $query = 'UPDATE `socios` SET telefonoValido = 1 WHERE idSocio = '.$idsocio;
                    $database->sendQuery($query,array(), "BOOLE");
                }
            }else{
                $errores ++;
            }
        }


        $passSimpleValidation = simpleValidation($fax);
        if ($passSimpleValidation){
            $faxFormated = transform($fax);
            $exist = exist($faxFormated);
            $total_ver ++;

            if(!$exist){
                $errores ++;
            }

            if(isset($exist)){
                $verificados ++;
                if($exist->result == 2){
                    $query = 'UPDATE `socios` SET telefaxValido = 1 WHERE idSocio = '.$idsocio;
                    $database->sendQuery($query,array(), "BOOLE");
                }
            }else{
                $errores ++;
            }
        }
    }
}


echo "todos los registros: $total";
echo "\nprocesados: $total_ver";
echo "\nverificados: $verificados";
echo "\nerrores: $errores";
$tiempo = microtime(true) - $inicio;
echo "\ntiempo en procesar: ". number_format($tiempo, 2) ." segundos";



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

function transform($number) {

    // Expresiones regulares para los tres formatos
    $patron1 = '/^\d{8}$/';           // 8 dígitos
    $patron2 = '/^\d{9}$/';           // 9 dígitos
    $patron3 = '/^598\d{8}$/';        // comienza con 598 seguido de 8 dígitos

    // Verificar si el número coincide con alguno de los patrones
    if (preg_match($patron1, $number)) {
        // Agregar prefijo 598 al número de 8 dígitos
        return '598' . $number;
    } elseif (preg_match($patron2, $number)) {
        // Reemplazar el primer dígito (asumido como 0) por 598
        if ($number[0] == '0') {
            return '598' . substr($number, 1);
        } else {
            return false; // no comienza con 0, no válido para este patrón
        }
    } elseif (preg_match($patron3, $number)) {
        // Ya está en el formato correcto
        return $number;
    } else {
        // No coincide con ningún patrón
        return false;
    }
}

function exist( $number ){

    $curl = curl_init();

    $data = 'id='.WHATSAPP_EXIST_USER.'&num='.$number.'&token=45ek2wrhgr3rg33m';
    $options = array(
        CURLOPT_URL => URL_WHATSAPP_EXIST."client/exist",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST"
    );

    curl_setopt_array($curl, $options);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded'
    ));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($curl);

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
?>