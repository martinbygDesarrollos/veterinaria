<?php

require_once '../src/clases/usuarios.php';
require_once '../src/clases/socios.php';

require_once '../src/clases/configuracionSistema.php';

require_once '../src/utils/validate.php';
require_once '../src/utils/fechas.php';

require_once '../src/controladores/ctr_mascotas.php';


class ctr_usuarios{
    //----------------------------------- FUNCIONES DE USUARIO ------------------------------------------

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
		}else{
			$response->result = 0;
			$response->message = "No se encontro una sesión activa.";
		}

		if($response->result != 2)
			session_destroy();

		return $response;
	}

	public function insertNewUsuario($nombre, $email){
		$response = new \stdClass();
		$usuario = usuarios::getUsuarioNombre($nombre);

		if(!$usuario){
			$result = usuarios::insertUsuario($nombre, $email);
			if($result){
				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Usuario agregado", "fue agregado el usuario " . $nombre . " y email " . $email . " por el administrador del sistema.");
				if($resultInsertOperacionUsuario)
					$response->enHistorial = "Registrado en el historial del usuario.";
				else
					$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------
				$response->retorno = true;
				$response->mensaje = "El usuario fue ingresado correctamente, al iniciar sesión por primera vez se fijará la contraseña ingresada.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "Ocurrió un error interno y el usuario no pudo ser ingresado correctamente, por favor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El usuario que se esta intentando ingresar ya existe en el sistema.";
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
			}else{
				$response->result = 0;
				$response->message = "El usuario y contraseña ingresados no coinciden.";
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
								$response->message = "Se desvinculó la mascota " . $responseGetMascota->objectResult->nombre . " del socio seleccionado y su cuota fue actualizada. Se generó un registro en el historial.";
							}else{
								$response->result = 1;
								$response->message = "Se desvinculó la mascota " . $responseGetMascota->objectResult->nombre . " del socio seleccionado y su cuota fue actualizada. Por un error interno no se generó un registro en el historial de usuario.";
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
				$responseGetMascotaSocio = ctr_mascotas::mascotaIsVinculada($idMascota);
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
							$response->message = "Se vinculó la mascota " . $responseGetMascota->objectResult->nombre . " al socio seleccionado ". $resultQuota .", se creo un registro en el historial de usuario.";
						}else{
							$response->result = 1;
							$response->message = "Se vinculó la mascota " . $responseGetMascota->objectResult->nombre . " al socio seleccionado ". $resultQuota .", por un error interno no se pudo generar un registro en el historial de usuario.";
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

	public function updateAllQuotaSocio($cuotaUno, $cuotaDos, $cuotaExtra, $plazoDeuda){
		$response = new \stdClass();

		$responseUpdateQuota = configuracionSistema::updateQuotaSistema($cuotaUno, $cuotaDos, $cuotaExtra, $plazoDeuda);
		if($responseUpdateQuota->result == 2){
			$responseGetNewQuota = configuracionSistema::getQuota();
			if($responseGetNewQuota->result == 2)
				$response->quota = $responseGetNewQuota->objectResult;
			$fechaVencimiento = fechas::getYearMonthINT($plazoDeuda);
			$responseSetInactive = socios::setInactiveStateSocio($fechaVencimiento);
			if($responseSetInactive->result  == 2){
				$responseSetActive = socios::setActiveStateSocio($fechaVencimiento);
				if($responseSetActive->result == 2){
					$responseDesactivarMascotas = ctr_mascotas::updateStateMascotas(1);
					if($responseDesactivarMascotas->result == 2){
						$responseGetSociosActives = socios::getSociosWithMascotas();
						$actualizados = array();
						$noActualizados = array();
						foreach ($responseGetSociosActives->listResult as $key => $socio) {
							$responseGetQuota = configuracionSistema::getQuotaSocio($socio['cantMascotas']);
							if($responseGetQuota->result == 2){
								$responseUpdateQuota = socios:: updateQuotaSocio($socio['idSocio'], $responseGetQuota->quota);
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
								$response->message = "Los estados y las cuotas de los socios fueron actualizadas correctamente, se generó un registro en el historial de usuario.";
							}else{
								$response->result = 2;
								$response->message = "Los estados y las cuotas de los socios fueron actualizadas correctamente, pero un error no permitió crear un registro en el historial de usuario.";
							}
						}else if(sizeof($responseGetSociosActives->listResult) < sizeof($actualizados)){
							$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Actualización de cuotas", null, null, "Se actualizaron las cuotas de los socios, para los socios " . implode(",", $noActualizados) . " las cuotas no fueron actualizadas por un error.");
							if($responseInsertHistorial->result ==  2){
								$response->result = 1;
								$response->message = "Los estados y cuotas de los socios fueron actualizadas, por algun error " . sizeof($noActualizados) . " socios no actualizaron su cuota. se generó un registro en el historial de usuario.";
							}else{
								$response->result = 1;
								$response->message = "No todos los estados y cuotas de los socios fueron actualizados por un error, " . sizeof($noActualizados) . " socios no actualizaron su cuota. No se generó un registro en el historial de usuario.";
							}
						}
					}else {
						$response->result = 0;
						$response->message = "Los estados de las mascotas no fueron actualizados por un error interno, por favor vuelva a intentarlo.";
					}
				}else return $responseSetActive;
			}else return $responseSetInactive;
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
					$response->message = "La cuota del socio fue actualizada correctamente y se creo un registro en el historial de usuario.";
				}else{
					$response->result = 1;
					$response->message = "La cuota del socio fue actualizada correctamente, pero un error interno no permitió crear un registro en el historial de usaurio.";
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

		$responseGetMascotasSocio = ctr_mascotas::getSocioActivePets($idSocio);
		if($responseGetMascotasSocio->result == 2){
			$responseGetQuota = configuracionSistema::getQuotaSocio(sizeof($responseGetMascotasSocio->mascotas));
			if($responseGetQuota->result == 2){
				$response->result = 2;
				$response->quota = $responseGetQuota->quota;
			}else{
				$response->result = 0;
				$response->message = "Ocurrió un error y la cuota del socio no pudo ser calculada.";
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
				if(!is_null($fechaIngreso))
					$fechaIngreso = fechas::getDateToINT($fechaIngreso);

				if(!is_null($fechaPago))
					$fechaPago = fechas::getDateToINT($fechaPago);

				$responseInsertSocio = socios::insertSocio($nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $telefax, $fechaIngreso, $email, $rut, $tipoSocio);
				if($responseInsertSocio->result == 2){
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Nuevo socio ingresado", $responseInsertSocio->id, null, "Se ingresó un nuevo socio en el sistema con nombre " . $nombre . ".");
					if($responseInsertHistorial->result == 2){
						$response->result = 2;
						$response->message = "El nuevo socio fue creado correctamente y se creo un registro en el historial.";
					}else{
						$response->result = 1;
						$response->message = "El nuevo socio fue creado correctamente, pero no se generó el registro en su historial de usuario por un error interno.";
					}
					$response->newIdSocio = $responseInsertSocio->id;
				}else return $responseInsertSocio;
			}else{
				$response->result = 0;
				$response->message = "La cédula ingresada corresponde al socio registrado " . $responseGetSocio->objectResult->nombre;
			}
		}else return $responseValidateData;

		return $response;
	}

	public function updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax, $tipoSocio, $lugarPago, $fechaIngreso, $ultimoPago, $fechaPago, $ultimoMesPago){
		$response = new \stdClass();

		$responseGetSocio = socios::getSocio($idSocio);
		if($responseGetSocio->result == 2){
			$responseCedulaNotRepeated = socios::getSocioCedula($cedula, $idSocio);
			if($responseCedulaNotRepeated->result == 1){
				$responseValidateData = ctr_usuarios::validateInfoSocio($nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax);
				if($responseValidateData->result == 2){

					if(!is_null($fechaIngreso))
						$fechaIngreso = fechas::getDateToINT($fechaIngreso);

					if(!is_null($ultimoMesPago))
						$ultimoMesPago = fechas::getDateWithMonthYearToINT($ultimoMesPago);

					if(!is_null($ultimoPago))
						$ultimoPago = fechas::getDateToINT($ultimoPago);

					$responseGetQuota = ctr_usuarios::calculateQuotaSocio($idSocio);
					if($responseGetQuota->result == 2){
						$responseUpdateSocio = socios::updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax, $tipoSocio, $lugarPago, $fechaIngreso, $ultimoPago, $fechaPago, $ultimoMesPago, $responseGetQuota->quota);
						if($responseUpdateSocio->result == 2){
							$responseHistorial = ctr_historiales::insertHistorialUsuario("Modificación de socio", "La informacion del socio " . $nombre . " fue actualizada en el sistema.");
							if($responseHistorial->result == 2){
								$response->result = 2;
								$response->message = "Se actualizó la información del socio y se generó un registro en el historial de su usuario.";
								$responseGetSocioUpdated = ctr_usuarios::getSocioToShow($idSocio);
								if($responseGetSocioUpdated->result == 2)
									$response->newSocio = $responseGetSocioUpdated->objectResult;
							}else{
								$response->result = 1;
								$response->message = "Se actualizó la información del socio, pero no se generó el registro en su historial de usuario por un error interno.";
							}
						}else return $responseUpdateSocio;
					}else return $responseGetQuota;
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
				if($responseGetCantMascotas->result == 2)
					$value['cantMascotas'] = $responseGetCantMascotas->objectResult->cantMascotas;
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
						$response->message = "El socio fue notificado por su falta de pago. se registro en el historial de usuario el envió.";
					}else{
						$response->result = 1;
						$response->message = "El socio fue notificado por su falta de pago, pero la operación no se registro en el historial de usuario.";
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
							$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Notificar vacunas vencidas", $responseGetSocioMascota->objectResult->idSocio, $idMascota, "Se notificó los vencimientos de las vacunas de la mascota seleccionada al socio.");
							if($responseInsertHistorial->result == 2){
								$response->result = 2;
								$response->message = "Se notificó al socio por el vencimiento de vacunas de su mascota y se generó un registro en el historial de usuario.";
							}else{
								$response->result = 1;
								$response->message = "Se notificó al socio por el vencimiento de vacuans de su mascota, pero un error no permitió registrarlo en el historial de usaurio.";
							}
						}else{
							$response->result = 0;
							$response->message = "Ocurrió un error y no se pudo notificar al socio por el vencimiento de vacunas.";
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
					$response->message = $nuevoTextEstado . " correctamente y se creo un registro en el historial de usuario.";
				}else{
					$response->result = 1;
					$response->message = $nuevoTextEstado . " correctamente pero un error interno no permitio crear un registro en el historial de usuario.";
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
