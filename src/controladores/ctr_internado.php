<?php

require_once '../src/clases/internado.php';
require_once '../src/clases/socios.php';

class ctr_internado {

	public function getHospitalizedPet( $hospitalizedPlace, $lastId ){
		$response = new \stdClass();
		$hospitalizedPetClass = new internado();
		$clientClass = new socios();
		$mascotaClass = new mascotas();

		if ( $lastId == 0 ){
			$lastId = $mascotaClass->getMascotaMaxId()->objectResult->idMaximo;
		}


		$response = $hospitalizedPetClass->getAllHospitalizedPet($hospitalizedPlace, $lastId);
		if ( $response->result == 2 ){


			$newLastId = $lastId;
			foreach ($response->listResult as $key => $value) {


				if($newLastId > $value["idMascota"])
					$newLastId = $value["idMascota"];


				$response->listResult[$key]['deudor'] = $clientClass->socioDeudor($value['fechaUltimaCuota'])->deudor;
			}

			$response->lastId = $newLastId;

		}



		return $response;
	}




	public function getInternacionDocument(){

		$response = new \stdClass();
		$hospitalizedPetClass = new internado();

		return $hospitalizedPetClass->getAllHospitalizedPetDocument();

	}

}
?>