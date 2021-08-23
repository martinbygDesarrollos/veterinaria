<?php

require_once '../src/utils/formats.php';

class socios{
	//TIPO SOCIO::: SOCIO = 1, NO SOCIO = 0 ONG = 2
	//activo = 1 inactivo = 0

	public function updateQuotaSocio($idSocio, $cuotaSocio){
		return DataBase::sendQuery("UPDATE socios SET cuota = ? WHERE idSocio = ?", array('ii', $cuotaSocio, $idSocio), "BOOLE");
	}

	public function getSocioByCedula($cedula){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE cedula = ?", array('s', $cedula), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "La cédula ingresada no corresponse a un socio en el sistema.";

		return $responseQuery;
	}

	public function getSocioMaxId(){
		return DataBase::sendQuery("SELECT MAX(idSocio) AS idMaximo FROM socios", null, "OBJECT");
	}

	public function getSociosPagina($lastId, $estado, $textToSearch){
		if($lastId == 0){
			$responseGetMaxID = socios::getSocioMaxId();
			if($responseGetMaxID->result == 2)
				$lastId = $responseGetMaxID->objectResult->idMaximo + 1;
		}

		$sqlToSearch = "";
		if(!is_null($textToSearch)){
			$sqlToSearch = " AND nombre LIKE '" . $textToSearch . "%' ";
		}

		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE estado = ? AND idSocio <= ? " . $sqlToSearch . " ORDER BY idSocio DESC LIMIT 14", array('ii', $estado, $lastId), "LIST");
		if($responseQuery->result == 2){
			$newLastId = $lastId;
			$arrayResult = array();
			foreach ($responseQuery->listResult as $key => $row) {
				if($row['idSocio'] < $newLastId) $newLastId = $row['idSocio'];

				$row['cuota'] = number_format($row['cuota'],2,",",".");
				if(!is_null($row['fechaUltimaCuota']) && strlen($row['fechaUltimaCuota']) == 6)
					$row['fechaUltimaCuota'] = fechas::getDayMonthFormatBar($row['fechaUltimaCuota']);
				else
					$row['fechaUltimaCuota'] = "No especificado";

				$arrayResult[] = $row;
			}
			$responseQuery->listResult = $arrayResult;
			$responseQuery->lastId = $newLastId;
		}else if($responseQuery->result == 1) $responseQuery->message = "No quedan socios que mostrar en la base de datos.";

		return $responseQuery;
	}

	public function setInactiveStateSocio($dateVencimiento){
		return DataBase::sendQuery("UPDATE socios SET estado = ? WHERE (fechaUltimaCuota <= ? OR fechaUltimaCuota IS NULL) AND tipo != 2 ", array('ii', 0, $dateVencimiento), "BOOLE");
	}

	public function setActiveStateSocio($dateVencimiento){
		return DataBase::sendQuery("UPDATE socios SET estado = ? WHERE fechaUltimaCuota > ? AND tipo != 2 ", array('ii', 1, $dateVencimiento), "BOOLE");
	}

	public function getSociosWithMascotas(){
		return DataBase::sendQuery("SELECT S.idSocio, S.estado , COUNT(MS.idMascota) AS cantMascotas FROM socios AS S, mascotasocio AS MS WHERE S.idSocio = MS.idSocio AND S.estado = 0 GROUP BY MS.idSocio", null, "LIST");
	}

