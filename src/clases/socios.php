<?php

class socios{

	private $idSocio;
	private $cedula;
	private $nombre;
	private $telefono;
	private $telefax;
	private $direccion;
	private $fechaIngreso;
	private $fechaPago;
	private $lugarPago;
	private $estado; //activo = 1 inactivo = 0 Honorario = 4 No socio = 2 Sin Mascota = 3
	private $motivoBaja;
	private $cuota;
	private $email;
	private $rut;
	private $fechaUltimoPago;
	private $fechaUltimaCuota;

	public function __construct($idSocio, $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUltimoPago, $fechaUltimaCuota){
		$this->idSocio = $idSocio;
		$this->nombre = $nombre;
		$this->cedula = $cedula;
		$this->telefono = $telefono;
		$this->telefax = $telefax;
		$this->direccion = $direccion;
		$this->fechaIngreso = $fechaIngreso;
		$this->fechaPago = $fechaPago;
		$this->lugarPago = $lugarPago;
		$this->estado = $estado;
		$this->motivoBaja = $motivoBaja;
		$this->cuota = $cuota;
		$this->email = $email;
		$this->rut = $rut;
		$this->fechaUltimoPago = $fechaUltimoPago;
		$this->fechaUltimaCuota = $fechaUltimaCuota;
	}

	public function getSocios(){
		$query = DB::conexion()->prepare("SELECT * FROM socios LIMIT 100");
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
		$query = DB::conexion()->prepare("SELECT * FROM socios WHERE idSocio NOT IN (SELECT idSocio FROM mascotasocio WHERE idMascota != ?)");
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

	public function insertSocio($cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUPago, $fechaUCuota){
		$conexion = DB::conexion();
		$query = $conexion->prepare("INSERT INTO socios (cedula, nombre, telefono, telefax, direccion, fechaIngreso, fechaPago, lugarPago, estado, motivoBaja, cuota, email, rut, fechaUltimoPago, fechaUltimaCuota) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$query->bind_param('sssssiiiisissii', $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUPago, $fechaUCuota);
		if($query->execute()) return $conexion->insert_id;
		else return false;
	}

	public function updateSocio($idSocio, $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $email, $rut){
		$query = DB::conexion()->prepare("UPDATE socios SET cedula = ?, nombre = ?, telefono = ?, telefax = ?, direccion = ?, fechaIngreso = ?, fechaPago = ?, lugarPago = ?, email = ?, rut = ? WHERE idSocio = ?");
		$query->bind_param('sssssiiissi', $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $email, $rut, $idSocio);
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

	public function esMiMascota($idSocio, $idMascota){
		$query = DB::conexion()->prepare("SELECT * FROM mascotasocio WHERE idMascotaSocio = (SELECT MAX(idMascotaSocio) FROM mascotasocio WHERE idSocio = ? AND idMascota = ? )");
		$query->bind_param('ii', $idSocio, $idMascota);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}
}
