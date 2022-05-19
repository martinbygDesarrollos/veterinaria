<?php

require_once '../src/controladores/ctr_usuarios.php';
require_once '../src/controladores/ctr_historiales.php';
require_once '../src/clases/mascotas.php';
require_once "../src/utils/fechas.php";
require_once '../src/clases/serviciosMascota.php';

class ctr_mascotas {

	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
    //---------------------------------------------------FUNCIONES DE MASCOTA --------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------

	public function changeStateMascotas($idSocio){
		return mascotas::changeStateMascotas($idSocio);
	}

	public function getCantMascotas($idSocio){
		return mascotas::getCantMascotas($idSocio);
	}

	public function deleteVinculoMascota($idMascota){
		return mascotas::deleteVinculoMascota($idMascota);
	}

	public function getMascotaVinculadaToShow($idMascota){
		return mascotas::getMascotaToShow($idMascota);
	}

	public function mascotaIsVinculada($idMascota){
		return mascotas::mascotaIsVinculada($idMascota);
	}

	public function vincularMascotaSocio($idSocio, $idMascota){
		$currentDate = fechas::getCurrentDateInt();
		return mascotas::vincularMascotaSocio($idSocio, $idMascota, $currentDate);
	}

	public function getMascotaToEdit($idMascota){
		return mascotas::getMascotaToEdit($idMascota);
	}

	public function  insertNewMascota($idSocio, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones){
		$response = new \stdClass();

		$responseGetSocio = ctr_usuarios::getSocio($idSocio);
		if($responseGetSocio->result == 2){
			if(!is_null($fechaNacimiento))
				$fechaNacimiento = fechas::getDateToINT($fechaNacimiento);

			$responseInsertMascota = mascotas::insertMascota($nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, 1, $pelo, $chip, $observaciones);
			if($responseInsertMascota->result == 2){
				$responseAsociarMascota = mascotas::vincularMascotaSocio($idSocio, $responseInsertMascota->id, fechas::getCurrentDateInt());
				if($responseAsociarMascota->result == 2){
					$responseGetQuota = ctr_usuarios::calculateQuotaSocio($idSocio);
					if($responseGetQuota->result == 2){
						$responseUpdateQuota = ctr_usuarios::updateQuotaSocio($idSocio, $responseGetQuota->quota);
						if($responseUpdateQuota->result == 2){
							$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Nueva mascota", $idSocio, $responseInsertMascota->id, "Se le agregó una nueva mascota al socio, se vinculó y actualizó su cuota.");
							if($responseInsertHistorial->result == 2){
								$response->result = 2;
								$response->message = "Se agregó la nueva mascota de " . $responseGetSocio->socio->nombre . ", y se modifico su cuota correctamente.";
							}else{
								$response->result = 1;
								$response->message = "Se agregó la nueva mascota de " . $responseGetSocio->socio->nombre . ", y se modifico la cuota correctamente.";
							}
						}else return $responseUpdateQuota;
					}else return $responseGetQuota;
				}else return $responseAsociarMascota;
			}else return $responseInsertMascota;
		}else return $responseGetSocio;

		return $response;
	}

	public function modificarMascota($idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento,$muerte, $pelo, $chip, $observaciones){
		$response = new \stdClass();

		$responseGetMascota = mascotas::getMascota($idMascota);
		if($responseGetMascota->result == 2){
			if(!is_null($fechaNacimiento))
				$fechaNacimiento = fechas::getDateToINT($fechaNacimiento);
			if(!is_null($muerte) && $muerte != "")
				$muerte = fechas::getDateToINT($muerte);
			else $muerte = null;
			$responseUpdateMascota = mascotas::updateMascota($idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $muerte, $pelo, $chip, $observaciones);
			if($responseUpdateMascota->result == 2){
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Modificar mascota", null, $idMascota, "Se actualizó la información de la mascota.");
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "La mascota fue modificada correctamente.";
				}else{
					$response->result = 2;
					$response->message = "La mascota fue modificada correctamente.";
				}

				$responseGetUpdatedMascota = mascotas::getMascotaToShow($idMascota);
				if($responseGetUpdatedMascota->result == 2)
					$response->updatedMascota = $responseGetUpdatedMascota->objectResult;
			}else return $responseUpdateMascota;
		}else return $responseGetMascota;

