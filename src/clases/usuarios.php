<?php
class usuarios{

	public function cleanPassword($idUser, $password, $token){
		return DataBase::sendQuery("UPDATE usuarios SET pass = ?, token = ? WHERE idUsuario = ? " , array('ssi', $password, $token, $idUser), "BOOLE");
	}

	public function deleteUser($idUser){
		return DataBase::sendQuery("DELETE FROM usuarios WHERE idUsuario = ?", array('i', $idUser), "BOOLE");
	}

	public function desableUser($idUser){
		return DataBase::sendQuery("UPDATE usuarios SET activo = 0 WHERE idUsuario = ?", array('i', $idUser), "BOOLE");
	}

	public function updateUserPassword($idUser, $password){
		return DataBase::sendQuery("UPDATE usuarios SET pass = ? WHERE idUsuario = ?", array('si', $password, $idUser), "BOOLE");
	}

	public function getToken(){
		$longitud = 150;
		return bin2hex(random_bytes(($longitud - ($longitud % 2)) / 2));
	}

	public function getUser($idUser){
		$responseQuery = DataBase::sendQuery("SELECT * FROM usuarios WHERE idUsuario = ? AND activo = 1", array('i', $idUser), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "El identificador ingresado no corresponde a un usuario del sistema.";

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
		return DataBase::sendQuery("SELECT idUsuario, nombre, email FROM usuarios WHERE nombre != 'admin' AND activo = 1 ", null, "LIST");
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
		$responseQuery = DataBase::sendQuery("SELECT * FROM usuarios WHERE nombre = ? AND activo = 1 ", array('s', $name), "OBJECT");
		if($responseQuery->result == 1)
			$responseQuery->message = "No se encontro un usuario con ese nombre en el sisitema.";

		return $responseQuery;
	}

	public function insertUser($nombre, $pass){
		return DataBase::sendQuery("INSERT INTO usuarios(nombre, email) VALUES (?,?)", array('ss', $nombre, $pass), "BOOLE");
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

	public function enviarNotificacionVacunas($nombreSocio, $vacunas, $email){
		$header  = 'MIME-Version: 1.0' . "\r\n";
		$header .= 'Content-type:text/html; charset=UTF-8' . "\r\n";
		$header .= "From: Veterinaria Nan <veterinariaNan@byg.uy>" . "\r\n";


		$tableBody = '';

		foreach ($vacunas as $key => $value) {
			$tableBody .= '<tr style="background-color: "><th>'. $value['nombreVacuna'] .'</th>';
			$tableBody .= '<th>'. $value['fechaUltimaDosis'] .'</th>';
			$tableBody .= '<th>'. $value['fechaProximaDosis'] .'</th>';
			$tableBody .= '<th>'. $value['nombre'] .'</th></tr>';
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
		'<p class="message">' . $nombreSocio . ' Su mascota  registra las siguientes vacunas o medicamentos vencidos o próximos a vencer.</p>' .
		'<div align="center">
		<table style="width:70%">
		<tr>
		<th></th>
		<th>Última dosis</th>
		<th>Próxima Dosis</th>
		<th>Mascota</th>
		</tr>
		'. $tableBody . '
		</table>
		</div>' .
		'</body>' .
		'</html>';

		return mail($email, $subtitulo, $mensaje, $header);
	}

	public function enviarNotificacionCuota($nombreSocio, $fechaUltimaCuota, $email){
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
		'<p class="message">' . $nombreSocio . ' se le informa que su último pago registrado es el correspondiente al mes de ' . $fechaUltimaCuota . ' se le solicita que realize el abono correspondiente.</p>'.
		'</body>' .
		'</html>';

		return mail($email, $subtitulo, $mensaje, $header);
	}

	//----------------------------------------------------------------------------------------------------------
}