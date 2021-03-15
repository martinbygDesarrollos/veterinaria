<?php

class historialSocio{
	private $idHistorialSocio;
	private $clienteMascota;
	private $observaciones;
	private $fecha;

	public function __construct($idHistorialSocio, $clienteMascota, $observaciones, $fecha){
		$this->idHistorialSocio = $idHistorialSocio;
		$this->clienteMascota = $clienteMascota;
		$this->observaciones = $observaciones;
		$this->fecha = $fecha;
	}

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
}
