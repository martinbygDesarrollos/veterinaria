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



		$sql = "INSERT INTO `facturacion` (`mes`, `fechaDePago`, `importe`, `comprobante`, `recibo`, `observaciones`, `idCliente`) VALUES (?,?,?,?,?,?,?)";

		return $database->sendQuery($sql,array("ssisssi",$mes, $fecha, $importe, $comprobante, $recibo, $observaciones, $numSocio), "BOOLE");

	}


}

?>