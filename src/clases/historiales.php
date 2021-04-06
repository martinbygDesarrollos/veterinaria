<?php
require_once "../src/clases/fechas.php";

class historiales{

	//============================================================================================================
	//===========================================HISTORIAL CLINICO================================================
	//============================================================================================================
	public function insertHistoriaClinica($idMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$query = DB::conexion()->prepare("INSERT INTO historiasclinica (idMascota, fecha, motivoConsulta, diagnostico, observaciones) VALUES(?,?,?,?,?)");
		$query->bind_param('iisss', $idMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones);
		return $query->execute();
	}

	public function updateHistorialClinico($idHistorialClinico, $clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$query = DB::conexion()->prepare("UPDATE historiasclinica SET clienteMascota = ?, fecha = ?, motivoConsulta = ?, diagnostico = ?, observaciones = ? WHERE idHistorialClinico = ?");
		$query->bind_param('iisssi', $clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones, $idHistorialClinico);
		if($query->execute()) return true;
		else return false;
	}

	public function getHistorialClinico(){
		$query = DB::conexion()->prepare("SELECT * FROM historiasclinica");
		if($query->execute()){
			$response = $query->get_result();
			$arrayResponse = array();
			while($row = $response->fetch_array(MYSQLI_ASSOC)){
				$arrayResponse[] = $row;
			}
			return $arrayResponse;
		}else return null;
	}

	public function getOneHistoriaClinica($idHistorialClinico){
		$query = DB::conexion()->prepare("SELECT * FROM historiasclinica WHERE idHistorialClinico = ?");
		$query->bind_param('i', $idHistorialClinico);
		if($query->execute()){
			$response = $query->get_result();
			$result = $response->fetch_object();
			$result->fecha = fechas::parceFechaFormatDMA($result->fecha, "/");
			return $result;
		}else return null;
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

	public function getOneHistoriaClinicaMascota($idMascota){
		$query = DB::conexion()->prepare("SELECT * FROM historiasclinica WHERE idMascota = ?");
		$query->bind_param('i', $idMascota);
		if($query->execute()){
			$response = $query->get_result();
			$arrayResponse = array();
			while($row = $response->fetch_array(MYSQLI_ASSOC)){
				$row['fecha'] = fechas::parceFechaFormatDMA($row['fecha'],"/");
				$arrayResponse[] = $row;
			}
			return $arrayResponse;
		}else return null;
	}

	public function getHistoriaClinicaMinId($historial, $maxValor){
		$valorMinimo = $maxValor;
		foreach ($historial as $key => $value) {
			if($value['idHistorialClinico'] < $valorMinimo)
				$valorMinimo = $value['idHistorialClinico'];
		}
		return $valorMinimo;
	}

	public function getHistoriaClinicaMaxId($idMascota){
		$query = DB::conexion()->prepare("SELECT MAX(idHistorialClinico) AS idMaximo FROM historiasclinica WHERE idMascota = ?");
		$query->bind_param('i', $idMascota);
		if($query->execute()){
			$result = $query->get_result();
			$response = $result->fetch_object();
			return $response->idMaximo;
		}else return null;
	}

	public function getHistoriaClinicaPagina($ultimoID, $idMascota){
		$query = DB::conexion()->prepare("SELECT * FROM historiasclinica WHERE idMascota=? AND idHistorialClinico<= ? ORDER BY idHistorialClinico DESC LIMIT 10");
		$query->bind_param('ii', $idMascota, $ultimoID);
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$row['fecha'] = fechas::parceFechaFormatDMA($row['fecha'], "/");
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}
	//============================================================================================================
	//============================================================================================================
	//============================================================================================================

	//============================================================================================================
	//===============================================HISTORIAL SOCIO==============================================
	//============================================================================================================
	public function getHistorialSocios(){
		$query = DB::conexion()->prepare("SELECT * FROM historialsocios");
		if($query->execute()){
			$response = $query->get_result();
			$arrayResponse = array();
			while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
				$arrayResponse[] = $row;
			}
			return $arrayResponse;
		}else return null;
	}

	public function getHistorialSocio($idHistorialSocio){
		$query = DB::conexion()->prepare("SELECT * FROM historialsocios WHERE idHistorialSocio = ?");
		$query->bind_param('i', $idHistorialSocio);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function insertHistorialSocio($clienteMascota, $observaciones, $fecha){
		$query = DB::conexion()->prepare("INSERT INTO historialsocios (clienteMascota, observaciones, fecha) VALUES(?,?,?)");
		$query->bind_param('isi',$clienteMascota, $observaciones, $fecha);
		if($query->execute()) return true;
		else return false;
	}

	public function updateHistorialSocio($idHistorialSocio, $clienteMascota, $observaciones, $fecha){
		$query = DB::conexion()->prepare("UPDATE historialsocios SET clienteMascota = ?, observaciones = ?, fecha = ? WHERE idHistorialSocio = ?");
		$query->bind_param('isii', $clienteMascota, $observaciones, $fecha, $idHistorialSocio);
		if($query->execute()) return true;
		else return false;
	}
	//============================================================================================================
	//============================================================================================================
	//============================================================================================================

	//============================================================================================================
	//==============================================HISTORIAL USUARIO=============================================
	//============================================================================================================

	public function getHistorialUsuarios(){
		$query = DB::conexion()->prepare("SELECT HU.funcion, HU.observacion, HU.fecha, US.nombre FROM historialusuarios AS HU, usuarios AS US WHERE US.idUsuario = HU.usuario");
		if($query->execute()){
			$response = $query->get_result();
			$arrayResponse = array();
			while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
				$row['fecha'] = fechas::parceFechaTimeFormatDMA($row['fecha']);
				$arrayResponse[] = $row;
			}
			return $arrayResponse;
		}else return null;
	}

	public function getHistorialUsuario($usuario){
		$query = DB::conexion()->prepare("SELECT HU.funcion, HU.observacion, HU.fecha, US.nombre FROM historialusuarios AS HU, usuarios AS US WHERE US.idUsuario = HU.usuario AND usuario = ?");
		$query->bind_param('i', $usuario);
		if($query->execute()){
			$response = $query->get_result();
			$arrayResponse = array();
			while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
				$row['fecha'] = fechas::parceFechaTimeFormatDMA($row['fecha']);
				$arrayResponse[] = $row;
			}
			return $arrayResponse;
		}else return null;
	}

	public function insertHistorialUsuario($usuario, $funcion, $fecha, $observaciones){
		$query = DB::conexion()->prepare("INSERT INTO historialusuarios(usuario, funcion, fecha, observacion) VALUES (?,?,?,?)");
		$query->bind_param('isss', $usuario, $funcion, $fecha, $observaciones);
		return $query->execute();
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