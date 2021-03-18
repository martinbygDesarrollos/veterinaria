<?php


require_once '../src/clases/configuracionSistema.php';
require_once '../src/clases/historiales.php';
require_once '../src/clases/copiarDB.php';
require_once '../src/clases/fechas.php';
require_once '../src/controladores/ctr_mascotas.php';

class ctr_historiales {

	public function levantarDB(){
 				//($cedula, $nombre, $numSocio, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUltimoPago, $fechaUltimaCouta)

		return copiarDB::getSociosOriginal();
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

	//-------------------------------------------------------------------------------------------------------------
    //----------------------------------- FUNCIONES DE HISTORIAL USUARIO ------------------------------------------




    //-------------------------------------------------------------------------------------------------------------


}

?>