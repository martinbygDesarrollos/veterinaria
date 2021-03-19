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

	public function signIn($usuario, $pass){

		$response = new \stdClass();
		$usuario = usuarios::getUsuarioNombre($usuario);

		if($usuario){
			if($pass == $usuario->pass){
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

	public function checkPermissions($grupoUsuario, $idFuncion){
		return usuarios::getGrupoFuncion($grupoUsuario, $idFuncion);
	}

	public function updatePassAdministrador($passActual, $pass1, $pass2){

		$response = new \stdClass();
		$usuario = usuarios::getUsuarioNombre("admin");
		if($usuario != null){

			if($usuario->pass == $passActual){
				$result = usuarios::updateUsuario($pass1);

				if($result){
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

	public function insertNewRegistroHistorialUsuario($usuario, $funcion, $fecha){
		$fechaFormat = fechas::parceFechaInt($fecha);
		usuarios::insertHistorialUsuario($usuario, $funcion, $fecha);
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
