<?php
require_once "../src/utils/fechas.php";

class historiales{

	//============================================================================================================
	//===========================================HISTORIAL CLINICO================================================
	//============================================================================================================

	public function agregarHistoriaClinica($idMascota, $fecha, $hora, $motivoConsulta, $diagnostico, $observaciones, $peso, $temperatura, $fc, $fr, $tllc){
		if(is_null($fecha) || $fecha == "")
			$fecha = date("Ymd");

		$idUserLogued = $_SESSION['ADMIN']['IDENTIFICADOR'];

		return DataBase::sendQuery("INSERT INTO historiasclinica(idMascota, fecha, hora, motivoConsulta, diagnostico, observaciones, peso, temperatura, fc, fr, tllc, idUsuario ) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)", array('iissssdddddi', $idMascota, $fecha, $hora, $motivoConsulta, $diagnostico, $observaciones, $peso, $temperatura, $fc, $fr, $tllc, $idUserLogued), "BOOLE");
	}

	public function modificarHistoriaClinica($idHistoriaClinica, $fecha, $hora, $motivoConsulta, $diagnostico, $observaciones, $peso, $temperatura, $fc, $fr, $tllc){
		if ( $peso == "" )
			$peso = null;

		if ( $temperatura == "" )
			$temperatura = null;

		if ( $diagnostico == "" )
			$diagnostico = null;

		if ( $observaciones == "" )
			$observaciones = null;

		if ( $fc == "" )
			$fc = null;

		if ( $fr == "" )
			$fr = null;

		if ( $tllc == "" )
			$tllc = null;

		if ( $hora == "" )
			$hora = null;

		$result = DataBase::sendQuery("UPDATE historiasclinica SET fecha = ?, hora = ?, motivoConsulta = ?, diagnostico = ?, observaciones = ?, peso = ?, temperatura = ?, fc = ?, fr = ?, tllc = ? WHERE idHistoriaClinica = ?", array('issssdddddi', $fecha, $hora, $motivoConsulta, $diagnostico, $observaciones, $peso, $temperatura, $fc, $fr, $tllc, $idHistoriaClinica), "BOOLE");
		return $result;
	}

	public function borrarHistoriaClinica($idHistoriaClinica){
		return DataBase::sendQuery("DELETE FROM historiasclinica WHERE idHistoriaClinica = ?", array('i', $idHistoriaClinica), "BOOLE");
	}

	public function getHistoriaClinicaToShow($idHistoriaClinica){
		$usersClass = new usuarios();

		$responseQuery = historiales::getHistoriaClinica($idHistoriaClinica);
		if($responseQuery->result == 2){
			$historia = $responseQuery->objectResult;

			if(!is_null($historia->fecha) && strlen($historia->fecha) == 8)
				$historia->fecha = fechas::dateToFormatBar($historia->fecha);

			if(is_null($historia->observaciones) ||  strlen($historia->observaciones) < 2)
				$historia->observaciones = "";

			if(is_null($historia->motivoConsulta) || strlen($historia->motivoConsulta) < 2)
				$historia->motivoConsulta = "";

			if(is_null($historia->diagnostico) ||  strlen($historia->diagnostico) < 2)
				$historia->diagnostico = "";


			$historia->usuario = "";
			if ( $historia->idUsuario ){
				$user = $usersClass->getUser($historia->idUsuario);
				if ($user->result == 2){

					$historia->usuario = $user->objectResult->nombre;
				}
			}

			$responseQuery->objectResult = $historia;
		}

		return $responseQuery;
	}

	public function getHistoriaClinicaToEdit($idHistoriaClinica){
		$responseQuery = historiales::getHistoriaClinica($idHistoriaClinica);
		if($responseQuery->result == 2){
			$historia = $responseQuery->objectResult;

			if(!is_null($historia->fecha) && strlen($historia->fecha) == 8)
				$historia->fecha = fechas::dateToFormatHTML($historia->fecha);
			$responseQuery->objectResult = $historia;
		}

		return $responseQuery;
	}

