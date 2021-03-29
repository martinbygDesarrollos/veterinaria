<?php

require_once 'fechas.php';

class mascotas{

	private $idMascota;
	private $nombre;
	private $especie;
	private $raza;
	private $sexo;
	private $color;
	private $pedigree;
	private $propietario;
	private $estado; //0 INACTIVA 1 ACTIVA 2 PENDIENTE
	private $pelo;
	private $chip;

	public function __construct($idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $propietario, $estado, $pelo, $chip){
		$this->idMascota = $idMascota;
		$this->nombre = $nombre;
		$this->especie = $especie;
		$this->raza = $raza;
		$this->sexo = $sexo;
		$this->color = $color;
		$this->pedigree = $pedigree;
		$this->propietario = $propietario;
		$this->estado = $estado;
		$this->pelo = $pelo;
		$this->chip = $chip;
	}

	public function getMascotas(){
		$query = DB::conexion()->prepare("SELECT * FROM mascotas WHERE estado = 1");
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$row['fechaNacimiento'] = fechas::parceFechaFormatDMA($row['fechaNacimiento'], '/');
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}

	public function getMascotasInactivasPendientes(){
		$query = DB::conexion()->prepare("SELECT * FROM mascotas WHERE estado != 1");
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$row['fechaNacimiento'] = fechas::parceFechaFormatDMA($row['fechaNacimiento'], '/');
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}

	public function getMascota($idMascota){
		$query = DB::conexion()->prepare("SELECT * FROM mascotas WHERE idMascota = ?");
		$query->bind_param('i', $idMascota);

		if($query->execute()){
			$response = $query->get_result();
			$retorno = $response->fetch_object();
			$retorno->fechaNacimiento = fechas::parceFechaFormatDMA($retorno->fechaNacimiento, "/");
			return $retorno;
		}else return null;
	}

	public function insertMascota($nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $estado, $pelo, $chip, $observaciones){
		$conexion = DB::conexion();
		$query = $conexion->prepare("INSERT INTO mascotas (nombre, especie, raza, sexo, color, pedigree, fechaNacimiento, estado, pelo, chip, observaciones) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
		$query->bind_param('sssisiiisss', $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $estado, $pelo, $chip, $observaciones);
		if($query->execute())
			return $conexion->insert_id;
		else
			return false;
	}

	public function updateMascota($idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones){
		$query = DB::conexion()->prepare("UPDATE mascotas SET nombre = ?, especie = ?, raza = ?, sexo = ?, color = ?, pedigree = ?, fechaNacimiento = ?, pelo = ?, chip = ? , observaciones = ? WHERE idMascota = ?");
		$query->bind_param('sssisiisssi', $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones, $idMascota);
		return $query->execute();
	}

	public function vincularMascotaSocio($idSocio, $idMascota, $fechaCambio){
		$query = DB::conexion()->prepare("INSERT INTO mascotasocio (idSocio, idMascota, fechaCambio) VALUES (?,?,?)");
		$query->bind_param('iii', $idSocio, $idMascota, $fechaCambio);
		if($query->execute()) return true;
		else return false;
	}

	public function activarDesactivarMascota($idMascota, $estado){
		$query = DB::conexion()->prepare("UPDATE mascotas SET estado = ? WHERE idMascota = ?");
		$query->bind_param('ii', $estado, $idMascota);
		return $query->execute();
	}

	public function getMascotasSocios($idSocio){
		$query = DB::conexion()->prepare("SELECT * FROM mascotas WHERE idMascota IN (SELECT idMascota FROM mascotasocio WHERE idSocio = ? )");
		$query->bind_param('i', $idSocio);
		if($query->execute()){
			$result = $query->get_result();
			$arrayMascotas = array();

			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$row['fechaNacimiento'] = fechas::parceFechaFormatDMA($row['fechaNacimiento'], "/");
				$arrayMascotas[] = $row;
			}
			return $arrayMascotas;
		}
		return null;
	}

	public function desactivarMascotasSocio($idSocio){
		$query = DB::conexion()->prepare("SELECT M.idMascota FROM mascotas AS M, mascotasocio AS MS WHERE M.idMascota = MS.idMascota AND MS.idSocio = ?");
		$query->bind_param('i', $idSocio);
		if($query->execute()){
			$result = $query->get_result();
			$arrayMascotas = array();
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$arrayMascotas[] = $row;
			}
			return $arrayMascotas;
		}
		return null;
	}
}