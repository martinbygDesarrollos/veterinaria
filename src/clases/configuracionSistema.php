<?php

class configuracionSistema{

	public function updateQuotaSistema($cuotaUna, $cuotaDos, $cuotaExtra, $plazoDeuda){
		return DataBase::sendQuery("UPDATE cuota SET cuotaUno = ?, cuotaDos = ?, cuotaExtra = ?, plazoDeuda = ? WHERE id = 1", array('iiii', $cuotaUna, $cuotaDos, $cuotaExtra, $plazoDeuda), "BOOLE");
	}

	public function getQuota(){
		$responseQuery =  DataBase::sendQuery("SELECT * FROM cuota WHERE id = 1", null, "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "La información de la cuota y el plazo de deuda no fueron obtenidas.";
		return $responseQuery;
	}

	public function getQuotaSocio($cantMascotas, $tipoCliente){
		$response = new \stdClass();

		$response->result = 1;
		$response->quota = 0;
		if ( $tipoCliente == 1 ){
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
		}else{
			$response->result = 2;
			$response->quota = 0;
		}

		return $response;
	}
}