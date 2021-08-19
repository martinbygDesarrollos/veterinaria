<?php

require_once "../src/utils/fechas.php";
require_once '../src/connection/open_connection.php';

class copiarDB{

    function getConexion(){
        $conexion = new mysqli("127.0.0.1", "root", "", "veterinaria");
        $conexion->set_charset("utf8");
        return $conexion;
    }

    public function seleccionarInsertarSocios(){

        $conexion = copiarDB::getConexion();
        $sql = $conexion->prepare("SELECT * FROM socio");
        if($sql->execute()){
            $response = $sql->get_result();
            $array = array();

            $fechaInt = fechas::parceFechaInt(fechas::calcularFechaMinimaDeuda(date('Y-m-d'), 1500));
            $fechaInt = substr($fechaInt, 0,4) . substr($fechaInt, 4,2);

            while($row = $response->fetch_array(MYSQLI_ASSOC)){

                $tipoSocio = copiarDB::getTipoSocio($row['estado']);

                $fechaUltimoP = null;
                if(strlen($row['ultimopago']) == 8)
                    $fechaUltimoP = fechas::parceFechaInt($row['ultimopago']);
                $fechaUltimaC = null;
                if(strlen($row['ultimacuota']) > 10)
                    $fechaUltimaC = copiarDB::calcularUltimaCuota($row['ultimacuota']);
                $fechaIngreso = null;
                if(strlen($row['fechaingreo']) > 9)
                    $fechaIngreso = fechas::parceFechaInt($row['fechaingreo']);



                // $estado = copiarDB::getEstado($row['estado']);
                $estado = 1;
                if($fechaInt > $fechaUltimaC)
                    $estado = 0;

                $cedula = null;
                if(strlen($row['cedula']) > 8 || strlen($row['cedula']) < 12)
                    $cedula = copiarDB::extructurarCedula($row['cedula']);

                $responseQuery = copiarDB::insertarSocio($cedula, $row['nombre'], $row['numero'], $row['telefono'], $row['telfax'], $row['calle'] . " " . $row['numerocasa'] ." ". $row['apto'], $fechaIngreso , 0, $row['lugarpago'], $estado, $row['motivobaja'], $row['cuota'], $row['email'], null, $fechaUltimoP, $fechaUltimaC, $tipoSocio);
                if($responseQuery->result == 2)
                    $array[] = array("idSocio" => $responseQuery->id, "numSocio" => $row['numero']);
            }
            return $array;
        }
    }

    public function getTipoSocio($tipo){
        if($tipo == "No Socio") return 0;
        else if($tipo == "Sin Mascota") return 0;
        else if($tipo == "Activo")return 1;
        else if($tipo == "Honorario") return 2;
        return 0;
    }

    public function insertarSocio($cedula, $nombre, $numSocio, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUltimoPago, $fechaUltimaCouta, $tipoSocio){

        return DataBase::sendQuery("INSERT INTO socios(cedula, nombre, numSocio, telefono, telefax, direccion, fechaIngreso, fechaPago, lugarPago, estado, motivoBaja, cuota, email, rut, fechaUltimoPago, fechaUltimaCuota, tipo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", array('ssisssiisisissiii',$cedula, $nombre, $numSocio, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUltimoPago, $fechaUltimaCouta, $tipoSocio), "BOOLE");
    }

    function extructurarCedula($cedula){
        $cedula = preg_replace('([^A-Za-z0-9])', '', $cedula);
        if(copiarDB::validarCedula($cedula)){
            // $cedulaValidada =  substr($cedula,0,1) . substr($cedula,2,3) . substr($cedula,6,3) . substr($cedula,10,1);
            if($cedula > 9999999 && strlen($cedula) <= 8)
                return $cedula;
        }
        return null;
    }

    function seleccionarInsertarMascota($idSocio, $numSocio){
        $conexion = copiarDB::getConexion();
        $sql = $conexion->prepare("SELECT * FROM mascota WHERE duenio = ?");
        $sql->bind_param('i', $numSocio);
        if($sql->execute()){
            $response = $sql->get_result();
            while($row = $response->fetch_array(MYSQLI_ASSOC)){
                $estado = copiarDB::getEstadoMascota($row['estado']);

                $fecha = fechas::parceFechaInt($row['nacimiento']);

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

                $idMascota = copiarDB::insertarMascota($row['nombre'], $row['especie'], $row['raza'], $sexo, $row['color'], $pedigree, $fecha, $estado, $row['pelo'], $row['chip']);

                copiarDB::insertarMascotaSocio($idSocio, $idMascota);
            }
        }
    }

