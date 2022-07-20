<?php

require_once '../src/clases/usuarios.php';
require_once '../src/clases/socios.php';

require_once '../src/clases/configuracionSistema.php';

require_once '../src/utils/validate.php';
require_once '../src/utils/fechas.php';

require_once '../src/controladores/ctr_mascotas.php';


class ctr_usuarios{

	public function getSociosVistaFactura(){
		return socios::getSociosVistaFactura();
	}

	public function getAllClients(){
		$sociosClass = new socios();
		return $sociosClass->getAllSocios();
	}

	public function gestComRestCuotas($idSocio, $ultimoPago, $ultimaCuota, $token){
		$response = new \stdClass();

		$currentDate = fechas::getCurrentDateInt();
		$myToken = base64_encode($currentDate . "gestcom1213");
		if(strcmp($token, $myToken) == 0){
			if(is_null($idSocio) || is_null($ultimoPago) || is_null($ultimaCuota)){
				$response->result = 0;
				$response->message = "Se espera recibir los campos: 'ultimoCuota', 'ultimoPago', 'numSocio' para procesar la consulta.";
			}else{
				$responseGetSocio = socios::getSocio($idSocio);
				if($responseGetSocio->result == 2){
					if(!ctype_digit($ultimoPago) || strlen($ultimoPago) != 8){
						$response->result = 0;
						$response->message = "La fecha 'ultimoPago' debe ser numérica y con formato yyyymmdd.";
					}else if(!ctype_digit($ultimaCuota) || strlen($ultimaCuota) !=  6){
						$response->result = 0;
						$response->message = "La fecha 'ultimaCuota' debe ser numérica y con formato yyyymm.";
					}else{
						$responseUpdateSocio = socios::updateGestcomSocio($idSocio, $ultimoPago, $ultimaCuota);
						if($responseUpdateSocio->result == 2){
							$response->result = 2;
							$response->message = "Socio actualizado!.";
						}else return $responseUpdateSocio;
					}
				}else{
					$response->result = 0;
					$response->message = "El 'numSocio' ingresado no corresponde a un socio en la base de datos.";
				}
			}
		}else{
			$response->result = 0;
			$response->message = "El Token de validación no es correcto.";
		}

		return $response;
	}

	public function getFileVistaFactura($token){
		$response = new \stdClass();

		$currentDate = fechas::getCurrentDateInt();
		$myToken = base64_encode($currentDate . "gestcom1213");
		if(!is_null($token)){
			if(strcmp($token, $myToken) == 0){
				//ctr_usuarios::updateStateSocio();
				//$responseGetSocios = ctr_usuarios::getSociosVistaFactura();
				$responseGetSocios = ctr_usuarios::getAllClients();
				if($responseGetSocios->result == 2){
					$stringList = "";
					foreach ($responseGetSocios->listResult as $key => $socio){
						$newArray = array();

						//"nro_deu","N",7,0
						if(is_null($socio['idSocio'])) $stringList .= "'',";
						else $stringList .= $socio['idSocio'] . ",";

						//"nombre","C",30,0
						if(is_null($socio['nombre'])) $stringList .= "'',";
						else $stringList .= $socio['nombre']. ",";


						//"calle","C",55,0
						if(is_null($socio['direccion'])) $stringList .= "'',";
						else $stringList .= $socio['direccion'] . ",";

						//"casa","N",5,0
						$stringList .= "'',";

						//"apto","N",4,0
						$stringList .= "'',";

						//"rut","C",12,0
						if($socio['rut']>0) $stringList .= $socio['rut'].",";
						else $stringList .= "'',";



						//"cant","N",2,0
						$cantMascotas = "''";
						$cuota = "0";
						$responseGetCantMascotas = ctr_mascotas::getSocioActivePets($socio['idSocio']);
						if($responseGetCantMascotas->result == 2){
							$cantMascotas = sizeof($responseGetCantMascotas->mascotas);

							//calcular el importe,
						/**
						 * calcular importe de cada cliente
						 * 0 cliente - $0
							1 socio fa $...
							2 ong - $0
							3 ex socio fa fb $0

							0 inactivo $0
							1 activo $...
						 */
							if ( $socio['estado'] == 1 && $socio['tipo'] == 1 ){
								$responseCalculateQuota = configuracionSistema::getQuotaSocio(sizeof($responseGetCantMascotas->mascotas), $socio['tipo']);
								if($responseCalculateQuota->result == 2)
									$cuota = $responseCalculateQuota->quota;
							}else $cuota = 0;
						}

						$stringList .= $cantMascotas . ",";


						//"imp","N",13,2
						$stringList .= $cuota . ",";



						//"lugar","C",15,0
						if($socio['lugarPago'] == 1)
							$stringList .= "Cobrador". ",";
						else
							$stringList .= "Veterinaria". ",";


						//"prox_vacu","C",11,0
						$stringList .= "'',";


						//"mascota","C",20,0
						$stringList .= "'',";

						//"fec_ingr","C",8,0
						if(is_null($socio['fechaIngreso'])) $stringList .= "'',";
						else $stringList .= $socio['fechaIngreso'].",";
						//else $stringList .= fechas::dateToFormatBar($socio['fechaIngreso']) . " 12:13:00,".chr(13).chr(10);

						//"fec_baja", ...
						if(is_null($socio['fechaBajaSocio'])) $stringList .= "'',";
						else $stringList .= $socio['fechaBajaSocio'].",";

						//"activo", boolean
						if(is_null($socio['estado'])) $stringList .= "''";
						else $stringList .= $socio['estado'];

						//para finalizar la línea
						$stringList .= chr(13).chr(10);
					}
					$response->result = 2;
					$response->string = base64_encode($stringList);
				}
			}else{
				$response->result = 0;
				$response->message = "El token de validación no es correcto.";
			}
		}else{
			$response->result = 0;
			$response->message = "Es necesario el token de validación.";
		}

		return $response;
	}

