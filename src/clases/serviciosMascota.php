<?php

require_once 'fechas.php';
require_once '../src/controladores/ctr_usuarios.php';

class serviciosMascota {

    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------ANALISIS MASCOTA ---------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------

    public function getAnalisisMascota($idMascota){
        $query =DB::conexion()->prepare("SELECT fecha, nombre FROM analisismascota WHERE idMascota = ?");
        $query->bind_param('i', $idMascota);
        if($query->execute()){
            $result = $query->get_result();
            $arrayResponse = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $row['fecha'] = fechas::parceFechaFormatDMA($row['fecha'], '/');
                $arrayResponse[] = $row;
            }
            return $arrayResponse;
        }else return null;
    }

    public function insertNewAnalisis($idMascota, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis){
        $query = DB::conexion()->prepare("INSERT INTO analisismascota(idMascota, nombre, fecha, detalle, resultado) VALUES(?,?,?,?,?)");
        $query->bind_param('isiss', $idMascota, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis);
        return $query->execute();
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //-------------------------------------------------------VACUNAS MASCOTA ---------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------

    public function getVacunasMascotas(){
        $query = DB::conexion()->prepare("SELECT * FROM vacunasmascota, mascotas WHERE mascotas.idMascota = vacunasmascota.idMascota");
        if($query->execute()){
            $response = $query->get_result();
            $arrayResponse = array();
            while($row = $response->fetch_array(MYSQLI_ASSOC)){
                $row['fechaProximaDosis'] = fechas::parceFechaFormatDMA($row['fechaProximaDosis'], "/");
                $row['fechaUltimaDosis'] = fechas::parceFechaFormatDMA($row['fechaUltimaDosis'], "/");
                $row['fechaPrimerDosis'] = fechas::parceFechaFormatDMA($row['fechaPrimerDosis'], "/");

                $arrayResponse[] = $row;
            }
            return $arrayResponse;
        }else return null;
    }

    public function getVacunasVencidas($fechaLimite){
        $query = DB::conexion()->prepare("SELECT VM.fechaProximaDosis, VM.idMascota, VM.nombreVacuna, M.nombre AS nombreMascota
            FROM vacunasmascota AS VM, mascotas AS M WHERE M.idMascota = VM.idMascota AND VM.idVacunaMascota IN (SELECT idVacunaMascota FROM `vacunasmascota` WHERE fechaProximaDosis <= ? AND fechaProximaDosis != 0)");
        $query->bind_param('i', $fechaLimite);
        if($query->execute()){
            $response = $query->get_result();
            $arrayResponse = array();
            $fechaActual = date('Y-m-d');
            while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
                if($row['fechaProximaDosis'] != 0){
                    if($fechaActual > $row['fechaProximaDosis']){
                        $row['vencida'] = 1;
                    }else if(fechas::obtenerDiferenciaDias($row['fechaProximaDosis'], date('Y-m-d')) == 0){
                        $row['vencida'] = 1;
                    }else{
                        $row['vencida'] = 0;
                    }
                    $row['fechaProximaDosis'] = fechas::parceFechaFormatDMA($row['fechaProximaDosis'],"/");
                }

                $row['socio'] = ctr_usuarios::getSocioMascota($row['idMascota']);

                $arrayResponse[] = $row;
            }
            return $arrayResponse;
        }else return null;
    }

    public function getVacunasVencidasMascota($idMascota, $fechaActual){
        $query = DB::conexion()->prepare("SELECT nombreVacuna, fechaProximaDosis FROM `vacunasmascota` WHERE idMascota = ? AND fechaProximaDosis != 0 AND fechaProximaDosis <= ?");
        $query->bind_param('ii', $idMascota, $fechaActual);
        if($query->execute()){
            $response = $query->get_result();
            $arrayResponse = array();
            while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
               $row['fechaProximaDosis'] = fechas::parceFechaFormatDMA($row['fechaProximaDosis'],"/");
               $arrayResponse[] = $row;
           }
           return $arrayResponse;
       }else return null;
   }

   public function getVacunaMascota($idVacunaMascota){
    $query = DB::conexion()->prepare("SELECT * FROM vacunasmascota WHERE idVacunaMascota = ?");
    $query->bind_param('i', $idVacunaMascota);
    if($query->execute()){
        $response = $query->get_result();
        return $response->fetch_object();
    }else return null;
}

