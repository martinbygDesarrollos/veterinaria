<?php

require_once "../src/utils/fechas.php";

class serviciosMascota {

    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------ANALISIS MASCOTA ---------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------

    public function insertAnalisis($idMascota, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis){
        return DataBase::sendQuery("INSERT INTO analisismascota(idMascota, nombre, fecha, detalle, resultado) VALUES(?,?,?,?,?)", array('isiss', $idMascota, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis), "BOOLE");
    }

    public function updateAnalisisMascota($idAnalisis, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis){
        return DataBase::sendQuery("UPDATE analisismascota SET nombre= ?, fecha = ?, detalle= ?, resultado = ? WHERE idAnalisis = ?", array('sissi', $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis, $idAnalisis), "BOOLE");
    }

    public function deleteAnalisis($idAnalisis){
        return DataBase::sendQuery("DELETE FROM analisismascota WHERE idAnalisis = ?", array('i', $idAnalisis), "BOOLE");
    }

    public function getAnalisisToShow($idAnalisis){
        $responseQuery = serviciosMascota::getAnalisis($idAnalisis);
        if($responseQuery->result == 2){

            $responseQueryFiles = DataBase::sendQuery("SELECT idMedia, nombre FROM media WHERE categoria = ? AND idCategoria = ?", array('si', "analisismascota", $idAnalisis), "LIST");
            if ( $responseQueryFiles->result == 2 ){
                $responseQuery->objectResult->archivos = $responseQueryFiles->listResult;
            }else $responseQuery->objectResult->archivos = null;



            $analisis = $responseQuery->objectResult;
            $noData = "";
            if(is_null($analisis->detalle) || strlen($analisis->detalle) < 2)
                $analisis->detalle = $noData;

            if(is_null($analisis->nombre) || strlen($analisis->nombre) < 2)
                $analisis->nombre = $noData;

            if(is_null($analisis->resultado) || strlen($analisis->resultado) < 2)
                $analisis->resultado = $noData;

            if(!is_null($analisis->fecha) && strlen($analisis->fecha) == 10)
                $analisis->fecha = fechas::dateToFormatBar(fechas::getDateToINT($analisis->fecha));
            else
                $analisis->fecha = $noData;

            $responseQuery->objectResult = $analisis;
        }

        return $responseQuery;
    }

