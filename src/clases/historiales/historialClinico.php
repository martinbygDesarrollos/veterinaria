<?php

class historialClinico{
	private $idHistorialClinico;
	private $clienteMascota;
	private $fecha;
	private $motivoConsulta;
	private $diagnostico;
	private $observaciones;

	public function __construct($idHistorialClinico, $clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$this->idHistorialClinico = $idHistorialClinico;
		$this->clienteMascota = $clienteMascota;
		$this->fecha = $fecha;
		$this->motivoConsulta = $motivoConsulta;
		$this->diagnostico = $diagnostico;
		$this->observaciones = $observaciones;
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
			return $response->fetch_object();
		}else return null;
	}

	public function checkHayHistorial($idMascota){
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
				$arrayResponse[] = $row;
			}
			return $arrayResponse;
		}else return null;
	}

	public function insertHistorialClinico($clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$query = DB::conexion()->prepare("INSERT INTO hisotiralclinico (clienteMascota, fecha, motivoConsulta, diagnostico, observaciones) VALUES (?,?,?,?,?)");
		$query->bind_param('iisss', $clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones);
		if($query->execute()) return true;
		else return false;
	}

	public function updateHistorialClinico($idHistorialClinico, $clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$query = DB::conexion()->prepare("UPDATE hisotiralclinico SET clienteMascota = ?, fecha = ?, motivoConsulta = ?, diagnostico = ?, observaciones = ? WHERE idHistorialClinico = ?");
		$query->bind_param('iisssi', $clienteMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones, $idHistorialClinico);
		if($query->execute()) return true;
		else return false;
	}


}