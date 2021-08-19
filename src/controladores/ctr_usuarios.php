<?php

/**
 *
 */
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

	public function updateUsuario($idUsuario, $nombre, $email){
		$response = new \stdClass();

		$usuario = usuarios::getUsuario($idUsuario);

		if($usuario){
			$usuarioNombre = usuarios::getUsuarioNombre($nombre);
			if($usuarioNombre->idUsuario == $usuario->idUsuario){
				$result = usuarios::updateUsuario($idUsuario, $nombre, $email);
				if($result){

					//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificación de usuario", "La información del usuario " . $nombre . " fue actualizada en el sistema.");
					if($resultInsertOperacionUsuario)
						$response->enHistorial = "Registrado en el historial del usuario.";
					else
						$response->enHistorial = "No ingresado en historial de usuario.";
					//----------------------------------------------------------------------------------------------------------------

					$response->retorno = true;
					$response->mensaje = "El usuario " . $nombre . " fue modificado correctamente.";
				}else{
					$response->retorno = false;
					$response->mensajeError = "El usuario " . $nombre . " no pudo ser modificado, por favor vuelva a intentarlo.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "El nombre de usuario que desea asignar ya fue vinculado a otro usuario, ingrese uno distinto.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El usuario que desea modificar no fue encontrado en el sistema, por favor vuelva a intentarlo.";
		}

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

	public function getUsuarioNombre($nombre){
		return usuarios::getUsuarioNombre($nombre);
	}

	public function getUsuarios(){
		return usuarios::getUsuarios();
	}
    //---------------------------------------------------------------------------------------------------

    //----------------------------------- FUNCIONES DE SOCIO --------------------------------------------

	public function updateQuotaSocio($idSocio, $quota){
		return socios::updateQuotaSocio($idSocio, $quota);
	}

	public function actualizarCuotaSocio($idSocio){
		$response = new \stdClass();

		$responseCalcultateQuota = ctr_usuarios::calculateQuotaSocio($idSocio);
		if($responseCalcultateQuota->result == 2){
			$responseUpdateQuota =  socios::updateQuotaSocio($idSocio, $responseCalcultateQuota->quota);
			if($responseUpdateQuota->result == 2){
				$response->result = 2;
				$response->message = "La cuota del socio fue actualizada correctamente.";
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
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Nuevo socio ingresado", "Se ingresó un nuevo socio en el sistema con nombre " . $nombre . ".");
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
		}else if(strlen($cedula) != 8){
			$response->result = 1;
			$response->message = "La cédula del socio debe tener una longitud de 9 caracteres alfanuméricos.";
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
			if(strlen($telefax) > 6 || strlen($telefax) > 100){
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
			$response->socios = $responseGetSocios->listResult;
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

		$socio = ctr_usuarios::getSocio($idSocio);

		if($socio){
			$mensaje =  $socio->nombre . " se le informa que su último pago registrado es el correspondiente al mes de " . $socio->fechaUltimaCuota . " se le solicita que realize el abono correspondiente.";
			$result = usuarios::enviarNotificacionCuota($mensaje, $socio->email);
			if($result){
				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Notificar socio cuota", "Se notificó al socio de nombre " . $socio->nombre . " a traves de un correo electrónico sobre su falta de pago.");
				if($resultInsertOperacionUsuario)
					$response->enHistorial = "Registrado en el historial del usuario.";
				else
					$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------
				$response->retorno = true;
				$response->mensaje = "Se envió un recordatorio al socio sobre su falta de pago.";
			}else{
				$response->retorno = false;
				$response->mensaje = "Ocurrió un error y el recordatorio de su falta de pago no fue enviado.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El socio al que quiere notificar no fue encontrado en el sistema, por favor vuelva a intentarlo.";
		}

		return $response;
	}

	public function notificarSocioVacuna($idSocio, $idMascota){
		$response = new \stdClass();

		$socio = ctr_usuarios::getSocio($idSocio);
		if($socio){
			$mascota = ctr_mascotas::getMascota($idMascota);
			if($mascota){
				if(ctr_usuarios::esMiMascota($idSocio, $idMascota)){
					$vacunasVencidas = ctr_mascotas::getVacunasVencidasMascota($idMascota);
					if($vacunasVencidas){
						$mensaje = $socio->nombre . " se le informa: <br> Las siguientes vacunas de " . $mascota->nombre . " vencieron o venceran pronto.";
						$result = usuarios::enviarNotificacionVacunas($mensaje, $socio->email, $vacunasVencidas);
						if($result){
							//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
							$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Notificar socio vacuna", "Se notificó al socio de nombre " . $nombre . " a traves de un correo electrónico sobre las vacunas vencidas de su mascota.");
							if($resultInsertOperacionUsuario)
								$response->enHistorial = "Registrado en el historial del usuario.";
							else
								$response->enHistorial = "No ingresado en historial de usuario.";
							//----------------------------------------------------------------------------------------------------------------

							$response->retorno = true;
							$response->mensaje = "Se le envió un email a " . $socio->nombre . " con la información de las vacunas vencidas de " . $mascota->nombre;
						}else{
							$response->retorno = false;
							$response->mensajeError = "El email a " . $socio->nombre . " no fue enviado por un error interno, por favor vuelva a intentarlo.";
						}
					}else{
						$response->retorno = false;
						$response->mensajeError = "La mascota de " . $socio->nombre . " no tiene vacunas vencidas.";
					}
				}else{
					$response->retorno = false;
					$response->mensajeError = "La mascota y socio proporcionados no estan vinculados en el sistema, vinculelos para realizar la notificación.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "La mascota sobre la que desea notificar no fue encontrada en el sistema.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El socio al que desea notificar no fue encontrado en el sistema, por favor vuelva a intentarlo.";
		}

		return $response;
	}

	public function activarDesactivarSocio($idSocio){
		$response = new \stdClass();

		$socio = socios::getSocio($idSocio);
		if($socio){
			$estadoMensaje = "activo";
			$estado = $socio->estado;
			if($estado == 0)$estado = 1;
			else{
				$estadoMensaje = "inactivo";
				$estado = 0;
			}
			$result = socios::actualizarEstadoSocio($idSocio, $estado);
			if($result){
				$mensajeMascotasDesactivadas = "El estado de sus mascotas no fue modificado.";
				if($estado == 0){
					$resultDesactivarMascotas = ctr_mascotas::desactivarMascotasSocio($idSocio, $estado);
					$mensajeMascotasDesactivadas = "Las mascotas de este socio fueron desactivadas correctamente.";
					if(!$resultDesactivarMascotas)
						$mensajeMascotasDesactivadas = "Las mascotas de este socio no fueron desactivadas.";
				}
				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificar estado socio", "El estado del socio " . $socio->nombre . " fue modificado y ahora se encuentra " . $estadoMensaje . ". " . $mensajeMascotasDesactivadas);
				if($resultInsertOperacionUsuario)
					$response->enHistorial = "Registrado en el historial del usuario.";
				else
					$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------

				$response->retorno = true;
				$response->mensaje = "El estado fue modificado correctamente y ahora el socio " . $socio->nombre . " se encuentra " . $estadoMensaje . ". " . $mensajeMascotasDesactivadas;
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El socio que quiere modificar no fue encontrado en el sisitema por favor vuelva a intentarlo.";
		}

		return $response;
	}

	public function actualizarEstadosSocios($plazoDeuda){
		$response = new \stdClass();

		$fechaInt = fechas::parceFechaInt(fechas::calcularFechaMinimaDeuda(date('Y-m-d'), $plazoDeuda));
		$fechaInt = substr($fechaInt, 0,4) . substr($fechaInt, 4,2);

		$resultSocio = socios::setSociosInactivosPorCuotaVencida(0, $fechaInt);
		$resultRestaurarSocio = socios::setSociosActivosPorCuotaVencida(1, $fechaInt);

		if($resultSocio && $resultRestaurarSocio){
			$resultMascota = ctr_mascotas::modificarEstadoSociosCuotas(0,0);
			$resultRestaurarMascota = ctr_mascotas::modificarEstadoSociosCuotas(1,1);
			if($resultMascota && $resultRestaurarMascota){
				$response->retorno = true;
				$response->mensaje = "Los socios fueron desactivados y restaurados segun el nuevo plazo, así tambien sus respectivas mascotas.";
			}else{
				if($resultRestaurarMascota){
					$response->retorno = true;
					$response->mensaje = "Los socios fueron desactivados y restaurados segun el nuevo plazo, fallo la desactivación de sus mascotas.";
				}else if($resultMascota){
					$response->retorno = true;
					$response->mensaje = "Los socios fueron desactivados y restaurados segun el nuevo plazo, fallo la desactivación de sus mascotas.";
				}else{
					$response->retorno = true;
					$response->mensaje = "Los socios fueron desactivados y restaurados segun el nuevo plazo, fallo la modificación de sus mascotas.";
				}
			}
		}else{
			if($resultRestaurarSocio){
				$response->retorno = false;
				$response->mensaje = "Los socios con cuotas vencidas no fueron desactivados por lo que el proceso no continuo.";
			}else if($resultSocio){
				$response->retorno = false;
				$response->mensaje = "Los socios inactivos que debian ser activados con el nuevo plazo no se modificaron, el proceso no continuo.";
			}else{
				$response->retorno = false;
				$response->mensaje = "Ocurrió un error, no se desactivaron y tampoco se restauraron los socios segun el nuevo plazo estipulado.";
			}
		}
		return $response;
	}

	public function buscadorDeSociosVencimientoCuota($nombreSocio){
		return socios::buscadorDeSociosVencimientoCuota($nombreSocio);
	}

	public function getVencimientosCuotaPagina($ultimoId){
		if($ultimoId == 0){
			$ultimoId = socios::getVencimientosCuotaMaxId();
		}

		$vencimientosCuota = socios::getVencimientosCuotaPagina($ultimoId);
		$minId = socios::getVencimientosCuotaMinId($vencimientosCuota, $ultimoId);
		return array(
			"min" => $minId,
			"max" => $ultimoId,
			"vencimientos" => $vencimientosCuota
		);
	}

	public function haySociosConCuotasVencidas(){
		$result = ctr_usuarios::getVencimientosCuotaPagina(0);
		if(sizeof($result['vencimientos']) > 0) return 1;
		else return 0;
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
