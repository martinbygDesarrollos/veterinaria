<?php

require_once '../src/clases/agenda.php';
require_once '../src/clases/agenda.php';

class ctr_agenda {


	public function getCirugiasByDay($day){
		$response = new \stdClass();
		$calendarClass = new agenda();
		$formatClass = new formats();

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