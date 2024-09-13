<?php

require_once "../src/utils/dbf.php";
require_once "../src/clases/historiaArticulo.php";


class ctr_historiaArticulos {

    public function getArticulosPendientesByIdClient($idClient){
        $historaArticuloClass = new historiaArticulo();
		$response = new \stdClass();
        $responseGetResults = new \stdClass();
        $responseGetResults->result = 0;


        if ($idClient == 0){
            $responseGetResults = $historaArticuloClass->getArticulosPendientesAllClient();
        }else{
    		$responseGetResults = $historaArticuloClass->getArticulosPendientesByIdClient($idClient);
        }


		if($responseGetResults->result == 2){
			$response->result = 2;
			$response->articulos = $responseGetResults->listResult;
		} else if ($responseGetResults->result == 1){
			$response->result = 1;
			$response->message = "Ningún artículo pendiente encontrado.";
            $response->articulos = array();
		} else return $responseGetResults;
		return $response;
    }

    public function updateHistoriaArticulo($ids, $tipo, $serie, $numero){
        $historaArticuloClass = new historiaArticulo();
		$response = new \stdClass();
        $exitos = [];
        $errores = [];
        foreach ($ids as $id) {
            $pendiente = $historaArticuloClass->getArticulosPendientesById($id);
		    if($pendiente->result == 2){
                $responseUpdate = $historaArticuloClass->updateArticuloPendiente($id, $tipo, $serie, $numero);
                if($responseUpdate->result == 2){
                    // $response->result = 2;
                    $exitos[] = $id;
                } else {
                    $errores[] = $id;
                }
            } else {
                $errores[] = $id;
            }
        }
        if(count($errores) == 0)
            $response->result = 2;
        else
            $response->result = 1;
        $response->exitos = $exitos;
        $response->errores = $errores;
		return $response;
    }

    public function updateFacturaspendientes($facturas){
        $historaArticuloClass = new historiaArticulo();
		$response = new \stdClass();
        $responseEmpty = $historaArticuloClass->emptyTable();
        foreach ($ids as $id) {
            $pendiente = $historaArticuloClass->getArticulosPendientesById($id);
		    if($pendiente->result == 2){
                $responseUpdate = $historaArticuloClass->updateArticuloPendiente($id, $tipo, $serie, $numero);
                if($responseUpdate->result == 2){
                    // $response->result = 2;
                    $exitos[] = $id;
                } else {
                    $errores[] = $id;
                }
            } else {
                $errores[] = $id;
            }
        }
        if(count($errores) == 0)
            $response->result = 2;
        else
            $response->result = 1;
        $response->exitos = $exitos;
        $response->errores = $errores;
		return $response;
    }

    public function altaArticulos($path){

        $response = new stdClass();
        $historaArticuloClass = new historiaArticulo();
        $dbfClass = new dbf();
        $readResponse = $dbfClass->readArticulos($path);

        $errores = array();

        if (isset($readResponse->objectResult) && count($readResponse->objectResult) > 0){

            $responseEmpty = $historaArticuloClass->emptyTable();
            if ($responseEmpty->result == 2) {
                $filasArt = $readResponse->objectResult;
                foreach ($filasArt as $articulo) {

                    if (isset($articulo->desc) && $articulo->desc != "" ){
                        $responseCreate = $historaArticuloClass->create($articulo);
                        if ($responseCreate->result != 2)
                            $errores[] = $articulo->rubro."_".$articulo->nro.": ". $responseCreate->message;
                    }

                }

                if(count($errores) >0 ){
                    $response->result = 1;
                    $response->message = "Error al procesar los artículos.";
                    $response->errores = $errores;
                    return $response;

                }else{
                    $response->result = 2;
                    $response->message = "Ok";
                    $response->errores = $errores;
                    return $response;
                }
            }else{
                return $responseEmpty;
            }

        }
    }




    public function searchArticuloByDescOrCodeBar($text){

        $articulos = new historiaArticulo();

		$textArray = explode(" ", $text);
		return $articulos->getByDescOrCodebar( $textArray );

    }


    public function matchArticulosHistoria($art, $idHist, $idMascota){


        $articulosClass = new historiaArticulo();
        $response = new stdClass();
        $response->result = 2;
        $response->message = "";

        //obtener id cliente segun la mascota
        $clienteClass = new socios();
        $clienteResponse = $clienteClass->getSocioMascota($idMascota);


        if($clienteResponse->result == 2){
            $idCliente = $clienteResponse->objectResult->idSocio;
        }else return $clienteResponse;


        //$arrayArticulos = explode(",", $art);

        $data = [
            "idHistoriaClinica"=>$idHist,
            "idCliente"=>$idCliente,
            "cantidad"=>1
        ];

        foreach ($art as $row) {
            $data["idArticulo"] = $row["art"];

            if (isset($row["cant"]))
                $data["cantidad"] = $row["cant"];
            
            $articuloResponse = $articulosClass->new($data);
            if ($articuloResponse->result != 2){
                $response->result = $articuloResponse->result;
                $response->message .= $articuloResponse->message."<br>";
            }
        }


        if ( $response->result == 2){
            $response->message = "Artículos ingresados.";
        }

        return $response;

    }



    public function getArticulosByHistoria($idHist){
        $articulosClass = new historiaArticulo();
        return $articulosClass->getArticulosByHistoria($idHist);

    }
}
?>