    public function seleccionarInsertarMascotaSinSocio(){
        $conexion = copiarDB::getConexion();
        $query = $conexion->prepare("SELECT * FROM mascota WHERE duenio NOT IN (SELECT numero FROM socio)");
        if($query->execute()){
            $result = $query->get_result();
            $arrayResult = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $estado = copiarDB::getEstadoMascota($row['estado']);

                $fecha = fechas::parceFechaInt($row['nacimiento']);

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

                $responseQuery = copiarDB::insertarMascota($row['nombre'], $row['especie'], $row['raza'], $sexo, $row['color'], $pedigree, $fecha, $estado, $row['pelo'], $row['chip']);

                // copiarDB::seleccionarInsertarVacunasMascotas($idMascota, $row['nombre'], $row['duenio']);
                // copiarDB::seleccionarInsertarEnfermedadesMascota($idMascota, $row['nombre'], $row['duenio']);
                // copiarDB::seleccionarInsertarHistorialClinico($idMascota, $row['nombre'], $row['duenio']);
            }
        }
    }

    public function insertarMascota($nombre, $especie, $raza, $sexo, $color, $pedigree, $fecha, $estado, $pelo, $chip){
        return DataBase::sendQuery("INSERT INTO mascotas (nombre, especie, raza, sexo, color, pedigree, fechaNacimiento, estado, pelo, chip) VALUES(?,?,?,?,?,?,?,?,?,?)", array('sssissiiss',$nombre, $especie, $raza, $sexo, $color, $pedigree, $fecha, $estado, $pelo, $chip), "BOOLE");
    }

    public function insertarMascotaSocio($idSocio, $idMascota){
        return DataBase::sendQuery("INSERT INTO mascotasocio(idSocio, idMascota) VALUES (?,?)", array('ii', $idSocio, $idMascota), "BOOLE");
    }

    function seleccionarInsertarVacunasMascotas($idMascota, $nombre, $duenio){

        $conexion = copiarDB::getConexion();
        $sql = $conexion->prepare("SELECT * FROM vacuna_asignada WHERE mascota = ? AND socio = ?");
        $sql->bind_param('si',$nombre, $duenio);
        if($sql->execute()){
            $result = $sql->get_result();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $fechaPParce = fechas::parceFechaInt($row['fecha']);
                $fechaUParce = fechas::parceFechaInt($row['proximavacuna']);
                copiarDB::insertarVacuna($row['nombre'], $idMascota, "1", "1", $fechaPParce, $fechaUParce, $row['docis']);
            }
        }
    }

    function insertarVacuna($nombreVacuna, $idMascota, $intervaloDosis, $numDosis, $fechaPrimerDosis, $fechaUltimaDosis, $observacion){
        return DataBase::sendQuery("INSERT INTO vacunasmascota(nombreVacuna, idMascota, intervaloDosis, numDosis, fechaPrimerDosis, fechaUltimaDosis, observacion) VALUES(?,?,?,?,?,?,?)", array('siiiiis',$nombreVacuna, $idMascota, $intervaloDosis, $numDosis, $fechaPrimerDosis, $fechaUltimaDosis, $observacion), "BOOLE");
    }

    function seleccionarInsertarEnfermedadesMascota($idMascota, $nombre, $duenio){
        $conexion = copiarDB::getConexion();
        $query = $conexion->prepare("SELECT * FROM enfermedades WHERE socio = ? AND mascota = ?");
        $query->bind_param('is', $duenio, $nombre);
        if($query->execute()){
            $result = $query->get_result();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                copiarDB::insertEnfermedadMascota($idMascota, $row['enfermedad'], fechas::parceFechaInt($row['anio'] . "-" . $row['mes']) . "-01");
            }
        }
    }

    function insertEnfermedadMascota($idMascota, $enfermedad, $fecha){
        return DataBase::sendQuery("INSERT INTO enfermedadesmascota(idMascota, fechaDiagnostico, nombreEnfermedad) VALUES (?,?,?)", array('iis',$idMascota, $fecha, $enfermedad), "BOOLE");
    }

    public function seleccionarInsertarHistorialClinico($idMascota, $nombre, $duenio){
        $conexion = copiarDB::getConexion();
        $sql = $conexion->prepare("SELECT * FROM historial_clinico WHERE socio = ? AND mascota = ?");
        $sql->bind_param('is', $duenio, $nombre);
        if($sql->execute()){
            $response = $sql->get_result();
            while($row = $response->fetch_array(MYSQLI_ASSOC)){
                $fecha = fechas::parceFechaInt($row['fecha']);
                copiarDB::insertarHistoriaClinica($idMascota, $fecha, $row['motivoconsulta'], $row['diagnostico'], $row['descripcion']);
            }
        }
    }

    public function insertarHistoriaClinica($idRelacion, $fecha, $motivoConsulta, $diagnostico, $observaciones){
        $sql = DB::conexion()->prepare("INSERT INTO historiasclinica(idMascota, fecha, motivoConsulta, diagnostico, observaciones) VALUES (?,?,?,?,?)");
        $sql->bind_param('iisss', $idRelacion, $fecha, $observaciones, $motivoConsulta, $diagnostico);
        $sql->execute();
    }

    public function seleccionarInsertarFechaDeCambio($nombre, $duenio, $idMascotaSocio){
        $conexion = copiarDB::getConexion();
        $query = $conexion->prepare("SELECT fechacambio FROM historial_mascota WHERE nombre = ? AND duenio = ?");
        $query->bind_param('ss', $nombre, $duenio);
        if($query->execute()){
            $result = $query->get_result();
            $arrayResult = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $row['fechacambio'] = fechas::parceFechaInt($row['fechacambio']);
                copiarDB::actualizarMascotaSocio($idMascotaSocio, $row['fechacambio']);
            }
        }
    }

    public function actualizarMascotaSocio($idMascotaSocio, $fechacambio){
        $query = DB::conexion()->prepare("UPDATE mascotasocio SET fechaCambio= ? WHERE idMascotaSocio = ?");
        $query->bind_param('ii', $fechacambio, $idMascotaSocio);
        return  $query->execute();
    }

    function getEstadoMascota($estado){
        if($estado == "Activa")
            return 1;
        else if ($estado == "Inactiva")
            return 0;
        else if($estado == "Pendiente")
            return 2;
        else return 0;
    }

    public function validarCedula($ci){
        $ciLimpia = preg_replace( '/\D/', '', $ci );
        $validationDigit = $ciLimpia[-1];
        $ciLimpia = preg_replace('/[0-9]$/', '', $ciLimpia );
        return $validationDigit == copiarDB::validarDigitoVerificador($ci);
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

    function calcularUltimaCuota($fecha){

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