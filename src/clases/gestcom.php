<?php
class gestcom{


	public function saveFacturacion($data){

		$database = new DataBase();
		$mes = $data['mes'];
		$fecha = ( !isset($data['fecha']) || $data['fecha'] == "") ? null : $data['fecha'];
		$importe = $data['importe'];
		$comprobante = $data['comprobante'];
		$recibo = ( !isset($data['recibo']) || $data['recibo'] == "") ? null : $data['recibo'];
		$observaciones = ( !isset($data['observaciones']) || $data['observaciones'] == "") ? null : $data['observaciones'];
		$numSocio = $data['numSocio'];



		//$sql = "INSERT INTO `facturacion` (`mes`, `fechaDePago`, `importe`, `comprobante`, `recibo`, `observaciones`, `idCliente`) VALUES (?,?,?,?,?,?,?)";
		$sql = "INSERT INTO `historialsocios` (`idSocio`, `asunto`, `importe`, `fecha`, `observaciones`, `fechaEmision`, `mes`, `comprobante`, `recibo`) VALUES (?,?,?,?,?,?,?,?,?)";



		//return $database->sendQuery($sql,array("ssisssi",$mes, $fecha, $importe, $comprobante, $recibo, $observaciones, $numSocio), "BOOLE");
		return $database->sendQuery($sql,array("isissssss",$numSocio, "GESTCOM", $importe, $fecha, $observaciones, date("YmdHis"), $mes, $comprobante, $recibo), "BOOLE");
	}




	public function getFacturacionById( $numSocio, $mes ){

		$database = new DataBase();

		$sql = "SELECT idHistorialSocio FROM `historialsocios` WHERE idSocio = ? AND mes = ?";
		$params = array('is', $numSocio, $mes);

		return $database->sendQuery($sql, $params, "OBJECT");
	}


	public function updateFacturacion($idFacturacion, $data){

		$database = new DataBase();
		$fecha = ( !isset($data['fecha']) || $data['fecha'] == "") ? null : $data['fecha'];
		$recibo = ( !isset($data['recibo']) || $data['recibo'] == "") ? null : $data['recibo'];

		$sql = "UPDATE `historialsocios` SET `recibo` = ?, `fecha` = ? WHERE `historialsocios`.`idHistorialSocio` = ?";
		$params = array('ssi', $recibo, $fecha, $idFacturacion);

		return $database->sendQuery($sql, $params, "BOOLE");

	}


}

?>