	public function getHistoriaClinica($idHistoriaClinica){
		$responseQuery = DataBase::sendQuery("SELECT * FROM historiasclinica WHERE idHistoriaClinica = ?", array('i', $idHistoriaClinica), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontro una historia clínica con el identificador seleccionado.";
		else if ($responseQuery->result == 2){
			$responseQueryFiles = DataBase::sendQuery("SELECT idMedia, nombre FROM media WHERE categoria = ? AND idCategoria = ?", array('si', "historiasclinica", $idHistoriaClinica), "LIST");
			if ( $responseQueryFiles->result == 2 ){
				$responseQuery->objectResult->archivos = $responseQueryFiles->listResult;
			}else $responseQuery->objectResult->archivos = null;
		}
		return $responseQuery;
	}

	public function checkHayHistorialClinico($idMascota){
		$query =DB::conexion()->prepare("SELECT * FROM historiasclinica WHERE idMascota = ?");
		$query->bind_param('i', $idMascota);
		if($query->execute()){
			$response = $query->get_result();
			if($response->fetch_object())
				return true;
		}
		return false;
	}

	public function getHistoriaClinicaMascotaMaxID($idMascota){
		$responseQuery = DataBase::sendQuery("SELECT MAX(idHistoriaClinica) AS idMaximo FROM historiasclinica WHERE idMascota = ?", array('i', $idMascota), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontraron registros en el historial clinico.";

		return $responseQuery;
	}

	public function getHistoriaClinicaMascota($lastId, $idMascota){
		if($lastId == 0){
			$responseGetMaxId = historiales::getHistoriaClinicaMascotaMaxID($idMascota);
			if($responseGetMaxId->result == 2)
				$lastId = $responseGetMaxId->objectResult->idMaximo + 1;
			else return $responseGetMaxId;
		}

		$responseQuery = DataBase::sendQuery("SELECT * FROM historiasclinica WHERE idMascota=? AND idHistoriaClinica< ? ORDER BY idHistoriaClinica DESC LIMIT 14", array('ii', $idMascota, $lastId), "LIST");
		if($responseQuery->result == 2){
			$arrayResult = array();
			$newLastId = $lastId;
			//$noData = "No especificado";
			$noData = "";
			foreach ($responseQuery->listResult as $key => $row) {
				if($newLastId > $row['idHistoriaClinica']) $newLastId = $row['idHistoriaClinica'];

				if(!is_null($row['fecha']) && strlen($row['fecha']) == 8)
					$row['fecha'] = fechas::dateToFormatBar($row['fecha']);
				else $row['fecha'] = $noData;

				if(is_null($row['motivoConsulta']) || strlen($row['motivoConsulta']) < 4)
					$row['motivoConsulta'] = $noData;

				if(is_null($row['diagnostico']) || strlen($row['diagnostico']) < 4)
					$row['diagnostico'] = $noData;

				if(is_null($row['observaciones']) || strlen($row['observaciones']) < 4)
					$row['observaciones'] = $noData;

				$arrayResult[] = $row;
			}

			$responseQuery->listResult = $arrayResult;
			$responseQuery->lastId = $newLastId;
		}else if($responseQuery->result == 1) $responseQuery->message = "No se obtuvo la lista de registros del historial clinico de esta mascota.";

		return $responseQuery;
	}
	//============================================================================================================
	//============================================================================================================
	//============================================================================================================

	//============================================================================================================
	//===============================================HISTORIAL SOCIO==============================================
	//============================================================================================================

	public function getMaxIdHistorialSocios(){
		$responseQuery = DataBase::sendQuery("SELECT MAX(idHistorialSocio) AS maxID FROM historialsocios", null, "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontraron registros en el historial de socios.";

		return $responseQuery;
	}

	public function getListHistorialSocio($lastId, $idSocio){
		if($lastId == 0){
			$responseGetMaxId = historiales::getMaxIdHistorialSocios();
			if($responseGetMaxId->result == 2)
				$lastId = $responseGetMaxId->objectResult->maxID + 1;
			else return $responseGetMaxId;
		}

		$responseQuery = DataBase::sendQuery("SELECT * FROM historialsocios WHERE idSocio = ? AND idHistorialSocio < ? ORDER BY idHistorialSocio DESC LIMIT 18", array('ii', $idSocio, $lastId), "LIST");
		if($responseQuery->result == 2){
			$arrayResult = array();
			$newLastId = $lastId;
			foreach ($responseQuery->listResult as $key => $row) {
				if($newLastId > $row['idHistorialSocio'])
					$newLastId = $row['idHistorialSocio'];

				$row['fecha'] = fechas::dateToFormatBar($row['fecha']);
				$row['fechaEmision'] = fechas::dateTimeToFormatBar($row['fechaEmision']);

				if(!is_null($row['importe']))
					$row['importe'] = number_format($row['importe'],2, ",", ".");
				else
					$row['importe'] = "";

				if(is_null($row['observaciones']))
					$row['observaciones'] = "";

				$arrayResult[] = $row;
			}
			$responseQuery->lastId = $newLastId;
			$responseQuery->listResult = $arrayResult;
		}else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron registros en el historial para el socio seleccionado.";

		return $responseQuery;
	}

	public function getHistorialSocio($idHistorialSocio){
		$responseQuery = DataBase::sendQuery("SELECT * FROM historialsocios WHERE idHistorialSocio = ?", array('i', $idHistorialSocio),"OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "El identificador seleccionado no corresponde a un registro del historial.";

		return $responseQuery;
	}

	public function getHistorialSocioToShow($idHistorialSocio){
		$responseQuery = DataBase::sendQuery("SELECT * FROM historialsocios WHERE idHistorialSocio = ?", array('i', $idHistorialSocio),"OBJECT");
		if($responseQuery->result == 2){
			$responseQuery->objectResult->fecha = fechas::dateToFormatBar($responseQuery->objectResult->fecha);
			$responseQuery->objectResult->fechaEmision = fechas::dateTimeToFormatBar($responseQuery->objectResult->fechaEmision);
			$responseQuery->objectResult->importe = number_format($responseQuery->objectResult->importe,2, ",", ".");
			if(is_null($responseQuery->objectResult->observaciones) || strlen($responseQuery->objectResult->observaciones) == 0)
				$responseQuery->objectResult->observaciones = "";
		}else if($responseQuery->result == 1) $responseQuery->message = "El identificador seleccionado no corresponde a un registro del historial.";

		return $responseQuery;
	}

	public function insertHistorialSocio($idSocio, $idMascota, $asunto, $importe, $observaciones, $fecha, $fechaEmision){
		return DataBase::sendQuery("INSERT INTO historialsocios (idSocio, idMascota, asunto, importe, observaciones, fecha, fechaEmision) VALUES(?,?,?,?,?,?,?)", array('iisdsis', $idSocio, $idMascota, $asunto, $importe, $observaciones, $fecha, $fechaEmision), "BOOLE");
	}

	public function updateHistorialSocio($idHistorialSocio, $asunto, $observaciones, $importe, $fecha){
		return DataBase::sendQuery("UPDATE historialsocios SET asunto = ? , observaciones = ? , importe = ? , fecha = ? WHERE idHistorialSocio = ?", array('ssdi', $asunto, $observaciones, $importe, $fecha, $idHistorialSocio), "BOOLE");
	}

	//============================================================================================================
	//============================================================================================================
	//============================================================================================================

	//============================================================================================================
	//==============================================HISTORIAL USUARIO=============================================
	//============================================================================================================

	public function getHistorialUsuarioMaxID($idUsuario){
		$responseQuery = DataBase::sendQuery("SELECT MAX(idHistorialUsuario) AS maxID FROM historialusuarios WHERE usuario = ?", array('i', $idUsuario), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontraron registros en el historial del usuario.";
		return $responseQuery;
	}
	public function getHistorialUsuario($lastId, $idUsuario){
		if($lastId == 0){
			$responseGetMaxId = historiales::getHistorialUsuarioMaxID($idUsuario);
			if($responseGetMaxId->result == 2)
				$lastId = $responseGetMaxId->objectResult->maxID + 1;
			else return $responseGetMaxId;
		}

		$responseQuery = DataBase::sendQuery("SELECT * FROM historialusuarios WHERE usuario = ? AND idHistorialUsuario < ? ORDER BY idHistorialUsuario DESC LIMIT 14", array('ii', $idUsuario, $lastId), "LIST");
		if($responseQuery->result == 2){
			$arrayResult = array();
			$newLastId = $lastId;
			foreach ($responseQuery->listResult as $key => $row) {
				if($newLastId > $row['idHistorialUsuario']) $newLastId = $row['idHistorialUsuario'];
				$row['fecha'] = fechas::dateTimeToFormatBar($row['fecha']);
				$arrayResult[] = $row;
			}

			$responseQuery->listResult = $arrayResult;
			$responseQuery->lastId = $newLastId;
		}else if($responseQuery->result == 1) $responseQuery->message = "No se obtuvieron los registros del historial del usuario.";

		return $responseQuery;
	}

	public function insertHistorialUsuario($usuario, $funcion, $idSocio, $idMascota, $observaciones){
		$timeStamp = fechas::getDateTimeNowInt();
		return DataBase::sendQuery("INSERT INTO historialusuarios(usuario, funcion, idSocio, idMascota, fecha, observacion) VALUES (?,?,?,?,?,?)", array('isiiss', $usuario, $funcion, $idSocio, $idMascota, $timeStamp, $observaciones), "BOOLE");
	}

	public function getHistorialUsuariosMaxId(){
		$query = DB::conexion()->prepare("SELECT MAX(idHistorialUsuario) AS idMaximo FROM historialusuarios");
		if($query->execute()){
			$result = $query->get_result();
			$response = $result->fetch_object();
			return $response->idMaximo;
		}else return null;
	}

	public function getHistorialUsuariosPagina($maxID){
		$query = DB::conexion()->prepare("SELECT HU.idHistorialUsuario, HU.funcion, HU.observacion, HU.fecha, US.nombre FROM historialusuarios AS HU, usuarios AS US WHERE US.idUsuario = HU.usuario AND  HU.idHistorialUsuario <= ? ORDER BY  HU.idHistorialUsuario DESC LIMIT 10");
		$query->bind_param('i',  $maxID);
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$row['fecha'] = fechas::parceFechaTimeFormatDMA($row['fecha'], "/");
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}

	public function getHistorialUsuariosMinId($historial, $maxID){
		$valorMinimo = $maxID;
		foreach ($historial as $key => $value) {
			if($value['idHistorialUsuario'] < $valorMinimo)
				$valorMinimo = $value['idHistorialUsuario'];
		}
		return $valorMinimo;
	}
	//============================================================================================================
	//============================================================================================================
	//============================================================================================================
}