	public function gestcomNewClient($data=array())
	{
    	$userController = new ctr_usuarios();
		$response = new \stdClass();

		$response->cliente = 0;

		if ( count($data) > 0 ){
			$currentDate = fechas::getCurrentDateInt();
			$token = base64_encode($currentDate . "gestcom1213");
			if (isset($data['token'])){
				if ( $token == $data['token'] ){
					if ( isset($data['nombre']) ){
						if ( isset($data['cedula']) ){
							if ( isset($data['lugarpago']) ){
								$nombre = $data['nombre'];
								$lugarPago = $data['lugarpago'];
								$cedula = null;
								$rut = null;

								if(strlen($data['cedula'])>8)
									$rut = $data['cedula'];
								else $cedula = $data['cedula'];


								$direccion = null;
								$telefono = null;
								$fechaPago = null;
								$email = null;

								//no importa si no está definido el dato en la consulta
								if (isset($data['direccion']) && $data['direccion'] != "") $direccion = $data['direccion'];
								if (isset($data['telefono']) && $data['telefono'] != "") $telefono = $data['telefono'];
								if (isset($data['fechapago']) && $data['fechapago'] != "") $fechaPago = $data['fechapago'];
								if (isset($data['email'])  && $data['email'] != "") $email = $data['email'];

								$telefax = null;
								$fechaIngreso = null;
								$tipoSocio = 0;

								$responseInsert = $userController->insertNewSocio($nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $telefax, $fechaIngreso, $email, $rut, $tipoSocio);
								if ( isset($responseInsert->newIdSocio) ){
									$response->cliente = $responseInsert->newIdSocio;
								}else if(isset($responseInsert->cliente)){
									$response->cliente = $responseInsert->cliente;
								}
								$response->message = $responseInsert->message;
							}else {
								$response->result = 0;
								$response->message = "El lugar de pago debe estar definido y ser distinto de null.";
							}
						}else {
							$response->result = 0;
							$response->message = "La cédula debe estar definida y ser distinta de null.";
						}
					}else {
						$response->result = 0;
						$response->message = "El nombre debe estar definido y ser distinto de null.";
					}
				}else{
					$response->result = 0;
					$response->message = "El Token de validación no es correcto.";
				}
			}else {
				$response->result = 0;
				$response->message = "El Token debe estar definido y ser distinto de null.";
			}
		}else {
			$response->result = 0;
			$response->message = "No se han obtenido datos.";
		}
		return $response;
	}

	public function creteFile($arrayResult){
		if(is_array($arrayResult) && sizeof($arrayResult) > 0){
			$file = fopen('C:\Users\Usuario\Desktop\archivo\FACTURA.txt','w+');
			foreach ($arrayResult as $key => $value) {
				fwrite($file, $value['numero'] .",");
				fwrite($file, $value['socio'] .",");
				fwrite($file, $value['direccion'] .",");
				fwrite($file, $value['casa'] .",");
				fwrite($file, $value['apto'] .",");
				fwrite($file, $value['rut'] .",");
				fwrite($file, $value['cantidadmascotas'] .",");
				fwrite($file, $value['cuota'] .",");
				fwrite($file, $value['lugarpago'] .",");
				fwrite($file, $value['proximavacuna'] .",");
				fwrite($file, $value['mascota'] .",");
				fwrite($file, $value['fechaingreso'] . ",");

				fwrite($file,chr(13).chr(10));
			}
			fclose($file);
		}
	}

    //----------------------------------- FUNCIONES DE USUARIO ------------------------------------------

	public function deleteUser($idUser){
		$response = new \stdClass();

		$responseGetUser = usuarios::getUser($idUser);
		if($responseGetUser->result == 2){
			$responseGetUserInSesion = ctr_usuarios::getUserInSession();
			if($responseGetUserInSesion->result == 2){
				if($responseGetUserInSesion->user->idUsuario != $responseGetUser->objectResult->idUsuario){
					$responseDeleteUser = usuarios::deleteUser($idUser);
					if($responseDeleteUser->result == 2){
						$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Borrar usuario", null,null, "El usuario " . $responseGetUser->objectResult->nombre . " fue borrado del sistema.");
						if($responseInsertHistorial->result == 2){
							$response->result = 2;
							$response->message = "El usuario fue borrado del sistema.";
						}else{
							$response->result = 1;
							$response->message = "El usaurio fue borrado del sistema.";
						}
					}else return $responseDeleteUser;
				}else{
					$response->result = 0;
					$response->message = "No se puede borrar el usuario con el que mantiene una sesión activa.";
				}
			}else return $responseGetUserInSesion;
		}else return $responseGetUser;

		return $response;
	}

	public function cleanPassword($idUser){
		$response = new \stdClass();
		$responseGetUser = usuarios::getUser($idUser);
		if($responseGetUser->result == 2){
			$responseGetUserInSesion = ctr_usuarios::getUserInSession();
			if($responseGetUserInSesion->result == 2){
				if($responseGetUserInSesion->user->idUsuario != $responseGetUser->objectResult->idUsuario){
					$responseCleanPassword = usuarios::cleanPassword($idUser, "", "");
					if($responseCleanPassword->result == 2){
						$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Borrar usuario", null,null, "El usuario " . $responseGetUser->objectResult->nombre . " fue borrado del sistema.");
						if($responseInsertHistorial->result == 2){
							$response->result = 2;
							$response->message = "Contraseña borrada, la nueva contraseña se fijará al inciar sesión.";
						}else{
							$response->result = 1;
							$response->message = "Contraseña borrada, la nueva contraseña se fijará al inciar sesión.";
						}
					}else return $responseCleanPassword;
				}else{
					$response->result = 0;
					$response->message = "No se puede borrar la contraseña del usuario con el que mantiene una sesión activa.";
				}
			}else return $responseGetUserInSesion;
		}else return $responseGetUser;

		return $response;
	}

