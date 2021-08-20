<?php


require_once '../src/clases/configuracionSistema.php';
require_once '../src/clases/historiales.php';
require_once '../src/clases/copiarDB.php';
require_once "../src/utils/fechas.php";
require_once '../src/controladores/ctr_mascotas.php';
require_once '../src/controladores/ctr_usuarios.php';

class ctr_historiales {

	public function insertarSociosOriginales(){
		copiarDB::seleccionarInsertarSocios();
	}

	public function insertarMascotasOriginales(){
		$sociosSeleccionados = socios::getTotSocios();
		foreach ($sociosSeleccionados as $key => $socio) {
			copiarDB::seleccionarInsertarMascota($socio['idSocio'], $socio['numSocio']);
		}
	}

	public function insertarMascotasSinSociosOriginales(){
		copiarDB::seleccionarInsertarMascotaSinSocio();
	}

	public function insertarVacunasOriginales(){
		$mascotasSocio = mascotas::getMascotaIds();
		foreach ($mascotasSocio as $key => $mascotaSocio) {
			copiarDB::seleccionarInsertarVacunasMascotas($mascotaSocio['idMascota'], $mascotaSocio['nombre'], $mascotaSocio['numSocio']);
		}
	}

	public function insertarHistorialClinicoOriginales(){
		$mascotasSocio = mascotas::getMascotaIds();
		foreach ($mascotasSocio as $key => $mascotaSocio) {
			copiarDB::seleccionarInsertarHistorialClinico($mascotaSocio['idMascota'], $mascotaSocio['nombre'], $mascotaSocio['numSocio']);
		}
	}

	public function insertarEnfermedadesOriginales(){
		$mascotasSocio = mascotas::getMascotaIds();
		foreach ($mascotasSocio as $key => $mascotaSocio) {
			copiarDB::seleccionarInsertarEnfermedadesMascota($mascotaSocio['idMascota'], $mascotaSocio['nombre'], $mascotaSocio['numSocio']);
		}
	}

	public function insertarFechaDeCambioOriginales(){
		$mascotasSocio = mascotas::getMascotaIds();
		foreach ($mascotasSocio as $key => $mascotaSocio) {
			copiarDB::seleccionarInsertarFechaDeCambio($mascotaSocio['nombre'], $mascotaSocio['numSocio'], $mascotaSocio['idMascotaSocio']);
		}
	}

	//----------------------------------- FUNCIONES DE HISTORIAL CLINICO ------------------------------------------

	public function agregarHistoriaClinica($idMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$response = new \stdClass();

		$responseGetMascota = ctr_mascotas::getMascota($idMascota);
		if($responseGetMascota->result == 2){
			$fecha = fechas::getDateToINT($fecha);
			$responseInsertHistoriaClinica = historiales::agregarHistoriaClinica($idMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones);
			if($responseInsertHistoriaClinica->result == 2){
				$response->result = 2;
				$response->message = "La historia clínica se agregó correctamente.";
				$responseGetHistoriaClinica = historiales::getHistoriaClinicaToShow($responseInsertHistoriaClinica->id);
				if($responseGetHistoriaClinica->result == 2)
					$response->newHistoria = $responseGetHistoriaClinica->objectResult;
			}else return $responseInsertHistoriaClinica;
		}else return $responseGetMascota;

		return $response;
	}

	public function modificarHistoriaClinica($idHistoriaClinica, $fecha, $motivoConsulta, $diagnostico, $observaciones){
		$response = new \stdClass();

		$responseGetHistoriaClinica = historiales::getHistoriaClinica($idHistoriaClinica);
		if($responseGetHistoriaClinica->result == 2){
			$fecha = fechas::getDateToINT($fecha);
			$responseUpdateHistoriaClinica = historiales::modificarHistoriaClinica($idHistoriaClinica, $fecha, $motivoConsulta, $diagnostico, $observaciones);
			if($responseUpdateHistoriaClinica->result == 2){
				$response->result = 2;
				$response->message = "La historia clínica se agregó correctamente.";
				$responseGetHistoriaClinica = historiales::getHistoriaClinicaToShow($idHistoriaClinica);
				if($responseGetHistoriaClinica->result == 2)
					$response->updatedHistoria = $responseGetHistoriaClinica->objectResult;
			}else return $responseUpdateHistoriaClinica;
		}else return $responseGetHistoriaClinica;

		return $response;
	}

