<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'controladores/ctr_mascotas.php';

return function (App $app) {
    $container = $app->getContainer();

	//---------------------------- VISTAS ------------------------------------------------------
    $app->get('/mascotas', function($request, $response, $args) use ($container){
        $args['mascotas'] = ctr_mascotas::getMascotas();
        return $this->view->render($response, "mascotas.twig", $args);
    })->setName('verMascotas');

    $app->get('/newMascota', function($request, $response, $args) use ($container){
        $args['socios'] = ctr_usuarios::getSocios();
        return $this->view->render($response, "newMascota.twig", $args);
    })->setName('newMascota');

    $app->get('/verMascota/{idMascota }', function($request, $response, $args) use ($container){
        $idMascota = $args['idMascota'];
        $args['info'] = ctr_mascotas::getMascota($idMascota);
        return $this->view->render($response, "verMascota.twig", $args);
    })->setName("verMascota");

    //------------------------------------------------------------------------------------------
	//----------------------------- POST -------------------------------------------------------
    $app->post('/insertNewMascota', function(Request $request, Response $response){

    	$data = $request->getParams();
    	$idSocio = $data['idSocio'];
    	$nombre = $data['nombre'];
    	$especie = $data['especie'];
    	$raza = $data['raza'];
    	$sexo = $data['sexo'];
    	$color = $data['color'];
    	$pedigree  = $data['pedigree'];
    	$fechaNacimiento = $data['nacimiento'];
    	$pelo = $data['pelo'];
    	$chip = $data['chip'];
    	$observaciones = $data['observaciones'];

    	return json_encode(ctr_mascotas::insertNewMascota($idSocio, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones));
    });

    $app->post('/insertNewVacuna', function(Request $request, Response $response){
        $data = $request->getParams();

        $nombre = $data['nombre'];
        $codigo = $data['codigo'];
        $fechaVencimiento = $data['fechaVencimiento'];
        $laboratorio = $data['laboratorio'];

        return json_encode(ctr_mascotas::insertNewVacuna($nombre, $codigo, $fechaVencimiento, $laboratorio));
    });

    $app->post('/updateVacuna', function(Request $request, Response $response){

        $data = $request->getParams();
        $nombre = $data['nombre'];
        $codigo = $data['codigo'];
        $fechaVencimiento = $data['fechaVencimiento'];
        $laboratorio = $data['laboratorio'];
    });

    $app->post('/asignarVacunaMascota', function(Request $request, Response $response){
        $data = $request->getParams();

        $idMascota = $data['idMascota'];
        $idVacuna = $data['idVacuna'];

        return json_encode(ctr_mascotas::asignarVacunaMascota($idMascota, $idVacuna));
    });


    $app->post('/vacunarMascota', function(Request $request, Response $response){
        $data = $request->getParams();

        $idMascota = $data['idMascota'];
        $idVacuna = $data['idVacuna'];

        return json_encode(ctr_mascotas::vacunarMascota($idMascota, $idVacuna));
    });

	//------------------------------------------------------------------------------------------

}
?>