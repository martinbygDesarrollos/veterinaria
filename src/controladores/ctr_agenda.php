<?php

require_once '../src/clases/agenda.php';
require_once '../src/controladores/ctr_usuarios.php';
require_once '../src/controladores/ctr_mascotas.php';


class ctr_agenda {


	public function getCirugiasByDay($day){
		$response = new \stdClass();
		$calendarClass = new agenda();
		$formatClass = new formats();
		$usersController = new ctr_usuarios();
		$petController = new ctr_mascotas();

		$day = str_replace("-", "", $day);

		if ($day == "") {
			$response->result = 1;
			$response->message = no_select_data." Ingrese día.";
			return $response;
		}


		$response = $calendarClass->getCirugiasByDay($day);
		if ( $response->result == 2 ){
			foreach ($response->listResult as $value) {
				//formato del dato de la hora para mostrar en el input
				$hora = $formatClass->formatStringToTime(substr($value['fechaHora'], 8, 4));
				$index = array_search($value, $response->listResult);
				$response->listResult[$index]['hora'] = $hora;

				if (!$value['descripcion'])
					$response->listResult[$index]['descripcion'] = "";

				$response->listResult[$index]['socio'] = null;
				if ( isset($value['idSocio'])){
					if ( ctype_digit($value['idSocio']) ){
						$resultClient = $usersController->getSocio($value['idSocio']);
						if ( $resultClient->result == 2 ){
							$response->listResult[$index]['socio'] = $resultClient->socio;
							$response->listResult[$index]['nombreCliente'] = $resultClient->socio->idSocio." - ".$resultClient->socio->nombre;
						}
					}else
						$response->listResult[$index]['nombreCliente'] = $value['idSocio'];
				}else $response->listResult[$index]['nombreCliente'] = "";


				$response->listResult[$index]['mascota'] = null;
				if ( isset($value['idMascota'])){
					if ( ctype_digit($value['idMascota']) ){
						$resultPet = $petController->getMascota($value['idMascota']);
						//var_dump($resultPet);exit;
						if ( $resultPet->result == 2 ){
							$response->listResult[$index]['mascota'] = $resultPet->objectResult;
							$response->listResult[$index]['nombreMascota'] = $resultPet->objectResult->idMascota." - ".$resultPet->objectResult->nombre;
						}
					}else
						$response->listResult[$index]['nombreMascota'] = $value['idMascota'];
				}else $response->listResult[$index]['nombreMascota'] = "";
			}
		}else if ( $response->result == 1 ){
			$response->result = 2;
			$response->listResult = array();
		}

		return $response;
	}

	public function modifyNewEventCirugias( $data, $idUser ){
		$response = new \stdClass();
		$calendarClass = new agenda();

		$idAgenda = $data['id'];
		$time = isset($data['fechaHora']) && $data['fechaHora'] != "" ? $data['fechaHora'] : null;
		$event = isset($data['descripcion']) && $data['descripcion'] != "" ? $data['descripcion'] : null;
		$client = isset($data['cliente']) && $data['cliente'] != "" ? $data['cliente'] : null;
		$pet = isset($data['mascota']) && $data['mascota'] != "" ? $data['mascota'] : null;

		$response = $calendarClass->modifyNewEventCirugias($idAgenda, $idUser, $time, $event, $client, $pet );
		return $response;
	}

	public function saveNewEventCirugias( $data, $idUser ){
		$response = new \stdClass();
		$calendarClass = new agenda();

		$time = isset($data['fechaHora']) && $data['fechaHora'] != "" ? $data['fechaHora'] : null;
		$event = isset($data['descripcion']) && $data['descripcion'] != "" ? $data['descripcion'] : null;
		$client = isset($data['cliente']) && $data['cliente'] != "" ? $data['cliente'] : null;
		$pet = isset($data['mascota']) && $data['mascota'] != "" ? $data['mascota'] : null;

		$response = $calendarClass->saveNewEventCirugias( $idUser, $time, $event, $client, $pet );
		return $response;
	}
}
?>