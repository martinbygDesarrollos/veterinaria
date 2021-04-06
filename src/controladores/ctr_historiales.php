<?php


require_once '../src/clases/configuracionSistema.php';
require_once '../src/clases/historiales.php';
require_once '../src/clases/copiarDB.php';
require_once '../src/clases/fechas.php';
require_once '../src/controladores/ctr_mascotas.php';
require_once '../src/controladores/ctr_usuarios.php';

class ctr_historiales {

	public function levantarDB(){

		$sociosInsertados = copiarDB::seleccionarInsertarSocios();

		$arrayMascotasInsertadas = array();
		foreach ($sociosInsertados as $keySocio => $socio) {
			$arrayMascotasInsertadas = copiarDB::seleccionarInsertarMascota($socio['idSocio'], $socio['numSocio']);

			foreach ($arrayMascotasInsertadas as $keyMascota => $mascota) {
				copiarDB::seleccionarInsertarVacunasMascotas($mascota['idMascota'], $mascota['nombreMascota'], $mascota['numSocio'] );
				copiarDB::seleccionarInsertarEnfermedadesMascota($mascota['idMascota'], $mascota['nombreMascota'], $mascota['numSocio']);
				copiarDB::seleecionarInsertarHistorialClinicoMascota($mascota['idMascota'], $mascota['nombreMascota'], $mascota['numSocio']);
				copiarDB::seleccionarInsertarHistorialMascota($mascota['idMascota'], $mascota['nombreMascota'], $mascota['numSocio'], $mascota['idSocio']);
			}
		}
	}


	//----------------------------------- FUNCIONES DE HISTORIAL CLINICO ------------------------------------------

