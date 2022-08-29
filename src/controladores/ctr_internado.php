<?php

require_once '../src/clases/internado.php';
require_once '../src/clases/socios.php';

class ctr_internado {

	public function getHospitalizedPet( $hospitalizedPlace ){
		$response = new \stdClass();
		$hospitalizedPetClass = new internado();
		$clientClass = new socios();

		$response = $hospitalizedPetClass->getAllHospitalizedPet($hospitalizedPlace);
		if ( $response->result == 2 ){
			foreach ($response->listResult as $key => $value) {
				$response->listResult[$key]['deudor'] = $clientClass->socioDeudor($value['fechaUltimaCuota'])->deudor;
			}
		}



		return $response;
	}

}
?>