	public function getUserInSession(){
		$response = new \stdClass();

		if(isset($_SESSION['ADMIN'])){
			$session = $_SESSION['ADMIN'];
			$responseGetUser = usuarios::getUser($session['IDENTIFICADOR']);
			if($responseGetUser->result == 2){
				$response->result = 2;
				$response->user = $responseGetUser->objectResult;
			}else return $responseGetUser;
		}else{
			$response->result = 0;
			$response->message = "No se encontro una sesión activa.";
		}
		return $response;
	}

	public function validateSession(){
		$response = new \stdClass();

		if(isset($_SESSION['ADMIN'])){
			$session = $_SESSION['ADMIN'];
			if( isset($session['IDENTIFICADOR']) ){
				$responseGetUser = usuarios::getUser($session['IDENTIFICADOR']);
				if($responseGetUser->result == 2){
					if(strcmp($responseGetUser->objectResult->token, $session['TOKEN']) == 0){
						$response->result = 2;
						$response->session = $session;
					}else{
						$response->result = 0;
						$response->message = "Su sesión caduco, por favor vuelva a ingresar.";
					}
				}else return $responseGetUser;
			}
		}else{
			$response->result = 0;
			$response->message = "No se encontro una sesión activa.";
		}

		if($response->result != 2)
			session_destroy();

		return $response;
	}

