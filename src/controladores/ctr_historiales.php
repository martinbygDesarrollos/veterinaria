<?php


require_once '../src/clases/configuracionSistema.php';
require_once '../src/clases/historiales.php';
require_once '../src/clases/migrateDB.php';
require_once "../src/utils/fechas.php";
require_once '../src/controladores/ctr_mascotas.php';
require_once '../src/controladores/ctr_usuarios.php';

class ctr_historiales {

	public function getFileVistaFactura(){
		$response = new \stdClass();

		$responseGetSocios = ctr_usuarios::getSociosVistaFactura();
		if($responseGetSocios->result == 2){
			$arrayResult = array();
			foreach ($responseGetSocios->listResult as $key => $socio){
				$newArray = array();

				$newArray['numero'] = $socio['idSocio'];
				$newArray['proximavacuna'] = null;
				$newArray['mascota'] = null;
				$newArray['socio'] = $socio['nombre'];
				$newArray['direccion'] = $socio['direccion'];
				$newArray['casa'] = null;
				$newArray['apto'] = null;

				$responseGetCantMascotas = ctr_mascotas::getSocioActivePets($socio['idSocio']);
				if($responseGetCantMascotas->result == 2){
					$newArray['cantidadmascotas'] = sizeof($responseGetCantMascotas->mascotas);
					$responseCalculateQuota = configuracionSistema::getQuotaSocio(sizeof($responseGetCantMascotas->mascotas));
					if($responseCalculateQuota->result == 2)
						$socio['cuota'] = $responseCalculateQuota->quota;
				}

				if($socio['lugarPago'] == 1)
					$newArray['lugarpago'] = "Cobrador";
				else
					$newArray['lugarpago'] = "Veterinaria";

				$newArray['rut'] = $socio['rut'];
				$newArray['cuota'] = $socio['cuota'];
				$newArray['fechaingreso'] = fechas::dateToFormatBar($socio['fechaIngreso']) . " 12:13:00";

				$arrayResult[] = $newArray;
			}
			ctr_historiales::creteFile($arrayResult);
			$response->listResult = $arrayResult;
		}

		return $response;
	}

	public function creteFile($arrayResult){
		if(is_array($arrayResult) && sizeof($arrayResult) > 0){
			$file = fopen('C:\Users\Usuario\Desktop\archivo\FACTURA.txt','w+');

			foreach ($arrayResult as $key => $value) {
				fwrite($file, $value['numero'] .",");
				fwrite($file, $value['socio'] .",");
				fwrite($file, $value['direccion'] .",");
				fwrite($file, $value['casa'] .",");
				fwrite($file, $value['apto'] .",");
				fwrite($file, $value['rut'] .",");
				fwrite($file, $value['cantidadmascotas'] .",");
				fwrite($file, $value['cuota'] .",");
				fwrite($file, $value['lugarpago'] .",");
				fwrite($file, $value['proximavacuna'] .",");
				fwrite($file, $value['mascota'] .",");
				fwrite($file, $value['fechaingreso'] . ",");

				fwrite($file,chr(13).chr(10));
			}
			fclose($file);
		}
	}

	public function executeMigrateDB($session){
		$response = new \stdClass();

		if(strcmp($session['USUARIO'], "martin") == 0){
			$arraySocios = migrateDB::getSocios();
			if(is_array($arraySocios) && sizeof($arraySocios) > 2){
				foreach ($arraySocios as $key => $socio){
					$arrayMascotaSocio = migrateDB::getMascotasSocio($socio['idSocio'], $socio['numSocio'], $socio['estado']);
					if(is_array($arrayMascotaSocio) && sizeof($arrayMascotaSocio) != 0){
						foreach ($arrayMascotaSocio as $key => $mascotaSocio){
							migrateDB::getFechaCambio($mascotaSocio['nombre'], $socio['numSocio'], $mascotaSocio['idMascotaSocio']);
							migrateDB::getHistoriaClinica($mascotaSocio['idMascota'], $mascotaSocio['nombre'], $socio['numSocio']);
						}
					}
				}
			}

			migrateDB::getMascotasSinSocio();
			migrateDB::getVacunasMascotas();
			migrateDB::getEnfermedades();

			$response->result = 2;
			$response->message = "Se analizaron e ingresaron los registros del sistema anterior.";
		}else{
			$response->result = 0;
			$response->message = "Usted no es un usuarios con privilegios para ejecutar esta operación";
		}
		return $response;
	}

