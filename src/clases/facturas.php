<?php

class facturas{
	public function create( $factura ){
        // var_dump($factura);exit;
		$database = new DataBase();
        $factura['tipo'] = $factura['tipo'] == "" ? NULL : $factura['tipo'];  
        return $database->sendQuery("INSERT INTO `facturaspendientes` (`idCliente`, `fecha`, `tipo`, `serie`, `numero`, `importe`, `saldo`) VALUES (?,?,?,?,?,?,?)", array('ssssidd',$factura['idCliente'], $factura['fecha'], $factura['tipo'], $factura['serie'], $factura['numero'], $factura['importe'], $factura['saldo']), "BOOLE");
	}

    public function emptyTable(){
        $database = new DataBase();
        $sql = "TRUNCATE `facturaspendientes`";
        return $database->sendQuery($sql, array(), "BOOLE");
    }

}

?>