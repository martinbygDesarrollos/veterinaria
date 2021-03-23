<?php
class usuarios{


	private $idUsuario;
	private $nombre;
	private $pass;
	private $estado;

	public function __construct($idUsuario, $nombre, $pass, $estado){

		$this->idUsuario = $idUsuario;
		$this->nombre = $nombre;
		$this->pass = $pass;
		$this->estado = $estado;
	}

	public function getUsuarios(){

		$query = DB::conexion()->prepare("SELECT idUsuario, nombre FROM usuarios WHERE nombre != 'admin'");
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

		$query = DB::conexion()->prepare("SELECT * FROM usuarios WHERE idUsuario = ? ");
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

		$query = DB::conexion()->prepare("SELECT * FROM usuarios WHERE nombre = ?");
		$query->bind_param('s', $nombre);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function insertUsuario($nombre, $pass){

		$query = DB::conexion()->prepare("INSERT INTO usuarios(nombre, pass) VALUES (?,?)");
		$query->bind_param('ss', $nombre, $pass);
		return $query->execute();
	}

	public function updateUsuario($idUsuario, $nombre, $email){

		$query = DB::conexion()->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE idUsuario = ?");
		$query->bind_param('ssi', $nombre, $email, $idUsuario);
		return $query->execute();
	}

	public function updatePasswordUsuario($nombre, $pass){
		$query = DB::conexion()->prepare("UPDATE usuarios SET pass = ? WHERE nombre = ?");
		$query->bind_param('ss', $pass, $nombre);
		return $query->execute();
	}

	public function enviarNotificacionVacunas($mensaje, $email, $vacunas){
		$header  = 'MIME-Version: 1.0' . "\r\n";
		$header .= 'Content-type:text/html; charset=UTF-8' . "\r\n";
		$header .= "From: Veterinaria Nan <veterinariaNan@byg.uy>" . "\r\n";


		$tableBody = '';

		foreach ($vacunas as $key => $value) {
			$tableBody .= '<tr style="background-color: "><th>'. $value['nombreVacuna'] .'</th><th>'. $value['fechaProximaDosis'] .'</th></tr>';
		}

		$subtitulo = "Veterinaria Nan";
		$mensaje = '<html>' .
		'<head>' .
		'<title>Veterinaria Nan</title>' .
		'<head>' .
		'<style>
		.fondo{
			border: 2px solid  #06692C;
			border-radius: 25px;
			background-color: #06692C;
			padding: 15px;
		}
		.message{
			text-align: center;
			color:white;
			font-size:25px;
		}
		table, th, td {
			border: 1px solid white;
			border-collapse: collapse;
			color: white
		}



		</style>' .
		'</head>' .
		'<body class="fondo"><h1 style="color: white; font-size:35px; text-align: center;">' . $subtitulo . '</h1>' .
		'<p class="message">' . $mensaje . '</p>' .
		'<div align="center">
		<table style="width:70%">
		<tr>
		<th>Nombre vacuna</th>
		<th>Fecha Dosis</th>
		</tr>
		'. $tableBody . '
		</table>
		</div>' .
		'</body>' .
		'</html>';

		return mail($email, $subtitulo, $mensaje, $header);
	}

	//----------------------------------------------------------------------------------------------------------
}