	public function getVencimientosCuotaPagina($maxID){
		$fecha = date('Y-m-d');
		$fecha = substr($fecha, 0, 4) . substr($fecha, 5, 2);

		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE estado = 1 AND fechaUltimaCuota <= ? AND idSocio <= ? ORDER BY idSocio DESC LIMIT 10");
		$query->bind_param('ii', $fecha, $maxID);
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				if(strlen($row['fechaUltimaCuota']) == 6){
					$resultFecha = fechas::esUnaCuotaVencida($row['fechaUltimaCuota'], $row['fechaPago']);
					if($resultFecha){
						$row['fechaUltimaCuota'] = fechas::parceFechaFormatDMANoDay($row['fechaUltimaCuota'], "/");
						$arrayResult[] = $row;
					}
				}
			}
			return $arrayResult;
		}else return null;
	}

	public function getSociosNoVinculados($idMascota){
		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE idSocio NOT IN (SELECT idSocio FROM mascotasocio WHERE idMascota = ?)");
		$query->bind_param('i', $idMascota);
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}else return null;
	}

	public function getSocioToShow($idSocio){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE idSocio = ?", array('i', $idSocio), "OBJECT");
		if($responseQuery->result == 2){
			$socio = $responseQuery->objectResult;
			$noData = "No especificado.";

			if(is_null($socio->cedula) || strlen($socio->cedula) == 0)
				$socio->cedula = $noData;
			else
				$socio->cedula = formats::formatCI($socio->cedula);

			if(is_null($socio->email) || strlen($socio->email) == 0)
				$socio->email = $noData;

			if(is_null($socio->nombre) || strlen($socio->nombre) == 0)
				$socio->nombre = $noData;

			if(is_null($socio->direccion) || strlen($socio->direccion) == 0)
				$socio->direccion = $noData;

			if(is_null($socio->rut) || strlen($socio->rut) == 0)
				$socio->rut = $noData;

			if(is_null($socio->telefax) || strlen($socio->telefax) == 0)
				$socio->telefax = $noData;

			if(!is_null($socio->cuota))
				$socio->cuota = number_format($socio->cuota, 2, ",", ".");
			else
				$socio->cuota = $noData;

			if(is_null($socio->telefono) || strlen($socio->telefono) == 0)
				$socio->telefono = $noData;

			if(is_null($socio->fechaPago) || $socio->fechaPago == 0)
				$socio->fechaPago = $noData;

			if(is_null($socio->fechaUltimoPago) || $socio->fechaUltimoPago == 0)
				$socio->fechaUltimoPago = $noData;
			else $socio->fechaUltimoPago = fechas::dateToFormatBar($socio->fechaUltimoPago);

			if(is_null($socio->fechaUltimaCuota) || $socio->fechaUltimaCuota == 0)
				$socio->fechaUltimaCuota = $noData;
			else $socio->fechaUltimaCuota = fechas::getDayMonthFormatBar($socio->fechaUltimaCuota);

			if(is_null($socio->fechaIngreso) || $socio->fechaIngreso == 0)
				$socio->fechaIngreso = $noData;
			else $socio->fechaIngreso = fechas::dateToFormatBar($socio->fechaIngreso);

			if($socio->lugarPago == 0)
				$socio->lugarPago = "Veterinaria";
			else $socio->lugarPago = "Cobrador";

			if($socio->tipo == 1)
				$socio->tipo = "Socio";
			else if($socio->tipo == 0)
				$socio->tipo = "No socio";
			else $socio->tipo = "ONG";

			$responseQuery->objectResult = $socio;
		}else if($responseQuery->result == 1) $responseQuery->message = "No fue encontrado el socio seleccionado.";

		return $responseQuery;
	}

	public function getSocio($idSocio){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE idSocio = ?", array('i', $idSocio), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No fue encontrado el socio seleccionado.";

		return $responseQuery;
	}

	public function getSocioCedula($cedula, $idSocio){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE cedula = ? AND idSocio != ?", array('si', $cedula, $idSocio), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se econtro otro socio con la cédula ingresada.";
		return $responseQuery;
	}

	public function getSocioMascota($idMascota){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE socios.idSocio IN (SELECT MAX(idSocio) FROM mascotasocio WHERE idMascota = ?)", array('i', $idMascota), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontro el socio vinculado a la mascota seleccionada.";

		return $responseQuery;
	}

	public function insertSocio($nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $telefax, $fechaIngreso, $email, $rut, $tipoSocio){
		return DataBase::sendQuery("INSERT INTO socios (nombre, cedula, direccion, telefono, fechaPago, lugarPago, telefax, fechaIngreso, email, rut, tipo) VALUES(?,?,?,?,?,?,?,?,?,?,?)", array('ssssiisissi', $nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $telefax, $fechaIngreso, $email, $rut, $tipoSocio), "BOOLE");
	}

	public function updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax, $tipoSocio, $lugarPago, $fechaIngreso, $ultimoPago, $fechaPago, $ultimoMesPago, $quota){
		return DataBase::sendQuery("UPDATE socios SET cedula = ?, nombre = ?, telefono = ?, telefax = ?, direccion = ?, fechaIngreso = ?, fechaPago = ?, lugarPago = ?, email = ?, rut = ?, tipo = ?, fechaUltimaCuota = ?, cuota = ?, fechaUltimoPago = ? WHERE idSocio = ?", array('sssssiiissiiiii', $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $email, $rut, $tipoSocio, $ultimoMesPago, $quota, $ultimoPago, $idSocio), "BOOLE");
	}
}
