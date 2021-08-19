<?php

class fechas{

	public function getDateByMonth($months){
		date_default_timezone_set('America/Montevideo');
		$date = date('Y-m-d', strtotime($months . " month", strtotime(date('Y-m-d'))));
		return substr($date, 5, 2) . "/" . substr($date, 0, 4);
	}

	public function getDateToINT($date){
		$response = null;
		$onlyDate = explode(" ", $date);

		if(strpos(substr($onlyDate[0],0,4), "/") || strpos(substr($onlyDate[0],0,4),"-"))
			return substr($onlyDate[0], 6, 4) . substr($onlyDate[0], 3, 2) . substr($onlyDate[0],0,2);
		else
			return substr($onlyDate[0], 0, 4) . substr($onlyDate[0], 5, 2) . substr($onlyDate[0], 8, 2);
	}

	public function getDateWithMonthYearToINT($date){
		$onlyDate = explode(" ", $date);

		if(strpos(substr($onlyDate[0],0,4), "/") || strpos(substr($onlyDate[0],0,4),"-"))
			return substr($onlyDate[0], 6, 4) . substr($onlyDate[0], 3, 2);
		else
			return substr($onlyDate[0], 0, 4) . substr($onlyDate[0], 5, 2);
	}


	public function getCurrentMonth(){
		date_default_timezone_set('America/Montevideo');
		$date = date('Y-m-d');
		return substr($date, 5, 2);
	}

	public function getDatePlusMonthsInt($months){
		date_default_timezone_set('America/Montevideo');
		$date = date('Y-m-d', strtotime("+ " . $months . " month", strtotime(date('Y-m-d'))));
		return substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);
	}

	public function getDateTimeNowInt(){ // 05-12-2016 15:30:50
		date_default_timezone_set('America/Montevideo');
		$dateTime = date('m-d-Y h:i:s a', time());
		return substr($dateTime, 6, 4) . substr($dateTime, 0, 2) . substr($dateTime, 3, 2) . substr($dateTime,11,2) . substr($dateTime, 14, 2) . substr($dateTime, 17, 2);
	}

	public function getCurrentDateInt(){
		date_default_timezone_set('America/Montevideo');
		$date = date('Y-m-d');
		return fechas::getDateToINT($date);
	}

	public function dateToFormatBar($intDate){
		return  substr($intDate, 6, 2) . "/" .  substr($intDate, 4, 2) . "/" . substr($intDate, 0, 4);
	}

	public function dateTimeToFormatBar($intDateTime){
		return substr($intDateTime, 6, 2) . "/" .  substr($intDateTime, 4, 2) . "/" . substr($intDateTime, 0, 4) . " " . substr($intDateTime, 8, 2) . ":" . substr($intDateTime, 10, 2) . ":" . substr($intDateTime, 12, 2);
	}

	public function dateToFormatHTML($intDate){
		return substr($intDate, 0, 4) . "-" .  substr($intDate, 4, 2) . "-" . substr($intDate, 6, 2);
	}

	public function monthToFormatHTML($intDate){
		return substr($intDate, 0, 4) . "-" .  substr($intDate, 4, 2);
	}

	public function getDayMonthFormatBar($intDate){
		return  substr($intDate, 4, 2) . "/" .  substr($intDate, 0, 4);
	}

	public function getDateForVoucherService(){
		$date = date('Y-m-d', strtotime("+ 1 month", strtotime(date('Y-m-d'))));
		return substr($date, 0, 4) . "-" . substr($date, 5, 2) . "-01";
	}

	public function obtenerDiferenciaDias($fechaProxDosis, $fechaLimite){
		$fechaP = fechas::parceFechaFormatAMD($fechaProxDosis, "-");
		$dias = (strtotime($fechaP)- strtotime($fechaLimite))/86400;
		$dias = abs($dias); $dias = floor($dias);
		return $dias;
	}

	public function calcularFechaProximaDosis($fechaUltimaDosis, $intervalo){
		$nuevafecha = date("Y-m-d", strtotime("$fechaUltimaDosis + ". $intervalo ." day"));
		$nuevafecha = fechas::getDateToINT($nuevafecha);
		return fechas::dateToFormatBar($nuevafecha, "/");
	}

	public function calcularFechaMinimaDeuda($fechaUltimaDosis, $intervalo){
		$nuevafecha = date("Y-m-d", strtotime("$fechaUltimaDosis - ". $intervalo ." day"));
		$nuevafecha = fechas::parceFechaInt($nuevafecha);
		return fechas::parceFechaFormatDMA($nuevafecha, "/");
	}

	public function esUnaCuotaVencida($fechaUltimaCuota, $fechaPago){
		$fecha = fechas::parceFechaInt(date('Y-m-d'));
		$yearActual = substr($fecha, 0, 4);
		$monthActual = substr($fecha, 4, 2);
		$dayActual = substr($fecha, 6, 2);

		$yearUltimaCuota = substr($fechaUltimaCuota, 0, 4);
		$monthUltimaCuota = substr($fechaUltimaCuota, 4, 2);

		if($yearUltimaCuota < $yearActual){
			return true;
		}else if($yearUltimaCuota == $yearActual){
			if(($monthActual - $monthUltimaCuota) > 1){
				return true;
			}else if(($monthActual - $monthUltimaCuota) == 1){
				if($fechaPago <= $dayActualy){
					return true;
				}
			}
		}
		return false;
	}
}