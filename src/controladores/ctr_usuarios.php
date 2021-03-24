<?php

/**
 *
 */
require_once '../src/clases/usuarios.php';
require_once '../src/clases/socios.php';
require_once '../src/clases/fechas.php';
require_once '../src/clases/configuracionSistema.php';
require_once '../src/controladores/ctr_mascotas.php';


class ctr_usuarios{
    //----------------------------------- FUNCIONES DE USUARIO ------------------------------------------
	public function insertNewUsuario($nombre, $email){
		$response = new \stdClass();
		$usuario = usuarios::getUsuarioNombre($nombre);

		if(!$usuario){
			$result = usuarios::insertUsuario($nombre, $email);
			if($result){
				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Usuario agregado", "fue agregado el usuario " . $nombre . " y email " . $email . " por el administrador del sistema.");
				if($resultInsertOperacionUsuario)
					$response->historialUsuario = "Registrado en el historial del usuario.";
				else
					$response->historialUsuario = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------
				$response->retorno = true;
				$response->mensaje = "El usuario fue ingresado correctamente, al iniciar sesión por primera vez se fijará la contraseña ingresada.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "Ocurrio un error interno y el usuario no pudo ser ingresado correctamente, porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El usuario que se esta intentando ingresar ya existe en el sistema.";
		}

		return $response;
	}

	public function signIn($nombreUsuario, $pass){

		$response = new \stdClass();
		$usuario = usuarios::getUsuarioNombre($nombreUsuario);

		if($usuario){
			if($usuario->pass == ""){
				$result = usuarios::updatePasswordUsuario($nombreUsuario, $pass);
				if($result){
					$usu = new \stdClass();
					$usu->usuario = $usuario->nombre;
					session_destroy();
					session_start();
					$_SESSION['administrador'] = $usu;
					$response->retorno = true;
					$response->mensaje = "Usted inicio sesión por primera vez, la contraseña ingresada será su contraseña de ahora en más.";
					$response->primerSesion = 1;
				}else{
					$response->retorno = false;
					$response->mensajeError = "Usted intento iniciar sesión por primera vez, el sistema no pudo asociar su contraseña a esta cuenta, vuelva a intentarlo.";
				}
			}else if($pass == $usuario->pass){
				$usu = new \stdClass();
				$usu->usuario = $usuario->nombre;
				session_destroy();
				session_start();
				$_SESSION['administrador'] = $usu;
				$response->retorno = true;
				$response->mensaje = "Sesión iniciada.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "El usuario y la contraseña no coinciden.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El usuario ingresado no existe en el sistema.";
		}

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
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificación de usuario", "La informacion del usuario " . $nombre . " fue actualizada en el sistema.");
					if($resultInsertOperacionUsuario)
						$response->historialUsuario = "Registrado en el historial del usuario.";
					else
						$response->historialUsuario = "No ingresado en historial de usuario.";
					//----------------------------------------------------------------------------------------------------------------

					$response->retorno = true;
					$response->mensaje = "El usuario " . $nombre . " fue modificado correctamente.";
				}else{
					$response->retorno = false;
					$response->mensajeError = "El usuario " . $nombre . " no pudo ser modificado, porfavor vuelva a intentarlo.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "El nombre de usuario que desea asignar ya fue vinculado a otro usuario, ingrese uno distinto.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El usuario que desea modificar no fue encontrado en el sistema, porfavor vuelva a intentarlo.";
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
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificación de contraseña", "El usuario " . $nombre . " actualizo su contraseña.");
					if($resultInsertOperacionUsuario)
						$response->historialUsuario = "Registrado en el historial del usuario.";
					else
						$response->historialUsuario = "No ingresado en historial de usuario.";
					//----------------------------------------------------------------------------------------------------------------

					$response->retorno = true;
					$response->mensaje = "La contraseña del administrador fue modificada correctamente.";
				}else{
					$response->retorno = false;
					$response->mensajeError = "Ocurrio un error interno y la contraseña del administrador no fue modificada, porfavor vuelva a intentarlo.";
				}
			}else {
				$response->retorno = false;
				$response->mensajeError = "La contraseña ingresada no corresponde al usuario administrador.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "No hay un usuario administrador inicializado, contactese a servicio tecnico.";
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
	public function insertNewSocio($cedula, $nombre, $telefono, $telefax, $direccion, $fechaPago, $lugarPago, $email, $rut){
		$response = new \stdClass();

		if(ctr_usuarios::validarCedula($cedula)){
			$usuario = socios::getSocioCedula($cedula);
			if($usuario == null){
				$fechaIngreso = fechas::parceFechaInt(date('Y-m-d'));

				if(strlen($rut) > 1){
					if(!ctr_usuarios::validarRut($rut)){
						$response->retorno = false;
						$response->mensajeError = "El rut ingresado para el socio " . $nombre . " no es valido, para continuar ingreselo correctamente o deje el campo sin valor.";
						return $response;
					}
				}else $rut = null;

				$result = socios::insertSocio($cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, 1, null, null, $email, $rut, null, null);
				if($result != false){
					//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Nuevo socio ingresado", "Se ingresó un nuevo socio en el sistema con nombre " . $nombre . ".");
					if($resultInsertOperacionUsuario)
						$response->historialUsuario = "Registrado en el historial del usuario.";
					else
						$response->historialUsuario = "No ingresado en historial de usuario.";
					//----------------------------------------------------------------------------------------------------------------

					$response->retorno = true;
					$response->mensaje = "El socio fue ingresado correctamente, asignele una mascota";
					$response->idSocio = $result;
				}else{
					$response->retorno = false;
					$response->mensajeError = "Ocurrio un error interno y el socio no fue ingresado en el sistema";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "La cédula ingresada ya esta registrada para el usuario " . $usuario->nombre . ", modifique el usuario existente para persistir la información ingresada.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "La cédula ingresada no es valida, para continuar ingrese una correcta.";
		}

		return $response;
	}

	public function updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $fechaIngreso, $email, $rut, $telefax){
		$response = new \stdClass();

		$socio = socios::getSocio($idSocio);
		if($socio != null){
			$fechaIngresoFormat = fechas::parceFechaInt($fechaIngreso);
			$cuotaActualizada = ctr_usuarios::calcularCostoCuota($idSocio);
			$mensajeCuota = ".";
			if($cuotaActualizada)
				$mensajeCuota = ", el sistema actualizo la cuota con el costo ingresado en el sistema.";
			else
				$mensajeCuota = ", pero el sistema no puedo actualizar la cuota para este socio.";

			$result = socios::updateSocio($idSocio, $cedula, $nombre, $telefono, $telefax, $direccion, $fechaIngresoFormat, $fechaPago, $lugarPago, $email, $rut);

			if($result){

				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificación de socio", "La informacion del socio " . $nombre . " fue actualizada en el sistema.");
				if($resultInsertOperacionUsuario)
					$response->historialUsuario = "Registrado en el historial del usuario.";
				else
					$response->historialUsuario = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------

				$response->retorno = true;
				$response->mensaje = "La información del socio fue actualizada" . $mensajeCuota;
			}else{
				$response->retorno = true;
				$response->mensaje = "La informacion del socio no pudo ser actualizada, porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El socio que se quiere modificar no fue encontrado en el sistema porfavor vuelva a intentarlo.";
		}

		return $response;
	}

	public function getSocios(){
		return socios::getSocios();
	}

	public function sociosNoVinculados($idMascota){
		return socios::getSociosNoVinculados($idMascota);
	}

	public function getSocio($idSocio){
		$socio = socios::getSocio($idSocio);
		if($socio ){
			$socio->fechaIngreso = fechas::parceFechaFormatDMA($socio->fechaIngreso, "/");
			if(strlen($socio->fechaUltimoPago) == 8)
				$socio->fechaUltimoPago = fechas::parceFechaFormatDMA($socio->fechaUltimoPago, "/");
			else
				$socio->fechaUltimoPago = "No especificado";
			if(strlen($socio->fechaUltimaCuota) == 6)
				$socio->fechaUltimaCuota = fechas::parceFechaMesFormatDMA($socio->fechaUltimaCuota);
			else $socio->fechaUltimaCuota = "No especificado";

			$socio->mascotas = ctr_mascotas::getMasctoasSocio($idSocio);
		}
		return $socio;
	}

	public function getSocioMascota($idMascota){
		$socio = socios::getSocioMascota($idMascota);
		if($socio){
			$socio->fechaIngreso = fechas::parceFechaFormatDMA($socio->fechaIngreso, "/");
			$socio->fechaUltimoPago = fechas::parceFechaFormatDMA($socio->fechaUltimoPago, "/");
			$socio->fechaUltimaCuota = fechas::parceFechaMesFormatDMA($socio->fechaUltimaCuota);
		}
		return $socio;
	}

	public function notificarSocio($idSocio, $idMascota){
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
							$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Notificar socio", "Se notificó al socio de nombre " . $nombre . " a traves de un correo electrónico.");
							if($resultInsertOperacionUsuario)
								$response->historialUsuario = "Registrado en el historial del usuario.";
							else
								$response->historialUsuario = "No ingresado en historial de usuario.";
							//----------------------------------------------------------------------------------------------------------------

							$response->retorno = true;
							$response->mensaje = "Se le envió un email a " . $socio->nombre . " con la información de las vacunas vencidas de " . $mascota->nombre;
						}else{
							$response->retorno = false;
							$response->mensajeError = "El email a " . $socio->nombre . " no fue enviado por un error interno, porfavor vuelva a intentarlo.";
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
			$response->mensajeError = "El socio al que desea notificar no fue encontrado en el sistema, porfavor vuelva a intentarlo.";
		}

		return $response;
	}

	public function esMiMascota($idSocio, $idMascota){
		$result = socios::esMiMascota($idSocio, $idMascota);
		if($result != null) return true;
		else return false;
	}

    //---------------------------------------------------------------------------------------------------

	//----------------------------------- FUNCIONES COMUNES --------------------------------------------
	public function calcularCostoCuota($idSocio){
		$cantMascotas = socios::getCantMascotasSocio($idSocio);
		if($cantMascotas == 0)
			return socios::actualizarCuotaSocio($idSocio, $cantMascotas);

		$costoCuota = configuracionSistema::getCostoCuota($cantMascotas);
		return socios::actualizarCuotaSocio($idSocio, $costoCuota);
	}

	public function validarRut($rut){
		return true;
	}

	public function validarCedula($ci){
		$ciLimpia = preg_replace( '/\D/', '', $ci );
		$validationDigit = $ciLimpia[-1];
		$ciLimpia = preg_replace('/[0-9]$/', '', $ciLimpia );
		return $validationDigit == copiarDB::validarDigitoVerificador($ci);
	}


	public function validarDigitoVerificador($ci){
		$ci = preg_replace( '/\D/', '', $ci );
		$ci = str_pad( $ci, 7, '0', STR_PAD_LEFT );
		$a = 0;

		$baseNumber = "2987634";
		for ( $i = 0; $i < 7; $i++ ) {
			$baseDigit = $baseNumber[ $i ];
			$ciDigit = $ci[ $i ];

			$a += ( intval($baseDigit ) * intval( $ciDigit ) ) % 10;
		}
		return $a % 10 == 0 ? 0 : 10 - $a % 10;
	}
	//---------------------------------------------------------------------------------------------------
}
