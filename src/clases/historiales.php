<?php
require_once "../src/clases/fechas.php";

class historiales{

	//============================================================================================================
	//===========================================HISTORIAL CLINICO================================================
	//============================================================================================================
	public function insertHistoriaClinica($idMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$query = DB::conexion()->prepare("INSERT INTO hisotiralclinico (idMascota, fecha, motivoConsulta, diagnostico, observaciones) VALUES(?,?,?,?,?)");
		$query->bind_param('iisss', $idMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones);
		return $query->execute();
	}

	public function updateHistorialClinico($idHistorialClinico, $clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$query = DB::conexion()->prepare("UPDATE hisotiralclinico SET clienteMascota = ?, fecha = ?, motivoConsulta = ?, diagnostico = ?, observaciones = ? WHERE idHistorialClinico = ?");
		$query->bind_param('iisssi', $clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones, $idHistorialClinico);
		if($query->execute()) return true;
		else return false;
	}

	public function getHistorialClinico(){
		$query = DB::conexion()->prepare("SELECT * FROM hisotiralclinico");
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
		$query = DB::conexion()->prepare("SELECT * FROM hisotiralclinico WHERE idHistorialClinico = ?");
		$query->bind_param('i', $idHistorialClinico);
		if($query->execute()){
			$response = $query->get_result();
			$result = $response->fetch_object();
			$result->fecha = fechas::parceFechaFormatDMA($result->fecha, "/");
			return $result;
		}else return null;
	}

	public function checkHayHistorialClinico($idMascota){
		$query =DB::conexion()->prepare("SELECT * FROM hisotiralclinico WHERE idMascota = ?");
		$query->bind_param('i', $idMascota);
		if($query->execute()){
			$response = $query->get_result();
			if($response->fetch_object())
				return true;
		}
		return false;
	}

	public function getOneHistoriaClinicaMascota($idMascota){
		$query = DB::conexion()->prepare("SELECT * FROM hisotiralclinico WHERE idMascota = ?");
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
	//============================================================================================================
	//============================================================================================================
	//============================================================================================================

	//============================================================================================================
	//===============================================HISTORIAL SOCIO==============================================
	//============================================================================================================
	public function getHistorialSocios(){
		$query = DB::conexion()->prepare("SELECT * FROM hisotiralsocios");
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
		$query = DB::conexion()->prepare("SELECT * FROM hisotiralsocios WHERE idHistorialSocio = ?");
		$query->bind_param('i', $idHistorialSocio);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function insertHistorialSocio($clienteMascota, $observaciones, $fecha){
		$query = DB::conexion()->prepare("INSERT INTO hisotiralsocios (clienteMascota, observaciones, fecha) VALUES(?,?,?)");
		$query->bind_param('isi',$clienteMascota, $observaciones, $fecha);
		if($query->execute()) return true;
		else return false;
	}

	public function updateHistorialSocio($idHistorialSocio, $clienteMascota, $observaciones, $fecha){
		$query = DB::conexion()->prepare("UPDATE hisotiralsocios SET clienteMascota = ?, observaciones = ?, fecha = ? WHERE idHistorialSocio = ?");
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
		$query = DB::conexion()->prepare("SELECT * FROM historialusuarios");
		if($query->execute()){
			$response = $query->get_result();
			$arrayResponse = array();
			while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
				$arrayResponse[] = $row;
			}
		}else return null;
	}

	public function getHistorialUsuario($nombre){
		$query = DB::conexion()->prepare("SELECT * FROM historialusuarios WHERE nombre = ?");
		$query->bind_param('i', $nombre);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function insertHistorialUsuario($usuario, $funcion, $fecha){
		$query = DB::conexion()->prepare("INSERT INTO historialusuarios (usuario, funcion, fecha) VALUES (?,?,?)");
		$query->bind_param('iii', $usuario, $funcion, $fecha);
		$query->execute();
	}
	//============================================================================================================
	//============================================================================================================
	//============================================================================================================
}