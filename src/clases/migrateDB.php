<?php

require_once "../src/utils/fechas.php";
require_once '../src/connection/open_connection.php';
require_once '../src/controladores/ctr_mascotas.php';

class migrateDB{

    function __construct(){
        ini_set('memory_limit','-1');
    }

    public function getFechaCambio($nombre, $duenio, $idMascotaSocio){
        $responseQueryCambio = migrateDB::sendQueryExternalDB("SELECT MAX(fechacambio) AS fecha FROM historial_mascota WHERE nombre = ? AND duenio = ?", array('ss', $nombre, $duenio), "OBJECT");
        if($responseQueryCambio->result == 2){
            $fecha = fechas::getDateToINT($responseQueryCambio->objectResult->fecha);
            if(strlen($fecha) == 8)
                DataBase::sendQuery("UPDATE mascotasocio SET fechaCambio= ? WHERE idMascotaSocio = ?", array('ii', $fecha, $idMascotaSocio),"BOOLE");
        }
    }

    public function getHistoriaClinica($idMascota, $nombre, $duenio){
        $responseGetHistoriaClinica = migrateDB::sendQueryExternalDB("SELECT * FROM historial_clinico WHERE socio = ? AND mascota = ?", array('is', $duenio, $nombre), "LIST");
        if($responseGetHistoriaClinica->result == 2){
            foreach ($responseGetHistoriaClinica->listResult as $key => $row) {
                $fecha = fechas::getDateToINT($row['fecha']);

                $diagnostico = migrateDB::clearEspecialCharacters($row['diagnostico']);
                if(strlen($diagnostico) < 4)
                    $diagnostico = null;

                $motivoConsulta = migrateDB::clearEspecialCharacters($row['motivoconsulta']);
                if(strlen($motivoConsulta) < 5)
                    $motivoConsulta = null;

                $observaciones = $row['descripcion'];
                if(strlen($observaciones) < 3)
                    $observaciones =  null;

                if(!is_null($motivoConsulta) || !is_null($diagnostico) || !is_null($observaciones)){
                    DataBase::sendQuery("INSERT INTO historiasclinica(idMascota, fecha, motivoConsulta, diagnostico, observaciones) VALUES (?,?,?,?,?)", array('iisss', $idMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones), "BOOLE");
                }
            }
        }
    }

    function getEnfermedades(){
        $responseQueryGetEnfermedades = migrateDB::sendQueryExternalDB("SELECT * FROM enfermedades", null, "LIST");
        if($responseQueryGetEnfermedades->result == 2){
            foreach ($responseQueryGetEnfermedades->listResult as $key => $row) {
                $nombreEnfermedad = migrateDB::clearEspecialCharacters($row['enfermedad']);
                if(strlen($nombreEnfermedad) > 2){
                    if($row['mes'] < 10)
                        $row['mes'] = "0" . $row['mes'];

                    $fechaDiagnostico = $row['anio'] . $row['mes'] . "01";
                    $responseGetMascota = ctr_mascotas::getMascotaId($row['mascota'], $row['socio']);
                    if($responseGetMascota->result == 2){
                        DataBase::sendQuery("INSERT INTO enfermedadesmascota(idMascota, fechaDiagnostico, nombreEnfermedad) VALUES (?,?,?)", array('iis',$responseGetMascota->objectResult->idMascota, $fechaDiagnostico, $nombreEnfermedad), "BOOLE");
                    }
                }
            }
        }
    }

    function getVacunasMascotas(){
        $responseQueryGetVacunas = migrateDB::sendQueryExternalDB("SELECT COUNT(VA.mascota) AS cantDosis, VA.mascota, VA.nombre, VA.socio, VA.fecha, VA.docis, VA.proximavacuna FROM vacuna_asignada AS VA, socio AS S, mascota AS M WHERE VA.socio = S.numero AND M.nombre = VA.mascota GROUP BY VA.mascota, VA.socio, VA.nombre", null, "LIST");
        if($responseQueryGetVacunas->result == 2){
            foreach ($responseQueryGetVacunas->listResult as $key => $row) {
                $row['nombre'] = migrateDB::clearEspecialCharacters($row['nombre']);
                if(!is_null($row['nombre']) && strlen($row['nombre']) > 2){
                    $cantDosis = 1;
                    if(!is_null($row['cantDosis']) && strlen($row['cantDosis']) > 1){
                        $row['cantDosis'] = migrateDB::getNumericPart($row['cantDosis']);
                        $row['cantDosis'] = migrateDB::clearEspecialCharacters($row['cantDosis']);
                        $cantDosis = $row['cantDosis'];
                    }

                    $fechaUltimaDosis = null;
                    if(!is_null($row['fecha']))
                        $fechaUltimaDosis = fechas::getDateToINT($row['fecha']);

                    $fechaProximaDosis = null;
                    if(!is_null($row['proximavacuna']))
                        $fechaProximaDosis = fechas::getDateToINT($row['proximavacuna']);

                    $observaciones = null;
                    if(!is_null($row['docis']) || strlen($row['docis']) > 2)
                        $observaciones = $row['docis'];

                    $responseGetMascota = ctr_mascotas::getMascotaId($row['mascota'], $row['socio']);
                    if($responseGetMascota->result == 2){
                        $responseQuery = DataBase::sendQuery("INSERT INTO vacunasmascota(nombreVacuna, idMascota, intervaloDosis, numDosis, fechaPrimerDosis, fechaUltimaDosis, fechaProximaDosis, observacion) VALUES(?,?,?,?,?,?,?,?)", array('siiiiiis',$row['nombre'], $responseGetMascota->objectResult->idMascota, 360, $cantDosis, $fechaUltimaDosis, $fechaUltimaDosis, $fechaProximaDosis, $observaciones), "BOOLE");
                        if($responseQuery->result != 2)
                            return $responseQuery;
                    }
                }
            }
        }
    }

