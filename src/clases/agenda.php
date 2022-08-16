<?php
define("no_data_select", "mensaje no data");
class agenda{


	public function getCalDataByDayCategory($day, $type){
		$database = new DataBase();
		return $database->sendQuery("SELECT * FROM `agenda` WHERE `categoria` = ? AND `fechaHora` like '%".$day."%' ORDER BY fechaHora ASC LIMIT 30", array('s', $type), "LIST");

	}

	public function modifyNewEvent( $idAgenda, $idUser, $time, $event, $client, $pet ){
		$database = new DataBase();
		return $database->sendQuery("UPDATE `agenda` SET `idUsuario` = ?, `fechaHora` = ?, `descripcion` = ?, `idSocio` = ?, `idMascota` = ? WHERE `agenda`.`idAgenda` = ?", array('issssi', $idUser, $time, $event, $client, $pet, $idAgenda), "BOOLE");
	}

	public function saveNewEvent( $idUser, $time, $event, $client, $pet, $category ){
		$database = new DataBase();
		return $database->sendQuery("INSERT INTO `agenda` (`categoria`, `fechaHora`, `idUsuario`, `descripcion`, `idSocio`, `idMascota`) VALUES (?,?,?,?,?,?)", array('ssisss', $category, $time, $idUser, $event, $client, $pet), "BOOLE");
	}
}

?>