<?php

require_once "../src/utils/dbf.php";
require_once "../src/clases/historiaArticulo.php";


class ctr_historiaArticulos {


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

                    //var_dump($articulo);
                    $responseCreate = $historaArticuloClass->create($articulo);
                    if ($responseCreate->result != 2)
                        $errores[] = $articulo->rubro."_".$articulo->nro.": ". $responseCreate->message;

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

}
?>