    public function getMascotasSinSocio(){
        $responseQueryGetMascota = migrateDB::sendQueryExternalDB("SELECT * FROM mascota WHERE duenio NOT IN (SELECT numero FROM socio)", null, "LIST");
        if($responseQueryGetMascota->result == 2){
            foreach ($responseQueryGetMascota->listResult as $key => $row) {
                if(!ctype_digit($row['nombre'])){
                    $estadoMascota = migrateDB::getEstadoMascota($row['estado']);

                    $fecha = fechas::getDateToINT($row['nacimiento']);

                    if(strlen($row['chip']) < 12)
                        $row['chip'] = null;

                    if(!ctype_alpha($row['color']) && strlen($row['color']) < 3)
                        $row['color'] = null;

                    if(!ctype_alpha($row['pelo']))
                        $row['pelo'] = null;

                    if(!ctype_alpha($row['especie']))
                        $row['especie'] = null;

                    if(!ctype_alpha($row['raza']))
                        $row['raza'] = null;

                    $sexo = 0;
                    if($row['sexo'] == "Macho")
                        $sexo = 1;

                    $pedigree = 0;
                    if($row['pedigree'] == "Si")
                        $pedigree = 1;

                    $observaciones = null;
                    $row['observaciones'] = migrateDB::clearEspecialCharacters($row['observaciones']);
                    if(strlen($row['observaciones']) > 3)
                        $observaciones = $row['observaciones'];

                    $responseQuery = DataBase::sendQuery("INSERT INTO mascotas (nombre, especie, raza, sexo, color, pedigree, fechaNacimiento, estado, pelo, chip, observaciones) VALUES(?,?,?,?,?,?,?,?,?,?,?)", array('sssissiisss', $row['nombre'], $row['especie'], $row['raza'], $sexo, $row['color'], $pedigree, $fecha, $estadoMascota, $row['pelo'], $row['chip'], $observaciones), "BOOLE");
                }
            }
        }
    }

    public function getMascotasSocio(){
        $responseQueryGetMascota = migrateDB::sendQueryExternalDB("SELECT * FROM mascota", null, "LIST");
        if($responseQueryGetMascota->result == 2){
            $arrayResult = array();
            foreach ($responseQueryGetMascota->listResult as $key => $row) {
                if(!ctype_digit($row['nombre'])){
                    $estadoMascota = migrateDB::getEstadoMascota($row['estado']);

                    $fecha = fechas::getDateToINT($row['nacimiento']);

                    if(strlen($row['chip']) < 12)
                        $row['chip'] = null;

                    if(!ctype_alpha($row['color']) || strlen($row['color']) < 4)
                        $row['color'] = null;

                    if(!ctype_alpha($row['pelo']))
                        $row['pelo'] = null;

                    if(!ctype_alpha($row['especie']))
                        $row['especie'] = null;

                    if(!ctype_alpha($row['raza']))
                        $row['raza'] = null;

                    $sexo = 0;
                    if($row['sexo'] == "Macho")
                        $sexo = 1;

                    $pedigree = 0;
                    if($row['pedigree'] == "Si")
                        $pedigree = 1;

                    $observaciones = null;
                    $row['observaciones'] = migrateDB::clearEspecialCharacters($row['observaciones']);
                    if(strlen($row['observaciones']) > 3)
                        $observaciones = $row['observaciones'];

                    $responseQuery = DataBase::sendQuery("INSERT INTO mascotas (nombre, especie, raza, sexo, color, pedigree, fechaNacimiento, estado, pelo, chip, observaciones) VALUES(?,?,?,?,?,?,?,?,?,?,?)", array('sssissiisss', $row['nombre'], $row['especie'], $row['raza'], $sexo, $row['color'], $pedigree, $fecha, $estadoMascota, $row['pelo'], $row['chip'], $observaciones), "BOOLE");
                    if($responseQuery->result == 2){
                        $responseQueryInsert = DataBase::sendQuery("INSERT INTO mascotasocio(idSocio, idMascota) VALUES (?,?)", array('ii', $row['duenio'], $responseQuery->id), "BOOLE");
                        if($responseQueryInsert->result == 2)
                            $arrayResult[] = array("idMascotaSocio"=> $responseQueryInsert->id, "idSocio"=> $row['duenio'], "nombre" => $row['nombre'], "idMascota" => $responseQuery->id);
                    }
                }
            }
            return $arrayResult;
        }
    }

