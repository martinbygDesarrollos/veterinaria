<?php

require_once '../src/utils/formats.php';

class socios{
	//TIPO SOCIO::: SOCIO = 1, NO SOCIO o cliente = 0 ONG = 2 ex socio = 3
	//estado activo = 1 inactivo = 0

	public function updateGestcomSocio($idSocio, $ultimoPago, $ultimaCuota){
		return DataBase::sendQuery("UPDATE socios SET fechaUltimoPago = ?, fechaUltimaCuota = ? WHERE idSocio = ?", array('iii', $ultimoPago, $ultimaCuota, $idSocio), "BOOLE");
	}

	public function getSociosVistaFactura(){
		return DataBase::sendQuery("SELECT * FROM socios WHERE estado = 1 AND tipo = 1 AND idSocio IN (SELECT idSocio FROM mascotasocio) GROUP BY idSocio", null, "LIST");
	}

	public function updateQuotaSocio($idSocio, $cuotaSocio){
		return DataBase::sendQuery("UPDATE socios SET cuota = ? WHERE idSocio = ?", array('ii', $cuotaSocio, $idSocio), "BOOLE");
	}

	public function getSocioByCedula($cedula){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE cedula = ?", array('s', $cedula), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "La cédula ingresada no corresponse a un socio en el sistema.";

		return $responseQuery;
	}

	public function getSocioById($id){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE idSocio = ?", array('i', $id), "OBJECT");
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
			$sqlToSearch = " AND nombre LIKE '%" . $textToSearch . "%' ";
		}

		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE estado = ? AND idSocio < ? " . $sqlToSearch . " ORDER BY idSocio DESC LIMIT 14", array('ii', $estado, $lastId), "LIST");
		if($responseQuery->result == 2){
			$newLastId = $lastId;
			$arrayResult = array();
			foreach ($responseQuery->listResult as $key => $row) {
				if($row['idSocio'] < $newLastId) $newLastId = $row['idSocio'];
				$row['deudorFecha'] = "";
				$row['cuota'] = number_format($row['cuota'],2,",",".");
				if(!is_null($row['fechaUltimaCuota']) && strlen($row['fechaUltimaCuota']) == 6)
					$row['fechaUltimaCuota'] = fechas::getYearMonthFormatBar($row['fechaUltimaCuota']);
				else
					$row['fechaUltimaCuota'] = "";//"No especificado";

				if ( $row['fechaUltimaCuota'] != "" ){
					$dateToCompare = date('Ym', strtotime(date('Ym')." -3 month"));
					$date = substr($row['fechaUltimaCuota'], 3, 4) . substr($row['fechaUltimaCuota'], 0, 2);
					if ( $date < $dateToCompare ){
						$row['deudor'] = true;
					}else{
						$row['deudor'] = false;
					}
				}else{
					$row['deudorFecha'] = "";
					$row['deudor'] = false;
				}

				if ( $row['fechaUltimoPago'] != "" && $row['fechaUltimoPago'] != null ){
					$row['deudorFecha'] = substr($row['fechaUltimoPago'], 6, 2)."/".substr($row['fechaUltimoPago'], 4, 2)."/".substr($row['fechaUltimoPago'], 0, 4);
				}
				$arrayResult[] = $row;
			}
			$responseQuery->listResult = $arrayResult;
			$responseQuery->lastId = $newLastId;
		}else if($responseQuery->result == 1) $responseQuery->message = "No quedan socios que mostrar en la base de datos.";