	public function borrarHistoriaClinica($idHistoriaClinica){
		$response = new \stdClass();
		$responseDeleteHistoriaClinica = historiales::borrarHistoriaClinica($idHistoriaClinica);
		if($responseDeleteHistoriaClinica->result == 2){
			$response->result = 2;
			$response->message = "La historia clínica fue borrada correctamente.";
		}else{
			$response->result = 0;
			$response->message = "Ocurrió un erorr y la historia clínica no fue borrada del sistema.";
		}

		return $response;
	}

	public function getHistoriaClinicaToShow($idHistoriaClinica){
		return historiales::getHistoriaClinicaToShow($idHistoriaClinica);
	}

	public function getHistoriaClinicaToEdit($idHistoriaClinica){
		return historiales::getHistoriaClinicaToEdit($idHistoriaClinica);
	}

	public function getHistoriaClinicaMascota($lastId, $idMascota){
		return historiales::getHistoriaClinicaMascota($lastId, $idMascota);
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
			$response->mensaje = "Ocurrió un error y la cuota no pudo ser actualizada, por favor vuelva a intentarlo.";
		}

		return $response;
	}

	public function getMontoCuotas(){
		return configuracionSistema::getQuota();
	}

	public function updatePlazoDeuda($plazoDeuda){
		$response = new \stdClass();

		$result = configuracionSistema::updatePlazoDeuda($plazoDeuda);
		if($result){
			$resultActualizacionPlazo = ctr_usuarios::actualizarEstadosSocios($plazoDeuda);
			//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
			$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificar plazo deuda", "Se modificó el plazo de deuda para los socios (". $plazoDeuda ." días). " . $resultActualizacionPlazo->mensaje);
			if($resultInsertOperacionUsuario)
				$response->enHistorial = "Registrado en el historial del usuario.";
			else
				$response->enHistorial = "No ingresado en historial de usuario.";
			//----------------------------------------------------------------------------------------------------------------
			$response->retorno = true;
			$response->mensaje = "EL plazo de vencimiento de deuda fue modificado correctamente. " . $resultActualizacionPlazo->mensaje;
		}else{
			$response->retorno = false;
			$response->mensajeError = "Ocurrió un error y la cuota no pudo modificarse correctamente, por favor vuelva a intentarlo.";
		}

		return $response;
	}
	//-------------------------------------------------------------------------------------------------------------
    //----------------------------------- FUNCIONES DE HISTORIAL USUARIO ------------------------------------------

	public function insertHistorialUsuario($operacion, $idSocio, $idMascota, $observaciones){
		$response = new \stdClass();

		$responseGetUserInSesion = ctr_usuarios::getUserInSession();
		if($responseGetUserInSesion->result == 2){
			if(!is_null($idMascota) && is_null($idSocio)){
				$responseGetSocio = ctr_usuarios::getSocioMascota($idMascota);
				if($responseGetSocio->result == 2)
					$idSocio = $responseGetSocio->socio->idSocio;
			}

			$responseInsertHistorial = historiales::insertHistorialUsuario($responseGetUserInSesion->user->idUsuario, $operacion, $idSocio, $idMascota, $observaciones);
			if($responseInsertHistorial->result == 2){
				$response->result = 2;
				$response->message = "Se registró la operación realizada en el historial";
			}else return $responseInsertHistorial;
		}else return $responseGetUserInSesion;

		return $response;
	}

	public function getHistorialUsuario($lastId, $idUsuario){
		$responseListHistorial = historiales::getHistorialUsuario($lastId, $idUsuario);

		if($responseListHistorial->result == 2){
			$arrayResult = array();
			foreach ($responseListHistorial->listResult as $key => $value) {
				if(!is_null($value['idSocio'])){
					$responseGetSocio = ctr_usuarios::getSocio($value['idSocio']);
					if($responseGetSocio->result == 2)
						$value['socio'] = $responseGetSocio->socio->nombre;
				}else $value['socio'] = "No corresponde";

				if(!is_null($value['idMascota'])){
					$responseGetMascota = ctr_mascotas::getMascota($value['idMascota']);
					if( $responseGetMascota->result == 2){
						$value['mascota'] = $responseGetMascota->objectResult->nombre;
					}
				}else $value['mascota'] = "No corresponde";

				$arrayResult[] = $value;
			}
			$responseListHistorial->listResult = $arrayResult;
		}

		return $responseListHistorial;
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