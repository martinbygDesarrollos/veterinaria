<?php
define("no_data_select", "mensaje no data");
class agenda{


	public function getCirugiasByDay($day){
		$database = new DataBase();
		return $database->sendQuery("SELECT * FROM `agenda` WHERE `categoria` = 'cirugia' AND `fechaHora` like '%".$day."%' ORDER BY fechaHora ASC", array(), "LIST");

	}

	public function modifyNewEventCirugias( $idAgenda, $idUser, $time, $event, $client, $pet ){
		$database = new DataBase();
		return $database->sendQuery("UPDATE `agenda` SET `idUsuario` = ?, `fechaHora` = ?, `descripcion` = ?, `idSocio` = ?, `idMascota` = ? WHERE `agenda`.`idAgenda` = ?", array('issssi', $idUser, $time, $event, $client, $pet, $idAgenda), "BOOLE");
	}

	public function saveNewEventCirugias( $idUser, $time, $event, $client, $pet ){
		$database = new DataBase();
		return $database->sendQuery("INSERT INTO `agenda` (`categoria`, `fechaHora`, `idUsuario`, `descripcion`, `idSocio`, `idMascota`) VALUES (?,?,?,?,?,?)", array('ssisss',"cirugia", $time, $idUser, $event, $client, $pet), "BOOLE");
	}
}

?>