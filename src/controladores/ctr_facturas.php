<?php

require_once "../src/clases/facturas.php";

class ctr_facturas {

    public function updateFacturaspendientes($facturas){
        $facturasClass = new facturas();
		$response = new \stdClass();
        $responseEmpty = $facturasClass->emptyTable();
        $errores = array();
        foreach ($facturas as $factura) {
            $responseCreate = $facturasClass->create($factura);
            if ($responseCreate->result != 2){
                $errores[] = $factura;
            }
        }
        if(count($errores) == 0)
            $response->result = 2;
        else
            $response->result = 1;
        $response->errores = $errores;
		return $response;
    }
}
?>