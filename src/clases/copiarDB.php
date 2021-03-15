<?php

require_once 'fechas.php';
require_once '../src/conexion/abrir_conexion.php';

class copiarDB{

    function getConexion(){
        $conexion = new mysqli("127.0.0.1", "root", "", "veterinaria");
        $conexion->set_charset("utf8");
        return $conexion;
    }

    function getSociosOriginal(){
        $conexion = copiarDB::getConexion();
        $sql = $conexion->prepare("SELECT * FROM socio LIMIT 25");
        if($sql->execute()){
            $response = $sql->get_result();

            while($row = $response->fetch_array(MYSQLI_ASSOC)){
                $estado = copiarDB::getEstado($row['estado']);
                $fechaUltimoP = fechas::parceFechaInt($row['ultimopago']);
                $fechaUltimaC = copiarDB::calcularUltimaCuota($row['ultimacuota']);
                $fechaIngreso = fechas::parceFechaInt($row['fechaingreo']);
                $cedula = copiarDB::extructurarCedula($row['cedula']);

                $idSocio = copiarDB::insertarSociosNuevo($cedula, $row['nombre'], $row['numero'], $row['telefono'], $row['telfax'], $row['calle'] . " " . $row['numerocasa'] ." ". $row['apto'], $fechaIngreso , 0, $row['lugarpago'], $estado, $row['motivobaja'], $row['cuota'], $row['email'], null, $fechaUltimoP, $fechaUltimaC);

                if($row['cantidadMascotas'] > 0){
                   copiarDB::ingresarMascotasSocio($row['numero'], $idSocio);
               }
           }
       }
   }

