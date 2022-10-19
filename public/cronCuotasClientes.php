<?php
exit;
/* SE NOTIFICA LO SIGUIENTE
CUOTAS
¿Cuándo enviar?
 (El 5 de cada mes vencido, osea si se vence el mes de agosto le llegue el msj el 5 de Setiembre como que tiene cuotas vencidas y así sucesivamente, hasta que se ponga al dia)

Estimado socio, Veterinaria Nan le recuerda que posee cuota/s vencidas.
Al tercer mes de vencido, perderá todos los beneficios de socio, los cuales podrá recuperar al momento de cancelar la deuda.
Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.
Muchas gracias!
*/

require_once "../src/config.php";
require_once '../src/connection/open_connection.php';
require_once '../src/utils/whatsapp.php';

//enviar el 7 de cada mes
//fechaUltimaCuota
$sql = 'SELECT idSocio, telefax, fechaUltimaCuota FROM `socios`
    WHERE `fechaUltimaCuota` is not null and `fechaUltimaCuota` <> "" and estado = 1 and tipo = 1
    ORDER BY `socios`.`fechaUltimaCuota` ASC';


$database = new DataBase();
$whatsappClass = new whatsapp();
$deudoresList = $database->sendQuery($sql,array(), "LIST");
var_dump("<pre>",$deudoresList);exit;
if ( $deudoresList->result == 2 ){
    foreach ($deudoresList->listResult as $client) {
        $cuota = substr($client['fechaUltimaCuota'], 4, 2).'/'.substr($client['fechaUltimaCuota'], 0, 4);
        $clientWppNumber = $client['telefax'];
        $message = "Estimado socio, Veterinaria Nan le recuerda que posee cuota/s vencidas.

Al tercer mes de vencido, perderá todos los beneficios de socio, los cuales podrá recuperar al momento de cancelar la deuda.

Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.

Muchas gracias!";

        $path = "message/txt";
        $data = 'id='.WHATSAPP_API_USER.'&content='.$message.'&to='.$clientWppNumber.'&token='.TOKEN_API;
        $send = $whatsappClass->apiConection($path, $data);
        if ( $send->result != 2 ){
            echo "Socio ".$client['idSocio'].' número '.$client['telefax'].': error: '.$send->error;
        }
    }
}



?>