	public function insertHistoriaMascota($idMascota, $motivoConsulta, $diagnostico, $observaciones){
		$response = new \stdClass();

		$mascota = ctr_mascotas::getMascota($idMascota);

		if($mascota != null){
			$fechaHistoria = fechas::parceFechaInt(date('Y-m-d'));
			$result = historiales::insertHistoriaClinica($idMascota, $fechaHistoria, $motivoConsulta, $diagnostico, $observaciones);

			if($result){
				$response->retorno = true;
				$response->mensaje = "Se realizo un nuevo registro en el historial clinico de " . $mascota->nombre . " puede acceder a él desde Ver historia clinica.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "No se realizo el registro en la historia clinica de " . $mascota->nombre . " por un error interno, porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "La mascota seleccionada no fue encontrada en el sistema, porfavor vuelva a intentarlo.";
		}

		return $response;
	}

	public function getHistoriaCompleta($idHistoria){
		$response = new \stdClass();
		$response->historia = historiales::getOneHistoriaClinica($idHistoria);
		return $response;
	}

	public function getHistoriasClinica($idMascota){
		return historiales::getOneHistoriaClinicaMascota($idMascota);
	}

	public function checkHayHistorial($idMascota){
		return historiales::checkHayHistorialClinico($idMascota);
	}

	public function getHistoriaClinicaPagina($ultimoID, $idMascota){
		if($ultimoID == 0){
			$maxId = historiales::getHistoriaClinicaMaxId($idMascota);
			$historial = historiales::getHistoriaClinicaPagina($maxId, $idMascota);
			$minId = historiales::getHistoriaClinicaMinId($historial, $maxId);
			return array(
				"min" => $minId,
				"max" => $maxId,
				"historial" => $historial
			);
		}else{
			$historial = historiales::getHistoriaClinicaPagina($ultimoID, $idMascota);
			$minId = historiales::getHistoriaClinicaMinId($historial, $ultimoID);
			return array(
				"min" => $minId,
				"max" => $ultimoID,
				"historial" => $historial
			);
		}
	}


    //-------------------------------------------------------------------------------------------------------------
    //----------------------------------- FUNCIONES DE HISTORIAL SOCIO --------------------------------------------

    //-------------------------------------------------------------------------------------------------------------

  	//-------------------------------------------------------------------------------------------------------------
    //------------------------------------------ACTUALIZAR CUOTA---------------------------------------------------

	public function updateCuotaSocio($cuotaUna, $cuotaDos, $cuotaExtra){
		$response = new \stdClass();

		$result = configuracionSistema::setNuevaCuota($cuotaUna, $cuotaDos, $cuotaExtra);

		if($result){
			$response->retorno = true;
			$response->mensaje = "Los nuevos montos de las cuotas fueron ingresados correctamente.";
		}else{
			$response->retorno = false;
			$response->mensaje = "Ocurrio un error y la cuota no pudo ser actualizada, porfavor vuelva a intentarlo.";
		}

		return $response;
	}

	public function getMontoCuotas(){
		return configuracionSistema::getCuota();
	}

	public function updatePlazoDeuda($plazoDeuda){
		$response = new \stdClass();

		$result = configuracionSistema::updatePlazoDeuda($plazoDeuda);
		if($result){
			$resultActualizacionPlazo = ctr_usuarios::actualizarEstadosSocios($plazoDeuda);
			//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
			$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificar plazo deuda", "Se modifico el plazo de deuda para los socios (". $plazoDeuda ." dias).");
			if($resultInsertOperacionUsuario)
				$response->enHistorial = "Registrado en el historial del usuario.";
			else
				$response->enHistorial = "No ingresado en historial de usuario.";
			//----------------------------------------------------------------------------------------------------------------
			$response->retorno = true;
			$response->mensaje = "EL plazo de vencimiento de deuda fue modificado correctamente. " . $resultActualizacionPlazo->mensaje;
		}else{
			$response->retorno = false;
			$response->mensajeError = "Ocurrio un error y la cuota no pudo modificarse correctamente, porfavor vuelva a intentarlo.";
		}

		return $response;
	}
	//-------------------------------------------------------------------------------------------------------------
    //----------------------------------- FUNCIONES DE HISTORIAL USUARIO ------------------------------------------

	public function insertHistorialUsuario($operacion, $observaciones){
		$response = new \stdClass();
		$objectFecha = new DateTime();
		$objectFecha->setTimezone(new DateTimeZone('America/Montevideo'));
		$fecha = $objectFecha->format('Y-m-d H:i:s');
		$fecha = fechas::StringToIntFechaHoraGuion($fecha);

		if(isset( $_SESSION['administrador'])){
			$usuario = $_SESSION['administrador'];
			$usuario = ctr_usuarios::getUsuarioNombre($usuario->usuario);
			if($usuario){
				$result = historiales::insertHistorialUsuario($usuario->idUsuario, $operacion, $fecha, $observaciones);
				if($result){
					$response->retorno = true;
					$response->mensaje = "Se generó un registro en el historial de usuario.";
					return $response;
				}
			}
		}
		$response->retorno = false;
		$response->mensajeError = "Algo impidió que se genere el registro en el historial.";
		return $response;
	}

	public function getHistorialUsuario($nombre){
		$usuario = ctr_usuarios::getUsuarioNombre($nombre);
		return historiales::getHistorialUsuario($usuario->idUsuario);
	}

	public function getHistorialUsuarios(){
		return historiales::getHistorialUsuarios();
	}

	public function getHistorialUsuariosPagina($ultimoID){
		if($ultimoID == 0){
			$maxId = historiales::getHistorialUsuariosMaxId();
			$historial = historiales::getHistorialUsuariosPagina($maxId);
			$minId = historiales::getHistorialUsuariosMinId($historial, $maxId);
			return array(
				"min" => $minId,
				"max" => $maxId,
				"historial" => $historial
			);
		}else{
			$historial = historiales::getHistorialUsuariosPagina($ultimoID);
			$minId = historiales::getHistorialUsuariosMinId($historial, $ultimoID);
			return array(
				"min" => $minId,
				"max" => $ultimoID,
				"historial" => $historial
			);
		}
	}

    //-------------------------------------------------------------------------------------------------------------


}

?>