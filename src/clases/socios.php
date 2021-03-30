<?php

class socios{
	//TIPO SOCIO::: SOCIO = 1, NO SOCIO = 0 ONG = 2
	//activo = 1 inactivo = 0

	public function getSocios($estado){
		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE estado" . $estado);
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}

	public function getSociosParaCuotasVencidas(){
		$fecha = date('Y-m-d');
		$fecha = substr($fecha, 0, 4) . substr($fecha, 5, 2);

		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE estado = 1 AND fechaUltimaCuota <= ? ");
		$query->bind_param('i', $fecha);
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
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

	public function getSocio($idSocio){
		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE idSocio = ?");
		$query->bind_param('i', $idSocio);
		if($query->execute()){
			$result = $query->get_result();
			return $result->fetch_object();
		}else return null;
	}

	public function getSocioCedula($cedula){
		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE cedula = ?");
		$query->bind_param('s', $cedula);
		if($query->execute()){
			$result = $query->get_result();
			return $result->fetch_object();
		}else return null;
	}

	public function getSocioMascota($idMascota){
		$query = DB::conexion()->prepare("SELECT * FROM socios, mascotasocio WHERE socios.idSocio = mascotasocio.idSocio AND mascotasocio.idMascota = ? AND fechaCambio = (SELECT MAX(fechaCambio) FROM mascotasocio WHERE mascotasocio.idMascota = ?)");
		$query->bind_param('ii', $idMascota, $idMascota);
		if($query->execute()){
			$result = $query->get_result();
			return $result->fetch_object();
		}else return null;
	}

	public function obtenerBusqueda($busqueda){
		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE nombre LIKE '%" . $busqueda ."%' LIMIT 100");
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}

	public function insertSocio($cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUPago, $fechaUCuota, $tipoSocio){
		$conexion = DB::conexion();
		$query = $conexion->prepare("INSERT INTO socios (cedula, nombre, telefono, telefax, direccion, fechaIngreso, fechaPago, lugarPago, estado, motivoBaja, cuota, email, rut, fechaUltimoPago, fechaUltimaCuota, tipo) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$query->bind_param('sssssiiiisissiii', $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUPago, $fechaUCuota, $tipoSocio);
		if($query->execute()) return $conexion->insert_id;
		else return false;
	}

	public function updateSocio($idSocio, $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $email, $rut, $tipoSocio){
		$query = DB::conexion()->prepare("UPDATE socios SET cedula = ?, nombre = ?, telefono = ?, telefax = ?, direccion = ?, fechaIngreso = ?, fechaPago = ?, lugarPago = ?, email = ?, rut = ?, tipo = ? WHERE idSocio = ?");
		$query->bind_param('sssssiiissii', $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $email, $rut, $tipoSocio, $idSocio);
		return $query->execute();
	}


	public function getCantMascotasSocio($idSocio){
		$query = DB::conexion()->prepare("SELECT COUNT(*) AS cantMascotas FROM mascotas WHERE estado = 1 AND idMascota IN (SELECT idMascota AS cantMascotas FROM mascotasocio WHERE idSocio = ?)");
		$query->bind_param('i', $idSocio);
		if($query->execute()){
			$response = $query->get_result();
			$response = $response->fetch_object();
			return $response->cantMascotas;
		}else return null;
	}

	public function actualizarCuotaSocio($idSocio, $cuotaSocio){
		$query = DB::conexion()->prepare("UPDATE socios SET cuota = ? WHERE idSocio = ?");
		$query->bind_param('ii', $cuotaSocio, $idSocio);
		if($query->execute()) return true;
		return false;
	}

	public function getSociosConPlazoVencido($plazoDeuda){
		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE estado = 1 OR estado = 3 AND fechaUltimaCuota < ? LIMIT 200");
		$query->bind_param('i', $plazoDeuda);
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}

	public function actualizarEstadoSocio($idSocio, $estado){
		$query = DB::conexion()->prepare("UPDATE socios SET estado = ? WHERE idSocio = ?");
		$query->bind_param('ii', $estado, $idSocio);
		return $query->execute();
	}

	public function esMiMascota($idSocio, $idMascota){
		$query = DB::conexion()->prepare("SELECT * FROM mascotasocio WHERE idMascotaSocio = (SELECT MAX(idMascotaSocio) FROM mascotasocio WHERE idSocio = ? AND idMascota = ? )");
		$query->bind_param('ii', $idSocio, $idMascota);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}
}