    public function getAnalisis($idAnalisis){
        $responseQuery = DataBase::sendQuery("SELECT * FROM analisismascota WHERE idAnalisis = ?", array('i',$idAnalisis), "OBJECT");
        if($responseQuery->result == 2){
            $responseQuery->objectResult->fecha = fechas::dateToFormatHTML($responseQuery->objectResult->fecha);
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontro un analisis con el identificador seleccionado.";
        return $responseQuery;
    }

    public function getAnalisisMascota($idMascota){
        $responseQuery = DataBase::sendQuery("SELECT * FROM analisismascota WHERE idMascota = ? ORDER BY idAnalisis DESC LIMIT 14", array('i', $idMascota), "LIST");
        if($responseQuery->result == 2){
            $noData = "";
            $arrayResult = array();
            foreach ($responseQuery->listResult as $key => $row) {

                if(!is_null($row['fecha']) && strlen($row['fecha']) == 8)
                    $row['fecha'] = fechas::dateToFormatBar($row['fecha']);
                else $row['fecha'] = $noData;

                if(is_null($row['nombre']))
                    $row['nombre'] = $noData;

                if(is_null($row['resultado']))
                    $row['resultado'] = "No ingresados";

                $arrayResult[] = $row;
            }
            $responseQuery->listResult = $arrayResult;
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron analisis para la mascota seleccioanda.";

        return $responseQuery;
    }



    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //-------------------------------------------------------VACUNAS MASCOTA ---------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------

    public function getFechasVacunasVencimiento($currentDate){
        $responseQuery = DataBase::sendQuery("SELECT VM.fechaProximaDosis FROM vacunasmascota AS VM, mascotas AS M WHERE VM.idMascota = M.idMascota AND M.estado = 1 AND VM.fechaProximaDosis IS NOT NULL AND VM.fechaProximaDosis <= ? GROUP BY VM.fechaProximaDosis ORDER BY VM.fechaProximaDosis DESC", array('i', $currentDate), "LIST");
        if($responseQuery->result ==2 ){
            $arrayResult = array();
            foreach ($responseQuery->listResult as $key => $row) {
                $row['fechaProximaDosisFormat'] = fechas::dateToFormatBar($row['fechaProximaDosis']);
                $arrayResult[] = $row;
            }
            $responseQuery->listResult = $arrayResult;
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron resultados.";

        return $responseQuery;
    }

    public function getVacunasVencidas($from, $to, $lastid){
        $from = str_replace("-", "", $from);
        $to = str_replace("-", "", $to);

        $responseQuery = DataBase::sendQuery("
            SELECT VM.idVacunaMascota, VM.nombreVacuna, VM.intervaloDosis, VM.numDosis, VM.fechaProximaDosis, VM.notifEnviada as observacion , M.idMascota, M.nombre, M.raza
            FROM vacunasmascota AS VM, mascotas AS M
            WHERE VM.idMascota = M.idMascota AND M.estado = 1 AND M.fechaFallecimiento IS NULL AND
                VM.fechaProximaDosis IS NOT NULL AND VM.fechaProximaDosis >= ? AND VM.fechaProximaDosis <= ?
                AND VM.idVacunaMascota < ?
            ORDER BY `VM`.`idVacunaMascota` ASC LIMIT 50", array('iii', $from, $to, $lastid), "LIST");

        if($responseQuery->result == 2){
            $arrayResult = array();
            foreach ($responseQuery->listResult as $key => $row) {
                if(is_null($row['raza']))
                    $row['raza'] = "";
                $row['fechaProximaDosis'] = fechas::dateToFormatBar($row['fechaProximaDosis']);
                $arrayResult[] = $row;
            }
            $responseQuery->listResult = $arrayResult;
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron vacunas/medicamentos vencidos para la fecha seleccionada.";
        return $responseQuery;
    }

    public function borrarVacunaMascota($idVacunaMascota){
        return DataBase::sendQuery("DELETE FROM vacunasmascota WHERE idVacunaMascota = ?", array('i', $idVacunaMascota), "BOOLE");
    }

    public function getVacunasMascotas($idMascota){
        $responseQuery = DataBase::sendQuery("SELECT * FROM vacunasmascota WHERE idMascota = ? ORDER BY idVacunaMascota DESC", array('i', $idMascota), "LIST");
        if($responseQuery->result == 2){
            $noData = "";
            $arrayResult = array();
            foreach ($responseQuery->listResult as $key => $row) {
                $row = serviciosMascota::vacunaToFormat($row);
                $arrayResult[] = $row;
            }
            $responseQuery->listResult = $arrayResult;
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron vacunas/medicamentos de la mascota seleccionada.";

        return $responseQuery;
    }

    public function vacunaToFormat($vacuna){
        $noData = "";

        if(!is_null($vacuna['fechaPrimerDosis']) && strlen($vacuna['fechaPrimerDosis']) == 8)
            $vacuna['fechaPrimerDosis'] = fechas::dateToFormatBar($vacuna['fechaPrimerDosis']);

        if(!is_null($vacuna['fechaProximaDosis']) && strlen($vacuna['fechaProximaDosis']) == 8)
            $vacuna['fechaProximaDosis'] = fechas::dateToFormatBar($vacuna['fechaProximaDosis']);
        else
            $vacuna['fechaProximaDosis'] = $noData;

        if(!is_null($vacuna['fechaUltimaDosis']) && strlen($vacuna['fechaUltimaDosis']) == 8)
            $vacuna['fechaUltimaDosis'] = fechas::dateToFormatBar($vacuna['fechaUltimaDosis']);
        else
            $vacuna['fechaUltimaDosis'] = $noData;

        if(is_null($vacuna['observacion']) || strlen($vacuna['observacion']) < 1)
            $vacuna['observaciones'] = $noData;

        return $vacuna;
    }

    public function getVacunasVencidasMaxId($fechaLimite){
        $query = DB::conexion()->prepare("SELECT MAX(idVacunaMascota) AS idMaximo FROM vacunasmascota WHERE fechaProximaDosis <= ? AND fechaProximaDosis != 0");
        $query->bind_param('i', $fechaLimite);
        if($query->execute()){
            $response = $query->get_result();
            $result = $response->fetch_object();
            return $result->idMaximo;
        }else return 0;
    }

    public function getVacunasVencidasMinId($vacunasVencidas, $maxID){
        $valorMinimo = $maxID;
        foreach ($vacunasVencidas as $key => $value) {
            if($value['idVacunaMascota'] < $valorMinimo)
                $valorMinimo = $value['idVacunaMascota'];
        }
        return $valorMinimo;
    }

    public function getVacunasVencidasPagina($ultimoID, $fechaLimite){
        $query = DB::conexion()->prepare("SELECT VM.idVacunaMascota, VM.fechaProximaDosis, VM.idMascota, VM.nombreVacuna, M.nombre AS nombreMascota
            FROM vacunasmascota AS VM, mascotas AS M WHERE M.idMascota = VM.idMascota AND M.estado = 1 AND VM.idVacunaMascota IN (SELECT idVacunaMascota FROM vacunasmascota WHERE fechaProximaDosis <= ? AND fechaProximaDosis != 0 AND idVacunaMascota <= ?) ORDER BY VM.idVacunaMascota LIMIT 10");
        $query->bind_param('ii', $fechaLimite, $ultimoID);
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
                if($row['socio'] == null){
                    $row['socio'] = array(
                        "nombre" => "No vinculado",
                        "telefono" => "No corresponde",
                        "correo" => "No corresponde");
                }
                $arrayResponse[] = $row;
            }
            return $arrayResponse;
        }else return null;
    }

    public function getVacunasVencidasMascota($idMascota, $fechaActual){
        $responseQuery = DataBase::sendQuery("SELECT VM.nombreVacuna, VM.fechaUltimaDosis, VM.fechaProximaDosis, M.nombre, VM.intervaloDosis FROM vacunasmascota AS VM, mascotas AS M WHERE VM.idMascota = M.idMascota AND M.idMascota = ? AND VM.fechaProximaDosis <= ? AND M.estado = 1", array('ii', $idMascota, $fechaActual), "LIST");
        if($responseQuery->result == 2){
            $arrayResult = array();
            foreach ($responseQuery->listResult as $key => $row) {
                $row['fechaUltimaDosis'] = fechas::dateToFormatBar($row['fechaUltimaDosis']);
                $row['fechaProximaDosis'] = fechas::dateToFormatBar($row['fechaProximaDosis']);

                $arrayResult[] = $row;
            }
            $responseQuery->listResult = $arrayResult;
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron vacunas/medicamentos vencidas de la mascota seleccionada.";

        return $responseQuery;
    }

    public function getVacunaMascotaToShow($idVacunaMascota){
        $responseQuery = DataBase::sendQuery("SELECT * FROM vacunasmascota WHERE idVacunaMascota = ?", array('i', $idVacunaMascota), "OBJECT");
        if($responseQuery->result == 2){
            $responseQuery->objectResult = serviciosMascota::formatVacunaObject($responseQuery->objectResult);
        }elseif($responseQuery->result == 1) $responseQuery->message = "No se encontro vacuna/medicamento con el identificador seleccionado.";

        return $responseQuery;
    }

    public function formatVacunaObject($vacuna){
        $noData = "";

        if(strlen($vacuna->fechaPrimerDosis) == 8)
            $vacuna->fechaPrimerDosis = fechas::dateToFormatBar($vacuna->fechaPrimerDosis);

        if(!is_null($vacuna->fechaProximaDosis) && strlen($vacuna->fechaProximaDosis) == 8)
            $vacuna->fechaProximaDosis = fechas::dateToFormatBar($vacuna->fechaProximaDosis);
        else if($vacuna->intervaloDosis == 1)
            $vacuna->fechaProximaDosis = "No corresponde.";
        else
            $vacuna->fechaProximaDosis = $noData;

        if(strlen($vacuna->fechaUltimaDosis) == 8)
            $vacuna->fechaUltimaDosis = fechas::dateToFormatBar($vacuna->fechaUltimaDosis);

        if(is_null($vacuna->observacion) || strlen($vacuna->observacion) < 1)
            $vacuna->observacion = $noData;

        return $vacuna;
    }

    public function getVacunaMascota($idVacunaMascota){
        $responseQuery = DataBase::sendQuery("SELECT * FROM vacunasmascota WHERE idVacunaMascota = ?", array('i', $idVacunaMascota), "OBJECT");
        if($responseQuery->result == 2){
            if(strlen($responseQuery->objectResult->fechaPrimerDosis) == 8)
                $responseQuery->objectResult->fechaPrimerDosis = fechas::dateToFormatHTML($responseQuery->objectResult->fechaPrimerDosis);

            if(strlen($responseQuery->objectResult->fechaProximaDosis) == 8)
                $responseQuery->objectResult->fechaProximaDosis = fechas::dateToFormatHTML($responseQuery->objectResult->fechaProximaDosis);

            if(strlen($responseQuery->objectResult->fechaUltimaDosis) == 8)
                $responseQuery->objectResult->fechaUltimaDosis = fechas::dateToFormatHTML($responseQuery->objectResult->fechaUltimaDosis);

        }elseif($responseQuery->result == 1) $responseQuery->message = "No se encontro vacuna/medicamento con el identificador seleccionado.";

        return $responseQuery;
    }

    public function insertVacunaMascota($nombre, $idMascota, $intervaloDosis, $numDosis, $fechaPrimerDosis, $fechaUltimaDosis,$fechaProximaDosis, $observaciones){
        return DataBase::sendQuery("INSERT INTO vacunasmascota (nombreVacuna, idMascota, intervaloDosis, numDosis, fechaPrimerDosis, fechaUltimaDosis, fechaProximaDosis, notifEnviada) VALUES (?,?,?,?,?,?,?,?)", array('siiiiiis', $nombre, $idMascota, $intervaloDosis, $numDosis, $fechaPrimerDosis, $fechaUltimaDosis,$fechaProximaDosis, $observaciones),"BOOLE");
    }

    public function updateVacunaMascota($idVacunaMascota, $nombre, $intervalo, $fechaUltimaDosis, $fechaProximaDosis, $observaciones){
        return DataBase::sendQuery("UPDATE vacunasmascota SET nombreVacuna= ?, intervaloDosis = ?, fechaUltimaDosis = ?, fechaProximaDosis = ?, notifEnviada = ? WHERE idVacunaMascota = ?", array('siiisi', $nombre, $intervalo, $fechaUltimaDosis, $fechaProximaDosis, $observaciones, $idVacunaMascota), "BOOLE");
    }

    public function aplicarDosisVacunaMascota($idVacunaMascota, $nuevaUltimaDosis, $nuevaNumDosis, $fechaProximaDosis){
        return DataBase::sendQuery("UPDATE vacunasmascota SET numDosis = ? , fechaUltimaDosis = ?, fechaProximaDosis = ? WHERE idVacunaMascota  = ?", array('iiii', $nuevaNumDosis, $nuevaUltimaDosis, $fechaProximaDosis, $idVacunaMascota), "BOOLE");
    }

    public function getVacunasByName($nombreVacuna){
        return DataBase::sendQuery("SELECT * FROM vacunas WHERE nombre = ?", array('s', $nombreVacuna), "OBJECT");
    }

    public function getVacunasByInput($input){
        return DataBase::sendQuery("SELECT * FROM vacunas WHERE nombre like '%". $input ."%' ORDER BY nombre ASC LIMIT 10", array(), "LIST");
    }

    public function getVacunasSinNotificar($lastId){

        return DataBase::sendQuery("
            SELECT vm.*, s.nombre as nombreSocio,s.email, s.telefono, m.nombre FROM vacunasmascota as vm
            LEFT JOIN mascotasocio as ms on vm.idMascota = ms.idMascota
            LEFT JOIN socios as s on ms.idSocio = s.idSocio
            LEFT JOIN mascotas as m on ms.idMascota = m.idMascota
            WHERE ( notifEnviada IS NULL OR notifEnviada = '' ) AND idVacunaMascota < ?
            ORDER BY vm.idVacunaMascota DESC LIMIT 14", array('i', $lastId), "LIST");
    }

    public function getLastIdVacunasMascotas(){
        $responseQuery = DataBase::sendQuery("SELECT MAX(idVacunaMascota) AS lastID FROM vacunasmascota", array(), "OBJECT");
        if($responseQuery->result == 2) return ($responseQuery->objectResult->lastID +1);
    }
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------ENFERMEDADES------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------

    public function getEnfermedadesMascota($idMascota){
        $responseQuery = DataBase::sendQuery("SELECT * FROM enfermedadesmascota WHERE idMascota = ? ORDER BY idEnfermedad DESC", array('i', $idMascota), "LIST");
        if($responseQuery->result == 2){
            $noData = "";
            $arrayResult = array();
            foreach ($responseQuery->listResult as $key => $row) {
                if(!is_null($row['fechaDiagnostico']) && strlen($row['fechaDiagnostico']) == 8)
                    $row['fechaDiagnostico'] = fechas::dateToFormatBar($row['fechaDiagnostico']);
                else
                    $row['fechaDiagnostico'] = $noData;

                if(is_null($row['observaciones']) || strlen($row['observaciones']) == 0)
                    $row['observaciones'] = $noData;

                $arrayResult[] = $row;
            }
            $responseQuery->listResult = $arrayResult;
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron enfermedades asociadas a la mascota seleccionada.";

        return $responseQuery;
    }

    public function getEnfermedadMascotaToShow($idEnfermedad){
        $responseQuery = DataBase::sendQuery("SELECT * FROM enfermedadesmascota WHERE idEnfermedad = ? ", array('i', $idEnfermedad), "OBJECT");
        if($responseQuery->result == 2){
            $responseQuery->objectResult->fechaDiagnostico = fechas::dateToFormatBar($responseQuery->objectResult->fechaDiagnostico);

            if(is_null($responseQuery->objectResult->observaciones))
                $responseQuery->objectResult->observaciones = "";
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontro una enfermedad con el identificador seleccionado.";

        return $responseQuery;
    }

    public function insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnostico, $observaciones){
        return DataBase::sendQuery("INSERT INTO enfermedadesmascota (idMascota, fechaDiagnostico, nombreEnfermedad, observaciones) VALUES (?,?,?,?)", array('iiss', $idMascota, $fechaDiagnostico, $nombre, $observaciones), "BOOLE");
    }

    public function updateEnfermedadMascota($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones){
        return DataBase::sendQuery("UPDATE enfermedadesmascota SET fechaDiagnostico = ?, nombreEnfermedad = ?, observaciones = ? WHERE idEnfermedad = ?", array('issi', $fechaDiagnostico, $nombre, $observaciones, $idEnfermedad), "BOOLE");
    }

    public function deleteEnfermedad($idEnfermedad){
        return DataBase::sendQuery("DELETE FROM enfermedadesmascota WHERE idEnfermedad = ?", array('i', $idEnfermedad), "BOOLE");
    }

    public function getEnfermedadMascota($idEnfermedad){
        $responseQuery = DataBase::sendQuery("SELECT * FROM enfermedadesmascota WHERE idEnfermedad  = ?", array('i', $idEnfermedad), "OBJECT");
        if($responseQuery->result == 2){
            $responseQuery->objectResult->fechaDiagnostico = fechas::dateToFormatHTML($responseQuery->objectResult->fechaDiagnostico);
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontro una enfermedad con el identificador seleccionado.";

        return $responseQuery;
    }
}