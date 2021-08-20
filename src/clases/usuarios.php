<?php
class usuarios{

	public function updateUserPassword($idUser, $password){
		return DataBase::sendQuery("UPDATE usuarios SET pass = ? WHERE idUsuario = ?", array('si', $password, $idUser), "BOOLE");
	}

	public function getToken(){
		$longitud = 150;
		return bin2hex(random_bytes(($longitud - ($longitud % 2)) / 2));
	}

	public function getUser($idUser){
		$responseQuery = DataBase::sendQuery("SELECT * FROM usuarios WHERE idUsuario = ? ", array('i', $idUser), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "EL identificador ingresado no corresponde a un usuario del sistema.";

		return $responseQuery;
	}

	public function signIn($idUser){
		$responseGetUser = usuarios::getUser($idUser);
		if($responseGetUser->result == 2){
			$token = usuarios::getToken();
			$responseQuery = DataBase::sendQuery("UPDATE usuarios SET token = ? WHERE idUsuario = ?", array('si', $token, $idUser), "BOOLE");
			if($responseQuery->result == 2){
				$_SESSION['ADMIN'] = array(
					"IDENTIFICADOR" => $responseGetUser->objectResult->idUsuario,
					"USUARIO" => $responseGetUser->objectResult->nombre,
					"EMAIL" => $responseGetUser->objectResult->email,
					"TOKEN" => $token
				);
			}else if($responseQuery->result == 1) $responseQuery->message = "El usuario no fue encontrado en la base de datos y no pudo iniciarse sesión.";
			return $responseQuery;
		}else return $responseGetUser;
	}

	public function getUsuarios(){
		return DataBase::sendQuery("SELECT idUsuario, nombre, email FROM usuarios WHERE nombre != 'admin'", null, "LIST");
	}

	public function getUsuario($idUsuario){
		return DataBase::sendQuery("SELECT * FROM usuarios WHERE idUsuario = ? ", array('i', $idUsuario), "OBJECT");
	}

	public function getUsuarioNombre($nombre, $idUsuario){
		return DataBase::sendQuery("SELECT * FROM usuarios WHERE nombre = ? AND idUsuario != ?", array('si', $nombre, $idUsuario), "OBJECT");
	}

	public function getUsuarioEstado($idUsuario, $estado){

		$query = DB::conexion()->prepare("SELECT * FROM usuarios WHERE idUsuario = ? AND estado = ?");
		$query->bind_param('ii', $idUsuario, $estado);
		if($query->execute()){
			$response = $query->get_result();
			return $response->fetch_object();
		}else return null;
	}

	public function getUserName($name){
		$responseQuery = DataBase::sendQuery("SELECT * FROM usuarios WHERE nombre = ?", array('s', $name), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontro un usuario con ese nombre en el sisitema.";

		return $responseQuery;
	}

	public function insertUsuario($nombre, $pass){

		$query = DB::conexion()->prepare("INSERT INTO usuarios(nombre, pass) VALUES (?,?)");
		$query->bind_param('ss', $nombre, $pass);
		return $query->execute();
	}

	public function updateUsuario($idUsuario, $nombre, $email){
		return DataBase::sendQuery("UPDATE usuarios SET nombre = ?, email = ? WHERE idUsuario = ?", array('ssi', $nombre, $email, $idUsuario), "BOOLE");
	}

	public function validarSesionActiva($nomUsuario, $token){
		$query = DB::conexion()->prepare("SELECT * FROM usuarios WHERE nombre = ? AND token = ? ");
		$query->bind_param('ss', $nomUsuario, $token);
		if($query->execute()){
			$result = $query->get_result();
			return $result->fetch_object();
		}else return null;
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

	public function enviarNotificacionCuota($mensaje, $email){
		$header  = 'MIME-Version: 1.0' . "\r\n";
		$header .= 'Content-type:text/html; charset=UTF-8' . "\r\n";
		$header .= "From: Veterinaria Nan <veterinariaNan@byg.uy>" . "\r\n";


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
		'</body>' .
		'</html>';

		return mail($email, $subtitulo, $mensaje, $header);
	}

	//----------------------------------------------------------------------------------------------------------
}