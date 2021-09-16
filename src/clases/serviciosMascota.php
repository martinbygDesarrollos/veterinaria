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
            $analisis = $responseQuery->objectResult;
            $noData = "No especificado";
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
        $responseQuery = DataBase::sendQuery("SELECT * FROM analisismascota WHERE idMascota = ?", array('i', $idMascota), "LIST");
        if($responseQuery->result == 2){
            $noData = "No especificado";
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
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron vacunas en la base de datos.";

        return $responseQuery;
    }

    public function getVacunasVencidas($dateVacuna){
        $responseQuery = DataBase::sendQuery("SELECT VM.idVacunaMascota, VM.nombreVacuna, VM.intervaloDosis, VM.numDosis, VM.fechaProximaDosis, M.idMascota, M.nombre, M.raza FROM vacunasmascota AS VM, mascotas AS M WHERE VM.idMascota = M.idMascota AND M.estado = 1 AND VM.fechaProximaDosis IS NOT NULL AND VM.fechaProximaDosis = ? ORDER BY VM.idVacunaMascota DESC", array('i', $dateVacuna), "LIST");
        if($responseQuery->result == 2){
            $arrayResult = array();
            foreach ($responseQuery->listResult as $key => $row) {
                if(is_null($row['raza']))
                    $row['raza'] = "No especificada";
                $row['fechaProximaDosis'] = fechas::dateToFormatBar($row['fechaProximaDosis']);
                $row['intervaloDosis'] = serviciosMascota::getInterval($row['intervaloDosis']);

                $arrayResult[] = $row;
            }
            $responseQuery->listResult = $arrayResult;
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron vacunas vencidas para la fecha seleccionada.";
        return $responseQuery;
    }

    public function borrarVacunaMascota($idVacunaMascota){
        return DataBase::sendQuery("DELETE FROM vacunasmascota WHERE idVacunaMascota = ?", array('i', $idVacunaMascota), "BOOLE");
    }

    public function getVacunasMascotas($idMascota){
        $responseQuery = DataBase::sendQuery("SELECT * FROM vacunasmascota WHERE idMascota = ? ORDER BY idVacunaMascota DESC", array('i', $idMascota), "LIST");
        if($responseQuery->result == 2){
            $noData = "No especificada";
            $arrayResult = array();
            foreach ($responseQuery->listResult as $key => $row) {
                $row = serviciosMascota::vacunaToFormat($row);
                $arrayResult[] = $row;
            }
            $responseQuery->listResult = $arrayResult;
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron vacunas de la mascota seleccionada.";

        return $responseQuery;
    }

    public function vacunaToFormat($vacuna){
        $noData = "No especificada";

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

        if($vacuna['intervaloDosis'] == 1)
            $vacuna['intervaloDosis'] = "Única dosis";
        else if($vacuna['intervaloDosis'] == 30)
            $vacuna['intervaloDosis'] = "Mensual";
        else if($vacuna['intervaloDosis'] == 60)
            $vacuna['intervaloDosis'] = "Bimestral";
        else if($vacuna['intervaloDosis'] == 180)
            $vacuna['intervaloDosis'] = "Semestral";
        else if($vacuna['intervaloDosis'] == 360)
            $vacuna['intervaloDosis'] = "Anual";

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
        }else if($responseQuery->result == 1) $responseQuery->message = "No se encontraron vacunas vencidas de la mascota seleccionada.";

        return $responseQuery;
    }

    public function getVacunaMascotaToShow($idVacunaMascota){
        $responseQuery = DataBase::sendQuery("SELECT * FROM vacunasmascota WHERE idVacunaMascota = ?", array('i', $idVacunaMascota), "OBJECT");
        if($responseQuery->result == 2){
            $responseQuery->objectResult = serviciosMascota::formatVacunaObject($responseQuery->objectResult);
        }elseif($responseQuery->result == 1) $responseQuery->message = "La se encontro una vacuna con el identificador seleccionado.";

        return $responseQuery;
    }

    public function formatVacunaObject($vacuna){
        $noData = "No especificada";

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

        if($vacuna->intervaloDosis == 1)
            $vacuna->intervaloDosis = "Única dosis";
        else if($vacuna->intervaloDosis == 30)
            $vacuna->intervaloDosis = "Mensual";
        else if($vacuna->intervaloDosis == 60)
            $vacuna->intervaloDosis = "Bimestral";
        else if($vacuna->intervaloDosis == 180)
            $vacuna->intervaloDosis = "Semestral";
        else if($vacuna->intervaloDosis == 360)
            $vacuna->intervaloDosis = "Anual";

        return $vacuna;
    }

    public function getInterval($value){
        if($value == 1)
            return "Única dosis";
        if($value == 30)
            return "Mensual";
        if($value == 60)
            return "Bimestral";
        if($value == 180)
            return "Semestral";
        if($value == 360)
            return "Anual";
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

        }elseif($responseQuery->result == 1) $responseQuery->message = "La se encontro una vacuna con el identificador seleccionado.";

        return $responseQuery;
    }

    public function insertVacunaMascota($nombre, $idMascota, $intervaloDosis, $numDosis, $fechaPrimerDosis, $fechaUltimaDosis,$fechaProximaDosis, $observaciones){
        return DataBase::sendQuery("INSERT INTO vacunasmascota (nombreVacuna, idMascota, intervaloDosis, numDosis, fechaPrimerDosis, fechaUltimaDosis, fechaProximaDosis, observacion) VALUES (?,?,?,?,?,?,?,?)", array('siiiiiis', $nombre, $idMascota, $intervaloDosis, $numDosis, $fechaPrimerDosis, $fechaUltimaDosis,$fechaProximaDosis, $observaciones),"BOOLE");
    }

    public function updateVacunaMascota($idVacunaMascota, $nombre, $intervalo, $fechaUltimaDosis, $fechaProximaDosis, $observaciones){
        return DataBase::sendQuery("UPDATE vacunasmascota SET nombreVacuna= ?, intervaloDosis = ?, fechaUltimaDosis = ?, fechaProximaDosis = ?, observacion = ? WHERE idVacunaMascota = ?", array('siiisi', $nombre, $intervalo, $fechaUltimaDosis, $fechaProximaDosis, $observaciones, $idVacunaMascota), "BOOLE");
    }

    public function aplicarDosisVacunaMascota($idVacunaMascota, $nuevaUltimaDosis, $nuevaNumDosis, $fechaProximaDosis){
        return DataBase::sendQuery("UPDATE vacunasmascota SET numDosis = ? , fechaUltimaDosis = ?, fechaProximaDosis = ? WHERE idVacunaMascota  = ?", array('iiii', $nuevaNumDosis, $nuevaUltimaDosis, $fechaProximaDosis, $idVacunaMascota), "BOOLE");
    }

    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------ENFERMEDADES------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------------------------------------------------------------------

    public function getEnfermedadesMascota($idMascota){
        $responseQuery = DataBase::sendQuery("SELECT * FROM enfermedadesmascota WHERE idMascota = ? ORDER BY idEnfermedad DESC", array('i', $idMascota), "LIST");
        if($responseQuery->result == 2){
            $noData = "No especificado.";
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
                $responseQuery->objectResult->observaciones = "No especificado";
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