	public function getMontoCuotas(){
		return configuracionSistema::getQuota();
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

    //----------------------------------- FUNCIONES DE HISTORIAL SOCIO --------------------------------------------

	public function getListHistorialSocio($lastId, $idSocio){
		$responseGetHistorialSocio = historiales::getListHistorialSocio($lastId, $idSocio);
		if($responseGetHistorialSocio->result == 2){
			$arrayResult = array();
			foreach ($responseGetHistorialSocio->listResult as $key => $historial) {

				if(!is_null($historial['idMascota'])){
					$responseGetMascota = ctr_mascotas::getMascota($historial['idMascota']);
					if($responseGetMascota->result == 2)
						$historial['mascota'] = $responseGetMascota->objectResult->nombre;
				}
				$arrayResult[] = $historial;
			}
			$responseGetHistorialSocio->listResult = $arrayResult;
		}

		return $responseGetHistorialSocio;
	}

	public function crearHistorialSocio($idSocio, $idMascota, $fecha, $asunto, $importe, $observaciones){
		$response = new \stdClass();

		$responseGetSocio = ctr_usuarios::getSocio($idSocio);
		if($responseGetSocio->result == 2){
			if($idMascota != 0){
				$responseGetMascota = ctr_mascotas::getMascota($idMascota);
				if($responseGetMascota->result != 2)
					return $responseGetMascota;
			}else $idMascota = null;

			$fecha = fechas::getDateToINT($fecha);
			$fechaEmision = fechas::getDateTimeNowInt();
			$responseInsertHistorialSocio = historiales::insertHistorialSocio($idSocio, $idMascota, $asunto, $importe, $observaciones, $fecha, $fechaEmision);
			if($responseInsertHistorialSocio->result == 2){
				$responseInsertHistorial = ctr_historiales::insertHistorialUsuario("Agregar Historial Socio", $idSocio, $idMascota, "Se creo un registro en el historial del socio con motivo: " . $asunto);
				if($responseInsertHistorial->result == 2){
					$response->result = 2;
					$response->message = "Se creó un nuevo registro en el historial de socio, la operación fue guardada en el historial de usuario.";
				}else{
					$response->result = 1;
					$response->message = "Se creó un nuevo registro en el historial de socio, por un error interno la operación no fue guardada en el historial de usuario.";
				}

				$responseGetInserted = ctr_historiales::getHistorialSocioToShow($responseInsertHistorialSocio->id);
				if($responseGetInserted->result == 2)
					$response->newHistorial = $responseGetInserted->objectResult;
			}else return $responseInsertHistorialSocio;
		}else return $responseGetSocio;

		return $response;
	}

	public function getHistorialSocioToShow($idHistorialSocio){
		$responseGetHistorialSocio = historiales::getHistorialSocioToShow($idHistorialSocio);
		if($responseGetHistorialSocio->result == 2){
			if(!is_null($responseGetHistorialSocio->objectResult->idMascota)){
				$responseGetMascota = ctr_mascotas::getMascota($responseGetHistorialSocio->objectResult->idMascota);
				if($responseGetMascota->result == 2)
					$responseGetHistorialSocio->objectResult->mascota = $responseGetMascota->objectResult->nombre;
			}
		}

		return $responseGetHistorialSocio;
	}

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

    //-------------------------------------------------------------------------------------------------------------
}

?>