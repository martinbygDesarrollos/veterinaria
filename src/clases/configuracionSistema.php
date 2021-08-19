<?php

class configuracionSistema{

	public function updatePlazoDeuda($plazoDeuda){
		$query = DB::conexion()->prepare("UPDATE cuota SET plazoDeuda = ? WHERE id = 1");
		$query->bind_param('i', $plazoDeuda);
		return $query->execute();
	}

	public function setNuevaCuota($cuotaUna, $cuotaDos, $cuotaExtra){
		$query = DB::conexion()->prepare("UPDATE cuota SET cuotaUno = ?, cuotaDos = ?, cuotaExtra = ? WHERE id = 1");
		$query->bind_param('iii', $cuotaUna, $cuotaDos, $cuotaExtra);
		return $query->execute();
	}

	public function getQuota(){
		return DataBase::sendQuery("SELECT * FROM cuota WHERE id = 1", null, "OBJECT");
	}

	public function getQuotaSocio($cantMascotas){
		$response = new \stdClass();

		$response->result = 1;
		$response->quota = 0;

		$responseGetQuota = configuracionSistema::getQuota();
		if($responseGetQuota->result == 2){
			if($cantMascotas == 1){
				$response->result = 2;
				$response->quota = $responseGetQuota->objectResult->cuotaUno;
			}else if($cantMascotas == 2){
				$response->result = 2;
				$response->quota = $responseGetQuota->objectResult->cuotaDos;
			}else if($cantMascotas > 2){
				$response->result = 2;
				$response->quota = ($responseGetQuota->objectResult->cuotaDos + (($cantMascotas - 2) * $responseGetQuota->objectResult->cuotaExtra));
			}
		}else return $responseGetQuota;

		return $response;
	}
}