   function getEstado($estado){

    if($estado == "Activo")
        return 1;
    else if($estado == "Inactivo")
        return 0;
    else if ($estado == "No Socio")
        return 2;
    else if ($estado == "Sin Mascota")
        return 3;
    else if($estado == "Honorario")
        return 4;
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

    if(sizeof($fechaArray))
        return $fechaArray[2] . $mes;
    else return null;
}

function extructurarCedula($cedula){
    if(copiarDB::validarCedula($cedula)){

        $cedulaValidada =  substr($cedula,0,1) . substr($cedula,2,3) . substr($cedula,6,3) . substr($cedula,10,1);
        if($cedulaValidada < 9999999)
            return null;
        else return $cedulaValidada;
    }else return null;
}

function ingresarMascotasSocio($numSocio, $idSocio){
    $conexion = copiarDB::getConexion();
    $sql = $conexion->prepare("SELECT * FROM mascota WHERE duenio = ? LIMIT 25");
    $sql->bind_param('i', $numSocio);
    if($sql->execute()){

        $response = $sql->get_result();
        while($row = $response->fetch_array(MYSQLI_ASSOC)){
            $estado = copiarDB::getEstadoMascota($row['estado']);
            $nombre = $row['nombre'];
            $nombreSolo = explode(' ', $nombre);
            if($nombreSolo[0] == null)
                $nombreSolo = $row['nombre'];
            else
                $nombreSolo = $nombreSolo[0];
            $fecha = fechas::parceFechaInt($row['nacimiento']);

            if(strlen($row['chip']) < 12)
                $row['chip'] = null;

            if(!ctype_alpha($row['color']) || strlen($row['color']) < 4)
                $row['color'] = null;

            if(!ctype_alpha($row['pelo']))
                $row['pelo'] = null;

            $sexo = 0;
            if($row['sexo'] == "Macho")
                $sexo = 1;

            $idMascota = copiarDB::insertarSocioMascota($nombreSolo, $row['especie'], $row['raza'], $sexo, $row['color'], $row['pedigree'], $fecha, $estado, $row['pelo'], $row['chip']);
            copiarDB::insertarHistorialRelacion($idMascota, $idSocio, $nombreSolo, $row['duenio']);
        }
    }
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

function insertarSocioMascota($nombre, $especie, $raza, $sexo, $color, $pedigree, $fecha, $estado, $pelo, $chip){
    $conn = DB::conexion();
    $sql = $conn->prepare("INSERT INTO mascotas (nombre, especie, raza, sexo, color, pedigree, fechaNacimiento, estado, pelo, chip) VALUES(?,?,?,?,?,?,?,?,?,?)");
    $sql->bind_param('sssissiiss',$nombre, $especie, $raza, $sexo, $color, $pedigree, $fecha, $estado, $pelo, $chip);
    if($sql->execute()){
        return $conn->insert_id;
    }return null;
}


function insertarSociosNuevo($cedula, $nombre, $numSocio, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUltimoPago, $fechaUltimaCouta){
    $conn = DB::conexion();

    $sql = $conn->prepare("INSERT INTO `socios`(`cedula`, `nombre`, `numSocio`, `telefono`, `telefax`, `direccion`, `fechaIngreso`, `fechaPago`, `lugarPago`, `estado`, `motivoBaja`, `cuota`, `email`, `rut`, `fechaUltimoPago`, `fechaUltimaCuota`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $sql->bind_param('ssisssiisisissii',$cedula, $nombre, $numSocio, $telefono, $telefax, $direccion, $fechaIngreso, $fechaPago, $lugarPago, $estado, $motivoBaja, $cuota, $email, $rut, $fechaUltimoPago, $fechaUltimaCouta );

    if($sql->execute()){
        return $conn->insert_id;
    }else return null;
}

function vincularSocioMascota($idSocio, $idMascota, $fecha){
    $conn = DB::conexion();
    $sql = $conn->prepare("INSERT INTO mascotasocio(idMascota, idSocio, fechaCambio) VALUES(?,?,?)");
    $sql->bind_param('iii', $idMascota, $idSocio, $fecha);

    if($sql->execute()){
        return $conn->insert_id;
    }else return null;
}

function insertarHistorialRelacion($idMascota, $idSocio, $nombreMascota, $duenioMascota){
    $conexion = copiarDB::getConexion();
    $sql = $conexion->prepare("SELECT fechaCambio FROM historial_mascota WHERE nombre = ? AND duenio = ?");
    $sql->bind_param('si', $nombreMascota, $duenioMascota);
    if($sql->execute()){
        $response = $sql->get_result();
        while($row = $response->fetch_array(MYSQLI_ASSOC)){
            $fechaFormateada = fechas::parceFechaInt($row['fechaCambio']);
            $idRelacion = copiarDB::vincularSocioMascota($idSocio, $idMascota, $fechaFormateada);
            if($idRelacion != null){
                copiarDB::getHistoriaClinica($idMascota, $nombreMascota, $duenioMascota);
            }
        }
    }
}

public function getHistoriaClinica($idMascota, $nombreMascota, $duenioMascota){
    $conexion = copiarDB::getConexion();
    $sql = $conexion->prepare("SELECT * FROM `historial_clinico` WHERE socio = ? AND mascota = ?");
    $sql->bind_param('is', $duenioMascota, $nombreMascota);
    if($sql->execute()){
        $response = $sql->get_result();
        while($row = $response->fetch_array(MYSQLI_ASSOC)){
            $fecha = fechas::parceFechaInt($row['fecha']);
            copiarDB::insertarHistoriaClinica($idMascota, $fecha, $row['motivoconsulta'], $row['diagnostico'], $row['descripcion']);
        }
    }
}

public function insertarHistoriaClinica($idRelacion, $fecha, $motivoConsulta, $diagnostico, $observaciones){
    $sql = DB::conexion()->prepare("INSERT INTO `hisotiralclinico`(idMascota, fecha, motivoConsulta, diagnostico, observaciones) VALUES (?,?,?,?,?)");
    $sql->bind_param('iisss', $idRelacion, $fecha,$observaciones, $motivoConsulta, $diagnostico);
    $sql->execute();
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
}