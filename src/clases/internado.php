<?php
class internado{


	public function getAllHospitalizedPet($place, $lastId){

		$where = " WHERE m.internado is not null ";
		if ($place == "vet"){
			$where = " WHERE m.internado = 'vet' ";
		}else if ($place == "casa"){
			$where = " WHERE m.internado = 'casa' ";
		}

		$lastId = $lastId +1;

		$database = new DataBase();
		return $database->sendQuery("SELECT m.*, s.idSocio, s.nombre as nomCliente, s.fechaUltimaCuota, s.telefax, s.tipo FROM `mascotas` as m
		LEFT JOIN mascotasocio AS ms ON m.idMascota = ms.idMascota
		LEFT JOIN socios AS s ON ms.idSocio = s.idSocio ".$where." AND m.idMascota < ? AND m.fechaFallecimiento IS null
		ORDER BY m.`idMascota` DESC LIMIT 30",array('i',$lastId), "LIST");

	}


}

?>