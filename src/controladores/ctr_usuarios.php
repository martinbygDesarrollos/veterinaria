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
				if($usuario->grupo){
					$usu = new \stdClass();
					$usu->usuario = $usuario->nombre;
					$usu->permisos = "nivel de permisos";
					return $usu;
				}else{
					$response->retorno = false;
					$response->mensajeError = "El usuario ingresado no fue asignado a un grupo de funciones, por lo que no puede iniciar sesión con él.";
				}
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

	public function insertNewUsuario($nombre, $pass){

		$response = new \stdClass();
		if(usuarios::getUsuarioNombre($nombre) == null){
			$result = usuarios::insertUsuario($nombre, $pass, 1);
			if($result){
				$response->retorno = true;
				$response->mensaje = "El usuario fue ingresado correctamente, asignelo a un grupo de funciones para comenzar a utilizarlo.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "Ocurrio un error interno y el usuario que desea ingresar no fue dado de alta, intentelo nuevamente.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "Ya existe un usuario con este nombre, modifique el nombre ingresado para dar de alta un nuevo usuario";
		}
		return $response;
	}

	public function vincularUsuarioGrupo($idUsuario, $nombreGrupo){

		$response = new \stdClass();
		$grupo = usuarios::getGrupoNombre($nombreGrupo);
		if($grupo){
			$usuario = usaurios::getUsuario($idUsuario);
			if($usuario){
				$observacion = ".";
				if($usuario->grupo){
					$observacion = ", el mismo ya pertenecia a un grupo, pero fue reasignado al grupo elegido.";
				}
				$result = usuarios::setGrupoUsuario($idUsuario, $grupo->idGrupo);
				if($result){
					$response->retorno = true;
					$response->mensaje = "El usuario fue asignado al grupo" . $observacion;
				}else{
					$response->retorno = false;
					$response->mensajeError = "El usuario no pudo ser vinculado al grupo por error, interno intentelo otra vez.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "El usuario que selecciono no se encuentra en el sistema, porfavor verifique su seleccion.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El grupo que selecciono para el usuario no fue encontrado en el sistema.";
		}

		return $response;
	}

	public function createNewGroup($nombre, $idsFunciones){

		$response = new \stdClass();
		$existeGrupo = usuarios::getGrupoNombre($nombre);
		if(!$existeGrupo){
			$grupoId = usuarios::insertGrupo($nombre);
			if($grupoId != null){
				$funciones = explode(",", $idsFunciones);
				$todosIngresados = true;
				for ($i=0; $i < count($funciones); $i++) {

					$todosIngresados = usuarios::asignarFuncionGrupo($grupoId, $funciones[$i]);
					if(!$todosIngresados)
						break;
				}

				if($todosIngresados){
					$response->retorno = true;
					$response->mensajeError = "El grupo fue creado correctamente y todas sus funciones asignadas.";
				}else{
					$response->retorno = false;
					$response->mensajeError = "Algunas funciones no fueron asignadas al grupo verifiquelo e intente nuevamente.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "No pudo ser creado el grupo bajo el nombre " . $nombre .  ", porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "Ya existe un grupo creado bajo el nombre " . $nombre . " este dato debe ser de valor unico en el sistema.";
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

	public function getSocios(){
		return socios::getSocios();
	}

	public function getSocio($idSocio){
		$socio = socios::getSocio($idSocio);
		$socio->mascotas = ctr_mascotas::getMasctoasSocio($idSocio);
		return $socio;
	}
    //---------------------------------------------------------------------------------------------------

	//----------------------------------- FUNCIONES COMUNES --------------------------------------------
	public function calcularCostoCuota($idSocio){
		$cantMascotas = socios::getCantMascotasSocio($idSocio);
		if($cantMascotas == 0)
			return 0;

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
