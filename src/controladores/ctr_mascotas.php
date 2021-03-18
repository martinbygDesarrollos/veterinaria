<?php

require_once '../src/controladores/ctr_usuarios.php';
require_once '../src/controladores/ctr_historiales.php';
require_once '../src/clases/mascotas.php';
require_once '../src/clases/fechas.php';
require_once '../src/clases/vacunasMascota.php';

class ctr_mascotas {

	public function getFechaActual(){
		$fecha = date('Y-m-d');
		return fechas::parceFechaFormatDMA(fechas::parceFechaInt($fecha),"/");
	}

    //----------------------------------- FUNCIONES DE MASCOTA ------------------------------------------
	public function  insertNewMascota($idSocio, $idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones){
		$response = new \stdClass();

		$fechaNacimientoFormat = fechas::parceFechaInt($fechaNacimiento);
		$idMascota = mascotas::insertMascota($nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimientoFormat, 1, $pelo, $chip, $observaciones);

		if($idMascota != false){
			$fechaCambio = fechas::parceFechaInt(date('Y-m-d'));
			$result = mascotas::vincularMascotaSocio($idSocio, $idMascota, $fechaCambio);
			if($result){
				$cuotaAsignada = ctr_usuarios::calcularCostoCuota($idSocio);
				$estadoCuota = "La cuota del socio no fue actualizada verifiquela.";
				if($cuotaAsignada){
					$estadoCuota = "Tambien fue actualizada la cuota de este socio.";
				}

				$response->retorno = true;
				$response->mensaje = "Se ingreso la mascota correctamente y se vinculo al socio seleccionado. " . $estadoCuota;
			}else{
				$response->retorno = false;
				$response->mensaje = "Se ingreso la mascota, por un error interno el sistema no pudo vincularla al socio seleccionado.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "Ocurrio un error interno y el sistema no pudo almacenar la mascota ingresada.";
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

	public function getMasctoasSocio($idSocio){
		return mascotas::getMascotasSocios($idSocio);
	}

	public function getMascota($idMascota){
		return mascotas::getMascota($idMascota);
	}

	public function getMascotaCompleto($idMascota){
		$mascota = mascotas::getMascota($idMascota);
		$vacunasMascota = vacunasMascota::getVacunaMascotaID($idMascota);
		$duenio = ctr_usuarios::getSocioMascota($idMascota);
		return array(
			"mascota" => $mascota,
			"vacunas" => $vacunasMascota,
			"hayHistorial" => ctr_historiales::checkHayHistorial($idMascota),
			"duenio" => $duenio);
	}

	public function getMascotas(){
		return mascotas::getMascotas();
	}

    //---------------------------------------------------------------------------------------------------

	//-------------------------------------FUNCIONES VACUNAS---------------------------------------------

	public function aplicarNuevaVacunaMascota($idMascota, $nombreVacuna, $intervalo, $fechaDosis, $observaciones){
		$response = new \stdClass();

		$mascota = mascotas::getMascota($idMascota);
		if($mascota){
			$fechaDosisFormat = fechas::parceFechaInt($fechaDosis);
			$fechaProximaDosis = 0;
			if($intervalo != 1)
				$fechaProximaDosis = fechas::parceFechaInt(fechas::calcularFechaProximaDosis($fechaDosis, $intervalo));

			$result = vacunasMascota::insertVacunaMascota($nombreVacuna, $idMascota, $intervalo, 1, $fechaDosisFormat, $fechaDosisFormat,$fechaProximaDosis, $observaciones);
			if($result){
				$response->retorno = true;
				$response->mensaje = "La vacuna fue vinculada correctamente a la mascota.";
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

		$vacunaMascota = vacunasMascota::getVacunaMascota($idVacunaMascota);
		if($vacunaMascota){
			$fechaUltimaDosis = fechas::parceFechaInt(date('Y-m-d'));
			$fechaProximaDosis = 0;
			if($vacunaMascota->intervaloDosis != 1)
				$fechaProximaDosis = fechas::parceFechaInt(fechas::calcularFechaProximaDosis(date('Y-m-d'), $vacunaMascota->intervaloDosis));

			$result = vacunasMascota::aplicarDosisVacunaMascota($idVacunaMascota, $fechaUltimaDosis, ($vacunaMascota->numDosis + 1), $fechaProximaDosis);
			if($result){
				$response->retorno = true;
				$response->mensaje = "La dosis de la vacuna aplicada fue almacenada correctamente.";
			}else{
				$response->retorno = false;
				$response->mensajeError = "La dosis de la vacuna aplicada no pudo ser registrada porfavor vuelva a intentarlo.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "No se pudo encontrar la vacuna para registrar la dosis, porfavor intentelo nuevamente.";
		}

		return $response;
	}

	public function getVacunasMascotas(){
		return vacunasMascota::getVacunasMascotas();
	}

	public function getVacunasVencidasMascota($idMascota){
		$fechaActual = fechas::parceFechaInt(date('Y-m-d'));
		return vacunasMascota::getVacunasVencidasMascota($idMascota, $fechaActual);
	}

	public function getVacunasNoAplicadas($idMascota){
		return vacunas::getVacunasNoAplicadas($idMascota);
	}


	public function getInfoVencimientos(){
		$fechaActual = date('Y-m-d');
		$fecha = date("Y-m-d", strtotime("$fechaActual + 3 day"));
		$fecha = fechas::parceFechaInt($fecha);
		$vacunasMascotas = vacunasMascota::getVacunasVencidas($fecha);
		return $vacunasMascotas;
	}
	//---------------------------------------------------------------------------------------------------
}