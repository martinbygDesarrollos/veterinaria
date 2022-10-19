<?php
exit;
require_once "../src/config.php";
require_once '../src/connection/open_connection.php';
require_once '../src/utils/whatsapp.php';

//enviar el 7 de cada mes
//fechaUltimaCuota
$sql = 'SELECT idSocio, telefax, fechaUltimaCuota FROM `socios`
    WHERE `fechaUltimaCuota` IS NOT NULL AND `fechaUltimaCuota` <> "" AND estado = 1 AND tipo <> 1
    ORDER BY `socios`.`fechaUltimaCuota` ASC';


$database = new DataBase();
$whatsappClass = new whatsapp();
$deudoresList = $database->sendQuery($sql,array(), "LIST");
if ( $deudoresList->result == 2 ){
    foreach ($deudoresList->listResult as $client) {
        $cuota = substr($client['fechaUltimaCuota'], 4, 2).'/'.substr($client['fechaUltimaCuota'], 0, 4);
        $clientWppNumber = $client['telefax'];
        $message = "Estimado cliente, Veterinaria Nan le recuerda que posee saldos pendientes correspondientes al mes ".$cuota.".

Agradecemos pasar por el local a la brevedad.

Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.

Muchas gracias!";

        $path = "message/txt";
        $data = 'id='.WHATSAPP_API_USER.'&content='.$message.'&to='.$clientWppNumber.'&token='.TOKEN_API;
        $send = $whatsappClass->apiConection($path, $data);
        if ( $send->result != 2 ){
            echo "Cliente ".$client['idSocio'].' número '.$client['telefax'].': error: '.$send->error;
        }
    }
}



?>