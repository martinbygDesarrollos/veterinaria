<?php

require_once '../src/controladores/ctr_usuarios.php';
require_once '../src/controladores/ctr_historiales.php';
require_once '../src/clases/mascotas.php';
require_once '../src/clases/fechas.php';
require_once '../src/clases/serviciosMascota.php';

class ctr_mascotas {

	public function getFechaActual(){
		$fecha = date('Y-m-d');
		return fechas::parceFechaFormatDMA(fechas::parceFechaInt($fecha),"/");
	}
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
    //---------------------------------------------------FUNCIONES DE MASCOTA --------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------

	public function  insertNewMascota($idSocio, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones){
		$response = new \stdClass();

		$fechaNacimientoFormat = fechas::parceFechaInt($fechaNacimiento);
		$idMascota = mascotas::insertMascota($nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimientoFormat, 1, $pelo, $chip, $observaciones);

		$socio = ctr_usuarios::getSocio($idSocio);
		if($socio){
			if($idMascota != false){
				$fechaCambio = fechas::parceFechaInt(date('Y-m-d'));
				$result = mascotas::vincularMascotaSocio($idSocio, $idMascota, $fechaCambio);
				if($result){
					$cuotaAsignada = ctr_usuarios::calcularCostoCuota($idSocio);
					$estadoCuota = "La cuota del socio no fue actualizada verifiquela.";
					if($cuotaAsignada){
						$estadoCuota = "Tambien fue actualizada la cuota de este socio.";
					}
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Agregó nueva mascota", "Se agrego una nueva mascota de nombre " . $nombre . " y se le asignó al socio " . $socio->nombre . $cuotaAsignada);
					$response->retorno = true;
					$response->mensaje = "Se ingreso la mascota correctamente y se vinculo al socio seleccionado. " . $estadoCuota;
					if($resultInsertOperacionUsuario)
						$response->enHistorial = "Registrado en el historial del usuario.";
					else
						$response->enHistorial = "No ingresado en historial de usuario.";
				}else{
					$response->retorno = false;
					$response->mensaje = "Se ingreso la mascota, por un error interno el sistema no pudo vincularla al socio seleccionado.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "Ocurrio un error interno y el sistema no pudo almacenar la mascota ingresada.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El socio al que desea asignarle la mascota no fue encontrado.";
			return $response;
		}
		return $response;
	}

	public function updateMascota($idSocio, $idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones){
		$response = new \stdClass();

		$socio = ctr_usuarios::getSocio($idSocio);
		if($socio != null){
			$mascota = mascotas::getMascota($idMascota);
			if($mascota != null){

				$fechaNacimientoFormat = fechas::parceFechaInt($fechaNacimiento);
				$result = mascotas::updateMascota($idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimientoFormat, $pelo, $chip, $observaciones);
				if($result){
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificar mascota", "Se modificó la información de la mascota de nombre " . $nombre . " vinculada al socio " . $socio->nombre .".");
					if($resultInsertOperacionUsuario)
						$response->enHistorial = "Registrado en el historial del usuario.";
					else
						$response->enHistorial = "No ingresado en historial de usuario.";
					$response->retorno = true;
					$response->mensaje = "La información de " . $nombre . " fue modificada correctamente.";
				}else{
					$response->retorno = false;
					$response->mensajeError = "La información de la mascota no pudo ser modificada, porfavor vuelva a intentarlo.";
				}
			}else{
				$response->retorno = true;
				$response->mensajeError = "La mascota que desea modificar no fue encontrada en el sistema, porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = true;
			$response->mensajeError = "El socio al que desea vincular la mascota no fue encontrado en el sistema, porfavor vuelva a intentarlo.";
		}

		return $response;
	}

	public function activarDesactivarMascota($idMascota){
		$response = new \stdClass();

		$mascota = mascotas::getMascota($idMascota);
		if($mascota != null){
			$socio = ctr_usuarios::getSocioMascota($idMascota);
			if($socio){
				if($socio->estado != 0){
					$estado = 0;
					if($mascota->estado == 0)
						$estado = 1;
					$result = mascotas::activarDesactivarMascota($idMascota, $estado);

					if($estado == 1)
						$estado = "Activada";
					else
						$estado = "Desactivada";

					if($result){
				//------------------------ RECALCULAR CUOTA ---------------------
						$cuotaAsignada = ctr_usuarios::calcularCostoCuota($socio->idSocio);
						$estadoCuota = "La cuota del socio no fue actualizada verifiquela.";
						if($cuotaAsignada){
							$estadoCuota = "Tambien fue actualizada la cuota de este socio.";
						}
				//------------------------------------------------------------------
				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
						$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Activar Desactivar Mascota", "La mascota de nombre " . $mascota->nombre . " y vinculada al socio " . $socio->nombre . " fue " . $estado . $cuotaAsignada);
						if($resultInsertOperacionUsuario)
							$response->enHistorial = "Registrado en el historial del usuario.";
						else
							$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------
						$response->retorno = true;
						$response->mensaje = "La mascota fue " . $estado . " correctamente.";
						$response->titulo = "Mascota " . $estado;
					}else{
						$response->retorno = false;
						$response->mensajeError = "Ocurrio un error, la mascota no pudo ser " . $estado . ", porfavor vuelva a intentarlo.";
						$response->titulo = "Error: Mascota no " . $estado;
					}
				}else{
					$response->retorno = false;
					$response->mensajeError = "No se puede modificar el estado de una mascota de un socio inactivo, sin activarlo previamente.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "No hay información de un responsable por esta mascota, por lo que no puede realizarse este cambio.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "La mascota seleccionada no fue encontrada.";
			$response->titulo = "Error: Mascota no encontrada";
		}

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
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Vincular socio a mascota", "La mascota de nombre " . $socio->nombre . " se vinculo al socio " . $socio->nombre . $cuotaAsignada);
					if($resultInsertOperacionUsuario)
						$response->enHistorial = "Registrado en el historial del usuario.";
					else
						$response->enHistorial = "No ingresado en historial de usuario.";
					//----------------------------------------------------------------------------------------------------------------
					$response->retorno = true;
					$response->mensaje = "La mascota fue vinculada al socio correctamente" . $cuotaAsignada;
				}else{
					$response->retorno = false;
					$response->mensajeError = "La mascota no pudo ser vinculada al socio porfavor vuelva a intentarlo.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "La mascota seleccionada no fue encontrada en el sistema, porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "El socio que selecciono no fue encontrado en el sistema, porfavor vuelva a intentarlo.";
		}

		return $response;
	}

	public function getMasctoasSocio($idSocio){
		return mascotas::getMascotasSocios($idSocio);
	}

	public function getMascota($idMascota){
		return mascotas::getMascota($idMascota);
	}

	public function getMascotaCompleto($idMascota){
		$mascota = mascotas::getMascota($idMascota);
		$enfermedades = serviciosMascota::getEnfermedades($idMascota);
		$vacunasMascota = serviciosMascota::getVacunaMascotaID($idMascota);
		$duenio = ctr_usuarios::getSocioMascota($idMascota);
		$analisis = serviciosMascota::getAnalisisMascota($idMascota);
		return array(
			"mascota" => $mascota,
			"duenio" => $duenio,
			"hayHistorial" => ctr_historiales::checkHayHistorial($idMascota),
			"enfermedades" => $enfermedades,
			"analisis" => $analisis);
	}

	public function getMascotas(){
		return mascotas::getMascotas();
	}

	public function getMascotasInactivasPendientes(){
		return mascotas::getMascotasInactivasPendientes();
	}

	public function getMascotasPagina($ultimoID, $estadoMascota){
		if($ultimoID == 0){
			$maxId = mascotas::getMascotaMaxId($estadoMascota);
			$mascotas = mascotas::getMascotasPagina($maxId->idMaximo, $estadoMascota);
			$minId = mascotas::getMin($mascotas, $maxId->idMaximo);
			return array(
				"min" => $minId,
				"max" => $maxId,
				"mascotas" => $mascotas
			);
		}else{
			$mascotas = mascotas::getMascotasPagina($ultimoID, $estadoMascota);
			$minId = mascotas::getMin($mascotas, $ultimoID);
			return array(
				"min" => $minId,
				"max" => $ultimoID,
				"mascotas" => $mascotas
			);
		}
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

    //--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------FUNCIONES VACUNAS------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------

	public function aplicarNuevaVacunaMascota($idMascota, $nombreVacuna, $intervalo, $fechaDosis, $observaciones){
		$response = new \stdClass();

		$mascota = mascotas::getMascota($idMascota);
		if($mascota){
			$socio = ctr_usuarios::getSocioMascota($idMascota);
			$conSocio = "sin socio vinculado";
			if($socio != null)
				$conSocio = "vinculada al socio " . $socio->nombre;
			$fechaDosisFormat = fechas::parceFechaInt($fechaDosis);
			$fechaProximaDosis = 0;
			if($intervalo != 1)
				$fechaProximaDosis = fechas::parceFechaInt(fechas::calcularFechaProximaDosis($fechaDosis, $intervalo));

			$result = serviciosMascota::insertVacunaMascota($nombreVacuna, $idMascota, $intervalo, 1, $fechaDosisFormat, $fechaDosisFormat,$fechaProximaDosis, $observaciones);
			if($result){
				$resultInsertHistoria = ctr_historiales::insertHistoriaMascota($idMascota, "Se aplicó primer dosis de la vacuna " . $nombreVacuna, "" , "");
				$enHistoriaClinica = "";
				if($resultInsertHistoria)
					$enHistoriaClinica = "Se generó un registro en la historia clínica de la mascota.";

				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Aplicar nueva vacuna", "La mascota de nombre " . $mascota->nombre . " " . $conSocio . " se le aplicó una vacuna de nombre " . $nombreVacuna . ". " . $enHistoriaClinica);
				if($resultInsertOperacionUsuario)
					$response->enHistorial = "Registrado en el historial del usuario.";
				else
					$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------

				$response->retorno = true;
				$response->mensaje = "La vacuna fue vinculada correctamente a la mascota. " . $enHistoriaClinica;
			}else{
				$response->retorno = false;
				$response->mensajeError = "La vacuna no pudo ser ingresada correctamente, verifique la información y vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "La mascota a la que se le quiere aplicar la vacuna no fue encontrada por un error interno, porfavor vuelva a intentarlo.";
		}
		return $response;
	}

	public function aplicarDosisVacuna($idVacunaMascota){
		$response = new \stdClass();

		$vacunaMascota = serviciosMascota::getVacunaMascota($idVacunaMascota);
		if($vacunaMascota){
			$mascota = mascotas::getMascota($vacunaMascota->idMascota);
			if($mascota){
				$socio = ctr_usuarios::getSocioMascota($mascota->idMascota);
				$conSocio = "sin socio vinculado";
				if($socio != null)
					$conSocio = "vinculada al socio " . $socio->nombre;

				$fechaUltimaDosis = fechas::parceFechaInt(date('Y-m-d'));
				$fechaProximaDosis = 0;
				if($vacunaMascota->intervaloDosis != 1)
					$fechaProximaDosis = fechas::parceFechaInt(fechas::calcularFechaProximaDosis(date('Y-m-d'), $vacunaMascota->intervaloDosis));

				$result = serviciosMascota::aplicarDosisVacunaMascota($idVacunaMascota, $fechaUltimaDosis, ($vacunaMascota->numDosis + 1), $fechaProximaDosis);
				if($result){
					$resultInsertHistoria = ctr_historiales::insertHistoriaMascota($vacunaMascota->idMascota, "Se aplicó dosis N° " . ($vacunaMascota->numDosis + 1) . " de la vacuna " . $vacunaMascota->nombreVacuna, "" , "");
					$enHistoriaClinica = "";
					if($resultInsertHistoria)
						$enHistoriaClinica = "Se generó un registro en la historia clínica de la mascota.";

				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
					$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Aplicar dosis vacuna", "La mascota de nombre " . $mascota->nombre . " " . $conSocio . " se le fue aplicada una dosis de " . $vacunaMascota->nombreVacuna . ". " . $enHistoriaClinica);
					if($resultInsertOperacionUsuario)
						$response->enHistorial = "Registrado en el historial del usuario.";
					else
						$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------
					$response->retorno = true;
					$response->mensaje = "La dosis de la vacuna aplicada fue almacenada correctamente. " . $enHistoriaClinica;
				}else{
					$response->retorno = false;
					$response->mensajeError = "La dosis de la vacuna aplicada no pudo ser registrada porfavor vuelva a intentarlo.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "La vacuna que esta intentando aplicar no tiene una mascota vinculada. porfavor actualice su pantalla.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "No se pudo encontrar la vacuna para registrar la dosis, porfavor intentelo nuevamente.";
		}

		return $response;
	}

	public function getVacunasMascotas(){
		return serviciosMascota::getVacunasMascotas();
	}

	public function getVacunasVencidasMascota($idMascota){
		$fechaActual = fechas::parceFechaInt(date('Y-m-d'));
		return serviciosMascota::getVacunasVencidasMascota($idMascota, $fechaActual);
	}

	public function getVacunasNoAplicadas($idMascota){
		return vacunas::getVacunasNoAplicadas($idMascota);
	}

	public function getVacunasPagina($ultimoID, $idMascota){
		if($ultimoID == 0){
			$maxId = serviciosMascota::getVacunaMaxId($idMascota);
			$vacunas = serviciosMascota::getVacunasPagina($maxId, $idMascota);
			$minId = serviciosMascota::getVacunasMinId($vacunas, $maxId);
			return array(
				"min" => $minId,
				"max" => $maxId,
				"vacunas" => $vacunas
			);
		}else{
			$vacunas = serviciosMascota::getVacunasPagina($ultimoID, $idMascota);
			$minId = serviciosMascota::getVacunasMinId($vacunas, $ultimoID);
			return array(
				"min" => $minId,
				"max" => $ultimoID,
				"vacunas" => $vacunas
			);
		}
	}

	public function getInfoVencimientos(){
		$fechaActual = date('Y-m-d');
		$fecha = date("Y-m-d", strtotime("$fechaActual + 3 day"));
		$fecha = fechas::parceFechaInt($fecha);
		$vacunasMascotas = serviciosMascota::getVacunasVencidas($fecha);
		if(sizeof($vacunasMascotas) == 0) return null;
		else return $vacunasMascotas;
	}
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//-------------------------------------------------------FUNCIONES ANALISIS-------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------

	public function insertNewAnalisis($idMascota, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis){
		$response = new \stdClass();

		$mascota = mascotas::getMascota($idMascota);
		if($mascota){
			$fechaAnalisisFormat = fechas::parceFechaInt($fechaAnalisis);
			$result = serviciosMascota::insertNewAnalisis($idMascota, $nombreAnalisis, $fechaAnalisisFormat, $detalleAnalisis, $resultadoAnalisis);

			if($result){
				$socio = ctr_usuarios::getSocioMascota($idMascota);
				$conSocio = "sin socio vinculado";
				if($socio != null)
					$conSocio = "vinculada al socio " . $socio->nombre;
				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Nuevo Analisis ingresado", "La mascota de nombre " . $mascota->nombre . " " . $conSocio . " se le fue ingresado un analisis de " . $nombreAnalisis . ".");
				if($resultInsertOperacionUsuario)
					$response->enHistorial = "Registrado en el historial del usuario.";
				else
					$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------
				$response->retorno = true;
				$response->mensaje = "EL analisis de " . $mascota->nombre . " se ingresó correctamente.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "Ocurrio un error y el analisis que se intento agregar no pudo ser guardado, porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "La mascota a la que se le quiere ingresar un analisis no fue encontrada en el sisitema.";
		}

		return $response;
	}

	public function updateAnalisis($idAnalisis, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis){
		$response = new \stdClass();

		$analisis = serviciosMascota::getAnalisis($idAnalisis);
		if($analisis){
			$mascota = mascotas::getMascota($analisis->idMascota);
			$socio =  ctr_usuarios::getSocioMascota($mascota->idMascota);
			$conSocio = "sin socio vinculado";
			if($socio != null)
				$conSocio = "vinculada al socio " . $socio->nombre;
			$fechaAnalisisFormat = fechas::parceFechaInt($fechaAnalisis);
			$result = serviciosMascota::updateAnalisisMascota($idAnalisis, $nombreAnalisis, $fechaAnalisisFormat, $detalleAnalisis, $resultadoAnalisis);
			if($result){
				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Modificar Analisis", "El analisis de " . $nombreAnalisis ." perteneciente a la mascota " . $mascota->nombre . " " . $conSocio . " fue modificado.");
				if($resultInsertOperacionUsuario)
					$response->enHistorial = "Registrado en el historial del usuario.";
				else
					$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------
				$response->retorno = true;
				$response->mensaje = "EL analisis fue modificado correctamente.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "Ocurrio un error y el analisis no pudo ser modificado, porfavor vuelva a intentarlo.";
			}

		}else{
			$response->retorno = false;
			$response->mensajeError = "El analisis que intenta modificar no fue encontrado ene el sistema porfavor vuelva a intentarlo.";
		}

		return $response;
	}

	public function getAnalisis($idAnalisis){
		$response = new \stdClass();
		$analisis = serviciosMascota::getAnalisis($idAnalisis);
		if($analisis){
			$analisis->fecha = fechas::parceFechaFormatDMA($analisis->fecha, "/");
			return $analisis;
		}else{
			$response->retorno = false;
			$response->mensajeError = "El analisis que intenta seleccionar no se encontro en el sistema, porfavor vuelva a intentarlo.";
			return $response;
		}
	}

	public function getAnalisisPagina($ultimoID, $idMascota){
		if($ultimoID == 0){
			$maxId = serviciosMascota::getAnalisisMaxId($idMascota);
			$analisis = serviciosMascota::getAnalisisPagina($maxId, $idMascota);
			$minId = serviciosMascota::getAnalisisMinId($analisis, $maxId);
			return array(
				"min" => $minId,
				"max" => $maxId,
				"analisis" => $analisis
			);
		}else{
			$analisis = serviciosMascota::getAnalisisPagina($ultimoID, $idMascota);
			$minId = serviciosMascota::getAnalisisMinId($analisis, $ultimoID);
			return array(
				"min" => $minId,
				"max" => $ultimoID,
				"analisis" => $analisis
			);
		}
	}

	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//-------------------------------------------------------------FUNCIONES ENFERMEDAD-----------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------------------------------------------------------------------------------------------

	public function insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnostico, $observaciones){
		$response = new \stdClass();

		$mascota = mascotas::getMascota($idMascota);

		if($mascota != null){
			$fechaDiagnosticoFormat = fechas::parceFechaInt($fechaDiagnostico);
			$socio = ctr_usuarios::getSocioMascota($idMascota);
			$conSocio = "sin socio vinculado";
			if($socio != null)
				$conSocio = "vinculada al socio " . $socio->nombre;
			$result = serviciosMascota::insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnosticoFormat, $observaciones);
			if($result){
				$resultInsertHistoria = ctr_historiales::insertHistoriaMascota($idMascota, "Se asigno enfermedad a ". $mascota->nombre, "Se diagnostico la mascota de " . $nombre, "");
				$enHistoriaClinica = "";
				if($resultInsertHistoria)
					$enHistoriaClinica = "Se generó un registro en la historia clínica de la mascota.";

				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Enfermedad mascota", "La mascota de nombre " . $mascota->nombre . " " . $conSocio . " se le fue diagnosticada la enfermedad " . $nombre . ". " . $enHistoriaClinica);
				if($resultInsertOperacionUsuario)
					$response->enHistorial = "Registrado en el historial del usuario.";
				else
					$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------
				$response->retorno = true;
				$response->mensaje = "La enfermdad de " . $mascota->nombre . " fue agregada correctamente. ";
			}else{
				$response->retorno = false;
				$response->mensajeError = "La enfermdad de " . $mascota->nombre . " no pudo ingresarse, porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "La mascota no fue encontrada en el sistema, porfavor vuelva a intentarlo.";
		}

		return $response;
	}

	public function updateEnfermedadMascota($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones){
		$response = new \stdClass();

		$enfermedad = serviciosMascota::getEnfermedadMascota($idEnfermedad);

		if($enfermedad != null){
			$fechaDiagnostico = fechas::parceFechaInt($fechaDiagnostico);
			$result = serviciosMascota::updateEnfermedadMascota($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones);
			if($result){

				//----------------------------INSERTAR REGISTRO HISTORIAL USUARIO------------------------------------------------
				$resultInsertOperacionUsuario = ctr_historiales::insertHistorialUsuario("Enfermedad actualizada", "La mascota de nombre " . $mascota->nombre . " " . $conSocio . " se le actualizó la información de la enfermedad " . $nombre . ".");
				if($resultInsertOperacionUsuario)
					$response->enHistorial = "Registrado en el historial del usuario.";
				else
					$response->enHistorial = "No ingresado en historial de usuario.";
				//----------------------------------------------------------------------------------------------------------------

				$response->retorno = true;
				$response->mensaje = "La enfermedad fue modificada correctamente.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "Ocurrio un error y la enfermedad no pudo modificarse, porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "La enfermedad que se desea modificar no fue encontrada en el sistema, porfavor vuelva a intentarlo.";
		}
		return $response;
	}

	public function getEnfermedadMascota($idEnfermedad){
		return serviciosMascota::getEnfermedadMascota($idEnfermedad);
	}

	public function getEnfermedadesPagina($ultimoID, $idMascota){
		if($ultimoID == 0){
			$maxId = serviciosMascota::getEnfermedadesMaxId($idMascota);
			$enfermedades = serviciosMascota::getEnfermedadesPagina($maxId, $idMascota);
			$minId = serviciosMascota::getEnfermedadesMinId($enfermedades, $maxId);
			return array(
				"min" => $minId,
				"max" => $maxId,
				"enfermedades" => $enfermedades
			);
		}else{
			$enfermedades = serviciosMascota::getEnfermedadesPagina($ultimoID, $idMascota);
			$minId = serviciosMascota::getEnfermedadesMinId($enfermedades, $ultimoID);
			return array(
				"min" => $minId,
				"max" => $ultimoID,
				"enfermedades" => $enfermedades
			);
		}
	}
}