		return $response;
	}

	public function modificarEstadoSociosCuotas($estadoNuevo, $estadoActual){
		return mascotas::modificarEstadoSociosCuotas($estadoNuevo, $estadoActual);
	}

	public function updateStateMascotas($state){
		return mascotas::updateStateMascotas($state);
	}

	public function activarDesactivarMascota($idMascota){
		$response = new \stdClass();

		$responseGetMascota = mascotas::getMascota($idMascota);
		if($responseGetMascota->result == 2){
			$idSocio = null;
			$responseGetSocio = ctr_usuarios::getSocioMascota($idMascota);
			if($responseGetSocio->result == 2){
				$idSocio = $responseGetSocio->socio->idSocio;
				if($responseGetSocio->socio->estado == 0){
					$response->result = 0;
					$response->message = "La mascota no se puede activar porque el socio al que esta vinculada se encuentra inactivo.";
					return $response;
				}
			}

			$newState = 0;
			$newStateString = "desactivada";
			if($responseGetMascota->objectResult->estado == 0){
				$newState = 1;
				$newStateString = "activada";
			}

			$responseChangeStateMascota = mascotas::activarDesactivarMascota($idMascota, $newState);
			if($responseChangeStateMascota->result == 2){
				$resultUpdateQuota = ".";
				if(!is_null($idSocio)){
					$responseGetQuota = ctr_usuarios::calculateQuotaSocio($responseGetSocio->socio->idSocio);
					if($responseGetQuota->result == 2){
						$responseUpdateQuota = ctr_usuarios::updateQuotaSocio($responseGetSocio->socio->idSocio, $responseGetQuota->quota);
						if($responseUpdateQuota->result == 2){
							$resultUpdateQuota = " y la cuota del socio se modificó a $ " . number_format($responseGetQuota->quota, 2, ",", ".") . ".";
						}else return $responseUpdateQuota;
					}else return $responseGetQuota;
				}
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Mascota " . $newStateString, $idSocio, $idMascota, "Se actualizó el estado de la mascota" . $resultUpdateQuota);

				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "La mascota fue " . $newStateString . $resultUpdateQuota;
				}else{
					$response->result = 1;
					$response->message = "La mascota fue " . $newStateString . $resultUpdateQuota;
				}

			}else return $responseChangeStateMascota;
		}else return $responseGetMascota;

		return $response;
	}

	public function vincularSocioMascota($idSocio, $idMascota){
		$response = new \stdClass();

		$socio = socios::getSocio($idSocio);

		if($socio != null){
			$mascota = ctr_mascotas::getMascota($idMascota);
			if($mascota != null){
				$fechaActual = fechas::parceFechaInt(date('Y-m-d'));
				$result = mascotas::vincularMascotaSocio($idSocio, $idMascota, $fechaActual);
				if($result){

					//-----------------------------CALCULAR CUOTA SOCIO---------------------------------------------
					$cuotaAsignada = ", la cuota no pudo ser actualizada verifiquela.";
					if(ctr_usuarios::calcularCostoCuota($idSocio))
						$cuotaAsignada = ", la cuota de este socio se modifico respecto a su cantidad de mascotas actual.";
					//----------------------------------------------------------------------------------------------

					//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Vincular socio a mascota", "La mascota de nombre " . $socio->nombre . " se vinculó al socio " . $socio->nombre . $cuotaAsignada);
					if($resultInsertOperacionUsuario)
						$response->enHistorial = "Registrado en el historial del usuario.";
					else
						$response->enHistorial = "No ingresado en historial de usuario.";
					//----------------------------------------------------------------------------------------------------------------
					$response->retorno = true;
					$response->mensaje = "La mascota fue vinculada al socio correctamente" . $cuotaAsignada;
				}else{
					$response->retorno = false;
					$response->mensajeError = "La mascota no pudo ser vinculada al socio por favor vuelva a intentarlo.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "La mascota seleccionada no fue encontrada en el sistema, por favor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El socio que selecciono no fue encontrado en el sistema, por favor vuelva a intentarlo.";
		}

		return $response;
	}

	public function getSocioActivePets($idSocio){
		$response = new \stdClass();

		$responseGetPets = mascotas::getSocioActivePets($idSocio);
		if($responseGetPets->result == 2){
			$response->result = 2;
			$response->mascotas = $responseGetPets->listResult;
		}else return $responseGetPets;

		return $response;
	}

	public function getMascotaSocio($idMascota){
		return mascotas::getMascotaSocio($idMascota);
	}

	public function getMascotasSocio($idSocio){
		$response = new \stdClass();

		$responseGetMascotas = mascotas::getMascotasSocio($idSocio);
		if($responseGetMascotas->result == 2){
			$response->result = 2;
			$response->listMascotas = $responseGetMascotas->listResult;
		}else return $responseGetMascotas;

		return $response;
	}

	public function getMascota($idMascota){
		return mascotas::getMascota($idMascota);
	}

	public function getMascotaWithSocio($idMascota){
		$response = new \stdClass();

		$responseGetMascota = mascotas::getMascotaToShow($idMascota);
		if($responseGetMascota->result == 2)
			$response->mascota = $responseGetMascota->objectResult;

		$responseGetIDSocio = mascotas::getSocioIdByMascota($idMascota);
		if($responseGetIDSocio->result == 2){
			$responseGetSocio = ctr_usuarios::getSocioToShow($responseGetIDSocio->objectResult->idSocio);
			if($responseGetSocio->result == 2)
				$response->socio = $responseGetSocio->objectResult;
		}

		return $response;
	}

	public function getMascotas($lastId, $textToSearch, $stateMascota){
		$response = new \stdClass();

		$responseGetMascotas = mascotas::getMascotas($lastId, $textToSearch, $stateMascota);
		if($responseGetMascotas->result == 2){
			$response->result = 2;
			$response->lastId = $responseGetMascotas->lastId;
			$response->listMascotas = $responseGetMascotas->listResult;
		}else return $responseGetMascotas;

		return $response;
	}

	public function getMascotasNoSocio($textToSearch){
		return mascotas::getMascotasNoSocio($textToSearch);
	}

	public function buscadorMascotaNombre($nombreMascota, $estadoMascota){
		return mascotas::buscadorMascotaNombre($nombreMascota, $estadoMascota);
	}

	public function  desactivarMascotasSocio($idSocio, $estado){

		$arrayMascotas =  mascotas::desactivarMascotasSocio($idSocio);
		if($arrayMascotas){
			foreach ($arrayMascotas as $key => $value) {
				mascotas::activarDesactivarMascota($value['idMascota'], $estado);
			}
			return true;
		}
		return false;
	}

	public function getMascotaId($nombreMascota, $numSocio){
		return mascotas::getMascotaId($nombreMascota, $numSocio);
	}

	public function getMascotasIds(){
		return mascotas::getMascotasIds();
	}

    //--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------FUNCIONES VACUNAS------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------

	public function getFechasVacunasVencimiento(){
		$currentDate = fechas::getCurrentDateInt();
		return serviciosMascota::getFechasVacunasVencimiento($currentDate);
	}

	public function getVacunasVencidas($dateVencimiento){
		$responseGetVencimientos = serviciosMascota::getVacunasVencidas($dateVencimiento);
		if($responseGetVencimientos->result == 2){
			$arrayResult = array();
			foreach ($responseGetVencimientos->listResult as $key => $value) {
				$responseGetSocioMascota = ctr_usuarios::getSocioMascota($value['idMascota']);
				if($responseGetSocioMascota->result == 2){
					$value['nombreSocio'] = $responseGetSocioMascota->socio->nombre;
					$value['idSocio'] = $responseGetSocioMascota->socio->idSocio;
					$value['telefono'] = $responseGetSocioMascota->socio->telefono;
					if (!filter_var($responseGetSocioMascota->socio->email, FILTER_VALIDATE_EMAIL))
						$value['email'] = "No especificado";
					else if(strlen($responseGetSocioMascota->socio->email) > 7)
						$value['email'] = $responseGetSocioMascota->socio->email;
				}else{
					$value['nombreSocio'] = "Sin socio";
					$value['telefono'] = "No corresponde";
					$value['email'] = "No corresponde";
				}
				$arrayResult[] = $value;
			}
			$responseGetVencimientos->listResult = $arrayResult;
		}

		return $responseGetVencimientos;
	}

	public function aplicarNuevaVacunaMascota($idMascota, $nombreVacuna, $intervalo, $fechaDosis, $observaciones){
		$response = new \stdClass();

		$responseGetMascota = mascotas::getMascota($idMascota);
		if($responseGetMascota->result == 2){
			$fechaDosis = fechas::getDateToINT($fechaDosis);
			$fechaProximaDosis = null;
			if($intervalo !=  1)
				$fechaProximaDosis = fechas::getDateToINT(fechas::calcularFechaProximaDosis($fechaDosis, $intervalo));
			$responseInsertVacuna = serviciosMascota::insertVacunaMascota($nombreVacuna, $idMascota, $intervalo, 1, $fechaDosis, $fechaDosis, $fechaProximaDosis, $observaciones);
			if($responseInsertVacuna->result == 2){
				//$responseInsertHistoriaClinica = ctr_historiales::
				$responseInsertHistorial = ctr_historiales::agregarHistoriaClinica($idMascota, null, "Se aplicó la primer dosis de la vacuna " . $nombreVacuna, null, null);
				$response->result = 2;
				if($responseInsertHistorial->result == 2)
					$response->message = "La vacuna fue insertada correctamente.";
				else
					$response->message = "La vacuna fue insertada correctamente";
				$responseGetVacuna = serviciosMascota::getVacunaMascotaToShow($responseInsertVacuna->id);
				if($responseGetVacuna->result == 2)
					$response->newVacuna = $responseGetVacuna->objectResult;
			}else return $responseInsertVacuna;
		}else return $responseGetMascota;

		return $response;
	}

	public function updateVacunaMascota($idVacunaMascota, $nombre, $intervalo, $fechaUltimaDosis, $observaciones){
		$response = new \stdClass();

		$responseGetVacunaMascota = serviciosMascota::getVacunaMascota($idVacunaMascota);
		if($responseGetVacunaMascota->result == 2){
			$fechaProximaDosis = fechas::getDateToINT(fechas::calcularFechaProximaDosis($fechaUltimaDosis, $intervalo));
			$fechaUltimaDosis = fechas::getDateToINT($fechaUltimaDosis);
			$responseUpdateVacuna = serviciosMascota::updateVacunaMascota($idVacunaMascota, $nombre, $intervalo, $fechaUltimaDosis, $fechaProximaDosis, $observaciones);
			if($responseUpdateVacuna->result == 2){
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Modificar vacuna", null, $responseGetVacunaMascota->objectResult->idMascota, "Se actualizó la información de la vacuna " . $nombre . ".");
				$response->result = 2;
				if($responseInsertHistorial->result == 2)
					$response->message = "La vacuna fue modificada correctamente.";
				else
					$response->message = "La vacuna fue modificada correctamente.";

				$responseGetVacuna = serviciosMascota::getVacunaMascotaToShow($idVacunaMascota);
				if($responseGetVacuna->result == 2)
					$response->updatedVacuna = $responseGetVacuna->objectResult;
			}else return $responseUpdateVacuna;
		}else return $responseGetVacunaMascota;

		return $response;
	}

	public function borrarVacunaMascota($idVacunaMascota){
		$response = new \stdClass();

		$responseGetVacunaMascota = serviciosMascota::getVacunaMascota($idVacunaMascota);
		if($responseGetVacunaMascota->result == 2){
			$responseDelete = serviciosMascota::borrarVacunaMascota($idVacunaMascota);
			if($responseDelete->result == 2){
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Borrar vacuna", null, $responseGetVacunaMascota->objectResult->idMascota, "Se borro la vacuna de nombre " . $responseGetVacunaMascota->objectResult->nombreVacuna . " la cual llevaba " . $responseGetVacunaMascota->objectResult->numDosis . "Dosis");
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "La vacuna fue borrada correctamente.";
				}else{
					$response->result = 1;
					$response->message = "La vacuna fue borrada correctamente.";
				}
			}else{
				$response->result = 0;
				$response->message = "Ocurrió un error y la vacuna no fue borrada.";
			}
		}else return $responseGetVacunaMascota;

		return $response;
	}

	public function aplicarDosisVacuna($idVacunaMascota, $dateDosis){
		$response = new \stdClass();

		$responseGetVacunaMascota = serviciosMascota::getVacunaMascota($idVacunaMascota);
		if($responseGetVacunaMascota->result == 2){
			$responseGetMascota = mascotas::getMascota($responseGetVacunaMascota->objectResult->idMascota);
			if($responseGetMascota->result == 2){
				$fechaProximaDosis = null;
				if($responseGetVacunaMascota->objectResult->intervaloDosis != 1)
					$fechaProximaDosis = fechas::getDateToINT(fechas::calcularFechaProximaDosis($dateDosis, $responseGetVacunaMascota->objectResult->intervaloDosis));

				$dateDosis = fechas::getDateToINT($dateDosis);
				$responseUpdateVacuna = serviciosMascota::aplicarDosisVacunaMascota($idVacunaMascota, $dateDosis, $responseGetVacunaMascota->objectResult->numDosis + 1, $fechaProximaDosis);
				if($responseUpdateVacuna->result == 2){
					$responseInsertHistoriaClinica = ctr_historiales::agregarHistoriaClinica($responseGetMascota->objectResult->idMascota, null, "Se aplicó dosis N° " . ($responseGetVacunaMascota->objectResult->numDosis +1) . " de la vacuna " . $responseGetVacunaMascota->objectResult->nombreVacuna . ".", null, null);
					if($responseInsertHistoriaClinica->result == 2){
						$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Aplicar dosis", null, $responseGetMascota->objectResult->idMascota, "Se aplicó dosis N° " . ($responseGetVacunaMascota->objectResult->numDosis +1) . " de la vacuna " . $responseGetVacunaMascota->objectResult->nombreVacuna . ".");
						if($responseInsertHistorial->result == 2){
							$response->result = 2;
							$response->message = "Se registró la dosis de la vacuna aplicada.";
						}else{
							$response->result = 1;
							$response->message = "Se registró la dosis de la vacuna aplicada.";
						}
					}else{
						$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Aplicar dosis", null, $responseGetMascota->objectResult->idMascota, "Se aplicó dosis N° " . ($responseGetVacunaMascota->objectResult->numDosis +1) . " de la vacuna " . $responseGetVacunaMascota->objectResult->nombreVacuna . ".");
						if($responseInsertHistorial->result == 2){
							$response->result = 1;
							$response->message = "Se registró la dosis de la vacuna aplicada.";
						}else{
							$response->result = 1;
							$response->message = "Se registró la dosis de la vacuna aplicada.";
						}
					}
					$responseGetVacuna = serviciosMascota::getVacunaMascotaToShow($idVacunaMascota);
					if($responseGetVacuna->result == 2)
						$response->updatedVacuna = $responseGetVacuna->objectResult;
				}else return $responseUpdateVacuna;
			}else return $responseGetMascota;
		}else return $responseGetVacunaMascota;

		return $response;
	}

	public function getVacunasMascota($idMascota){
		return serviciosMascota::getVacunasMascotas($idMascota);
	}

	public function getVacunasVencidasMascota($idMascota){
		$fechaActual = fechas::getDateToINT(date('Y-m-d'));
		return serviciosMascota::getVacunasVencidasMascota($idMascota, $fechaActual);
	}

	public function getVacunaMascota($idVacunaMascota){
		return serviciosMascota::getVacunaMascota($idVacunaMascota);
	}

	public function getVacunaMascotaToShowView($idVacunaMascota){
		return serviciosMascota::getVacunaMascotaToShow($idVacunaMascota);
	}

	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//-------------------------------------------------------FUNCIONES ANALISIS-------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------

	public function getAnalisisMascota($idMascota){
		return serviciosMascota::getAnalisisMascota($idMascota);
	}

	public function insertAnalisis($idMascota, $nombre, $fecha, $detalle, $resultado){
		$response = new \stdClass();

		$responseGetMascota = mascotas::getMascota($idMascota);
		if($responseGetMascota->result == 2){
			$fecha = fechas::getDateToINT($fecha);
			$responseInsertAnalisis = serviciosMascota::insertAnalisis($idMascota, $nombre, $fecha, $detalle, $resultado);
			if($responseInsertAnalisis->result == 2){
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Nuevo Análisis ingresado", null, $idMascota, "La mascota de nombre " . $responseGetMascota->objectResult->nombre . " se le fue ingresado un análisis de " . $nombre . ".");
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "El análisis fue ingresado correctamente.";
				}else{
					$response->result = 1;
					$response->message = "El análisis fue ingresado correctamente.";
				}

				$responseGetInserted = serviciosMascota::getAnalisisToShow($responseInsertAnalisis->id);
				if($responseInsertAnalisis->result == 2)
					$response->newAnalisis = $responseGetInserted->objectResult;
			}else return $responseInsertAnalisis;
		}else return $responseGetMascota;

		return $response;
	}

	public function updateAnalisis($idAnalisis, $nombre, $fecha, $detalle, $resultado){
		$response = new \stdClass();

		$responseGetAnalisis = serviciosMascota::getAnalisis($idAnalisis);
		if($responseGetAnalisis->result == 2){
			$responseGetMascota = mascotas::getMascota($responseGetAnalisis->objectResult->idMascota);
			$fecha = fechas::getDateToINT($fecha);
			$responseUpdateAnalisis = serviciosMascota::updateAnalisisMascota($idAnalisis, $nombre, $fecha, $detalle, $resultado);
			if($responseUpdateAnalisis->result == 2){
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Modificar Análisis", null, $responseGetAnalisis->objectResult->idMascota, "El análisis " . $responseGetAnalisis->objectResult->nombre ." fue modificado.");
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "El análisis fue modificado correctamente.";
				}else{
					$response->result = 1;
					$response->message = "El análisis fue modificado correctamente.";
				}

				$responseGetInserted = serviciosMascota::getAnalisisToShow($idAnalisis);
				if($responseGetInserted->result == 2)
					$response->newAnalisis = $responseGetInserted->objectResult;
			}else return $responseUpdateAnalisis;
		}else return $responseGetAnalisis;

		return $response;
	}

	public function deleteAnalisis($idAnalisis){
		$response = new \stdClass();

		$responseGetAnalisis = serviciosMascota::getAnalisis($idAnalisis);
		if($responseGetAnalisis->result == 2){
			$responseDeleteAnalisis = serviciosMascota::deleteAnalisis($idAnalisis);
			if($responseDeleteAnalisis->result == 2){
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Borrar análisis", null, $responseGetAnalisis->objectResult->idMascota, "Se borró el análisis " . $responseGetAnalisis->objectResult->nombre . ".");
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "El análisis fue borrado correctamente.";
				}else{
					$response->result = 1;
					$response->message = "El análisis fue borrado correctamente.";
				}
			}else {
				$response->result = 0;
				$response->message = "El análisis no fue borrado por un error interno.";
			}
		}else return $responseGetAnalisis;

		return $response;
	}

	public function getAnalisis($idAnalisis){
		return serviciosMascota::getAnalisis($idAnalisis);
	}

	public function getAnalisisToShow($idAnalisis){
		return serviciosMascota::getAnalisisToShow($idAnalisis);
	}

	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//-------------------------------------------------------------FUNCIONES ENFERMEDAD-----------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------

	public function updateEnfermedad($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones){
		$response = new \stdClass();

		$responseGetEnfermedad = serviciosMascota::getEnfermedadMascota($idEnfermedad);
		if($responseGetEnfermedad->result == 2){
			$responseGetMascota = mascotas::getMascota($responseGetEnfermedad->objectResult->idMascota);
			if($responseGetMascota->result == 2){
				$fechaDiagnostico = fechas::getDateToINT($fechaDiagnostico);
				$responseUpdateEnfermedad = serviciosMascota::updateEnfermedadMascota($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones);
				if($responseUpdateEnfermedad->result == 2){
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Enfermedad actualizada", null, $responseGetMascota->objectResult->idMascota, "La mascota de nombre " . $responseGetMascota->objectResult->nombre . " modificó la información de la enfermedad " . $nombre . ".");
					if($responseInsertHistorial->result == 2){
						$response->result = 2;
						$response->message = "La enfermedad fue modificada correctamente.";
					}else{
						$response->result = 1;
						$response->message = "La enfermedad fue modificada correctamente.";
					}

					$repsonseGetUpdatedEnfermedad = serviciosMascota::getEnfermedadMascota($idEnfermedad);
					if($repsonseGetUpdatedEnfermedad->result == 2){
						$repsonseGetUpdatedEnfermedad->objectResult->fechaDiagnostico = fechas::dateToFormatBar(fechas::getDateToINT($repsonseGetUpdatedEnfermedad->objectResult->fechaDiagnostico));
						$response->updatedEnfermedad = $repsonseGetUpdatedEnfermedad->objectResult;
					}
				}else return $responseUpdateEnfermedad;
			}else return $responseGetMascota;
		}else return $responseGetEnfermedad;

		return $response;
	}

	public function deleteEnfermedad($idEnfermedad){
		$response = new \stdClass();

		$responseGetEnfermedad = serviciosMascota::getEnfermedadMascota($idEnfermedad);
		if($responseGetEnfermedad->result == 2){
			$responseGetMascota = mascotas::getMascota($responseGetEnfermedad->objectResult->idMascota);
			if($responseGetMascota->result == 2){
				$responseDeleteEnfermedad = serviciosMascota::deleteEnfermedad($idEnfermedad);
				if($responseDeleteEnfermedad->result == 2){
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Borrar enfermedad", null, $responseGetEnfermedad->objectResult->idMascota, "Se borró la enfermedad " . $responseGetEnfermedad->objectResult->nombreEnfermedad . ".");
					if($responseInsertHistorial->result == 2){
						$response->result = 2;
						$response->message = "La enfermedad fue borrada correctamente.";
					}else{
						$response->result = 1;
						$response->message = "La enfermedad fue borrada correctamente.";
					}
				}else return $responseDeleteEnfermedad;
			}else return $responseGetMascota;
		}else return $responseGetEnfermedad;

		return $response;
	}

	public function insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnostico, $observaciones){
		$response = new \stdClass();

		$responseGetMascota = mascotas::getMascota($idMascota);
		if($responseGetMascota->result == 2){
			$fechaDiagnostico = fechas::getDateToINT($fechaDiagnostico);
			$responseInsertEnfermedad = serviciosMascota::insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnostico, $observaciones);
			if($responseInsertEnfermedad->result == 2){
				$responseInsertHistorial = ctr_historiales::agregarHistoriaClinica($idMascota, null, "Se agregó la enfermedad " . $nombre . " a la mascota " . $responseGetMascota->objectResult->nombre, null, null);
				if($responseInsertHistorial->result == 2){
					$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Nueva enfermedad mascota", null, $idMascota, "Se agregó la enfermedad " . $nombre . " a la mascota " . $responseGetMascota->objectResult->nombre);
					$response->result = 2;
					$response->message = "Se agregó correctamente la nueva enfermedad.";
				}else{
					$response->result = 1;
					$response->message = "Se agregó correctamente la nueva enfermedad.";
				}
				$responseGetEnfermedadFormat = serviciosMascota::getEnfermedadMascotaToShow($responseInsertEnfermedad->id);
				if($responseGetEnfermedadFormat->result == 2)
					$response->newEnfermedad = $responseGetEnfermedadFormat->objectResult;

			}else return $responseInsertEnfermedad;
		}else return $responseGetMascota;

		return $response;
	}

	public function getEnfermedadesMascota($idMascota){
		return serviciosMascota::getEnfermedadesMascota($idMascota);
	}

	public function getEnfermedadMascota($idEnfermedad){
		return serviciosMascota::getEnfermedadMascota($idEnfermedad);
	}

	public function getEnfermedadMascotaToShow($idEnfermedad){
		return serviciosMascota::getEnfermedadMascotaToShow($idEnfermedad);
	}
}