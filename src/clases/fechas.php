<?php

class fechas{

	public function StringToIntFechaHoraGuion($fecha){ // 2019-12-31 => 20191231
		$fechaSinGuion = str_replace('-', '', $fecha);
		$fechaSinDosPuntos = str_replace(':', '', $fechaSinGuion);
		return str_replace(' ', '', $fechaSinDosPuntos);
	}


	public function parceFechaIntNoDay($anio, $mes){
		if($mes < 10)
			$mes = '0' . $mes;
		return $anio . $mes;
	}

	public function parceFechaFormatDMANoDay($fecha, $separador){
		return substr($fecha, 4,2) . $separador . substr($fecha, 0,4);
	}

	public function parceFechaInt($fecha){
		$response = null;
		$soloFecha = explode(" ", $fecha);

		if(strpos($soloFecha[0], "/")){
			$arrayFecha = explode("/",$soloFecha[0]);

			$mes = $arrayFecha[1];
			if(strlen($mes) == 1)
				$mes = "0" . $mes;

			if(strlen($arrayFecha[0]) <= 2){

				$dia = $arrayFecha[0];
				if(strlen($dia)  == 1)
					$dia = "0" . $dia;

				$response = $arrayFecha[2] . $mes . $dia;
			}else if (strlen($arrayFecha[0]) == 4) {

				$dia = $arrayFecha[2];
				if(strlen($dia)  == 1)
					$dia = "0" . $dia;

				$response = $arrayFecha[0] . $mes . $dia;
			}
		}else if(strpos($soloFecha[0], "-")){
			$arrayFecha = explode("-",$soloFecha[0]);

			$mes = $arrayFecha[1];
			if(strlen($mes) == 1)
				$mes = "0" . $mes;

			if(strlen($arrayFecha[0]) <= 2){
				$dia = $arrayFecha[0];
				if(strlen($dia)  == 1)
					$dia = "0" . $dia;

				$response = $arrayFecha[2] . $mes . $dia;
			}else if (strlen($arrayFecha[0]) == 4) {
				$dia = $arrayFecha[2];
				if(strlen($dia)  == 1)
					$dia = "0" . $dia;
				$response = $arrayFecha[0] . $mes . $dia;
			}
		}

		return $response;
	}

	public function parceFechaFormatDMA($fecha, $separador){
		return substr($fecha, 6,2). $separador .substr($fecha, 4,2). $separador .substr($fecha, 0,4);
	}

	public function parceFechaFormatAMD($fecha, $separador){  // 20191231 =>  2019-12-31
		return substr($fecha, 0,4). $separador .substr($fecha, 4,2). $separador .substr($fecha, 6,2);
	}

	public function obtenerDiferenciaDias($fechaProxDosis, $fechaLimite){
		$fechaP = fechas::parceFechaFormatAMD($fechaProxDosis, "-");
		$dias = (strtotime($fechaP)- strtotime($fechaLimite))/86400;
		$dias = abs($dias); $dias = floor($dias);
		return $dias;
	}

	public function parceFechaMesFormatDMA($fecha){
		return substr($fecha, 4,2). "/" .substr($fecha, 0,4);
	}

	public function calcularFechaProximaDosis($fechaUltimaDosis, $intervalo){
		$nuevafecha = date("Y-m-d", strtotime("$fechaUltimaDosis + ". $intervalo ." day"));
		$nuevafecha = fechas::parceFechaInt($nuevafecha);
		return fechas::parceFechaFormatDMA($nuevafecha, "/");
	}
}