public function insertVacunaMascota($nombre, $idMascota, $intervaloDosis, $numDosis, $fechaPrimerDosis, $fechaUltimaDosis,$fechaProximaDosis, $observaciones){
    $query = DB::conexion()->prepare("INSERT INTO vacunasmascota (nombreVacuna, idMascota, intervaloDosis, numDosis, fechaPrimerDosis, fechaUltimaDosis, fechaProximaDosis, observacion) VALUES (?,?,?,?,?,?,?,?)");
    $query->bind_param('siiiiiis', $nombre, $idMascota, $intervaloDosis, $numDosis, $fechaPrimerDosis, $fechaUltimaDosis,$fechaProximaDosis, $observaciones);
    return $query->execute();
}

public function getVacunaMascotaID($idMascota){
    $query = DB::conexion()->prepare("SELECT * FROM vacunasmascota WHERE idMascota = ?");
    $query->bind_param('i', $idMascota);
    if($query->execute()){
        $response = $query->get_result();
        $arrayResponse = array();
        $fechaActual = fechas::parceFechaInt(date('Y-m-d'));
        while ($row = $response->fetch_array(MYSQLI_ASSOC)) {

            $row['fechaPrimerDosis'] = fechas::parceFechaFormatDMA($row['fechaPrimerDosis'], "");
            $row['fechaUltimaDosis'] = fechas::parceFechaFormatDMA($row['fechaUltimaDosis'], "/");

            if($row['fechaProximaDosis'] != 0){
                if($fechaActual > $row['fechaProximaDosis']){
                    $row['vencida'] = 1;
                }else if(fechas::obtenerDiferenciaDias($row['fechaProximaDosis'], date('Y-m-d')) < 5){
                    $row['vencida'] = 1;
                }else{
                    $row['vencida'] = 0;
                }
                $row['fechaProximaDosis'] = fechas::parceFechaFormatDMA($row['fechaProximaDosis'], "/");
            }

            $arrayResponse[] = $row;
        }
        return $arrayResponse;
    }else return null;
}

public function aplicarDosisVacunaMascota($idVacunaMascota, $nuevaUltimaDosis, $nuevaNumDosis, $fechaProximaDosis){
    $query = DB::conexion()->prepare("UPDATE vacunasmascota SET numDosis = ? , fechaUltimaDosis = ?, fechaProximaDosis = ? WHERE idVacunaMascota  = ?");
    $query->bind_param('iiii', $nuevaNumDosis, $nuevaUltimaDosis, $fechaProximaDosis, $idVacunaMascota);
    return $query->execute();
}

    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------ENFERMEDADES------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------

public function insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnostico, $observaciones){
    $query = DB::conexion()->prepare("INSERT INTO enfermedadesmascota (idMascota, fechaDiagnostico, nombreEnfermedad, observaciones) VALUES (?,?,?,?)");
    $query->bind_param('iiss', $idMascota, $fechaDiagnostico, $nombre, $observaciones);
    return $query->execute();
}

public function updateEnfermedadMascota($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones){
    $query = DB::conexion()->prepare("UPDATE enfermedadesmascota SET fechaDiagnostico = ?, nombreEnfermedad = ?, observaciones = ? WHERE idEnfermedad = ?");
    $query->bind_param('issi', $fechaDiagnostico, $nombre, $observaciones, $idEnfermedad);
    return $query->execute();
}

public function getEnfermedades($idMascota){
    $query = DB::conexion()->prepare("SELECT * FROM enfermedadesmascota WHERE idMascota = ?");
    $query->bind_param('i', $idMascota);
    if($query->execute()){
        $response = $query->get_result();
        $arrayResponse = array();
        while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
            $row['fechaDiagnostico'] = fechas::parceFechaFormatDMA($row['fechaDiagnostico'],"/");
            $arrayResponse[] = $row;
        }
        return $arrayResponse;
    }else return null;
}

public function getEnfermedadMascota($idEnfermedad){
    $query = DB::conexion()->prepare("SELECT * FROM enfermedadesmascota WHERE idEnfermedad  = ?");
    $query->bind_param('i', $idEnfermedad);
    if($query->execute()){
        $response = $query->get_result();
        $response = $response->fetch_object();
        $response->fechaDiagnostico = fechas::parceFechaFormatDMA($response->fechaDiagnostico, "/");
        return $response;
    }else return null;
}
}