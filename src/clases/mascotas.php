<?php

require_once 'fechas.php';

class mascotas{
 //0 INACTIVA 1 ACTIVA 2 PENDIENTE
	//MACHO 1 HEMBRA 0

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

	public function getMin($mascotas, $maxValor){
		$valorMinimo = $maxValor;
		foreach ($mascotas as $key => $value) {
			if($value['idMascota'] < $valorMinimo)
				$valorMinimo = $value['idMascota'];
		}
		return $valorMinimo;
	}

	public function getMascotaMaxId($estadoMascota){
		$query = DB::conexion()->prepare("SELECT MAX(idMascota) AS idMaximo FROM mascotas WHERE estado = ?");
		$query->bind_param('i', $estadoMascota);
		if($query->execute()){
			$result = $query->get_result();
			return $result->fetch_object();
		}else return null;
	}

	public function getMascotasPagina($ultimoID, $estadoMascota){
		$query = DB::conexion()->prepare("SELECT * FROM mascotas WHERE estado = ? AND idMascota <= ? ORDER BY idMascota DESC LIMIT 10");
		$query->bind_param('ii', $estadoMascota, $ultimoID);
		if($query->execute()){
			$result = $query->get_result();
			$arrayResult = array();
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$row['fechaNacimiento'] = fechas::parceFechaFormatDMA($row['fechaNacimiento'], "/");
				if($row['sexo'] == 0) $row['sexo'] = "Hembra";
				else $row['sexo'] = "Macho";
				$arrayResult[] = $row;
			}
			return $arrayResult;
		}
		return null;
	}

	public function buscadorMascotaNombre($nombreMascota, $estadoMascota){
		$query = DB::conexion()->prepare("SELECT * FROM mascotas WHERE  estado = ? AND nombre LIKE '%" . $nombreMascota ."%' LIMIT 10 ");
		$query->bind_param('i', $estadoMascota);
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