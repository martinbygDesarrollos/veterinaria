<?php

require_once '../src/controladores/ctr_usuarios.php';
require_once '../src/controladores/ctr_historiales.php';
require_once '../src/clases/mascotas.php';
require_once '../src/clases/fechas.php';
require_once '../src/clases/vacunas.php';

class ctr_mascotas {

    //----------------------------------- FUNCIONES DE MASCOTA ------------------------------------------
	public function  insertNewMascota($idSocio, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones){
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

	public function getMasctoasSocio($idSocio){
		return mascotas::getMascotasSocios($idSocio);
	}

	public function getMascota($idMascota){
		$mascota = mascotas::getMascota($idMascota);
		$vacunasMascota = vacunas::getVacunasMascota($idMascota);
		return array(
			"mascota" => $mascota,
			"vacunas" => $vacunasMascota,
			"hayHistorial" => ctr_historiales::checkHayHistorial($idMascota));
	}

	public function getMascotas(){
		return mascotas::getMascotas();
	}

    //---------------------------------------------------------------------------------------------------

	//-------------------------------------FUNCIONES VACUNAS---------------------------------------------

	public function insertNewVacuna($nombre, $codigo, $laboratorio){
		$response = new \stdClass();

		$resultNombre = vacunas::getVacunaNombre($nombre);
		$resultCodigo = vacunas::getVacunaCodigo($codigo);

		if(!$resultNombre){
			if(!$resultCodigo){
				$result = vacunas::insertVacuna($nombre, $codigo, $laboratorio);
				if($result){
					$response->retorno = true;
					$response->mensaje = "La vacuna fue ingresada correctamente en el sistema.";
				}else{
					$response->retorno = false;
					$response->mensajeError = "Ocurrio un error interno y la vacuna no pudo ser ingresada correctamente.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "Usted esta intentando ingresar una vacuna con un codigo que ya se encuentra en el sistema.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "Usted esta intentando ingresar una vacuna con un nombre que ya se encuentra en el sistema.";
		}
		return $response;
	}

	public function asignarVacunaMascota($idVacuna, $idMascota, $intervaloDosis, $numDosisTot, $vencimiento){

		$response = new \stdClass();

		$mascota = mascotas::getMascota($idMascota);
		if($mascota){
			$vacuna = vacunas::getVacuna($idVacuna);
			if($vacuna){
				if(!vacunas::getVacunaMascotaID($idMascota, $idVacuna)){
					$fechaVencimientoFormat = fechas::parceFechaInt($vencimiento);
					$result = vacunas::asignarVacunaMascota($idVacuna, $idMascota, $intervaloDosis, $numDosisTot, $fechaVencimientoFormat);
				}else{
					$response->retorno = false;
					$response->mensajeError = "La vacuna ya fue asignada a esta mascota por lo que no se duplicara la información.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "La vacuna que desea asigar a la mascota no fue encontrada en el sistema";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "La mascota a la que se le asigno la vacuna no fue encontrada en el sistema.";
		}
		return $response;
	}

	public function vacunarMascota($idMascota, $idVacuna){
		$response = new \stdClass();

		$mascota = mascotas::getMascota($idMascota);
		if($mascota){
			$vacuna = vacunas::getVacuna($idVacuna);
			if($vacuna){
				$result = vacunas::getVacunaMascotaID($idMascota, $idVacuna);
				if($result){
					$fechaFormat = fechas::parceFechaInt(date('Y-m-d'));
					$result = vacunas::vacunarMascota($result->idVacunaMascota, $fechaFormat);
					if($result){
						$response->retorno = true;
						$response->mensaje = "Se guardo correctamente la dosis aplicada a la mascota.";
					}else{
						$response->retorno = false;
						$response->mensajeError = "La dosis que se intento aplicar no pudo ser almacenada en el sistema por un error interno.";
					}
				}else{
					$response->retorno = false;
					$response->mensajeError = "Debe vincular la vacuna a la mascota previo a aplicar una dosis de esta.";
				}
			}else{
				$response->retorno = false;
				$response->mensajeError = "No se puede aplicar una dosis de una vacuna que no se encuentra registrada en el sistema.";
			}
		}else{
			$response->retorno = false;
			$response->mensajeError = "No se puede aplicar una vacuna a una mascota que no fue previamente registrada en el sistema.";
		}

		return $response;
	}

	//---------------------------------------------------------------------------------------------------

}