<?php

class vacunas {
    private $idVacuna;
    private $nombre;
    private $codigo;
    private $laboratorio;

    // Intervalo entre dosis , duracion de la vacuna, numero de dosis.

    public function __construct($idVacuna, $nombre, $codigo, $laboratorio){
        $this->idVacuna = $idVacuna;
        $this->nombre = $nombre;
        $this->codigo = $codigo;
        $this->laboratorio = $laboratorio;
    }

    public function getVacunas(){
        $query = DB::conexion()->prepare("SELECT * FROM vacunas");
        if($query->execute()){
            $response = get_result();
            $arrayResponse = array();
            while($row = $response->fetch_array(MYSQLI_ASSOC)){
                $arrayResponse[] = $row;
            }
            return $arrayResponse;
        }else return null;
    }

    public function getVacuna($idVacuna){
        $query = DB::conexion()->prepare("SELECT * FROM vacunas WHERE idVacuna = ?");
        $query->bind_param('i', $idVacuna);
        if($query->execute()){
            $response = $query->get_result();
            return $response->fetch_object();
        }else return null;
    }

    public function getVacunasMascota($idMascota){
        $query = DB::conexion()->prepare("SELECT * FROM vacunas, vacunasmascota WHERE vacunas.idVacuna = vacunasmascota.idVacuna AND vacunasmascota.idMascota = ?");
        $query->bind_param('i', $idMascota);
        if($query->execute()){

            $response = $query->get_result();
            $arrayResponse = array();
            while ($row = $response->fetch_array(MYSQLI_ASSOC)) {
                $arrayResponse[] = $row;
            }
            return $arrayResponse;
        }
        return null;
    }

    public function getVacunaNombre($nombre){
        $query = DB::conexion()->prepare("SELECT * FROM vacunas WHERE nombre = ?");
        $query->bind_param('s', $nombre);
        if($query->execute()){
            $response = $query->get_result();
            return $response->fetch_object();
        }else return null;
    }

    public function getVacunaCodigo($codigo){
        $query = DB::conexion()->prepare("SELECT * FROM vacunas WHERE codigo = ?");
        $query->bind_param('i', $codigo);
        if($query->execute()){
            $response = $query->get_result();
            return $response->fetch_object();
        }else return null;
    }

    public function insertVacuna($nombre, $codigo, $laboratorio){
        $query = DB::conexion()->prepare("INSERT INTO vacunas (nombre, codigo, laboratorio) VALUES (?,?,?)");
        $query->bind_param('sis', $nombre, $codigo, $laboratorio);
        if($query->execute()) return true;
        else return null;
    }

    public function updateVacuna($idVacuna, $nombre, $codigo, $laboratorio){
        $query = DB::conexion()->prepare("UPDATE vacunas SET nombre = ?, codigo = ?,  laboratorio = ? WHERE idVacuna = ?");
        $query->bind_param('sisi', $nombre, $codigo, $laboratorio, $idVacuna);
        if($query->execute()) return true;
        else return null;
    }

    public function getVacunaMascotaID($idMascota, $idVacuna){
        $query = DB::conexion()->prepare("SELECT *  FROM vacunasmascota WHERE idVacuna = ? AND idMascota = ?");
        $query->bind_param('ii', $idVacuna, $idMascota);
        if($query->execute()){
            $response = $query->get_result();
            return $response->fetch_object();
        }else return null;
    }

    public function asignarVacunaMascota($idVacuna, $idMascota, $intervaloDosis, $numDosisTot, $vencimiento){

        $query = DB::conexion()->prepare("INSERT INTO vacunasmascota (idVacuna, idMascota, intervaloDosis, numDosisTot, vencimiento) VALUES (?,?,?,?,?)");
        $query->bind_param('iiiii', $idVacuna, $idMascota, $intervaloDosis, $numDosisTot, $vencimiento);
        if($query->execute()) return true;
        else return false;
    }

    public function modificarVacunaAsignada($idVacunaMascota, $intervaloDosis, $numDosisTot, $vencimiento){

        $query = DB::conexion()->prepare("UPDATE vacunasmascota SET intervaloDosis = ?, numDosisTot = ?, vencimiento = ? WHERE idVacunaMascota  = ?");
        $query->bind_param('iiii', $intervaloDosis, $numDosisTot, $vencimiento, $idVacunaMascota);
        if($query->execute()) return true;
        else return false;
    }

    public function vacunarMascota($idVacunaMascota, $fechaDosis){

        $query = DB::conexion()->prepare("INSERT INTO dosis (idVacunaMascota, fechaHora) VALUES (?,?)");
        $query->bind_param('ii', $idVacunaMascota, $fechaDosis);
        if($query->execute()) return true;
        else return false;
    }


}