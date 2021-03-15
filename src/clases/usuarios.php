<?php
class usuarios{


	private $idUsuario;
	private $nombre;
	private $pass;
	private $estado;
	private $grupo;

	public function __construct($idUsuario, $nombre, $pass, $estado, $grupo){

		$this->idUsuario = $idUsuario;
		$this->nombre = $nombre;
		$this->pass = $pass;
		$this->estado = $estado;
		$this->grupo = $grupo;
	}

	public function getUsuarios(){

		$query = DB::conexion()->prepare("SELECT * FROM usuarios");
		if($query->execute()){
			$response = $query->get_result();
			$arrayResponse = array();
			while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
				$arrayResponse[] = $row;
			}
			return $arrayResponse;
		}
		return null;
	}

	public function getUsuario($idUsuario){

		$query = DB::conexion()->prepare("SELECT * FROM usuarios WHERE idUsuario = ? AND estado = 1");
		$query->bind_param('i', $idUsuario);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function getUsuarioEstado($idUsuario, $estado){

		$query = DB::conexion()->prepare("SELECT * FROM usuarios WHERE idUsuario = ? AND estado = ?");
		$query->bind_param('ii', $idUsuario, $estado);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function getUsuarioNombre($nombre){

		$query = DB::conexion()->prepare("SELECT * FROM usuarios WHERE nombre = ? AND estado = 1");
		$query->bind_param('s', $nombre);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function insertUsuario($nombre, $pass){

		$query = DB::conexion()->prepare("INSERT INTO usuarios(nombre, pass) VALUES (?,?)");
		$query->bind_param('ss', $nombre, $pass);
		if($query->execute()) return true;
		else return false;
	}

	public function updateUsuario($nombre, $pass, $estado, $grupo){

		$query = DB::conexion()->prepare("UPDATE usuarios SET nombre = ?, pass = ?, estado = ?, grupo = ? WHERE idUsuario = ?");
		$query->bind_param('ssii', $nombre, $pass, $estado, $grupo);
		if($query->execute())
			return true;
		else return null;
	}

	//--------------------------- GRUPO FUNCIONES ------------------------------------------------------------

	public function insertGrupo($nombre){

		$conexion = DB::conexion();
		$query = $conexion->prepare("INSERT INTO grupos (nombre) VALUES(?)");
		$query->bind_param('s', $nombre);
		if($query->execute()) return $conexion->insert_id;
		else return null;
	}

	public function setGrupoUsuario($idUsuario, $idGrupo){

		$query = DB::conexion()->prepare("UPDATE usuarios SET grupo = ? WHERE idUsuario = ?");
		$query->bind_param('ii', $grupo, $idUsuario);
		if($query->execute()) return true;
		else return false;
	}

	public function getGrupoNombre($nombre){

		$query = DB::conexion()->prepare("SELECT * FROM grupos WHERE nombre = ?");
		$query->bind_param('s', $nombre);
		if($query->execute()) {
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function asignarFuncionGrupo($idGrupo, $idFuncion){
		$query = DB::conexion()->prepare("INSERT INTO grupofunciones (idGrupo, idFuncion) VALUES (?,?)");
		$query->bind_param('ii', $idGrupo, $idFuncion);
		if($query->execute()) return true;
		else return false;
	}

	public function getGrupoFuncion($idGrupo, $idFuncion){

		$query = DB::conexion()->prepare("SELECT * FROM grupofunciones WHERE idGrupo = ? AND idFuncion = ?");
		$query->bind_param('ii', $idGrupo, $idFuncion);
		if($query->execute()){
			$response = $query->get_result();

			if($response != null) return true;
			else return false;
		}else return false;
	}

	public function getPermissions($idGrupo){

		$query = DB::conexion()->prepare("SELECT * FROM funciones WHERE idFuncion IN (SELECT idFuncion FROM grupofunciones WHERE idGrupo = ?)");
		$query->bind_param('i', $idGrupo);
		if($query->execute()){
			$response = $query->get_result();
			$arrayResponse = array();
			while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
				$arrayResponse[] = $row;
			}

			return $arrayResponse;
		}
		return null;
	}

	//----------------------------------------------------------------------------------------------------------
}