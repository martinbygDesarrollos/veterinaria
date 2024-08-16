<?php

class historiaArticulo{


    //crear registro en la tabla articulos
    public function create( $articulo ){
        //var_dump($articulo);exit;
		$database = new DataBase();
        //$this = new historiaArticulo();

        $responseId = $this->articuloid_encode($articulo->rubro, $articulo->nro);
        if ($responseId->result == 2){

            return $database->sendQuery("INSERT INTO `articulos` (`id`, `descripcion`, `marca`, `saldo`, `costo`, 
            `coef`, `porc_cif`, `proveedor_id`, `fecha_compra`, `pos`, `iva`, `cofis`, `unidades`, `codigo_barras`, 
            `codigo`, `rot`, `stock_minimo`, `garantia`, `rubro_contable`, `fecha_inventario`, `fecha_stock_inicial`, 
            `stock_inicial`, `observaciones`, `tipo_impuesto`, `coef1`, `coef2`, `coef3`, `fruta_verdura`, `peso_unidad`) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 
            array('sssidddissddisisissssissdddss',$responseId->id, $articulo->desc ,  $articulo->marca,  $articulo->saldo,
                $articulo->costo, $articulo->coef,  $articulo->porc_cif,  $articulo->prov,  $articulo->fec_comp,
                $articulo->pos,  $articulo->iva,  $articulo->cofis,  $articulo->unid,  $articulo->codebar, 
                $articulo->codigo,  $articulo->rot,  $articulo->stomin,  $articulo->garantia,  $articulo->rubrocont,
                $articulo->fec_invent,  $articulo->fec_stkini, $articulo->stk_ini, $articulo->obs,  $articulo->tp_impuest,  $articulo->coef1,
                $articulo->coef2,  $articulo->coef3, $articulo->frut_verd, $articulo->peso_unid), "BOOLE");

        }else return $responseId;

	}

    //crear registro en la tabla historiaarticulo
    public function new( $data ){
		$database = new DataBase();

        return $database->sendQuery("INSERT INTO `historiaarticulo`
            (`idHistoriaClinica`, `idArticulo`, `idCliente`, `cantidad`)
            VALUES (?,?,?,?);",
            array('isii',$data["idHistoriaClinica"], $data["idArticulo"], $data["idCliente"], $data["cantidad"]), "BOOLE");

	}


    //toma rubro "FA001" y numero "  1" devuelve "FA001_1"
    public function articuloid_encode($rubro, $num){
        $response = new stdClass();

        if ( !isset($rubro) || $rubro == "" ){
            $response->result = 1;
            $response->message = "Rubro $rubro no es correcto.";
            return $response;
        }

        if ( !isset($num) || $num == "" ){
            $response->result = 1;
            $response->message = "Número $num no es correcto.";
            return $response;
        }

        $rubro = str_replace(" ", "", $rubro);
        $num = str_replace(" ", "", $num);

        $response->result = 2;
        $response->id = $rubro."_".$num;
        return $response;

    }

    //toma "FA001_1" y devuelve ["rubro"=>"FA001", "numero"=>"1"]
    public function articuloid_decode($id){
        $rub_num = explode("_",$id);
        return ["rubro"=>$rub_num[0], "numero"=>$rub_num[1]];
    }


    public function emptyTable(){
        $database = new DataBase();

        $sql = "TRUNCATE `articulos`";
        return $database->sendQuery($sql, array(), "BOOLE");

    }

    public function getArticulosPendientesByIdClient($idClient){
        $dbClass = new DataBase();
        return $dbClass->sendQuery("SELECT id, idArticulo, fecha, cantidad FROM historiaarticulo WHERE tipo IS NULL AND serie IS NULL AND numero IS NULL AND idCliente = ? ", array('i', $idClient), "LIST");
    }

    public function getArticulosPendientesById($idHistoriaArticulo){
        $dbClass = new DataBase();
        return $dbClass->sendQuery("SELECT * FROM historiaarticulo WHERE tipo IS NULL AND serie IS NULL AND numero IS NULL AND id= ? ", array('i', $idHistoriaArticulo), "OBJECT");
    }

    public function updateArticuloPendiente($idHistoriaArticulo, $tipo, $serie, $numero){
        $dbClass = new DataBase();
        return $dbClass->sendQuery("UPDATE historiaarticulo SET tipo = ?, serie = ?, numero = ? WHERE id= ? ", array('ssii', $tipo, $serie, $numero, $idHistoriaArticulo), "BOOLE");
    }


    public function getByDescOrCodebar($textArray){
        $dbClass = new DataBase();
        $where = "";
        foreach ($textArray as $text) {
            if ( strlen($text) > 0 ){
                if ( strlen($where) > 0 ){
                    $where .= " AND (descripcion like '%".$text."%' OR codigo_barras like '%".$text."%' ) ";
                }else
                    $where .= " (descripcion like '%".$text."%' OR codigo_barras like '%".$text."%' )";
            }
        }


        $sql = "SELECT id, descripcion, saldo FROM `articulos`
            WHERE $where
            ORDER BY `descripcion` ASC
            LIMIT 30";

            //var_dump($sql);exit;

        return $dbClass->sendQuery($sql, array(), "LIST");
    }


    public function getArticulosByHistoria($id){
        $dbClass = new DataBase();
        $sql = "SELECT ha.*, a.descripcion FROM historiaarticulo ha
            LEFT JOIN articulos a on a.id = ha.idArticulo
            WHERE ha.idHistoriaClinica = ?";

        return $dbClass->sendQuery($sql, array("i", $id), "LIST");
    }

}

?>