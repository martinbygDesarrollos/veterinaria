<?php 


class formats{
	
	public function formatCI($ci){
		if(strlen($ci) == 8)
			return substr($ci, 0, 1) . "." . substr($ci, 1, 3) . "." . substr($ci, 4, 3) . "-" . substr($ci, 7, 1);
		else return $ci;
	}
}