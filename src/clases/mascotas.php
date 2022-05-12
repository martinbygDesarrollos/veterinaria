<?php

require_once "../src/utils/fechas.php";

class mascotas{
 //0 INACTIVA 1 ACTIVA 2 PENDIENTE
	//MACHO 1 HEMBRA 0

	public function getCantMascotas($idSocio){
		return DataBase::sendQuery("SELECT COUNT(*) AS cantMascotas FROM mascotasocio WHERE idSocio = ?", array('i', $idSocio), "OBJECT");
	}

	public function deleteVinculoMascota($idMascota){
		return DataBase::sendQuery("DELETE FROM mascotasocio WHERE idMascota = ?", array('i', $idMascota), "BOOLE");
	}

	public function getMascotaSocio($idMascota){
		$responseQuery = DataBase::sendQuery("SELECT * FROM mascotasocio WHERE idMascota = ?", array('i', $idMascota), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontró un socio vinculado a la mascota seleccionada.";

		return $responseQuery;
	}

	public function updateStateMascotas($state){
		return DataBase::sendQuery("UPDATE mascotas SET estado = ? WHERE idMascota IN (SELECT idMascota FROM mascotasocio WHERE idSocio IN (SELECT idSocio FROM socios WHERE estado = ?))", array('ii', $state, $state), "BOOLE");
	}

	public function mascotaIsVinculada($idMascota){
		$responseQuery =  DataBase::sendQuery("SELECT * FROM mascotasocio WHERE idMascota = ?", array('i', $idMascota), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "La mascota no tiene un socio vinculado.";

		return $responseQuery;
	}

	public function getMascotasNoSocio($textToSearch){
		$responseQuery  = DataBase::sendQuery("SELECT * FROM mascotas WHERE nombre LIKE '" . $textToSearch . "%' AND idMascota NOT IN (SELECT idMascota FROM mascotasocio) LIMIT 5", null, "LIST");
		if($responseQuery->result == 2){
			$arrayResult = array();
			$noData = "No especificado";
			foreach ($responseQuery->listResult as $key => $row) {
				if(is_null($row['especie']) || strlen($row['especie']) < 2)
					$row['especie'] = $noData;

				if(is_null($row['raza']) || strlen($row['raza']) < 2)
					$row['raza'] = $noData;

				if($row['sexo'] == 0 )
					$row['sexo'] = "Hembra";
				else
					$row['sexo'] = "Macho";

				if(!is_null($row['fechaNacimiento']) && strlen($row['fechaNacimiento']) == 8)
					$row['fechaNacimiento'] = fechas::dateToFormatBar($row['fechaNacimiento']);
				else
					$row['fechaNacimiento'] = $noData;

				$arrayResult[] = $row;
			}
			$responseQuery->listResult = $arrayResult;
		}else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron mascotas con la sugerencia de texto ingresada.";

		return $responseQuery;
	}

	public function getSocioIdByMascota($idMascota){
		$responseQuery = DataBase::sendQuery("SELECT * FROM mascotasocio WHERE idMascota = ?", array('i', $idMascota), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontro un socio vinculado a la mascota seleccionada.";

		return $responseQuery;
	}

	public function getSocioActivePets($idSocio){
		$responseQuery = DataBase::sendQuery("SELECT * FROM mascotas WHERE estado = 1 AND idMascota IN (SELECT idMascota AS cantMascotas FROM mascotasocio WHERE idSocio = ?)", array('i', $idSocio), "LIST");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se econtraron mascotas vinculadas al socio seleccionado.";

		return $responseQuery;
	}

	public function getTotMascotas(){
		$query = DB::conexion()->prepare("SELECT * FROM mascotas");
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$row['fechaNacimiento'] = fechas::parceFechaFormatDMA($row['fechaNacimiento'], '/');
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}

	public function getMascotaMaxId(){
		$responseQuery = DataBase::sendQuery("SELECT MAX(idMascota) AS idMaximo FROM mascotas", null, "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontraron mascotas registradas en la base de datos.";

		return $responseQuery;
	}

	public function getMascotas($lastId, $textToSearch, $stateMascota){
		if($lastId == 0){
			$responseGetMaxID = mascotas::getMascotaMaxId();
			if($responseGetMaxID->result == 2)
				$lastId = $responseGetMaxID->objectResult->idMaximo + 1;
			else
				return $responseGetMaxID;
		}

		$sqlToSearch = "";
		if(!is_null($textToSearch))
			$sqlToSearch = " AND m.nombre LIKE '". $textToSearch ."%' ";

		$select = "SELECT m.*, ms.idSocio, s.nombre AS nombreSocio, s.fechaUltimoPago, s.fechaUltimaCuota FROM mascotas AS m ";
		$join = " LEFT JOIN mascotasocio AS ms ON m.idMascota = ms.idMascota
				LEFT JOIN socios AS s ON s.idSocio = ms.idSocio ";
		$where = " WHERE m.estado = ? AND m.idMascota < ? ";
		$orderAndList = " ORDER BY m.idMascota DESC LIMIT 14 ";

		$responseQuery = DataBase::sendQuery($select . $join . $where . $sqlToSearch . $orderAndList, array('ii', $stateMascota, $lastId), "LIST");
		if($responseQuery->result == 2){
			$newLastID = $lastId;
			$arrayResult = array();
			$noData = "No especificado";
			foreach ($responseQuery->listResult as $key => $row) {
				if($newLastID > $row['idMascota']) $newLastID = $row['idMascota'];

				if(is_null($row['especie']) || strlen($row['especie']) < 2)
					$row['especie'] = $noData;

				if(is_null($row['raza']) || strlen($row['raza']) < 2)
					$row['raza'] = $noData;

				if($row['sexo'] == 0 )
					$row['sexo'] = "Hembra";
				else
					$row['sexo'] = "Macho";

				if(!is_null($row['fechaNacimiento']) && strlen($row['fechaNacimiento']) == 8)
					$row['fechaNacimiento'] = fechas::dateToFormatBar($row['fechaNacimiento']);
				else
					$row['fechaNacimiento'] = $noData;


				//socio deudor
				$responseDeudor = socios::socioDeudor($row['fechaUltimaCuota']);
				$row['socioDeudor'] = $responseDeudor->deudor;

				$arrayResult[] = $row;
			}
			$responseQuery->lastId = $newLastID;
			$responseQuery->listResult = $arrayResult;
		}elseif($responseQuery->result == 1) $responseQuery->message = "No se encontraron mascotas que listar.";

		return $responseQuery;
	}

	public function getMascotaToEdit($idMascota){
		$responseQuery = mascotas::getMascota($idMascota);
		if($responseQuery->result == 2){
			if(!is_null($responseQuery->objectResult->fechaNacimiento))
				$responseQuery->objectResult->fechaNacimiento = fechas::dateToFormatHTML($responseQuery->objectResult->fechaNacimiento);
		}

		return $responseQuery;
	}

	public function getMascota($idMascota){
		$responseQuery = DataBase::sendQuery("SELECT * FROM mascotas WHERE idMascota = ?", array('i', $idMascota), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontro una mascota con el identificador seleccionado.";

		return $responseQuery;
	}

	public function getMascotaToShow($idMascota){
		$responseQuery = DataBase::sendQuery("SELECT * FROM mascotas WHERE idMascota = ?", array('i', $idMascota), "OBJECT");
		if($responseQuery->result == 2){
			$responseQuery->objectResult = mascotas::getFormatMascota($responseQuery->objectResult);
		}elseif($responseQuery->result == 1)$responseQuery->message = "No se encontro una mascota con el identificador seleccionado.";

		return $responseQuery;
	}

	public function getFormatMascota($mascota){
		$noData = "No especificado";

		if(is_null($mascota->raza) || strlen($mascota->raza) < 1)
			$mascota->raza = $noData;

		if(is_null($mascota->especie) || strlen($mascota->especie) < 1)
			$mascota->especie = $noData;

		if($mascota->sexo == 0) $mascota->sexo = "Hembra";
		else $mascota->sexo = "Macho";

		if($mascota->pedigree == 0) $mascota->pedigree = "No";
		else $mascota->pedigree = "Si";

		if(is_null($mascota->pelo) || strlen($mascota->pelo) < 1)
			$mascota->pelo = $noData;

		if(is_null($mascota->color) || strlen($mascota->color) < 1)
			$mascota->color = $noData;

		if(is_null($mascota->chip) || strlen($mascota->chip) < 1)
			$mascota->chip = $noData;

		if(is_null($mascota->observaciones) || strlen($mascota->observaciones) < 1)
			$mascota->observaciones = $noData;

		if(!is_null($mascota->fechaNacimiento) && strlen($mascota->fechaNacimiento) == 8)
			$mascota->fechaNacimiento = fechas::dateToFormatBar($mascota->fechaNacimiento);
		else $mascota->fechaNacimiento = $noData;

		return $mascota;
	}

	public function insertMascota($nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $estado, $pelo, $chip, $observaciones){
		return DataBase::sendQuery("INSERT INTO mascotas(nombre, especie, raza, sexo, color, pedigree, fechaNacimiento, estado, pelo, chip, observaciones) VALUES(?,?,?,?,?,?,?,?,?,?,?)", array('sssisiiisss', $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $estado, $pelo, $chip, $observaciones), "BOOLE");
	}

	public function updateMascota($idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones){
		return DataBase::sendQuery("UPDATE mascotas SET nombre = ?, especie = ?, raza = ?, sexo = ?, color = ?, pedigree = ?, fechaNacimiento = ?, pelo = ?, chip = ? , observaciones = ? WHERE idMascota = ?", array('sssisiisssi', $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones, $idMascota), "BOOLE");
	}

	public function vincularMascotaSocio($idSocio, $idMascota, $fechaCambio){
		return DataBase::sendQuery("INSERT INTO mascotasocio (idSocio, idMascota, fechaCambio) VALUES (?,?,?)", array('iii', $idSocio, $idMascota, $fechaCambio), "BOOLE");
	}

	public function activarDesactivarMascota($idMascota, $estado){
		return DataBase::sendQuery("UPDATE mascotas SET estado = ? WHERE idMascota = ?", array('ii', $estado, $idMascota), "BOOLE");
	}

	public function changeStateMascotas($idSocio){
		return DataBase::sendQuery("UPDATE mascotas SET estado = ? WHERE idMascota IN (SELECT idMascota FROM mascotasocio WHERE idSocio = ? )", array('ii', 0, $idSocio), "BOOLE");
	}

	public function getMascotasSocio($idSocio){
		$responseQuery = DataBase::sendQuery("SELECT * FROM mascotas WHERE idMascota IN (SELECT idMascota FROM mascotasocio WHERE idSocio = ? )", array('i', $idSocio), "LIST");
		if($responseQuery->result == 2){
			$arrayResult = array();
			foreach ($responseQuery->listResult as $key => $row) {
				$row['fechaNacimiento'] = fechas::dateToFormatBar($row['fechaNacimiento'], "/");
				$arrayResult[] = $row;
			}
			$responseQuery->listResult = $arrayResult;
		}else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron mascotas para el socio seleccionado.";

		return $responseQuery;
	}

	public function getMascotaId($nombreMascota, $numSocio){
		return DataBase::sendQuery("SELECT MS.idMascota FROM socios AS S, mascotasocio AS MS, mascotas AS M WHERE S.idSocio = MS.idSocio AND MS.idMascota = M.idMascota AND S.idSocio = ? AND M.nombre = ? LIMIT 1", array('is', $numSocio, $nombreMascota), "OBJECT");
	}

	public function getMascotasIds(){
		return DataBase::sendQuery("SELECT MS.idMascota, M.nombre, S.numSocio FROM socios AS S, mascotasocio AS MS, mascotas AS M WHERE S.idSocio = MS.idSocio AND MS.idMascota = M.idMascota", null, "LIST");
	}
}