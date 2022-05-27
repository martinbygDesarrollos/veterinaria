<?php
define("no_data_select", "mensaje no data");
class agenda{


	public function getCirugiasByDay($day){
		$database = new DataBase();
		return $database->sendQuery("SELECT * FROM `agenda` WHERE `categoria` = 'cirugia' AND `fechaHora` like '%".$day."%' ORDER BY fechaHora ASC", array(), "LIST");

	}

	public function modifyNewEventCirugias( $idAgenda, $idUser, $time, $event ){
		$database = new DataBase();
		return $database->sendQuery("UPDATE `agenda` SET `idUsuario` = ?, `fechaHora` = ?, `descripcion` = ? WHERE `agenda`.`idAgenda` = ?", array('issi', $idUser, $time, $event, $idAgenda), "BOOLE");
	}

	public function saveNewEventCirugias( $idUser, $time, $event ){
		$database = new DataBase();
		return $database->sendQuery("INSERT INTO `agenda` (`categoria`, `fechaHora`, `idUsuario`, `descripcion`) VALUES (?,?,?,?)", array('ssis',"cirugia", $time, $idUser, $event), "BOOLE");

	}
}

?>