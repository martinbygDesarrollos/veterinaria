<?php
define("no_data_select", "mensaje no data");
class agenda{


	public function getCalDataByDayCategory($day, $type){
		$database = new DataBase();
		return $database->sendQuery("SELECT * FROM `agenda` WHERE `categoria` = ? AND `fechaHora` like '%".$day."%' AND (`estado` <> 'eliminado' OR `estado` IS NULL ) ORDER BY fechaHora ASC LIMIT 30", array('s', $type), "LIST");

	}

	public function modifyNewEvent( $idAgenda, $idUser, $time, $event, $client, $pet ){
		$database = new DataBase();
		return $database->sendQuery("UPDATE `agenda` SET `idUsuario` = ?, `fechaHora` = ?, `descripcion` = ?, `idSocio` = ?, `idMascota` = ? WHERE `agenda`.`idAgenda` = ?", array('issssi', $idUser, $time, $event, $client, $pet, $idAgenda), "BOOLE");
	}

	public function saveNewEvent( $idUser, $time, $event, $client, $pet, $category ){
		$database = new DataBase();
		return $database->sendQuery("INSERT INTO `agenda` (`categoria`, `fechaHora`, `idUsuario`, `descripcion`, `idSocio`, `idMascota`) VALUES (?,?,?,?,?,?)", array('ssisss', $category, $time, $idUser, $event, $client, $pet), "BOOLE");
	}

	public function modifyNoteCalendar( $idUser, $time, $event, $client, $pet, $category ){
		$database = new DataBase();
		return $database->sendQuery("UPDATE `agenda` SET `idUsuario` = ?, `descripcion` = ?, `idSocio` = ?, `idMascota` = ? WHERE `agenda`.`fechaHora` = ? AND `agenda`.`categoria` = ?", array('isssss', $idUser, $event, $client, $pet, $time, $category), "BOOLE");
	}



	public function deleteEvent( $idAgenda ){
		$database = new DataBase();
		return $database->sendQuery("UPDATE `agenda` SET `estado` = 'eliminado' WHERE `agenda`.`idAgenda` = ?", array('i', $idAgenda), "BOOLE");
	}

	public function changeStatusEvent( $idAgenda, $status ){

		if ($status == "")
			$status = null;

		$database = new DataBase();
		return $database->sendQuery("UPDATE `agenda` SET `estado` = ? WHERE `agenda`.`idAgenda` = ?", array('si', $status, $idAgenda), "BOOLE");
	}



	public function saveNewGuarderia( $idUser, $client, $pet, $dateInit, $dateFinish ){
		$database = new DataBase();
		return $database->sendQuery("INSERT INTO `agenda` (`categoria`, `idUsuario`,`idSocio`, `idMascota`, `guarderiaEntrada`, `guarderiaSalida` ) VALUES (?,?,?,?,?,?)", array('siiiss', "guarderia", $idUser, $client, $pet, $dateInit, $dateFinish), "BOOLE");
	}


	public function updateEventGuarderia( $idEvent, $dateInit, $dateFinish, $pet, $client ){
		$database = new DataBase();
		return $database->sendQuery("UPDATE `agenda` SET `guarderiaEntrada` = ?, `guarderiaSalida` = ?, `idMascota` = ?, `idSocio` = ? WHERE `agenda`.`idAgenda` = ?",
			array("ssiii", $dateInit, $dateFinish, $pet, $client, $idEvent), "BOOLE");
	}



	public function getGuarderias($pagination){
		$database = new DataBase();
		$sql = 'SELECT * FROM `agenda`
			WHERE categoria = "guarderia" AND (estado <> "eliminado" OR estado IS null)
			ORDER BY
				`agenda`.`guarderiaEntrada` DESC,
				`agenda`.`idAgenda` DESC
			LIMIT ?,30';
		$params = array('i', $pagination);
		return $database->sendQuery($sql, $params, "LIST");
	}


	//Tener en cuentra que esta consulta se hace cuando se quieren descargar los datos en un pdf por eso no hay límite
	public function getCalendarDocumentByDayCategory($day, $type){
		$database = new DataBase();
		$sql = "SELECT ag.*, s.nombre AS socionombre, s.telefono, s.telefax, s.direccion, m.nombre
		FROM `agenda` AS ag
		LEFT JOIN socios AS s ON s.idSocio = ag.idSocio
		LEFT JOIN mascotas AS m ON m.idMascota = ag.idMascota
		WHERE ag.`categoria` = ? AND
			ag.`fechaHora` LIKE '%".$day."%' AND
			(ag.`estado` <> 'eliminado' OR ag.`estado` IS NULL )
		ORDER BY fechaHora ASC";

		return $database->sendQuery($sql, array('s', $type), "LIST");

	}

}

?>