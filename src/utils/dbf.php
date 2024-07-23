<?php

use XBase\Table;


class dbf {

    public function readArticulos($uploadedFilePath){
        $articulos = array();
		$response = new \stdClass();
        $db = $uploadedFilePath;

        $fdbf = fopen($db,'r');
        if (!$fdbf){
            $response->result = 1;
            $response->message = "No se pudo leer el archivo";
        }

        //$fields = array();
        $buf = fread($fdbf,32);
        $header=unpack( "VRecordCount/vFirstRecord/vRecordLength", substr($buf,4,8));
        $goon = true;
        $unpackString='';
        $fieldTypes = array();

        while ($goon && !feof($fdbf)) {
            $buf = fread($fdbf,32);
            if (substr($buf,0,1)==chr(13)) {
                $goon=false;
            }else {
                $field=unpack( "a11fieldname/A1fieldtype/Voffset/Cfieldlen/Cfielddec", substr($buf,0,18));
                $unpackString.="A$field[fieldlen]$field[fieldname]/";
                $fieldTypes[$field['fieldname']] = $field['fieldtype'];
                //array_push($fields, $field);
            }
        }
        fseek($fdbf, $header['FirstRecord']);
        for ($i=1; $i<=$header['RecordCount']; $i++) {
            $buf = fread($fdbf,$header['RecordLength']);

            $buf = substr($buf, 1);
            $row = unpack($unpackString, $buf);

            $obj = new \stdClass();
            $obj->rubro = trim($row['RUBRO']) == "" ? null : trim($row['RUBRO']);
            $obj->nro = trim($row['NRO']) == "" ? null : trim($row['NRO']);
            $obj->desc = trim($row['DESC']) == "" ? null : trim($row['DESC']);
            $obj->marca = trim($row['MARCA']) == "" ? null : trim($row['MARCA']);
            $obj->saldo = intval(trim($row['SALDO']));
            $obj->costo = floatval(trim($row['COSTO']));
            $obj->coef = floatval(trim($row['COEF']));
            $obj->porc_cif = floatval(trim($row['PORC_CIF']));
            $obj->prov = intval(trim($row['PROV']));
            $obj->fec_comp = trim($row['FEC_COMP']) == "" ? null : trim($row['FEC_COMP']);
            $obj->pos = trim($row['POS']) == "" ? null : trim($row['POS']);
            $obj->iva = floatval(trim($row['IVA']));
            $obj->cofis = floatval(trim($row['COFIS']));
            $obj->unid = intval(trim($row['UNID']));
            $obj->codebar = trim($row['CODEBAR']) == "" ? null : trim($row['CODEBAR']);
            $obj->codigo = intval(trim($row['CODIGO']));
            $obj->rot = trim($row['ROT']) == "" ? null : trim($row['ROT']);
            $obj->stomin = intval(trim($row['STOMIN']));
            $obj->garantia = trim($row['GARANTIA']) == "" ? null : trim($row['GARANTIA']);
            $obj->rubrocont = trim($row['RUBROCONT']) == "" ? null : trim($row['RUBROCONT']);
            $obj->fec_invent = trim($row['FEC_INVENT']) == "" ? null : trim($row['FEC_INVENT']);
            $obj->fec_stkini = trim($row['FEC_STKINI']) == "" ? null : trim($row['FEC_STKINI']);
            $obj->stk_ini = intval(trim($row['STK_INI']));
            $obj->obs = trim($row['OBS']) == "" ? null : trim($row['OBS']);
            $obj->tp_impuest = trim($row['TP_IMPUEST']) == "" ? null : trim($row['TP_IMPUEST']);
            $obj->coef1 = floatval(trim($row['COEF1']));
            $obj->coef2 = floatval(trim($row['COEF2']));
            $obj->coef3 = floatval(trim($row['COEF3']));
            $obj->frut_verd = trim($row['FRUT_VERD']) == "" ? null : trim($row['FRUT_VERD']);
            $obj->peso_unid = trim($row['PESO_UNID']) == "" ? null : trim($row['PESO_UNID']);
            $articulos[] = $obj;
        }
		$response->result = 2;
		$response->objectResult = $articulos;
		return $response;
    }

}