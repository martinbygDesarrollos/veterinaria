/* SE NOTIFICA LO SIGUIENTE
VACUNAS

(7 días antes del vencimiento y el dia que se vence)

(7 días antes)

Estimado cliente Veterinaria Nan le recuerda que la vacuna de su mascota—-- (nombre de la mascota) vencerá en 7 días. Recomendamos antes de cada vacuna desparasitarlo/a de 5 a 7 días previos a la vacuna.
Saluda atte, equipo médico de Veterinaria Nan.
Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.
Muchas gracias!


(el dia del vencimiento)

Estimado cliente Veterinaria Nan le recuerda que su mascota— (nombre de la mascota)-- tiene la vacuna –(anual, rabia, primer dosis,etc)--vencida.
Saluda atte, equipo médico de Veterinaria Nan.
Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.
Muchas gracias!



DESPARASITACIONES
(enviar el dia del vencimiento de la desparasitación) y si no fue que quede pendiente y se envíe una vez al mes nuevamente

Estimado cliente Veterinaria Nan le recuerda que su mascota —-- (nombre de la mascota) tiene el antiparasitario —------(nombre del antiparasitario) vencido.
Saluda atte, equipo médico de Veterinaria Nan.
Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.
Muchas gracias

*/
<?php
exit;
require_once "../src/config.php";
require_once '../src/connection/open_connection.php';
require_once '../src/utils/whatsapp.php';

//(7 días antes)enviar wpp si en 7 días se vence una vacuna

$today = date("Ymd");
$sevenDays = date('Ymd', strtotime("+ 7 days" , strtotime($today)));



$sql = 'SELECT m.nombre AS nomMascota, s.telefax, vm.idVacunaMascota FROM `vacunasmascota` AS vm
		LEFT JOIN `mascotas` AS m ON vm.idMascota = m.idMascota
        LEFT JOIN `mascotasocio` AS ms ON ms.idMascota = m.idMascota
        LEFT JOIN `socios` AS s ON ms.idSocio = s.idSocio
		WHERE vm.fechaProximaDosis = ? AND
		(m.fechaFallecimiento IS null OR m.fechaFallecimiento = "") AND
		s.estado = 1 AND
		vm.nombreVacuna like "%vacuna%" ';


$database = new DataBase();
$whatsappClass = new whatsapp();
$vacunasList = $database->sendQuery($sql,array('i',$sevenDays), "LIST");
if ( $vacunasList->result == 2 ){
	foreach ($vacunasList->listResult as $vacuna) {

		$messageSevenDays = "Estimado cliente Veterinaria Nan le recuerda que la vacuna de su mascota ".$vacuna['nomMascota']." vencerá en 7 días.

Recomendamos antes de cada vacuna desparasitarlo/a de 5 a 7 días previos a la vacuna. Saluda atte, equipo médico de Veterinaria Nan.

Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.

Muchas gracias!";

		$clientWppNumber = $vacuna['telefax'];

		$path = "message/txt";
		$data = 'id='.WHATSAPP_API_USER.'&content='.$messageSevenDays.'&to='.$clientWppNumber.'&token='.TOKEN_API;
		$send = $whatsappClass->apiConection($path, $data);
		if ( $send->result != 2 ){
			echo "Vacuna ".$vacuna['idVacunaMascota'].': error: '.$send->error;
		}
	}
}


$sql = 'SELECT m.nombre AS nomMascota, s.telefax, vm.idVacunaMascota, vm.nombreVacuna FROM `vacunasmascota` AS vm
		LEFT JOIN `mascotas` AS m ON vm.idMascota = m.idMascota
        LEFT JOIN `mascotasocio` AS ms ON ms.idMascota = m.idMascota
        LEFT JOIN `socios` AS s ON ms.idSocio = s.idSocio
		WHERE vm.fechaProximaDosis = ? AND
		(m.fechaFallecimiento IS null OR m.fechaFallecimiento = "") AND
		s.estado = 1 AND
		vm.nombreVacuna not like "%vacuna%" ';


$database = new DataBase();
$whatsappClass = new whatsapp();
$vacunasList = $database->sendQuery($sql,array('i',$sevenDays), "LIST");
if ( $vacunasList->result == 2 ){
	foreach ($vacunasList->listResult as $vacuna) {
		$messageSevenDays = "Estimado cliente Veterinaria Nan le recuerda que su mascota ".$vacuna['nomMascota']." tiene el antiparasitario ".$vacuna['nombreVacuna']." vencido.

Saluda atte, equipo médico de Veterinaria Nan.

Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.

Muchas gracias!";

		$clientWppNumber = $vacuna['telefax'];

		$path = "message/txt";
		$data = 'id='.WHATSAPP_API_USER.'&content='.$messageSevenDays.'&to='.$clientWppNumber.'&token='.TOKEN_API;
		$send = $whatsappClass->apiConection($path, $data);
		if ( $send->result != 2 ){
			echo "Vacuna ".$vacuna['idVacunaMascota'].': error: '.$send->error;
		}
	}
}

//(el dia del vencimiento)enviar wpp si hoy se vence una vacuna
$sql = 'SELECT m.nombre AS nomMascota, s.telefax, vm.idVacunaMascota, vm.nombreVacuna FROM `vacunasmascota` AS vm
		LEFT JOIN `mascotas` AS m ON vm.idMascota = m.idMascota
        LEFT JOIN `mascotasocio` AS ms ON ms.idMascota = m.idMascota
        LEFT JOIN `socios` AS s ON ms.idSocio = s.idSocio
		WHERE vm.fechaProximaDosis = ? AND
		(m.fechaFallecimiento IS null OR m.fechaFallecimiento = "") AND
		s.estado = 1';


$database = new DataBase();
$whatsappClass = new whatsapp();
$vacunasTodayList = $database->sendQuery($sql,array('i',$today), "LIST");

if ( $vacunasTodayList->result == 2 ){

	foreach ($vacunasTodayList->listResult as $value) {
		$messageToday = "Estimado cliente Veterinaria Nan le recuerda que su mascota *".$value['nomMascota']."* tiene *".$value['nombreVacuna']." vencida/o*.

Saluda atte, equipo médico de Veterinaria Nan.

Este mensaje es automático. Por cualquier consulta comunicarse al 472- 34039 en nuestros horarios de atención.

Muchas gracias!";


		$clientWppNumber = $vacuna['telefax'];

		$path = "message/txt";
		$data = 'id='.WHATSAPP_API_USER.'&content='.$messageToday.'&to='.$clientWppNumber.'&token='.TOKEN_API;
		$send = $whatsappClass->apiConection($path, $data);
		if ( $send->result != 2 ){
			echo "Vacuna ".$vacuna['idVacunaMascota'].': error: '.$send->error;
		}
	}
}

?>