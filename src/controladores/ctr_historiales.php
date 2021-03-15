<?php

require_once '../src/clases/historiales/historialClinico.php';
require_once '../src/clases/historiales/historialSocios.php';
require_once '../src/clases/historiales/historialUsuarios.php';

require_once '../src/clases/copiarDB.php';
require_once '../src/clases/fechas.php';

class ctr_historiales {

	public function levantarDB(){
 				//($cedula, $nombre, $numSocio, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUltimoPago, $fechaUltimaCouta)

		return copiarDB::getSociosOriginal();
	}

	//----------------------------------- FUNCIONES DE HISTORIAL CLINICO ------------------------------------------

	public function getHistoriasClinica($idMascota){
		return historialClinico::getOneHistoriaClinicaMascota($idMascota);
	}

	public function checkHayHistorial($idMascota){
		return historialClinico::checkHayHistorial($idMascota);
	}
    //-------------------------------------------------------------------------------------------------------------
    //----------------------------------- FUNCIONES DE HISTORIAL SOCIO --------------------------------------------


    //-------------------------------------------------------------------------------------------------------------
    //----------------------------------- FUNCIONES DE HISTORIAL USUARIO ------------------------------------------


    //-------------------------------------------------------------------------------------------------------------
}

?>