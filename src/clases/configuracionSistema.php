<?php

class configuracionSistema{

	public function setNuevaCuota($cuotaUna, $cuotaDos, $cuotaExtra){
		$query = DB::conexion()->prepare("UPDATE cuota SET cuotaUno = ?, cuotaDos = ?, cuotaExtra = ? WHERE id = 1");
		$query->bind_param('iii', $cuotaUna, $cuotaDos, $cuotaExtra);
		return $query->execute();
	}

	public function getCuota(){
		$query = DB::conexion()->prepare("SELECT * FROM cuota WHERE id = 1");
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function getCostoCuota($cantMascotas){
		$tarifa = configuracionSistema::getCuota();
		if($cantMascotas == 0){
			return 0;
		}else if($cantMascotas == 1){
			return $tarifa->cuotaUno;
		}else if($cantMascotas == 2){
			return $tarifa->cuotaDos;
		}else if($cantMascotas > 2){
			return ($tarifa->cuotaDos + ($cantMascotas * $tarifa->cuotaExtra));
		}
	}

}