	public function insertNewUsuario($name, $email){
		$response = new \stdClass();

		$responseGetUserName = usuarios::getUserName($name);
		if($responseGetUserName->result != 2){
			if(strlen($email) > 6){
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
					$response->result = 0;
					$response->message = "El correo ingresado no es valido.";
					return $response;
				}
			}else $email = null;

			$responseInsertNewUser = usuarios::insertUser($name, $email);
			if($responseInsertNewUser->result == 2){
				$responseGetUserInserted = usuarios::getUser($responseInsertNewUser->id);
				if($responseGetUserInserted->result == 2)
					$response->newUser = $responseGetUserInserted->objectResult;
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Usuario agregado", null ,null, "Se agrego el usuario " . $name .  " con email " .  $email . " por el administrador.");
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "El usuario fue agregado correctamente.";
				}else{
					$response->result = 1;
					$response->message = "El usuario fue agregado correctamente.";
				}
			}else return $responseInsertNewUser;
		}else{
			$response->result = 0;
			$response->message = "El nombreo de usuario ingresado ya fue registrado.";
		}

		return $response;
	}

	public function signIn($user, $password){
		$response = new \stdClass();

		$responseGetUsuario = usuarios::getUserName($user);
		if($responseGetUsuario->result == 2){
			if(!is_null($responseGetUsuario->objectResult->pass)){
				if(strcmp($responseGetUsuario->objectResult->pass, $password) == 0){
					$responseSignIn = usuarios::signIn($responseGetUsuario->objectResult->idUsuario);
					if($responseSignIn->result == 2){
						$response->result = 2;
					}else{
						$response->result = 0;
						$response->message = "Ocurrió un error y no se pudo iniciar sesión, por favor vuelva a intentarlo.";
					}
				}else{
					$response->result = 0;
					$response->message = "El usuario y contraseña ingresados no coinciden.";
				}
			}else{
				$responseUpdatePassword = usuarios::updateUserPassword($responseGetUsuario->objectResult->idUsuario, $password);
				if($responseUpdatePassword->result == 2){
					$responseSignIn = usuarios::signIn($responseGetUsuario->objectResult->idUsuario);
					if($responseSignIn->result == 2){
						$response->result = 2;
					}else{
						$response->result = 0;
						$response->message = "Ocurrió un error y no se pudo iniciar sesión por primera vez, por favor vuelva a intentarlo.";
					}
				}else return $responseUpdatePassword;
			}
		}else return $responseGetUsuario;

		return $response;
	}

	public function modificarUsuario($idUsuario, $usuario, $correo){
		$response = new \stdClass();

		$responseGetUsuario = usuarios::getUsuario($idUsuario);
		if($responseGetUsuario->result == 2){
			$responseGetUsuarioName = usuarios::getUsuarioNombre($usuario, $idUsuario);
			if($responseGetUsuarioName->result != 2){
				$responseUpdateUsuario = usuarios::updateUsuario($idUsuario, $usuario, $correo);
				if($responseUpdateUsuario->result == 2){
					$response->result = 2;
					$response->message = "El usuario fue modificado correctamente.";
					$responseUpdatedUser = usuarios::getUsuario($idUsuario);
					if($responseUpdatedUser->result == 2)
						$response->user = $responseUpdatedUser->objectResult;
				}else return $responseUpdateUsuario;
			}else{
				$response->result = 0;
				$response->message = "El nombre ingresado corresponde a otro usuario.";
			}
		}else return $responseGetUsuario;

		return $response;
	}

	public function updatePassUsuario($nombre, $passActual, $pass){

		$response = new \stdClass();
		$usuario = usuarios::getUsuarioNombre($nombre);
		if($usuario != null){

			if($usuario->pass == $passActual){
				$result = usuarios::updateUsuario($pass);

				if($result){
					//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificación de contraseña", "El usuario " . $nombre . " actualizó su contraseña.");
					if($resultInsertOperacionUsuario)
						$response->enHistorial = "Registrado en el historial del usuario.";
					else
						$response->enHistorial = "No ingresado en historial de usuario.";
					//----------------------------------------------------------------------------------------------------------------

					$response->retorno = true;
					$response->mensaje = "La contraseña del administrador fue modificada correctamente.";
				}else{
					$response->retorno = false;
					$response->mensajeError = "Ocurrió un error interno y la contraseña del administrador no fue modificada, por favor vuelva a intentarlo.";
				}
			}else {
				$response->retorno = false;
				$response->mensajeError = "La contraseña ingresada no corresponde al usuario administrador.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El usuario al que intenta modificar la contraseña no fue encontrado, por favor vuelva a intentarlo.";
		}
		return $response;
	}

	public function getUsuario($idUsuario){
		return usuarios::getUsuario($idUsuario);
	}

	public function getUsuarios(){
		return usuarios::getUsuarios();
	}
    //---------------------------------------------------------------------------------------------------

    //----------------------------------- FUNCIONES DE SOCIO --------------------------------------------

	public function desvincularMascota($idMascota){
		$response = new \stdClass();

		$responseGetMascota = ctr_mascotas::getMascota($idMascota);
		if($responseGetMascota->result == 2){
			$responseGetMascotaSocio = ctr_mascotas::getMascotaSocio($idMascota);
			if($responseGetMascotaSocio->result == 2){
				$responseDeleteVinculo = ctr_mascotas::deleteVinculoMascota($idMascota);
				if($responseDeleteVinculo->result == 2){
					$responseCalcultateQuota = ctr_usuarios::calculateQuotaSocio($responseGetMascotaSocio->objectResult->idSocio);
					if($responseCalcultateQuota->result == 2){
						$responseUpdateQuota =  socios::updateQuotaSocio($responseGetMascotaSocio->objectResult->idSocio, $responseCalcultateQuota->quota);
						if($responseUpdateQuota->result == 2){
							$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Desvincular mascota", $responseGetMascotaSocio->objectResult->idSocio, $idMascota, "Se desvinculo la mascota y se actualizó la cuota del socio.");
							if($responseInsertHistorial->result == 2){
								$response->result = 2;
								$response->message = "Se desvinculó la mascota " . $responseGetMascota->objectResult->nombre . " del socio seleccionado y su cuota fue actualizada.";
							}else{
								$response->result = 1;
								$response->message = "Se desvinculó la mascota " . $responseGetMascota->objectResult->nombre . " del socio seleccionado y su cuota fue actualizada.";
							}
							$response->newQuota = number_format($responseCalcultateQuota->quota, 2, ",", ".");
						}else return $responseUpdateQuota;
					}else return $responseCalcultateQuota;
				}else return $responseDeleteVinculo;
			}else return $responseGetMascotaSocio;
		}else return $responseGetMascota;

		return $response;
	}

	public function asignarMascotaSocio($idSocio, $idMascota){
		$response = new \stdClass();

		$responseGetSocio = socios::getSocio($idSocio);
		if($responseGetSocio->result == 2){
			$responseGetMascota = ctr_mascotas::getMascota($idMascota);
			if($responseGetMascota->result == 2){
				$responseGetMascotaSocio = ctr_mascotas::getMascotaSocio($idMascota);
				if($responseGetMascotaSocio->result == 1){
					$responseInsertMascotaSocio = ctr_mascotas::vincularMascotaSocio($idSocio, $idMascota);
					if($responseInsertMascotaSocio->result == 2){
						$resultQuota = "";
						$responseUpdateQuota = ctr_usuarios::actualizarCuotaSocio($idSocio);
						if($responseUpdateQuota->result == 2){
							$response->newQuota = $responseUpdateQuota->newQuota;
							$resultQuota = " y su cuota fue actualizada ";
						}
						$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Vincular mascota", $idSocio, $idMascota, "Se vinculó la mascota ". $responseGetMascota->objectResult->nombre ." al socio seleccionado.");
						if($responseInsertHistorial->result == 2){
							$response->result = 2;
							$response->message = "Se vinculó la mascota " . $responseGetMascota->objectResult->nombre . " al socio seleccionado ". $resultQuota .".";
						}else{
							$response->result = 1;
							$response->message = "Se vinculó la mascota " . $responseGetMascota->objectResult->nombre . " al socio seleccionado ". $resultQuota .".";
						}
						$responseGetMascotaResult = ctr_mascotas::getMascotaVinculadaToShow($idMascota);
						if($responseGetMascotaResult->result == 2)
							$response->newMascota = $responseGetMascotaResult->objectResult;
					}else return $responseInsertMascotaSocio;
				}else{
					$response->result = 0;
					$response->message = "La mascota seleccionada ya cuenta con un socio vinculado.";
				}
			}else return $responseGetMascota;
		}else return $responseGetSocio;

		return $response;
	}

	public function updateStateSocio(){
		$response = new \stdClass();

		$responseGetQuota = configuracionSistema::getQuota();
		if($responseGetQuota->result == 2){

			$fechaVencimiento = fechas::getYearMonthINT($responseGetQuota->objectResult->plazoDeuda);
			$responseGetSociosToInactive = socios::getSociosToInactive($fechaVencimiento);
			$responseSetInactive = socios::setInactiveStateSocio($fechaVencimiento);
			if($responseSetInactive->result  == 2){
				$responseDesactivarMascotas = ctr_mascotas::updateStateMascotas(1);
				if($responseDesactivarMascotas->result == 2){//$idSocio, $idMascota, $fecha, $asunto, $importe, $observaciones
					if($responseGetSociosToInactive->result == 2){
						foreach ($responseGetSociosToInactive->listResult as $key => $socioInactive) {
							ctr_historiales::crearHistorialSocio($socioInactive['idSocio'], null, fechas::getCurrentDateInt(), "Desactivación automática por morosidad.", null, null);
						}
					}

					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Actualizar estados de socio", null, null, "Se actualizaron los estados de todos los socios.");
					if($responseInsertHistorial->result == 2){
						$response->result = 2;
						$response->message = "Los estados de los socios fueron actualizados.";
					}else{
						$response->result = 0;
						$response->message = "Los estados de los socios fueron actualizados.";
					}
				}else return $responseDesactivarMascotas;
			}else return $responseSetInactive;
		}else return $responseGetQuota;

		return $response;
	}

	public function updateAllQuotaSocio($cuotaUno, $cuotaDos, $cuotaExtra, $plazoDeuda){
		$response = new \stdClass();

		$responseUpdateQuota = configuracionSistema::updateQuotaSistema($cuotaUno, $cuotaDos, $cuotaExtra, $plazoDeuda);
		if($responseUpdateQuota->result == 2){
			$responseGetNewQuota = configuracionSistema::getQuota();
			if($responseGetNewQuota->result == 2)
				$response->quota = $responseGetNewQuota->objectResult;

			$responseGetSociosActives = socios::getSociosWithMascotas();
			if($responseGetSociosActives->result == 2){
				$actualizados = array();
				$noActualizados = array();
				foreach ($responseGetSociosActives->listResult as $key => $socio) {
					$responseGetQuota = configuracionSistema::getQuotaSocio($socio['cantMascotas'], $socio['tipo']);
					if($responseGetQuota->result == 2){
						$responseUpdateQuota = socios::updateQuotaSocio($socio['idSocio'], $responseGetQuota->quota);
						if($responseUpdateQuota->result == 2)
							$actualizados[] = $socio['idSocio'];
						else
							$noActualizados[] = $socio['idSocio'];
					}else return $responseGetQuota;
				}

				if(sizeof($responseGetSociosActives->listResult) == sizeof($actualizados)){
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Actualización de cuotas", null, null, "Se actualizaron todas las cuotas de los socios.");
					if($responseInsertHistorial->result ==  2){
						$response->result = 2;
						$response->message = "Cuotas actualizadas correctamente";
					}else{
						$response->result = 2;
						$response->message = "Cuotas actualizadas correctamente.";
					}
				}else if(sizeof($responseGetSociosActives->listResult) < sizeof($actualizados)){
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Actualización de cuotas", null, null, "Se actualizaron las cuotas de los socios, para los socios " . implode(",", $noActualizados) . " las cuotas no fueron actualizadas por un error.");
					if($responseInsertHistorial->result ==  2){
						$response->result = 1;
						$response->message = "Las cuotas de los socios fueron actualizadas, por algun error " . sizeof($noActualizados) . " socios no actualizaron su cuota.";
					}else{
						$response->result = 1;
						$response->message = "No todas las cuotas de los socios fueron actualizados por un error, " . sizeof($noActualizados) . " socios no actualizaron su cuota.";
					}
				}
			}else return $responseGetSociosActives;
		}else return $responseUpdateQuota;

		return $response;
	}

	public function updateQuotaSocio($idSocio, $quota){
		return socios::updateQuotaSocio($idSocio, $quota);
	}

	public function actualizarCuotaSocio($idSocio){
		$response = new \stdClass();

		$responseCalcultateQuota = ctr_usuarios::calculateQuotaSocio($idSocio);
		if($responseCalcultateQuota->result == 2){
			$responseUpdateQuota =  socios::updateQuotaSocio($idSocio, $responseCalcultateQuota->quota);
			if($responseUpdateQuota->result == 2){
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Actulizar cuota", $idSocio, null, "Se actualizo la cuota del socio seleccionado.");
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "La cuota del socio fue actualizada correctamente.";
				}else{
					$response->result = 1;
					$response->message = "La cuota del socio fue actualizada correctamente.";
				}
				$response->newQuota = number_format($responseCalcultateQuota->quota, 2, ",", ".");
			}else{
				$response->result = 0;
				$response->message = "La cuota del socio no fue actualizada por un error interno.";
			}
		}else return $responseCalcultateQuota;

		return $response;
	}

	public function calculateQuotaSocio($idSocio){
		$response = new \stdClass();

		error_log("actualizar el valor de la cuota del cliente");
		$responseGetMascotasSocio = ctr_mascotas::getSocioActivePets($idSocio);
		if($responseGetMascotasSocio->result == 2){
			$socio = socios::getSocioById($idSocio);
			if( $socio->result == 2 ){
				$socioTipo = $socio->objectResult->tipo;
				$responseGetQuota = configuracionSistema::getQuotaSocio(sizeof($responseGetMascotasSocio->mascotas), $socioTipo);
				if($responseGetQuota->result == 2){
					$response->result = 2;
					$response->quota = $responseGetQuota->quota;
				}else{
					$response->result = 0;
					$response->message = "Ocurrió un error y la cuota del socio no pudo ser calculada.";
				}
			}else{
				$response->result = 0;
				$response->message = "No se conoce el tipo de cliente.";
			}
		}else{
			$response->result = 2;
			$response->quota = 0;
		}

		return $response;
	}

	public function getSocioToShow($idSocio){
		return socios::getSocioToShow($idSocio);
	}

	public function getSocioWithMascotaToShow($idSocio){
		$response = new \stdClass();

		$responseGetSocio = socios::getSocioToShow($idSocio);
		if($responseGetSocio->result == 2){
			$response->result = 2;
			$response->socio = $responseGetSocio->objectResult;
			$response->mascotas = ctr_mascotas::getMascotasSocio($idSocio);
		}else return $responseGetSocio;

		return $response;
	}

	public function insertNewSocio($nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $telefax, $fechaIngreso, $email, $rut, $tipoSocio){
		$response = new \stdClass();

		$responseValidateData = ctr_usuarios::validateInfoSocio($nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax);
		if($responseValidateData->result == 2){
			$responseGetSocio = socios::getSocioByCedula($cedula);
			if($responseGetSocio->result == 1){
				if (strlen($direccion)<=0)
					$direccion  = null;

				if (strlen($telefono)<=0)
					$telefono  = null;

				if (strlen($telefax)<=0)
					$telefax  = null;

				if (strlen($email)<=0)
					$email  = null;

				if (strlen($rut)<=0)
					$rut = null;


				/*if(!is_null($fechaIngreso))
					$fechaIngreso = fechas::getDateToINT($fechaIngreso);*/
				if ( $tipoSocio != 1 ){
					$fechaIngreso = null;
				}else $fechaIngreso = date("Ymd");

				if(!is_null($fechaPago))
					$fechaPago = fechas::getDateToINT($fechaPago);

				$responseInsertSocio = socios::insertSocio($nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $telefax, $fechaIngreso, $email, $rut, $tipoSocio);
				if($responseInsertSocio->result == 2){
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Nuevo socio ingresado", $responseInsertSocio->id, null, "Se ingresó un nuevo socio en el sistema con nombre " . $nombre . ".");
					if($responseInsertHistorial->result == 2){
						$response->result = 2;
						$response->message = "Socio creado correctamente.";
					}else{
						$response->result = 1;
						$response->message = "Socio creado correctamente.";
					}
					$response->newIdSocio = $responseInsertSocio->id;
				}else return $responseInsertSocio;
			}else{
				$response->result = 0;
				$response->cliente = $responseGetSocio->objectResult->idSocio;
				$response->message = "La cédula ingresada corresponde al socio registrado " . $responseGetSocio->objectResult->nombre;
			}
		}else return $responseValidateData;

		return $response;
	}

	public function updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax, $tipoSocioNuevo, $lugarPago, $fechaIngreso, $fechaBajaSocio, $ultimoPago, $fechaPago, $ultimoMesPago){

		$response = new \stdClass();
		$sociosClass = new socios();
		$responseGetSocio = socios::getSocio($idSocio);
		if($responseGetSocio->result == 2){

			$tipoSocio = $responseGetSocio->objectResult->tipo;
			$responseCedulaNotRepeated = socios::getSocioCedula($cedula, $idSocio);
			if($responseCedulaNotRepeated->result == 1){
				$responseValidateData = ctr_usuarios::validateInfoSocio($nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax);
				if($responseValidateData->result == 2){

					if ( !isset($fechaIngreso) ){
						$fechaIngreso = $responseGetSocio->objectResult->fechaIngreso;
					}

					if( !isset($fechaBajaSocio) ){
						$fechaBajaSocio = $responseGetSocio->objectResult->fechaBajaSocio;
					}

					if ( $tipoSocio != $tipoSocioNuevo ){
						$fechasTipoSocio = $sociosClass->clientTypeChangesDate( $tipoSocio, $tipoSocioNuevo );
						if ( $fechasTipoSocio->result == 2 ){
							if ( isset($fechasTipoSocio->dateInit) )
								$fechaIngreso = $fechasTipoSocio->dateInit;

							if ( isset($fechasTipoSocio->dateFinish) )
								$fechaBajaSocio = $fechasTipoSocio->dateFinish;
						}
					}

					if(!is_null($fechaIngreso)){
						$fechaIngreso = fechas::getDateToINT($fechaIngreso);
					}

					if(!is_null($fechaBajaSocio)){
						$fechaBajaSocio = fechas::getDateToINT($fechaBajaSocio);
					}

					if(!is_null($ultimoMesPago))
						$ultimoMesPago = str_replace("-","",$ultimoMesPago);

					if(!is_null($ultimoPago))
						$ultimoPago = fechas::getDateToINT($ultimoPago);

					$cuota = $responseGetSocio->objectResult->cuota;
					//if($responseGetQuota->result == 2){
						$responseUpdateSocio = socios::updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax, $tipoSocioNuevo, $lugarPago, $fechaIngreso, $ultimoPago, $fechaPago, $ultimoMesPago, $cuota, $fechaBajaSocio);

						if($responseUpdateSocio->result == 2){
							$responseGetQuota = ctr_usuarios::calculateQuotaSocio($idSocio);
							if ( $responseGetQuota->result == 2 ){
								$responseUpdateQuota =  socios::updateQuotaSocio($idSocio, $responseGetQuota->quota);
								if ( $responseUpdateQuota->result != 2 ){
									$response->result = 1;
									$response->message = "Se actualizó la información del socio. No se puedo actualizar el valor de la cuota.";
								}
							}
							//$responseHistorial = ctr_historiales::insertHistorialUsuario("Modificación de socio", "La informacion del socio " . $nombre . " fue actualizada en el sistema.");
							$responseHistorial = ctr_historiales::insertHistorialUsuario("Modificación de socio", $idSocio, null, "La informacion del socio " . $nombre . " fue actualizada en el sistema.");
							if($responseHistorial->result == 2){
								$response->result = 2;
								$response->message = "Se actualizó la información del socio.";
								$responseGetSocioUpdated = ctr_usuarios::getSocioToShow($idSocio);
								if($responseGetSocioUpdated->result == 2)
									$response->newSocio = $responseGetSocioUpdated->objectResult;
							}else{
								$response->result = 1;
								$response->message = "Se actualizó la información del socio.";
							}
						}else return $responseUpdateSocio;
					//}else return $responseGetQuota;
				}else return $responseValidateData;
			}else{
				$response->result = 0;
				$response->message = "La cédula ingresada ya pertenece al socio " . $responseCedulaNotRepeated->objectResult->nombre . ".";
			}
		}else return $responseGetSocio;

		return $response;
	}

	public function validateInfoSocio($nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax){
		$response = new \stdClass();

		$response->result = 2;

		if(is_null($nombre)){
			$response->result = 1;
			$response->message = "El nombre de usuario no puede ser ingresado vacio.";
		}else if(strlen($nombre) < 6 || strlen($nombre) > 50){
			$response->result = 1;
			$response->message = "El nombre de usuario debe tener una longitud entre los 6 y 50 caracteres.";
		}else if(preg_match("/[^a-zA-Z ]/", $nombre)){
			$response->result = 1;
			$response->message = "El nombre unicamente permite caracteres alfabeticos.";
		}

		if(is_null($cedula)){
			$response->result = 1;
			$response->message = "La cédula no puede ser ingresada nula.";
		}else if(!ctype_digit($cedula)){
			$response->result = 1;
			$response->message = "La cédula solo permite caracteres alfanuméricos.";
		}else if(strlen($cedula) < 7){
			$response->result = 1;
			$response->message = "La longitud de la cédula no es valida.";
		}else if(!validate::validateCI($cedula)){
			$response->result = 1;
			$response->message = "La cédula ingresada no es valida.";
		}

		if(!is_null($direccion) && strlen($direccion) > 1){
			if(strlen($direccion) < 6 || strlen($direccion) > 100){
				$response->result = 1;
				$response->message = "La dirección del socio debe tener una longitud entre los 6 y 100 caracteres.";
			}
		}

		if(!is_null($telefono) && strlen($telefono) > 1){
			if(!ctype_digit($telefono)){
				$response->result = 1;
				$response->message = "El teléfono solo puede contener caracteres numéricos.";
			}else if(strlen($telefono) < 8 && strlen($telefono > 9)){
				$response->result = 1;
				$response->message = "El teléfono debe tener una longitud entre los 8 y 9 caracteres.";
			}
		}

		if(!is_null($email) && strlen($email) > 1){
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$response->result = 0;
				$response->message = "El email ingresado no es valido.";
			}
		}

		if(!is_null($rut) && strlen($rut) > 1)
			$response = validate::validateRut($rut);

		if(!is_null($telefax) && strlen($telefax) > 1){
			if(strlen($telefax) < 6 || strlen($telefax) > 100){
				$response->result = 1;
				$response->message = "El telefax del socio debete tener una longitud entre los 6 y 100 caracteres.";
			}
		}

		return $response;
	}

	public function getSociosPagina($lastId, $estado, $textToSearch){
		$response = new \stdClass;

		$responseGetSocios = socios::getSociosPagina($lastId, $estado, $textToSearch);
		if($responseGetSocios->result == 2){
			$response->result = 2;
			$response->lastId = $responseGetSocios->lastId;
			$arrayResult = array();
			foreach ($responseGetSocios->listResult as $key => $value) {
				$responseGetCantMascotas = ctr_mascotas::getCantMascotas($value['idSocio']);
				if($responseGetCantMascotas->result == 2){
					$value['cantMascotas'] = $responseGetCantMascotas->objectResult->cantMascotas;
					$value['mascotas'] = array();
					if ( $value['cantMascotas'] > 0 ){
						$mascotasSocio = ctr_mascotas::getMascotasSocio($value['idSocio']);
						if ( $mascotasSocio->result == 2 ){
							$value['mascotas'] = $mascotasSocio->listMascotas;
						}else return $mascotasSocio;
					}
				}
				else
					$value['cantMascotas'] = "Sin mascotas";
				$arrayResult[] = $value;
			}
			$response->socios = $arrayResult;
		}else return $responseGetSocios;

		return $response;
	}

	public function sociosNoVinculados($idMascota){
		return socios::getSociosNoVinculados($idMascota);
	}

	public function getSocio($idSocio){
		$response = new \stdClass();

		$responseGetSocio = socios::getSocio($idSocio);
		if($responseGetSocio->result == 2){
			if(!is_null($responseGetSocio->objectResult->fechaIngreso))
				$responseGetSocio->objectResult->fechaIngreso = fechas::dateToFormatHTML($responseGetSocio->objectResult->fechaIngreso);
			if(!is_null($responseGetSocio->objectResult->fechaBajaSocio))
				$responseGetSocio->objectResult->fechaBajaSocio = fechas::dateToFormatHTML($responseGetSocio->objectResult->fechaBajaSocio);
			if(!is_null($responseGetSocio->objectResult->fechaUltimaCuota))
				$responseGetSocio->objectResult->fechaUltimaCuota = fechas::monthToFormatHTML($responseGetSocio->objectResult->fechaUltimaCuota);
			if(!is_null($responseGetSocio->objectResult->fechaUltimoPago))
				$responseGetSocio->objectResult->fechaUltimoPago = fechas::dateToFormatHTML($responseGetSocio->objectResult->fechaUltimoPago);

			$response->result = 2;
			$response->socio = $responseGetSocio->objectResult;
		}else return $responseGetSocio;
		return $response;
	}


	public function getSocioMascota($idMascota){
		$response = new \stdClass();

		$responseGetSocio = socios::getSocioMascota($idMascota);
		if($responseGetSocio->result == 2){
			$response->result = 2;
			$response->socio = $responseGetSocio->objectResult;
		}else return $responseGetSocio;

		return $response;
	}

	public function notificarSocioCuota($idSocio){
		$response = new \stdClass();

		$responseGetSocio = socios::getSocio($idSocio);
		if($responseGetSocio->result == 2){
			if(filter_var($responseGetSocio->objectResult->email, FILTER_VALIDATE_EMAIL)){
				$result = usuarios::enviarNotificacionCuota($responseGetSocio->objectResult->nombre, fechas::getYearMonthFormatBar($responseGetSocio->objectResult->fechaUltimaCuota), $responseGetSocio->objectResult->email);
				if($result){
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Notificar falta de pago",$idSocio, null, "Se notificó al socio a traves de un correo electrónico sobre su falta de pago.");
					if($responseInsertHistorial->result == 2){
						$response->result = 2;
						$response->message = "El socio fue notificado.";
					}else{
						$response->result = 1;
						$response->message = "El socio fue notificado.";
					}
				}else{
					$response->result = 0;
					$response->message = "Ocurrió un error y el email no pudo ser enviado.";
				}
			}else{
				$response->result = 0;
				$response->message = "El correo del socio seleccionado no es valido, modifiquelo para notificarlo.";
			}
		}else return $responseGetSocio;

		return $response;
	}

	public function notificarVacunaMascota($idMascota){
		$response = new \stdClass();

		$responseGetMascota = ctr_mascotas::getMascota($idMascota);
		if($responseGetMascota->result == 2){
			$responseGetSocioMascota = socios::getSocioMascota($idMascota);
			if($responseGetSocioMascota->result == 2){
				if(filter_var($responseGetSocioMascota->objectResult->email, FILTER_VALIDATE_EMAIL)){
					$responseGetVacunasVencidas = ctr_mascotas::getVacunasVencidasMascota($idMascota);
					if($responseGetVacunasVencidas->result == 2){
						$resultSendEmail = usuarios::enviarNotificacionVacunas($responseGetSocioMascota->objectResult->nombre, $responseGetVacunasVencidas->listResult, $responseGetSocioMascota->objectResult->email);
						if($resultSendEmail){
							$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Notificar vacunas/medicamento vencidas", $responseGetSocioMascota->objectResult->idSocio, $idMascota, "Se notificó los vencimientos de las vacunas/medicamentos de la mascota seleccionada al socio.");
							if($responseInsertHistorial->result == 2){
								$response->result = 2;
								$response->message = "Se notificó al socio.";
							}else{
								$response->result = 1;
								$response->message = "Se notificó al socio.";
							}
						}else{
							$response->result = 0;
							$response->message = "Ocurrió un error, no se pudo notificar al socio.";
						}
					}else return $responseGetVacunasVencidas;
				}else{
					$response->result = 0;
					$response->message = "El correo del socio seleccionado no es valido, modifiquelo para notificarlo.";
				}
			}else return $responseGetSocioMascota;
		}else return $responseGetMascota;

		return $response;
	}

	public function activarDesactivarSocio($idSocio){
		$response = new \stdClass();

		$responseGetSocio = socios::getSocio($idSocio);
		if($responseGetSocio->result == 2){
			$nuevoEstado = 0;
			$nuevoTextEstado = "Socio desactivado";
			if($responseGetSocio->objectResult->estado == 0){
				$nuevoEstado = 1;
				$nuevoTextEstado = "Socio activado";
			}

			$responseUpdateStateSocio = socios::changeStateSocio($idSocio, $nuevoEstado);
			if($responseUpdateStateSocio->result == 2){
				if($nuevoEstado == 0){
					$responseUpdateStateMascota = ctr_mascotas::changeStateMascotas($idSocio);
					if($responseUpdateStateMascota->result != 2)
						return $responseUpdateStateMascota;

					$responseUpdateQuota = socios::updateQuotaSocio($idSocio, 0);
					if($responseUpdateQuota->result != 2)
						return $responseUpdateQuota;
				}
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario($nuevoTextEstado . " socio", $idSocio, null, "Se cambio el estadod el socio y el de sus mascotas en caso de tenerlas.");
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = $nuevoTextEstado . " correctamente.";
				}else{
					$response->result = 1;
					$response->message = $nuevoTextEstado . " correctamente.";
				}
				$response->newState = $nuevoEstado;
			}else return $responseUpdateStateSocio;
		}else return $responseGetSocio;

		return $response;
	}

	public function buscadorDeSociosVencimientoCuota($nombreSocio){
		return socios::buscadorDeSociosVencimientoCuota($nombreSocio);
	}

	public function getCuotasVencidas($lastId, $textToSearch){
		$responsePlazoDeuda = configuracionSistema::getQuota();
		if($responsePlazoDeuda->result == 2){
			return socios::getCuotasVencidas($lastId, $textToSearch, $responsePlazoDeuda->objectResult->plazoDeuda);
		}else return $responsePlazoDeuda;
	}

	public function esMiMascota($idSocio, $idMascota){
		$result = socios::esMiMascota($idSocio, $idMascota);
		if($result != null) return true;
		else return false;
	}

	public function buscadorSocioNombre($nombreSocio, $estadoSocio){
		return socios::buscadorSocioNombre($nombreSocio, $estadoSocio);
	}
}