    function getEstadoMascota($estado){
        if($estado == "Activa")
            return 1;
        else if ($estado == "Inactiva")
            return 0;
        else if($estado == "Pendiente")
            return 0;

        else return 0;
    }

    public function getSocios(){
        $responseQueryGetSocio = migrateDB::sendQueryExternalDB("SELECT * FROM socio", null, "LIST");
        if($responseQueryGetSocio->result == 2){
            $arraySocios = array();
            $fechaVencimiento = fechas::getYearMonthINT(4);
            foreach ($responseQueryGetSocio->listResult as $key => $row) {
                $tipoSocio = migrateDB::getTipoSocio($row['estado']);

                $fechaUltimoPago = migrateDB::getFechaInt($row['ultimopago']);

                $fechaUltimaCuota = null;
                if(strlen($row['ultimacuota']) > 10)
                    $fechaUltimaCuota = migrateDB::getMonthYearInt($row['ultimacuota']);

                //fechaUltimaCuota tiene formato yyyymm
                //if ( $fechaUltimaCuota > date("Ym") )
                if ( $fechaUltimaCuota > date( "Ym", strtotime( date("Ym")." +12 month" ) ) )
                    $fechaUltimaCuota = null;

                $fechaIngreso = null;
                if(strlen($row['fechaingreo']) > 9)
                    $fechaIngreso = fechas::getDateToINT($row['fechaingreo']);

                /*$estado = 1;
                if($fechaVencimiento >= $fechaUltimaCuota)
                    $estado = 0;*/

                $estado = 1;
                if(strcmp($row['estado'], "Inactivo") == 0)
                    $estado = 0;

                $cedula = null;
                if(!is_null($row['cedula'])){
                    $cedula = migrateDB::getNumericPart($row['cedula']);
                    $cedula = migrateDB::clearEspecialCharacters($cedula);
                    if(strlen($cedula) < 7 || strlen($cedula) > 8 || strcmp($cedula,"00000000") == 0)
                        $cedula = null;
                }

                $telefono = null;
                if(!is_null($row['telefono'])){
                    $telefono = migrateDB::getNumericPart($row['telefono']);
                    $telefono = migrateDB::clearEspecialCharacters($telefono);
                    if(strlen($telefono) < 5 || strlen($telefono) > 9)
                        $telefono = null;
                }

                $lugarPago = 1;
                if(strcmp($row['lugarpago'], "Veterinaria") == 0)
                    $lugarPago = 0;

                $fechaPago = null;
                if(!is_null($row['fechapago']) && strlen($row['fechapago']) != 0)
                    $fechaPago = migrateDB::getNumericPart($row['fechapago']);

                $motivoBaja = null;
                if(!is_null($motivoBaja) && strlen($row['motivobaja']) > 4)
                    $motivoBaja = $row['motivobaja'];

                $email = null;
                if(filter_var($row['email'], FILTER_VALIDATE_EMAIL))
                    $email = $row['email'];

                $rut = null;
                if(!is_null($row['rut'])){
                    $responseValidateRut = validate::validateRut($row['rut']);
                    if($responseValidateRut->result == 2)
                        $rut = $row['rut'];
                }

                $telefax = null;
                if(!is_null($row['telfax']) & strlen($row['telfax']) > 5)
                    $telefax = $row['telfax'];

                $responseQuery = DataBase::sendQuery("INSERT INTO socios(idSocio, cedula, nombre, telefono, telefax, direccion, fechaIngreso, fechaPago, lugarPago, estado, motivoBaja, cuota, email, rut, fechaUltimoPago, fechaUltimaCuota, tipo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", array('isssssiisisissiii', $row['numero'], $cedula, $row['nombre'], $telefono, $telefax, $row['calle'] . " " . $row['numerocasa'] ." ". $row['apto'], $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $row['cuota'], $email, $rut, $fechaUltimoPago, $fechaUltimaCuota, $tipoSocio), "BOOLE");
            }
            return $arraySocios;
        }
    }

    public function getTipoSocio($tipo){
        if($tipo == "No Socio")
            return 0;
        else if($tipo == "Sin Mascota")
            return 0;
        else if($tipo == "Activo")
            return 1;
        else if($tipo == "Honorario")
            return 2;

        return 0;
    }