		return $responseQuery;
	}

	public function getCuotasVencidas($lastId, $textToSearch, $plazoDeuda){
		if($lastId == 0){
			$responseGetMaxID = socios::getSocioMaxId();
			if($responseGetMaxID->result == 2)
				$lastId = $responseGetMaxID->objectResult->idMaximo + 1;
		}

		$fechaVencimiento = fechas::getYearMonthINT($plazoDeuda);

		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE estado = 1 AND fechaUltimaCuota < ? AND idSocio <= ? ORDER BY idSocio DESC LIMIT 10", array('ii', $fechaVencimiento, $lastId), "LIST");
		if($responseQuery->result == 2){
			$newLastId = $lastId;
			$arrayResult = array();
			foreach ($responseQuery->listResult as $key => $row) {
				if($newLastId > $row['idSocio']) $newLastId = $row['idSocio'];

				$newRow = socios::getSocioToShowArray($row);

				if (!filter_var($newRow['email'], FILTER_VALIDATE_EMAIL))
					$newRow['email'] = null;

				$arrayResult[] = $newRow;
			}

			$responseQuery->listResult = $arrayResult;
			$responseQuery->lastId = $newLastId;
			array_multisort(array_map(function($element) {
				return $element['fechaUltimaCuota'];
			}, $responseQuery->listResult), SORT_DESC, $responseQuery->listResult);
		}else if($responseQuery->result == 1){
			$responseQuery->message = "No se encontraron cuotas vencidas en la base de datos.";
		}

		return $responseQuery;
	}

	public function getSocioToShowArray($socio){

		$socio['cuota'] = number_format($socio['cuota'],2,",",".");

		if(is_null($socio['fechaPago']))
			$socio['fechaPago'] = "No especificado";

		if(!is_null($socio['fechaUltimaCuota']) && strlen($socio['fechaUltimaCuota']) == 6)
			$socio['fechaUltimaCuota'] = fechas::getYearMonthFormatBar($socio['fechaUltimaCuota']);
		else
			$socio['fechaUltimaCuota'] = "No especificado";

		if(is_null($socio['telefono']))
			$socio['telefono'] = "No especificado";

		if($socio['lugarPago'] == 0)
			$socio['lugarPago'] = "Veterinaria";
		else if( $socio['lugarPago'] == 1 ){
			$socio['lugarPago'] = "Cobrador";
		}else if( $socio['lugarPago'] == 2 ){
			$socio['lugarPago'] = "OCA";
		}

		return $socio;
	}

	public function changeStateSocio($idSocio, $newState){
		return DataBase::sendQuery("UPDATE socios SET estado = ? WHERE idSocio = ?", array('ii', $newState, $idSocio), "BOOLE");
	}

	public function getSociosToInactive($dateVencimiento){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE estado = 1 AND (fechaUltimaCuota < ? OR fechaUltimaCuota IS NULL) AND tipo != 2", array('i', $dateVencimiento), "LIST");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontraron socios con incumplimiento en el pago de sus cuotas.";

		return $responseQuery;
	}

	public function setInactiveStateSocio($dateVencimiento){
		return DataBase::sendQuery("UPDATE socios SET estado = ?, cuota = 0 WHERE (fechaUltimaCuota < ? OR fechaUltimaCuota IS NULL) AND tipo != 2 ", array('ii', 0, $dateVencimiento), "BOOLE");
	}

	public function getSociosToActive($dateVencimiento){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE estado = 0 AND fechaUltimaCuota > ? AND tipo != 2", array('i', $dateVencimiento), "LIST");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontraron socios con incumplimiento en el pago de sus cuotas.";

		return $responseQuery;
	}

	public function setActiveStateSocio($dateVencimiento){
		return DataBase::sendQuery("UPDATE socios SET estado = ? WHERE fechaUltimaCuota > ? AND tipo != 2 ", array('ii', 1, $dateVencimiento), "BOOLE");
	}

	public function getSociosWithMascotas(){
		return DataBase::sendQuery("SELECT S.idSocio, S.estado, S.tipo, COUNT(MS.idMascota) AS cantMascotas FROM socios AS S, mascotasocio AS MS WHERE S.idSocio = MS.idSocio AND S.estado = 1 GROUP BY MS.idSocio", null, "LIST");
	}

	public function getSocioToShow($idSocio){
		$responseQuery = DataBase::sendQuery("SELECT * FROM socios WHERE idSocio = ?", array('i', $idSocio), "OBJECT");
		if($responseQuery->result == 2){
			$socio = $responseQuery->objectResult;
			//$noData = "No especificado.";
			$noData = "";

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
			else $socio->fechaUltimaCuota = fechas::getYearMonthFormatBar($socio->fechaUltimaCuota);

			if(is_null($socio->fechaIngreso) || $socio->fechaIngreso == 0)
				$socio->fechaIngreso = $noData;
			else $socio->fechaIngreso = fechas::dateToFormatBar($socio->fechaIngreso);

			if(is_null($socio->fechaBajaSocio) || $socio->fechaBajaSocio == 0)
				$socio->fechaBajaSocio = $noData;
			else $socio->fechaBajaSocio = fechas::dateToFormatBar($socio->fechaBajaSocio);

			if($socio->lugarPago == 0)
				$socio->lugarPago = "Veterinaria";
			else if($socio->lugarPago == 1)
				$socio->lugarPago = "Cobrador";
			else if($socio->lugarPago == 2)
				$socio->lugarPago = "OCA";

			$socio->tipoSocio = $socio->tipo;
			if($socio->tipo == 0)
				$socio->tipo = "No socio";
			else if($socio->tipo == 1)
				$socio->tipo = "Socio";
			else if($socio->tipo == 2)
				$socio->tipo = "ONG";
			else if($socio->tipo == 3)
				$socio->tipo = "Ex socio";

			$socio->deudorFecha = "";
			if ( $socio->fechaUltimaCuota != "" ){
				$dateToCompare = date('Ym', strtotime(date('Ym')." -3 month"));
				$date = substr($socio->fechaUltimaCuota, 3, 4) . substr($socio->fechaUltimaCuota,0, 2);
				if ( $date < $dateToCompare ){
					$socio->deudor = true;
				}else{
					$socio->deudor = false;
				}
			}else{
				$socio->deudorFecha = "";
				$socio->deudor = false;
			}

			if ( $socio->fechaUltimoPago != "" && $socio->fechaUltimoPago != null ){
				$socio->deudorFecha = $socio->fechaUltimoPago;
			}
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

		if ($cedula == "")
			$cedula = null;

		return DataBase::sendQuery("INSERT INTO socios (nombre, cedula, direccion, telefono, fechaPago, lugarPago, telefax, fechaIngreso, email, rut, tipo) VALUES(?,?,?,?,?,?,?,?,?,?,?)", array('ssssiisissi', $nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $telefax, $fechaIngreso, $email, $rut, $tipoSocio), "BOOLE");
	}

	public function updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax, $tipoSocio, $lugarPago, $fechaIngreso, $ultimoPago, $fechaPago, $ultimoMesPago, $quota, $fechaBajaSocio){

		if ($cedula == "")
			$cedula = null;

		return DataBase::sendQuery("UPDATE socios SET cedula = ?, nombre = ?, telefono = ?, telefax = ?, direccion = ?, fechaIngreso = ?, fechaPago = ?, lugarPago = ?, email = ?, rut = ?, tipo = ?, fechaUltimaCuota = ?, cuota = ?, fechaUltimoPago = ?, fechaBajaSocio = ? WHERE idSocio = ?", array('sssssiiissiiiiii', $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $email, $rut, $tipoSocio, $ultimoMesPago, $quota, $ultimoPago, $fechaBajaSocio, $idSocio), "BOOLE");
	}

	public function getAllSocios(){
		return DataBase::sendQuery("SELECT * FROM socios", null, "LIST");
	}

	//funcion que devuelve true o false si el socio es deudor o no (debe mas de tres meses)
	public function socioDeudor($fechaUltimaCuota){
		$response = new \stdClass();
		$deudorFecha = "";
		if(!is_null($fechaUltimaCuota) && strlen($fechaUltimaCuota) == 6)
			$fechaUltimaCuota = fechas::getYearMonthFormatBar($fechaUltimaCuota);
		else
			$fechaUltimaCuota = "";//"No especificado";

		if ( $fechaUltimaCuota != "" ){
			$dateToCompare = date('Ym', strtotime(date('Ym')." -3 month"));
			$date = substr($fechaUltimaCuota, 3, 4) . substr($fechaUltimaCuota, 0, 2);
			if ( $date < $dateToCompare ){
				$deudor = true;
			}else{
				$deudor = false;
			}
		}else{
			$deudorFecha = "";
			$deudor = false;
		}

		$response->deudor = $deudor;

		return $response;
	}


	public function clientTypeChangesDate( $currentClientType, $newClientType ){
		/**
		 * cliente normal(0) a socio(1) se guarda la fecha de alta
		 *
		 * socio(1) a cualquier otra excepto ong(2) se guarda la fecha de baja
		 *
		 * de exsocio (3) a socio (1), fecha de alta no cambia, fecha de baja se limpia
		 */

		$response = new \stdClass();
		if ( isset($currentClientType) && isset($newClientType) ){
			if ( $currentClientType != $newClientType ){
				if ( $currentClientType == 0 && $newClientType == 1 ){
					$response->dateInit = date("Y-m-d");
					$response->dateFinish = null;
					$response->result = 2;
				}elseif ( $currentClientType == 1 && $newClientType != 2 ){
					$response->dateInit = null;
					$response->dateFinish = date("Y-m-d");
					$response->result = 2;
				}elseif ( $currentClientType == 3 && $newClientType == 1 ){
					$response->dateInit = null;
					$response->dateFinish = null;
					$response->result = 2;
				}else{

					error_log("funcion clientTypeChangesDate tipo cliente actual ".$currentClientType." - ".$newClientType." tipo cliente nuevo");
					$response->result = 1;
				}
			}else{
				error_log("funcion clientTypeChangesDate tipo cliente actual y nuevo son iguales");
				$response->result = 1;
			}
		}else{
			error_log("funcion clientTypeChangesDate tipo cliente actual ".$currentClientType." - ".$newClientType." tipo cliente nuevo. undefined o null");
			$response->result = 1;
		}

		return $response;
	}

	public function searchClientByName($value){
		$dataBaseCLass = new DataBase();
		return $dataBaseCLass->sendQuery("SELECT idSocio, nombre FROM `socios` WHERE `nombre` LIKE '%".$value."%' ORDER BY `nombre` ASC LIMIT 5", array(), "LIST");
	}

	public function searchClientByNameAndRut($name, $rut){
		$dataBaseCLass = new DataBase();
		$response = $dataBaseCLass->sendQuery("SELECT * FROM `socios` WHERE `nombre` = ? AND `rut` = ?", array('ss', $name, $rut), "OBJECT");
		if($response->result == 1)
			$response->message = "La cédula ingresada no corresponse a un socio en el sistema.";

		return $response;
	}
}
