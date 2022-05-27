<?php 


class formats{
	
	public function formatCI($ci){
		if(strlen($ci) == 8)
			return substr($ci, 0, 1) . "." . substr($ci, 1, 3) . "." . substr($ci, 4, 3) . "-" . substr($ci, 7, 1);
		else return $ci;
	}

	//ingresa 1030 devuelve 10:30
	public function formatStringToTime($string){
		if (strlen($string) != 4){
			return false;
		}else
			return substr($string, 0, 2) . ":" . substr($string, 2, 2);
	}
}