    public function getFechaInt($value){
        $value = str_replace(" ", "", $value);
        $arrayDate = explode("/", $value);
        if(sizeof($arrayDate) == 3){
            $day = null;
            if(strlen($arrayDate[0]) == 1)
                $day = "0" . $arrayDate[0];
            else
                $day = $arrayDate[0];


            $month = null;
            if(strlen($arrayDate[1]) == 1)
                $month = "0" . $arrayDate[1];
            else
                $month = $arrayDate[1];


            $year = $arrayDate[2];
            if(strcmp($day, "00") == 0 || strcmp($month, "00") == 0 || strcmp($year, "0000") == 0)
                return null;

            $newDate = $year . $month . $day;
            if(strlen($newDate) == 8)
                return $newDate;
        }

        return null;
    }

    public function getNumericPart($value){
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public function clearEspecialCharacters($value){
        return preg_replace('([^A-Za-z0-9])', '', $value);
    }

    public function sendQueryExternalDB($sql, $params, $tipoRetorno){
        $response = new \stdClass();

        //$connection = new mysqli("127.0.0.1", "root", "", "veterinaria");
        $connection = new mysqli("127.0.0.1", DB_USR, DB_PASS, "veterinaria");
        $connection->set_charset("utf8");
        if($connection){
            $query = $connection->prepare($sql);

            $paramsTemp = array();
            if($params){
                foreach($params as $key => $value)
                    $paramsTemp[$key] = &$params[$key];

                call_user_func_array(array($query, 'bind_param'), $paramsTemp);
            }

            if($query->execute()){
                $result = $query->get_result();

                if($tipoRetorno == "LIST"){
                    $arrayResult = array();
                    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $arrayResult[] = $row;
                    }
                    if(sizeof($arrayResult) > 0){
                        $response->result = 2;
                        $response->listResult = $arrayResult;
                    }else $response->result = 1;
                }else if($tipoRetorno == "OBJECT") {
                    $objectResult = $result->fetch_object();
                    if(!is_null($objectResult)){
                        $response->result = 2;
                        $response->objectResult = $objectResult;
                    }else $response->result = 1;
                }else if($tipoRetorno == "BOOLE"){
                    $response->result = 2;
                    $response->id = $connection->insert_id;
                }
            }else{
                $response->result = 0;
                if(strpos($query->error, "Duplicate") !== false){
                    $msjError = $query->error;
                    $msjError = str_replace("Duplicate entry", "BASE DE DATOS: El valor ", $msjError);
                    $msjError = str_replace(" for key", " ya fue ingresado previamente para el campo ", $msjError);
                    $response->message = $msjError . "(dato único)";
                }else if(strpos($query->error, "Column") !== false){
                    $msjError = $query->error;
                    $msjError = str_replace("Column", "BASE DE DATOS: La columna", $msjError);
                    $msjError = str_replace("cannot be", "no puede ser", $msjError);
                    $response->message = $msjError;
                }else{
                    $response->message = "BASE DE DATOS: " . $query->error;
                }
            }
        }else{
            $response->result = 0;
            $response->message = "Ocurrió un error y no se pudo acceder a la base de datos del sistema.";
        }
        return $response;
    }

    public function validarCedula($ci){
        $ciLimpia = preg_replace( '/\D/', '', $ci );
        $validationDigit = $ciLimpia[-1];
        $ciLimpia = preg_replace('/[0-9]$/', '', $ciLimpia );
        return $validationDigit == migrateDB::validarDigitoVerificador($ci);
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
    function getEstado($estado){

        if($estado == "Inactivo")
            return 0;

        return 1;
    }

    function getMonthYearInt($fecha){

        $fechaArray = explode(" ", $fecha);
        $mes = "";
        if($fechaArray[0] == 'Enero')
            $mes = "01";
        else if($fechaArray[0] == 'Febrero')
            $mes = "02";
        else if($fechaArray[0] == 'Marzo')
            $mes = "03";
        else if($fechaArray[0] == 'Abril')
            $mes = "04";
        else if($fechaArray[0] == 'Mayo')
            $mes = "05";
        else if($fechaArray[0] == 'Junio')
            $mes = "06";
        else if($fechaArray[0] == 'Julio')
            $mes = "07";
        else if($fechaArray[0] == 'Agosto')
            $mes = "08";
        else if($fechaArray[0] == 'Septiembre')
            $mes = "09";
        else if($fechaArray[0] == 'Octubre')
            $mes = "10";
        else if($fechaArray[0] == 'Noviembre')
            $mes = "11";
        else if($fechaArray[0] == 'Diciembre')
            $mes = "12";
        else return null;

        if(sizeof($fechaArray))
            return $fechaArray[2] . $mes;
        else return null;
    }
}