<?php

class historialUsuarios{
	private $idHistorialUsuario;
	private $usuario;
	private $funcion;
	private $fecha;

	public function __construct($idHistorialUsuario, $usuario, $funcion, $fecha){
		$this->idHistorialUsuario = $idHistorialUsuario;
		$this->usuario = $usuario;
		$this->funcion = $funcion;
		$this->fecha = $fecha;
	}

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

	public function getHistorialUsuario($idHistorialUsuario){
		$query = DB::conexion()->prepare("SELECT * FROM historialusuarios WHERE idHistorialUsuario = ?");
		$query->bind_param('i', $idHistorialUsuario);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function insertHistorialUsuario($usuario, $funcion, $fecha){
		$query = DB::conexion()->prepare("INSERT INTO historialusuarios (usuario, funcion, fecha) VALUES (?,?,?)");
		$query->bind_param('iii', $usuario, $funcion, $fecha);
		if($query->execute()) return true;
		else return false;
	}

	public function updateHistorialUsuario($idHistorialUsuario, $usuario, $funcion, $fecha){
		$query = DB::conexion()->prepare("UPDATE historialusuarios SET usuario= ?, funcion = ?, fecha = ? WHERE idHistorialUsuario = ?");
		$query->bind_param('iiii', $usuario, $funcion, $fecha, $idHistorialUsuario);
		if($query->execute()) return true;
		else return false;
	}
}