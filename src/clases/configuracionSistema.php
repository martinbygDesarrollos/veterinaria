<?php

class configuracionSistema{


	public function getTarifa(){
		$query = DB::conexion()->prepare("SELECT * FROM `cuota` WHERE id = 1");
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function getCostoCuota($cantMascotas){
		$tarifa = configuracionSistema::getTarifa();
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